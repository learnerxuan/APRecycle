<?php
session_start();
require_once '../php/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'recycler') {
    header("Location: ../login.php");
    exit();
}

// Handle Join Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['team_id'])) {
    $team_id = intval($_POST['team_id']);
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("UPDATE user SET team_id = ? WHERE user_id = ?");
    $stmt->bind_param("ii", $team_id, $user_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Successfully joined the team!'); window.location.href='teams.php';</script>";
        exit();
    } else {
        $error = "Error joining team.";
    }
}

// Fetch Teams (Strict SQL Mode Compatible)
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
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Join Team - APRecycle</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .team-card {
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius-md);
            margin-bottom: 1rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--color-gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.2s;
        }
        .team-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .search-input { padding: 10px; border-radius: 6px; border: 1px solid #ccc; width: 300px; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <a href="teams.php" class="btn btn-secondary mb-4">&larr; Back</a>
        <h2>Join a Team</h2>
        
        <form method="GET" class="mb-4" style="display:flex; gap:10px;">
            <input type="text" name="search" class="search-input" placeholder="Search team name..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="team-card">
                    <div>
                        <h3 style="margin:0; color: var(--color-primary);"><?php echo htmlspecialchars($row['team_name']); ?></h3>
                        <p style="margin: 5px 0; color: var(--color-gray-600); font-size: 0.9rem;">
                            <?php echo htmlspecialchars($row['description']); ?>
                        </p>
                        <small style="color: var(--color-gray-500);">
                            👥 Members: <?php echo $row['member_count']; ?> &bull; 🏆 Points: <?php echo number_format($row['points']); ?>
                        </small>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="team_id" value="<?php echo $row['team_id']; ?>">
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Join <?php echo htmlspecialchars($row['team_name']); ?>?');">Join</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No teams found. Why not create one?</p>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>