<?php
require_once '../php/config.php';

// Check role permissions (Double check, though header handles it)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrator') {
    header('Location: ../login.php');
    exit();
}

$conn = getDBConnection();

// --- 1. Fetch Statistics from Database ---

// A. Total Users (All roles or just Recyclers? Usually Recyclers + Mods)
$user_query = "SELECT COUNT(*) as count FROM user WHERE role != 'administrator'";
$user_result = mysqli_query($conn, $user_query);
$total_users = mysqli_fetch_assoc($user_result)['count'];

// B. Active Challenges (End date is in the future or today)
$challenge_query = "SELECT COUNT(*) as count FROM challenge WHERE end_date >= CURDATE()";
$challenge_result = mysqli_query($conn, $challenge_query);
$active_challenges = mysqli_fetch_assoc($challenge_result)['count'];

// C. Items Recycled (Sum of all quantities in approved submissions)
$items_query = "SELECT SUM(sm.quantity) as count 
                FROM submission_material sm
                JOIN recycling_submission rs ON sm.submission_id = rs.submission_id
                WHERE rs.status = 'Approved'";
$items_result = mysqli_query($conn, $items_query);
$row = mysqli_fetch_assoc($items_result);
$items_recycled = $row['count'] ? $row['count'] : 0; // Handle NULL if empty

// D. Eco-Moderators
$mod_query = "SELECT COUNT(*) as count FROM user WHERE role = 'eco-moderator'";
$mod_result = mysqli_query($conn, $mod_query);
$total_mods = mysqli_fetch_assoc($mod_result)['count'];

$page_title = "Dashboard";
include 'includes/header.php';
?>

<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: var(--space-6);
        margin-bottom: var(--space-8);
    }

    .stat-card {
        background: var(--color-white);
        padding: var(--space-6);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        text-align: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid var(--color-gray-200);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
        border-color: var(--color-primary-light);
    }

    .stat-value {
        font-size: var(--text-4xl);
        font-weight: 700;
        color: var(--color-gray-800);
        margin-bottom: var(--space-2);
        line-height: 1;
    }

    .stat-label {
        font-size: var(--text-sm);
        color: var(--color-gray-600);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: var(--space-6);
    }

    .action-card {
        background: var(--color-white);
        padding: var(--space-8);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        text-align: center;
        text-decoration: none;
        color: inherit;
        transition: all 0.3s ease;
        border: 1px solid var(--color-gray-200);
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100%;
    }

    .action-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
        border-color: var(--color-primary);
    }

    .action-icon-wrapper {
        width: 80px;
        height: 80px;
        background: var(--color-gray-100);
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: var(--space-6);
        transition: background-color 0.3s ease;
    }

    .action-card:hover .action-icon-wrapper {
        background: var(--color-primary-light);
    }

    .action-icon {
        font-size: 2.5rem; /* 40px */
        color: var(--color-gray-500);
        transition: color 0.3s ease;
    }

    .action-card:hover .action-icon {
        color: var(--color-white);
    }

    .action-title {
        font-size: var(--text-xl);
        font-weight: 700;
        color: var(--color-gray-800);
        margin-bottom: var(--space-3);
    }

    .action-desc {
        font-size: var(--text-base);
        color: var(--color-gray-600);
        margin-bottom: var(--space-6);
        flex-grow: 1; /* Pushes the link to bottom */
        line-height: 1.5;
    }

    .action-link {
        font-weight: 700;
        color: var(--color-primary);
        font-size: var(--text-sm);
        display: inline-flex;
        align-items: center;
        gap: var(--space-2);
    }

    .action-card:hover .action-link {
        color: var(--color-primary-dark);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .stat-value {
            font-size: var(--text-3xl);
        }
        
        .action-card {
            padding: var(--space-6);
        }
    }
</style>

<div class="page-header">
    <h2 class="page-title">
        <i class="fas fa-tachometer-alt" style="margin-right: 10px;"></i>
        Administrator Dashboard
    </h2>
    <p class="page-description">System overview and management tools</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?php echo number_format($total_users); ?></div>
        <div class="stat-label">Total Users</div>
    </div>

    <div class="stat-card">
        <div class="stat-value"><?php echo number_format($active_challenges); ?></div>
        <div class="stat-label">Active Challenges</div>
    </div>

    <div class="stat-card">
        <div class="stat-value"><?php echo number_format($items_recycled); ?></div>
        <div class="stat-label">Items Recycled</div>
    </div>

    <div class="stat-card">
        <div class="stat-value"><?php echo number_format($total_mods); ?></div>
        <div class="stat-label">Eco-Moderators</div>
    </div>
</div>

<div class="actions-grid">
    
    <a href="challenges.php" class="action-card">
        <div class="action-icon-wrapper">
            <i class="fas fa-trophy action-icon"></i>
        </div>
        <h3 class="action-title">Challenges Management</h3>
        <p class="action-desc">Create, edit, and manage recycling challenges and competitions.</p>
        <span class="action-link">
            Manage Challenges <i class="fas fa-arrow-right"></i>
        </span>
    </a>

    <a href="analytics.php" class="action-card">
        <div class="action-icon-wrapper">
            <i class="fas fa-chart-pie action-icon"></i>
        </div>
        <h3 class="action-title">System Analytics</h3>
        <p class="action-desc">View campus-wide statistics, trends, and generate reports.</p>
        <span class="action-link">
            View Analytics <i class="fas fa-arrow-right"></i>
        </span>
    </a>

    <a href="moderators.php" class="action-card">
        <div class="action-icon-wrapper">
            <i class="fas fa-user-shield action-icon"></i>
        </div>
        <h3 class="action-title">Eco-Moderator Management</h3>
        <p class="action-desc">Add, update, or remove eco-moderators from the system.</p>
        <span class="action-link">
            Manage Moderators <i class="fas fa-arrow-right"></i>
        </span>
    </a>

</div>

<?php 
include 'includes/footer.php'; 
mysqli_close($conn);
?>