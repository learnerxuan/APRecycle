<?php
session_start();
require_once '../php/config.php';

// Check if user is logged in and is an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrator') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$conn = getDBConnection();

// Handle moderator removal
$success_message = '';
$error_message = '';

if (isset($_POST['remove_moderator'])) {
    $moderator_id = intval($_POST['moderator_id']);
    
    // Update user role back to recycler
    $update_stmt = mysqli_prepare($conn, "UPDATE user SET role = 'recycler' WHERE user_id = ? AND role = 'eco_moderator'");
    mysqli_stmt_bind_param($update_stmt, "i", $moderator_id);
    
    if (mysqli_stmt_execute($update_stmt)) {
        $success_message = "Eco-Moderator removed successfully!";
    } else {
        $error_message = "Failed to remove eco-moderator.";
    }
}

// Get all eco-moderators with their stats
$moderators_query = "SELECT 
                     u.user_id,
                     u.username,
                     u.email,
                     u.created_at,
                     COUNT(DISTINCT rs.submission_id) as reviews_completed,
                     COUNT(DISTINCT ec.content_id) as content_created
                     FROM user u
                     LEFT JOIN recycling_submission rs ON u.user_id = rs.user_id
                     LEFT JOIN educational_content ec ON u.user_id = ec.author_id
                     WHERE u.role = 'eco_moderator'
                     GROUP BY u.user_id
                     ORDER BY u.created_at DESC";

$moderators_result = mysqli_query($conn, $moderators_query);
$moderators = [];
while ($row = mysqli_fetch_assoc($moderators_result)) {
    $moderators[] = $row;
}

$page_title = "Eco-Moderator Management";
include 'includes/header.php';
?>

<style>
    .moderators-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--space-6);
        flex-wrap: wrap;
        gap: var(--space-4);
    }

    .btn-add {
        padding: var(--space-3) var(--space-6);
        background: var(--color-primary);
        color: white;
        border: none;
        border-radius: var(--radius-md);
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: var(--space-2);
        transition: all 0.2s ease;
    }

    .btn-add:hover {
        background: var(--color-primary-dark);
        transform: translateY(-2px);
    }

    .alert {
        padding: var(--space-4);
        border-radius: var(--radius-md);
        margin-bottom: var(--space-6);
        border-left: 4px solid;
    }

    .alert-success {
        background: var(--color-success-light);
        color: var(--color-success);
        border-left-color: var(--color-success);
    }

    .alert-error {
        background: var(--color-danger-light);
        color: var(--color-danger);
        border-left-color: var(--color-danger);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--space-4);
        margin-bottom: var(--space-6);
    }

    .stat-card {
        background: white;
        padding: var(--space-6);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        text-align: center;
    }

    .stat-value {
        font-size: var(--text-3xl);
        font-weight: 700;
        color: var(--color-primary);
        margin-bottom: var(--space-2);
    }

    .stat-label {
        font-size: var(--text-sm);
        color: var(--color-gray-600);
        font-weight: 600;
    }

    .moderators-table {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        overflow: hidden;
    }

    .table-header {
        background: var(--color-primary);
        color: white;
        padding: var(--space-4);
        font-weight: 700;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        background: var(--color-primary);
        color: white;
        padding: var(--space-4);
        text-align: left;
        font-weight: 600;
        font-size: var(--text-sm);
    }

    td {
        padding: var(--space-4);
        border-bottom: 1px solid var(--color-gray-200);
    }

    tr:hover {
        background: var(--color-gray-50);
    }

    .moderator-name {
        font-weight: 600;
        color: var(--color-gray-800);
    }

    .moderator-email {
        color: var(--color-gray-600);
        font-size: var(--text-sm);
    }

    .moderator-id {
        color: var(--color-gray-500);
        font-size: var(--text-xs);
        font-family: monospace;
    }

    .badge {
        display: inline-block;
        padding: var(--space-1) var(--space-3);
        background: var(--color-info-light);
        color: var(--color-info);
        border-radius: var(--radius-full);
        font-size: var(--text-xs);
        font-weight: 600;
    }

    .action-buttons {
        display: flex;
        gap: var(--space-2);
    }

    .btn-edit {
        padding: var(--space-2) var(--space-4);
        background: var(--color-warning);
        color: white;
        border: none;
        border-radius: var(--radius-md);
        font-size: var(--text-sm);
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-edit:hover {
        background: var(--color-warning-dark);
    }

    .btn-remove {
        padding: var(--space-2) var(--space-4);
        background: var(--color-danger);
        color: white;
        border: none;
        border-radius: var(--radius-md);
        font-size: var(--text-sm);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-remove:hover {
        background: var(--color-danger-dark);
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

    @media (max-width: 768px) {
        .moderators-table {
            overflow-x: auto;
        }

        table {
            min-width: 800px;
        }

        .page-header {
            flex-direction: column;
            align-items: stretch;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="moderators-container">
    <div class="page-header">
        <div>
            <h1 style="font-size: var(--text-3xl); font-weight: 700; margin-bottom: var(--space-2);">
                <i class="fas fa-user-shield"></i> Eco-Moderator Management
            </h1>
            <p style="color: var(--color-gray-600);">Manage eco-moderator accounts and permissions</p>
        </div>
        <a href="moderator_add.php" class="btn-add">
            <i class="fas fa-plus"></i> Add New Moderator
        </a>
    </div>

    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?php echo count($moderators); ?></div>
            <div class="stat-label">Active Eco-Moderators</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo array_sum(array_column($moderators, 'reviews_completed')); ?></div>
            <div class="stat-label">Total Reviews Completed</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo array_sum(array_column($moderators, 'content_created')); ?></div>
            <div class="stat-label">Educational Content Created</div>
        </div>
    </div>

    <!-- Moderators Table -->
    <?php if (empty($moderators)): ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <h3 class="empty-title">No Eco-Moderators Yet</h3>
            <p style="color: var(--color-gray-500); margin-bottom: var(--space-6);">
                Add your first eco-moderator to start managing recycling submissions.
            </p>
            <a href="moderator_add.php" class="btn-add">
                <i class="fas fa-plus"></i> Add First Moderator
            </a>
        </div>
    <?php else: ?>
        <div class="moderators-table">
            <table>
                <thead>
                    <tr>
                        <th>Moderator ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Joined Date</th>
                        <th>Reviews</th>
                        <th>Content</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($moderators as $moderator): ?>
                        <tr>
                            <td>
                                <span class="moderator-id">#<?php echo str_pad($moderator['user_id'], 4, '0', STR_PAD_LEFT); ?></span>
                            </td>
                            <td>
                                <div class="moderator-name"><?php echo htmlspecialchars($moderator['username']); ?></div>
                                <span class="badge">Eco-Moderator</span>
                            </td>
                            <td>
                                <div class="moderator-email"><?php echo htmlspecialchars($moderator['email']); ?></div>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($moderator['created_at'])); ?></td>
                            <td><?php echo $moderator['reviews_completed']; ?></td>
                            <td><?php echo $moderator['content_created']; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="moderator_edit.php?id=<?php echo $moderator['user_id']; ?>" class="btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to remove this eco-moderator? They will be converted back to a recycler.');">
                                        <input type="hidden" name="moderator_id" value="<?php echo $moderator['user_id']; ?>">
                                        <button type="submit" name="remove_moderator" class="btn-remove">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>