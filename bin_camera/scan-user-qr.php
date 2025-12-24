<?php
/**
 * Bin Camera - User QR Code Scanner
 * Scans recycler's QR code and links waste to their account
 */

header('Content-Type: application/json');

// Include config for database connection
require_once '../php/config.php';

// Initialize response
$response = [
    'status' => 'error',
    'message' => 'Initialization error.',
    'user_id' => null,
    'username' => null,
    'points_awarded' => 0
];

// Get QR code data from request
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!isset($data['qr_code']) || empty($data['qr_code'])) {
    $response['message'] = 'Error: No QR code data received.';
    echo json_encode($response);
    exit;
}

// Now we receive image, classification, and confidence instead of submission_id
if (!isset($data['image']) || empty($data['image'])) {
    $response['message'] = 'Error: No image data provided.';
    echo json_encode($response);
    exit;
}

if (!isset($data['classification']) || !isset($data['confidence'])) {
    $response['message'] = 'Error: No classification data provided.';
    echo json_encode($response);
    exit;
}

$qr_code = trim($data['qr_code']); // Trim whitespace
$base64Image = $data['image'];
$classification = $data['classification'];
$confidence = (float) $data['confidence'];

// Parse QR code data
// Expected format: RECYCLER:user_id:verification_hash
$parts = explode(':', $qr_code);

// Debug: Log what we received
error_log("QR Code Scanned: " . $qr_code);
error_log("Parts count: " . count($parts));
if (count($parts) > 0) {
    error_log("Part 0: " . $parts[0]);
}

if (count($parts) !== 3 || $parts[0] !== 'RECYCLER') {
    $response['message'] = 'Invalid QR code format. Please use a valid recycler QR code.';
    $response['debug'] = [
        'received' => $qr_code,
        'parts_count' => count($parts),
        'expected_format' => 'RECYCLER:user_id:hash'
    ];
    echo json_encode($response);
    exit;
}

$scanned_user_id = intval($parts[1]);
$provided_hash = $parts[2];

// Connect to database using config
$conn = getDBConnection();

if (!$conn) {
    $response['message'] = "Database connection failed.";
    echo json_encode($response);
    exit;
}

// Get user from database
$stmt = $conn->prepare("SELECT user_id, username, role FROM user WHERE user_id = ? AND role = 'recycler'");
$stmt->bind_param("i", $scanned_user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $response['message'] = 'User not found or not a recycler.';
    echo json_encode($response);
    $conn->close();
    exit;
}

// Verify hash
$secret_key = "APRecycle2024SecretKey";
$expected_hash = substr(hash('sha256', $user['user_id'] . $user['username'] . $secret_key), 0, 16);

if ($provided_hash !== $expected_hash) {
    $response['message'] = 'Invalid QR code. Verification hash does not match.';
    echo json_encode($response);
    $conn->close();
    exit;
}

// QR code is valid! Now save the image and create the submission
$target_dir = "../uploads/";
$file_name = "waste_" . uniqid() . ".jpg";
$target_file_path = $target_dir . $file_name;
$db_image_path = "uploads/" . $file_name; // Path stored in DB should be relative to web root
$image_data_binary = base64_decode($base64Image);

if (!is_dir($target_dir))
    mkdir($target_dir, 0777, true);

if (!file_put_contents($target_file_path, $image_data_binary)) {
    $response['message'] = 'Failed to save image file.';
    echo json_encode($response);
    $conn->close();
    exit;
}

// Pre-process classification for matching
$class_lower = strtolower($classification);
$is_valid_waste = true;

// Check if AI explicitly says it's not waste
if (strpos($class_lower, 'not a waste') !== false || strpos($class_lower, 'non-recyclable') !== false) {
    $is_valid_waste = false;
}

$simulated_bin_id = 1;

// Fetch all materials from database dynamically
$materials_query = "SELECT material_id, material_name, points_per_item FROM material ORDER BY material_name ASC";
$materials_result = mysqli_query($conn, $materials_query);

if (!$materials_result) {
    $response['message'] = 'Database error: Failed to fetch materials.';
    echo json_encode($response);
    $conn->close();
    exit;
}

// Build material map from database
$material_map = [];
while ($mat = mysqli_fetch_assoc($materials_result)) {
    $mat_name_lower = strtolower($mat['material_name']);
    $material_map[$mat_name_lower] = [
        'id' => $mat['material_id'],
        'points' => $mat['points_per_item']
    ];
}

// Try to match classification to a material in database
$detected_material_id = null;
$material_points = 0;

foreach ($material_map as $mat_name => $mat_data) {
    // Check if classification contains the material name (flexible matching)
    if (strpos($class_lower, $mat_name) !== false) {
        $detected_material_id = $mat_data['id'];
        $material_points = $mat_data['points'];
        break;
    }
}

// Determine status and points based on validation
$status = 'Rejected';
$points_awarded = 0;

if (!$is_valid_waste) {
    // AI says it's not waste - reject
    $status = 'Rejected';
    $points_awarded = 0;
} elseif ($detected_material_id === null) {
    // Item not in materials list - reject
    $status = 'Rejected';
    $points_awarded = 0;
} elseif ($confidence >= 0.80) {
    // High confidence AND material found - approve with material points
    $status = 'Approved';
    $points_awarded = $material_points;
} else {
    // Low confidence but material found - pending review
    $status = 'Pending';
    $points_awarded = 0;
}

// Default multiplier
$total_multiplier = 1.0;

// Check and update challenges
if ($detected_material_id) {
    $challenge_stmt = $conn->prepare("
            SELECT uc.challenge_id, uc.challenge_quantity, uc.challenge_point, uc.is_completed,
                   c.target_material_id, c.point_multiplier,
                   c.target_quantity, c.target_points, c.completion_type, c.badge_id, c.reward_id
            FROM user_challenge uc 
            JOIN challenge c ON uc.challenge_id = c.challenge_id 
            WHERE uc.user_id = ? AND c.end_date >= CURDATE() AND c.start_date <= CURDATE()
        ");
    $challenge_stmt->bind_param("i", $user['user_id']);
    $challenge_stmt->execute();
    $challenges = $challenge_stmt->get_result();

    while ($ch = $challenges->fetch_assoc()) {
        // Check if material matches (or if it's a generic challenge)
        if (is_null($ch['target_material_id']) || $ch['target_material_id'] == $detected_material_id) {

            // Calculate new values
            $points_to_add = floor($material_points * $ch['point_multiplier']);
            $new_quantity = $ch['challenge_quantity'] + 1;
            $new_points = $ch['challenge_point'] + $points_to_add;

            // Update challenge progress
            $update_ch = $conn->prepare("UPDATE user_challenge SET challenge_quantity = ?, challenge_point = ? WHERE user_id = ? AND challenge_id = ?");
            $update_ch->bind_param("iiii", $new_quantity, $new_points, $user['user_id'], $ch['challenge_id']);
            $update_ch->execute();
            $update_ch->close();

            // Check for completion (if not already completed)
            if ($ch['is_completed'] == 0) {
                $completed = false;

                if ($ch['completion_type'] == 'quantity' && $new_quantity >= $ch['target_quantity']) {
                    $completed = true;
                } elseif ($ch['completion_type'] == 'points' && $new_points >= $ch['target_points']) {
                    $completed = true;
                } elseif ($ch['completion_type'] == 'participation' && $new_quantity >= 1) {
                    $completed = true;
                }

                if ($completed) {
                    // Mark as completed
                    $mark_complete = $conn->prepare("UPDATE user_challenge SET is_completed = 1 WHERE user_id = ? AND challenge_id = ?");
                    $mark_complete->bind_param("ii", $user['user_id'], $ch['challenge_id']);
                    $mark_complete->execute();
                    $mark_complete->close();

                    // Award Badge
                    if (!empty($ch['badge_id'])) {
                        $award_badge = $conn->prepare("INSERT IGNORE INTO user_badge (user_id, badge_id) VALUES (?, ?)");
                        $award_badge->bind_param("ii", $user['user_id'], $ch['badge_id']);
                        $award_badge->execute();
                        $award_badge->close();
                    }

                    // Award Reward
                    if (!empty($ch['reward_id'])) {
                        $award_reward = $conn->prepare("INSERT IGNORE INTO user_reward (user_id, reward_id) VALUES (?, ?)");
                        $award_reward->bind_param("ii", $user['user_id'], $ch['reward_id']);
                        $award_reward->execute();
                        $award_reward->close();
                    }
                }
            }

            // Apply highest multiplier (for immediate points calculation)
            // Only apply multiplier if challenge is not completed
            if ($ch['is_completed'] == 0 && $ch['point_multiplier'] > $total_multiplier) {
                $total_multiplier = $ch['point_multiplier'];
            }
        }
    }
    $challenge_stmt->close();
}

// Apply multiplier to points
$points_awarded = floor($points_awarded * $total_multiplier);

// Insert submission with user_id
$stmt = $conn->prepare("INSERT INTO recycling_submission (user_id, bin_id, image_url, ai_confidence, status) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iisds", $user['user_id'], $simulated_bin_id, $db_image_path, $confidence, $status);

if ($stmt->execute()) {
    $submission_id = $conn->insert_id;

    // Link submission material (helper table)
    if ($detected_material_id) {
        $stmt_mat = $conn->prepare("INSERT INTO submission_material (submission_id, material_id, quantity) VALUES (?, ?, 1)");
        $stmt_mat->bind_param("ii", $submission_id, $detected_material_id);
        $stmt_mat->execute();
        $stmt_mat->close();
    }

    // Award points if approved
    if ($points_awarded > 0) {
        $stmt_points = $conn->prepare("UPDATE user SET lifetime_points = lifetime_points + ? WHERE user_id = ?");
        $stmt_points->bind_param("ii", $points_awarded, $user['user_id']);
        $stmt_points->execute();
        $stmt_points->close();
    }

    $response['status'] = 'success';
    $response['message'] = 'Submission saved successfully!';
    $response['user_id'] = $user['user_id'];
    $response['username'] = $user['username'];
    $response['points_awarded'] = $points_awarded;
    if ($total_multiplier > 1.0) {
        $response['message'] .= " (" . $total_multiplier . "x Challenge Multiplier Applied!)";
    }

} else {
    $response['message'] = 'Failed to save submission: ' . $stmt->error;
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>