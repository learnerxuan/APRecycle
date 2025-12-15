<?php
session_start();
require_once '../php/config.php';

// Handle Join Request
if (isset($_POST['join_team_id'])) {
    $team_id = $_POST['join_team_id'];
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("UPDATE user SET team_id = ? WHERE user_id = ?");
    $stmt->bind_param("ii", $team_id, $user_id);
    if ($stmt->execute()) {
        header("Location: teams.php");
        exit();
    }
}

// Fetch Teams with Member Count
$search = $_GET['search'] ?? '';
$sql = "SELECT t.*, COUNT(u.user_id) as member_count 
        FROM team t 
        LEFT JOIN user u ON t.team_id = u.team_id 
        WHERE t.team_name LIKE ? 
        GROUP BY t.team_id 
        ORDER BY t.points DESC";
$stmt = $conn->prepare($sql);
$search_param = "%$search%";
$stmt->bind_param("s", $search_param);
$stmt->execute();
$teams = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Join a Team - APRecycle</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .team-list-item { background: white; padding: 1.5rem; margin-bottom: 1rem; border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center; box-shadow: var(--shadow-sm); }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <a href="teams.php" class="btn btn-secondary mb-4">&larr; Back</a>
        
        <h2>Join a Team</h2>
        
        <form method="GET" class="mb-4">
            <input type="text" name="search" placeholder="Search teams..." value="<?php echo htmlspecialchars($search); ?>" style="padding: 10px; width: 300px; border-radius: 5px; border: 1px solid #ccc;">
            <button type="submit" class="btn btn-secondary">Search</button>
        </form>

        <?php while($team = $teams->fetch_assoc()): ?>
            <div class="team-list-item">
                <div>
                    <h3 style="margin:0;"><?php echo htmlspecialchars($team['team_name']); ?></h3>
                    <small class="text-gray-500"><?php echo $team['member_count']; ?> members &bull; <?php echo number_format($team['points']); ?> pts</small>
                    <p style="margin: 0.5rem 0 0; font-size: 0.9rem;"><?php echo htmlspecialchars($team['description']); ?></p>
                </div>
                <form method="POST">
                    <input type="hidden" name="join_team_id" value="<?php echo $team['team_id']; ?>">
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Join this team?')">Join</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>