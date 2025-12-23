<?php
require_once '../php/config.php';

$page_title = 'Badge Management';
$conn = getDBConnection();

$success_message = '';
$error_message = '';

// Handle delete badge
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $badge_id = (int) $_GET['delete'];

    // Check if badge is used in challenges
    $check_query = "SELECT COUNT(*) as count FROM challenge WHERE badge_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "i", $badge_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $check_row = mysqli_fetch_assoc($check_result);
    mysqli_stmt_close($check_stmt);

    if ($check_row['count'] > 0) {
        $error_message = "Cannot delete this badge. It is currently used in {$check_row['count']} challenge(s).";
    } else {
        // Delete user_badge records first
        $delete_user_badges = mysqli_prepare($conn, "DELETE FROM user_badge WHERE badge_id = ?");
        mysqli_stmt_bind_param($delete_user_badges, "i", $badge_id);
        mysqli_stmt_execute($delete_user_badges);
        mysqli_stmt_close($delete_user_badges);

        // Delete the badge
        $delete_stmt = mysqli_prepare($conn, "DELETE FROM badge WHERE badge_id = ?");
        mysqli_stmt_bind_param($delete_stmt, "i", $badge_id);

        if (mysqli_stmt_execute($delete_stmt)) {
            $success_message = "Badge deleted successfully!";
        } else {
            $error_message = "Error deleting badge: " . mysqli_error($conn);
        }
        mysqli_stmt_close($delete_stmt);
    }
}

// Fetch all badges
$badges_query = "SELECT b.*,
                 COUNT(DISTINCT ub.user_id) as users_earned,
                 COUNT(DISTINCT c.challenge_id) as used_in_challenges
                 FROM badge b
                 LEFT JOIN user_badge ub ON b.badge_id = ub.badge_id
                 LEFT JOIN challenge c ON b.badge_id = c.badge_id
                 GROUP BY b.badge_id
                 ORDER BY b.badge_type ASC, b.point_required ASC";
$badges_result = mysqli_query($conn, $badges_query);

// Get total count
$total_badges = mysqli_num_rows($badges_result);

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
        background: var(--gradient-primary);
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

    .badges-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: var(--space-6);
    }

    .badge-card {
        background: var(--color-white);
        border: 2px solid var(--color-gray-200);
        border-radius: var(--radius-lg);
        padding: var(--space-6);
        transition: all 0.3s ease;
    }

    .badge-card:hover {
        border-color: var(--color-secondary);
        box-shadow: var(--shadow-lg);
        transform: translateY(-4px);
    }

    .badge-card-header {
        margin-bottom: var(--space-4);
    }

    .badge-icon {
        width: 80px;
        height: 80px;
        background: var(--gradient-badge);
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin: 0 auto var(--space-4) auto;
        box-shadow: var(--shadow-md);
    }

    .badge-title {
        font-size: var(--text-xl);
        font-weight: 700;
        color: var(--color-gray-800);
        margin: 0 0 var(--space-2) 0;
        text-align: center;
    }

    .badge-description {
        color: var(--color-gray-600);
        font-size: var(--text-base);
        line-height: 1.5;
        margin-bottom: var(--space-4);
        text-align: center;
    }

    .badge-meta {
        display: grid;
        gap: var(--space-3);
        margin-bottom: var(--space-4);
        padding: var(--space-4);
        background: var(--color-gray-100);
        border-radius: var(--radius-md);
    }

    .badge-meta-item {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        font-size: var(--text-sm);
    }

    .badge-meta-item i {
        color: var(--color-primary);
        width: 16px;
    }

    .badge-meta-item strong {
        color: var(--color-gray-700);
        min-width: 90px;
    }

    .badge-meta-item span {
        color: var(--color-gray-800);
    }

    .badge-actions {
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

    .badge-type-tag {
        display: inline-block;
        padding: var(--space-1) var(--space-3);
        border-radius: var(--radius-full);
        font-weight: 700;
        font-size: var(--text-xs);
        text-transform: uppercase;
        margin-bottom: var(--space-3);
    }

    .badge-type-milestone {
        background: linear-gradient(135deg, #FCD34D, #F59E0B);
        color: #78350F;
    }

    .badge-type-challenge {
        background: linear-gradient(135deg, #A78BFA, #8B5CF6);
        color: #4C1D95;
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

        .badges-grid {
            grid-template-columns: 1fr;
        }

        .badge-actions {
            flex-direction: column;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header-actions">
    <h2>
        <i class="fas fa-medal"></i> Badge Management
    </h2>
    <a href="badge_create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create New Badge
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
    <h3><?php echo $total_badges; ?></h3>
    <p>Total Badges Created</p>
</div>

<!-- Badges Grid -->
<?php if ($total_badges > 0): ?>
    <div class="badges-grid">
        <?php while ($badge = mysqli_fetch_assoc($badges_result)): ?>
            <div class="badge-card">
                <div class="badge-card-header">
                    <span class="badge-type-tag badge-type-<?php echo $badge['badge_type']; ?>">
                        <?php echo $badge['badge_type'] == 'milestone' ? '⭐ Milestone' : '🏆 Challenge'; ?>
                    </span>
                    <div class="badge-icon">
                        <i class="fas fa-medal"></i>
                    </div>
                    <h3 class="badge-title"><?php echo htmlspecialchars($badge['badge_name']); ?></h3>
                </div>

                <p class="badge-description">
                    <?php echo htmlspecialchars($badge['description']); ?>
                </p>

                <div class="badge-meta">
                    <?php if ($badge['badge_type'] == 'milestone'): ?>
                        <div class="badge-meta-item">
                            <i class="fas fa-star"></i>
                            <strong>Points:</strong>
                            <span class="points-badge"><?php echo number_format($badge['point_required']); ?> pts</span>
                        </div>
                    <?php else: ?>
                        <div class="badge-meta-item">
                            <i class="fas fa-trophy"></i>
                            <strong>Type:</strong>
                            <span>Challenge-only badge</span>
                        </div>
                    <?php endif; ?>
                    <div class="badge-meta-item">
                        <i class="fas fa-users"></i>
                        <strong>Users Earned:</strong>
                        <span><?php echo $badge['users_earned']; ?> users</span>
                    </div>
                    <div class="badge-meta-item">
                        <i class="fas fa-trophy"></i>
                        <strong>In Challenges:</strong>
                        <span><?php echo $badge['used_in_challenges']; ?> challenges</span>
                    </div>
                </div>

                <div class="badge-actions">
                    <a href="badge_create.php?id=<?php echo $badge['badge_id']; ?>" class="btn btn-edit">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="?delete=<?php echo $badge['badge_id']; ?>" class="btn btn-delete"
                        onclick="return confirm('Are you sure you want to delete this badge?');">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-medal"></i></div>
        <h3>No Badges Yet</h3>
        <p>Create your first badge to reward recyclers for their achievements!</p>
        <a href="badge_create.php" class="btn btn-primary" style="margin-top: var(--space-4);">
            <i class="fas fa-plus"></i> Create First Badge
        </a>
    </div>
<?php endif; ?>

<?php
include 'includes/footer.php';
mysqli_close($conn);
?>
