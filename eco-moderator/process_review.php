<?php
session_start();
require_once '../php/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'eco-moderator') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: review-queue.php');
    exit();
}

$conn = getDBConnection();

$submission_id = intval($_POST['submission_id']);
$user_id = intval($_POST['user_id']);
$action = $_POST['action'];
$material_id = intval($_POST['material_id']);

if ($action === 'reject') {
    // Get rejection details
    $reject_reason = $_POST['reject_reason'] ?? 'Manual Review';
    $reject_feedback = $_POST['reject_feedback'] ?? 'Your submission was rejected by our eco-moderator.';

    // Create moderator feedback message
    $moderator_feedback = "Reason: " . $reject_reason . "\n\nFeedback: " . $reject_feedback;

    // Update submission status
    $stmt = $conn->prepare("UPDATE recycling_submission SET status='rejected', moderator_feedback=? WHERE submission_id=?");
    $stmt->bind_param("si", $moderator_feedback, $submission_id);
    $stmt->execute();
    $stmt->close();

    // Send notification to recycler's inbox
    $notification_title = "❌ Submission Rejected - #" . $submission_id;
    $notification_message = "**Reason:** " . $reject_reason . "\n\n**Feedback:**\n" . $reject_feedback . "\n\n*Please review our educational materials and try again. Learn from this experience to improve your recycling submissions!*";

    // Insert notification using recycling_submission table for storage
    // We'll store it in moderator_feedback and mark status as 'rejected'
    // The inbox will read from this

    // Optional: Create notification table entry if it exists
    $check_notification_table = $conn->query("SHOW TABLES LIKE 'notification'");
    if ($check_notification_table->num_rows > 0) {
        $stmt_notif = $conn->prepare("INSERT INTO notification (user_id, title, message, type, submission_id, is_read) VALUES (?, ?, ?, 'error', ?, FALSE)");
        $stmt_notif->bind_param("issi", $user_id, $notification_title, $notification_message, $submission_id);
        $stmt_notif->execute();
        $stmt_notif->close();
    }

    header('Location: review-queue.php?msg=' . urlencode('Submission #' . $submission_id . ' rejected. Feedback sent to recycler.'));
    exit();

} elseif ($action === 'approve') {
    // Create approval feedback
    $moderator_feedback = "✅ Approved by eco-moderator. Great job recycling!";

    // 1. Update Submission Status
    $stmt = $conn->prepare("UPDATE recycling_submission SET status='approved', moderator_feedback=? WHERE submission_id=?");
    $stmt->bind_param("si", $moderator_feedback, $submission_id);
    $stmt->execute();
    $stmt->close();

    // 2. Update/Fix Material if needed
    $check_mat = $conn->query("SELECT * FROM submission_material WHERE submission_id = $submission_id");
    if ($check_mat->num_rows > 0) {
        $update_mat = $conn->prepare("UPDATE submission_material SET material_id=? WHERE submission_id=?");
        $update_mat->bind_param("ii", $material_id, $submission_id);
        $update_mat->execute();
        $update_mat->close();
    } else {
        $insert_mat = $conn->prepare("INSERT INTO submission_material (submission_id, material_id, quantity) VALUES (?, ?, 1)");
        $insert_mat->bind_param("ii", $submission_id, $material_id);
        $insert_mat->execute();
        $insert_mat->close();
    }

    // 3. Award Points
    // Get material points
    $mat_result = $conn->query("SELECT points_per_item FROM material WHERE material_id = $material_id");
    $mat_row = $mat_result->fetch_assoc();
    $base_points = $mat_row['points_per_item'] ?? 15;

    $total_multiplier = 1.0;

    // Check active challenges
    $challenge_sql = "SELECT uc.challenge_id, uc.challenge_quantity, uc.challenge_point, uc.is_completed,
                      c.target_material_id, c.point_multiplier,
                      c.target_quantity, c.target_points, c.completion_type, c.badge_id, c.reward_id
                      FROM user_challenge uc
                      JOIN challenge c ON uc.challenge_id = c.challenge_id
                      WHERE uc.user_id = ? AND c.end_date >= CURDATE() AND c.start_date <= CURDATE()";

    $stmt_ch = $conn->prepare($challenge_sql);
    $stmt_ch->bind_param("i", $user_id);
    $stmt_ch->execute();
    $challenges = $stmt_ch->get_result();

    while ($ch = $challenges->fetch_assoc()) {
        // Check if material matches (or if it's a generic challenge)
        if (is_null($ch['target_material_id']) || $ch['target_material_id'] == $material_id) {

            // Calculate new values
            $points_to_add = floor($base_points * $ch['point_multiplier']);
            $new_quantity = $ch['challenge_quantity'] + 1;
            $new_points = $ch['challenge_point'] + $points_to_add;

            // Update challenge progress
            $update_ch = $conn->prepare("UPDATE user_challenge SET challenge_quantity = ?, challenge_point = ? WHERE user_id = ? AND challenge_id = ?");
            $update_ch->bind_param("iiii", $new_quantity, $new_points, $user_id, $ch['challenge_id']);
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
                    $mark_complete->bind_param("ii", $user_id, $ch['challenge_id']);
                    $mark_complete->execute();
                    $mark_complete->close();

                    // Award Badge
                    if (!empty($ch['badge_id'])) {
                        $award_badge = $conn->prepare("INSERT IGNORE INTO user_badge (user_id, badge_id) VALUES (?, ?)");
                        $award_badge->bind_param("ii", $user_id, $ch['badge_id']);
                        $award_badge->execute();
                        $award_badge->close();
                    }

                    // Award Reward
                    if (!empty($ch['reward_id'])) {
                        $award_reward = $conn->prepare("INSERT IGNORE INTO user_reward (user_id, reward_id) VALUES (?, ?)");
                        $award_reward->bind_param("ii", $user_id, $ch['reward_id']);
                        $award_reward->execute();
                        $award_reward->close();
                    }
                }
            }

            // Only apply multiplier if challenge is not completed
            if ($ch['is_completed'] == 0 && $ch['point_multiplier'] > $total_multiplier) {
                $total_multiplier = $ch['point_multiplier'];
            }
        }
    }
    $stmt_ch->close();

    // Apply Multiplier
    $final_points = floor($base_points * $total_multiplier);

    // Update User Points
    if ($final_points > 0) {
        $update_pts = $conn->prepare("UPDATE user SET lifetime_points = lifetime_points + ? WHERE user_id = ?");
        $update_pts->bind_param("ii", $final_points, $user_id);
        $update_pts->execute();
        $update_pts->close();
    }

    // Send success notification to recycler
    $notification_title = "✅ Submission Approved - #" . $submission_id;
    $notification_message = "Congratulations! Your submission has been approved.\n\n**Points Awarded:** " . $final_points . " points\n**Material:** " . ($mat_row['material_name'] ?? 'Recyclable Material') . "\n\n*Keep up the great work! You're making a difference!*";

    // Check if notification table exists
    $check_notification_table = $conn->query("SHOW TABLES LIKE 'notification'");
    if ($check_notification_table->num_rows > 0) {
        // Get material name for notification
        $mat_result2 = $conn->query("SELECT material_name FROM material WHERE material_id = $material_id");
        $mat_row2 = $mat_result2->fetch_assoc();
        $material_name = $mat_row2['material_name'] ?? 'Recyclable Material';

        $notification_message_full = "Congratulations! Your submission has been approved.\n\n**Points Awarded:** " . $final_points . " points\n**Material:** " . $material_name . "\n\n*Keep up the great work! You're making a difference!*";

        $stmt_notif = $conn->prepare("INSERT INTO notification (user_id, title, message, type, submission_id, is_read) VALUES (?, ?, ?, 'success', ?, FALSE)");
        $stmt_notif->bind_param("issi", $user_id, $notification_title, $notification_message_full, $submission_id);
        $stmt_notif->execute();
        $stmt_notif->close();
    }

    header('Location: review-queue.php?msg=' . urlencode('Submission #' . $submission_id . ' approved! ' . $final_points . ' points awarded.'));
    exit();
}

$conn->close();
?>