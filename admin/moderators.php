<?php
session_start();
require_once '../php/config.php';

// Check Admin Role (Assuming session check logic)
// if ($_SESSION['role'] !== 'administrator') header("Location: ../login.php");

// Handle Remove Logic
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM user WHERE user_id = ? AND role = 'eco-moderator'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: moderators.php");
    exit();
}

// Fetch Moderators
$sql = "SELECT * FROM user WHERE role = 'eco-moderator' ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Moderator Management - APRecycle</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .mod-card { background: white; padding: 1.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center; }
        .mod-avatar { width: 50px; height: 50px; background: var(--color-gray-200); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4" style="display:flex; justify-content:space-between;">
            <h2>Eco-Moderator Management</h2>
            <a href="moderator_add.php" class="btn btn-primary">+ Add New Moderator</a>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="mod-card">
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <div class="mod-avatar"><?php echo strtoupper(substr($row['username'], 0, 1)); ?></div>
                        <div>
                            <h3 style="margin:0; font-size: 1.2rem;"><?php echo htmlspecialchars($row['username']); ?></h3>
                            <small style="color: gray;"><?php echo htmlspecialchars($row['email']); ?></small>
                        </div>
                    </div>
                    <div>
                        <a href="moderator_edit.php?id=<?php echo $row['user_id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                        <a href="?delete=<?php echo $row['user_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove this moderator? This cannot be undone.')">Remove</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No moderators found.</p>
        <?php endif; ?>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>