<?php
session_start();
require_once '../php/config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'recycler') {
    header('Location: ../login.php');
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];
$message = '';

// Handle Join Challenge Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_challenge_id'])) {
    $challenge_id = intval($_POST['join_challenge_id']);

    // Check if already joined
    $check = $conn->prepare("SELECT 1 FROM user_challenge WHERE user_id = ? AND challenge_id = ?");
    $check->bind_param("ii", $user_id, $challenge_id);
    $check->execute();
    if ($check->get_result()->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO user_challenge (user_id, challenge_id, challenge_point, is_completed) VALUES (?, ?, 0, 0)");
        $stmt->bind_param("ii", $user_id, $challenge_id);
        if ($stmt->execute()) {
            $message = "You have successfully joined the challenge!";
        } else {
            $message = "Error joining challenge.";
        }
        $stmt->close();
    }
    $check->close();
}

$my_sql = "SELECT c.*, uc.challenge_point, uc.is_completed, b.badge_name, b.point_required AS target_points, r.reward_name, m.material_name 
           FROM user_challenge uc
           JOIN challenge c ON uc.challenge_id = c.challenge_id
           LEFT JOIN badge b ON c.badge_id = b.badge_id
           LEFT JOIN reward r ON c.reward_id = r.reward_id
           LEFT JOIN material m ON c.target_material_id = m.material_id
           WHERE uc.user_id = ? AND c.end_date >= CURDATE()
           ORDER BY c.end_date ASC";
$stmt = $conn->prepare($my_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$my_challenges = $stmt->get_result();
$stmt->close();

// Fetch Available Challenges (Not Joined)
$avail_sql = "SELECT c.*, b.badge_name, r.reward_name, m.material_name
              FROM challenge c
              LEFT JOIN badge b ON c.badge_id = b.badge_id
              LEFT JOIN reward r ON c.reward_id = r.reward_id
              LEFT JOIN material m ON c.target_material_id = m.material_id
              WHERE c.start_date <= CURDATE() AND c.end_date >= CURDATE()
              AND c.challenge_id NOT IN (SELECT challenge_id FROM user_challenge WHERE user_id = ?)
              ORDER BY c.start_date DESC";
$stmt = $conn->prepare($avail_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$available_challenges = $stmt->get_result();
$stmt->close();

$page_title = 'Challenges';
require_once 'includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">Challenges</h1>
    <p class="page-description">Join challenges to earn bonus points and badges!</p>
</div>

<?php if ($message): ?>
    <div
        style="background: #ECFDF5; color: #065F46; padding: var(--space-4); border-radius: var(--radius-md); margin-bottom: var(--space-6); border: 1px solid #A7F3D0;">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- My Challenges Section -->
<h2
    style="font-size: var(--text-xl); color: var(--color-gray-800); margin-bottom: var(--space-4); display: flex; align-items: center; gap: var(--space-2);">
    <i class="fas fa-fire" style="color: var(--color-primary);"></i> My Active Challenges
</h2>

<div
    style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: var(--space-6); margin-bottom: var(--space-8);">
    <?php if ($my_challenges->num_rows > 0): ?>
        <?php while ($row = $my_challenges->fetch_assoc()): ?>
            <div class="card"
                style="background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); padding: var(--space-6); border-top: 4px solid var(--color-primary);">
                <div
                    style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                    <h3 style="margin: 0; font-size: var(--text-lg); color: var(--color-gray-900);">
                        <?php echo htmlspecialchars($row['title']); ?>
                    </h3>
                    <span
                        style="background: var(--color-primary-light); color: white; padding: 2px 8px; border-radius: var(--radius-full); font-size: 11px; font-weight: 700;">
                        x<?php echo $row['point_multiplier']; ?>
                    </span>
                </div>

                <p
                    style="color: var(--color-gray-600); font-size: var(--text-sm); margin-bottom: var(--space-4); line-height: 1.5;">
                    <?php echo htmlspecialchars($row['description']); ?>
                </p>

                <div style="margin-bottom: var(--space-4);">
                    <div
                        style="display: flex; justify-content: space-between; font-size: var(--text-xs); color: var(--color-gray-500); margin-bottom: 4px;">
                        <span>Progress</span>
                        <span><?php echo $row['challenge_point']; ?> / <?php echo $row['target_points']; ?></span>
                    </div>
                    <?php
                    $target = $row['target_points'] > 0 ? $row['target_points'] : 100; // Default to 100 if null/zero
                    $percent = min(100, ($row['challenge_point'] / $target) * 100);
                    ?>
                    <div style="background: var(--color-gray-200); height: 8px; border-radius: 4px; overflow: hidden;">
                        <div style="background: var(--color-primary); width: <?php echo $percent; ?>%; height: 100%;"></div>
                    </div>
                </div>

                <div style="display: flex; gap: var(--space-4); font-size: var(--text-xs); color: var(--color-gray-600);">
                    <?php if ($row['material_name']): ?>
                        <div style="display: flex; align-items: center; gap: 4px;">
                            <i class="fas fa-recycle" style="color: var(--color-success);"></i>
                            <?php echo htmlspecialchars($row['material_name']); ?>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; align-items: center; gap: 4px;">
                            <i class="fas fa-globe" style="color: var(--color-info);"></i> All Materials
                        </div>
                    <?php endif; ?>

                    <div style="display: flex; align-items: center; gap: 4px;">
                        <i class="fas fa-clock" style="color: var(--color-warning);"></i> Ends
                        <?php echo date('M d', strtotime($row['end_date'])); ?>
                    </div>
                </div>

                <?php if ($row['reward_name']): ?>
                    <div
                        style="margin-top: var(--space-4); padding-top: var(--space-3); border-top: 1px solid var(--color-gray-100); font-size: var(--text-sm); font-weight: 500; color: var(--color-secondary);">
                        <i class="fas fa-gift"></i> Reward: <?php echo htmlspecialchars($row['reward_name']); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div
            style="grid-column: 1/-1; text-align: center; padding: var(--space-8); background: var(--color-gray-50); border-radius: var(--radius-lg); color: var(--color-gray-500);">
            You haven't joined any challenges yet. Check out the available ones below!
        </div>
    <?php endif; ?>
</div>

<!-- Available Challenges Section -->
<h2
    style="font-size: var(--text-xl); color: var(--color-gray-800); margin-bottom: var(--space-4); display: flex; align-items: center; gap: var(--space-2);">
    <i class="fas fa-plus-circle" style="color: var(--color-success);"></i> Available Challenges
</h2>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: var(--space-6);">
    <?php if ($available_challenges->num_rows > 0): ?>
        <?php while ($row = $available_challenges->fetch_assoc()): ?>
            <div class="card"
                style="background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); padding: var(--space-6);">
                <div
                    style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                    <h3 style="margin: 0; font-size: var(--text-lg); color: var(--color-gray-900);">
                        <?php echo htmlspecialchars($row['title']); ?>
                    </h3>
                    <span
                        style="background: var(--color-gray-100); color: var(--color-gray-600); padding: 2px 8px; border-radius: var(--radius-full); font-size: 11px; font-weight: 700;">
                        x<?php echo $row['point_multiplier']; ?>
                    </span>
                </div>

                <p
                    style="color: var(--color-gray-600); font-size: var(--text-sm); margin-bottom: var(--space-4); line-height: 1.5;">
                    <?php echo htmlspecialchars($row['description']); ?>
                </p>

                <div
                    style="display: flex; gap: var(--space-4); font-size: var(--text-xs); color: var(--color-gray-600); margin-bottom: var(--space-6);">
                    <?php if ($row['material_name']): ?>
                        <div style="display: flex; align-items: center; gap: 4px;">
                            <i class="fas fa-recycle"></i> <?php echo htmlspecialchars($row['material_name']); ?>
                        </div>
                    <?php endif; ?>
                    <div style="display: flex; align-items: center; gap: 4px;">
                        <i class="fas fa-calendar-alt"></i> Ends <?php echo date('M d', strtotime($row['end_date'])); ?>
                    </div>
                </div>

                <form method="POST">
                    <input type="hidden" name="join_challenge_id" value="<?php echo $row['challenge_id']; ?>">
                    <button type="submit"
                        style="width: 100%; padding: var(--space-3); background: var(--color-success); color: white; border: none; border-radius: var(--radius-md); font-weight: 600; cursor: pointer; transition: background 0.3s; display: flex; align-items: center; justify-content: center; gap: var(--space-2);">
                        Join Challenge <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div
            style="grid-column: 1/-1; text-align: center; padding: var(--space-8); background: var(--color-gray-50); border-radius: var(--radius-lg); color: var(--color-gray-500);">
            No new challenges available right now. Come back later!
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'includes/footer.php';
$conn->close();
?>