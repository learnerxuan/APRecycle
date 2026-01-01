<?php
session_start();
require_once '../php/config.php';
$conn = getDBConnection();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'recycler') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leave_team'])) {
    $update_stmt = $conn->prepare("UPDATE user SET team_id = NULL WHERE user_id = ?");
    $update_stmt->bind_param("i", $user_id);
    if ($update_stmt->execute()) {
        header("Location: teams.php");
        exit();
    }
}

$sql = "SELECT u.team_id, t.team_name, t.description, t.points FROM user u LEFT JOIN team t ON u.team_id = t.team_id WHERE u.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$has_team = $user_data && !empty($user_data['team_id']);

$members = null;
if ($has_team) {
    $sql_members = "SELECT username, lifetime_points FROM user WHERE team_id = ? ORDER BY lifetime_points DESC";
    $stmt_m = $conn->prepare($sql_members);
    $stmt_m->bind_param("i", $user_data['team_id']);
    $stmt_m->execute();
    $members = $stmt_m->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Team - APRecycle</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .page-hero {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
            color: white;
            padding: 3rem 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .page-hero h1 { font-size: 2.25rem; font-weight: 700; margin-bottom: 0.5rem; }
        .page-hero p { font-size: 1.125rem; opacity: 0.9; margin: 0; }
        
        .split-container { display: flex; gap: 2rem; margin-top: 2rem; }
        .split-box { flex: 1; background: white; padding: 3rem; border-radius: 12px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; display: flex; flex-direction: column; justify-content: space-between; }
        .icon-large { font-size: 3rem; margin-bottom: 1rem; display: block; }
        
        .member-list { background: white; border-radius: 12px; padding: 0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; }
        .member-item { display: flex; justify-content: space-between; padding: 1.25rem; border-bottom: 1px solid #edf2f7; align-items: center; }
        .member-item:last-child { border-bottom: none; }
        .badge-points { background: #FFD93D; color: #1A202C; padding: 4px 12px; border-radius: 20px; font-weight: bold; font-size: 0.9rem; }

        .btn-primary { display: block; width: 100%; text-decoration: none; text-align: center; border-radius: 8px; padding: 0.75rem; }
        .btn-secondary { display: block; width: 100%; padding: 0.75rem; border-radius: 8px; font-weight: 600; text-align: center; text-decoration: none; color: #4a5568; background: #e2e8f0; transition: all 0.2s; }
        .btn-secondary:hover { background: #cbd5e0; }

        .leave-btn { background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3); color: white; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.85rem; transition: all 0.2s; margin-top: 1rem; }
        .leave-btn:hover { background: rgba(220, 38, 38, 0.8); border-color: transparent; }

        @media (max-width: 768px) { 
            .split-container { flex-direction: column; } 
            .page-hero { padding: 2rem 1rem; }
            .page-hero h1 { font-size: 1.75rem; }
            .split-box { padding: 2rem; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container" style="padding-top: 2rem;">
        <?php if ($has_team): ?>
            <div class="page-hero">
                <h1><?php echo htmlspecialchars($user_data['team_name']); ?></h1>
                <p><?php echo htmlspecialchars($user_data['description']); ?></p>
                <div style="margin-top: 1.5rem; display: inline-block; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 20px; font-weight: bold;">
                    🏆 <?php echo number_format($user_data['points']); ?> Points
                </div>
                <div>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to leave this team?');">
                        <input type="hidden" name="leave_team" value="1">
                        <button type="submit" class="leave-btn">Leave Team</button>
                    </form>
                </div>
            </div>

            <h3 style="margin-bottom: 1.5rem; color: #2d3748;">Team Members</h3>
            <div class="member-list">
                <?php while($mem = $members->fetch_assoc()): ?>
                    <div class="member-item">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 35px; height: 35px; background: #edf2f7; border-radius: 50%; display: flex; align-items: center; justify-content: center;">👤</div>
                            <strong><?php echo htmlspecialchars($mem['username']); ?></strong>
                        </div>
                        <span class="badge-points"><?php echo number_format($mem['lifetime_points']); ?> pts</span>
                    </div>
                <?php endwhile; ?>
            </div>

        <?php else: ?>
            <div class="page-hero">
                <h1>Team Management</h1>
                <p>Join forces with others to recycle more and climb the leaderboard!</p>
            </div>
            
            <div class="split-container">
                <div class="split-box">
                    <div>
                        <span class="icon-large">🤝</span>
                        <h3>Join a Team</h3>
                        <p style="margin-bottom: 2rem; color: #718096;">Find an existing squad and start contributing immediately.</p>
                    </div>
                    <a href="team_join.php" class="btn btn-primary">Browse Teams</a>
                </div>
                
                <div class="split-box">
                    <div>
                        <span class="icon-large">🚩</span>
                        <h3>Create a Team</h3>
                        <p style="margin-bottom: 2rem; color: #718096;">Be a leader! Start your own team and invite friends.</p>
                    </div>
                    <a href="team_create.php" class="btn btn-secondary">Create New Team</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>