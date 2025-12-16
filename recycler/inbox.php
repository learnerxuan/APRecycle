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

// Handle message deletion
if (isset($_POST['delete_message'])) {
    $submission_id = intval($_POST['submission_id']);
    $delete_stmt = mysqli_prepare($conn, "UPDATE recycling_submission SET moderator_feedback = NULL WHERE submission_id = ? AND user_id = ?");
    mysqli_stmt_bind_param($delete_stmt, "ii", $submission_id, $user_id);
    mysqli_stmt_execute($delete_stmt);
    header('Location: inbox.php?deleted=1');
    exit();
}

// Get all messages (moderator feedback)
$query = "SELECT 
          rs.submission_id,
          rs.moderator_feedback,
          rs.status,
          rs.created_at,
          rs.image_url,
          GROUP_CONCAT(CONCAT(m.material_name, ' (', sm.quantity, ')') SEPARATOR ', ') as materials,
          SUM(sm.quantity * m.points_per_item) as points_earned
          FROM recycling_submission rs
          LEFT JOIN submission_material sm ON rs.submission_id = sm.submission_id
          LEFT JOIN material m ON sm.material_id = m.material_id
          WHERE rs.user_id = ? AND rs.moderator_feedback IS NOT NULL
          GROUP BY rs.submission_id
          ORDER BY rs.created_at DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$messages = [];
while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = $row;
}

$page_title = "Inbox";
include 'includes/header.php';
?>

<style>
    .inbox-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .message-card {
        background: var(--color-white);
        border-radius: var(--radius-lg);
        padding: var(--space-6);
        margin-bottom: var(--space-4);
        box-shadow: var(--shadow-md);
        border-left: 4px solid var(--color-primary);
        transition: transform 0.2s ease;
    }

    .message-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
        background: var(--color-gray-50);
    }
    
    .message-card {
        cursor: pointer;
    }

    .message-card.approved {
        border-left-color: var(--color-success);
    }

    .message-card.rejected {
        border-left-color: var(--color-danger);
    }

    .message-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: var(--space-4);
        flex-wrap: wrap;
        gap: var(--space-3);
    }

    .message-title {
        font-size: var(--text-lg);
        font-weight: 700;
        color: var(--color-gray-800);
        margin: 0;
    }

    .status-badge {
        padding: var(--space-2) var(--space-4);
        border-radius: var(--radius-full);
        font-size: var(--text-xs);
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-badge.approved {
        background: var(--color-success-light);
        color: var(--color-success);
    }

    .status-badge.rejected {
        background: var(--color-danger-light);
        color: var(--color-danger);
    }

    .message-meta {
        display: flex;
        gap: var(--space-4);
        font-size: var(--text-sm);
        color: var(--color-gray-600);
        margin-bottom: var(--space-3);
        flex-wrap: wrap;
    }

    .message-meta span {
        display: flex;
        align-items: center;
        gap: var(--space-2);
    }

    .message-body {
        background: var(--color-gray-50);
        padding: var(--space-4);
        border-radius: var(--radius-md);
        margin-bottom: var(--space-4);
    }

    .message-text {
        color: var(--color-gray-700);
        line-height: 1.6;
        margin: 0;
    }

    .message-materials {
        font-size: var(--text-sm);
        color: var(--color-gray-600);
        margin-bottom: var(--space-3);
        padding: var(--space-3);
        background: var(--color-info-light);
        border-radius: var(--radius-md);
    }

    .message-actions {
        display: flex;
        gap: var(--space-3);
        justify-content: flex-end;
    }

    .btn-delete {
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

    .btn-delete:hover {
        background: var(--color-danger-dark);
        transform: translateY(-1px);
    }

    .empty-state {
        text-align: center;
        padding: var(--space-12);
        background: var(--color-white);
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
        margin-bottom: var(--space-6);
    }

    .alert-success {
        background: var(--color-success-light);
        color: var(--color-success);
        padding: var(--space-4);
        border-radius: var(--radius-md);
        margin-bottom: var(--space-6);
        border-left: 4px solid var(--color-success);
    }

    @media (max-width: 768px) {
        .message-header {
            flex-direction: column;
        }

        .message-meta {
            flex-direction: column;
            gap: var(--space-2);
        }
    }
</style>

<div class="inbox-container">
    <div class="page-header">
        <h2 class="page-title">
            <i class="fas fa-inbox"></i> Inbox
        </h2>
        <p class="page-description">Feedback from moderators on your recycling submissions</p>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert-success">
            <i class="fas fa-check-circle"></i> Message deleted successfully
        </div>
    <?php endif; ?>

    <?php if (empty($messages)): ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-inbox"></i>
            </div>
            <h3 class="empty-title">No Messages Yet</h3>
            <p class="empty-text">You haven't received any feedback from moderators. Start recycling to get your first message!</p>
            <a href="dashboard.php" class="btn btn-primary">
                <i class="fas fa-recycle"></i> Go to Dashboard
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($messages as $message): ?>
            <div class="message-card <?php echo strtolower($message['status']); ?>" 
                 onclick="window.location.href='inbox-details.php?id=<?php echo $message['submission_id']; ?>'" 
                 style="cursor: pointer;">
                <div class="message-header">
                    <h3 class="message-title">
                        <i class="fas fa-user-shield"></i> Moderator
                    </h3>
                    <span class="status-badge <?php echo strtolower($message['status']); ?>">
                        <?php echo ucfirst($message['status']); ?>
                    </span>
                </div>

                <div class="message-meta">
                    <span>
                        <i class="fas fa-calendar"></i>
                        <?php echo date('M d, Y g:i A', strtotime($message['created_at'])); ?>
                    </span>
                    <?php if ($message['points_earned']): ?>
                        <span>
                            <i class="fas fa-star"></i>
                            +<?php echo $message['points_earned']; ?> points
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ($message['materials']): ?>
                    <div class="message-materials">
                        <strong><i class="fas fa-recycle"></i> Items:</strong> 
                        <?php echo htmlspecialchars($message['materials']); ?>
                    </div>
                <?php endif; ?>

                <div class="message-body">
                    <p class="message-text">
                        <?php echo nl2br(htmlspecialchars($message['moderator_feedback'])); ?>
                    </p>
                </div>

                <div class="message-actions">
                    <form method="POST" style="display: inline;" onsubmit="event.stopPropagation(); return confirm('Are you sure you want to delete this message?');">
                        <input type="hidden" name="submission_id" value="<?php echo $message['submission_id']; ?>">
                        <button type="submit" name="delete_message" class="btn-delete" onclick="event.stopPropagation();">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>