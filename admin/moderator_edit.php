<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'aprecycle');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in and is an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrator') {
    // For testing, comment out this redirect
    // header('Location: ../login.php');
    // exit();
}

$user_id = $_SESSION['user_id'] ?? 1;
$username = $_SESSION['username'] ?? 'Admin';

// Get moderator ID from URL
if (!isset($_GET['id'])) {
    header('Location: moderators.php');
    exit();
}

$moderator_id = intval($_GET['id']);

$error_message = '';
$success_message = '';

// Handle form submission
if (isset($_POST['update_moderator'])) {
    $mod_username = trim($_POST['username']);
    $mod_email = trim($_POST['email']);
    $new_password = trim($_POST['new_password']);
    
    // Validation
    if (empty($mod_username) || empty($mod_email)) {
        $error_message = "Username and email are required.";
    } else {
        // Check if email already exists for other users
        $check_stmt = mysqli_prepare($conn, "SELECT user_id FROM user WHERE email = ? AND user_id != ?");
        mysqli_stmt_bind_param($check_stmt, "si", $mod_email, $moderator_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error_message = "Email already exists for another user.";
        } else {
            // Update moderator info
            if (!empty($new_password)) {
                // Update with new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = mysqli_prepare($conn, 
                    "UPDATE user SET username = ?, email = ?, password = ? WHERE user_id = ? AND role = 'eco_moderator'");
                mysqli_stmt_bind_param($update_stmt, "sssi", $mod_username, $mod_email, $hashed_password, $moderator_id);
            } else {
                // Update without password change
                $update_stmt = mysqli_prepare($conn, 
                    "UPDATE user SET username = ?, email = ? WHERE user_id = ? AND role = 'eco_moderator'");
                mysqli_stmt_bind_param($update_stmt, "ssi", $mod_username, $mod_email, $moderator_id);
            }
            
            if (mysqli_stmt_execute($update_stmt)) {
                $success_message = "Eco-Moderator updated successfully!";
            } else {
                $error_message = "Failed to update eco-moderator. Please try again.";
            }
        }
    }
}

// Get moderator details
$mod_query = "SELECT user_id, username, email, created_at FROM user WHERE user_id = ? AND role = 'eco_moderator'";
$stmt = mysqli_prepare($conn, $mod_query);
mysqli_stmt_bind_param($stmt, "i", $moderator_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    header('Location: moderators.php');
    exit();
}

$moderator = mysqli_fetch_assoc($result);

$page_title = "Edit Eco-Moderator";
include 'includes/header.php';
?>

<style>
    .edit-container {
        max-width: 700px;
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

    .form-card {
        background: white;
        border-radius: var(--radius-lg);
        padding: var(--space-8);
        box-shadow: var(--shadow-lg);
    }

    .form-header {
        margin-bottom: var(--space-6);
    }

    .form-title {
        font-size: var(--text-2xl);
        font-weight: 700;
        color: var(--color-gray-800);
        margin-bottom: var(--space-2);
    }

    .form-subtitle {
        color: var(--color-gray-600);
        font-size: var(--text-sm);
    }

    .moderator-info {
        background: var(--color-gray-50);
        padding: var(--space-4);
        border-radius: var(--radius-md);
        margin-bottom: var(--space-6);
        border-left: 4px solid var(--color-primary);
    }

    .moderator-info p {
        margin: var(--space-2) 0;
        font-size: var(--text-sm);
        color: var(--color-gray-700);
    }

    .moderator-info strong {
        color: var(--color-gray-800);
    }

    .alert {
        padding: var(--space-4);
        border-radius: var(--radius-md);
        margin-bottom: var(--space-6);
        border-left: 4px solid;
    }

    .alert-success {
        background: var(--color-success-light);
        color: var(--color-success);
        border-left-color: var(--color-success);
    }

    .alert-error {
        background: var(--color-danger-light);
        color: var(--color-danger);
        border-left-color: var(--color-danger);
    }

    .form-group {
        margin-bottom: var(--space-6);
    }

    .form-label {
        display: block;
        font-weight: 600;
        margin-bottom: var(--space-2);
        color: var(--color-gray-700);
        font-size: var(--text-sm);
    }

    .required {
        color: var(--color-danger);
    }

    .form-input {
        width: 100%;
        padding: var(--space-3);
        border: 2px solid var(--color-gray-300);
        border-radius: var(--radius-md);
        font-size: var(--text-base);
        transition: all 0.2s ease;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .form-help {
        font-size: var(--text-xs);
        color: var(--color-gray-500);
        margin-top: var(--space-2);
    }

    .form-actions {
        display: flex;
        gap: var(--space-3);
        justify-content: flex-end;
        margin-top: var(--space-8);
        padding-top: var(--space-6);
        border-top: 1px solid var(--color-gray-200);
    }

    .btn-cancel {
        padding: var(--space-3) var(--space-6);
        background: var(--color-gray-200);
        color: var(--color-gray-700);
        border: none;
        border-radius: var(--radius-md);
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-cancel:hover {
        background: var(--color-gray-300);
    }

    .btn-submit {
        padding: var(--space-3) var(--space-6);
        background: var(--color-warning);
        color: white;
        border: none;
        border-radius: var(--radius-md);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-submit:hover {
        background: var(--color-warning-dark);
        transform: translateY(-2px);
    }

    .warning-box {
        background: var(--color-warning-light);
        border-left: 4px solid var(--color-warning);
        padding: var(--space-4);
        border-radius: var(--radius-md);
        margin-bottom: var(--space-6);
    }

    .warning-box p {
        margin: 0;
        color: var(--color-gray-700);
        font-size: var(--text-sm);
    }

    @media (max-width: 768px) {
        .form-card {
            padding: var(--space-6);
        }

        .form-actions {
            flex-direction: column;
        }
    }
</style>

<div class="edit-container">
    <a href="moderators.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back to Moderators
    </a>

    <div class="form-card">
        <div class="form-header">
            <h1 class="form-title">
                <i class="fas fa-user-edit"></i> Edit Eco-Moderator
            </h1>
            <p class="form-subtitle">Update eco-moderator account information</p>
        </div>

        <div class="moderator-info">
            <p><strong><i class="fas fa-id-card"></i> Moderator ID:</strong> #<?php echo str_pad($moderator['user_id'], 4, '0', STR_PAD_LEFT); ?></p>
            <p><strong><i class="fas fa-calendar"></i> Joined:</strong> <?php echo date('F d, Y', strtotime($moderator['created_at'])); ?></p>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label" for="username">
                    Full Name <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-input" 
                    value="<?php echo htmlspecialchars($moderator['username']); ?>"
                    required>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">
                    Email Address <span class="required">*</span>
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-input" 
                    value="<?php echo htmlspecialchars($moderator['email']); ?>"
                    required>
            </div>

            <div class="form-group">
                <label class="form-label" for="new_password">
                    New Password (Optional)
                </label>
                <input 
                    type="password" 
                    id="new_password" 
                    name="new_password" 
                    class="form-input" 
                    placeholder="Leave blank to keep current password"
                    minlength="8">
                <p class="form-help">Only fill this if you want to change the password. Minimum 8 characters.</p>
            </div>

            <div class="warning-box">
                <p><i class="fas fa-exclamation-triangle"></i> <strong>Note:</strong> Changes will take effect immediately. The moderator will need to use the new email/password to log in.</p>
            </div>

            <div class="form-actions">
                <a href="moderators.php" class="btn-cancel">Cancel</a>
                <button type="submit" name="update_moderator" class="btn-submit">
                    <i class="fas fa-save"></i> Update Moderator
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>