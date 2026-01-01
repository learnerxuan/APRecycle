<?php
session_start();
require_once '../php/config.php';
$conn = getDBConnection();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'recycler') { header("Location: ../login.php"); exit(); }

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $team_name = trim($_POST['team_name']);
    $description = trim($_POST['description']);
    $user_id = $_SESSION['user_id'];

    if (empty($team_name)) { $error = "Team Name is required."; } 
    else {
        $sql = "INSERT INTO team (team_name, description, date_created) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $team_name, $description);
        if ($stmt->execute()) {
            $new_team_id = $conn->insert_id;
            $update_user = $conn->prepare("UPDATE user SET team_id = ? WHERE user_id = ?");
            $update_user->bind_param("ii", $new_team_id, $user_id);
            $update_user->execute();
            header("Location: teams.php");
            exit();
        } else {
            $error = "Error: Team name might already exist.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Team - APRecycle</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .page-hero {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
            color: white;
            padding: 2.5rem 2rem;
            border-radius: 1rem;
            margin-bottom: 2.5rem;
            text-align: center;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .page-hero h1 { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; }
        .page-hero p { font-size: 1.1rem; opacity: 0.95; margin: 0; }

        .form-card { background: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; }
        input, textarea { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-family: inherit; transition: border-color 0.2s; }
        input:focus, textarea:focus { outline: none; border-color: var(--color-primary); }
        textarea { height: 120px; resize: vertical; }
        label { display: block; margin-bottom: 0.75rem; font-weight: 600; color: #4a5568; }

        @media (max-width: 768px) {
            .page-hero { padding: 2rem 1rem; }
            .form-card { padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container" style="padding-top: 2rem;">
        <div style="display: flex; justify-content: flex-end; margin-bottom: 1.5rem;">
            <a href="teams.php" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: white; color: var(--color-gray-700); text-decoration: none; border-radius: 8px; border: 1px solid var(--color-gray-200); font-weight: 500; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.background='var(--color-gray-50)'; this.style.borderColor='var(--color-gray-300)';" onmouseout="this.style.background='white'; this.style.borderColor='var(--color-gray-200)';">
                <i class="fas fa-arrow-left"></i> <span>Back</span>
            </a>
        </div>

        <div class="page-hero">
            <h1>Create a New Team</h1>
            <p>Start your own squad and lead the way to sustainability.</p>
        </div>
        
        <div class="form-card">
            <?php if($error) echo "<div style='background: #fff5f5; color: #c53030; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; border: 1px solid #feb2b2;'>$error</div>"; ?>
            <form method="POST">
                <div style="margin-bottom: 1.5rem;">
                    <label>Team Name *</label>
                    <input type="text" name="team_name" required>
                </div>
                <div style="margin-bottom: 2rem;">
                    <label>Description</label>
                    <textarea name="description"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; text-decoration: none; border-radius: 8px; padding: 12px;">Create Team</button>
            </form>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>