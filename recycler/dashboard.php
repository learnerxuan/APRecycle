<?php
session_start();
require_once '../php/config.php';

// Check if user is logged in and is a recycler
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'recycler') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$conn = getDBConnection();

// Get user data including QR code
$stmt = mysqli_prepare($conn, "SELECT user_id, username, email, qr_code, lifetime_points FROM user WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Generate QR code if not exists
if (empty($user['qr_code'])) {
    require_once 'generate-qr.php';
    $qr_data = generateRecyclerQRData($user_id, $username);

    $update_stmt = mysqli_prepare($conn, "UPDATE user SET qr_code = ? WHERE user_id = ?");
    mysqli_stmt_bind_param($update_stmt, "si", $qr_data, $user_id);
    mysqli_stmt_execute($update_stmt);

    $user['qr_code'] = $qr_data;
}

// Get QR code image URL
function getQRImageURL($qr_data)
{
    return "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qr_data);
}

$qr_image_url = getQRImageURL($user['qr_code']);

// Get recent submissions count
$recent_stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM recycling_submission WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
mysqli_stmt_bind_param($recent_stmt, "i", $user_id);
mysqli_stmt_execute($recent_stmt);
$recent_result = mysqli_stmt_get_result($recent_stmt);
$recent_count = mysqli_fetch_assoc($recent_result)['count'];

// Get user's current rank among all recyclers
$rank_stmt = mysqli_prepare($conn, "SELECT COUNT(*) + 1 AS user_rank FROM user WHERE role = 'recycler' AND lifetime_points > (SELECT lifetime_points FROM user WHERE user_id = ?)");
mysqli_stmt_bind_param($rank_stmt, "i", $user_id);
mysqli_stmt_execute($rank_stmt);
$rank_result = mysqli_stmt_get_result($rank_stmt);
$user_rank = mysqli_fetch_assoc($rank_result)['user_rank'];

// Get user's total items recycled and calculate CO₂ reduction
$co2_stmt = mysqli_prepare($conn, "SELECT SUM(sm.quantity) as total_items FROM submission_material sm JOIN recycling_submission rs ON sm.submission_id = rs.submission_id WHERE rs.user_id = ? AND rs.status = 'approved'");
mysqli_stmt_bind_param($co2_stmt, "i", $user_id);
mysqli_stmt_execute($co2_stmt);
$co2_result = mysqli_stmt_get_result($co2_stmt);
$user_total_items = mysqli_fetch_assoc($co2_result)['total_items'];
$user_total_items = $user_total_items ? $user_total_items : 0;
// CO₂ calculation: 0.15kg CO₂ saved per item recycled (same formula as admin analytics)
$user_co2_saved = round($user_total_items * 0.15, 2);

$page_title = "Dashboard";
include 'includes/header.php';
?>

<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: var(--space-6);
        margin-bottom: var(--space-8);
    }

    .dashboard-card {
        background: var(--color-white);
        border-radius: var(--radius-lg);
        padding: var(--space-6);
        box-shadow: var(--shadow-md);
    }

    .qr-section {
        text-align: center;
        grid-column: 1 / -1;
    }

    .qr-card {
        max-width: 500px;
        margin: 0 auto;
        background: var(--gradient-primary);
        color: white;
        padding: var(--space-8);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-lg);
    }

    .qr-title {
        font-size: var(--text-2xl);
        font-weight: 700;
        margin-bottom: var(--space-2);
    }

    .qr-subtitle {
        font-size: var(--text-sm);
        opacity: 0.9;
        margin-bottom: var(--space-6);
    }

    .qr-code-container {
        background: white;
        padding: var(--space-6);
        border-radius: var(--radius-lg);
        margin-bottom: var(--space-6);
    }

    .qr-code-image {
        width: 100%;
        max-width: 300px;
        height: auto;
        border-radius: var(--radius-md);
    }

    .qr-username {
        font-size: var(--text-lg);
        font-weight: 600;
        margin-top: var(--space-4);
        color: var(--color-gray-800);
    }

    .qr-actions {
        display: flex;
        gap: var(--space-3);
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-qr {
        padding: var(--space-3) var(--space-6);
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: var(--text-sm);
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: var(--space-2);
        text-decoration: none;
    }

    .btn-download {
        background: var(--color-white);
        color: var(--color-primary);
    }

    .btn-download:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .btn-print {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 2px solid white;
    }

    .btn-print:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .stats-card {
        text-align: center;
    }

    .stat-value {
        font-size: var(--text-4xl);
        font-weight: 700;
        color: var(--color-primary);
        margin-bottom: var(--space-2);
    }

    .stat-label {
        font-size: var(--text-sm);
        color: var(--color-gray-600);
        font-weight: 600;
    }

    .info-box {
        background: var(--color-info-light);
        border-left: 4px solid var(--color-info);
        padding: var(--space-4);
        border-radius: var(--radius-md);
        margin-top: var(--space-6);
    }

    .info-box p {
        margin: 0;
        color: var(--color-gray-700);
        font-size: var(--text-sm);
    }

    @media print {

        .recycler-header,
        .recycler-nav,
        .qr-actions,
        .info-box,
        .dashboard-card:not(.qr-section) {
            display: none !important;
        }

        .qr-section {
            margin: 0;
            padding: var(--space-4);
        }
    }
</style>

<div class="page-header">
    <h2 class="page-title">Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
    <p class="page-description">Your personal recycling dashboard</p>
</div>

<!-- QR Code Section -->
<div class="dashboard-grid">
    <div class="qr-section">
        <div class="qr-card">
            <h3 class="qr-title">Your Recycler QR Code</h3>
            <p class="qr-subtitle">Scan this code at the bin to track your recycling contributions</p>

            <div class="qr-code-container">
                <img src="<?php echo htmlspecialchars($qr_image_url); ?>" alt="Recycler QR Code" class="qr-code-image"
                    id="qrCodeImage">
                <p class="qr-username"><?php echo htmlspecialchars($username); ?></p>
            </div>


        </div>

        <div class="info-box">
            <p><i class="fas fa-info-circle"></i> <strong>How to use:</strong> After the bin camera scans your waste,
                show this QR code to the camera to link the recycling to your account and earn points!</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="dashboard-card stats-card">
        <div class="stat-value"><?php echo number_format($user['lifetime_points']); ?></div>
        <div class="stat-label">Total Points</div>
    </div>

    <div class="dashboard-card stats-card">
        <div class="stat-value"><?php echo number_format($user_co2_saved, 2); ?><span style="font-size: 0.6em">kg</span>
        </div>
        <div class="stat-label">CO₂ Reduced</div>
    </div>

    <div class="dashboard-card stats-card">
        <div class="stat-value"><?php echo $recent_count; ?></div>
        <div class="stat-label">Submissions This Week</div>
    </div>

    <div class="dashboard-card stats-card">
        <div class="stat-value">#<?php echo $user_rank; ?></div>
        <div class="stat-label">Current Rank</div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>