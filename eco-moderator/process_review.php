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
$action = $_POST['action'];
$material_id = intval($_POST['material_id']);
$moderator_feedback = "Manual Review by Moderator"; // Could extend to allow custom feedback

if ($action === 'reject') {
    $stmt = $conn->prepare("UPDATE recycling_submission SET status='rejected', moderator_feedback=? WHERE submission_id=?");
    $stmt->bind_param("si", $moderator_feedback, $submission_id);
    $stmt->execute();
    $stmt->close();
    header('Location: review-queue.php?msg=Submission rejected.');
    exit();

} elseif ($action === 'approve') {
    // 1. Update Submission Status
    $stmt = $conn->prepare("UPDATE recycling_submission SET status='approved', moderator_feedback=? WHERE submission_id=?");
    $stmt->bind_param("si", $moderator_feedback, $submission_id);
    $stmt->execute();
    $stmt->close();

    // 2. Update/Fix Material if needed
    // First check if submission_material exists
    $check_mat = $conn->query("SELECT * FROM submission_material WHERE submission_id = $submission_id");
    if ($check_mat->num_rows > 0) {
        $update_mat = $conn->prepare("UPDATE submission_material SET material_id=? WHERE submission_id=?");
        $update_mat->bind_param("ii", $material_id, $submission_id);
        $update_mat->execute();
    } else {
        $insert_mat = $conn->prepare("INSERT INTO submission_material (submission_id, material_id, quantity) VALUES (?, ?, 1)");
        $insert_mat->bind_param("ii", $submission_id, $material_id);
        $insert_mat->execute();
    }

    // 3. Award Points (Logic copied from scan-user-qr.php)
    // Fetch User ID
    $user_res = $conn->query("SELECT user_id FROM recycling_submission WHERE submission_id = $submission_id");
    $user_row = $user_res->fetch_assoc();
    $recycler_id = $user_row['user_id'];

    if ($recycler_id) {
        $points_awarded = 15; // Base points
        $total_multiplier = 1.0;

        // Check active challenges
        $challenge_sql = "SELECT uc.challenge_id, c.target_material_id, c.point_multiplier 
                          FROM user_challenge uc 
                          JOIN challenge c ON uc.challenge_id = c.challenge_id 
                          WHERE uc.user_id = ? AND c.end_date >= CURDATE() AND c.start_date <= CURDATE()";

        $stmt_ch = $conn->prepare($challenge_sql);
        $stmt_ch->bind_param("i", $recycler_id);
        $stmt_ch->execute();
        $challenges = $stmt_ch->get_result();

        while ($ch = $challenges->fetch_assoc()) {
            // Check if material matches (or if it's a generic challenge)
            if (is_null($ch['target_material_id']) || $ch['target_material_id'] == $material_id) {
                // Update challenge progress
                $update_ch = $conn->prepare("UPDATE user_challenge SET challenge_point = challenge_point + 1 WHERE user_id = ? AND challenge_id = ?");
                $update_ch->bind_param("ii", $recycler_id, $ch['challenge_id']);
                $update_ch->execute();

                if ($ch['point_multiplier'] > $total_multiplier) {
                    $total_multiplier = $ch['point_multiplier'];
                }
            }
        }

        // Apply Multiplier
        $final_points = floor($points_awarded * $total_multiplier);

        // Update User Points
        if ($final_points > 0) {
            $update_pts = $conn->prepare("UPDATE user SET lifetime_points = lifetime_points + ? WHERE user_id = ?");
            $update_pts->bind_param("ii", $final_points, $recycler_id);
            $update_pts->execute();
        }
    }

    header('Location: review-queue.php?msg=Submission approved and points awarded.');
    exit();
}

$conn->close();
?>