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

// Fetch Pending Submissions
$sql = "SELECT s.submission_id, s.image_url, s.ai_confidence, s.created_at, u.username, m.material_id, m.material_name 
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
    <h1 class="page-title">Review Queue</h1>
    <p class="page-description">Review and process pending recycling submissions.</p>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div
        style="background: #ECFDF5; color: #065F46; padding: var(--space-4); border-radius: var(--radius-md); margin-bottom: var(--space-6); border: 1px solid #A7F3D0;">
        <?php echo htmlspecialchars($_GET['msg']); ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: var(--space-6);">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="card"
                style="background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); overflow: hidden; display: flex; flex-direction: column;">
                <div style="height: 200px; overflow: hidden; background: #f3f4f6; position: relative;">
                    <img src="../<?php echo htmlspecialchars($row['image_url']); ?>" alt="Waste Image"
                        style="width: 100%; height: 100%; object-fit: cover;">
                    <div
                        style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                        <?php echo number_format($row['ai_confidence'] * 100, 0); ?>% Conf.
                    </div>
                </div>
                <div style="padding: var(--space-5); flex-grow: 1;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-2);">
                        <span style="font-size: var(--text-xs); color: var(--color-gray-500); font-weight: 600;">ID:
                            #<?php echo $row['submission_id']; ?></span>
                        <span
                            style="font-size: var(--text-xs); color: var(--color-gray-500);"><?php echo date('M d, H:i', strtotime($row['created_at'])); ?></span>
                    </div>

                    <h3 style="font-size: var(--text-lg); margin: 0 0 var(--space-1) 0; color: var(--color-gray-900);">
                        <?php echo htmlspecialchars($row['material_name'] ?? 'Unclassified'); ?>
                    </h3>
                    <p style="font-size: var(--text-sm); color: var(--color-gray-600); margin: 0;">
                        User: <strong><?php echo htmlspecialchars($row['username']); ?></strong>
                    </p>
                </div>
                <div
                    style="padding: var(--space-5); border-top: 1px solid var(--color-gray-100); background: var(--color-gray-50); display: flex; gap: var(--space-2);">
                    <button onclick="openReviewModal(<?php echo htmlspecialchars(json_encode($row)); ?>)"
                        style="flex: 1; padding: var(--space-2); background: var(--color-primary); color: white; border: none; border-radius: var(--radius-md); font-weight: 500; cursor: pointer;">
                        Review
                    </button>
                    <!-- Quick Reject -->
                    <!-- <a href="process_review.php?action=reject&id=<?php echo $row['submission_id']; ?>" onclick="return confirm('Reject this submission?')" style="padding: var(--space-2) var(--space-3); background: white; border: 1px solid var(--color-gray-300); color: var(--color-gray-700); border-radius: var(--radius-md); text-decoration: none; display: flex; align-items: center justify-content: center;">
                        <i class="fas fa-times"></i>
                    </a> -->
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div
            style="grid-column: 1 / -1; text-align: center; padding: var(--space-12); background: white; border-radius: var(--radius-lg); color: var(--color-gray-500);">
            <i class="fas fa-check-circle"
                style="font-size: 48px; color: var(--color-success); margin-bottom: var(--space-4); opacity: 0.5;"></i>
            <p>All caught up! No pending reviews.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Review Modal -->
<div id="reviewModal"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center;">
    <div
        style="background: white; width: 90%; max-width: 500px; border-radius: var(--radius-lg); box-shadow: var(--shadow-xl); overflow: hidden;">
        <div
            style="padding: var(--space-4) var(--space-6); border-bottom: 1px solid var(--color-gray-200); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: var(--text-lg);">Review Submission #<span id="modalId"></span></h3>
            <button onclick="closeReviewModal()"
                style="background: none; border: none; font-size: var(--text-lg); cursor: pointer; color: var(--color-gray-500);">&times;</button>
        </div>

        <form action="process_review.php" method="POST" style="padding: var(--space-6);">
            <input type="hidden" name="submission_id" id="formId">

            <div style="margin-bottom: var(--space-4);">
                <label
                    style="display: block; font-size: var(--text-sm); font-weight: 500; color: var(--color-gray-700); margin-bottom: var(--space-1);">Detected
                    Material</label>
                <div id="modalMaterial"
                    style="padding: var(--space-3); background: var(--color-gray-100); border-radius: var(--radius-md); font-weight: 600;">
                </div>
            </div>

            <div style="margin-bottom: var(--space-6);">
                <label for="correct_material"
                    style="display: block; font-size: var(--text-sm); font-weight: 500; color: var(--color-gray-700); margin-bottom: var(--space-1);">Correct
                    Material (if typical)</label>
                <select name="material_id" id="materialSelect"
                    style="width: 100%; padding: var(--space-2); border: 1px solid var(--color-gray-300); border-radius: var(--radius-md);">
                    <?php foreach ($materials as $mat): ?>
                        <option value="<?php echo $mat['material_id']; ?>">
                            <?php echo htmlspecialchars($mat['material_name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <p style="font-size: var(--text-xs); color: var(--color-gray-500); margin-top: var(--space-1);">Change
                    this only if AI was wrong.</p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
                <button type="submit" name="action" value="reject"
                    style="padding: var(--space-3); background: var(--color-white); border: 1px solid var(--color-error); color: var(--color-error); border-radius: var(--radius-md); font-weight: 600; cursor: pointer;">
                    Reject
                </button>
                <button type="submit" name="action" value="approve"
                    style="padding: var(--space-3); background: var(--color-success); border: none; color: white; border-radius: var(--radius-md); font-weight: 600; cursor: pointer;">
                    Approve
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openReviewModal(data) {
        document.getElementById('reviewModal').style.display = 'flex';
        document.getElementById('modalId').textContent = data.submission_id;
        document.getElementById('formId').value = data.submission_id;
        document.getElementById('modalMaterial').textContent = data.material_name || 'Unclassified';

        // Select the current material in dropdown if exists
        const select = document.getElementById('materialSelect');
        if (data.material_id) {
            select.value = data.material_id;
        }
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').style.display = 'none';
    }

    // Close on outside click
    document.getElementById('reviewModal').addEventListener('click', function (e) {
        if (e.target === this) closeReviewModal();
    });
</script>

<?php require_once 'includes/footer.php'; ?>