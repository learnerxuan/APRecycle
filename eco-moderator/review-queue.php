<?php
session_start();
require_once '../php/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'eco-moderator') {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Review Queue';
require_once 'includes/header.php';

$conn = getDBConnection();

// Fetch Pending Submissions with AI confidence < 80%
$sql = "SELECT s.submission_id, s.image_url, s.ai_confidence, s.created_at, s.user_id,
        u.username, u.email, m.material_id, m.material_name
        FROM recycling_submission s
        LEFT JOIN user u ON s.user_id = u.user_id
        LEFT JOIN submission_material sm ON s.submission_id = sm.submission_id
        LEFT JOIN material m ON sm.material_id = m.material_id
        WHERE s.status = 'pending'
        ORDER BY s.created_at ASC";
$result = $conn->query($sql);

// Fetch All Materials for Reclassification Dropdown
$materials_res = $conn->query("SELECT * FROM material ORDER BY material_name ASC");
$materials = [];
while ($mat = $materials_res->fetch_assoc()) {
    $materials[] = $mat;
}
?>

<div class="page-header">
    <h1 class="page-title"><i class="fas fa-clipboard-check"></i> Review Queue</h1>
    <p class="page-description">Review pending waste submissions. Approve or reject with feedback.</p>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($_GET['msg']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-triangle"></i>
        <?php echo htmlspecialchars($_GET['error']); ?>
    </div>
<?php endif; ?>

<div class="review-grid">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php
            $confidence_pct = number_format($row['ai_confidence'] * 100, 0);
            $confidence_class = $confidence_pct >= 60 ? 'confidence-medium' : 'confidence-low';
            ?>
            <div class="submission-card">
                <div class="submission-image">
                    <img src="../<?php echo htmlspecialchars($row['image_url']); ?>" alt="Waste Submission">
                    <div class="confidence-badge <?php echo $confidence_class; ?>">
                        <i class="fas fa-brain"></i> <?php echo $confidence_pct; ?>%
                    </div>
                </div>

                <div class="submission-details">
                    <div class="submission-meta">
                        <span class="submission-id">#<?php echo $row['submission_id']; ?></span>
                        <span class="submission-time">
                            <i class="fas fa-clock"></i>
                            <?php echo date('M d, H:i', strtotime($row['created_at'])); ?>
                        </span>
                    </div>

                    <h3 class="submission-material">
                        <?php echo htmlspecialchars($row['material_name'] ?? 'Unclassified'); ?>
                    </h3>

                    <div class="submission-user">
                        <i class="fas fa-user"></i>
                        <strong><?php echo htmlspecialchars($row['username']); ?></strong>
                        <span class="user-id">(ID: <?php echo $row['user_id']; ?>)</span>
                    </div>
                </div>

                <div class="submission-actions">
                    <button onclick='openReviewModal(<?php echo json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'
                        class="btn btn-primary">
                        <i class="fas fa-search-plus"></i> Review
                    </button>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3>All Caught Up!</h3>
            <p>No pending reviews that need your attention. Great work!</p>
        </div>
    <?php endif; ?>
</div>

<!-- Review Modal -->
<div id="reviewModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-clipboard-check"></i> Review Submission <span id="modalId"></span></h2>
            <button onclick="closeReviewModal()" class="btn-close">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="modal-body">
            <!-- Preview Image -->
            <div class="review-image-container">
                <img id="reviewImage" src="" alt="Waste Image" class="review-image">
                <div id="reviewConfidence" class="review-confidence"></div>
            </div>

            <!-- Submission Info -->
            <div class="review-info">
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-user"></i> Submitted by:</span>
                    <span class="info-value" id="reviewUser"></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-clock"></i> Submitted at:</span>
                    <span class="info-value" id="reviewTime"></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-brain"></i> AI Detection:</span>
                    <span class="info-value" id="reviewMaterial"></span>
                </div>
            </div>

            <form action="process_review.php" method="POST" id="reviewForm">
                <input type="hidden" name="submission_id" id="formSubmissionId">
                <input type="hidden" name="user_id" id="formUserId">

                <!-- Material Selection -->
                <div class="form-group">
                    <label for="materialSelect">
                        <i class="fas fa-recycle"></i> Correct Material Classification
                    </label>
                    <select name="material_id" id="materialSelect" class="form-control">
                        <?php foreach ($materials as $mat): ?>
                            <option value="<?php echo $mat['material_id']; ?>">
                                <?php echo htmlspecialchars($mat['material_name']); ?>
                                (<?php echo $mat['points_per_item']; ?> pts)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-hint">Select the correct material if AI detection was incorrect.</small>
                </div>

                <!-- Action Buttons -->
                <div class="form-actions">
                    <button type="button" onclick="showRejectForm()" class="btn btn-danger">
                        <i class="fas fa-times-circle"></i> Reject
                    </button>
                    <button type="submit" name="action" value="approve" class="btn btn-success">
                        <i class="fas fa-check-circle"></i> Approve
                    </button>
                </div>
            </form>

            <!-- Reject Feedback Form (Hidden Initially) -->
            <div id="rejectFormContainer" style="display: none;">
                <div class="reject-divider"></div>
                <form action="process_review.php" method="POST">
                    <input type="hidden" name="submission_id" id="rejectSubmissionId">
                    <input type="hidden" name="user_id" id="rejectUserId">
                    <input type="hidden" name="action" value="reject">

                    <h3 class="reject-title">
                        <i class="fas fa-comment-dots"></i> Rejection Feedback
                    </h3>
                    <p class="reject-description">Please provide feedback to help the recycler improve.</p>

                    <div class="form-group">
                        <label for="rejectReason">Reason for Rejection *</label>
                        <select name="reject_reason" id="rejectReason" class="form-control" required>
                            <option value="">-- Select a reason --</option>
                            <option value="Not Recyclable">❌ Item is not recyclable</option>
                            <option value="Contaminated">⚠️ Item is contaminated or dirty</option>
                            <option value="Wrong Category">🔄 Wrong material category</option>
                            <option value="Poor Image Quality">📷 Image quality too poor to verify</option>
                            <option value="Non-Waste Item">🚫 Not a waste item</option>
                            <option value="Duplicate Submission">📋 Duplicate submission</option>
                            <option value="Other">💬 Other reason (specify below)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="rejectFeedback">Additional Feedback *</label>
                        <textarea name="reject_feedback" id="rejectFeedback" class="form-control" rows="4"
                            placeholder="Provide helpful feedback to guide the recycler..." required></textarea>
                        <small class="form-hint">
                            <i class="fas fa-lightbulb"></i> Tip: Be constructive and educational!
                        </small>
                    </div>

                    <div class="form-actions">
                        <button type="button" onclick="hideRejectForm()" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-paper-plane"></i> Send Rejection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* Review Grid */
    .review-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: var(--space-6);
    }

    /* Submission Card */
    .submission-card {
        background: var(--color-white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
    }

    .submission-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .submission-image {
        height: 220px;
        background: var(--color-gray-100);
        position: relative;
        overflow: hidden;
    }

    .submission-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .confidence-badge {
        position: absolute;
        top: var(--space-3);
        right: var(--space-3);
        padding: var(--space-2) var(--space-3);
        border-radius: var(--radius-md);
        font-size: var(--text-xs);
        font-weight: 600;
        backdrop-filter: blur(10px);
    }

    .confidence-medium {
        background: rgba(237, 137, 54, 0.9);
        color: white;
    }

    .confidence-low {
        background: rgba(252, 129, 129, 0.9);
        color: white;
    }

    .submission-details {
        padding: var(--space-5);
        flex-grow: 1;
    }

    .submission-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--space-3);
        font-size: var(--text-xs);
    }

    .submission-id {
        font-weight: 700;
        color: var(--color-primary);
        background: var(--color-success-light);
        padding: var(--space-1) var(--space-2);
        border-radius: var(--radius-sm);
    }

    .submission-time {
        color: var(--color-gray-500);
    }

    .submission-material {
        font-size: var(--text-lg);
        font-weight: 600;
        color: var(--color-gray-900);
        margin: 0 0 var(--space-2) 0;
    }

    .submission-user {
        font-size: var(--text-sm);
        color: var(--color-gray-600);
        display: flex;
        align-items: center;
        gap: var(--space-2);
    }

    .user-id {
        font-size: var(--text-xs);
        color: var(--color-gray-500);
    }

    .submission-actions {
        padding: var(--space-4);
        border-top: 1px solid var(--color-gray-100);
        background: var(--color-gray-50);
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        z-index: 2000;
        align-items: center;
        justify-content: center;
        overflow-y: auto;
        padding: var(--space-4);
    }

    .modal-content {
        background: var(--color-white);
        width: 100%;
        max-width: 700px;
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-xl);
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: var(--space-5) var(--space-6);
        border-bottom: 2px solid var(--color-gray-200);
        background: var(--color-gray-50);
    }

    .modal-header h2 {
        margin: 0;
        font-size: var(--text-xl);
        color: var(--color-gray-800);
    }

    .btn-close {
        background: none;
        border: none;
        font-size: var(--text-2xl);
        color: var(--color-gray-500);
        cursor: pointer;
        padding: var(--space-2);
        transition: all 0.3s ease;
    }

    .btn-close:hover {
        color: var(--color-error);
        transform: rotate(90deg);
    }

    .modal-body {
        padding: var(--space-6);
    }

    .review-image-container {
        position: relative;
        margin-bottom: var(--space-5);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-md);
    }

    .review-image {
        width: 100%;
        max-height: 400px;
        object-fit: contain;
        background: var(--color-gray-900);
    }

    .review-confidence {
        position: absolute;
        top: var(--space-3);
        right: var(--space-3);
        padding: var(--space-2) var(--space-4);
        border-radius: var(--radius-md);
        font-weight: 600;
        backdrop-filter: blur(10px);
    }

    .review-info {
        background: var(--color-info-light);
        border-left: 4px solid var(--color-info);
        padding: var(--space-4);
        border-radius: var(--radius-md);
        margin-bottom: var(--space-5);
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: var(--space-2) 0;
        border-bottom: 1px solid rgba(66, 153, 225, 0.2);
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: var(--color-gray-700);
        font-size: var(--text-sm);
    }

    .info-value {
        color: var(--color-gray-800);
        font-weight: 500;
    }

    .form-group {
        margin-bottom: var(--space-5);
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: var(--color-gray-700);
        margin-bottom: var(--space-2);
        font-size: var(--text-sm);
    }

    .form-control {
        width: 100%;
        padding: var(--space-3);
        border: 2px solid var(--color-gray-300);
        border-radius: var(--radius-md);
        font-size: var(--text-base);
        transition: all 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(45, 93, 63, 0.1);
    }

    .form-hint {
        display: block;
        margin-top: var(--space-2);
        font-size: var(--text-xs);
        color: var(--color-gray-500);
    }

    .form-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--space-3);
        margin-top: var(--space-6);
    }

    .btn {
        padding: var(--space-3) var(--space-5);
        border: none;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: var(--text-base);
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: var(--space-2);
    }

    .btn-primary {
        background: var(--color-primary);
        color: white;
        width: 100%;
    }

    .btn-primary:hover {
        background: var(--color-primary-light);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .btn-success {
        background: var(--color-success);
        color: white;
    }

    .btn-success:hover {
        background: #38A169;
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .btn-danger {
        background: var(--color-white);
        color: var(--color-error);
        border: 2px solid var(--color-error);
    }

    .btn-danger:hover {
        background: var(--color-error);
        color: white;
    }

    .btn-secondary {
        background: var(--color-gray-200);
        color: var(--color-gray-700);
    }

    .btn-secondary:hover {
        background: var(--color-gray-300);
    }

    /* Reject Form */
    .reject-divider {
        height: 2px;
        background: var(--color-gray-200);
        margin: var(--space-6) 0;
    }

    .reject-title {
        color: var(--color-error);
        font-size: var(--text-lg);
        margin-bottom: var(--space-2);
    }

    .reject-description {
        color: var(--color-gray-600);
        margin-bottom: var(--space-5);
    }

    /* Alert Messages */
    .alert {
        padding: var(--space-4) var(--space-5);
        border-radius: var(--radius-md);
        margin-bottom: var(--space-6);
        display: flex;
        align-items: center;
        gap: var(--space-3);
        font-weight: 500;
    }

    .alert-success {
        background: var(--color-success-light);
        color: var(--color-success);
        border-left: 4px solid var(--color-success);
    }

    .alert-error {
        background: var(--color-error-light);
        color: var(--color-error);
        border-left: 4px solid var(--color-error);
    }

    /* Empty State */
    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: var(--space-12);
        background: white;
        border-radius: var(--radius-lg);
    }

    .empty-icon {
        font-size: 64px;
        color: var(--color-success);
        opacity: 0.4;
        margin-bottom: var(--space-4);
    }

    .empty-state h3 {
        font-size: var(--text-2xl);
        color: var(--color-gray-700);
        margin-bottom: var(--space-2);
    }

    .empty-state p {
        color: var(--color-gray-500);
        font-size: var(--text-base);
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .review-grid {
            grid-template-columns: 1fr;
        }

        .modal-content {
            max-width: 100%;
            margin: 0;
            border-radius: 0;
            max-height: 100vh;
        }

        .form-actions {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    let currentSubmissionData = null;

    function openReviewModal(data) {
        currentSubmissionData = data;

        document.getElementById('reviewModal').style.display = 'flex';
        document.getElementById('modalId').textContent = '#' + data.submission_id;
        document.getElementById('formSubmissionId').value = data.submission_id;
        document.getElementById('formUserId').value = data.user_id;
        document.getElementById('rejectSubmissionId').value = data.submission_id;
        document.getElementById('rejectUserId').value = data.user_id;

        // Set image
        document.getElementById('reviewImage').src = '../' + data.image_url;

        // Set confidence badge
        const confidencePct = Math.round(data.ai_confidence * 100);
        const confBadge = document.getElementById('reviewConfidence');
        confBadge.textContent = '🧠 AI Confidence: ' + confidencePct + '%';
        confBadge.className = 'review-confidence ' + (confidencePct >= 60 ? 'confidence-medium' : 'confidence-low');

        // Set info
        document.getElementById('reviewUser').textContent = data.username + ' (ID: ' + data.user_id + ')';
        document.getElementById('reviewTime').textContent = new Date(data.created_at).toLocaleString();
        document.getElementById('reviewMaterial').textContent = data.material_name || 'Unclassified';

        // Select material in dropdown
        const select = document.getElementById('materialSelect');
        if (data.material_id) {
            select.value = data.material_id;
        }

        // Hide reject form initially
        document.getElementById('rejectFormContainer').style.display = 'none';
        document.getElementById('reviewForm').style.display = 'block';
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').style.display = 'none';
        currentSubmissionData = null;
    }

    function showRejectForm() {
        document.getElementById('reviewForm').style.display = 'none';
        document.getElementById('rejectFormContainer').style.display = 'block';
    }

    function hideRejectForm() {
        document.getElementById('rejectFormContainer').style.display = 'none';
        document.getElementById('reviewForm').style.display = 'block';
    }

    // Close on outside click
    document.getElementById('reviewModal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeReviewModal();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeReviewModal();
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>