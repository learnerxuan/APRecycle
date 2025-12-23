<?php
require_once '../php/config.php';

$page_title = 'Create New Challenge';
$conn = getDBConnection();

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $point_multiplier = floatval($_POST['point_multiplier']);
    $badge_id = !empty($_POST['badge_id']) ? intval($_POST['badge_id']) : null;
    $reward_id = !empty($_POST['reward_id']) ? intval($_POST['reward_id']) : null;
    $target_material_id = !empty($_POST['target_material_id']) ? intval($_POST['target_material_id']) : null;

    // New completion criteria fields
    $completion_type = $_POST['completion_type'];
    $target_quantity = intval($_POST['target_quantity']);
    $target_points = intval($_POST['target_points']);

    // Validation
    if (empty($title) || empty($description) || empty($start_date) || empty($end_date)) {
        $error_message = "Please fill in all required fields.";
    } elseif (strtotime($start_date) >= strtotime($end_date)) {
        $error_message = "End date must be after start date.";
    } elseif ($point_multiplier <= 0) {
        $error_message = "Point multiplier must be greater than 0.";
    } elseif ($completion_type == 'quantity' && $target_quantity <= 0) {
        $error_message = "Target quantity must be greater than 0 for quantity-based challenges.";
    } elseif ($completion_type == 'points' && $target_points <= 0) {
        $error_message = "Target points must be greater than 0 for points-based challenges.";
    } else {
        // Insert challenge
        $insert_query = "INSERT INTO challenge (title, description, start_date, end_date, point_multiplier, badge_id, reward_id, target_material_id, target_quantity, target_points, completion_type, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "ssssdiiiiis", $title, $description, $start_date, $end_date, $point_multiplier, $badge_id, $reward_id, $target_material_id, $target_quantity, $target_points, $completion_type);

        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Challenge created successfully!";
            // Clear form
            $title = $description = $start_date = $end_date = '';
            $point_multiplier = 1.0;
            $badge_id = $reward_id = $target_material_id = null;
            $target_quantity = $target_points = 0;
            $completion_type = 'quantity';
        } else {
            $error_message = "Error creating challenge: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch available badges
$badges_query = "SELECT badge_id, badge_name, description FROM badge ORDER BY badge_name";
$badges_result = mysqli_query($conn, $badges_query);

// Fetch available rewards
$rewards_query = "SELECT reward_id, reward_name, description FROM reward ORDER BY reward_name";
$rewards_result = mysqli_query($conn, $rewards_query);

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
    .form-group input[type="date"],
    .form-group input[type="number"],
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: var(--space-3);
        border: 2px solid var(--color-gray-300);
        border-radius: var(--radius-md);
        font-size: var(--text-base);
        font-family: var(--font-sans);
        transition: border-color 0.3s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
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

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--space-6);
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

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }

        .form-container {
            padding: var(--space-4);
        }
    }
</style>

<!-- Page Header -->
<div class="page-header-actions">
    <h2>
        <i class="fas fa-plus-circle"></i> Create New Challenge
    </h2>
    <a href="challenges.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Challenges
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
        <span>Create engaging challenges to motivate recyclers. Set dates, point multipliers, and optional
            badges/rewards to make recycling more fun!</span>
    </p>
</div>

<div class="form-container">
    <form method="POST" action="">
        <!-- Challenge Title -->
        <div class="form-group">
            <label for="title" class="required">Challenge Title</label>
            <input type="text" id="title" name="title"
                value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>"
                placeholder="e.g., Earth Day Recycling Challenge" required>
            <small>Choose a catchy, descriptive name for your challenge</small>
        </div>

        <!-- Challenge Description -->
        <div class="form-group">
            <label for="description" class="required">Description</label>
            <textarea id="description" name="description"
                placeholder="Describe the challenge goals, rules, and what participants need to do..."
                required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
            <small>Provide clear instructions and motivation for participants</small>
        </div>

        <!-- Date Range -->
        <div class="form-row">
            <div class="form-group">
                <label for="start_date" class="required">Start Date</label>
                <input type="date" id="start_date" name="start_date"
                    value="<?php echo isset($start_date) ? $start_date : date('Y-m-d'); ?>"
                    min="<?php echo date('Y-m-d'); ?>" required>
                <small>When the challenge begins</small>
            </div>

            <div class="form-group">
                <label for="end_date" class="required">End Date</label>
                <input type="date" id="end_date" name="end_date"
                    value="<?php echo isset($end_date) ? $end_date : ''; ?>"
                    min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                <small>When the challenge ends</small>
            </div>
        </div>

        <!-- Point Multiplier -->
        <div class="form-group">
            <label for="point_multiplier" class="required">Point Multiplier</label>
            <input type="number" id="point_multiplier" name="point_multiplier"
                value="<?php echo isset($point_multiplier) ? $point_multiplier : '1.0'; ?>" min="0.1" max="10"
                step="0.1" required>
            <small>
                <i class="fas fa-bolt"></i>
                Multiply recycling points during this challenge (e.g., 2.0 = double points, 1.5 = 50% bonus)
            </small>
        </div>

        <!-- Badge Selection (Optional) -->
        <div class="form-group">
            <label for="badge_id">Badge Reward (Optional)</label>
            <select id="badge_id" name="badge_id">
                <option value="">-- No Badge --</option>
                <?php while ($badge = mysqli_fetch_assoc($badges_result)): ?>
                    <option value="<?php echo $badge['badge_id']; ?>" <?php echo (isset($badge_id) && $badge_id == $badge['badge_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($badge['badge_name']); ?>
                        <?php if ($badge['description']): ?>
                            - <?php echo htmlspecialchars($badge['description']); ?>
                        <?php endif; ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <small>
                <i class="fas fa-medal"></i>
                Award a badge to participants who complete the challenge
            </small>
        </div>

        <!-- Reward Selection (Optional) -->
        <div class="form-group">
            <label for="reward_id">Reward (Optional)</label>
            <select id="reward_id" name="reward_id">
                <option value="">-- No Reward --</option>
                <?php while ($reward = mysqli_fetch_assoc($rewards_result)): ?>
                    <option value="<?php echo $reward['reward_id']; ?>" <?php echo (isset($reward_id) && $reward_id == $reward['reward_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($reward['reward_name']); ?>
                        <?php if ($reward['description']): ?>
                            - <?php echo htmlspecialchars($reward['description']); ?>
                        <?php endif; ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <small>
                <i class="fas fa-gift"></i>
                Offer a reward for challenge completion
            </small>
        </div>

        <!-- Target Material Selection (Smart Challenge Logic) -->
        <div class="form-group">
            <label for="target_material_id">Target Material (Smart Challenge)</label>
            <select id="target_material_id" name="target_material_id">
                <option value="">-- All Materials (Generic Challenge) --</option>
                <?php
                $materials_query = "SELECT material_id, material_name FROM material ORDER BY material_name";
                $materials_result = mysqli_query($conn, $materials_query);
                while ($material = mysqli_fetch_assoc($materials_result)):
                    ?>
                    <option value="<?php echo $material['material_id']; ?>" <?php echo (isset($_POST['target_material_id']) && $_POST['target_material_id'] == $material['material_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($material['material_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <small>
                <i class="fas fa-recycle"></i>
                Select a specific material for this challenge (e.g., "Plastic" for a plastic-free challenge). Leave
                empty for generic challenges.
            </small>
        </div>

        <!-- Completion Criteria Section -->
        <div style="border-top: 3px solid var(--color-primary); margin: var(--space-8) 0; padding-top: var(--space-6);">
            <h3
                style="color: var(--color-primary); margin-bottom: var(--space-4); display: flex; align-items: center; gap: var(--space-2);">
                <i class="fas fa-check-circle"></i> Challenge Completion Criteria
            </h3>
            <p style="color: var(--color-gray-600); margin-bottom: var(--space-6); font-size: var(--text-sm);">
                Define how users complete this challenge to earn the badge/reward. Choose one type below:
            </p>

            <!-- Completion Type -->
            <div class="form-group">
                <label for="completion_type" class="required">Completion Type</label>
                <select id="completion_type" name="completion_type" required>
                    <option value="quantity" <?php echo (isset($completion_type) && $completion_type == 'quantity') ? 'selected' : ''; ?>>Quantity-Based (Recycle X items)</option>
                    <option value="points" <?php echo (isset($completion_type) && $completion_type == 'points') ? 'selected' : ''; ?>>Points-Based (Earn X points during challenge)</option>
                    <option value="participation" <?php echo (isset($completion_type) && $completion_type == 'participation') ? 'selected' : ''; ?>>Participation (Just join + submit 1
                        item)</option>
                </select>
                <small>
                    <i class="fas fa-info-circle"></i>
                    <strong>Quantity:</strong> "Recycle 20 plastic bottles" | <strong>Points:</strong> "Earn 300 points
                    this week" | <strong>Participation:</strong> "Join Earth Day event"
                </small>
            </div>

            <!-- Target Quantity (for quantity-based) -->
            <div class="form-group" id="quantity-field">
                <label for="target_quantity">Target Quantity</label>
                <input type="number" id="target_quantity" name="target_quantity"
                    value="<?php echo isset($target_quantity) ? $target_quantity : '10'; ?>" min="1" max="1000"
                    step="1">
                <small>
                    <i class="fas fa-hashtag"></i>
                    How many items users must recycle to complete this challenge (e.g., 20 bottles, 50 cans)
                </small>
            </div>

            <!-- Target Points (for points-based) -->
            <div class="form-group" id="points-field" style="display: none;">
                <label for="target_points">Target Points</label>
                <input type="number" id="target_points" name="target_points"
                    value="<?php echo isset($target_points) ? $target_points : '100'; ?>" min="10" max="10000"
                    step="10">
                <small>
                    <i class="fas fa-star"></i>
                    How many points users must earn during the challenge period to complete it (e.g., 300 points)
                </small>
            </div>

            <div class="info-box" style="margin-top: var(--space-4);">
                <p>
                    <i class="fas fa-lightbulb"></i>
                    <span><strong>Note:</strong> Badge/Reward will be automatically awarded when users meet the
                        completion criteria. Point multiplier applies during the challenge period but is separate from
                        completion requirements.</span>
                </p>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-check"></i> Create Challenge
            </button>
            <a href="challenges.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<script>
    // Auto-update end date minimum when start date changes
    document.getElementById('start_date').addEventListener('change', function () {
        const startDate = new Date(this.value);
        const endDateInput = document.getElementById('end_date');

        // Set minimum end date to one day after start date
        startDate.setDate(startDate.getDate() + 1);
        const minEndDate = startDate.toISOString().split('T')[0];
        endDateInput.min = minEndDate;

        // If current end date is before new minimum, update it
        if (endDateInput.value && endDateInput.value < minEndDate) {
            endDateInput.value = minEndDate;
        }
    });

    // Toggle completion criteria fields based on type
    const completionTypeSelect = document.getElementById('completion_type');
    const quantityField = document.getElementById('quantity-field');
    const pointsField = document.getElementById('points-field');
    const targetQuantityInput = document.getElementById('target_quantity');
    const targetPointsInput = document.getElementById('target_points');

    function updateCompletionFields() {
        const type = completionTypeSelect.value;

        if (type === 'quantity') {
            quantityField.style.display = 'block';
            pointsField.style.display = 'none';
            targetQuantityInput.required = true;
            targetPointsInput.required = false;
            if (targetPointsInput.value !== '') targetPointsInput.value = ''; // Clear hidden
        } else if (type === 'points') {
            quantityField.style.display = 'none';
            pointsField.style.display = 'block';
            targetQuantityInput.required = false;
            targetPointsInput.required = true;
            if (targetQuantityInput.value !== '') targetQuantityInput.value = ''; // Clear hidden
        } else if (type === 'participation') {
            quantityField.style.display = 'none';
            pointsField.style.display = 'none';
            targetQuantityInput.required = false;
            targetPointsInput.required = false;
            if (targetQuantityInput.value !== '') targetQuantityInput.value = ''; // Clear hidden
            if (targetPointsInput.value !== '') targetPointsInput.value = ''; // Clear hidden
        }
    }

    completionTypeSelect.addEventListener('change', updateCompletionFields);
    // Initialize on page load
    updateCompletionFields();

    // Form validation
    document.querySelector('form').addEventListener('submit', function (e) {
        const startDate = new Date(document.getElementById('start_date').value);
        const endDate = new Date(document.getElementById('end_date').value);
        const multiplier = parseFloat(document.getElementById('point_multiplier').value);
        const completionType = completionTypeSelect.value;
        const targetQuantity = parseInt(targetQuantityInput.value);
        const targetPoints = parseInt(targetPointsInput.value);

        if (endDate <= startDate) {
            e.preventDefault();
            alert('End date must be after start date!');
            return false;
        }

        if (multiplier <= 0 || multiplier > 10) {
            e.preventDefault();
            alert('Point multiplier must be between 0.1 and 10!');
            return false;
        }

        // Validate completion criteria
        if (completionType === 'quantity' && (isNaN(targetQuantity) || targetQuantity <= 0)) {
            e.preventDefault();
            alert('Please enter a valid target quantity (must be greater than 0)!');
            return false;
        }

        if (completionType === 'points' && (isNaN(targetPoints) || targetPoints <= 0)) {
            e.preventDefault();
            alert('Please enter a valid target points (must be greater than 0)!');
            return false;
        }
    });
</script>

<?php
include 'includes/footer.php';
mysqli_close($conn);
?>