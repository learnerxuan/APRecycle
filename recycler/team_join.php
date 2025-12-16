<?php
session_start();
require_once '../php/config.php';
$conn = getDBConnection();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'recycler') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['team_id'])) {
    $team_id = intval($_POST['team_id']);
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("UPDATE user SET team_id = ? WHERE user_id = ?");
    $stmt->bind_param("ii", $team_id, $user_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Successfully joined the team!'); window.location.href='teams.php';</script>";
        exit();
    }
}

$search = $_GET['search'] ?? '';
$search_term = "%$search%";

$sql = "SELECT t.team_id, t.team_name, t.description, t.points, COUNT(u.user_id) as member_count 
        FROM team t 
        LEFT JOIN user u ON t.team_id = u.team_id 
        WHERE t.team_name LIKE ? 
        GROUP BY t.team_id, t.team_name, t.description, t.points 
        ORDER BY t.points DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $search_term);
$stmt->execute();
$teams = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Join Team - APRecycle</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .team-list-item { background: white; padding: 1.5rem; margin-bottom: 1rem; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; transition: transform 0.2s; }
        .team-list-item:hover { transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <a href="teams.php" class="btn btn-secondary" style="margin-bottom: 1.5rem; display: inline-block;">&larr; Back</a>
        
        <h2>Join a Team</h2>
        
        <form method="GET" style="display:flex; gap:10px; margin-bottom: 1.5rem;">
            <input type="text" name="search" placeholder="Search teams..." value="<?php echo htmlspecialchars($search); ?>" style="padding: 10px; width: 300px; border-radius: 6px; border: 1px solid #CBD5E0;">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <?php if ($teams->num_rows > 0): ?>
            <?php while($team = $teams->fetch_assoc()): ?>
                <div class="team-list-item">
                    <div>
                        <h3 style="margin:0; color: var(--color-primary);"><?php echo htmlspecialchars($team['team_name']); ?></h3>
                        <small style="color: #718096;">👥 <?php echo $team['member_count']; ?> members &bull; 🏆 <?php echo number_format($team['points']); ?> pts</small>
                        <p style="margin: 0.5rem 0 0; font-size: 0.9rem; color: #4A5568;"><?php echo htmlspecialchars($team['description']); ?></p>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="team_id" value="<?php echo $team['team_id']; ?>">
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Join <?php echo htmlspecialchars($team['team_name']); ?>?')">Join</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No teams found.</p>
        <?php endif; ?>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>