<?php
// 1. Setup Page Variables
$page_title = 'Individual Rankings';

// 2. Include Configuration and Header
require_once '../php/config.php';
include_once 'includes/header.php';

// 3. Connect to Database
$conn = getDBConnection();

// 4. Handle Pagination & Search Logic
$limit = 20; // Users per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page - 1) * $limit;

$search_term = '';
$where_clause = "WHERE u.role = 'recycler'";

// Add search filter if exists
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = mysqli_real_escape_string($conn, $_GET['search']);
    $where_clause .= " AND (u.username LIKE '%$search_term%' OR u.email LIKE '%$search_term%')";
}

// 5. Query to get Total Records (for pagination buttons)
$count_sql = "SELECT COUNT(*) as total FROM user u $where_clause";
$count_result = mysqli_query($conn, $count_sql);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);

// 6. Main Query to get Users
// We join with submissions to get an item count, or use a subquery for performance
$sql = "SELECT u.user_id, u.username, u.email, u.lifetime_points, u.created_at,
        (SELECT COUNT(*) FROM recycling_submission rs WHERE rs.user_id = u.user_id AND rs.status = 'Approved') as items_count
        FROM user u 
        $where_clause
        ORDER BY u.lifetime_points DESC, items_count DESC
        LIMIT $start, $limit";

$result = mysqli_query($conn, $sql);
?>

<style>
    /* Search Bar Styling */
    .toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--space-6);
        background: var(--color-white);
        padding: var(--space-4);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        flex-wrap: wrap;
        gap: var(--space-4);
    }

    .search-form {
        display: flex;
        gap: var(--space-2);
        flex: 1;
        max-width: 400px;
    }

    .search-input {
        flex: 1;
        padding: var(--space-2) var(--space-4);
        border: 1px solid var(--color-gray-300);
        border-radius: var(--radius-md);
        font-size: var(--text-sm);
    }

    .btn-primary {
        background: var(--color-primary);
        color: white;
        border: none;
        padding: var(--space-2) var(--space-4);
        border-radius: var(--radius-md);
        cursor: pointer;
        font-weight: 500;
    }

    .btn-primary:hover {
        opacity: 0.9;
    }

    /* Leaderboard Table Styling */
    .leaderboard-container {
        background: var(--color-white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        border: 1px solid var(--color-gray-200);
    }

    .table-responsive {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    th {
        background: var(--color-gray-50);
        padding: var(--space-4) var(--space-6);
        font-weight: 600;
        color: var(--color-gray-600);
        font-size: var(--text-xs);
        text-transform: uppercase;
        border-bottom: 1px solid var(--color-gray-200);
    }

    td {
        padding: var(--space-4) var(--space-6);
        border-bottom: 1px solid var(--color-gray-100);
        color: var(--color-gray-700);
        vertical-align: middle;
    }

    tr:last-child td {
        border-bottom: none;
    }

    tr:hover {
        background: var(--color-gray-50);
    }

    /* Rank Badges */
    .rank-cell {
        font-weight: bold;
        width: 60px;
        text-align: center;
    }

    .rank-icon {
        margin-right: 5px;
    }

    .gold {
        color: #FFD700;
    }

    .silver {
        color: #C0C0C0;
    }

    .bronze {
        color: #CD7F32;
    }

    .points-badge {
        background: var(--color-primary-light, #e0f2fe);
        color: var(--color-primary);
        padding: 4px 12px;
        border-radius: 999px;
        font-weight: 700;
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

    .page-link:hover:not(.active) {
        background: var(--color-gray-50);
    }
</style>

<div class="page-header">
    <h1 class="page-title">Individual Leaderboard</h1>
    <p class="page-description">Ranking of all recyclers based on lifetime points.</p>
</div>

<div class="toolbar">
    <div style="font-weight: 600; color: var(--color-gray-700);">
        Total Recyclers: <?php echo number_format($total_records); ?>
    </div>
    <form class="search-form" method="GET" action="">
        <input type="text" name="search" class="search-input" placeholder="Search by name or email..."
            value="<?php echo htmlspecialchars($search_term); ?>">
        <button type="submit" class="btn-primary">
            <i class="fas fa-search"></i>
        </button>
        <?php if (!empty($search_term)): ?>
            <a href="leaderboard_individual.php" class="btn-primary"
                style="background: var(--color-gray-500); display:flex; align-items:center; text-decoration: none;">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="leaderboard-container">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th class="rank-cell">Rank</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Items Recycled</th>
                    <th>Joined Date</th>
                    <th style="text-align: right;">Lifetime Points</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php
                    $counter = 0;
                    while ($row = mysqli_fetch_assoc($result)):
                        // Calculate Rank based on page number
                        $rank = $start + $counter + 1;
                        $counter++;
                        ?>
                        <tr>
                            <td class="rank-cell">
                                <?php if ($rank == 1): ?>
                                    <i class="fas fa-trophy rank-icon gold"></i>
                                <?php elseif ($rank == 2): ?>
                                    <i class="fas fa-medal rank-icon silver"></i>
                                <?php elseif ($rank == 3): ?>
                                    <i class="fas fa-medal rank-icon bronze"></i>
                                <?php else: ?>
                                    #<?php echo $rank; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div
                                        style="width: 32px; height: 32px; background: var(--color-gray-200); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: var(--color-gray-600);">
                                        <?php echo strtoupper(substr($row['username'], 0, 1)); ?>
                                    </div>
                                    <span style="font-weight: 600;"><?php echo htmlspecialchars($row['username']); ?></span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo number_format($row['items_count']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                            <td style="text-align: right;">
                                <span class="points-badge">
                                    <?php echo number_format($row['lifetime_points']); ?> pts
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--color-gray-500);">
                            No recyclers found matching your criteria.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search_term); ?>" class="page-link">&laquo;
                Prev</a>
        <?php endif; ?>

        <?php
        $range = 2;
        for ($i = 1; $i <= $total_pages; $i++):
            if ($i == 1 || $i == $total_pages || ($i >= $page - $range && $i <= $page + $range)):
                ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search_term); ?>"
                    class="page-link <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php elseif ($i == $page - $range - 1 || $i == $page + $range + 1): ?>
                <span style="padding: 5px;">...</span>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search_term); ?>" class="page-link">Next
                &raquo;</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
mysqli_close($conn);
include_once 'includes/footer.php';
?>