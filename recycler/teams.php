<?php
session_start();
require_once '../php/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'recycler') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if user is already in a team
$stmt = $conn->prepare("SELECT u.team_id, t.team_name, t.description, t.points, t.date_created 
                        FROM user u 
                        LEFT JOIN team t ON u.team_id = t.team_id 
                        WHERE u.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_team = $stmt->get_result()->fetch_assoc();

// If user is in a team, fetch members
$members = [];
if ($user_team['team_id']) {
    $m_stmt = $conn->prepare("SELECT username, lifetime_points FROM user WHERE team_id = ? ORDER BY lifetime_points DESC");
    $m_stmt->bind_param("i", $user_team['team_id']);
    $m_stmt->execute();
    $members = $m_stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Team - APRecycle</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .split-container { display: flex; gap: 2rem; margin-top: 2rem; }
        .split-box { flex: 1; background: white; padding: 3rem; border-radius: var(--radius-lg); text-align: center; box-shadow: var(--shadow-md); transition: transform 0.3s; }
        .split-box:hover { transform: translateY(-5px); }
        .icon-large { font-size: 4rem; margin-bottom: 1rem; display: block; }
        
        /* Team Dashboard Styles */
        .team-hero { background: var(--color-primary); color: white; padding: 2rem; border-radius: var(--radius-lg); margin-bottom: 2rem; }
        .member-list { background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm); }
        .member-item { display: flex; justify-content: space-between; padding: 1rem; border-bottom: 1px solid var(--color-gray-200); }
        .member-item:last-child { border-bottom: none; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <?php if ($user_team['team_id']): ?>
            <div class="team-hero">
                <h1><?php echo htmlspecialchars($user_team['team_name']); ?></h1>
                <p><?php echo htmlspecialchars($user_team['description']); ?></p>
                <div style="margin-top: 1rem; font-size: 1.2rem; font-weight: bold;">
                    Total Team Points: <?php echo number_format($user_team['points']); ?>
                </div>
            </div>

            <div class="member-list">
                <h3>Team Members</h3>
                <?php while($member = $members->fetch_assoc()): ?>
                    <div class="member-item">
                        <span>👤 <?php echo htmlspecialchars($member['username']); ?></span>
                        <span class="badge badge-success"><?php echo $member['lifetime_points']; ?> pts</span>
                    </div>
                <?php endwhile; ?>
            </div>

        <?php else: ?>
            <h2>Team Management</h2>
            <p>Join an existing team or create your own to compete in challenges.</p>
            
            <div class="split-container">
                <div class="split-box">
                    <span class="icon-large">🤝</span>
                    <h3>Join a Team</h3>
                    <p class="mb-4">Browse and join existing teams competing in recycling challenges.</p>
                    <a href="team_join.php" class="btn btn-primary">Browse Teams</a>
                </div>
                
                <div class="split-box">
                    <span class="icon-large">🚩</span>
                    <h3>Create a Team</h3>
                    <p class="mb-4">Start your own team and invite others to join your mission.</p>
                    <a href="team_create.php" class="btn btn-secondary">Create Team</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>