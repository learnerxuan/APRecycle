<?php
session_start();
require_once '../php/config.php';

// 1. Validate the ID from the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: educational_content.php"); // Redirect back to library if invalid
    exit();
}

$content_id = $_GET['id'];

// 2. Fetch the article and join with the user table to get the Author's Name
// (Requirement from Table 11: "displays the author's name through the author_id foreign key")
$sql = "SELECT ec.*, u.username AS author_name 
        FROM educational_content ec 
        LEFT JOIN user u ON ec.author_id = u.user_id 
        WHERE ec.content_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $content_id);
$stmt->execute();
$result = $stmt->get_result();
$article = $result->fetch_assoc();

// 3. Handle case where article doesn't exist
if (!$article) {
    echo "<script>alert('Article not found.'); window.location.href='educational_content.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - APRecycle</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        /* Specific styles for the article reading view */
        .article-container {
            background: var(--color-white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden; /* Ensures image corners follow border radius */
            max-width: 900px;
            margin: 0 auto;
        }

        .article-hero-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            background-color: var(--color-gray-200);
        }

        .article-body {
            padding: var(--space-8);
        }

        .article-header {
            margin-bottom: var(--space-6);
            border-bottom: 1px solid var(--color-gray-200);
            padding-bottom: var(--space-4);
        }

        .article-meta {
            display: flex;
            align-items: center;
            gap: var(--space-4);
            color: var(--color-gray-500);
            font-size: var(--text-sm);
            margin-top: var(--space-2);
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        .tag-badge {
            background: var(--color-primary-light);
            color: var(--color-white);
            padding: 4px 12px;
            border-radius: var(--radius-full);
            font-size: var(--text-xs);
            font-weight: 600;
            text-transform: uppercase;
        }

        .content-text {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--color-gray-800);
            white-space: pre-wrap; /* Preserves paragraph breaks from the textarea */
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .article-hero-image {
                height: 250px;
            }
            .article-body {
                padding: var(--space-4);
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div style="margin-bottom: 20px;">
            <a href="educational_content.php" class="btn btn-secondary">
                &larr; Back to Educational Content
            </a>
        </div>

        <div class="article-container">
            <?php if (!empty($article['image'])): ?>
                <img src="../<?php echo htmlspecialchars($article['image']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="article-hero-image">
            <?php endif; ?>

            <div class="article-body">
                <div class="article-header">
                    <span class="tag-badge">
                        <?php echo htmlspecialchars($article['tags']); ?>
                    </span>

                    <h1 style="margin-top: 10px; margin-bottom: 10px;">
                        <?php echo htmlspecialchars($article['title']); ?>
                    </h1>

                    <div class="article-meta">
                        <div class="meta-item">
                            <span>👤</span> 
                            <span>By <strong><?php echo htmlspecialchars($article['author_name'] ?? 'Unknown'); ?></strong></span>
                        </div>
                        <div class="meta-item">
                            <span>📅</span>
                            <span><?php echo date('F d, Y', strtotime($article['created_at'])); ?></span>
                        </div>
                    </div>
                </div>

                <div class="content-text">
                    <?php 
                    // nl2br converts newlines from the database into HTML <br> tags
                    echo nl2br(htmlspecialchars($article['content_body'])); 
                    ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>