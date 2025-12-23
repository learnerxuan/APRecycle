<?php
require_once '../php/config.php';

$page_title = 'Reward Management';
$conn = getDBConnection();

$success_message = '';
$error_message = '';

// Handle delete reward
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $reward_id = (int) $_GET['delete'];

    // Check if reward is used in challenges
    $check_query = "SELECT COUNT(*) as count FROM challenge WHERE reward_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "i", $reward_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $check_row = mysqli_fetch_assoc($check_result);
    mysqli_stmt_close($check_stmt);

    if ($check_row['count'] > 0) {
        $error_message = "Cannot delete this reward. It is currently used in {$check_row['count']} challenge(s).";
    } else {
        // Delete user_reward records first
        $delete_user_rewards = mysqli_prepare($conn, "DELETE FROM user_reward WHERE reward_id = ?");
        mysqli_stmt_bind_param($delete_user_rewards, "i", $reward_id);
        mysqli_stmt_execute($delete_user_rewards);
        mysqli_stmt_close($delete_user_rewards);

        // Delete the reward
        $delete_stmt = mysqli_prepare($conn, "DELETE FROM reward WHERE reward_id = ?");
        mysqli_stmt_bind_param($delete_stmt, "i", $reward_id);

        if (mysqli_stmt_execute($delete_stmt)) {
            $success_message = "Reward deleted successfully!";
        } else {
            $error_message = "Error deleting reward: " . mysqli_error($conn);
        }
        mysqli_stmt_close($delete_stmt);
    }
}

// Fetch all rewards
$rewards_query = "SELECT r.*,
                  COUNT(DISTINCT ur.user_id) as users_earned,
                  COUNT(DISTINCT c.challenge_id) as used_in_challenges
                  FROM reward r
                  LEFT JOIN user_reward ur ON r.reward_id = ur.reward_id
                  LEFT JOIN challenge c ON r.reward_id = c.reward_id
                  GROUP BY r.reward_id
                  ORDER BY r.point_required ASC";
$rewards_result = mysqli_query($conn, $rewards_query);

// Get total count
$total_rewards = mysqli_num_rows($rewards_result);

// Include admin header
include 'includes/header.php';
?>

<style>
    .page-header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--space-6);
        padding: var(--space-6);
        background: var(--color-white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
    }

    .page-header-actions h2 {
        color: var(--color-gray-800);
        margin: 0;
        display: flex;
        align-items: center;
        gap: var(--space-2);
    }

    .btn {
        padding: var(--space-3) var(--space-6);
        border-radius: var(--radius-md);
        font-size: var(--text-base);
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: var(--space-2);
        cursor: pointer;
        border: none;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: var(--color-primary);
        color: white;
        box-shadow: var(--shadow-sm);
    }

    .btn-primary:hover {
        background: var(--color-primary-light);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .btn-edit {
        background: var(--color-accent-blue);
        color: white;
        padding: var(--space-2) var(--space-4);
        font-size: var(--text-sm);
    }

    .btn-edit:hover {
        background: #2563EB;
        transform: translateY(-1px);
    }

    .btn-delete {
        background: var(--color-error);
        color: white;
        padding: var(--space-2) var(--space-4);
        font-size: var(--text-sm);
    }

    .btn-delete:hover {
        background: #DC2626;
        transform: translateY(-1px);
    }

    .alert {
        padding: var(--space-4) var(--space-6);
        border-radius: var(--radius-md);
        margin-bottom: var(--space-6);
        display: flex;
        align-items: center;
        gap: var(--space-3);
    }

    .alert-success {
        background: var(--color-success-light);
        color: #065F46;
        border-left: 4px solid var(--color-success);
    }

    .alert-error {
        background: var(--color-error-light);
        color: #991B1B;
        border-left: 4px solid var(--color-error);
    }

    .stats-card {
        background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 100%);
        color: white;
        padding: var(--space-6);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        margin-bottom: var(--space-6);
        text-align: center;
    }

    .stats-card h3 {
        font-size: var(--text-4xl);
        margin: 0 0 var(--space-2) 0;
    }

    .stats-card p {
        opacity: 0.9;
        font-size: var(--text-base);
        margin: 0;
    }

    .rewards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: var(--space-6);
    }

    .reward-card {
        background: var(--color-white);
        border: 2px solid var(--color-gray-200);
        border-radius: var(--radius-lg);
        padding: var(--space-6);
        transition: all 0.3s ease;
    }

    .reward-card:hover {
        border-color: var(--color-accent-yellow);
        box-shadow: var(--shadow-lg);
        transform: translateY(-4px);
    }

    .reward-card-header {
        margin-bottom: var(--space-4);
    }

    .reward-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 100%);
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin: 0 auto var(--space-4) auto;
        box-shadow: var(--shadow-md);
    }

    .reward-title {
        font-size: var(--text-xl);
        font-weight: 700;
        color: var(--color-gray-800);
        margin: 0 0 var(--space-2) 0;
        text-align: center;
    }

    .reward-description {
        color: var(--color-gray-600);
        font-size: var(--text-base);
        line-height: 1.5;
        margin-bottom: var(--space-4);
        text-align: center;
    }

    .reward-meta {
        display: grid;
        gap: var(--space-3);
        margin-bottom: var(--space-4);
        padding: var(--space-4);
        background: var(--color-gray-100);
        border-radius: var(--radius-md);
    }

    .reward-meta-item {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        font-size: var(--text-sm);
    }

    .reward-meta-item i {
        color: #F59E0B;
        width: 16px;
    }

    .reward-meta-item strong {
        color: var(--color-gray-700);
        min-width: 90px;
    }

    .reward-meta-item span {
        color: var(--color-gray-800);
    }

    .reward-actions {
        display: flex;
        gap: var(--space-3);
    }

    .points-badge {
        background: var(--color-warning-light);
        color: #92400E;
        padding: var(--space-1) var(--space-3);
        border-radius: var(--radius-full);
        font-weight: 700;
        font-size: var(--text-sm);
    }

    .empty-state {
        text-align: center;
        padding: var(--space-12) var(--space-8);
        color: var(--color-gray-600);
        background: var(--color-white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
    }

    .empty-state-icon {
        font-size: 4rem;
        margin-bottom: var(--space-4);
        opacity: 0.3;
    }

    .empty-state h3 {
        font-size: var(--text-xl);
        margin-bottom: var(--space-2);
        color: var(--color-gray-700);
    }

    @media (max-width: 768px) {
        .page-header-actions {
            flex-direction: column;
            align-items: stretch;
            gap: var(--space-3);
        }

        .rewards-grid {
            grid-template-columns: 1fr;
        }

        .reward-actions {
            flex-direction: column;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header-actions">
    <h2>
        <i class="fas fa-gift"></i> Reward Management
    </h2>
    <a href="reward_create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create New Reward
    </a>
</div>

<?php if ($success_message): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($success_message); ?>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-triangle"></i>
        <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php endif; ?>

<!-- Statistics -->
<div class="stats-card">
    <h3><?php echo $total_rewards; ?></h3>
    <p>Total Rewards Available</p>
</div>

<!-- Rewards Grid -->
<?php if ($total_rewards > 0): ?>
    <div class="rewards-grid">
        <?php while ($reward = mysqli_fetch_assoc($rewards_result)): ?>
            <div class="reward-card">
                <div class="reward-card-header">
                    <div class="reward-icon">
                        <i class="fas fa-gift"></i>
                    </div>
                    <h3 class="reward-title"><?php echo htmlspecialchars($reward['reward_name']); ?></h3>
                </div>

                <p class="reward-description">
                    <?php echo htmlspecialchars($reward['description']); ?>
                </p>

                <div class="reward-meta">
                    <div class="reward-meta-item">
                        <i class="fas fa-star"></i>
                        <strong>Points:</strong>
                        <span class="points-badge"><?php echo number_format($reward['point_required']); ?> pts</span>
                    </div>
                    <div class="reward-meta-item">
                        <i class="fas fa-users"></i>
                        <strong>Users Earned:</strong>
                        <span><?php echo $reward['users_earned']; ?> users</span>
                    </div>
                    <div class="reward-meta-item">
                        <i class="fas fa-trophy"></i>
                        <strong>In Challenges:</strong>
                        <span><?php echo $reward['used_in_challenges']; ?> challenges</span>
                    </div>
                </div>

                <div class="reward-actions">
                    <a href="reward_create.php?id=<?php echo $reward['reward_id']; ?>" class="btn btn-edit">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="?delete=<?php echo $reward['reward_id']; ?>" class="btn btn-delete"
                        onclick="return confirm('Are you sure you want to delete this reward?');">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-gift"></i></div>
        <h3>No Rewards Yet</h3>
        <p>Create your first reward to incentivize recyclers and make recycling more rewarding!</p>
        <a href="reward_create.php" class="btn btn-primary" style="margin-top: var(--space-4);">
            <i class="fas fa-plus"></i> Create First Reward
        </a>
    </div>
<?php endif; ?>

<?php
include 'includes/footer.php';
mysqli_close($conn);
?>
