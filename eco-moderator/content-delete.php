<?php
session_start();
require_once '../php/config.php';
$conn = getDBConnection();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'eco-moderator') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM educational_content WHERE content_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: educational_content.php");
exit();
?>