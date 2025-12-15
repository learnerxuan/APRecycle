<?php
session_start();
require_once '../php/config.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    // Generate a placeholder QR code string for now
    $qr_code = "QR_MOD_" . uniqid(); 

    // Check if email exists
    $check = $conn->prepare("SELECT user_id FROM user WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $error = "Email already exists.";
    } else {
        $stmt = $conn->prepare("INSERT INTO user (username, email, password, role, qr_code, created_at) VALUES (?, ?, ?, 'eco-moderator', ?, NOW())");
        $stmt->bind_param("ssss", $username, $email, $password, $qr_code);
        
        if ($stmt->execute()) {
            header("Location: moderators.php");
            exit();
        } else {
            $error = "Database Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Moderator - APRecycle</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <a href="moderators.php" class="btn btn-secondary mb-4">&larr; Back</a>
        
        <div class="card" style="padding: 2rem; max-width: 600px; margin: 0 auto;">
            <h2>Add New Eco-Moderator</h2>
            <?php if($error) echo "<p style='color: red;'>$error</p>"; ?>
            
            <form method="POST">
                <label>Full Name / Username</label>
                <input type="text" name="username" class="form-control mb-4" style="width:100%; padding:10px;" required>
                
                <label>Email Address</label>
                <input type="email" name="email" class="form-control mb-4" style="width:100%; padding:10px;" required>
                
                <label>Password</label>
                <input type="password" name="password" class="form-control mb-4" style="width:100%; padding:10px;" required>
                
                <button type="submit" class="btn btn-primary w-100">Add Moderator</button>
            </form>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>