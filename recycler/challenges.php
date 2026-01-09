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
        $stmt = $conn->prepare("INSERT INTO user_challenge (user_id, challenge_id, challenge_point, challenge_quantity, is_completed) VALUES (?, ?, 0, 0, 0)");
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

$my_sql = "SELECT c.*, uc.challenge_point, uc.challenge_quantity, uc.is_completed, b.badge_name, r.reward_name, m.material_name
           FROM user_challenge uc
           JOIN challenge c ON uc.challenge_id = c.challenge_id
           LEFT JOIN badge b ON c.badge_id = b.badge_id
           LEFT JOIN reward r ON c.reward_id = r.reward_id
           LEFT JOIN material m ON c.target_material_id = m.material_id
           WHERE uc.user_id = ? AND c.end_date >= CURDATE() AND uc.is_completed = 0
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

// Fetch Past Challenges (Completed or Expired)
$past_sql = "SELECT c.*, uc.challenge_point, uc.challenge_quantity, uc.is_completed, b.badge_name, r.reward_name, m.material_name
             FROM user_challenge uc
             JOIN challenge c ON uc.challenge_id = c.challenge_id
             LEFT JOIN badge b ON c.badge_id = b.badge_id
             LEFT JOIN reward r ON c.reward_id = r.reward_id
             LEFT JOIN material m ON c.target_material_id = m.material_id
             WHERE uc.user_id = ? AND (uc.is_completed = 1 OR c.end_date < CURDATE())
             ORDER BY c.end_date DESC";
$stmt = $conn->prepare($past_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$past_challenges = $stmt->get_result();
$stmt->close();

$page_title = 'Challenges';
require_once 'includes/header.php';
?>

<link rel="stylesheet" href="../css/challenges.css">


<div class="challenges-hero">
    <h1>🏆 Challenges</h1>
    <p>Join challenges to earn bonus points, unlock badges, and make a bigger impact!</p>
</div>

<?php if ($message): ?>
    <div class="success-message">
        <i class="fas fa-check-circle" style="font-size: 1.5rem;"></i>
        <span><?php echo htmlspecialchars($message); ?></span>
    </div>
<?php endif; ?>

<!-- My Challenges Section -->
<h2 class="section-header">
    <i class="fas fa-fire"></i> My Active Challenges
</h2>

<div class="challenges-grid">
    <?php if ($my_challenges->num_rows > 0): ?>
        <?php while ($row = $my_challenges->fetch_assoc()): ?>
            <div class="challenge-card challenge-card-active">
                <div class="challenge-header">
                    <h3 class="challenge-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                    <span class="multiplier-badge">x<?php echo $row['point_multiplier']; ?></span>
                </div>

                <p class="challenge-description">
                    <?php echo htmlspecialchars($row['description']); ?>
                </p>

                <?php if ($row['completion_type'] == 'participation'): ?>
                    <div class="progress-container">
                        <div class="progress-info">
                            <span>Progress</span>
                            <span><strong>✓ Joined!</strong> Submit 1 item to complete</span>
                        </div>
                        <?php
                        // For participation: 0% if no submissions, 100% if at least 1 submission
                        $participation_percent = ($row['challenge_quantity'] >= 1) ? 100 : 0;
                        ?>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $participation_percent; ?>%;"></div>
                        </div>
                    </div>
                <?php elseif ($row['completion_type'] == 'quantity'): ?>
                    <div class="progress-container">
                        <div class="progress-info">
                            <span>Progress</span>
                            <span><strong><?php echo $row['challenge_quantity']; ?></strong> /
                                <?php echo $row['target_quantity']; ?> items</span>
                        </div>
                        <?php
                        $target = $row['target_quantity'] > 0 ? $row['target_quantity'] : 10;
                        $percent = min(100, ($row['challenge_quantity'] / $target) * 100);
                        ?>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $percent; ?>%;"></div>
                        </div>
                    </div>
                <?php else: // points-based ?>
                    <div class="progress-container">
                        <div class="progress-info">
                            <span>Progress</span>
                            <span><strong><?php echo $row['challenge_point']; ?></strong> / <?php echo $row['target_points']; ?>
                                pts</span>
                        </div>
                        <?php
                        $target = $row['target_points'] > 0 ? $row['target_points'] : 100;
                        $percent = min(100, ($row['challenge_point'] / $target) * 100);
                        ?>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $percent; ?>%;"></div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="challenge-meta">
                    <?php if ($row['material_name']): ?>
                        <div class="meta-item">
                            <i class="fas fa-recycle" style="color: var(--color-success);"></i>
                            <?php echo htmlspecialchars($row['material_name']); ?>
                        </div>
                    <?php else: ?>
                        <div class="meta-item">
                            <i class="fas fa-globe" style="color: var(--color-info);"></i>
                            All Materials
                        </div>
                    <?php endif; ?>

                    <div class="meta-item">
                        <i class="fas fa-clock" style="color: var(--color-warning);"></i>
                        Ends <?php echo date('M d', strtotime($row['end_date'])); ?>
                    </div>
                </div>

                <?php if ($row['reward_name'] || $row['badge_name']): ?>
                    <div class="challenge-reward">
                        <?php if ($row['badge_name']): ?>
                            <div style="margin-bottom: 4px;">
                                <i class="fas fa-medal"></i>
                                Badge: <?php echo htmlspecialchars($row['badge_name']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($row['reward_name']): ?>
                            <div>
                                <i class="fas fa-gift"></i>
                                Reward: <?php echo htmlspecialchars($row['reward_name']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-trophy"></i>
            <p>You haven't joined any challenges yet. Check out the available ones below!</p>
        </div>
    <?php endif; ?>
</div>

<!-- Available Challenges Section -->
<h2 class="section-header">
    <i class="fas fa-plus-circle" style="color: var(--color-success);"></i> Available Challenges
</h2>

<div class="challenges-grid">
    <?php if ($available_challenges->num_rows > 0): ?>
        <?php while ($row = $available_challenges->fetch_assoc()): ?>
            <div class="challenge-card">
                <div class="challenge-header">
                    <h3 class="challenge-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                    <span class="multiplier-badge" style="background: var(--color-gray-200); color: var(--color-gray-700);">
                        x<?php echo $row['point_multiplier']; ?>
                    </span>
                </div>

                <p class="challenge-description">
                    <?php echo htmlspecialchars($row['description']); ?>
                </p>

                <div class="challenge-meta">
                    <?php if ($row['material_name']): ?>
                        <div class="meta-item">
                            <i class="fas fa-recycle"></i>
                            <?php echo htmlspecialchars($row['material_name']); ?>
                        </div>
                    <?php endif; ?>
                    <div class="meta-item">
                        <i class="fas fa-calendar-alt"></i>
                        Ends <?php echo date('M d', strtotime($row['end_date'])); ?>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-check-circle"></i>
                        <?php
                        if ($row['completion_type'] == 'quantity') {
                            echo "Recycle {$row['target_quantity']} items";
                        } elseif ($row['completion_type'] == 'points') {
                            echo "Earn {$row['target_points']} pts";
                        } else {
                            echo "Just participate!";
                        }
                        ?>
                    </div>
                </div>

                <?php if ($row['reward_name'] || $row['badge_name']): ?>
                    <div class="challenge-reward">
                        <?php if ($row['badge_name']): ?>
                            <div style="margin-bottom: 4px;">
                                <i class="fas fa-medal"></i>
                                Badge: <?php echo htmlspecialchars($row['badge_name']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($row['reward_name']): ?>
                            <div>
                                <i class="fas fa-gift"></i>
                                Reward: <?php echo htmlspecialchars($row['reward_name']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" style="margin-top: var(--space-4);">
                    <input type="hidden" name="join_challenge_id" value="<?php echo $row['challenge_id']; ?>">
                    <button type="submit" class="join-button">
                        Join Challenge <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <p>No new challenges available right now. Come back later!</p>
        </div>
    <?php endif; ?>
</div>

<!-- Past Challenges Section -->
<h2 class="section-header"
    style="color: var(--color-gray-600); border-top: 1px solid var(--color-gray-200); padding-top: var(--space-8);">
    <i class="fas fa-history" style="color: var(--color-gray-500);"></i> Past Challenges
</h2>

<div class="challenges-grid">
    <?php if ($past_challenges->num_rows > 0): ?>
        <?php while ($row = $past_challenges->fetch_assoc()): ?>
            <div class="challenge-card"
                style="border-top-color: <?php echo $row['is_completed'] ? 'var(--color-success)' : 'var(--color-gray-400)'; ?>; opacity: 0.8; filter: grayscale(<?php echo $row['is_completed'] ? '0' : '0.5'; ?>);">
                <div class="challenge-header">
                    <h3 class="challenge-title" style="color: var(--color-gray-700);">
                        <?php echo htmlspecialchars($row['title']); ?>
                    </h3>
                    <?php if ($row['is_completed']): ?>
                        <span class="multiplier-badge" style="background: var(--color-success);">
                            <i class="fas fa-check"></i> Completed
                        </span>
                    <?php else: ?>
                        <span class="multiplier-badge" style="background: var(--color-gray-500);">
                            Expired
                        </span>
                    <?php endif; ?>
                </div>

                <p class="challenge-description" style="color: var(--color-gray-500);">
                    <?php echo htmlspecialchars($row['description']); ?>
                </p>

                <div class="challenge-meta">
                    <div class="meta-item">
                        <i class="fas fa-calendar-times"></i>
                        Ended <?php echo date('M d', strtotime($row['end_date'])); ?>
                    </div>
                    <?php if ($row['completion_type'] == 'points'): ?>
                        <div class="meta-item">
                            <i class="fas fa-star"></i>
                            <?php echo $row['challenge_point']; ?> / <?php echo $row['target_points']; ?> pts
                        </div>
                    <?php elseif ($row['completion_type'] == 'participation'): ?>
                        <div class="meta-item">
                            <i class="fas fa-user-check"></i>
                            Participated (<?php echo $row['challenge_quantity']; ?> items submitted)
                        </div>
                    <?php else: ?>
                        <div class="meta-item">
                            <i class="fas fa-hashtag"></i>
                            <?php echo $row['challenge_quantity']; ?> / <?php echo $row['target_quantity']; ?> items
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($row['is_completed']): ?>
                    <div class="success-message"
                        style="margin-top: var(--space-4); margin-bottom: 0; font-size: var(--text-sm); background: rgba(34, 197, 94, 0.1); color: var(--color-success);">
                        <i class="fas fa-trophy"></i>
                        Challenge Completed!
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-history"></i>
            <p>You don't have any past challenges yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'includes/footer.php';
$conn->close();
?>