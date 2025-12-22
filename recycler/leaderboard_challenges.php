<?php
include('../php/config.php');
include('includes/header.php'); // Security: ensures role === 'recycler'

$conn = getDBConnection();

// Handle Challenge selection
$challenge_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. Fetch all challenges for the selector sidebar/dropdown
$all_challenges_query = "SELECT challenge_id, title, status FROM challenge ORDER BY end_date DESC";
// Note: 'status' might be derived from dates if not in schema. 
// Using dates comparison for logic:
$all_challenges_query = "SELECT challenge_id, title, end_date, 
                        CASE WHEN end_date >= CURRENT_DATE() THEN 'Active' ELSE 'Completed' END AS challenge_status 
                        FROM challenge ORDER BY end_date DESC";
$challenges_list = mysqli_query($conn, $all_challenges_query);

// Set default challenge if none selected
if ($challenge_id === 0 && mysqli_num_rows($challenges_list) > 0) {
    mysqli_data_seek($challenges_list, 0);
    $first = mysqli_fetch_assoc($challenges_list);
    $challenge_id = $first['challenge_id'];
    mysqli_data_seek($challenges_list, 0); // Reset for later use
}

// 2. Fetch details for the selected challenge
$details_query = "SELECT * FROM challenge WHERE challenge_id = $challenge_id";
$details_result = mysqli_query($conn, $details_query);
$challenge_info = mysqli_fetch_assoc($details_result);

// 3. Fetch rankings for this specific challenge
$rankings_query = "SELECT u.user_id, u.username, uc.challenge_point 
                   FROM user_challenge uc
                   JOIN user u ON uc.user_id = u.user_id
                   WHERE uc.challenge_id = $challenge_id
                   ORDER BY uc.challenge_point DESC LIMIT 20";
$rankings_result = mysqli_query($conn, $rankings_query);
?>

<div class="page-header">
    <h1 class="page-title">Challenge Standings</h1>
    <p class="page-description">Track performance for specific campus-wide recycling events.</p>
</div>

<div style="display: grid; grid-template-columns: 300px 1fr; gap: var(--space-6); align-items: start;">
    
    <aside class="card" style="padding: var(--space-4);">
        <h3 class="mb-4" style="font-size: var(--text-lg); border-bottom: 2px solid var(--color-gray-100); padding-bottom: var(--space-2);">Event List</h3>
        <div style="display: flex; flex-direction: column; gap: var(--space-2);">
            <?php while($list_item = mysqli_fetch_assoc($challenges_list)): ?>
                <a href="?id=<?php echo $list_item['challenge_id']; ?>" 
                   style="text-decoration: none; padding: var(--space-3); border-radius: var(--radius-md); transition: all 0.2s;
                          background: <?php echo $challenge_id == $list_item['challenge_id'] ? 'var(--color-primary-light)' : 'transparent'; ?>;
                          color: <?php echo $challenge_id == $list_item['challenge_id'] ? 'white' : 'var(--color-gray-700)'; ?>;"
                   class="challenge-link">
                    <div style="font-weight: 600; font-size: var(--text-sm);"><?php echo htmlspecialchars($list_item['title']); ?></div>
                    <div style="font-size: var(--text-xs); opacity: 0.8;">
                        <?php echo $list_item['challenge_status']; ?> • Ends <?php echo date('M d', strtotime($list_item['end_date'])); ?>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    </aside>

    <section>
        <?php if ($challenge_info): ?>
            <div class="card mb-6" style="border-left: 5px solid var(--color-accent-yellow);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <span class="badge badge-warning mb-2">Active Challenge</span>
                        <h2 class="mb-2"><?php echo htmlspecialchars($challenge_info['title']); ?></h2>
                        <p style="color: var(--color-gray-600);"><?php echo htmlspecialchars($challenge_info['description']); ?></p>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: var(--text-2xl); font-weight: 800; color: var(--color-primary);">
                            x<?php echo $challenge_info['point_multiplier']; ?>
                        </div>
                        <div style="font-size: var(--text-xs); color: var(--color-gray-500); text-transform: uppercase; letter-spacing: 1px;">
                            Point Multiplier
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" style="padding: 0; overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: var(--color-gray-50); color: var(--color-gray-600); text-align: left;">
                        <tr>
                            <th style="padding: var(--space-4);">Rank</th>
                            <th style="padding: var(--space-4);">Participant</th>
                            <th style="padding: var(--space-4); text-align: right;">Challenge Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rank = 1;
                        while($row = mysqli_fetch_assoc($rankings_result)): 
                            $is_me = ($row['user_id'] == $_SESSION['user_id']);
                        ?>
                        <tr style="border-bottom: 1px solid var(--color-gray-100); <?php echo $is_me ? 'background: var(--color-success-light);' : ''; ?>">
                            <td style="padding: var(--space-4); font-weight: 700;">#<?php echo $rank; ?></td>
                            <td style="padding: var(--space-4);">
                                <div style="display: flex; align-items: center; gap: var(--space-3);">
                                    <div class="recycler-user-avatar" style="width: 28px; height: 28px; font-size: 10px;">
                                        <?php echo strtoupper(substr($row['username'], 0, 1)); ?>
                                    </div>
                                    <span style="font-weight: 500;"><?php echo htmlspecialchars($row['username']); ?></span>
                                </div>
                            </td>
                            <td style="padding: var(--space-4); text-align: right; font-weight: 800; color: var(--color-secondary);">
                                <?php echo number_format($row['challenge_point']); ?>
                            </td>
                        </tr>
                        <?php $rank++; endwhile; ?>
                        
                        <?php if (mysqli_num_rows($rankings_result) == 0): ?>
                        <tr>
                            <td colspan="3" style="padding: var(--space-10); text-align: center; color: var(--color-gray-400);">
                                No one has joined this challenge yet. Be the first!
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="card text-center" style="padding: var(--space-12);">
                <div style="font-size: 4rem;">🔍</div>
                <h3>No Challenge Selected</h3>
                <p>Please select a challenge from the list on the left to view the standings.</p>
            </div>
        <?php endif; ?>
    </section>
</div>

<div style="margin-top: var(--space-8); text-align: center;">
    <a href="leaderboard.php" class="btn btn-secondary">Back to Hub</a>
</div>

<?php include('includes/footer.php'); ?>