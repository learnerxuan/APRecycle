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
    $update_stmt = mysqli_prepare($conn, "UPDATE user SET role = 'recycler' WHERE user_id = ? AND role = 'eco-moderator'");
    mysqli_stmt_bind_param($update_stmt, "i", $moderator_id);
    
    if (mysqli_stmt_execute($update_stmt)) {
        $affected = mysqli_stmt_affected_rows($update_stmt);
        if ($affected > 0) {
            $success_message = "Eco-Moderator removed successfully!";
        } else {
            $error_message = "No moderator found with that ID.";
        }
    } else {
        $error_message = "Failed to remove eco-moderator: " . mysqli_error($conn);
    }
    mysqli_stmt_close($update_stmt);
}

// Get all eco-moderators with their stats
$moderators_query = "SELECT 
                     u.user_id,
                     u.username,
                     u.email,
                     u.created_at,
                     (SELECT COUNT(DISTINCT ec.content_id) 
                      FROM educational_content ec 
                      WHERE ec.author_id = u.user_id) as content_created
                     FROM user u
                     WHERE u.role = 'eco-moderator'
                     ORDER BY u.created_at DESC";

$moderators_result = mysqli_query($conn, $moderators_query);
$moderators = [];

if ($moderators_result) {
    while ($row = mysqli_fetch_assoc($moderators_result)) {
        $moderators[] = $row;
    }
}

$page_title = "Eco-Moderator Management";
include 'includes/header.php';
?>

<style>
    .moderators-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .btn-add {
        padding: 0.75rem 1.5rem;
        background: #4F46E5;
        color: white;
        border: none;
        border-radius: 0.375rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
    }

    .btn-add:hover {
        background: #4338CA;
        transform: translateY(-2px);
    }

    .alert {
        padding: 1rem;
        border-radius: 0.375rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .alert-success {
        background: #D1FAE5;
        color: #10B981;
        border-left-color: #10B981;
    }

    .alert-error {
        background: #FEE2E2;
        color: #EF4444;
        border-left-color: #EF4444;
    }

    .alert-info {
        background: #DBEAFE;
        color: #3B82F6;
        border-left-color: #3B82F6;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        text-align: center;
    }

    .stat-value {
        font-size: 1.875rem;
        font-weight: 700;
        color: #4F46E5;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #6B7280;
        font-weight: 600;
    }

    .moderators-table {
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        background: #4F46E5;
        color: white;
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        font-size: 0.875rem;
    }

    td {
        padding: 1rem;
        border-bottom: 1px solid #E5E7EB;
        vertical-align: middle;
    }

    tbody tr:hover {
        background: #F9FAFB;
    }

    .moderator-name {
        font-weight: 600;
        color: #1F2937;
        margin-bottom: 0.25rem;
    }

    .moderator-email {
        color: #6B7280;
        font-size: 0.875rem;
    }

    .moderator-id {
        color: #9CA3AF;
        font-size: 0.75rem;
        font-family: monospace;
    }

    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        background: #DBEAFE;
        color: #3B82F6;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-top: 0.25rem;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .btn-edit {
        padding: 0.5rem 1rem;
        background: #F59E0B;
        color: white;
        border: none;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .btn-edit:hover {
        background: #D97706;
    }

    .btn-remove {
        padding: 0.5rem 1rem;
        background: #EF4444;
        color: white;
        border: none;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .btn-remove:hover {
        background: #DC2626;
        transform: scale(1.05);
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    }

    .empty-icon {
        font-size: 4rem;
        color: #D1D5DB;
        margin-bottom: 1rem;
    }

    .empty-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #4B5563;
        margin-bottom: 0.5rem;
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
            <h1 style="font-size: 1.875rem; font-weight: 700; margin-bottom: 0.5rem;">
                <i class="fas fa-user-shield"></i> Eco-Moderator Management
            </h1>
            <p style="color: #6B7280;">Manage eco-moderator accounts and permissions</p>
        </div>
        <a href="moderator_add.php" class="btn-add">
            <i class="fas fa-plus"></i> Add New Moderator
        </a>
    </div>

    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> 
            <span><?php echo htmlspecialchars($success_message); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> 
            <span><?php echo htmlspecialchars($error_message); ?></span>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?php echo count($moderators); ?></div>
            <div class="stat-label">Active Eco-Moderators</div>
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
            <p style="color: #9CA3AF; margin-bottom: 1.5rem;">
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
                        <th>Name & Email</th>
                        <th>Joined Date</th>
                        <th>Content Created</th>
                        <th style="width: 200px;">Actions</th>
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
                                <div class="moderator-email"><?php echo htmlspecialchars($moderator['email']); ?></div>
                                <span class="badge">Eco-Moderator</span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($moderator['created_at'])); ?></td>
                            <td><?php echo $moderator['content_created']; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="moderator_edit.php?id=<?php echo $moderator['user_id']; ?>" class="btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form method="POST" style="display: inline; margin: 0;" 
                                          onsubmit="return confirm('⚠️ Are you sure you want to remove <?php echo htmlspecialchars($moderator['username']); ?> as eco-moderator?\n\nThey will be converted back to a regular recycler.');">
                                        <input type="hidden" name="moderator_id" value="<?php echo $moderator['user_id']; ?>">
                                        <button type="submit" name="remove_moderator" class="btn-remove">
                                            <i class="fas fa-trash-alt"></i> Remove
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Additional Info -->
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <span><strong>Note:</strong> Removing an eco-moderator will convert their account back to a regular recycler. Their created content will remain intact.</span>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>