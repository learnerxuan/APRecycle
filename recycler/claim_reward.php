<?php
session_start();
require_once '../php/config.php';

// Check if user is logged in and is a recycler
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'recycler') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Check if reward_id is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reward_id'])) {
    $reward_id = intval($_POST['reward_id']);

    // Verify that the user has this reward and it's unclaimed
    $check_stmt = $conn->prepare("SELECT reward_id FROM user_reward WHERE user_id = ? AND reward_id = ? AND is_claimed = 0");
    $check_stmt->bind_param("ii", $user_id, $reward_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Update the reward to claimed
        $update_stmt = $conn->prepare("UPDATE user_reward SET is_claimed = 1 WHERE user_id = ? AND reward_id = ?");
        $update_stmt->bind_param("ii", $user_id, $reward_id);

        if ($update_stmt->execute()) {
            $_SESSION['claim_success'] = "Reward claimed successfully!";
        } else {
            $_SESSION['claim_error'] = "Failed to claim reward. Please try again.";
        }

        $update_stmt->close();
    } else {
        $_SESSION['claim_error'] = "Invalid reward or already claimed.";
    }

    $check_stmt->close();
} else {
    $_SESSION['claim_error'] = "Invalid request.";
}

$conn->close();

// Redirect back to achievements page
header('Location: achievements.php');
exit();
?>