<?php
require_once '../php/config.php';

$conn = getDBConnection();

$success_message = '';
$error_message = '';
$is_edit = false;
$reward = null;

// Check if editing existing reward
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $is_edit = true;
    $reward_id = (int) $_GET['id'];

    $reward_query = "SELECT * FROM reward WHERE reward_id = ?";
    $stmt = mysqli_prepare($conn, $reward_query);
    mysqli_stmt_bind_param($stmt, "i", $reward_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $reward = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$reward) {
        header('Location: rewards.php');
        exit();
    }
}

$page_title = $is_edit ? 'Edit Reward' : 'Create New Reward';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reward_name = trim($_POST['reward_name']);
    $description = trim($_POST['description']);

    // Validation
    if (empty($reward_name) || empty($description)) {
        $error_message = "Please fill in all required fields.";
    } else {
        if ($is_edit) {
            // Update existing reward
            $update_query = "UPDATE reward SET reward_name = ?, description = ? WHERE reward_id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "ssi", $reward_name, $description, $reward_id);

            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Reward updated successfully!";
                // Refresh reward data
                $reward['reward_name'] = $reward_name;
                $reward['description'] = $description;
            } else {
                $error_message = "Error updating reward: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        } else {
            // Insert new reward
            $insert_query = "INSERT INTO reward (reward_name, description) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, "ss", $reward_name, $description);

            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Reward created successfully!";
                // Clear form
                $reward_name = $description = '';
            } else {
                $error_message = "Error creating reward: " . mysqli_error($conn);
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
        <i class="fas fa-gift"></i> <?php echo $is_edit ? 'Edit Reward' : 'Create New Reward'; ?>
    </h2>
    <a href="rewards.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Rewards
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
        <span>Rewards are ONLY awarded when completing challenges. Create physical prizes (e.g., tote bags, water bottles) that motivate users to join challenges!</span>
    </p>
</div>

<div class="form-container">
    <form method="POST" action="">
        <!-- Reward Name -->
        <div class="form-group">
            <label for="reward_name" class="required">Reward Name</label>
            <input type="text" id="reward_name" name="reward_name"
                value="<?php echo $reward ? htmlspecialchars($reward['reward_name']) : (isset($reward_name) ? htmlspecialchars($reward_name) : ''); ?>"
                placeholder="e.g., APU Eco Tote Bag, Stainless Steel Water Bottle" required>
            <small>Choose an attractive name for this challenge reward</small>
        </div>

        <!-- Description -->
        <div class="form-group">
            <label for="description" class="required">Description</label>
            <textarea id="description" name="description"
                placeholder="Describe what this reward includes and how winners can claim it..."
                required><?php echo $reward ? htmlspecialchars($reward['description']) : (isset($description) ? htmlspecialchars($description) : ''); ?></textarea>
            <small>Provide clear details about the physical reward and collection process</small>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-check"></i> <?php echo $is_edit ? 'Update Reward' : 'Create Reward'; ?>
            </button>
            <a href="rewards.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>


<?php
include 'includes/footer.php';
mysqli_close($conn);
?>
