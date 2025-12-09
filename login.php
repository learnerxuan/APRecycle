<?php
require_once 'php/config.php';

$error = '';
$success_msg = '';

if (isset($_GET['success']) && $_GET['success'] == 'registered') {
    $success_msg = 'Registration successful! Please login with your new account.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $conn = getDBConnection();

        $stmt = mysqli_prepare($conn, "SELECT user_id, username, password, email, role FROM user WHERE username = ?");

        if ($stmt === false) {
            die("Statement preparation failed: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if($result && mysqli_num_rows($result) === 1){
            $user = mysqli_fetch_assoc($result);

            if(password_verify($password, $user['password'])){

                if (isset($_POST['remember_me'])) {
                    // Set cookies for 30 days
                    setcookie('username', $username, time() + (86400 * 30), "/");
                    setcookie('password', $password, time() + (86400 * 30), "/");
                } else{
                    if (isset($_COOKIE['username'])) {
                        setcookie('username', '', time() - 3600, "/");
                    }
                    if (isset($_COOKIE['password'])) {
                        setcookie('password', '', time() - 3600, "/");
                    }
                }

                // Set session data
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Redirect by role
                switch ($user['role']){
                    case 'administrator':
                        header('Location: admin/dashboard.php');
                        break;
                    case 'eco-moderator':
                        header('Location: eco-moderator/dashboard.php');
                        break;
                    case 'recycler':
                    default:
                        header('Location: recycler/dashboard.php');
                        break;
                }
                exit();
            } else{
                $error = 'Invalid username or password';
            }
        } else{
            $error = 'Invalid username or password';
        }

        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - APRecycle</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;       
            height: 100vh;             
            margin: 0;
}

        .login-container{
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

        input[type="text"], input[type="password"] {
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

        .alert-error{
            background: var(--color-error-light);
            color: #C53030;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid var(--color-error);
        }

        .alert-success {
            background: var(--color-success-light); 
            color: #22543D;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid var(--color-success);
        }

        .form-group{
            margin-bottom: 1.5rem;
        }

        .register-link{
            text-align: center;
            margin-top: 1.5rem;
            color: var(--color-info-ligh);
        }

        .register-link a {
            color: var(--color-primary);
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="assets/aprecycle-logo.png">
            <p>Smart Recycling System for APU</p>
        </div>

        <?php if ($error): ?>
            <div class="alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success_msg): ?>
            <div class="alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo isset($_COOKIE['username']) ? $_COOKIE['username'] : ''; ?>" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" value="<?php echo isset($_COOKIE['password']) ? $_COOKIE['password'] : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="remember_me" <?php echo isset($_COOKIE['username']) ? 'checked' : ''; ?>> 
                    Remember me
                </label>
            </div>
            
            <button type="submit" class="btn-primary">Login</button>
        </form>

        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</body>
</html>
