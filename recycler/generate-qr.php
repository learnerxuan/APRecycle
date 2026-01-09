<?php

session_start();
require_once '../php/config.php';

// Check if user is logged in and is a recycler
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'recycler') {
    header('Location: ../login.php');
    exit();
}

/**
 * Generate QR code data for a recycler
 * Format: RECYCLER:user_id:verification_hash
 */
function generateRecyclerQRData($user_id, $username) {
    // Secret key for hash generation 
    $secret_key = "APRecycle2024SecretKey";

    // Create verification hash using user_id and username
    $verification_hash = substr(hash('sha256', $user_id . $username . $secret_key), 0, 16);

    // Format: RECYCLER:user_id:hash
    $qr_data = "RECYCLER:{$user_id}:{$verification_hash}";

    return $qr_data;
}

/**
 * Get QR code image URL using external API
 */
function getQRCodeImageURL($qr_data, $size = 300) {
    // Using QR Server API 
    $encoded_data = urlencode($qr_data);
    return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encoded_data}";
}

// Generate QR code for current user
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$conn = getDBConnection();

// Generate QR data
$qr_data = generateRecyclerQRData($user_id, $username);

// Update database with QR code data
$stmt = mysqli_prepare($conn, "UPDATE user SET qr_code = ? WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "si", $qr_data, $user_id);

if (mysqli_stmt_execute($stmt)) {
    // Get QR code image URL
    $qr_image_url = getQRCodeImageURL($qr_data);

    // Return JSON response for AJAX calls
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'qr_data' => $qr_data,
            'qr_image_url' => $qr_image_url
        ]);
        exit();
    }
} else {
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Failed to generate QR code'
        ]);
        exit();
    }
    die("Error: " . mysqli_error($conn));
}

mysqli_close($conn);
?>
