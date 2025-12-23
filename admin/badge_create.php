<?php
require_once '../php/config.php';

$conn = getDBConnection();

$success_message = '';
$error_message = '';
$is_edit = false;
$badge = null;

// Check if editing existing badge
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $is_edit = true;
    $badge_id = (int) $_GET['id'];

    $badge_query = "SELECT * FROM badge WHERE badge_id = ?";
    $stmt = mysqli_prepare($conn, $badge_query);
    mysqli_stmt_bind_param($stmt, "i", $badge_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $badge = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$badge) {
        header('Location: badges.php');
        exit();
    }
}

$page_title = $is_edit ? 'Edit Badge' : 'Create New Badge';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $badge_name = trim($_POST['badge_name']);
    $description = trim($_POST['description']);
    $badge_type = $_POST['badge_type'];
    $point_required = intval($_POST['point_required']);

    // Validation
    if (empty($badge_name) || empty($description) || empty($badge_type)) {
        $error_message = "Please fill in all required fields.";
    } elseif ($point_required < 0) {
        $error_message = "Points required must be 0 or greater.";
    } elseif ($badge_type == 'milestone' && $point_required == 0) {
        $error_message = "Milestone badges must have points required greater than 0.";
    } elseif ($badge_type == 'challenge' && $point_required != 0) {
        $error_message = "Challenge badges must have points required set to 0.";
    } else {
        if ($is_edit) {
            // Update existing badge
            $update_query = "UPDATE badge SET badge_name = ?, badge_type = ?, point_required = ?, description = ? WHERE badge_id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "ssisi", $badge_name, $badge_type, $point_required, $description, $badge_id);

            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Badge updated successfully!";
                // Refresh badge data
                $badge['badge_name'] = $badge_name;
                $badge['badge_type'] = $badge_type;
                $badge['point_required'] = $point_required;
                $badge['description'] = $description;
            } else {
                $error_message = "Error updating badge: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        } else {
            // Insert new badge
            $insert_query = "INSERT INTO badge (badge_name, badge_type, point_required, description) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, "ssis", $badge_name, $badge_type, $point_required, $description);

            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Badge created successfully!";
                // Clear form
                $badge_name = $description = '';
                $badge_type = 'challenge';
                $point_required = 0;
            } else {
                $error_message = "Error creating badge: " . mysqli_error($conn);
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
    .form-group input[type="number"],
    .form-group textarea {
        width: 100%;
        padding: var(--space-3);
        border: 2px solid var(--color-gray-300);
        border-radius: var(--radius-md);
        font-size: var(--text-base);
        font-family: var(--font-sans);
        transition: border-color 0.3s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(45, 93, 63, 0.1);
    }

    .form-group textarea {
        min-height: 120px;
        resize: vertical;
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

    select {
        width: 100%;
        padding: var(--space-3);
        border: 2px solid var(--color-gray-300);
        border-radius: var(--radius-md);
        font-size: var(--text-base);
        font-family: var(--font-sans);
        transition: border-color 0.3s ease;
        background: white;
    }

    select:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(45, 93, 63, 0.1);
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
        <i class="fas fa-medal"></i> <?php echo $is_edit ? 'Edit Badge' : 'Create New Badge'; ?>
    </h2>
    <a href="badges.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Badges
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
        <span><strong>Milestone badges</strong> are auto-unlocked when users reach lifetime points. <strong>Challenge badges</strong> are only earned by completing specific challenges.</span>
    </p>
</div>

<div class="form-container">
    <form method="POST" action="">
        <!-- Badge Name -->
        <div class="form-group">
            <label for="badge_name" class="required">Badge Name</label>
            <input type="text" id="badge_name" name="badge_name"
                value="<?php echo $badge ? htmlspecialchars($badge['badge_name']) : (isset($badge_name) ? htmlspecialchars($badge_name) : ''); ?>"
                placeholder="e.g., Bronze Recycler, Plastic Free November Winner" required>
            <small>Choose a catchy, memorable name for this badge</small>
        </div>

        <!-- Badge Type -->
        <div class="form-group">
            <label for="badge_type" class="required">Badge Type</label>
            <select id="badge_type" name="badge_type" required>
                <option value="milestone" <?php echo ($badge && $badge['badge_type'] == 'milestone') || (isset($badge_type) && $badge_type == 'milestone') ? 'selected' : ''; ?>>
                    Milestone Badge (Long-term, auto-unlock at points)
                </option>
                <option value="challenge" <?php echo ($badge && $badge['badge_type'] == 'challenge') || (isset($badge_type) && $badge_type == 'challenge') || !$badge ? 'selected' : ''; ?>>
                    Challenge Badge (Short-term, only from challenges)
                </option>
            </select>
            <small>
                <i class="fas fa-trophy"></i>
                <strong>Milestone:</strong> Auto-unlocked at lifetime points | <strong>Challenge:</strong> Only from completing specific challenges
            </small>
        </div>

        <!-- Description -->
        <div class="form-group">
            <label for="description" class="required">Description</label>
            <textarea id="description" name="description"
                placeholder="Describe what this badge represents and how to earn it..."
                required><?php echo $badge ? htmlspecialchars($badge['description']) : (isset($description) ? htmlspecialchars($description) : ''); ?></textarea>
            <small>Explain what this badge means and what achievement it recognizes</small>
        </div>

        <!-- Point Requirement -->
        <div class="form-group" id="point-required-group">
            <label for="point_required" class="required">Points Required (for Milestone badges only)</label>
            <input type="number" id="point_required" name="point_required"
                value="<?php echo $badge ? $badge['point_required'] : (isset($point_required) ? $point_required : '100'); ?>"
                min="0" step="10" required>
            <small id="point-help-text">
                <i class="fas fa-star"></i>
                Number of lifetime points needed to auto-unlock this milestone badge (e.g., 100, 500, 1000)
            </small>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-check"></i> <?php echo $is_edit ? 'Update Badge' : 'Create Badge'; ?>
            </button>
            <a href="badges.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<script>
    // Dynamic form behavior based on badge type
    const badgeTypeSelect = document.getElementById('badge_type');
    const pointRequiredInput = document.getElementById('point_required');
    const pointRequiredGroup = document.getElementById('point-required-group');
    const pointHelpText = document.getElementById('point-help-text');

    function updatePointField() {
        const badgeType = badgeTypeSelect.value;

        if (badgeType === 'milestone') {
            pointRequiredInput.value = pointRequiredInput.value == '0' ? '100' : pointRequiredInput.value;
            pointRequiredInput.min = '1';
            pointHelpText.innerHTML = '<i class="fas fa-star"></i> Number of lifetime points needed to auto-unlock this milestone badge (e.g., 100, 500, 1000)';
        } else {
            pointRequiredInput.value = '0';
            pointRequiredInput.min = '0';
            pointHelpText.innerHTML = '<i class="fas fa-trophy"></i> Challenge badges are awarded when completing specific challenges (points should be 0)';
        }
    }

    badgeTypeSelect.addEventListener('change', updatePointField);
    updatePointField(); // Run on page load

    // Form validation
    document.querySelector('form').addEventListener('submit', function (e) {
        const badgeType = badgeTypeSelect.value;
        const pointRequired = parseInt(pointRequiredInput.value);

        if (pointRequired < 0) {
            e.preventDefault();
            alert('Points required must be 0 or greater!');
            return false;
        }

        if (badgeType === 'milestone' && pointRequired === 0) {
            e.preventDefault();
            alert('Milestone badges must have points required greater than 0!');
            return false;
        }

        if (badgeType === 'challenge' && pointRequired !== 0) {
            e.preventDefault();
            alert('Challenge badges must have points required set to 0!');
            return false;
        }
    });
</script>

<?php
include 'includes/footer.php';
mysqli_close($conn);
?>
