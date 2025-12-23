<?php
require_once '../php/config.php';

$page_title = 'Material Management';
$conn = getDBConnection();

$success_message = '';
$error_message = '';

// Handle delete material
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $material_id = (int) $_GET['delete'];

    // Check if material is used in challenges
    $check_challenge_query = "SELECT COUNT(*) as count FROM challenge WHERE target_material_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_challenge_query);
    mysqli_stmt_bind_param($check_stmt, "i", $material_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $check_row = mysqli_fetch_assoc($check_result);
    mysqli_stmt_close($check_stmt);

    if ($check_row['count'] > 0) {
        $error_message = "Cannot delete this material. It is currently used in {$check_row['count']} challenge(s).";
    } else {
        // Check if material is used in submissions
        $check_submission_query = "SELECT COUNT(*) as count FROM submission_material WHERE material_id = ?";
        $check_stmt2 = mysqli_prepare($conn, $check_submission_query);
        mysqli_stmt_bind_param($check_stmt2, "i", $material_id);
        mysqli_stmt_execute($check_stmt2);
        $check_result2 = mysqli_stmt_get_result($check_stmt2);
        $check_row2 = mysqli_fetch_assoc($check_result2);
        mysqli_stmt_close($check_stmt2);

        if ($check_row2['count'] > 0) {
            $error_message = "Cannot delete this material. It is used in {$check_row2['count']} recycling submission(s).";
        } else {
            // Delete the material
            $delete_stmt = mysqli_prepare($conn, "DELETE FROM material WHERE material_id = ?");
            mysqli_stmt_bind_param($delete_stmt, "i", $material_id);

            if (mysqli_stmt_execute($delete_stmt)) {
                $success_message = "Material deleted successfully!";
            } else {
                $error_message = "Error deleting material: " . mysqli_error($conn);
            }
            mysqli_stmt_close($delete_stmt);
        }
    }
}

// Fetch all materials
$materials_query = "SELECT m.*,
                    COUNT(DISTINCT sm.submission_id) as submission_count,
                    COUNT(DISTINCT c.challenge_id) as used_in_challenges
                    FROM material m
                    LEFT JOIN submission_material sm ON m.material_id = sm.material_id
                    LEFT JOIN challenge c ON m.material_id = c.target_material_id
                    GROUP BY m.material_id
                    ORDER BY m.material_name ASC";
$materials_result = mysqli_query($conn, $materials_query);

// Get total count
$total_materials = mysqli_num_rows($materials_result);

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
        background: var(--gradient-success);
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

    .materials-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: var(--space-6);
    }

    .material-card {
        background: var(--color-white);
        border: 2px solid var(--color-gray-200);
        border-radius: var(--radius-lg);
        padding: var(--space-6);
        transition: all 0.3s ease;
    }

    .material-card:hover {
        border-color: var(--color-secondary);
        box-shadow: var(--shadow-lg);
        transform: translateY(-4px);
    }

    .material-card-header {
        margin-bottom: var(--space-4);
    }

    .material-icon {
        width: 80px;
        height: 80px;
        background: var(--gradient-success);
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin: 0 auto var(--space-4) auto;
        box-shadow: var(--shadow-md);
    }

    .material-title {
        font-size: var(--text-xl);
        font-weight: 700;
        color: var(--color-gray-800);
        margin: 0 0 var(--space-2) 0;
        text-align: center;
    }

    .material-meta {
        display: grid;
        gap: var(--space-3);
        margin-bottom: var(--space-4);
        padding: var(--space-4);
        background: var(--color-gray-100);
        border-radius: var(--radius-md);
    }

    .material-meta-item {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        font-size: var(--text-sm);
    }

    .material-meta-item i {
        color: var(--color-secondary);
        width: 16px;
    }

    .material-meta-item strong {
        color: var(--color-gray-700);
        min-width: 110px;
    }

    .material-meta-item span {
        color: var(--color-gray-800);
    }

    .material-actions {
        display: flex;
        gap: var(--space-3);
    }

    .points-badge {
        background: var(--color-success-light);
        color: #065F46;
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

        .materials-grid {
            grid-template-columns: 1fr;
        }

        .material-actions {
            flex-direction: column;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header-actions">
    <h2>
        <i class="fas fa-recycle"></i> Material Management
    </h2>
    <a href="material_create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create New Material
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
    <h3><?php echo $total_materials; ?></h3>
    <p>Total Recyclable Materials</p>
</div>

<!-- Materials Grid -->
<?php if ($total_materials > 0): ?>
    <div class="materials-grid">
        <?php while ($material = mysqli_fetch_assoc($materials_result)): ?>
            <div class="material-card">
                <div class="material-card-header">
                    <div class="material-icon">
                        <i class="fas fa-recycle"></i>
                    </div>
                    <h3 class="material-title"><?php echo htmlspecialchars($material['material_name']); ?></h3>
                </div>

                <div class="material-meta">
                    <div class="material-meta-item">
                        <i class="fas fa-star"></i>
                        <strong>Points/Item:</strong>
                        <span class="points-badge"><?php echo number_format($material['points_per_item']); ?> pts</span>
                    </div>
                    <div class="material-meta-item">
                        <i class="fas fa-box"></i>
                        <strong>Submissions:</strong>
                        <span><?php echo $material['submission_count']; ?> times</span>
                    </div>
                    <div class="material-meta-item">
                        <i class="fas fa-trophy"></i>
                        <strong>In Challenges:</strong>
                        <span><?php echo $material['used_in_challenges']; ?> challenges</span>
                    </div>
                </div>

                <div class="material-actions">
                    <a href="material_create.php?id=<?php echo $material['material_id']; ?>" class="btn btn-edit">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="?delete=<?php echo $material['material_id']; ?>" class="btn btn-delete"
                        onclick="return confirm('Are you sure you want to delete this material?');">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-recycle"></i></div>
        <h3>No Materials Yet</h3>
        <p>Create recyclable materials to track recycling submissions and award points!</p>
        <a href="material_create.php" class="btn btn-primary" style="margin-top: var(--space-4);">
            <i class="fas fa-plus"></i> Create First Material
        </a>
    </div>
<?php endif; ?>

<?php
include 'includes/footer.php';
mysqli_close($conn);
?>
