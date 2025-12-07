<?php
// Regenerate QR code for john_warrior
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'aprecycle';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get john_warrior
$stmt = $conn->prepare("SELECT user_id, username FROM user WHERE username = 'john_warrior'");
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("john_warrior not found");
}

// Generate correct QR code
$secret_key = "APRecycle2024SecretKey";
$verification_hash = substr(hash('sha256', $user['user_id'] . $user['username'] . $secret_key), 0, 16);
$qr_data = "RECYCLER:{$user['user_id']}:{$verification_hash}";

// Update database
$update = $conn->prepare("UPDATE user SET qr_code = ? WHERE user_id = ?");
$update->bind_param("si", $qr_data, $user['user_id']);
$update->execute();

echo "<h2>QR Code Regenerated Successfully!</h2>";
echo "<p>Username: {$user['username']}</p>";
echo "<p>User ID: {$user['user_id']}</p>";
echo "<p>QR Code Data: <code>" . htmlspecialchars($qr_data) . "</code></p>";

// Generate QR code image
$qr_image_url = "https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=" . urlencode($qr_data);

echo "<h3>Your QR Code:</h3>";
echo "<img src='{$qr_image_url}' alt='QR Code' style='border: 2px solid #000; padding: 20px; background: white;'>";
echo "<br><br>";
echo "<p><strong>Instructions:</strong> Screenshot this QR code and use it to test the bin camera.</p>";

$conn->close();
?>
