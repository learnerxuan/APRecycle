<?php
require_once '../php/config.php';

$page_title = 'Edit Challenge';
$conn = getDBConnection();

$success_message = '';
$error_message = '';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: challenges.php');
    exit();
}

$challenge_id = intval($_GET['id']);

// Validations and Updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $point_multiplier = floatval($_POST['point_multiplier']);
    $badge_id = !empty($_POST['badge_id']) ? intval($_POST['badge_id']) : null;
    $reward_id = !empty($_POST['reward_id']) ? intval($_POST['reward_id']) : null;
    $target_material_id = !empty($_POST['target_material_id']) ? intval($_POST['target_material_id']) : null;

    // Validation
    if (empty($title) || empty($description) || empty($start_date) || empty($end_date)) {
        $error_message = "Please fill in all required fields.";
    } elseif (strtotime($start_date) >= strtotime($end_date)) {
        $error_message = "End date must be after start date.";
    } elseif ($point_multiplier <= 0) {
        $error_message = "Point multiplier must be greater than 0.";
    } else {
        // Update challenge
        $update_query = "UPDATE challenge SET 
                        title = ?, 
                        description = ?, 
                        start_date = ?, 
                        end_date = ?, 
                        point_multiplier = ?, 
                        badge_id = ?, 
                        reward_id = ?, 
                        target_material_id = ? 
                        WHERE challenge_id = ?";

        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "ssssdiisi", $title, $description, $start_date, $end_date, $point_multiplier, $badge_id, $reward_id, $target_material_id, $challenge_id);

        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Challenge updated successfully!";
        } else {
            $error_message = "Error updating challenge: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch existing challenge data
$select_query = "SELECT * FROM challenge WHERE challenge_id = ?";
$stmt = mysqli_prepare($conn, $select_query);
mysqli_stmt_bind_param($stmt, "i", $challenge_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$challenge = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$challenge) {
    header('Location: challenges.php');
    exit();
}

// Pre-fill variables if not set by POST (first load)
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $title = $challenge['title'];
    $description = $challenge['description'];
    $start_date = $challenge['start_date'];
    $end_date = $challenge['end_date'];
    $point_multiplier = $challenge['point_multiplier'];
    $badge_id = $challenge['badge_id'];
    $reward_id = $challenge['reward_id'];
    $target_material_id = $challenge['target_material_id'];
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
    .form-group input[type="datetime-local"],
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
        <i class="fas fa-edit"></i> Edit Challenge
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
        <span>Editing an active challenge may affect users currently participating in it. Proceed with caution.</span>
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
        </div>

        <!-- Challenge Description -->
        <div class="form-group">
            <label for="description" class="required">Description</label>
            <textarea id="description" name="description"
                placeholder="Describe the challenge goals, rules, and what participants need to do..."
                required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
        </div>

        <!-- Date Range -->
        <div class="form-row">
            <div class="form-group">
                <label for="start_date" class="required">Start Date</label>
                <?php
                // Format date for input type="date"
                $start_date_val = date('Y-m-d', strtotime($start_date));
                $end_date_val = date('Y-m-d', strtotime($end_date));
                ?>
                <input type="date" id="start_date" name="start_date" value="<?php echo $start_date_val; ?>" required>
            </div>

            <div class="form-group">
                <label for="end_date" class="required">End Date</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo $end_date_val; ?>" required>
            </div>
        </div>

        <!-- Point Multiplier -->
        <div class="form-group">
            <label for="point_multiplier" class="required">Point Multiplier</label>
            <input type="number" id="point_multiplier" name="point_multiplier"
                value="<?php echo isset($point_multiplier) ? $point_multiplier : '1.0'; ?>" min="0.1" max="10"
                step="0.1" required>
        </div>

        <!-- Badge Selection (Optional) -->
        <div class="form-group">
            <label for="badge_id">Badge Reward (Optional)</label>
            <select id="badge_id" name="badge_id">
                <option value="">-- No Badge --</option>
                <?php while ($badge = mysqli_fetch_assoc($badges_result)): ?>
                    <option value="<?php echo $badge['badge_id']; ?>" <?php echo (isset($badge_id) && $badge_id == $badge['badge_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($badge['badge_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Reward Selection (Optional) -->
        <div class="form-group">
            <label for="reward_id">Reward (Optional)</label>
            <select id="reward_id" name="reward_id">
                <option value="">-- No Reward --</option>
                <?php while ($reward = mysqli_fetch_assoc($rewards_result)): ?>
                    <option value="<?php echo $reward['reward_id']; ?>" <?php echo (isset($reward_id) && $reward_id == $reward['reward_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($reward['reward_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
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
                    <option value="<?php echo $material['material_id']; ?>" <?php echo (isset($target_material_id) && $target_material_id == $material['material_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($material['material_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <small>
                <i class="fas fa-recycle"></i>
                Select a specific material for this challenge.
            </small>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Changes
            </button>
            <a href="challenges.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<script>
    // Form validation
    document.querySelector('form').addEventListener('submit', function (e) {
        const startDate = new Date(document.getElementById('start_date').value);
        const endDate = new Date(document.getElementById('end_date').value);
        const multiplier = parseFloat(document.getElementById('point_multiplier').value);

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
    });
</script>

<?php
include 'includes/footer.php';
mysqli_close($conn);
?>