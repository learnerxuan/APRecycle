<?php
require_once 'php/config.php';

$error = '';

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
        /*@import url('variables.css');*/

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

        .logo h1 {
            color: var(--color-primary);
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
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

        .test-accounts{
            margin-top: 2rem;
            padding: 1rem;
            background: #F7FAFC;
            border-radius: 8px;
            font-size: 0.875rem;
        }

        .test-accounts h3 {
            color: #2D5D3F;
            margin-bottom: 0.5rem;
        }

        .test-accounts p {
            margin: 0.25rem 0;
            color: #4A5568;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>🌱 APRecycle</h1>
            <p>Smart Recycling System for APU</p>
        </div>

        <?php if ($error): ?>
            <div class="alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-primary">Login</button>
        </form>

        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>

        <div class="test-accounts">
            <h3>Test Accounts</h3>
            <p><strong>Recycler:</strong> recycler1 / password123</p>
            <p><strong>Moderator:</strong> moderator1 / password123</p>
            <p><strong>Admin:</strong> admin1 / password123</p>
        </div>
    </div>
</body>
</html>
