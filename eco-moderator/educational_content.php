<?php
session_start();
require_once '../php/config.php';
$conn = getDBConnection();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'eco-moderator') {
    header("Location: ../login.php");
    exit();
}

$sql_pub = "SELECT * FROM educational_content WHERE status = 'published' ORDER BY created_at DESC";
$res_pub = $conn->query($sql_pub);

$sql_draft = "SELECT * FROM educational_content WHERE status = 'draft' ORDER BY created_at DESC";
$res_draft = $conn->query($sql_draft);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educational Content Library - APRecycle</title>
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
        .page-hero p { font-size: 1.125rem; opacity: 0.95; margin: 0 0 1.5rem 0; }

        .btn-hero-action {
            display: inline-flex; align-items: center; gap: 8px;
            background: white; color: var(--color-primary);
            padding: 10px 24px; border-radius: 30px;
            font-weight: 700; text-decoration: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-hero-action:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,0.15); }

        .section-title { 
            background: var(--color-gray-100); 
            padding: 1rem 1.5rem; 
            font-weight: 600; color: var(--color-gray-700);
            border-top-left-radius: 12px; 
            border-top-right-radius: 12px; 
            margin-top: 2rem; 
            border-bottom: 1px solid #e2e8f0;
        }
        
        .content-card { 
            background: white; 
            padding: 1.5rem; 
            border-bottom: 1px solid #e2e8f0; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        .content-card:last-child { border-bottom: none; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; }
        
        .meta-info { color: var(--color-gray-500); font-size: 0.875rem; margin-top: 0.5rem; }
        .action-buttons { display: flex; gap: 0.5rem; }

        .btn-sm {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            border: none;
            cursor: pointer;
            display: inline-block;
            transition: all 0.2s ease;
            line-height: 1.2;
        }
        .btn-sm:hover { transform: translateY(-1px); filter: brightness(95%); box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .btn-danger.btn-sm:hover { background-color: #dc2626 !important; filter: brightness(110%); color: white; }

        @media (max-width: 768px) {
            .page-hero { padding: 2rem 1rem; }
            .page-hero h1 { font-size: 1.75rem; }
            .content-card { flex-direction: column; align-items: flex-start; gap: 1rem; }
            .action-buttons { width: 100%; justify-content: flex-start; gap: 0.5rem; flex-wrap: wrap; }
            .action-buttons .btn-sm { flex: 1; text-align: center; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container" style="padding-top: 2rem;">
        
        <div class="page-hero">
            <h1>Educational Content Library</h1>
            <p>Manage articles, tips, and guides for the recycling community.</p>
            <a href="content-creation.php" class="btn-hero-action">
                <i class="fas fa-plus"></i> Create New Content
            </a>
        </div>

        <div class="section-title">Published Content</div>
        <div class="card" style="padding: 0; border-top-left-radius: 0; border-top-right-radius: 0; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <?php if ($res_pub && $res_pub->num_rows > 0): ?>
                <?php while($row = $res_pub->fetch_assoc()): ?>
                    <div class="content-card">
                        <div style="flex: 1;">
                            <h3 style="margin: 0; color: var(--color-gray-800); font-size: 1.125rem;"><?php echo htmlspecialchars($row['title']); ?></h3>
                            <div class="meta-info">
                                <?php 
                                    $raw_tags = $row['tags'];
                                    $normalized_tags = str_replace(['General, Tips', 'General,Tips'], 'General Tips', $raw_tags);
                                    $tags_display = htmlspecialchars(implode(', ', array_map('trim', explode(',', $normalized_tags))));
                                ?>
                                Published <?php echo date('M d, Y', strtotime($row['created_at'])); ?> 
                                &bull; Category: <span style="font-weight: 600; color: var(--color-primary);"><?php echo $tags_display; ?></span>
                            </div>
                        </div>
                        <div class="action-buttons">
                            <a href="educational_content_view.php?id=<?php echo $row['content_id']; ?>" class="btn btn-secondary btn-sm" style="background: #edf2f7; color: #4a5568;">View</a>
                            <a href="content-edit.php?id=<?php echo $row['content_id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                            <a href="content-delete.php?id=<?php echo $row['content_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this article?');">Delete</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="content-card">
                    <p style="color: var(--color-gray-500); margin: 0;">No published content.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="section-title" style="margin-top: 2.5rem; background: #fff7ed; color: #9a3412;">Drafts</div>
        <div class="card" style="padding: 0; border-top-left-radius: 0; border-top-right-radius: 0; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <?php if ($res_draft && $res_draft->num_rows > 0): ?>
                <?php while($row = $res_draft->fetch_assoc()): ?>
                    <div class="content-card">
                        <div style="flex: 1;">
                            <h3 style="margin: 0; color: var(--color-gray-800); font-size: 1.125rem;">
                                <?php echo htmlspecialchars($row['title']); ?> 
                                <span style="font-size: 0.75rem; background: #fed7aa; color: #9a3412; padding: 2px 8px; border-radius: 4px; margin-left: 8px;">DRAFT</span>
                            </h3>
                            <div class="meta-info">
                                Created <?php echo date('M d, Y', strtotime($row['created_at'])); ?> 
                            </div>
                        </div>
                        <div class="action-buttons">
                            <a href="content-edit.php?id=<?php echo $row['content_id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                            <a href="content-delete.php?id=<?php echo $row['content_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this draft?');">Delete</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="content-card">
                    <p style="color: var(--color-gray-500); margin: 0;">No drafts saved.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>