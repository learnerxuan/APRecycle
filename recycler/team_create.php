<?php
session_start();
require_once '../php/config.php';
$conn = getDBConnection();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'recycler') {
    header("Location: ../login.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $team_name = trim($_POST['team_name']);
    $description = trim($_POST['description']);
    $user_id = $_SESSION['user_id'];

    if (empty($team_name)) {
        $error = "Team Name is required.";
    } else {
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
    <title>Create Team - APRecycle</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <a href="teams.php" class="btn btn-secondary" style="margin-bottom: 1.5rem; display: inline-block;">&larr; Back</a>
        
        <div class="card" style="max-width: 600px; margin: 0 auto; padding: 2rem; background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2>Create a New Team</h2>
            <p style="color: #718096; margin-bottom: 1.5rem;">Fill in the details below to start your journey.</p>
            
            <?php if($error) echo "<div style='background: #FED7D7; color: #C53030; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>$error</div>"; ?>

            <form method="POST">
                <label>Team Name *</label>
                <input type="text" name="team_name" required style="width:100%; padding:10px; margin-bottom:15px; border: 1px solid #CBD5E0; border-radius: 6px;">
                
                <label>Description</label>
                <textarea name="description" rows="4" style="width:100%; padding:10px; margin-bottom:15px; border: 1px solid #CBD5E0; border-radius: 6px;"></textarea>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Create Team</button>
            </form>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>