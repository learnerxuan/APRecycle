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

$error_message = '';
$success_message = '';

// Handle form submission
if (isset($_POST['add_moderator'])) {
    $mod_username = trim($_POST['username']);
    $mod_email = trim($_POST['email']);
    $mod_password = $_POST['password'];
    $qr_code = 'MOD_' . strtoupper(substr(md5($mod_email), 0, 8));
    
    // Validation
    if (empty($mod_username) || empty($mod_email) || empty($mod_password)) {
        $error_message = "All fields are required.";
    } else {
        // Check if email already exists
        $check_stmt = mysqli_prepare($conn, "SELECT user_id FROM user WHERE email = ?");
        mysqli_stmt_bind_param($check_stmt, "s", $mod_email);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error_message = "Email already exists in the system.";
        } else {
            // Hash password
            $hashed_password = password_hash($mod_password, PASSWORD_DEFAULT);
            
            // Insert new eco-moderator with 'eco-moderator' role (hyphen)
            $insert_stmt = mysqli_prepare($conn, 
                "INSERT INTO user (username, password, email, role, qr_code, lifetime_points, created_at) 
                 VALUES (?, ?, ?, 'eco-moderator', ?, 0, NOW())");
            mysqli_stmt_bind_param($insert_stmt, "ssss", $mod_username, $hashed_password, $mod_email, $qr_code);
            
            if (mysqli_stmt_execute($insert_stmt)) {
                $success_message = "Eco-Moderator added successfully!";
                // Clear form
                $mod_username = $mod_email = $mod_password = '';
            } else {
                $error_message = "Failed to add eco-moderator. Please try again.";
            }
            mysqli_stmt_close($insert_stmt);
        }
        mysqli_stmt_close($check_stmt);
    }
}

$page_title = "Add Eco-Moderator";
include 'includes/header.php';
?>

<style>
    .add-container {
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
        text-align: center;
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
        background: var(--color-primary);
        color: white;
        border: none;
        border-radius: var(--radius-md);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-submit:hover {
        background: var(--color-primary-dark);
        transform: translateY(-2px);
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

<div class="add-container">
    <a href="moderators.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back to Moderators
    </a>

    <div class="form-card">
        <div class="form-header">
            <h1 class="form-title">
                <i class="fas fa-user-plus"></i> Add New Eco-Moderator
            </h1>
            <p class="form-subtitle">Create a new eco-moderator account to help manage recycling submissions</p>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                <br><a href="moderators.php" style="color: var(--color-success); font-weight: 600; text-decoration: underline;">View all moderators</a>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="info-box">
            <p><i class="fas fa-info-circle"></i> <strong>Note:</strong> The new eco-moderator will be able to review recycling submissions, provide feedback, and create educational content.</p>
        </div>

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
                    placeholder="Enter full name"
                    value="<?php echo isset($mod_username) ? htmlspecialchars($mod_username) : ''; ?>"
                    required>
                <p class="form-help">This will be displayed as the moderator's name</p>
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
                    placeholder="moderator@apu.edu.my"
                    value="<?php echo isset($mod_email) ? htmlspecialchars($mod_email) : ''; ?>"
                    required>
                <p class="form-help">Must be a valid APU email address</p>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">
                    Password <span class="required">*</span>
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-input" 
                    placeholder="Enter a strong password"
                    minlength="8"
                    required>
                <p class="form-help">Minimum 8 characters. The moderator can change this later.</p>
            </div>

            <div class="form-actions">
                <a href="moderators.php" class="btn-cancel">Cancel</a>
                <button type="submit" name="add_moderator" class="btn-submit">
                    <i class="fas fa-check"></i> Add Eco-Moderator
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>