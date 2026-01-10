<?php
$page_title = 'Leaderboard Overview';

require_once '../php/config.php';
include_once 'includes/header.php';

if ($_SESSION['role'] !== 'administrator') {
    header('Location: ../login.php');
    exit();
}

$conn = getDBConnection();
$stats = [
    'total_recyclers' => 0,
    'active_this_month' => 0,
    'total_teams' => 0,
    'active_challenges' => 0,
    'top_recyclers' => []
];

$sql = "SELECT COUNT(*) as count FROM user WHERE role = 'recycler'";
if ($result = mysqli_query($conn, $sql)) {
    $stats['total_recyclers'] = mysqli_fetch_assoc($result)['count'];
}

$sql = "SELECT COUNT(DISTINCT user_id) as count FROM recycling_submission 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
if ($result = mysqli_query($conn, $sql)) {
    $stats['active_this_month'] = mysqli_fetch_assoc($result)['count'];
}

$sql = "SELECT COUNT(*) as count FROM team";
if ($result = mysqli_query($conn, $sql)) {
    $stats['total_teams'] = mysqli_fetch_assoc($result)['count'];
}

$sql = "SELECT COUNT(*) as count FROM challenge WHERE end_date >= CURDATE()";
if ($result = mysqli_query($conn, $sql)) {
    $stats['active_challenges'] = mysqli_fetch_assoc($result)['count'];
}

$sql = "SELECT u.username, u.lifetime_points, 
        (SELECT COUNT(*) FROM recycling_submission rs WHERE rs.user_id = u.user_id AND rs.status = 'Approved') as items_count
        FROM user u 
        WHERE u.role = 'recycler' 
        ORDER BY u.lifetime_points DESC 
        LIMIT 3";
if ($result = mysqli_query($conn, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $stats['top_recyclers'][] = $row;
    }
}
?>

<style>
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: var(--space-6);
        margin-bottom: var(--space-8);
    }
    
    .stat-card {
        background: var(--color-white);
        padding: var(--space-6);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        display: flex;
        flex-direction: column;
        border: 1px solid var(--color-gray-200);
    }
    
    .stat-value {
        font-size: var(--text-4xl);
        font-weight: 700;
        color: var(--color-primary);
        line-height: 1;
        margin-bottom: var(--space-2);
    }
    
    .stat-label {
        font-size: var(--text-sm);
        color: var(--color-gray-600);
        font-weight: 600;
        text-transform: uppercase;
    }

    .quick-access-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: var(--space-6);
        margin-bottom: var(--space-8);
    }

    .access-card {
        padding: var(--space-6);
        border-radius: var(--radius-lg);
        color: white;
        text-decoration: none;
        transition: transform 0.2s;
        box-shadow: var(--shadow-md);
    }

    .access-card:hover {
        transform: translateY(-4px);
    }

    .access-card h3 { font-size: var(--text-xl); margin-bottom: var(--space-2); }
    .access-card p { opacity: 0.9; font-size: var(--text-sm); }
    
    .bg-blue { background: var(--color-accent-blue); }
    .bg-green { background: var(--color-secondary); }
    .bg-yellow { background: var(--color-accent-yellow); color: var(--color-gray-900); }
    .bg-yellow h3 { color: var(--color-gray-900); }

    .top-list {
        background: var(--color-white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--color-gray-200);
        overflow: hidden;
    }

    .top-item {
        display: flex;
        align-items: center;
        padding: var(--space-4) var(--space-6);
        border-bottom: 1px solid var(--color-gray-100);
    }

    .top-item:last-child { border-bottom: none; }

    .rank-badge {
        width: 32px;
        height: 32px;
        background: var(--color-gray-100);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-right: var(--space-4);
        color: var(--color-gray-600);
    }

    .rank-1 { background: #FFD700; color: #B4690E; }
    .rank-2 { background: #C0C0C0; color: #2D3748; }
    .rank-3 { background: #CD7F32; color: #744210; }

    .user-info { flex: 1; }
    .user-name { font-weight: 600; display: block; }
    .user-role { font-size: var(--text-xs); color: var(--color-gray-500); }
    
    .user-points { font-weight: 700; color: var(--color-primary); }
</style>

<div class="page-header">
    <h1 class="page-title">Leaderboard Overview</h1>
    <p class="page-description">Overview of campus recycling performance and rankings.</p>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <span class="stat-value"><?php echo number_format($stats['total_recyclers']); ?></span>
        <span class="stat-label">Total Recyclers</span>
    </div>
    <div class="stat-card">
        <span class="stat-value"><?php echo number_format($stats['active_this_month']); ?></span>
        <span class="stat-label">Active This Month</span>
    </div>
    <div class="stat-card">
        <span class="stat-value"><?php echo number_format($stats['total_teams']); ?></span>
        <span class="stat-label">Total Teams</span>
    </div>
    <div class="stat-card">
        <span class="stat-value"><?php echo number_format($stats['active_challenges']); ?></span>
        <span class="stat-label">Active Challenges</span>
    </div>
</div>

<h2 style="margin-bottom: var(--space-4); font-size: var(--text-xl);">View Rankings</h2>
<div class="quick-access-grid">
    <a href="leaderboard_individual.php" class="access-card bg-blue">
        <i class="fas fa-user mb-2"></i>
        <h3>Individual Rankings</h3>
        <p>View top performing students and staff</p>
    </a>
    <a href="leaderboard_team.php" class="access-card bg-green">
        <i class="fas fa-users mb-2"></i>
        <h3>Team Rankings</h3>
        <p>View team competition standings</p>
    </a>
    <a href="leaderboard_challenges.php" class="access-card bg-yellow">
        <i class="fas fa-trophy mb-2"></i>
        <h3>Challenge Results</h3>
        <p>View active and past challenge winners</p>
    </a>
</div>

<h2 style="margin-bottom: var(--space-4); font-size: var(--text-xl);">Top 3 Recyclers (Lifetime)</h2>
<div class="top-list">
    <?php if (count($stats['top_recyclers']) > 0): ?>
        <?php foreach ($stats['top_recyclers'] as $index => $user): ?>
            <div class="top-item">
                <div class="rank-badge rank-<?php echo $index + 1; ?>">
                    <?php echo $index + 1; ?>
                </div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($user['username']); ?></span>
                    <span class="user-role"><?php echo $user['items_count']; ?> items recycled</span>
                </div>
                <div class="user-points">
                    <?php echo number_format($user['lifetime_points']); ?> pts
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="padding: var(--space-4); text-align: center; color: var(--color-gray-500);">
            No recycling data available yet.
        </div>
    <?php endif; ?>
</div>

<?php 
mysqli_close($conn);
include_once 'includes/footer.php'; 
?>