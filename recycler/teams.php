<?php
// 1. Enable Error Reporting for debugging (Remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../php/config.php';

// Check DB connection
if (!isset($conn)) {
    die("Error: Database connection not found.");
}

// Role Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'recycler') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Fetch current user's team status
// We use specific columns to be safe
$sql = "SELECT u.team_id, t.team_name, t.description, t.points 
        FROM user u 
        LEFT JOIN team t ON u.team_id = t.team_id 
        WHERE u.user_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Database Error (User Query): " . $conn->error);
}

$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    die("Database Execution Error: " . $stmt->error);
}

$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// If user data is missing (e.g. invalid session), redirect to login
if (!$user_data) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

// Check if user is in a team (team_id will be NULL or 0 if not)
$has_team = !empty($user_data['team_id']);

// 3. If in a team, fetch members
$members_result = null;
if ($has_team) {
    $sql_members = "SELECT username, lifetime_points FROM user WHERE team_id = ? ORDER BY lifetime_points DESC";
    $stmt_m = $conn->prepare($sql_members);
    if ($stmt_m) {
        $stmt_m->bind_param("i", $user_data['team_id']);
        $stmt_m->execute();
        $members_result = $stmt_m->get_result();
    } else {
        echo "";
    }
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
            transition: transform 0.3s ease;
        }
        .split-box:hover { transform: translateY(-5px); }
        .icon-large { font-size: 3rem; margin-bottom: 1rem; display: block; }
        
        /* Dashboard Styles */
        .team-hero { 
            background: linear-gradient(135deg, var(--color-primary) 0%, #1F4129 100%); 
            color: white; 
            padding: 2.5rem; 
            border-radius: var(--radius-lg); 
            margin-bottom: 2rem; 
            box-shadow: var(--shadow-md);
        }
        .member-list { background: white; border-radius: var(--radius-lg); padding: 0; overflow: hidden; box-shadow: var(--shadow-sm); border: 1px solid var(--color-gray-200); }
        .member-item { display: flex; justify-content: space-between; padding: 1.25rem; border-bottom: 1px solid var(--color-gray-200); align-items: center; }
        .member-item:last-child { border-bottom: none; }
        .badge-points { background: #FFD93D; color: #1A202C; padding: 4px 12px; border-radius: 20px; font-weight: bold; font-size: 0.9rem; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .split-container { flex-direction: column; }
        }
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
                <p style="opacity: 0.9; font-size: 1.1rem; margin-bottom: 1.5rem; color: #E2E8F0;">
                    <?php echo htmlspecialchars($user_data['description']); ?>
                </p>
                <div style="background: rgba(255,255,255,0.2); display: inline-block; padding: 8px 16px; border-radius: 8px;">
                    <strong>🏆 Total Team Points:</strong> <?php echo number_format($user_data['points']); ?>
                </div>
            </div>

            <h3 class="mb-4">Team Members</h3>
            <div class="member-list">
                <?php if ($members_result && $members_result->num_rows > 0): ?>
                    <?php while($mem = $members_result->fetch_assoc()): ?>
                        <div class="member-item">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div style="width: 40px; height: 40px; background: #EDF2F7; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">👤</div>
                                <strong><?php echo htmlspecialchars($mem['username']); ?></strong>
                            </div>
                            <span class="badge-points"><?php echo number_format($mem['lifetime_points']); ?> pts</span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="member-item">No members found (Wait, that includes you? Something is weird).</div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <div style="margin-bottom: 20px;">
                <a href="dashboard.php" class="btn btn-secondary">&larr; Back to Dashboard</a>
            </div>
            <h2>Team Management</h2>
            <p class="text-gray-600">You are not part of a team yet. Join forces to recycle more!</p>
            
            <div class="split-container">
                <div class="split-box">
                    <span class="icon-large">🤝</span>
                    <h3>Join a Team</h3>
                    <p class="mb-4 text-gray-600">Browse and join existing teams competing in recycling challenges.</p>
                    <a href="team_join.php" class="btn btn-primary w-100">Browse Teams</a>
                </div>
                
                <div class="split-box">
                    <span class="icon-large">🚩</span>
                    <h3>Create a Team</h3>
                    <p class="mb-4 text-gray-600">Start your own team and invite others to join your mission.</p>
                    <a href="team_create.php" class="btn btn-secondary w-100">Create Team</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>