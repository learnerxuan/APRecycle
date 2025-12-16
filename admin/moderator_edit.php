<?php
session_start();
require_once '../php/config.php';

// Check Admin
// if ($_SESSION['role'] !== 'administrator') { header("Location: ../login.php"); exit(); }

$id = $_GET['id'] ?? 0;
$error = '';

// Fetch existing data
$stmt = $conn->prepare("SELECT * FROM user WHERE user_id = ? AND role = 'eco-moderator'");
$stmt->bind_param("i", $id);
$stmt->execute();
$mod = $stmt->get_result()->fetch_assoc();

if (!$mod) {
    die("Moderator not found.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; // Raw password
    
    if (!empty($password)) {
        // Update WITH password
        $hashed_pw = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE user SET username = ?, email = ?, password = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $username, $email, $hashed_pw, $id);
    } else {
        // Update WITHOUT password
        $sql = "UPDATE user SET username = ?, email = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $username, $email, $id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Moderator updated.'); window.location.href='moderators.php';</script>";
        exit();
    } else {
        $error = "Error updating database: " . $conn->error;
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
            <h2 class="mb-4">Edit Eco-Moderator</h2>
            <?php if($error) echo "<div style='color:red; margin-bottom:15px;'>$error</div>"; ?>
            
            <form method="POST">
                <div class="mb-4">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($mod['username']); ?>" required style="width:100%; padding:10px;">
                </div>
                
                <div class="mb-4">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($mod['email']); ?>" required style="width:100%; padding:10px;">
                </div>
                
                <div class="mb-4">
                    <label>New Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password" style="width:100%; padding:10px;">
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Save Changes</button>
            </form>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>