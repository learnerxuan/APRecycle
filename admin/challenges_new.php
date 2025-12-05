<?php
require_once '../php/config.php';

$page_title = 'Challenge Management';
$conn = getDBConnection();

// Handle delete challenge
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $challenge_id = (int)$_GET['delete'];

    // Delete related records first (user_challenge)
    $delete_user_challenges = mysqli_prepare($conn, "DELETE FROM user_challenge WHERE challenge_id = ?");
    mysqli_stmt_bind_param($delete_user_challenges, "i", $challenge_id);
    mysqli_stmt_execute($delete_user_challenges);
    mysqli_stmt_close($delete_user_challenges);

    // Delete the challenge
    $delete_stmt = mysqli_prepare($conn, "DELETE FROM challenge WHERE challenge_id = ?");
    mysqli_stmt_bind_param($delete_stmt, "i", $challenge_id);

    if (mysqli_stmt_execute($delete_stmt)) {
        $success_message = "Challenge deleted successfully!";
    } else {
        $error_message = "Error deleting challenge: " . mysqli_error($conn);
    }
    mysqli_stmt_close($delete_stmt);
}

// Fetch active challenges (currently running)
$active_query = "SELECT c.*,
                 b.badge_name,
                 r.reward_name,
                 COUNT(DISTINCT uc.user_id) as participant_count
                 FROM challenge c
                 LEFT JOIN badge b ON c.badge_id = b.badge_id
                 LEFT JOIN reward r ON c.reward_id = r.reward_id
                 LEFT JOIN user_challenge uc ON c.challenge_id = uc.challenge_id
                 WHERE c.start_date <= CURDATE() AND c.end_date >= CURDATE()
                 GROUP BY c.challenge_id
                 ORDER BY c.start_date DESC";
$active_result = mysqli_query($conn, $active_query);

// Fetch upcoming challenges (not started yet)
$upcoming_query = "SELECT c.*,
                   b.badge_name,
                   r.reward_name,
                   COUNT(DISTINCT uc.user_id) as participant_count
                   FROM challenge c
                   LEFT JOIN badge b ON c.badge_id = b.badge_id
                   LEFT JOIN reward r ON c.reward_id = r.reward_id
                   LEFT JOIN user_challenge uc ON c.challenge_id = uc.challenge_id
                   WHERE c.start_date > CURDATE()
                   GROUP BY c.challenge_id
                   ORDER BY c.start_date ASC";
$upcoming_result = mysqli_query($conn, $upcoming_query);

// Fetch past challenges for reference
$past_query = "SELECT c.*,
               b.badge_name,
               r.reward_name,
               COUNT(DISTINCT uc.user_id) as participant_count
               FROM challenge c
               LEFT JOIN badge b ON c.badge_id = b.badge_id
               LEFT JOIN reward r ON c.reward_id = r.reward_id
               LEFT JOIN user_challenge uc ON c.challenge_id = uc.challenge_id
               WHERE c.end_date < CURDATE()
               GROUP BY c.challenge_id
               ORDER BY c.end_date DESC
               LIMIT 5";
$past_result = mysqli_query($conn, $past_query);

// Include admin header
include 'includes/header.php';
?>

    <style>
