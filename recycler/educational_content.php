<?php
session_start();
require_once '../php/config.php';

// Handle Search and Filter
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'All';

$sql = "SELECT * FROM educational_content WHERE title LIKE ?";
$params = ["%$search%"];
$types = "s";

if ($filter !== 'All') {
    $sql .= " AND tags LIKE ?";
    $params[] = "%$filter%";
    $types .= "s";
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
    <style>
        .search-container {
            background: var(--color-white);
            padding: var(--space-6);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--space-6);
        }
        
        .filter-tags {
            display: flex;
            gap: var(--space-2);
            flex-wrap: wrap;
            margin-top: var(--space-4);
        }

        .filter-tag {
            padding: var(--space-2) var(--space-4);
            border: 1px solid var(--color-gray-300);
            border-radius: var(--radius-md);
            text-decoration: none;
            color: var(--color-gray-700);
            transition: all 0.3s;
        }

        .filter-tag:hover, .filter-tag.active {
            background: var(--color-primary-light);
            color: white;
            border-color: var(--color-primary-light);
        }

        .article-card {
            background: var(--color-white);
            padding: var(--space-4);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--space-4);
            display: flex;
            gap: var(--space-4);
            transition: transform 0.2s;
            text-decoration: none;
            color: inherit;
        }

        .article-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .article-thumb {
            width: 100px;
            height: 100px;
            border-radius: var(--radius-md);
            object-fit: cover;
            background-color: var(--color-gray-100);
        }

        .article-badge {
            background: var(--color-gray-200);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: var(--text-xs);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <h2 class="mb-4">Educational Content</h2>
        
        <div class="search-container">
            <p class="mb-4 text-gray-600">Learn about recycling best practices and environmental impact.</p>
            <form action="" method="GET">
                <input type="text" name="search" class="form-control" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px;" placeholder="Search articles..." value="<?php echo htmlspecialchars($search); ?>">
                
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

        <h3>Featured Articles</h3>
        <div class="articles-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <a href="educational_content_view.php?id=<?php echo $row['content_id']; ?>" class="article-card">
                        <?php 
                            $imgSrc = !empty($row['image']) ? '../' . $row['image'] : '../assets/aprecycle-logo.png';
                        ?>
                        <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="Thumbnail" class="article-thumb">
                        
                        <div>
                            <h4><?php echo htmlspecialchars($row['title']); ?></h4>
                            <div class="mb-2">
                                <span class="article-badge"><?php echo htmlspecialchars($row['tags']); ?></span>
                                <small class="text-gray-500 ml-2">Published <?php echo date('M d, Y', strtotime($row['created_at'])); ?></small>
                            </div>
                            <p style="font-size: 0.9rem; color: var(--color-gray-600);">
                                <?php echo substr(htmlspecialchars(strip_tags($row['content_body'])), 0, 120); ?>...
                            </p>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No articles found.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>