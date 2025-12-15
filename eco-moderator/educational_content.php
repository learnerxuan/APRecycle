<?php
session_start();
require_once '../php/config.php';

// Check role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'eco-moderator') {
    header("Location: ../login.php");
    exit();
}

// Fetch content
$sql = "SELECT * FROM educational_content ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educational Content Library - APRecycle</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .library-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--color-white);
            padding: var(--space-6);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--space-6);
        }
        
        .section-title {
            background: var(--color-gray-200);
            padding: var(--space-3) var(--space-4);
            font-weight: 600;
            border-top-left-radius: var(--radius-lg);
            border-top-right-radius: var(--radius-lg);
            margin-top: var(--space-6);
        }

        .content-card {
            background: var(--color-white);
            padding: var(--space-4);
            border-bottom: 1px solid var(--color-gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .content-card:last-child {
            border-bottom: none;
            border-bottom-left-radius: var(--radius-lg);
            border-bottom-right-radius: var(--radius-lg);
        }

        .meta-info {
            color: var(--color-gray-500);
            font-size: var(--text-sm);
            margin-top: var(--space-2);
        }

        .action-buttons {
            display: flex;
            gap: var(--space-2);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <a href="dashboard.php" class="btn btn-secondary mb-4">&larr; Back to Main Menu</a>

        <div class="library-header">
            <h2>Educational Content Library</h2>
            <a href="content-creation.php" class="btn btn-primary">+ Create New</a>
        </div>

        <div class="section-title">Published Content</div>
        <div class="card" style="padding: 0; border-top-left-radius: 0; border-top-right-radius: 0;">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="content-card">
                        <div>
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                            <div class="meta-info">
                                Published <?php echo date('M d, Y', strtotime($row['created_at'])); ?> 
                                &bull; Category: <?php echo htmlspecialchars($row['tags']); ?>
                            </div>
                        </div>
                        <div class="action-buttons">
                            <a href="content-edit.php?id=<?php echo $row['content_id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                            <a href="content-delete.php?id=<?php echo $row['content_id']; ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirm('Are you sure you want to delete this article?');">Delete</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="content-card">
                    <p>No content found. Start by creating a new article.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>