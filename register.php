<?php
require_once 'php/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = 'recycler';

    // Validation
    if (empty($username) || empty($email) || empty($password)){
        $error = 'All fields are required';
    } elseif($password !== $confirm_password){
        $error = 'Passwords do not match';
    } elseif(strlen($password) < 6){
        $error = 'Password must be at least 6 characters';
    }else{

        $conn = getDBConnection();

        if(!$conn){
            die("Connection failed: " . mysqli_connect_error());
        }

        // Check username is unique
        $check_sql = "SELECT user_id FROM user WHERE username = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "s", $username);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $error = 'Username already exists';
        } else{
            mysqli_stmt_close($check_stmt);

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $insert_sql = "INSERT INTO user (username, password, email, role)
                    VALUES (?, ?, ?, ?)";
            $insert_stmt = mysqli_prepare($conn, $insert_sql);
            mysqli_stmt_bind_param($insert_stmt, "ssss", $username, $hashed_password, $email, $role);

            if (mysqli_stmt_execute($insert_stmt)) {
                    header("Location: login.php?success=registered");
                    exit();
            } else {
                $error = 'Registration failed: ' . mysqli_error($conn);
            }
            mysqli_stmt_close($insert_stmt);
        }
        mysqli_close($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - APRecycle</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;       
            height: 100vh;             
            margin: 0;
        }

        .register-container{
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 420px;
        }

        .logo{
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo img{
            width: 200px;
            height: 200px;
        }

        .logo p {
            color: var(--color-primary-light);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid var(--color-gray-200);
            border-radius: var(--radius-sm);
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        input:focus {
            outline: none;
            border-color: var(--color-primary);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-error{
            background: var(--color-error-light);
            color: #C53030;
            border: 1px solid var(--color-error);
        }

        .alert-success {
            background: var(--color-success-light);
            color: #22543D;
            border: 1px solid var(--color-success);
        }

        .form-group{
            margin-bottom: 1.5rem;
        }

        .form-group small{
            color: var(--color-gray-500);
        }

        .login-link{
            text-align: center;
            margin-top: 1.5rem;
            color: var(--color-info-ligh);
        }

        .login-link a {
            color: var(--color-primary);
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <img src="assets/aprecycle-logo.png">
            <p>Create your account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="" onsubmit="document.querySelector('.btn-primary').disabled = true; document.querySelector('.btn-primary').innerText = 'Processing...';">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required
                value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '';?>">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <small>At least 6 characters</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn-primary">Create Account</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>