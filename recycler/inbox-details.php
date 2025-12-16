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

// Get submission_id from URL
if (!isset($_GET['id'])) {
    header('Location: inbox.php');
    exit();
}

$submission_id = intval($_GET['id']);

// Handle message deletion
if (isset($_POST['delete_message'])) {
    $delete_stmt = mysqli_prepare($conn, "UPDATE recycling_submission SET moderator_feedback = NULL WHERE submission_id = ? AND user_id = ?");
    mysqli_stmt_bind_param($delete_stmt, "ii", $submission_id, $user_id);
    mysqli_stmt_execute($delete_stmt);
    header('Location: inbox.php?deleted=1');
    exit();
}

// Get message details
$query = "SELECT 
          rs.submission_id,
          rs.moderator_feedback,
          rs.status,
          rs.created_at,
          rs.image_url,
          rs.ai_confidence,
          rb.bin_name,
          rb.bin_location,
          GROUP_CONCAT(CONCAT(m.material_name, ' x', sm.quantity) SEPARATOR ', ') as materials_detail,
          SUM(sm.quantity * m.points_per_item) as points_earned,
          SUM(sm.quantity) as total_items
          FROM recycling_submission rs
          LEFT JOIN submission_material sm ON rs.submission_id = sm.submission_id
          LEFT JOIN material m ON sm.material_id = m.material_id
          LEFT JOIN recycling_bin rb ON rs.bin_id = rb.bin_id
          WHERE rs.submission_id = ? AND rs.user_id = ?
          GROUP BY rs.submission_id";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $submission_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    header('Location: inbox.php');
    exit();
}

$message = mysqli_fetch_assoc($result);

$page_title = "Message Details";
include 'includes/header.php';
?>

<style>
    .details-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .back-button {
        display: inline-flex;
        align-items: center;
        gap: var(--space-2);
        padding: var(--space-2) var(--space-4);
        background: var(--color-gray-200);
        color: var(--color-gray-700);
        border: none;
        border-radius: var(--radius-md);
        font-weight: 600;
        text-decoration: none;
        margin-bottom: var(--space-6);
        transition: all 0.2s ease;
    }

    .back-button:hover {
        background: var(--color-gray-300);
        transform: translateX(-4px);
    }

    .message-header-card {
        background: white;
        border-radius: var(--radius-lg);
        padding: var(--space-6);
        box-shadow: var(--shadow-md);
        margin-bottom: var(--space-6);
    }

    .header-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: var(--space-4);
        flex-wrap: wrap;
        gap: var(--space-3);
    }

    .message-title {
        font-size: var(--text-2xl);
        font-weight: 700;
        color: var(--color-gray-800);
        margin: 0;
        display: flex;
        align-items: center;
        gap: var(--space-2);
    }

    .status-badge {
        padding: var(--space-2) var(--space-4);
        border-radius: var(--radius-full);
        font-size: var(--text-sm);
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

    .status-badge.pending {
        background: var(--color-warning-light);
        color: var(--color-warning);
    }

    .message-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--space-4);
        padding: var(--space-4);
        background: var(--color-gray-50);
        border-radius: var(--radius-md);
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: var(--space-2);
    }

    .meta-label {
        font-size: var(--text-sm);
        color: var(--color-gray-600);
        font-weight: 600;
    }

    .meta-value {
        font-size: var(--text-sm);
        color: var(--color-gray-800);
    }

    .submission-details {
        background: white;
        border-radius: var(--radius-lg);
        padding: var(--space-6);
        box-shadow: var(--shadow-md);
        margin-bottom: var(--space-6);
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

    .materials-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--space-4);
        margin-bottom: var(--space-6);
    }

    .material-card {
        background: var(--color-gray-50);
        padding: var(--space-4);
        border-radius: var(--radius-md);
        border-left: 4px solid var(--color-primary);
    }

    .material-name {
        font-weight: 600;
        color: var(--color-gray-800);
        margin-bottom: var(--space-2);
    }

    .material-points {
        color: var(--color-success);
        font-weight: 700;
        font-size: var(--text-lg);
    }

    .image-preview {
        width: 100%;
        max-width: 400px;
        height: auto;
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-md);
        margin: var(--space-4) 0;
    }

    .feedback-section {
        background: white;
        border-radius: var(--radius-lg);
        padding: var(--space-6);
        box-shadow: var(--shadow-md);
        margin-bottom: var(--space-6);
    }

    .feedback-box {
        background: var(--color-gray-50);
        padding: var(--space-6);
        border-radius: var(--radius-md);
        border-left: 4px solid var(--color-primary);
    }

    .feedback-text {
        color: var(--color-gray-700);
        line-height: 1.8;
        font-size: var(--text-base);
        margin: 0;
        white-space: pre-wrap;
    }

    .bin-info {
        background: var(--color-info-light);
        padding: var(--space-4);
        border-radius: var(--radius-md);
        border-left: 4px solid var(--color-info);
        margin-bottom: var(--space-4);
    }

    .bin-info p {
        margin: 0;
        color: var(--color-gray-700);
        font-size: var(--text-sm);
    }

    .points-highlight {
        background: linear-gradient(135deg, var(--color-success), var(--color-success-dark));
        color: white;
        padding: var(--space-6);
        border-radius: var(--radius-lg);
        text-align: center;
        margin-bottom: var(--space-6);
    }

    .points-value {
        font-size: var(--text-4xl);
        font-weight: 700;
        margin-bottom: var(--space-2);
    }

    .points-label {
        font-size: var(--text-sm);
        opacity: 0.9;
    }

    .action-buttons {
        display: flex;
        gap: var(--space-3);
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .btn-delete {
        padding: var(--space-3) var(--space-6);
        background: var(--color-danger);
        color: white;
        border: none;
        border-radius: var(--radius-md);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-delete:hover {
        background: var(--color-danger-dark);
        transform: translateY(-2px);
    }

    .confidence-badge {
        display: inline-flex;
        align-items: center;
        gap: var(--space-2);
        padding: var(--space-2) var(--space-3);
        background: var(--color-gray-100);
        border-radius: var(--radius-md);
        font-size: var(--text-sm);
        font-weight: 600;
    }

    .confidence-high {
        background: var(--color-success-light);
        color: var(--color-success);
    }

    .confidence-medium {
        background: var(--color-warning-light);
        color: var(--color-warning);
    }

    .confidence-low {
        background: var(--color-danger-light);
        color: var(--color-danger);
    }

    @media (max-width: 768px) {
        .header-top {
            flex-direction: column;
        }

        .message-meta {
            grid-template-columns: 1fr;
        }

        .materials-grid {
            grid-template-columns: 1fr;
        }

        .points-value {
            font-size: var(--text-3xl);
        }
    }
</style>

<div class="details-container">
    <a href="inbox.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back to Inbox
    </a>

    <!-- Message Header -->
    <div class="message-header-card">
        <div class="header-top">
            <h1 class="message-title">
                <i class="fas fa-user-shield"></i>
                Message from Moderator
            </h1>
            <span class="status-badge <?php echo strtolower($message['status']); ?>">
                <?php echo ucfirst($message['status']); ?>
            </span>
        </div>

        <div class="message-meta">
            <div class="meta-item">
                <i class="fas fa-calendar"></i>
                <div>
                    <div class="meta-label">Date</div>
                    <div class="meta-value"><?php echo date('M d, Y', strtotime($message['created_at'])); ?></div>
                </div>
            </div>
            <div class="meta-item">
                <i class="fas fa-clock"></i>
                <div>
                    <div class="meta-label">Time</div>
                    <div class="meta-value"><?php echo date('g:i A', strtotime($message['created_at'])); ?></div>
                </div>
            </div>
            <div class="meta-item">
                <i class="fas fa-map-marker-alt"></i>
                <div>
                    <div class="meta-label">Location</div>
                    <div class="meta-value"><?php echo htmlspecialchars($message['bin_name'] ?? 'N/A'); ?></div>
                </div>
            </div>
            <?php if ($message['ai_confidence']): ?>
                <div class="meta-item">
                    <i class="fas fa-robot"></i>
                    <div>
                        <div class="meta-label">AI Confidence</div>
                        <div class="meta-value">
                            <span class="confidence-badge <?php 
                                $conf = $message['ai_confidence'];
                                echo $conf >= 90 ? 'confidence-high' : ($conf >= 70 ? 'confidence-medium' : 'confidence-low');
                            ?>">
                                <?php echo $message['ai_confidence']; ?>%
                            </span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Points Highlight -->
    <?php if ($message['points_earned'] > 0): ?>
        <div class="points-highlight">
            <div class="points-value">+<?php echo $message['points_earned']; ?> Points</div>
            <div class="points-label">Added to your account</div>
        </div>
    <?php endif; ?>

    <!-- Submission Details -->
    <div class="submission-details">
        <h2 class="section-title">
            <i class="fas fa-recycle"></i>
            Recycling Submission Details
        </h2>

        <?php if ($message['bin_location']): ?>
            <div class="bin-info">
                <p><strong><i class="fas fa-location-arrow"></i> Bin Location:</strong> <?php echo htmlspecialchars($message['bin_location']); ?></p>
            </div>
        <?php endif; ?>

        <div class="materials-grid">
            <div class="material-card">
                <div class="material-name">
                    <i class="fas fa-box"></i> Materials Recycled
                </div>
                <p><?php echo htmlspecialchars($message['materials_detail'] ?: 'No materials recorded'); ?></p>
            </div>

            <div class="material-card">
                <div class="material-name">
                    <i class="fas fa-calculator"></i> Total Items
                </div>
                <div class="material-points"><?php echo $message['total_items']; ?> items</div>
            </div>
        </div>

        <?php if ($message['image_url']): ?>
            <div>
                <h3 class="section-title">
                    <i class="fas fa-camera"></i>
                    Submission Image
                </h3>
                <img src="<?php echo htmlspecialchars($message['image_url']); ?>" 
                     alt="Recycling submission" 
                     class="image-preview"
                     onerror="this.style.display='none'">
            </div>
        <?php endif; ?>
    </div>

    <!-- Moderator Feedback -->
    <div class="feedback-section">
        <h2 class="section-title">
            <i class="fas fa-comment-alt"></i>
            Moderator Feedback
        </h2>
        <div class="feedback-box">
            <p class="feedback-text"><?php echo nl2br(htmlspecialchars($message['moderator_feedback'])); ?></p>
        </div>
    </div>

    <!-- Actions -->
    <div class="action-buttons">
        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this message?');">
            <button type="submit" name="delete_message" class="btn-delete">
                <i class="fas fa-trash"></i> Delete Message
            </button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>