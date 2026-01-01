<?php
include('../php/config.php');
include('includes/header.php');

$conn = getDBConnection(); //
$period = isset($_GET['period']) ? $_GET['period'] : 'lifetime';
$current_user_id = $_SESSION['user_id'];

// SQL Query Logic Fixes
if ($period === 'monthly') {
    // Monthly points based on approved submissions in the current month
    $query = "SELECT u.user_id, u.username, 
              SUM(m.points_per_item * sm.quantity) as points,
              SUM(sm.quantity) as items_count
              FROM user u
              JOIN recycling_submission rs ON u.user_id = rs.user_id
              JOIN submission_material sm ON rs.submission_id = sm.submission_id
              JOIN material m ON sm.material_id = m.material_id
              WHERE rs.status = 'approved' 
              AND MONTH(rs.created_at) = MONTH(CURRENT_DATE())
              AND YEAR(rs.created_at) = YEAR(CURRENT_DATE())
              GROUP BY u.user_id
              ORDER BY points DESC LIMIT 10";
} else {
    // Lifetime points - Fixed the table alias conflict
    $query = "SELECT user_id, username, lifetime_points as points,
              (SELECT IFNULL(SUM(sm.quantity), 0) 
               FROM submission_material sm 
               JOIN recycling_submission rs ON sm.submission_id = rs.submission_id 
               WHERE rs.user_id = user.user_id AND rs.status = 'approved') as items_count
              FROM user 
              WHERE role = 'recycler'
              ORDER BY points DESC LIMIT 10";
}

$result = mysqli_query($conn, $query);
$rankings = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $rankings[] = $row;
    }
}
?>

<div style="margin-bottom: var(--space-6); text-align: right;">
    <a href="leaderboard.php" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: white; color: var(--color-gray-700); text-decoration: none; border-radius: 8px; border: 1px solid var(--color-gray-200); font-weight: 500; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.background='var(--color-gray-50)'; this.style.borderColor='var(--color-gray-300)';" onmouseout="this.style.background='white'; this.style.borderColor='var(--color-gray-200)';">
        <i class="fas fa-arrow-left"></i>
        <span>Back</span>
    </a>
</div>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: var(--space-8);">
    <div>
        <h1 class="page-title">Individual Rankings</h1>
        <p class="page-description">Celebrating APU's most dedicated environmental heroes.</p>
    </div>
    <div style="background: var(--color-gray-100); padding: var(--space-1); border-radius: var(--radius-md); display: flex; gap: var(--space-1);">
        <a href="?period=lifetime" class="btn" style="padding: var(--space-2) var(--space-4); background: <?php echo $period == 'lifetime' ? 'var(--color-white)' : 'transparent'; ?>; color: var(--color-gray-700); box-shadow: <?php echo $period == 'lifetime' ? 'var(--shadow-sm)' : 'none'; ?>; border:none; cursor:pointer; text-decoration: none;">Lifetime</a>
        <a href="?period=monthly" class="btn" style="padding: var(--space-2) var(--space-4); background: <?php echo $period == 'monthly' ? 'var(--color-white)' : 'transparent'; ?>; color: var(--color-gray-700); box-shadow: <?php echo $period == 'monthly' ? 'var(--shadow-sm)' : 'none'; ?>; border:none; cursor:pointer; text-decoration: none;">Monthly</a>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--space-6); margin-bottom: var(--space-10); align-items: end;">
    <?php 
    $podium_map = [1 => '🥈', 0 => '🥇', 2 => '🥉']; 
    $display_order = [1, 0, 2]; // 2nd, 1st, 3rd for visual podium
    foreach($display_order as $idx): 
        if(isset($rankings[$idx])): 
            $user = $rankings[$idx];
            $is_gold = ($idx == 0);
    ?>
        <div class="card text-center" style="padding: var(--space-6); border-bottom: 4px solid <?php echo $is_gold ? 'var(--color-accent-yellow)' : 'var(--color-gray-300)'; ?>; transition: transform 0.3s ease;">
            <div style="font-size: 3rem; margin-bottom: var(--space-2);"><?php echo $podium_map[$idx]; ?></div>
            <div class="recycler-user-avatar" style="margin: 0 auto var(--space-3); width: 60px; height: 60px; font-size: var(--text-xl); background: var(--gradient-primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
            </div>
            <h3 style="margin-bottom: var(--space-1);"><?php echo htmlspecialchars($user['username']); ?></h3>
            <p style="color: var(--color-primary); font-weight: 700; font-size: var(--text-lg);"><?php echo number_format($user['points']); ?> pts</p>
        </div>
    <?php endif; endforeach; ?>
</div>

<div class="card" style="padding: 0; overflow: hidden; border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead style="background: var(--color-gray-50); color: var(--color-gray-600);">
            <tr>
                <th style="padding: var(--space-4); border-bottom: 1px solid var(--color-gray-200);">Rank</th>
                <th style="padding: var(--space-4); border-bottom: 1px solid var(--color-gray-200);">Recycler</th>
                <th style="padding: var(--space-4); border-bottom: 1px solid var(--color-gray-200);">Items</th>
                <th style="padding: var(--space-4); border-bottom: 1px solid var(--color-gray-200); text-align: right;">Points</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (count($rankings) > 3):
                for($i = 3; $i < count($rankings); $i++): 
                    $user = $rankings[$i];
                    $is_me = ($user['user_id'] == $current_user_id);
            ?>
            <tr style="border-bottom: 1px solid var(--color-gray-100); <?php echo $is_me ? 'background: var(--color-success-light);' : ''; ?>">
                <td style="padding: var(--space-4); font-weight: 700; color: var(--color-gray-400);">#<?php echo $i + 1; ?></td>
                <td style="padding: var(--space-4); font-weight: 600;">
                    <div style="display: flex; align-items: center; gap: var(--space-2);">
                        <?php echo htmlspecialchars($user['username']); ?> 
                        <?php if($is_me): ?><span class="badge" style="background: var(--color-primary); color: white; padding: 2px 6px; font-size: 10px; border-radius: 4px;">YOU</span><?php endif; ?>
                    </div>
                </td>
                <td style="padding: var(--space-4); color: var(--color-gray-500);"><?php echo number_format($user['items_count']); ?> items</td>
                <td style="padding: var(--space-4); text-align: right; font-weight: 700; color: var(--color-primary);"><?php echo number_format($user['points']); ?></td>
            </tr>
            <?php 
                endfor; 
            elseif (count($rankings) <= 3 && count($rankings) > 0):
            ?>
                <tr><td colspan="4" style="padding: var(--space-8); text-align: center; color: var(--color-gray-500);">Top contributors are shown in the podium above.</td></tr>
            <?php else: ?>
                <tr><td colspan="4" style="padding: var(--space-8); text-align: center; color: var(--color-gray-500);">No recycling data found for this period.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include('includes/footer.php'); ?>