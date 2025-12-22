<?php
include('../php/config.php');
include('includes/header.php');

$conn = getDBConnection();
$challenge_id = isset($_GET['challenge_id']) ? intval($_GET['challenge_id']) : 0;

// Fetch Rankings
$query = ($challenge_id > 0) 
    ? "SELECT t.team_id, t.team_name, SUM(uc.challenge_point) as display_points, (SELECT COUNT(*) FROM user WHERE team_id = t.team_id) as member_count
       FROM team t JOIN user u ON t.team_id = u.team_id JOIN user_challenge uc ON u.user_id = uc.user_id
       WHERE uc.challenge_id = $challenge_id GROUP BY t.team_id ORDER BY display_points DESC"
    : "SELECT team_id, team_name, points as display_points, (SELECT COUNT(*) FROM user WHERE team_id = t.team_id) as member_count
       FROM team t ORDER BY display_points DESC";

$rank_result = mysqli_query($conn, $query);
$teams = mysqli_fetch_all($rank_result, MYSQLI_ASSOC);

// Challenges for filter
$challenges = mysqli_query($conn, "SELECT challenge_id, title FROM challenge ORDER BY end_date DESC");
?>

<div class="page-header" style="margin-bottom: var(--space-8);">
    <h1 class="page-title">Team Leaderboard</h1>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: var(--space-2);">
        <p class="page-description">Collaboration is key to sustainability. See which teams are leading the way.</p>
        <form method="GET">
            <select name="challenge_id" onchange="this.form.submit()" style="padding: var(--space-2) var(--space-4); border-radius: var(--radius-md); border: 1px solid var(--color-gray-200); font-family: var(--font-sans);">
                <option value="0">Overall Rankings</option>
                <?php while($c = mysqli_fetch_assoc($challenges)): ?>
                    <option value="<?php echo $c['challenge_id']; ?>" <?php echo ($challenge_id == $c['challenge_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['title']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--space-6);">
    <?php for($i = 0; $i < min(3, count($teams)); $i++): 
        $t = $teams[$i];
    ?>
    <div class="card" style="position: relative; overflow: hidden; border-left: 6px solid var(--color-secondary);">
        <div style="position: absolute; right: -10px; top: -10px; font-size: 5rem; opacity: 0.1; font-weight: 900;"><?php echo $i+1; ?></div>
        <div style="display: flex; align-items: center; gap: var(--space-4);">
            <div style="background: var(--color-gray-50); width: 50px; height: 50px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                <?php echo ($i == 0) ? '🏆' : '🥈'; ?>
            </div>
            <div>
                <h3 style="margin: 0;"><?php echo htmlspecialchars($t['team_name']); ?></h3>
                <span style="font-size: var(--text-xs); color: var(--color-gray-500);"><?php echo $t['member_count']; ?> Members</span>
            </div>
        </div>
        <div style="margin-top: var(--space-4); display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: var(--text-sm); color: var(--color-gray-600);">Total Impact</span>
            <span style="font-size: var(--text-xl); font-weight: 800; color: var(--color-primary);"><?php echo number_format($t['display_points']); ?> pts</span>
        </div>
    </div>
    <?php endfor; ?>
</div>

<div class="card" style="margin-top: var(--space-8); padding: 0; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse;">
        <thead style="background: var(--color-gray-50); text-align: left;">
            <tr>
                <th style="padding: var(--space-4); width: 80px;">Rank</th>
                <th style="padding: var(--space-4);">Team</th>
                <th style="padding: var(--space-4); text-align: right;">Points</th>
            </tr>
        </thead>
        <tbody>
            <?php for($i = 3; $i < count($teams); $i++): $t = $teams[$i]; ?>
            <tr style="border-bottom: 1px solid var(--color-gray-100);">
                <td style="padding: var(--space-4); color: var(--color-gray-400); font-weight: 700;">#<?php echo $i+1; ?></td>
                <td style="padding: var(--space-4);">
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($t['team_name']); ?></div>
                    <div style="font-size: var(--text-xs); color: var(--color-gray-500);"><?php echo $t['member_count']; ?> contributors</div>
                </td>
                <td style="padding: var(--space-4); text-align: right; font-weight: 700; color: var(--color-primary);">
                    <?php echo number_format($t['display_points']); ?>
                </td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>
</div>

<?php include('includes/footer.php'); ?>