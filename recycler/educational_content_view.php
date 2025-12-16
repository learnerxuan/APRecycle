<?php
session_start();
require_once '../php/config.php';
$conn = getDBConnection(); // ✅ FIXED

$content_id = $_GET['id'] ?? 0;

$sql = "SELECT ec.*, u.username AS author_name 
        FROM educational_content ec 
        LEFT JOIN user u ON ec.author_id = u.user_id 
        WHERE ec.content_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $content_id);
$stmt->execute();
$result = $stmt->get_result();
$article = $result->fetch_assoc();

if (!$article) {
    echo "<script>alert('Article not found.'); window.location.href='educational_content.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($article['title']); ?> - APRecycle</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div style="margin-bottom: 20px;">
            <a href="educational_content.php" class="btn btn-secondary">&larr; Back to Educational Content</a>
        </div>

        <div class="card" style="padding: 0; overflow: hidden;">
            <?php if (!empty($article['image'])): ?>
                <img src="../<?php echo htmlspecialchars($article['image']); ?>" style="width:100%; height: 350px; object-fit: cover;" alt="Hero Image">
            <?php endif; ?>

            <div style="padding: 2.5rem;">
                <span class="badge badge-success"><?php echo htmlspecialchars($article['tags']); ?></span>
                <h1 style="margin: 1rem 0;"><?php echo htmlspecialchars($article['title']); ?></h1>
                
                <div style="display: flex; gap: 20px; color: #666; font-size: 0.9rem; margin-bottom: 2rem; border-bottom: 1px solid #eee; padding-bottom: 1rem;">
                    <span>👤 By <strong><?php echo htmlspecialchars($article['author_name'] ?? 'Unknown'); ?></strong></span>
                    <span>📅 <?php echo date('F d, Y', strtotime($article['created_at'])); ?></span>
                </div>

                <div style="line-height: 1.8; font-size: 1.1rem; color: #333;">
                    <?php echo nl2br(htmlspecialchars($article['content_body'])); ?>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>