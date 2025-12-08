<?php
require_once '../php/config.php';

// Check if user is logged in and is a recycler
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'recycler') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

$success_message = '';
$error_message = '';

// Fetch current user data
$stmt = mysqli_prepare($conn, "SELECT user_id, username, email, lifetime_points, created_at, team_id FROM user WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Get team name if user is in a team
$team_name = null;
if ($user['team_id']) {
    $team_stmt = mysqli_prepare($conn, "SELECT team_name FROM team WHERE team_id = ?");
    mysqli_stmt_bind_param($team_stmt, "i", $user['team_id']);
    mysqli_stmt_execute($team_stmt);
    $team_result = mysqli_stmt_get_result($team_stmt);
    $team_data = mysqli_fetch_assoc($team_result);
    $team_name = $team_data['team_name'];
    mysqli_stmt_close($team_stmt);
}

// Get user statistics
$stats_query = "SELECT 
    COUNT(DISTINCT rs.submission_id) as total_submissions,
    COUNT(DISTINCT CASE WHEN rs.status = 'Approved' THEN rs.submission_id END) as approved_submissions,
    COUNT(DISTINCT CASE WHEN rs.status = 'Pending' THEN rs.submission_id END) as pending_submissions,
    COUNT(DISTINCT CASE WHEN rs.status = 'Rejected' THEN rs.submission_id END) as rejected_submissions,
    COUNT(DISTINCT ub.badge_id) as badges_earned
FROM user u
LEFT JOIN recycling_submission rs ON u.user_id = rs.user_id
LEFT JOIN user_badge ub ON u.user_id = ub.user_id
WHERE u.user_id = ?
GROUP BY u.user_id";

$stats_stmt = mysqli_prepare($conn, $stats_query);
mysqli_stmt_bind_param($stats_stmt, "i", $user_id);
mysqli_stmt_execute($stats_stmt);
$stats_result = mysqli_stmt_get_result($stats_stmt);
$stats = mysqli_fetch_assoc($stats_result);
mysqli_stmt_close($stats_stmt);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($new_username) || empty($new_email)) {
        $error_message = "Username and email are required.";
    } else {
        // Check if username is already taken by another user
        $check_stmt = mysqli_prepare($conn, "SELECT user_id FROM user WHERE username = ? AND user_id != ?");
        mysqli_stmt_bind_param($check_stmt, "si", $new_username, $user_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error_message = "Username already taken. Please choose another.";
        } else {
            // If changing password, verify current password and validate new password
            if (!empty($new_password)) {
                if (empty($current_password)) {
                    $error_message = "Please enter your current password to change it.";
                } else {
                    // Verify current password
                    $pass_stmt = mysqli_prepare($conn, "SELECT password FROM user WHERE user_id = ?");
                    mysqli_stmt_bind_param($pass_stmt, "i", $user_id);
                    mysqli_stmt_execute($pass_stmt);
                    $pass_result = mysqli_stmt_get_result($pass_stmt);
                    $pass_data = mysqli_fetch_assoc($pass_result);
                    mysqli_stmt_close($pass_stmt);

                    if (!password_verify($current_password, $pass_data['password'])) {
                        $error_message = "Current password is incorrect.";
                    } elseif ($new_password !== $confirm_password) {
                        $error_message = "New passwords do not match.";
                    } elseif (strlen($new_password) < 6) {
                        $error_message = "New password must be at least 6 characters.";
                    } else {
                        // Update with new password
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $update_stmt = mysqli_prepare($conn, "UPDATE user SET username = ?, email = ?, password = ? WHERE user_id = ?");
                        mysqli_stmt_bind_param($update_stmt, "sssi", $new_username, $new_email, $hashed_password, $user_id);
                        
                        if (mysqli_stmt_execute($update_stmt)) {
                            $_SESSION['username'] = $new_username;
                            $_SESSION['email'] = $new_email;
                            $user['username'] = $new_username;
                            $user['email'] = $new_email;
                            $success_message = "Profile updated successfully! Password changed.";
                        } else {
                            $error_message = "Error updating profile: " . mysqli_error($conn);
                        }
                        mysqli_stmt_close($update_stmt);
                    }
                }
            } else {
                // Update without password change
                $update_stmt = mysqli_prepare($conn, "UPDATE user SET username = ?, email = ? WHERE user_id = ?");
                mysqli_stmt_bind_param($update_stmt, "ssi", $new_username, $new_email, $user_id);
                
                if (mysqli_stmt_execute($update_stmt)) {
                    $_SESSION['username'] = $new_username;
                    $_SESSION['email'] = $new_email;
                    $user['username'] = $new_username;
                    $user['email'] = $new_email;
                    $success_message = "Profile updated successfully!";
                } else {
                    $error_message = "Error updating profile: " . mysqli_error($conn);
                }
                mysqli_stmt_close($update_stmt);
            }
        }
        mysqli_stmt_close($check_stmt);
    }
}

$page_title = "Profile Management";
include 'includes/header.php';
?>

<style>
    /* Mobile-First Responsive Design */
    
    .profile-container {
        display: grid;
        grid-template-columns: 1fr;
        gap: var(--space-6);
        max-width: 1200px;
        margin: 0 auto;
    }

    .profile-card {
        background: var(--color-white);
        border-radius: var(--radius-lg);
        padding: var(--space-5);
        box-shadow: var(--shadow-md);
        border: 1px solid var(--color-gray-200);
    }

    .card-header {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        margin-bottom: var(--space-5);
        padding-bottom: var(--space-4);
        border-bottom: 2px solid var(--color-gray-200);
    }

    .card-icon {
        width: 40px;
        height: 40px;
        background: var(--gradient-primary);
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: var(--text-lg);
    }

    .card-title {
        font-size: var(--text-xl);
        font-weight: 700;
        color: var(--color-gray-800);
        margin: 0;
    }

    /* User Avatar Section */
    .user-avatar-section {
        text-align: center;
        margin-bottom: var(--space-6);
    }

    .user-avatar-large {
        width: 100px;
        height: 100px;
        background: var(--gradient-primary);
        border-radius: var(--radius-full);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: var(--space-3);
        box-shadow: var(--shadow-lg);
    }

    .user-name {
        font-size: var(--text-2xl);
        font-weight: 700;
        color: var(--color-gray-800);
        margin-bottom: var(--space-2);
    }

    .user-role {
        font-size: var(--text-base);
        color: var(--color-gray-600);
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: var(--space-4);
        margin-top: var(--space-5);
    }

    .stat-item {
        text-align: center;
        padding: var(--space-4);
        background: var(--color-gray-50);
        border-radius: var(--radius-md);
    }

    .stat-value {
        font-size: var(--text-2xl);
        font-weight: 700;
        color: var(--color-primary);
        margin-bottom: var(--space-1);
    }

    .stat-label {
        font-size: var(--text-xs);
        color: var(--color-gray-600);
        font-weight: 600;
    }

    /* Form Styles */
    .form-group {
        margin-bottom: var(--space-5);
    }

    .form-group label {
        display: block;
        margin-bottom: var(--space-2);
        color: var(--color-gray-800);
        font-weight: 600;
        font-size: var(--text-sm);
    }

    .form-group label.required::after {
        content: ' *';
        color: var(--color-error);
    }

    .form-group input {
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

    .form-group input:disabled {
        background: var(--color-gray-100);
        cursor: not-allowed;
    }

    .form-group small {
        display: block;
        margin-top: var(--space-2);
        color: var(--color-gray-600);
        font-size: var(--text-xs);
    }

    /* Alert Messages */
    .alert {
        padding: var(--space-4);
        border-radius: var(--radius-md);
        margin-bottom: var(--space-5);
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

    /* Buttons */
    .form-actions {
        display: flex;
        flex-direction: column;
        gap: var(--space-3);
        margin-top: var(--space-6);
        padding-top: var(--space-5);
        border-top: 2px solid var(--color-gray-200);
    }

    .btn {
        width: 100%;
        padding: var(--space-3) var(--space-5);
        border-radius: var(--radius-md);
        font-size: var(--text-base);
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
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

    /* Info Box */
    .info-box {
        background: var(--color-info-light);
        border-left: 4px solid var(--color-info);
        padding: var(--space-4);
        border-radius: var(--radius-md);
        margin-top: var(--space-5);
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

    /* Password Section */
    .password-section {
        margin-top: var(--space-6);
        padding-top: var(--space-6);
        border-top: 2px solid var(--color-gray-200);
    }

    .section-title {
        font-size: var(--text-lg);
        font-weight: 700;
        color: var(--color-gray-800);
        margin-bottom: var(--space-4);
        display: flex;
        align-items: center;
        gap: var(--space-2);
    }

    /* Member Since Badge */
    .member-badge {
        display: inline-flex;
        align-items: center;
        gap: var(--space-2);
        background: var(--color-info-light);
        color: #1E40AF;
        padding: var(--space-2) var(--space-4);
        border-radius: var(--radius-full);
        font-size: var(--text-sm);
        font-weight: 600;
        margin-top: var(--space-3);
    }

    .team-badge {
        display: inline-flex;
        align-items: center;
        gap: var(--space-2);
        background: var(--color-success-light);
        color: #065F46;
        padding: var(--space-2) var(--space-4);
        border-radius: var(--radius-full);
        font-size: var(--text-sm);
        font-weight: 600;
        margin-top: var(--space-2);
    }

    /* Tablet Breakpoint (768px+) */
    @media (min-width: 768px) {
        .profile-card {
            padding: var(--space-6);
        }

        .stats-grid {
            grid-template-columns: repeat(4, 1fr);
        }

        .form-actions {
            flex-direction: row;
        }

        .btn {
            width: auto;
            min-width: 150px;
        }
    }

    /* Desktop Breakpoint (1024px+) */
    @media (min-width: 1024px) {
        .profile-container {
            grid-template-columns: 1fr 2fr;
        }

        .profile-card {
            padding: var(--space-8);
        }
    }
</style>

<div class="page-header">
    <h2 class="page-title">
        <i class="fas fa-user-circle" style="margin-right: 10px;"></i>
        Profile Management
    </h2>
    <p class="page-description">Manage your account information and settings</p>
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

<div class="profile-container">
    <!-- User Overview Card -->
    <div class="profile-card">
        <div class="card-header">
            <div class="card-icon">
                <i class="fas fa-user"></i>
            </div>
            <h3 class="card-title">Your Profile</h3>
        </div>

        <div class="user-avatar-section">
            <div class="user-avatar-large">
                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
            </div>
            <div class="user-name"><?php echo htmlspecialchars($user['username']); ?></div>
            <div class="user-role">Recycler</div>
            
            <div class="member-badge">
                <i class="fas fa-calendar"></i>
                Member since <?php echo date('M Y', strtotime($user['created_at'])); ?>
            </div>

            <?php if ($team_name): ?>
                <br>
                <div class="team-badge">
                    <i class="fas fa-users"></i>
                    Team: <?php echo htmlspecialchars($team_name); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-value"><?php echo number_format($user['lifetime_points']); ?></div>
                <div class="stat-label">Total Points</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo number_format($stats['total_submissions']); ?></div>
                <div class="stat-label">Submissions</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo number_format($stats['approved_submissions']); ?></div>
                <div class="stat-label">Approved</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo number_format($stats['badges_earned']); ?></div>
                <div class="stat-label">Badges</div>
            </div>
        </div>

        <div class="info-box">
            <p>
                <i class="fas fa-info-circle"></i>
                <span>You have <?php echo $stats['pending_submissions']; ?> pending submission(s) awaiting review.</span>
            </p>
        </div>
    </div>

    <!-- Edit Profile Card -->
    <div class="profile-card">
        <div class="card-header">
            <div class="card-icon">
                <i class="fas fa-edit"></i>
            </div>
            <h3 class="card-title">Edit Information</h3>
        </div>

        <form method="POST" action="">
            <!-- Basic Information -->
            <div class="form-group">
                <label for="username" class="required">Username</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       value="<?php echo htmlspecialchars($user['username']); ?>"
                       required>
                <small>Choose a unique username for your account</small>
            </div>

            <div class="form-group">
                <label for="email" class="required">Email Address</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="<?php echo htmlspecialchars($user['email']); ?>"
                       required>
                <small>We'll use this email for important notifications</small>
            </div>

            <div class="form-group">
                <label>User ID</label>
                <input type="text" 
                       value="<?php echo $user['user_id']; ?>" 
                       disabled>
                <small>Your unique identifier in the system</small>
            </div>

            <!-- Password Change Section -->
            <div class="password-section">
                <h4 class="section-title">
                    <i class="fas fa-lock"></i>
                    Change Password (Optional)
                </h4>

                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" 
                           id="current_password" 
                           name="current_password"
                           placeholder="Enter current password">
                    <small>Required only if changing password</small>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" 
                           id="new_password" 
                           name="new_password"
                           placeholder="Enter new password">
                    <small>Minimum 6 characters</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password"
                           placeholder="Confirm new password">
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Save Changes
                </button>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    // Clear password fields if user clicks cancel
    document.querySelector('.btn-secondary').addEventListener('click', function(e) {
        if (document.getElementById('new_password').value || 
            document.getElementById('current_password').value || 
            document.getElementById('confirm_password').value) {
            if (!confirm('Discard password changes?')) {
                e.preventDefault();
            }
        }
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new_password').value;
        const currentPassword = document.getElementById('current_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        // If trying to change password
        if (newPassword) {
            if (!currentPassword) {
                e.preventDefault();
                alert('Please enter your current password to change it.');
                return false;
            }
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match!');
                return false;
            }
            if (newPassword.length < 6) {
                e.preventDefault();
                alert('New password must be at least 6 characters!');
                return false;
            }
        }
    });
</script>

<?php
include 'includes/footer.php';
mysqli_close($conn);
?>