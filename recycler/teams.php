<?php
session_start();
require_once '../php/config.php';

// Role Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'recycler') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. Fetch current user's team status
$sql = "SELECT u.team_id, t.team_name, t.description, t.points 
        FROM user u 
        LEFT JOIN team t ON u.team_id = t.team_id 
        WHERE u.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

// Check if user is in a team (team_id will be NULL if not)
$has_team = $user_data && !empty($user_data['team_id']);

// 2. If in a team, fetch members
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
    <title>My Team - APRecycle</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        /* Split layout for Join/Create */
        .split-container { display: flex; gap: 2rem; margin-top: 2rem; }
        .split-box { 
            flex: 1; 
            background: var(--color-white); 
            padding: 3rem; 
            border-radius: var(--radius-lg); 
            text-align: center; 
            box-shadow: var(--shadow-md);
            border: 1px solid var(--color-gray-200);
        }
        .icon-large { font-size: 3rem; margin-bottom: 1rem; display: block; }
        
        /* Dashboard Styles */
        .team-hero { 
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%); 
            color: white; 
            padding: 2.5rem; 
            border-radius: var(--radius-lg); 
            margin-bottom: 2rem; 
            box-shadow: var(--shadow-md);
        }
        .member-list { background: white; border-radius: var(--radius-lg); padding: 0; overflow: hidden; box-shadow: var(--shadow-sm); border: 1px solid var(--color-gray-200); }
        .member-item { display: flex; justify-content: space-between; padding: 1.25rem; border-bottom: 1px solid var(--color-gray-200); }
        .member-item:last-child { border-bottom: none; }
        .badge-points { background: var(--color-accent-yellow); color: var(--color-gray-900); padding: 4px 10px; border-radius: 12px; font-weight: bold; font-size: 0.9rem; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <?php if ($has_team): ?>
            <a href="dashboard.php" class="btn btn-secondary mb-4">&larr; Back to Dashboard</a>
            
            <div class="team-hero">
                <h1 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($user_data['team_name']); ?></h1>
                <p style="opacity: 0.9; font-size: 1.1rem; margin-bottom: 1.5rem;"><?php echo htmlspecialchars($user_data['description']); ?></p>
                <div style="background: rgba(255,255,255,0.2); display: inline-block; padding: 8px 16px; border-radius: 8px;">
                    <strong>🏆 Total Team Points:</strong> <?php echo number_format($user_data['points']); ?>
                </div>
            </div>

            <h3 class="mb-4">Team Members</h3>
            <div class="member-list">
                <?php while($mem = $members->fetch_assoc()): ?>
                    <div class="member-item">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 35px; height: 35px; background: var(--color-gray-200); border-radius: 50%; display: flex; align-items: center; justify-content: center;">👤</div>
                            <strong><?php echo htmlspecialchars($mem['username']); ?></strong>
                        </div>
                        <span class="badge-points"><?php echo number_format($mem['lifetime_points']); ?> pts</span>
                    </div>
                <?php endwhile; ?>
            </div>

        <?php else: ?>
            <a href="dashboard.php" class="btn btn-secondary mb-4">&larr; Back to Dashboard</a>
            <h2>Team Management</h2>
            <p class="text-gray-600">You are not part of a team yet. Join forces to recycle more!</p>
            
            <div class="split-container">
                <div class="split-box">
                    <span class="icon-large">🤝</span>
                    <h3>Join a Team</h3>
                    <p class="mb-4 text-gray-600">Find an existing squad and start contributing immediately.</p>
                    <a href="team_join.php" class="btn btn-primary w-100">Browse Teams</a>
                </div>
                
                <div class="split-box">
                    <span class="icon-large">🚩</span>
                    <h3>Create a Team</h3>
                    <p class="mb-4 text-gray-600">Be a leader! Start your own team and invite friends.</p>
                    <a href="team_create.php" class="btn btn-secondary w-100">Create New Team</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>