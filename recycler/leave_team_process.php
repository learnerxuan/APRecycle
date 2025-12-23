<?php
session_start();
require_once '../php/config.php';

// 1. Role Check - Ensure only logged-in recyclers can access this script
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'recycler' || !isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// 2. Prepare the SQL to remove the user from their team
// This sets the team_id to NULL for the current user in the 'user' table
$sql = "UPDATE user SET team_id = NULL WHERE user_id = ?";

if ($stmt = $conn->prepare($sql)) {
    // Bind the session user_id to the query as an integer ("i")
    $stmt->bind_param("i", $user_id);
    
    // 3. Execute the update
    if ($stmt->execute()) {
        // Success: Redirect back to the teams page with a status message
        header("Location: teams.php?status=left");
    } else {
        // Database error: Redirect back with an error message
        header("Location: teams.php?status=error");
    }
    $stmt->close();
} else {
    // Error in preparing the SQL statement
    header("Location: teams.php?status=error");
}

$conn->close();
exit();
?>