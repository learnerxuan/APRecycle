<?php
include('../php/config.php');
include('includes/header.php');

$conn = getDBConnection();
$current_user_id = $_SESSION['user_id'];

// Check if a specific challenge is selected for details
$challenge_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($challenge_id > 0) {
    // --- DETAIL VIEW: Specific Challenge Standings (Same as before) ---
    // Fetch Challenge Details
    $stmt = mysqli_prepare($conn, "SELECT *, CASE WHEN end_date >= CURRENT_DATE() THEN 'Active' ELSE 'Completed' END AS challenge_status FROM challenge WHERE challenge_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $challenge_id);
    mysqli_stmt_execute($stmt);
    $challenge_info = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    // Fetch Rankings
    $rank_query = "SELECT u.user_id, u.username, uc.challenge_point 
                   FROM user_challenge uc
                   JOIN user u ON uc.user_id = u.user_id
                   WHERE uc.challenge_id = $challenge_id
                   ORDER BY uc.challenge_point DESC LIMIT 50";
    $rank_result = mysqli_query($conn, $rank_query);
    $rankings = mysqli_fetch_all($rank_result, MYSQLI_ASSOC);
    ?>

    <div class="page-header" style="margin-bottom: var(--space-6);">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <a href="leaderboard_challenges.php" class="btn" style="background: var(--color-gray-200); color: var(--color-gray-700); margin-bottom: var(--space-4); display: inline-flex; align-items: center; gap: 8px; text-decoration: none; padding: 8px 16px; border-radius: 8px;">
                    <i class="fas fa-arrow-left"></i> Back to Challenges
                </a>
                <h1 class="page-title"><?php echo htmlspecialchars($challenge_info['title']); ?> Standings</h1>
            </div>
            <div class="badge" style="background: <?php echo $challenge_info['challenge_status'] == 'Active' ? 'var(--color-success-light)' : 'var(--color-gray-200)'; ?>; color: <?php echo $challenge_info['challenge_status'] == 'Active' ? '#065F46' : 'var(--color-gray-700)'; ?>; font-size: 1rem; padding: 8px 16px; font-weight: 600; border-radius: 20px;">
                <?php echo $challenge_info['challenge_status']; ?>
            </div>
        </div>
    </div>

    <?php if (count($rankings) > 0): ?>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--space-6); margin-bottom: var(--space-10); align-items: end;">
        <?php 
        $podium = [1 => '🥈', 0 => '🥇', 2 => '🥉'];
        $order = [1, 0, 2];
        foreach($order as $idx):
            if(isset($rankings[$idx])):
                $u = $rankings[$idx];
                $is_gold = ($idx == 0);
        ?>
        <div class="card text-center" style="padding: var(--space-6); border-bottom: 4px solid <?php echo $is_gold ? '#FBBF24' : 'var(--color-gray-300)'; ?>; background: white; position: relative; overflow: hidden;">
            <?php if($is_gold): ?><div style="position: absolute; top: 0; left: 0; width: 100%; height: 6px; background: linear-gradient(90deg, #FCD34D, #F59E0B);"></div><?php endif; ?>
            <div style="font-size: 3rem; margin-bottom: var(--space-2);"><?php echo $podium[$idx]; ?></div>
            <div style="width: 60px; height: 60px; background: var(--gradient-primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-3); font-weight: bold; font-size: 1.25rem;">
                <?php echo strtoupper(substr($u['username'], 0, 1)); ?>
            </div>
            <h3 style="margin-bottom: var(--space-1);"><?php echo htmlspecialchars($u['username']); ?></h3>
            <p style="color: var(--color-primary); font-weight: 700; font-size: 1.25rem;"><?php echo number_format($u['challenge_point']); ?> pts</p>
        </div>
        <?php endif; endforeach; ?>
    </div>
    
    <div class="card" style="padding: 0; overflow: hidden; border-radius: var(--radius-lg); background: white;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background: var(--color-gray-50); color: var(--color-gray-600); text-align: left;">
                <tr>
                    <th style="padding: var(--space-4);">Rank</th>
                    <th style="padding: var(--space-4);">Participant</th>
                    <th style="padding: var(--space-4); text-align: right;">Points</th>
                </tr>
            </thead>
            <tbody>
                <?php for($i = 3; $i < count($rankings); $i++): $row = $rankings[$i]; 
                    $is_me = ($row['user_id'] == $current_user_id);
                ?>
                <tr style="border-bottom: 1px solid var(--color-gray-100); <?php echo $is_me ? 'background: var(--color-success-light);' : ''; ?>">
                    <td style="padding: var(--space-4); font-weight: 700; color: var(--color-gray-500);">#<?php echo $i + 1; ?></td>
                    <td style="padding: var(--space-4); font-weight: 500;">
                        <?php echo htmlspecialchars($row['username']); ?>
                        <?php if($is_me): ?><span class="badge" style="background: var(--color-primary); color: white; font-size: 0.7rem; padding: 2px 6px; margin-left: 8px;">YOU</span><?php endif; ?>
                    </td>
                    <td style="padding: var(--space-4); text-align: right; font-weight: 700; color: var(--color-primary);"><?php echo number_format($row['challenge_point']); ?></td>
                </tr>
                <?php endfor; ?>
                <?php if(count($rankings) <= 3 && count($rankings) > 0): ?>
                    <tr><td colspan="3" style="padding: var(--space-8); text-align: center; color: var(--color-gray-500);">Top recyclers are shown above!</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="card text-center" style="padding: var(--space-12);">
            <div style="font-size: 4rem; opacity: 0.5;">🏃</div>
            <h3>No Participants Yet</h3>
            <p>Be the first to join and start recycling!</p>
        </div>
    <?php endif; ?>

<?php
} else {
    // --- LIST VIEW: Recycler Hub (Modified Admin Design) ---

    // Fetch Active Challenges with User Data
    $active_query = "SELECT c.*, 
                     (SELECT COUNT(*) FROM user_challenge WHERE challenge_id = c.challenge_id) as participant_count,
                     (SELECT challenge_point FROM user_challenge WHERE challenge_id = c.challenge_id AND user_id = $current_user_id) as my_points
                     FROM challenge c
                     WHERE c.start_date <= CURDATE() AND c.end_date >= CURDATE()
                     ORDER BY c.end_date ASC"; // Most urgent first
    $active_result = mysqli_query($conn, $active_query);
    $active_challenges = mysqli_fetch_all($active_result, MYSQLI_ASSOC);

    // Fetch Upcoming
    $upcoming_query = "SELECT c.* FROM challenge c WHERE c.start_date > CURDATE() ORDER BY c.start_date ASC";
    $upcoming_result = mysqli_query($conn, $upcoming_query);

    // Fetch Past
    $past_query = "SELECT c.*, 
                   (SELECT challenge_point FROM user_challenge WHERE challenge_id = c.challenge_id AND user_id = $current_user_id) as my_points
                   FROM challenge c WHERE c.end_date < CURDATE() ORDER BY c.end_date DESC LIMIT 5";
    $past_result = mysqli_query($conn, $past_query);
    
    // User Stats Calculation
    $my_stats_query = "SELECT COUNT(*) as joined_count, SUM(challenge_point) as total_points FROM user_challenge WHERE user_id = $current_user_id";
    $my_stats = mysqli_fetch_assoc(mysqli_query($conn, $my_stats_query));
?>

<style>
    /* Admin-inspired styles with Recycler modifications */
    .page-header-actions {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: var(--space-6); padding: var(--space-6);
        background: var(--color-white); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);
    }
    
    /* Stats Row - Recycler Themed */
    .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-4); margin-bottom: var(--space-8); }
    .stat-card { background: white; padding: var(--space-6); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--color-gray-100); text-align: center; }
    .stat-card h3 { font-size: 2.5rem; margin: 0; color: var(--color-primary); font-weight: 800; }
    .stat-card p { margin: 0; color: var(--color-gray-600); font-weight: 500; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; }

    /* Hero Card for Featured Challenge */
    .hero-challenge {
        background: linear-gradient(135deg, var(--color-primary) 0%, #059669 100%);
        color: white;
        border-radius: var(--radius-lg);
        padding: var(--space-8);
        margin-bottom: var(--space-8);
        box-shadow: var(--shadow-md);
        position: relative;
        overflow: hidden;
    }
    .hero-challenge::after {
        content: '🏆';
        position: absolute;
        right: -20px;
        bottom: -20px;
        font-size: 10rem;
        opacity: 0.1;
        transform: rotate(-15deg);
    }
    .hero-content { position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; gap: var(--space-6); }
    .hero-info h2 { font-size: 2rem; margin: 0 0 var(--space-2) 0; color: white; }
    .hero-badge { background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }

    /* Grid Layout */
    .challenges-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: var(--space-6); }
    .challenge-card { 
        background: white; border-radius: var(--radius-lg); padding: var(--space-6); 
        border: 1px solid var(--color-gray-200); transition: all 0.3s ease;
        display: flex; flex-direction: column; height: 100%;
    }
    .challenge-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); border-color: var(--color-primary); }
    
    .challenge-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4); }
    .challenge-title { font-size: 1.25rem; font-weight: 700; color: var(--color-gray-800); margin: 0; line-height: 1.3; }
    
    .meta-row { display: flex; align-items: center; gap: var(--space-2); color: var(--color-gray-600); font-size: 0.9rem; margin-bottom: var(--space-2); }
    .meta-row i { color: var(--color-primary); width: 20px; text-align: center; }
    
    .btn-action { margin-top: auto; padding: var(--space-3); border-radius: var(--radius-md); text-align: center; text-decoration: none; font-weight: 600; transition: all 0.2s; }
    .btn-primary { background: var(--color-primary); color: white; }
    .btn-primary:hover { background: var(--color-primary-light); }
    .btn-outline { border: 2px solid var(--color-gray-200); color: var(--color-gray-700); }
    .btn-outline:hover { border-color: var(--color-gray-400); background: var(--color-gray-50); }

    /* Personal Stats Badge inside Card */
    .my-points-badge {
        background: #ECFDF5; border: 1px solid #10B981; color: #065F46;
        padding: 4px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 700;
        display: inline-flex; align-items: center; gap: 4px; margin-top: var(--space-2);
    }
    
    @media (max-width: 768px) {
        .hero-content { flex-direction: column; align-items: flex-start; }
        .stats-row { grid-template-columns: 1fr; }
    }
</style>

<div class="page-header-actions">
    <h2 style="margin:0;"><i class="fas fa-trophy" style="margin-right: 10px; color: var(--color-primary);"></i> Challenge Hub</h2>
    <div style="font-size: var(--text-sm); color: var(--color-gray-600);">Compete, Recycle, Win!</div>
</div>

<div class="stats-row">
    <div class="stat-card">
        <h3><?php echo count($active_challenges); ?></h3>
        <p>Active Challenges</p>
    </div>
    <div class="stat-card" style="border-color: var(--color-accent-yellow);">
        <h3 style="color: #D97706;"><?php echo number_format($my_stats['total_points'] ?? 0); ?></h3>
        <p style="color: #D97706;">My Lifetime Points</p>
    </div>
    <div class="stat-card">
        <h3><?php echo number_format($my_stats['joined_count'] ?? 0); ?></h3>
        <p>Challenges Joined</p>
    </div>
</div>

<?php if (count($active_challenges) > 0): 
    $hero = $active_challenges[0]; // First one is featured
?>
<h2 style="margin-bottom: var(--space-4); color: var(--color-gray-800); display: flex; align-items: center; gap: 10px;">
    <i class="fas fa-fire" style="color: #EF4444;"></i> Spotlight Challenge
</h2>
<div class="hero-challenge">
    <div class="hero-content">
        <div style="flex: 1;">
            <span class="hero-badge">Ending <?php echo date('M d', strtotime($hero['end_date'])); ?></span>
            <h2 style="margin-top: var(--space-3);"><?php echo htmlspecialchars($hero['title']); ?></h2>
            <p style="color: rgba(255,255,255,0.9); max-width: 600px; line-height: 1.6; margin-bottom: var(--space-4);">
                <?php echo htmlspecialchars($hero['description']); ?>
            </p>
            <div style="display: flex; gap: var(--space-4);">
                <span style="display: flex; align-items: center; gap: 6px; font-weight: 600;">
                    <i class="fas fa-bolt"></i> x<?php echo $hero['point_multiplier']; ?> Points
                </span>
                <span style="display: flex; align-items: center; gap: 6px; font-weight: 600;">
                    <i class="fas fa-users"></i> <?php echo $hero['participant_count']; ?> Recyclers
                </span>
            </div>
        </div>
        <div>
            <a href="?id=<?php echo $hero['challenge_id']; ?>" class="btn" style="background: white; color: var(--color-primary); padding: 12px 32px; font-weight: 700; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                View Standings <i class="fas fa-arrow-right" style="margin-left: 8px;"></i>
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (count($active_challenges) > 1): ?>
    <h3 style="margin-bottom: var(--space-4); color: var(--color-gray-700);">More Active Challenges</h3>
    <div class="challenges-grid">
        <?php for($i = 1; $i < count($active_challenges); $i++): 
            $c = $active_challenges[$i];
        ?>
        <div class="challenge-card">
            <div class="challenge-header">
                <h3 class="challenge-title"><?php echo htmlspecialchars($c['title']); ?></h3>
                <span style="font-size: 0.8rem; font-weight: 700; color: #059669; background: #D1FAE5; padding: 2px 8px; border-radius: 4px;">ACTIVE</span>
            </div>
            <p style="color: var(--color-gray-600); font-size: 0.9rem; flex-grow: 1;"><?php echo htmlspecialchars(substr($c['description'], 0, 80)) . '...'; ?></p>
            
            <div style="border-top: 1px solid var(--color-gray-100); margin-top: var(--space-4); padding-top: var(--space-4);">
                <div class="meta-row">
                    <i class="far fa-clock"></i> Ends <?php echo date('M d', strtotime($c['end_date'])); ?>
                </div>
                <div class="meta-row">
                    <i class="fas fa-bolt"></i> x<?php echo $c['point_multiplier']; ?> Point Multiplier
                </div>
                <?php if($c['my_points'] > 0): ?>
                    <div class="my-points-badge">
                        <i class="fas fa-check-circle"></i> You have <?php echo number_format($c['my_points']); ?> pts
                    </div>
                <?php endif; ?>
            </div>

            <a href="?id=<?php echo $c['challenge_id']; ?>" class="btn-action btn-primary" style="margin-top: var(--space-4);">
                View Standings
            </a>
        </div>
        <?php endfor; ?>
    </div>
<?php endif; ?>

<?php if (mysqli_num_rows($upcoming_result) > 0): ?>
    <h2 style="margin: var(--space-8) 0 var(--space-4); color: var(--color-gray-800); border-top: 1px solid var(--color-gray-200); padding-top: var(--space-8);">
        <i class="fas fa-hourglass-start" style="color: #3B82F6;"></i> Coming Soon
    </h2>
    <div class="challenges-grid">
        <?php while($c = mysqli_fetch_assoc($upcoming_result)): ?>
        <div class="challenge-card" style="opacity: 0.8; background: var(--color-gray-50);">
            <div class="challenge-header">
                <h3 class="challenge-title"><?php echo htmlspecialchars($c['title']); ?></h3>
                <span style="font-size: 0.75rem; font-weight: 700; color: #1E40AF; background: #DBEAFE; padding: 2px 8px; border-radius: 4px;">SOON</span>
            </div>
            <div class="meta-row">
                <i class="far fa-calendar-alt"></i> Starts <?php echo date('M d', strtotime($c['start_date'])); ?>
            </div>
            <p style="color: var(--color-gray-600); font-size: 0.9rem; margin-top: var(--space-2);"><?php echo htmlspecialchars($c['description']); ?></p>
            <button class="btn-action btn-outline" disabled style="cursor: not-allowed; margin-top: auto;">
                Starts in <?php echo ceil((strtotime($c['start_date']) - time()) / 86400); ?> Days
            </button>
        </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php if (mysqli_num_rows($past_result) > 0): ?>
    <h2 style="margin: var(--space-8) 0 var(--space-4); color: var(--color-gray-800); border-top: 1px solid var(--color-gray-200); padding-top: var(--space-8);">
        <i class="fas fa-history" style="color: var(--color-gray-500);"></i> Recent Results
    </h2>
    <div class="challenges-grid">
        <?php while($c = mysqli_fetch_assoc($past_result)): ?>
        <div class="challenge-card">
            <div class="challenge-header">
                <h3 class="challenge-title" style="color: var(--color-gray-600);"><?php echo htmlspecialchars($c['title']); ?></h3>
                <span style="font-size: 0.75rem; font-weight: 700; color: var(--color-gray-600); background: var(--color-gray-200); padding: 2px 8px; border-radius: 4px;">ENDED</span>
            </div>
            <div class="meta-row">
                <i class="fas fa-flag-checkered" style="color: var(--color-gray-400);"></i> Ended <?php echo date('M d', strtotime($c['end_date'])); ?>
            </div>
            <?php if($c['my_points'] > 0): ?>
                <div style="font-size: 0.9rem; color: var(--color-primary); font-weight: 600; margin-top: var(--space-2);">
                    🏆 You earned <?php echo number_format($c['my_points']); ?> pts
                </div>
            <?php else: ?>
                <div style="font-size: 0.9rem; color: var(--color-gray-500); margin-top: var(--space-2);">
                    Did not participate
                </div>
            <?php endif; ?>
            <a href="?id=<?php echo $c['challenge_id']; ?>" class="btn-action btn-outline" style="margin-top: var(--space-4);">
                See Winners
            </a>
        </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php 
} // End Else (List View)
include('includes/footer.php'); 
?>