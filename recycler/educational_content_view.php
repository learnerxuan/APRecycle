<?php
session_start();
require_once '../php/config.php';
$conn = getDBConnection();

$content_id = $_GET['id'] ?? 0;
$sql = "SELECT ec.*, u.username AS author_name FROM educational_content ec LEFT JOIN user u ON ec.author_id = u.user_id WHERE ec.content_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $content_id);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();

if (!$article) { echo "<script>alert('Article not found.'); window.location.href='educational_content.php';</script>"; exit(); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - APRecycle</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .page-hero {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
            color: white;
            padding: 3rem 2rem;
            border-radius: 1rem;
            margin-bottom: 2.5rem;
            text-align: center;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .page-hero h1 { font-size: 2.25rem; font-weight: 700; margin-bottom: 0.5rem; }
        .page-hero p { font-size: 1.125rem; opacity: 0.95; margin: 0; }

        .article-container { background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; overflow: hidden; }
        
        .article-hero { 
            width: 100%; 
            height: auto; 
            max-height: 600px; 
            object-fit: contain; 
            background-color: #f7fafc;
            display: block;
        }
        
        .article-content { padding: 3rem; }
        .badge { background: #edf2f7; color: #4a5568; padding: 6px 14px; border-radius: 20px; font-weight: 600; font-size: 0.85rem; display: inline-block; margin-bottom: 1rem; }

        @media (max-width: 768px) {
            .page-hero { padding: 2rem 1rem; }
            .page-hero h1 { font-size: 1.75rem; }
            .article-content { padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container" style="padding-top: 2rem;">
        <div style="display: flex; justify-content: flex-end; margin-bottom: 1.5rem;">
            <a href="educational_content.php" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: white; color: var(--color-gray-700); text-decoration: none; border-radius: 8px; border: 1px solid var(--color-gray-200); font-weight: 500; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.background='var(--color-gray-50)'; this.style.borderColor='var(--color-gray-300)';" onmouseout="this.style.background='white'; this.style.borderColor='var(--color-gray-200)';">
                <i class="fas fa-arrow-left"></i> <span>Back</span>
            </a>
        </div>

        <div class="page-hero">
            <h1><?php echo htmlspecialchars($article['title']); ?></h1>
            <p>
                By <?php echo htmlspecialchars($article['author_name'] ?? 'APRecycle Team'); ?> 
                &bull; <?php echo date('F d, Y', strtotime($article['created_at'])); ?>
            </p>
        </div>

        <div class="article-container">
            <?php if (!empty($article['image'])): ?>
                <img src="../<?php echo htmlspecialchars($article['image']); ?>" class="article-hero" alt="Article Image">
            <?php endif; ?>

            <div class="article-content">
                <?php 
                    $raw_tags = $article['tags'];
                    $normalized_tags = str_replace(['General, Tips', 'General,Tips'], 'General Tips', $raw_tags);
                    $tags_display = htmlspecialchars(implode(', ', array_map('trim', explode(',', $normalized_tags))));
                ?>
                <span class="badge"><?php echo $tags_display; ?></span>
                
                <div style="line-height: 1.8; font-size: 1.125rem; color: #2d3748; white-space: pre-line; margin-top: 1rem;">
                    <?php echo htmlspecialchars($article['content_body']); ?>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>