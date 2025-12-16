<?php
// 1. Setup Page Variables
$page_title = 'Team Rankings';

// 2. Include Configuration and Header
require_once '../php/config.php';
include_once 'includes/header.php';

// 3. Connect to Database
$conn = getDBConnection();

// 4. Handle Pagination & Search Logic
$limit = 20; // Teams per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

$search_term = '';
$where_clause = "WHERE 1=1"; // Default always true

// Add search filter if exists
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = mysqli_real_escape_string($conn, $_GET['search']);
    $where_clause .= " AND t.team_name LIKE '%$search_term%'";
}

// 5. Query to get Total Records (for pagination buttons)
$count_sql = "SELECT COUNT(*) as total FROM team t $where_clause";
$count_result = mysqli_query($conn, $count_sql);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);

// 6. Main Query to get Teams
// FIX: Removed 't.created_at' because your database does not have that column
$sql = "SELECT t.team_id, t.team_name,
        (SELECT COUNT(*) FROM user u WHERE u.team_id = t.team_id) as member_count,
        (SELECT COALESCE(SUM(u.lifetime_points), 0) FROM user u WHERE u.team_id = t.team_id) as team_total_points
        FROM team t 
        $where_clause
        ORDER BY team_total_points DESC, member_count DESC
        LIMIT $start, $limit";

$result = mysqli_query($conn, $sql);
?>

<style>
    /* Reusing Styles from Individual Leaderboard for Consistency */
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
    
    .btn-primary:hover { opacity: 0.9; }

    .leaderboard-container {
        background: var(--color-white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        border: 1px solid var(--color-gray-200);
    }

    .table-responsive { overflow-x: auto; }

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

    tr:last-child td { border-bottom: none; }
    tr:hover { background: var(--color-gray-50); }

    /* Rank Badges */
    .rank-cell { font-weight: bold; width: 60px; text-align: center; }
    .rank-icon { margin-right: 5px; }
    .gold { color: #FFD700; }
    .silver { color: #C0C0C0; }
    .bronze { color: #CD7F32; }
    
    /* Green Badge for Team Points */
    .points-badge {
        background: #dcfce7; /* Light Green */
        color: #166534;       /* Dark Green */
        padding: 4px 12px;
        border-radius: 999px;
        font-weight: 700;
        font-size: var(--text-sm);
    }

    .member-count-badge {
        background: var(--color-gray-100);
        color: var(--color-gray-600);
        padding: 2px 8px;
        border-radius: 4px;
        font-size: var(--text-xs);
        font-weight: 600;
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
    
    .page-link:hover:not(.active) { background: var(--color-gray-50); }
</style>

<div class="page-header">
    <h1 class="page-title">Team Leaderboard</h1>
    <p class="page-description">Ranking of recycling teams based on combined member points.</p>
</div>

<div class="toolbar">
    <div style="font-weight: 600; color: var(--color-gray-700);">
        Total Teams: <?php echo number_format($total_records); ?>
    </div>
    <form class="search-form" method="GET" action="">
        <input type="text" name="search" class="search-input" placeholder="Search team name..." value="<?php echo htmlspecialchars($search_term); ?>">
        <button type="submit" class="btn-primary">
            <i class="fas fa-search"></i>
        </button>
        <?php if(!empty($search_term)): ?>
            <a href="leaderboard_team.php" class="btn-primary" style="background: var(--color-gray-500); display:flex; align-items:center;">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="leaderboard-container">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th class="rank-cell">Rank</th>
                    <th>Team Name</th>
                    <th>Members</th>
                    <th style="text-align: right;">Total Team Points</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php 
                    $counter = 0;
                    while ($row = mysqli_fetch_assoc($result)): 
                        $rank = $start + $counter + 1;
                        $counter++;
                    ?>
                        <tr>
                            <td class="rank-cell">
                                <?php if($rank == 1): ?>
                                    <i class="fas fa-trophy rank-icon gold"></i>
                                <?php elseif($rank == 2): ?>
                                    <i class="fas fa-medal rank-icon silver"></i>
                                <?php elseif($rank == 3): ?>
                                    <i class="fas fa-medal rank-icon bronze"></i>
                                <?php else: ?>
                                    #<?php echo $rank; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 32px; height: 32px; background: #e0f2fe; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--color-primary);">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <span style="font-weight: 600;"><?php echo htmlspecialchars($row['team_name']); ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="member-count-badge">
                                    <i class="fas fa-user-friends" style="font-size: 10px; margin-right: 4px;"></i>
                                    <?php echo number_format($row['member_count']); ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <span class="points-badge">
                                    <?php echo number_format($row['team_total_points']); ?> pts
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px; color: var(--color-gray-500);">
                            No teams found matching your criteria.
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
        <a href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search_term); ?>" class="page-link">&laquo; Prev</a>
    <?php endif; ?>

    <?php
    $range = 2;
    for ($i = 1; $i <= $total_pages; $i++):
        if ($i == 1 || $i == $total_pages || ($i >= $page - $range && $i <= $page + $range)):
    ?>
            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search_term); ?>" class="page-link <?php echo ($i == $page) ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php elseif ($i == $page - $range - 1 || $i == $page + $range + 1): ?>
            <span style="padding: 5px;">...</span>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($page < $total_pages): ?>
        <a href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search_term); ?>" class="page-link">Next &raquo;</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php
mysqli_close($conn);
include_once 'includes/footer.php';
?>