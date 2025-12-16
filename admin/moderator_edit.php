<?php
session_start();
require_once '../php/config.php';
$conn = getDBConnection(); // ✅ FIXED

$id = $_GET['id'] ?? 0;
$error = '';

$stmt = $conn->prepare("SELECT * FROM user WHERE user_id = ? AND role = 'eco-moderator'");
$stmt->bind_param("i", $id);
$stmt->execute();
$mod = $stmt->get_result()->fetch_assoc();

if (!$mod) die("Moderator not found");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (!empty($password)) {
        $hashed_pw = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE user SET username = ?, email = ?, password = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $username, $email, $hashed_pw, $id);
    } else {
        $sql = "UPDATE user SET username = ?, email = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $username, $email, $id);
    }

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
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($mod['username']); ?>" style="width:100%; padding:10px; margin-bottom:15px;" required>
                
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($mod['email']); ?>" style="width:100%; padding:10px; margin-bottom:15px;" required>
                
                <label>New Password (Leave blank to keep current)</label>
                <input type="password" name="password" style="width:100%; padding:10px; margin-bottom:15px;">
                
                <button type="submit" class="btn btn-primary w-100">Save Changes</button>
            </form>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>