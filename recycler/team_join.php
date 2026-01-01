<?php
session_start();
require_once '../php/config.php';
$conn = getDBConnection();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'recycler') { header("Location: ../login.php"); exit(); }

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
$sql = "SELECT t.team_id, t.team_name, t.description, t.points, COUNT(u.user_id) as member_count FROM team t LEFT JOIN user u ON t.team_id = u.team_id WHERE t.team_name LIKE ? GROUP BY t.team_id, t.team_name, t.description, t.points ORDER BY t.points DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $search_term);
$stmt->execute();
$teams = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Team - APRecycle</title>
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

        .team-list-item { background: white; padding: 2rem; margin-bottom: 1.5rem; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; transition: transform 0.2s, box-shadow 0.2s; }
        .team-list-item:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border-color: var(--color-primary); }
        .search-input { padding: 12px; width: 300px; border-radius: 8px; border: 1px solid #e2e8f0; outline: none; transition: border-color 0.2s; }
        .search-input:focus { border-color: var(--color-primary); }

        @media (max-width: 768px) {
            .page-hero { padding: 2rem 1rem; }
            .team-list-item { flex-direction: column; align-items: flex-start; gap: 1rem; padding: 1.5rem; }
            .team-list-item form { width: 100%; }
            .team-list-item button { width: 100%; }
            .team-list-item > div { padding-right: 0 !important; }
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
            <h1>Join a Team</h1>
            <p>Find allies, compete in challenges, and make a collective impact.</p>
        </div>
        
        <form method="GET" style="display:flex; gap:12px; margin-bottom: 2.5rem; align-items: center; justify-content: center; flex-wrap: wrap;">
            <input type="text" name="search" class="search-input" placeholder="Search teams..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary" style="text-decoration: none; border-radius: 8px; padding: 12px 24px;">Search</button>
        </form>

        <?php if ($teams->num_rows > 0): ?>
            <?php while($team = $teams->fetch_assoc()): ?>
                <div class="team-list-item">
                    <div style="padding-right: 2rem;">
                        <h3 style="margin: 0 0 0.5rem 0; color: #2d3748; font-size: 1.25rem;"><?php echo htmlspecialchars($team['team_name']); ?></h3>
                        <div style="margin-bottom: 0.75rem; color: #718096; font-size: 0.875rem;">
                            <span><?php echo $team['member_count']; ?> members</span>
                            <span style="margin: 0 4px;">&bull;</span>
                            <span><?php echo number_format($team['points']); ?> pts</span>
                        </div>
                        <p style="margin: 0; color: #4a5568; line-height: 1.5;"><?php echo htmlspecialchars($team['description']); ?></p>
                    </div>
                    <form method="POST" style="flex-shrink: 0;">
                        <input type="hidden" name="team_id" value="<?php echo $team['team_id']; ?>">
                        <button type="submit" class="btn btn-primary" style="text-decoration: none; border-radius: 8px; padding: 10px 24px;" onclick="return confirm('Join <?php echo htmlspecialchars($team['team_name']); ?>?')">Join</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 3rem; background: white; border-radius: 12px; color: #718096; border: 1px solid #e2e8f0;">
                <p style="font-size: 1.1rem;">No teams found matching your search.</p>
            </div>
        <?php endif; ?>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>