<?php
session_start();
require_once '../php/config.php';

// Check if user is logged in and is a recycler
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'recycler') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$conn = getDBConnection();

// Get user's total points
$user_query = "SELECT lifetime_points FROM user WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($user_result);
$total_points = $user_data['lifetime_points'];

// Get earned badges
$badges_query = "SELECT b.badge_id, b.badge_name, b.description,
                 ub.date_awarded
                 FROM user_badge ub
                 JOIN badge b ON ub.badge_id = b.badge_id
                 WHERE ub.user_id = ?
                 ORDER BY ub.date_awarded DESC";

$stmt = mysqli_prepare($conn, $badges_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$badges_result = mysqli_stmt_get_result($stmt);

$earned_badges = [];
while ($row = mysqli_fetch_assoc($badges_result)) {
    $earned_badges[] = $row;
}

// Get available badges (not yet earned)
$available_badges_query = "SELECT b.badge_id, b.badge_name, b.description
                           FROM badge b
                           WHERE b.badge_id NOT IN (
                               SELECT badge_id FROM user_badge WHERE user_id = ?
                           )
                           ORDER BY b.badge_name ASC";

$stmt = mysqli_prepare($conn, $available_badges_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$available_badges_result = mysqli_stmt_get_result($stmt);

$available_badges = [];
while ($row = mysqli_fetch_assoc($available_badges_result)) {
    $available_badges[] = $row;
}

// Get unclaimed rewards (earned but not claimed yet)
$unclaimed_rewards_query = "SELECT r.reward_id, r.reward_name, r.description,
                            ur.date_earned
                            FROM user_reward ur
                            JOIN reward r ON ur.reward_id = r.reward_id
                            WHERE ur.user_id = ? AND ur.is_claimed = 0
                            ORDER BY ur.date_earned DESC";

$stmt = mysqli_prepare($conn, $unclaimed_rewards_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$unclaimed_rewards_result = mysqli_stmt_get_result($stmt);

$unclaimed_rewards = [];
while ($row = mysqli_fetch_assoc($unclaimed_rewards_result)) {
    $unclaimed_rewards[] = $row;
}

// Get claimed rewards
$claimed_rewards_query = "SELECT r.reward_id, r.reward_name, r.description,
                          ur.date_earned
                          FROM user_reward ur
                          JOIN reward r ON ur.reward_id = r.reward_id
                          WHERE ur.user_id = ? AND ur.is_claimed = 1
                          ORDER BY ur.date_earned DESC";

$stmt = mysqli_prepare($conn, $claimed_rewards_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$claimed_rewards_result = mysqli_stmt_get_result($stmt);

$claimed_rewards = [];
while ($row = mysqli_fetch_assoc($claimed_rewards_result)) {
    $claimed_rewards[] = $row;
}

// Available rewards are those not in user_reward table at all
$available_rewards_query = "SELECT r.reward_id, r.reward_name, r.description
                            FROM reward r
                            WHERE r.reward_id NOT IN (
                                SELECT reward_id FROM user_reward WHERE user_id = ?
                            )
                            ORDER BY r.reward_name ASC";

$stmt = mysqli_prepare($conn, $available_rewards_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$available_rewards_result = mysqli_stmt_get_result($stmt);

$available_rewards = [];
while ($row = mysqli_fetch_assoc($available_rewards_result)) {
    $available_rewards[] = $row;
}

// Badge icons mapping
$badge_icons = [
    'First Recycler' => '🌱',
    'Streak Master' => '🔥',
    'Eco Warrior' => '🏆',
    'Carbon Crusher' => '💨',
    'Team Player' => '👥',
    'Dedication Award' => '⭐',
    'Plastic Buster' => '♻️',
    'Paper Champion' => '📄',
    'Metal Master' => '🥫',
    'Community Leader' => '👑'
];

$page_title = "My Achievements";
include 'includes/header.php';
?>

<style>
    .achievements-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .stats-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: var(--space-8);
        border-radius: var(--radius-lg);
        margin-bottom: var(--space-6);
        text-align: center;
        box-shadow: var(--shadow-lg);
    }

    .stats-value {
        font-size: var(--text-4xl);
        font-weight: 700;
        margin-bottom: var(--space-2);
    }

    .stats-label {
        font-size: var(--text-lg);
        opacity: 0.9;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--space-4);
        margin-top: var(--space-4);
    }

    .stat-item {
        background: rgba(255, 255, 255, 0.2);
        padding: var(--space-4);
        border-radius: var(--radius-md);
        text-align: center;
    }

    .stat-item-value {
        font-size: var(--text-2xl);
        font-weight: 700;
    }

    .tabs {
        display: flex;
        gap: var(--space-2);
        margin-bottom: var(--space-6);
        border-bottom: 2px solid var(--color-gray-200);
        flex-wrap: wrap;
    }

    .tab {
        padding: var(--space-3) var(--space-6);
        background: none;
        border: none;
        border-bottom: 3px solid transparent;
        font-weight: 600;
        font-size: var(--text-base);
        color: var(--color-gray-600);
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        bottom: -2px;
    }

    .tab:hover {
        color: var(--color-primary);
    }

    .tab.active {
        color: var(--color-primary);
        border-bottom-color: var(--color-primary);
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .section-title {
        font-size: var(--text-xl);
        font-weight: 700;
        margin-bottom: var(--space-4);
        display: flex;
        align-items: center;
        gap: var(--space-2);
        color: var(--color-gray-800);
    }

    .section-subtitle {
        color: var(--color-gray-600);
        font-size: var(--text-sm);
        margin-bottom: var(--space-6);
    }

    .items-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: var(--space-4);
        margin-bottom: var(--space-8);
    }

    .badge-card,
    .reward-card {
        background: white;
        border-radius: var(--radius-lg);
        padding: var(--space-6);
        box-shadow: var(--shadow-md);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .badge-card:hover,
    .reward-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .badge-card.earned {
        border: 2px solid var(--color-success);
    }

    .badge-card.locked {
        opacity: 0.6;
        border: 2px dashed var(--color-gray-300);
    }

    .reward-card.earned {
        border: 2px solid var(--color-warning);
    }

    .reward-card.locked {
        opacity: 0.6;
        border: 2px dashed var(--color-gray-300);
    }

    .item-icon {
        font-size: 4rem;
        text-align: center;
        margin-bottom: var(--space-3);
    }

    .item-icon.locked {
        filter: grayscale(100%);
        opacity: 0.5;
    }

    .earned-badge {
        position: absolute;
        top: var(--space-3);
        right: var(--space-3);
        background: var(--color-success);
        color: white;
        padding: var(--space-1) var(--space-3);
        border-radius: var(--radius-full);
        font-size: var(--text-xs);
        font-weight: 700;
    }

    .locked-badge {
        position: absolute;
        top: var(--space-3);
        right: var(--space-3);
        background: var(--color-gray-400);
        color: white;
        padding: var(--space-1) var(--space-3);
        border-radius: var(--radius-full);
        font-size: var(--text-xs);
        font-weight: 700;
    }

    .item-name {
        font-size: var(--text-lg);
        font-weight: 700;
        color: var(--color-gray-800);
        margin-bottom: var(--space-2);
        text-align: center;
    }

    .item-description {
        font-size: var(--text-sm);
        color: var(--color-gray-600);
        line-height: 1.5;
        margin-bottom: var(--space-4);
        text-align: center;
    }

    .item-footer {
        display: flex;
        justify-content: center;
        align-items: center;
        padding-top: var(--space-3);
        border-top: 1px solid var(--color-gray-200);
    }

    .date-earned {
        font-size: var(--text-sm);
        color: var(--color-gray-600);
        font-weight: 500;
    }

    .empty-state {
        text-align: center;
        padding: var(--space-12);
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
    }

    .empty-icon {
        font-size: 4rem;
        color: var(--color-gray-300);
        margin-bottom: var(--space-4);
    }

    .empty-title {
        font-size: var(--text-xl);
        font-weight: 700;
        color: var(--color-gray-600);
        margin-bottom: var(--space-2);
    }

    .empty-text {
        color: var(--color-gray-500);
    }

    .claim-button {
        width: 100%;
        padding: var(--space-3) var(--space-4);
        background: linear-gradient(135deg, #FFD93D, #FFA500);
        color: #1A202C;
        border: none;
        border-radius: var(--radius-md);
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--space-2);
        font-size: var(--text-base);
        line-height: 1;
        box-shadow: 0 2px 8px rgba(255, 217, 61, 0.3);
    }

    .claim-button i {
        font-size: var(--text-base);
        line-height: 1;
    }

    .claim-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 217, 61, 0.5);
        background: linear-gradient(135deg, #FFA500, #FFD93D);
    }

    .claim-button:active {
        transform: translateY(0);
    }

    @media (max-width: 768px) {
        .items-grid {
            grid-template-columns: 1fr;
        }

        .stats-header {
            padding: var(--space-6);
        }

        .stats-value {
            font-size: var(--text-3xl);
        }

        .tabs {
            overflow-x: auto;
        }

        .tab {
            white-space: nowrap;
        }
    }
</style>

<div class="achievements-container">
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['claim_success'])): ?>
        <div
            style="background: var(--color-success-light); color: var(--color-success); padding: var(--space-4); border-radius: var(--radius-lg); margin-bottom: var(--space-6); border-left: 4px solid var(--color-success); display: flex; align-items: center; gap: var(--space-3); font-weight: 600;">
            <i class="fas fa-check-circle" style="font-size: 1.5rem;"></i>
            <span><?php echo htmlspecialchars($_SESSION['claim_success']);
            unset($_SESSION['claim_success']); ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['claim_error'])): ?>
        <div
            style="background: var(--color-error-light); color: var(--color-error); padding: var(--space-4); border-radius: var(--radius-lg); margin-bottom: var(--space-6); border-left: 4px solid var(--color-error); display: flex; align-items: center; gap: var(--space-3); font-weight: 600;">
            <i class="fas fa-exclamation-circle" style="font-size: 1.5rem;"></i>
            <span><?php echo htmlspecialchars($_SESSION['claim_error']);
            unset($_SESSION['claim_error']); ?></span>
        </div>
    <?php endif; ?>

    <!-- Stats Header -->
    <div class="stats-header">
        <div class="stats-value"><?php echo number_format($total_points); ?> Points</div>
        <div class="stats-label">Total Lifetime Points</div>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-item-value"><?php echo count($earned_badges); ?></div>
                <div>Badges Earned</div>
            </div>
            <div class="stat-item">
                <div class="stat-item-value"><?php echo count($unclaimed_rewards); ?></div>
                <div>Rewards to Claim</div>
            </div>
            <div class="stat-item">
                <div class="stat-item-value"><?php echo count($claimed_rewards); ?></div>
                <div>Rewards Claimed</div>
            </div>
            <div class="stat-item">
                <div class="stat-item-value"><?php echo count($available_badges); ?></div>
                <div>Badges to Unlock</div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <button class="tab active" onclick="showTab('badges')">
            <i class="fas fa-trophy"></i> Badges
        </button>
        <button class="tab" onclick="showTab('rewards')">
            <i class="fas fa-gift"></i> Rewards
        </button>
    </div>

    <!-- Badges Tab -->
    <div id="badges-tab" class="tab-content active">
        <!-- Earned Badges -->
        <?php if (!empty($earned_badges)): ?>
            <div class="section-title">
                <i class="fas fa-star"></i> My Badges (<?php echo count($earned_badges); ?>)
            </div>
            <p class="section-subtitle">Badges you've earned through your recycling efforts</p>

            <div class="items-grid">
                <?php foreach ($earned_badges as $badge): ?>
                    <div class="badge-card earned">
                        <span class="earned-badge">✓ Earned</span>
                        <div class="item-icon">
                            <?php echo $badge_icons[$badge['badge_name']] ?? '🏅'; ?>
                        </div>
                        <div class="item-name"><?php echo htmlspecialchars($badge['badge_name']); ?></div>
                        <div class="item-description"><?php echo htmlspecialchars($badge['description']); ?></div>
                        <div class="item-footer">
                            <span class="date-earned">
                                Earned: <?php echo date('M d, Y', strtotime($badge['date_awarded'])); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Available Badges -->
        <?php if (!empty($available_badges)): ?>
            <div class="section-title" style="margin-top: var(--space-8);">
                <i class="fas fa-lock"></i> Available Badges (<?php echo count($available_badges); ?>)
            </div>
            <p class="section-subtitle">Keep recycling to unlock these badges!</p>

            <div class="items-grid">
                <?php foreach ($available_badges as $badge): ?>
                    <div class="badge-card locked">
                        <span class="locked-badge">🔒 Locked</span>
                        <div class="item-icon locked">
                            <?php echo $badge_icons[$badge['badge_name']] ?? '🏅'; ?>
                        </div>
                        <div class="item-name"><?php echo htmlspecialchars($badge['badge_name']); ?></div>
                        <div class="item-description"><?php echo htmlspecialchars($badge['description']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($earned_badges) && empty($available_badges)): ?>
            <div class="empty-state">
                <div class="empty-icon">🏆</div>
                <h3 class="empty-title">No Badges Available</h3>
                <p class="empty-text">Check back later for new badges!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Rewards Tab -->
    <div id="rewards-tab" class="tab-content">
        <!-- Unclaimed Rewards (Earned from Challenges) -->
        <?php if (!empty($unclaimed_rewards)): ?>
            <div class="section-title">
                <i class="fas fa-gift"></i> Rewards to Claim (<?php echo count($unclaimed_rewards); ?>)
            </div>
            <p class="section-subtitle">You've earned these rewards from completing challenges - claim them now!</p>

            <div class="items-grid">
                <?php foreach ($unclaimed_rewards as $reward): ?>
                    <div class="reward-card" style="border: 2px solid #FFD93D;">
                        <span class="earned-badge" style="background: #FFD93D; color: #1A202C;">🎁 Ready to Claim</span>
                        <div class="item-icon">🎁</div>
                        <div class="item-name"><?php echo htmlspecialchars($reward['reward_name']); ?></div>
                        <div class="item-description"><?php echo htmlspecialchars($reward['description']); ?></div>
                        <div class="item-footer"
                            style="border-top: 1px solid var(--color-gray-200); padding-top: var(--space-4); margin-top: var(--space-4);">
                            <form method="POST" action="claim_reward.php" style="width: 100%;">
                                <input type="hidden" name="reward_id" value="<?php echo $reward['reward_id']; ?>">
                                <button type="submit" class="claim-button">
                                    <i class="fas fa-hand-holding-heart"></i> Claim Reward
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Claimed Rewards -->
        <?php if (!empty($claimed_rewards)): ?>
            <div class="section-title" style="margin-top: var(--space-8);">
                <i class="fas fa-check-circle"></i> Claimed Rewards (<?php echo count($claimed_rewards); ?>)
            </div>
            <p class="section-subtitle">Rewards you've already claimed</p>

            <div class="items-grid">
                <?php foreach ($claimed_rewards as $reward): ?>
                    <div class="reward-card earned">
                        <span class="earned-badge">✓ Claimed</span>
                        <div class="item-icon">🎁</div>
                        <div class="item-name"><?php echo htmlspecialchars($reward['reward_name']); ?></div>
                        <div class="item-description"><?php echo htmlspecialchars($reward['description']); ?></div>
                        <div class="item-footer">
                            <span class="date-earned">
                                Earned: <?php echo date('M d, Y', strtotime($reward['date_earned'])); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Available Rewards (Not Earned Yet) -->
        <?php if (!empty($available_rewards)): ?>
            <div class="section-title" style="margin-top: var(--space-8);">
                <i class="fas fa-lock"></i> Other Rewards (<?php echo count($available_rewards); ?>)
            </div>
            <p class="section-subtitle">Complete challenges to earn these rewards</p>

            <div class="items-grid">
                <?php foreach ($available_rewards as $reward): ?>
                    <div class="reward-card locked">
                        <span class="locked-badge">🔒 Locked</span>
                        <div class="item-icon locked">🎁</div>
                        <div class="item-name"><?php echo htmlspecialchars($reward['reward_name']); ?></div>
                        <div class="item-description"><?php echo htmlspecialchars($reward['description']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($unclaimed_rewards) && empty($claimed_rewards) && empty($available_rewards)): ?>
            <div class="empty-state">
                <div class="empty-icon">🎁</div>
                <h3 class="empty-title">No Rewards Available</h3>
                <p class="empty-text">Check back later for new rewards!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function showTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelectorAll('.tab').forEach(btn => {
            btn.classList.remove('active');
        });

        // Show selected tab
        document.getElementById(tabName + '-tab').classList.add('active');
        event.target.closest('.tab').classList.add('active');
    }
</script>

<?php include 'includes/footer.php'; ?>