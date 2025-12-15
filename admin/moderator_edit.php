<?php
session_start();
require_once '../php/config.php';

$id = $_GET['id'] ?? 0;
$error = '';

// Fetch current data
$stmt = $conn->prepare("SELECT * FROM user WHERE user_id = ? AND role = 'eco-moderator'");
$stmt->bind_param("i", $id);
$stmt->execute();
$mod = $stmt->get_result()->fetch_assoc();

if (!$mod) die("Moderator not found");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    
    // Only update password if provided
    $sql = "UPDATE user SET username = ?, email = ? WHERE user_id = ?";
    $params = [$username, $email, $id];
    $types = "ssi";
    
    if (!empty($_POST['password'])) {
        $sql = "UPDATE user SET username = ?, email = ?, password = ? WHERE user_id = ?";
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $params = [$username, $email, $password, $id];
        $types = "sssi";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        header("Location: moderators.php");
        exit();
    } else {
        $error = "Error updating moderator.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Moderator - APRecycle</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <a href="moderators.php" class="btn btn-secondary mb-4">&larr; Back</a>
        
        <div class="card" style="padding: 2rem; max-width: 600px; margin: 0 auto;">
            <h2>Edit Eco-Moderator</h2>
            <?php if($error) echo "<p style='color: red;'>$error</p>"; ?>
            
            <form method="POST">
                <label>Full Name</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($mod['username']); ?>" class="form-control mb-4" style="width:100%; padding:10px;" required>
                
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($mod['email']); ?>" class="form-control mb-4" style="width:100%; padding:10px;" required>
                
                <label>New Password (Leave blank to keep current)</label>
                <input type="password" name="password" class="form-control mb-4" style="width:100%; padding:10px;">
                
                <button type="submit" class="btn btn-primary w-100">Update Information</button>
            </form>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>