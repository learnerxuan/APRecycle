<?php
// 1. Setup Page Variables
$page_title = 'Challenge Results';

// 2. Include Configuration and Header
require_once '../php/config.php';
include_once 'includes/header.php';

// 3. Connect to Database
$conn = getDBConnection();

// 4. Pagination Setup
$limit = 10; // Challenges per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// 5. Query to get Past Challenges
$sql = "SELECT * FROM challenge 
        WHERE end_date < CURDATE() 
        ORDER BY end_date DESC 
        LIMIT $start, $limit";

$result = mysqli_query($conn, $sql);

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM challenge WHERE end_date < CURDATE()";
$count_result = mysqli_query($conn, $count_sql);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);
?>

<style>
    /* Card Style for Challenges */
    .results-container {
        background: var(--color-white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--color-gray-200);
        overflow: hidden;
    }

    .challenge-row {
        display: flex;
        border-bottom: 1px solid var(--color-gray-100);
        padding: var(--space-6);
        align-items: center;
        gap: var(--space-6);
        transition: background 0.2s;
    }

    .challenge-row:last-child { border-bottom: none; }
    .challenge-row:hover { background: var(--color-gray-50); }

    /* Date Box */
    .date-box {
        background: var(--color-gray-100);
        border-radius: var(--radius-md);
        padding: var(--space-3);
        text-align: center;
        min-width: 80px;
        border: 1px solid var(--color-gray-200);
    }
    
    .date-month { font-size: var(--text-xs); text-transform: uppercase; color: var(--color-gray-600); font-weight: 700; display: block; }
    .date-year { font-size: var(--text-sm); color: var(--color-gray-500); }

    /* Challenge Info */
    .challenge-info { flex: 1; }
    .challenge-title { font-size: var(--text-lg); font-weight: 700; color: var(--color-gray-800); margin-bottom: var(--space-1); }
    .challenge-meta { font-size: var(--text-sm); color: var(--color-gray-500); }

    /* Winner Section */
    .winner-card {
        background: #fffbeb; /* Light Yellow */
        border: 1px solid #fcd34d;
        border-radius: var(--radius-lg);
        padding: var(--space-3) var(--space-5);
        display: flex;
        align-items: center;
        gap: var(--space-3);
        min-width: 250px;
    }

    .winner-icon {
        width: 40px; 
        height: 40px; 
        background: #fbbf24; 
        color: white; 
        border-radius: 50%; 
        display: flex; 
        align-items: center; 
        justify-content: center;
        font-size: var(--text-lg);
    }

    .winner-details { display: flex; flex-direction: column; }
    .winner-label { font-size: var(--text-xs); text-transform: uppercase; letter-spacing: 0.5px; color: #92400e; font-weight: 700; }
    .winner-name { font-weight: 600; color: var(--color-gray-800); }
    .winner-score { font-size: var(--text-sm); color: var(--color-gray-600); }

    .no-winner {
        color: var(--color-gray-400);
        font-style: italic;
        font-size: var(--text-sm);
    }

    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        gap: var(--space-2);
        margin-top: var(--space-6);
    }

    .page-link {
        padding: var(--space-2) var(--space-4);
        border: 1px solid var(--color-gray-300);
        background: white;
        color: var(--color-gray-700);
        text-decoration: none;
        border-radius: var(--radius-md);
        font-size: var(--text-sm);
    }

    .page-link.active {
        background: var(--color-primary);
        color: white;
        border-color: var(--color-primary);
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .challenge-row { flex-direction: column; align-items: flex-start; }
        .winner-card { width: 100%; margin-top: var(--space-3); }
        .date-box { display: flex; gap: 10px; width: 100%; align-items: center; justify-content: center; }
    }
</style>

<div class="page-header">
    <h1 class="page-title">Past Challenge Results</h1>
    <p class="page-description">Archive of completed challenges and their top performers.</p>
</div>

<div class="results-container">
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($challenge = mysqli_fetch_assoc($result)): 
            $start_date = $challenge['start_date'];
            $end_date = $challenge['end_date'];
            
            // FIX: Changed SUM(points) to COUNT(*) because 'points' column does not exist
            // This now calculates the winner based on NUMBER OF APPROVED SUBMISSIONS
            $winner_sql = "SELECT u.username, COUNT(*) as score
                           FROM recycling_submission rs
                           JOIN user u ON rs.user_id = u.user_id
                           WHERE rs.status = 'Approved' 
                           AND rs.created_at BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'
                           GROUP BY rs.user_id
                           ORDER BY score DESC
                           LIMIT 1";
            
            $winner_result = mysqli_query($conn, $winner_sql);
            $winner = ($winner_result) ? mysqli_fetch_assoc($winner_result) : null;
        ?>
            <div class="challenge-row">
                <div class="date-box">
                    <span class="date-month"><?php echo date('M', strtotime($end_date)); ?></span>
                    <span style="font-size: var(--text-xl); font-weight: 800; display:block;"><?php echo date('d', strtotime($end_date)); ?></span>
                    <span class="date-year"><?php echo date('Y', strtotime($end_date)); ?></span>
                </div>

                <div class="challenge-info">
                    <h3 class="challenge-title"><?php echo htmlspecialchars($challenge['title']); ?></h3>
                    <div class="challenge-meta">
                        <i class="far fa-calendar-alt"></i> 
                        <?php echo date('M d, Y', strtotime($start_date)); ?> — <?php echo date('M d, Y', strtotime($end_date)); ?>
                    </div>
                    <p style="margin-top: var(--space-2); color: var(--color-gray-600); font-size: var(--text-sm);">
                        <?php echo htmlspecialchars($challenge['description']); ?>
                    </p>
                </div>

                <?php if ($winner): ?>
                    <div class="winner-card">
                        <div class="winner-icon">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div class="winner-details">
                            <span class="winner-label">Winner</span>
                            <span class="winner-name"><?php echo htmlspecialchars($winner['username']); ?></span>
                            <span class="winner-score"><?php echo number_format($winner['score']); ?> Items Recycled</span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="winner-card" style="background: var(--color-gray-50); border-color: var(--color-gray-200);">
                        <div class="winner-icon" style="background: var(--color-gray-300);">
                            <i class="fas fa-minus"></i>
                        </div>
                        <div class="winner-details">
                            <span class="winner-label" style="color: var(--color-gray-500);">Result</span>
                            <span class="winner-name" style="color: var(--color-gray-500);">No participants</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="padding: var(--space-8); text-align: center; color: var(--color-gray-500);">
            <i class="fas fa-clipboard-list" style="font-size: 48px; margin-bottom: var(--space-4); opacity: 0.5;"></i>
            <p>No past challenges found in the archives.</p>
        </div>
    <?php endif; ?>
</div>

<?php if ($total_pages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?page=<?php echo ($page - 1); ?>" class="page-link">&laquo; Prev</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?php echo $i; ?>" class="page-link <?php echo ($i == $page) ? 'active' : ''; ?>">
            <?php echo $i; ?>
        </a>
    <?php endfor; ?>

    <?php if ($page < $total_pages): ?>
        <a href="?page=<?php echo ($page + 1); ?>" class="page-link">Next &raquo;</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php
mysqli_close($conn);
include_once 'includes/footer.php';
?>