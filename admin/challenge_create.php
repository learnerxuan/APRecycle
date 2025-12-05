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

    // Validation
    if (empty($title) || empty($description) || empty($start_date) || empty($end_date)) {
        $error_message = "Please fill in all required fields.";
    } elseif (strtotime($start_date) >= strtotime($end_date)) {
        $error_message = "End date must be after start date.";
    } elseif ($point_multiplier <= 0) {
        $error_message = "Point multiplier must be greater than 0.";
    } else {
        // Insert challenge
        $insert_query = "INSERT INTO challenge (title, description, start_date, end_date, point_multiplier, badge_id, reward_id, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "ssssdii", $title, $description, $start_date, $end_date, $point_multiplier, $badge_id, $reward_id);

        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Challenge created successfully!";
            // Clear form
            $title = $description = $start_date = $end_date = '';
            $point_multiplier = 1.0;
            $badge_id = $reward_id = null;
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
        <span>Create engaging challenges to motivate recyclers. Set dates, point multipliers, and optional badges/rewards to make recycling more fun!</span>
    </p>
</div>

<div class="form-container">
    <form method="POST" action="">
        <!-- Challenge Title -->
        <div class="form-group">
            <label for="title" class="required">Challenge Title</label>
            <input type="text"
                   id="title"
                   name="title"
                   value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>"
                   placeholder="e.g., Earth Day Recycling Challenge"
                   required>
            <small>Choose a catchy, descriptive name for your challenge</small>
        </div>

        <!-- Challenge Description -->
        <div class="form-group">
            <label for="description" class="required">Description</label>
            <textarea id="description"
                      name="description"
                      placeholder="Describe the challenge goals, rules, and what participants need to do..."
                      required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
            <small>Provide clear instructions and motivation for participants</small>
        </div>

        <!-- Date Range -->
        <div class="form-row">
            <div class="form-group">
                <label for="start_date" class="required">Start Date</label>
                <input type="date"
                       id="start_date"
                       name="start_date"
                       value="<?php echo isset($start_date) ? $start_date : date('Y-m-d'); ?>"
                       min="<?php echo date('Y-m-d'); ?>"
                       required>
                <small>When the challenge begins</small>
            </div>

            <div class="form-group">
                <label for="end_date" class="required">End Date</label>
                <input type="date"
                       id="end_date"
                       name="end_date"
                       value="<?php echo isset($end_date) ? $end_date : ''; ?>"
                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                       required>
                <small>When the challenge ends</small>
            </div>
        </div>

        <!-- Point Multiplier -->
        <div class="form-group">
            <label for="point_multiplier" class="required">Point Multiplier</label>
            <input type="number"
                   id="point_multiplier"
                   name="point_multiplier"
                   value="<?php echo isset($point_multiplier) ? $point_multiplier : '1.0'; ?>"
                   min="0.1"
                   max="10"
                   step="0.1"
                   required>
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
                    <option value="<?php echo $badge['badge_id']; ?>"
                            <?php echo (isset($badge_id) && $badge_id == $badge['badge_id']) ? 'selected' : ''; ?>>
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
                    <option value="<?php echo $reward['reward_id']; ?>"
                            <?php echo (isset($reward_id) && $reward_id == $reward['reward_id']) ? 'selected' : ''; ?>>
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
    document.getElementById('start_date').addEventListener('change', function() {
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

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
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
