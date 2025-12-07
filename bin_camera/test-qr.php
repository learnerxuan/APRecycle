<?php
// Test QR code generation and validation
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'aprecycle';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get john_warrior's data
$stmt = $conn->prepare("SELECT user_id, username, qr_code FROM user WHERE username = 'john_warrior'");
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("john_warrior not found in database");
}

echo "<h2>User Data:</h2>";
echo "User ID: " . $user['user_id'] . "<br>";
echo "Username: " . $user['username'] . "<br>";
echo "Stored QR Code: " . htmlspecialchars($user['qr_code']) . "<br><br>";

// Generate what the QR code SHOULD be
$secret_key = "APRecycle2024SecretKey";
$verification_hash = substr(hash('sha256', $user['user_id'] . $user['username'] . $secret_key), 0, 16);
$expected_qr = "RECYCLER:{$user['user_id']}:{$verification_hash}";

echo "<h2>Expected QR Code:</h2>";
echo htmlspecialchars($expected_qr) . "<br><br>";

echo "<h2>Match Status:</h2>";
if ($user['qr_code'] === $expected_qr) {
    echo "<span style='color:green'>✓ QR codes match! Code is valid.</span><br>";
} else {
    echo "<span style='color:red'>✗ QR codes DO NOT match!</span><br>";
    echo "<br><strong>Problem:</strong> The stored QR code doesn't match the expected format.<br>";
    echo "<strong>Solution:</strong> Regenerate the QR code.<br>";
}

$conn->close();
?>
