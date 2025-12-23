<?php
session_start();
require_once '../php/config.php';
$conn = getDBConnection();

// Role Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'recycler') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch Team Data
$sql = "SELECT u.team_id, t.team_name, t.description, t.points 
        FROM user u 
        LEFT JOIN team t ON u.team_id = t.team_id 
        WHERE u.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

$has_team = $user_data && !empty($user_data['team_id']);

// Fetch Members if in team
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
        .split-container { display: flex; gap: 2rem; margin-top: 2rem; }
        .split-box { flex: 1; background: #fff; padding: 3rem; border-radius: 12px; text-align: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; }
        .icon-large { font-size: 3rem; margin-bottom: 1rem; display: block; }
        .team-hero { background: linear-gradient(135deg, var(--color-primary) 0%, #1F4129 100%); color: white; padding: 2.5rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .member-list { background: white; border-radius: 12px; padding: 0; overflow: hidden; box-shadow: 0 1px 3px 0 rgba(0,0,0,0.1); border: 1px solid #e2e8f0; }
        .member-item { display: flex; justify-content: space-between; padding: 1.25rem; border-bottom: 1px solid #e2e8f0; align-items: center; }
        .member-item:last-child { border-bottom: none; }
        .badge-points { background: #FFD93D; color: #1A202C; padding: 4px 12px; border-radius: 20px; font-weight: bold; font-size: 0.9rem; }
        @media (max-width: 768px) { .split-container { flex-direction: column; } }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <?php if ($has_team): ?>
            <div style="margin-bottom: 20px;">
                <a href="dashboard.php" class="btn btn-secondary">&larr; Back to Dashboard</a>
            </div>
            
            <div class="team-hero">
                <h1 style="margin-bottom: 0.5rem; color: white;"><?php echo htmlspecialchars($user_data['team_name']); ?></h1>
                <p style="opacity: 0.9; font-size: 1.1rem; margin-bottom: 1.5rem; color: #E2E8F0;"><?php echo htmlspecialchars($user_data['description']); ?></p>
                <div style="background: rgba(255,255,255,0.2); display: inline-block; padding: 8px 16px; border-radius: 8px;">
                    <strong>🏆 Total Team Points:</strong> <?php echo number_format($user_data['points']); ?>
                </div>
            </div>

            <h3 style="margin-bottom: 1.5rem;">Team Members</h3>
            <div class="member-list">
                <?php while($mem = $members->fetch_assoc()): ?>
                    <div class="member-item">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="width: 40px; height: 40px; background: #EDF2F7; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">👤</div>
                            <strong><?php echo htmlspecialchars($mem['username']); ?></strong>
                        </div>
                        <span class="badge-points"><?php echo number_format($mem['lifetime_points']); ?> pts</span>
                    </div>
                <?php endwhile; ?>
            </div>

            <div style="margin-top: 2rem; text-align: center;">
                <button onclick="confirmLeaveTeam()" style="background-color: #DC2626; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; cursor: pointer;">
                    Leave Team
                </button>
            </div>

            <script>
            function confirmLeaveTeam() {
                if (confirm("Are you sure you want to leave your team?")) {
                    window.location.href = 'leave_team_process.php';
                }
            }
            </script>

        <?php else: ?>
            <div style="margin-bottom: 20px;">
                <a href="dashboard.php" class="btn btn-secondary">&larr; Back to Dashboard</a>
            </div>
            <h2>Team Management</h2>
            <p style="color: #718096;">You are not part of a team yet. Join forces to recycle more!</p>
            
            <div class="split-container">
                <div class="split-box">
                    <span class="icon-large">🤝</span>
                    <h3>Join a Team</h3>
                    <p style="color: #718096; margin-bottom: 1.5rem;">Browse and join existing teams competing in recycling challenges.</p>
                    <a href="team_join.php" class="btn btn-primary" style="width: 100%; display: inline-block;">Browse Teams</a>
                </div>
                
                <div class="split-box">
                    <span class="icon-large">🚩</span>
                    <h3>Create a Team</h3>
                    <p style="color: #718096; margin-bottom: 1.5rem;">Start your own team and invite others to join your mission.</p>
                    <a href="team_create.php" class="btn btn-secondary" style="width: 100%; display: inline-block;">Create New Team</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>