<?php
require_once '../php/config.php';

$conn = getDBConnection();

$success_message = '';
$error_message = '';
$is_edit = false;
$material = null;

// Check if editing existing material
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $is_edit = true;
    $material_id = (int) $_GET['id'];

    $material_query = "SELECT * FROM material WHERE material_id = ?";
    $stmt = mysqli_prepare($conn, $material_query);
    mysqli_stmt_bind_param($stmt, "i", $material_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $material = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$material) {
        header('Location: materials.php');
        exit();
    }
}

$page_title = $is_edit ? 'Edit Material' : 'Create New Material';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $material_name = trim($_POST['material_name']);
    $points_per_item = intval($_POST['points_per_item']);

    // Validation
    if (empty($material_name)) {
        $error_message = "Please enter a material name.";
    } elseif ($points_per_item <= 0) {
        $error_message = "Points per item must be greater than 0.";
    } else {
        if ($is_edit) {
            // Update existing material
            $update_query = "UPDATE material SET material_name = ?, points_per_item = ? WHERE material_id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "sii", $material_name, $points_per_item, $material_id);

            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Material updated successfully!";
                // Refresh material data
                $material['material_name'] = $material_name;
                $material['points_per_item'] = $points_per_item;
            } else {
                $error_message = "Error updating material: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        } else {
            // Insert new material
            $insert_query = "INSERT INTO material (material_name, points_per_item) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, "si", $material_name, $points_per_item);

            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Material created successfully!";
                // Clear form
                $material_name = '';
                $points_per_item = 10;
            } else {
                $error_message = "Error creating material: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Include admin header
include 'includes/header.php';
?>

<style>
    .form-container {
        background: var(--color-white);
        padding: var(--space-8);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        max-width: 800px;
        margin: 0 auto;
    }

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

    .btn-secondary {
        background: var(--color-gray-200);
        color: var(--color-gray-700);
    }

    .btn-secondary:hover {
        background: var(--color-gray-300);
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

    .form-group {
        margin-bottom: var(--space-6);
    }

    .form-group label {
        display: block;
        margin-bottom: var(--space-2);
        color: var(--color-gray-800);
        font-weight: 600;
        font-size: var(--text-base);
    }

    .form-group label.required::after {
        content: ' *';
        color: var(--color-error);
    }

    .form-group input[type="text"],
    .form-group input[type="number"] {
        width: 100%;
        padding: var(--space-3);
        border: 2px solid var(--color-gray-300);
        border-radius: var(--radius-md);
        font-size: var(--text-base);
        font-family: var(--font-sans);
        transition: border-color 0.3s ease;
    }

    .form-group input:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(45, 93, 63, 0.1);
    }

    .form-group small {
        display: block;
        margin-top: var(--space-2);
        color: var(--color-gray-600);
        font-size: var(--text-sm);
    }

    .form-actions {
        display: flex;
        gap: var(--space-4);
        margin-top: var(--space-8);
        padding-top: var(--space-6);
        border-top: 2px solid var(--color-gray-200);
    }

    .info-box {
        background: var(--color-info-light);
        border-left: 4px solid var(--color-info);
        padding: var(--space-4);
        border-radius: var(--radius-md);
        margin-bottom: var(--space-6);
    }

    .info-box p {
        margin: 0;
        color: #1E40AF;
        font-size: var(--text-sm);
        display: flex;
        align-items: flex-start;
        gap: var(--space-2);
    }

    .info-box i {
        margin-top: 2px;
    }

    .examples-box {
        background: var(--color-success-light);
        border: 1px solid var(--color-success);
        border-radius: var(--radius-md);
        padding: var(--space-4);
        margin-bottom: var(--space-6);
    }

    .examples-box h4 {
        color: #065F46;
        margin: 0 0 var(--space-2) 0;
        font-size: var(--text-base);
    }

    .examples-box ul {
        margin: 0;
        padding-left: var(--space-5);
        color: #065F46;
        font-size: var(--text-sm);
    }

    .examples-box li {
        margin-bottom: var(--space-1);
    }

    @media (max-width: 768px) {
        .form-actions {
            flex-direction: column;
        }

        .form-container {
            padding: var(--space-4);
        }

        .page-header-actions {
            flex-direction: column;
            align-items: stretch;
            gap: var(--space-3);
        }
    }
</style>

<!-- Page Header -->
<div class="page-header-actions">
    <h2>
        <i class="fas fa-recycle"></i> <?php echo $is_edit ? 'Edit Material' : 'Create New Material'; ?>
    </h2>
    <a href="materials.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Materials
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

<div class="info-box">
    <p>
        <i class="fas fa-info-circle"></i>
        <span>Materials define what can be recycled and how many points each item is worth. This helps track recycling activity and award users appropriately.</span>
    </p>
</div>

<div class="examples-box">
    <h4><i class="fas fa-lightbulb"></i> Example Materials:</h4>
    <ul>
        <li><strong>Plastic Bottle</strong> - 5 points</li>
        <li><strong>Aluminum Can</strong> - 3 points</li>
        <li><strong>Glass Bottle</strong> - 4 points</li>
        <li><strong>Paper/Cardboard</strong> - 2 points</li>
        <li><strong>E-Waste</strong> - 15 points</li>
    </ul>
</div>

<div class="form-container">
    <form method="POST" action="">
        <!-- Material Name -->
        <div class="form-group">
            <label for="material_name" class="required">Material Name</label>
            <input type="text" id="material_name" name="material_name"
                value="<?php echo $material ? htmlspecialchars($material['material_name']) : (isset($material_name) ? htmlspecialchars($material_name) : ''); ?>"
                placeholder="e.g., Plastic Bottle, Aluminum Can, Glass Bottle" required>
            <small>Enter the type of recyclable material (be specific, e.g., "Plastic Bottle" not just "Plastic")</small>
        </div>

        <!-- Points Per Item -->
        <div class="form-group">
            <label for="points_per_item" class="required">Points Per Item</label>
            <input type="number" id="points_per_item" name="points_per_item"
                value="<?php echo $material ? $material['points_per_item'] : (isset($points_per_item) ? $points_per_item : '5'); ?>"
                min="1" max="1000" step="1" required>
            <small>
                <i class="fas fa-star"></i>
                How many points a recycler earns for each item of this material (typically 1-20 points)
            </small>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-check"></i> <?php echo $is_edit ? 'Update Material' : 'Create Material'; ?>
            </button>
            <a href="materials.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<script>
    // Form validation
    document.querySelector('form').addEventListener('submit', function (e) {
        const pointsPerItem = parseInt(document.getElementById('points_per_item').value);

        if (pointsPerItem <= 0) {
            e.preventDefault();
            alert('Points per item must be greater than 0!');
            return false;
        }

        if (pointsPerItem > 1000) {
            e.preventDefault();
            alert('Points per item cannot exceed 1000!');
            return false;
        }
    });
</script>

<?php
include 'includes/footer.php';
mysqli_close($conn);
?>
