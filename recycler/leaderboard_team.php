<?php
include('../php/config.php');
include('includes/header.php');

$conn = getDBConnection();
$current_user_id = $_SESSION['user_id'];

$user_team_query = "SELECT team_id FROM user WHERE user_id = $current_user_id";
$user_team_result = mysqli_query($conn, $user_team_query);
$my_team_id = ($row = mysqli_fetch_assoc($user_team_result)) ? $row['team_id'] : 0;

$challenge_id = isset($_GET['challenge_id']) ? intval($_GET['challenge_id']) : 0;

if ($challenge_id > 0) {
    $query = "SELECT t.team_id, t.team_name, 
              COALESCE(SUM(uc.challenge_point), 0) as display_points, 
              COUNT(DISTINCT u.user_id) as member_count
              FROM team t 
              JOIN user u ON t.team_id = u.team_id 
              LEFT JOIN user_challenge uc ON u.user_id = uc.user_id AND uc.challenge_id = $challenge_id
              GROUP BY t.team_id 
              HAVING display_points > 0
              ORDER BY display_points DESC";
} else {
    $query = "SELECT t.team_id, t.team_name, 
              (SELECT COUNT(*) FROM user WHERE team_id = t.team_id) as member_count,
              (SELECT COALESCE(SUM(lifetime_points), 0) FROM user WHERE team_id = t.team_id) as display_points
              FROM team t 
              ORDER BY display_points DESC";
}

$rank_result = mysqli_query($conn, $query);
$teams = [];
if ($rank_result) {
    while ($row = mysqli_fetch_assoc($rank_result)) {
        $teams[] = $row;
    }
}

$challenges = mysqli_query($conn, "SELECT challenge_id, title, end_date FROM challenge ORDER BY end_date DESC");
?>

<style>
    @media (max-width: 768px) {
        .page-header-mobile {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 1rem;
        }

        .page-header-mobile .back-btn-mobile {
            order: -1;
            margin-bottom: 0.5rem;
        }

        .page-header-mobile .filter-mobile {
            width: 100%;
        }
    }
</style>

<div class="page-header page-header-mobile"
    style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: var(--space-8);">
    <div>
        <h1 class="page-title">Team Leaderboard</h1>
        <p class="page-description">Collaboration is key. See which teams are making the biggest impact.</p>
    </div>
    <div style="display: flex; align-items: center; gap: 1rem;">
        <div class="filter-mobile"
            style="background: var(--color-gray-100); padding: var(--space-2); border-radius: var(--radius-md);">
            <form method="GET">
                <select name="challenge_id" onchange="this.form.submit()"
                    style="padding: var(--space-2) var(--space-4); border-radius: var(--radius-sm); border: 1px solid var(--color-gray-300); background: white; font-weight: 500; color: var(--color-gray-700); cursor: pointer; min-width: 200px;">
                    <option value="0">🏆 Overall Lifetime Rankings</option>
                    <?php while ($c = mysqli_fetch_assoc($challenges)): ?>
                        <option value="<?php echo $c['challenge_id']; ?>" 
                            <?php echo ($challenge_id == $c['challenge_id']) ? 'selected' : ''; ?>>
                            Event: <?php echo htmlspecialchars($c['title']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>
        <a href="leaderboard.php" class="back-btn-mobile"
            style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: white; color: var(--color-gray-700); text-decoration: none; border-radius: 8px; border: 1px solid var(--color-gray-200); font-weight: 500; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05); white-space: nowrap;"
            onmouseover="this.style.background='var(--color-gray-50)'; this.style.borderColor='var(--color-gray-300)';"
            onmouseout="this.style.background='white'; this.style.borderColor='var(--color-gray-200)';">
            <i class="fas fa-arrow-left"></i>
            <span>Back</span>
        </a>
    </div>
</div>

<?php if (count($teams) > 0): ?>

    <div class="card"
        style="padding: 0; overflow: hidden; border-radius: var(--radius-lg); box-shadow: var(--shadow-md); background: white;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background: var(--color-gray-50); color: var(--color-gray-600);">
                <tr>
                    <th style="padding: var(--space-4); border-bottom: 1px solid var(--color-gray-200); width: 80px;">Rank
                    </th>
                    <th style="padding: var(--space-4); border-bottom: 1px solid var(--color-gray-200);">Team Name</th>
                    <th style="padding: var(--space-4); border-bottom: 1px solid var(--color-gray-200);">Members</th>
                    <th style="padding: var(--space-4); border-bottom: 1px solid var(--color-gray-200); text-align: right;">
                        Total Points</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $i < count($teams); $i++):
                    $team = $teams[$i];
                    $is_my_team = ($team['team_id'] == $my_team_id);
                    $rank = $i + 1;
                    ?>
                    <tr
                        style="border-bottom: 1px solid var(--color-gray-100); <?php echo $is_my_team ? 'background: #eff6ff;' : ''; ?>">
                        <td style="padding: var(--space-4); font-weight: 700; color: var(--color-gray-400);">
                            <?php
                            if ($rank == 1)
                                echo '🥇';
                            elseif ($rank == 2)
                                echo '🥈';
                            elseif ($rank == 3)
                                echo '🥉';
                            else
                                echo "#" . $rank;
                            ?>
                        </td>
                        <td style="padding: var(--space-4);">
                            <div style="display: flex; align-items: center; gap: var(--space-3);">
                                <div
                                    style="width: 32px; height: 32px; background: var(--color-gray-100); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: var(--color-gray-500); font-size: 0.8rem;">
                                    <?php echo strtoupper(substr($team['team_name'], 0, 1)); ?>
                                </div>
                                <span
                                    style="font-weight: 600; color: var(--color-gray-800);"><?php echo htmlspecialchars($team['team_name']); ?></span>
                                <?php if ($is_my_team): ?>
                                    <span class="badge"
                                        style="background: var(--color-primary); color: white; padding: 2px 6px; font-size: 10px; border-radius: 4px;">YOUR
                                        TEAM</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td style="padding: var(--space-4);">
                            <span
                                style="background: var(--color-gray-100); color: var(--color-gray-600); padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                <i class="fas fa-user-friends" style="margin-right: 4px;"></i>
                                <?php echo number_format($team['member_count']); ?>
                            </span>
                        </td>
                        <td style="padding: var(--space-4); text-align: right;">
                            <span
                                style="background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 999px; font-weight: 700; font-size: 14px;">
                                <?php echo number_format($team['display_points']); ?> pts
                            </span>
                        </td>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>

<?php else: ?>
    <div class="card text-center" style="padding: var(--space-12); background: white;">
        <div style="font-size: 4rem; margin-bottom: var(--space-4); opacity: 0.5;">🛡️</div>
        <h3>No Teams Found</h3>
        <p style="color: var(--color-gray-600);">There are no teams participating in this ranking yet.</p>
        <?php if ($my_team_id == 0): ?>
            <a href="teams.php" class="btn btn-primary"
                style="margin-top: var(--space-4); display: inline-block; text-decoration: none;">Join a Team</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php include('includes/footer.php'); ?>