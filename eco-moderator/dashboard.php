<?php
session_start();
require_once '../php/config.php';

// Ensure user is logged in and is an eco-moderator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'eco-moderator') {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Dashboard';
require_once 'includes/header.php';

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// 1. Fetch Stats
// Pending Reviews
$sql_pending = "SELECT COUNT(*) as count FROM recycling_submission WHERE status = 'pending'";
$res_pending = $conn->query($sql_pending);
$pending_count = $res_pending->fetch_assoc()['count'];

// Total Processed (Approved or Rejected) - Global Count
$sql_processed = "SELECT COUNT(*) as count FROM recycling_submission WHERE status IN ('approved', 'rejected')";
$res_processed = $conn->query($sql_processed);
$processed_count = $res_processed->fetch_assoc()['count'];

// My Content Created
$sql_content = "SELECT COUNT(*) as count FROM educational_content WHERE author_id = ?";
$stmt_content = $conn->prepare($sql_content);
$stmt_content->bind_param("i", $user_id);
$stmt_content->execute();
$content_count = $stmt_content->get_result()->fetch_assoc()['count'];
$stmt_content->close();

// 2. Fetch Recent Activity (Last 5 Submissions)
$sql_recent = "SELECT s.submission_id, s.image_url, s.ai_confidence, s.status, s.created_at, u.username, m.material_name 
               FROM recycling_submission s
               LEFT JOIN user u ON s.user_id = u.user_id
               LEFT JOIN submission_material sm ON s.submission_id = sm.submission_id
               LEFT JOIN material m ON sm.material_id = m.material_id
               ORDER BY s.created_at DESC LIMIT 5";
$recent_submissions = $conn->query($sql_recent);

?>

<div class="page-header">
    <h1 class="page-title">Eco-Moderator Dashboard</h1>
    <p class="page-description">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>. Overview of recycling activities.</p>
</div>

<!-- Stats Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--space-6); margin-bottom: var(--space-8);">
    
    <!-- Pending Card -->
    <div style="background: white; padding: var(--space-6); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border-left: 4px solid var(--color-warning);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
            <div>
                <p style="color: var(--color-gray-600); margin: 0; font-size: var(--text-sm); font-weight: 500;">Pending Reviews</p>
                <h2 style="font-size: var(--text-3xl); margin: var(--space-1) 0 0 0; color: var(--color-gray-900);"><?php echo $pending_count; ?></h2>
            </div>
            <div style="padding: var(--space-3); background: #FFFBEB; border-radius: var(--radius-full); color: var(--color-warning);">
                <i class="fas fa-clock fa-lg"></i>
            </div>
        </div>
        <a href="review-queue.php" style="color: var(--color-warning); font-size: var(--text-sm); font-weight: 500; text-decoration: none; display: flex; align-items: center; gap: var(--space-1);">
            Review Queue <i class="fas fa-arrow-right"></i>
        </a>
    </div>

    <!-- Processed Card -->
    <div style="background: white; padding: var(--space-6); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border-left: 4px solid var(--color-success);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
            <div>
                <p style="color: var(--color-gray-600); margin: 0; font-size: var(--text-sm); font-weight: 500;">Items Processed (Global)</p>
                <h2 style="font-size: var(--text-3xl); margin: var(--space-1) 0 0 0; color: var(--color-gray-900);"><?php echo $processed_count; ?></h2>
            </div>
            <div style="padding: var(--space-3); background: #ECFDF5; border-radius: var(--radius-full); color: var(--color-success);">
                <i class="fas fa-check-circle fa-lg"></i>
            </div>
        </div>
        <div style="font-size: var(--text-sm); color: var(--color-gray-500);">Total approved/rejected items</div>
    </div>

    <!-- Content Card -->
    <div style="background: white; padding: var(--space-6); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border-left: 4px solid var(--color-primary);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
            <div>
                <p style="color: var(--color-gray-600); margin: 0; font-size: var(--text-sm); font-weight: 500;">My Content</p>
                <h2 style="font-size: var(--text-3xl); margin: var(--space-1) 0 0 0; color: var(--color-gray-900);"><?php echo $content_count; ?></h2>
            </div>
            <div style="padding: var(--space-3); background: #EFF6FF; border-radius: var(--radius-full); color: var(--color-primary);">
                <i class="fas fa-pen-nib fa-lg"></i>
            </div>
        </div>
        <a href="content-creation.php" style="color: var(--color-primary); font-size: var(--text-sm); font-weight: 500; text-decoration: none; display: flex; align-items: center; gap: var(--space-1);">
            Create Article <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</div>

<!-- Recent Activity Section -->
<h2 style="font-size: var(--text-xl); color: var(--color-gray-800); margin-bottom: var(--space-4);">Recent Submissions</h2>
<div style="background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); overflow: hidden;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background: var(--color-gray-50); border-bottom: 1px solid var(--color-gray-200);">
                <tr>
                    <th style="padding: var(--space-4); font-size: var(--text-xs); font-weight: 600; color: var(--color-gray-600); text-transform: uppercase;">ID</th>
                    <th style="padding: var(--space-4); font-size: var(--text-xs); font-weight: 600; color: var(--color-gray-600); text-transform: uppercase;">User</th>
                    <th style="padding: var(--space-4); font-size: var(--text-xs); font-weight: 600; color: var(--color-gray-600); text-transform: uppercase;">Material</th>
                    <th style="padding: var(--space-4); font-size: var(--text-xs); font-weight: 600; color: var(--color-gray-600); text-transform: uppercase;">Confidence</th>
                    <th style="padding: var(--space-4); font-size: var(--text-xs); font-weight: 600; color: var(--color-gray-600); text-transform: uppercase;">Date</th>
                    <th style="padding: var(--space-4); font-size: var(--text-xs); font-weight: 600; color: var(--color-gray-600); text-transform: uppercase;">Status</th>
                    <th style="padding: var(--space-4); font-size: var(--text-xs); font-weight: 600; color: var(--color-gray-600); text-transform: uppercase;">Action</th>
                </tr>
            </thead>
            <tbody style="font-size: var(--text-sm);">
                <?php if ($recent_submissions && $recent_submissions->num_rows > 0): ?>
                        <?php while ($row = $recent_submissions->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid var(--color-gray-100);">
                                <td style="padding: var(--space-4); color: var(--color-gray-600);">#<?php echo $row['submission_id']; ?></td>
                                <td style="padding: var(--space-4); font-weight: 500; color: var(--color-gray-900);"><?php echo htmlspecialchars($row['username'] ?? 'Unknown'); ?></td>
                                <td style="padding: var(--space-4); color: var(--color-gray-700);"><?php echo htmlspecialchars($row['material_name'] ?? 'Unclassified'); ?></td>
                                <td style="padding: var(--space-4); color: var(--color-gray-600);">
                                    <?php
                                    $conf = $row['ai_confidence'] * 100;
                                    echo number_format($conf, 1) . '%';
                                    ?>
                                </td>
                                <td style="padding: var(--space-4); color: var(--color-gray-600);"><?php echo date('M d, H:i', strtotime($row['created_at'])); ?></td>
                                <td style="padding: var(--space-4);">
                                    <?php
                                    $status_color = 'var(--color-gray-500)';
                                    $bg_color = 'var(--color-gray-100)';
                                    $label = ucfirst($row['status']);

                                    if ($row['status'] == 'approved') {
                                        $status_color = 'var(--color-success)';
                                        $bg_color = '#ECFDF5';
                                    } elseif ($row['status'] == 'rejected') {
                                        $status_color = 'var(--color-error)';
                                        $bg_color = '#FEF2F2';
                                    } elseif ($row['status'] == 'pending') {
                                        $status_color = 'var(--color-warning)';
                                        $bg_color = '#FFFBEB';
                                    }
                                    ?>
                                    <span style="display: inline-block; padding: 2px 8px; border-radius: var(--radius-full); font-size: 11px; font-weight: 600; color: <?php echo $status_color; ?>; background: <?php echo $bg_color; ?>;">
                                        <?php echo $label; ?>
                                    </span>
                                </td>
                                <td style="padding: var(--space-4);">
                                    <?php if ($row['status'] == 'pending'): ?>
                                        <a href="review-queue.php?id=<?php echo $row['submission_id']; ?>" style="color: var(--color-primary); font-weight: 500; text-decoration: none;">Review</a>
                                    <?php else: ?>
                                        <span style="color: var(--color-gray-400);">View</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                <?php else: ?>
                        <tr>
                            <td colspan="7" style="padding: var(--space-8); text-align: center; color: var(--color-gray-500);">No recent submissions found.</td>
                        </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>