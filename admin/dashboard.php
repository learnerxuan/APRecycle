<?php
session_start();
require_once '../php/config.php';

// Ensure user is logged in as administrator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrator') {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Admin Dashboard';
require_once 'includes/header.php';

$conn = getDBConnection();

// 1. Fetch Key System Metrics
// Total Recyclers
$result = $conn->query("SELECT COUNT(*) as count FROM user WHERE role = 'recycler'");
$total_recyclers = $result->fetch_assoc()['count'];

// Total Recycled Items (Approved)
$result = $conn->query("SELECT COUNT(*) as count FROM recycling_submission WHERE status = 'approved'");
$total_recycled = $result->fetch_assoc()['count'];

// Total Challenges (Active)
$result = $conn->query("SELECT COUNT(*) as count FROM challenge WHERE start_date <= CURDATE() AND end_date >= CURDATE()");
$active_challenges = $result->fetch_assoc()['count'];

// Total Eco-Moderators
$result = $conn->query("SELECT COUNT(*) as count FROM user WHERE role = 'eco-moderator'");
$total_moderators = $result->fetch_assoc()['count'];

// 2. Fetch Recent System Activity (Combined View)
// We'll show the latest 5 approved submissions as a proxy for "Activity"
$sql_activity = "SELECT s.submission_id, s.created_at, u.username, m.material_name, s.ai_confidence
                 FROM recycling_submission s
                 LEFT JOIN user u ON s.user_id = u.user_id
                 LEFT JOIN submission_material sm ON s.submission_id = sm.submission_id
                 LEFT JOIN material m ON sm.material_id = m.material_id
                 WHERE s.status = 'approved'
                 ORDER BY s.created_at DESC LIMIT 5";
$recent_activity = $conn->query($sql_activity);
?>

<div class="page-header">
    <h1 class="page-title">Admin Overview</h1>
    <p class="page-description">High-level insights into the APRecycle system performance.</p>
</div>

<!-- Statistics Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: var(--space-6); margin-bottom: var(--space-8);">
    
    <!-- User Count card -->
    <div style="background: white; padding: var(--space-6); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border-bottom: 4px solid var(--color-primary);">
        <div style="display: flex; justify-content: space-between; align-items: start;">
            <div>
                <p style="color: var(--color-gray-600); margin: 0; font-size: var(--text-sm); font-weight: 500;">Total Recyclers</p>
                <h2 style="font-size: var(--text-4xl); margin: var(--space-2) 0 0 0; color: var(--color-gray-900);"><?php echo $total_recyclers; ?></h2>
            </div>
            <div style="padding: var(--space-3); background: var(--color-gray-100); border-radius: var(--radius-full); color: var(--color-primary);">
                <i class="fas fa-users fa-lg"></i>
            </div>
        </div>
    </div>

    <!-- Recycled Items Count -->
    <div style="background: white; padding: var(--space-6); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border-bottom: 4px solid var(--color-success);">
        <div style="display: flex; justify-content: space-between; align-items: start;">
            <div>
                <p style="color: var(--color-gray-600); margin: 0; font-size: var(--text-sm); font-weight: 500;">Items Recycled</p>
                <h2 style="font-size: var(--text-4xl); margin: var(--space-2) 0 0 0; color: var(--color-gray-900);"><?php echo $total_recycled; ?></h2>
            </div>
            <div style="padding: var(--space-3); background: #ECFDF5; border-radius: var(--radius-full); color: var(--color-success);">
                <i class="fas fa-recycle fa-lg"></i>
            </div>
        </div>
    </div>

    <!-- Active Challenges -->
    <div style="background: white; padding: var(--space-6); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border-bottom: 4px solid var(--color-warning);">
        <div style="display: flex; justify-content: space-between; align-items: start;">
            <div>
                <p style="color: var(--color-gray-600); margin: 0; font-size: var(--text-sm); font-weight: 500;">Active Challenges</p>
                <h2 style="font-size: var(--text-4xl); margin: var(--space-2) 0 0 0; color: var(--color-gray-900);"><?php echo $active_challenges; ?></h2>
            </div>
            <div style="padding: var(--space-3); background: #FFFBEB; border-radius: var(--radius-full); color: var(--color-warning);">
                <i class="fas fa-trophy fa-lg"></i>
            </div>
        </div>
    </div>

    <!-- Eco-Moderators -->
    <div style="background: white; padding: var(--space-6); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border-bottom: 4px solid var(--color-secondary);">
        <div style="display: flex; justify-content: space-between; align-items: start;">
            <div>
                <p style="color: var(--color-gray-600); margin: 0; font-size: var(--text-sm); font-weight: 500;">Eco-Moderators</p>
                <h2 style="font-size: var(--text-4xl); margin: var(--space-2) 0 0 0; color: var(--color-gray-900);"><?php echo $total_moderators; ?></h2>
            </div>
            <div style="padding: var(--space-3); background: #F3E8FF; border-radius: var(--radius-full); color: var(--color-secondary);">
                <i class="fas fa-user-shield fa-lg"></i>
            </div>
        </div>
    </div>
</div>

<!-- System Shortcuts -->
<h2 style="font-size: var(--text-xl); color: var(--color-gray-800); margin-bottom: var(--space-4);">System Shortcuts</h2>
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--space-6); margin-bottom: var(--space-8);">
    <a href="challenges.php" style="display: flex; align-items: center; gap: var(--space-4); padding: var(--space-4); background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); text-decoration: none; color: var(--color-gray-800); transition: all 0.3s ease;">
        <div style="padding: var(--space-3); background: #EFF6FF; border-radius: var(--radius-md); color: var(--color-primary);">
            <i class="fas fa-trophy"></i>
        </div>
        <div>
            <h4 style="margin: 0; font-size: var(--text-base);">Manage Challenges</h4>
            <p style="margin: 0; font-size: var(--text-sm); color: var(--color-gray-500);">Create or edit challenges</p>
        </div>
        <i class="fas fa-chevron-right" style="margin-left: auto; color: var(--color-gray-400);"></i>
    </a>

    <a href="moderators.php" style="display: flex; align-items: center; gap: var(--space-4); padding: var(--space-4); background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); text-decoration: none; color: var(--color-gray-800); transition: all 0.3s ease;">
        <div style="padding: var(--space-3); background: #F3E8FF; border-radius: var(--radius-md); color: var(--color-secondary);">
            <i class="fas fa-users-cog"></i>
        </div>
        <div>
            <h4 style="margin: 0; font-size: var(--text-base);">Manage Moderators</h4>
            <p style="margin: 0; font-size: var(--text-sm); color: var(--color-gray-500);">Add or remove staff</p>
        </div>
        <i class="fas fa-chevron-right" style="margin-left: auto; color: var(--color-gray-400);"></i>
    </a>

    <a href="reports.php" style="display: flex; align-items: center; gap: var(--space-4); padding: var(--space-4); background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); text-decoration: none; color: var(--color-gray-800); transition: all 0.3s ease;">
        <div style="padding: var(--space-3); background: #FFFBEB; border-radius: var(--radius-md); color: var(--color-warning);">
            <i class="fas fa-file-alt"></i>
        </div>
        <div>
            <h4 style="margin: 0; font-size: var(--text-base);">View Reports</h4>
            <p style="margin: 0; font-size: var(--text-sm); color: var(--color-gray-500);">System analytics</p>
        </div>
        <i class="fas fa-chevron-right" style="margin-left: auto; color: var(--color-gray-400);"></i>
    </a>
</div>

<!-- Recent Activity Section (Full Width) -->
<div>
    <h2 style="font-size: var(--text-xl); color: var(--color-gray-800); margin-bottom: var(--space-4);">Recent Recycling Activity</h2>
    <div style="background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background: var(--color-gray-50); border-bottom: 1px solid var(--color-gray-200);">
                <tr>
                    <th style="padding: var(--space-4); text-align: left; font-size: var(--text-xs); font-weight: 600; color: var(--color-gray-600); text-transform: uppercase;">User</th>
                    <th style="padding: var(--space-4); text-align: left; font-size: var(--text-xs); font-weight: 600; color: var(--color-gray-600); text-transform: uppercase;">Material</th>
                    <th style="padding: var(--space-4); text-align: left; font-size: var(--text-xs); font-weight: 600; color: var(--color-gray-600); text-transform: uppercase;">Date</th>
                    <th style="padding: var(--space-4); text-align: left; font-size: var(--text-xs); font-weight: 600; color: var(--color-gray-600); text-transform: uppercase;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($recent_activity->num_rows > 0): ?>
                    <?php while($row = $recent_activity->fetch_assoc()): ?>
                    <tr style="border-bottom: 1px solid var(--color-gray-100);">
                        <td style="padding: var(--space-4); font-weight: 500; color: var(--color-gray-900);">
                            <div style="display: flex; align-items: center; gap: var(--space-3);">
                                <div style="width: 24px; height: 24px; background: var(--color-gray-200); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; color: var(--color-gray-600); font-weight: bold;">
                                    <?php echo strtoupper(substr($row['username'], 0, 1)); ?>
                                </div>
                                <?php echo htmlspecialchars($row['username']); ?>
                            </div>
                        </td>
                        <td style="padding: var(--space-4); color: var(--color-gray-700);"><?php echo htmlspecialchars($row['material_name'] ?? 'Unclassified'); ?></td>
                        <td style="padding: var(--space-4); color: var(--color-gray-600); font-size: var(--text-sm);"><?php echo date('M d, H:i', strtotime($row['created_at'])); ?></td>
                        <td style="padding: var(--space-4);">
                            <span style="display: inline-block; padding: 2px 8px; border-radius: var(--radius-full); font-size: 11px; font-weight: 600; color: var(--color-success); background: #ECFDF5;">Approved</span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="padding: var(--space-6); text-align: center; color: var(--color-gray-500);">No recent activity.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
require_once 'includes/footer.php'; 
$conn->close();
?>
