<?php
session_start();
require_once '../php/config.php';
$conn = getDBConnection();

// Check if user is logged in as recycler
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'recycler') {
    header('Location: ../login.php');
    exit();
}

$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'All';

// [FIX 1] Added "AND status = 'published'" to prevent drafts from showing
$sql = "SELECT * FROM educational_content WHERE title LIKE ? AND status = 'published'";
$params = ["%$search%"];
$types = "s";

if ($filter !== 'All') {
    if ($filter === 'General Tips') {
        // [FIX] Ensure status check is respected when filtering tags
        $sql .= " AND (tags LIKE ? OR tags LIKE ?)";
        $params[] = "%General%";
        $params[] = "%Tips%";
        $types .= "ss";
    } else {
        $sql .= " AND tags LIKE ?";
        $params[] = "%$filter%";
        $types .= "s";
    }
}

$sql .= " ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educational Content - APRecycle</title>
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

        .search-container { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 2.5rem; border: 1px solid #e2e8f0; }
        .search-input { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-family: inherit; outline: none; transition: border-color 0.2s; }
        .search-input:focus { border-color: var(--color-primary); }
        .filter-tags { display: flex; gap: 0.75rem; flex-wrap: wrap; margin-top: 1.5rem; }
        .filter-tag { padding: 8px 16px; border: 1px solid #e2e8f0; border-radius: 20px; background: white; color: #4a5568; cursor: pointer; font-size: 0.9rem; font-weight: 500; transition: all 0.2s; }
        .filter-tag:hover, .filter-tag.active { background: var(--color-primary); color: white; border-color: var(--color-primary); }
        
        .article-card { background: white; padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; display: flex; gap: 1.5rem; text-decoration: none; color: inherit; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; transition: transform 0.2s, box-shadow 0.2s; }
        .article-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border-color: var(--color-primary); }
        .article-thumb { width: 120px; height: 120px; border-radius: 8px; object-fit: cover; background: #f7fafc; flex-shrink: 0; }

        @media (max-width: 768px) {
            .page-hero { padding: 2rem 1.5rem; }
            .page-hero h1 { font-size: 1.75rem; }
            .search-container { padding: 1.5rem; }
            .article-card { flex-direction: column; gap: 1rem; }
            .article-thumb { width: 100%; height: 180px; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container" style="padding-top: 2rem;">
        
        <div class="page-hero">
            <h1>Educational Content</h1>
            <p>Learn about recycling best practices, sustainability tips, and environmental impact.</p>
        </div>
        
        <div class="search-container">
            <form action="" method="GET">
                <input type="text" name="search" class="search-input" placeholder="Search articles..." value="<?php echo htmlspecialchars($search); ?>">
                
                <div class="filter-tags">
                    <?php 
                    $categories = ['All', 'Plastic', 'Paper', 'Metal', 'E-Waste', 'General Tips'];
                    foreach($categories as $cat): 
                        $activeClass = ($filter === $cat) ? 'active' : '';
                    ?>
                        <button type="submit" name="filter" value="<?php echo $cat; ?>" class="filter-tag <?php echo $activeClass; ?>">
                            <?php echo $cat; ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </form>
        </div>

        <h3 style="margin-bottom: 1.5rem; color: #2d3748; font-size: 1.5rem;">Featured Articles</h3>
        
        <div class="articles-list">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <a href="educational_content_view.php?id=<?php echo $row['content_id']; ?>" class="article-card">
                        
                        <?php if (!empty($row['image'])): ?>
                            <img src="../<?php echo htmlspecialchars($row['image']); ?>" alt="Thumbnail" class="article-thumb">
                        <?php else: ?>
                            <div class="article-thumb" style="display: flex; align-items: center; justify-content: center; background-color: #e2e8f0; color: #a0aec0;">
                                <i class="fas fa-book-open fa-2x"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div style="flex: 1; width: 100%;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                                <h4 style="margin: 0; color: #2d3748; font-size: 1.25rem;"><?php echo htmlspecialchars($row['title']); ?></h4>
                                <?php 
                                    $raw_tags = $row['tags'];
                                    $normalized_tags = str_replace(['General, Tips', 'General,Tips'], 'General Tips', $raw_tags);
                                    $tags_display = htmlspecialchars(implode(', ', array_map('trim', explode(',', $normalized_tags))));
                                ?>
                                <span style="background: #edf2f7; color: #4a5568; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; white-space: nowrap; margin-left: 1rem;"><?php echo $tags_display; ?></span>
                            </div>
                            <div style="font-size: 0.85rem; color: #a0aec0; margin-bottom: 0.75rem;">
                                Published <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                            </div>
                            <p style="font-size: 0.95rem; color: #4a5568; margin: 0; line-height: 1.6;">
                                <?php echo substr(htmlspecialchars(strip_tags($row['content_body'])), 0, 140); ?>...
                            </p>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem; background: white; border-radius: 12px; color: #718096; border: 1px solid #e2e8f0;">
                    <p style="font-size: 1.1rem;">No articles found matching your search.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>