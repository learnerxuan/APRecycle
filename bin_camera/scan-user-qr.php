<?php
/**
 * Bin Camera - User QR Code Scanner
 * Scans recycler's QR code and links waste to their account
 */

header('Content-Type: application/json');

// Database configuration
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'aprecycle';

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

// Connect to database
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    $response['message'] = "Database connection failed: " . $conn->connect_error;
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
$target_dir = "uploads/";
$file_name = "waste_" . uniqid() . ".jpg";
$target_file_path = $target_dir . $file_name;
$image_data_binary = base64_decode($base64Image);

if (!is_dir($target_dir))
    mkdir($target_dir, 0777, true);

if (!file_put_contents($target_file_path, $image_data_binary)) {
    $response['message'] = 'Failed to save image file.';
    echo json_encode($response);
    $conn->close();
    exit;
}

// Determine status and points based on confidence
$class_lower = strtolower($classification);
$is_valid_waste = true;

if (strpos($class_lower, 'not a waste') !== false || strpos($class_lower, 'non-recyclable') !== false) {
    $is_valid_waste = false;
}

if ($confidence >= 0.80 && $is_valid_waste) {
    $status = 'Approved';
    $points_awarded = 15;
} elseif ($confidence >= 0.80 && !$is_valid_waste) {
    $status = 'Rejected';
    $points_awarded = 0;
} else {
    $status = 'Pending';
    $points_awarded = 0;
}

$simulated_bin_id = 1;

// Map Classification to Material ID
$material_map = [
    'plastic bottle' => 1,
    'plastic' => 1,
    'aluminum can' => 2,
    'can' => 2,
    'glass bottle' => 3,
    'glass' => 3,
    'cardboard box' => 4,
    'cardboard' => 4,
    'paper' => 5,
    'e-waste' => 6,
    'metal' => 8,
    'metal scrap' => 8,
    'plastic container' => 9,
    'tetra pak' => 10
];

$detected_material_id = null;
foreach ($material_map as $key => $id) {
    if (strpos($class_lower, $key) !== false) {
        $detected_material_id = $id;
        break;
    }
}

// Default multiplier
$total_multiplier = 1.0;

// Check and update challenges
if ($detected_material_id) {
    $challenge_stmt = $conn->prepare("
            SELECT uc.challenge_id, c.target_material_id, c.point_multiplier 
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
            // Update challenge progress (+1 item recycled)
            $update_ch = $conn->prepare("UPDATE user_challenge SET challenge_point = challenge_point + 1 WHERE user_id = ? AND challenge_id = ?");
            $update_ch->bind_param("ii", $user['user_id'], $ch['challenge_id']);
            $update_ch->execute();
            $update_ch->close();

            // Apply highest multiplier
            if ($ch['point_multiplier'] > $total_multiplier) {
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
$stmt->bind_param("iisds", $user['user_id'], $simulated_bin_id, $target_file_path, $confidence, $status);

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