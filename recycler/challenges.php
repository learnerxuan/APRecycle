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

<style>
    /* Challenge Page Specific Styles */
    .challenges-hero {
        background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
        color: white;
        padding: var(--space-10) var(--space-8);
        border-radius: var(--radius-xl);
        margin-bottom: var(--space-8);
        text-align: center;
        box-shadow: var(--shadow-lg);
    }

    .challenges-hero h1 {
        font-size: var(--text-4xl);
        margin: 0 0 var(--space-3) 0;
        font-weight: 700;
    }

    .challenges-hero p {
        font-size: var(--text-lg);
        opacity: 0.95;
        margin: 0;
    }

    .success-message {
        background: var(--color-success-light);
        color: var(--color-success);
        padding: var(--space-4);
        border-radius: var(--radius-lg);
        margin-bottom: var(--space-6);
        border-left: 4px solid var(--color-success);
        display: flex;
        align-items: center;
        gap: var(--space-3);
        animation: slideInDown 0.5s ease;
        font-weight: 600;
    }

    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .section-header {
        font-size: var(--text-2xl);
        color: var(--color-gray-800);
        margin-bottom: var(--space-6);
        display: flex;
        align-items: center;
        gap: var(--space-3);
        font-weight: 700;
    }

    .section-header i {
        color: var(--color-primary);
        font-size: var(--text-xl);
    }

    .challenges-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: var(--space-6);
        margin-bottom: var(--space-10);
    }

    .challenge-card {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        padding: var(--space-6);
        transition: all 0.3s ease;
        border-top: 4px solid var(--color-gray-300);
        position: relative;
        overflow: hidden;
    }

    .challenge-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-xl);
    }

    .challenge-card-active {
        border-top-color: var(--color-success);
    }

    .challenge-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: var(--space-4);
    }

    .challenge-title {
        margin: 0;
        font-size: var(--text-lg);
        color: var(--color-gray-900);
        font-weight: 700;
    }

    .multiplier-badge {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: white;
        padding: 6px 12px;
        border-radius: var(--radius-full);
        font-size: 12px;
        font-weight: 700;
        box-shadow: var(--shadow-sm);
        flex-shrink: 0;
    }

    .challenge-description {
        color: var(--color-gray-600);
        font-size: var(--text-sm);
        margin-bottom: var(--space-4);
        line-height: 1.6;
    }

    .progress-container {
        margin-bottom: var(--space-4);
    }

    .progress-info {
        display: flex;
        justify-content: space-between;
        font-size: var(--text-xs);
        color: var(--color-gray-500);
        margin-bottom: 6px;
        font-weight: 600;
    }

    .progress-bar {
        background: var(--color-gray-200);
        height: 10px;
        border-radius: var(--radius-full);
        overflow: hidden;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
    }

    .progress-fill {
        background: linear-gradient(90deg, var(--color-success), var(--color-secondary));
        height: 100%;
        transition: width 0.5s ease;
        border-radius: var(--radius-full);
    }

    .challenge-meta {
        display: flex;
        flex-wrap: wrap;
        gap: var(--space-2);
        font-size: var(--text-xs);
        color: var(--color-gray-600);
        margin-bottom: var(--space-4);
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 4px;
        background: var(--color-gray-50);
        padding: 6px 10px;
        border-radius: var(--radius-md);
        font-weight: 500;
    }

    .meta-item i {
        font-size: 11px;
    }

    .challenge-reward {
        margin-top: var(--space-4);
        padding-top: var(--space-3);
        border-top: 1px solid var(--color-gray-100);
        font-size: var(--text-sm);
        font-weight: 600;
        color: var(--color-secondary);
        display: flex;
        align-items: center;
        gap: var(--space-2);
    }

    .join-button {
        width: 100%;
        padding: var(--space-3) var(--space-4);
        background: linear-gradient(135deg, var(--color-success), var(--color-secondary));
        color: white;
        border: none;
        border-radius: var(--radius-md);
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--space-2);
        font-size: var(--text-sm);
        box-shadow: var(--shadow-sm);
    }

    .join-button:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .join-button:active {
        transform: translateY(0);
    }

    .empty-state {
        grid-column: 1/-1;
        text-align: center;
        padding: var(--space-10);
        background: var(--color-gray-50);
        border-radius: var(--radius-xl);
        color: var(--color-gray-500);
        border: 2px dashed var(--color-gray-300);
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: var(--space-4);
        color: var(--color-gray-400);
    }

    .empty-state p {
        font-size: var(--text-lg);
        margin: 0;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .challenges-hero h1 {
            font-size: var(--text-3xl);
        }

        .challenges-hero p {
            font-size: var(--text-base);
        }

        .challenges-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

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

                <div class="progress-container">
                    <div class="progress-info">
                        <span>Progress</span>
                        <span><strong><?php echo $row['challenge_point']; ?></strong> / <?php echo $row['target_points']; ?> pts</span>
                    </div>
                    <?php
                    $target = $row['target_points'] > 0 ? $row['target_points'] : 100;
                    $percent = min(100, ($row['challenge_point'] / $target) * 100);
                    ?>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $percent; ?>%;"></div>
                    </div>
                </div>

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

                <?php if ($row['reward_name']): ?>
                    <div class="challenge-reward">
                        <i class="fas fa-gift"></i>
                        Reward: <?php echo htmlspecialchars($row['reward_name']); ?>
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
                </div>

                <?php if ($row['reward_name']): ?>
                    <div class="challenge-reward">
                        <i class="fas fa-gift"></i>
                        Reward: <?php echo htmlspecialchars($row['reward_name']); ?>
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

<?php
require_once 'includes/footer.php';
$conn->close();
?>
