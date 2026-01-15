<?php
// Security Check: Ensure user is logged in and is an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrator') {
    header('Location: ../login.php');
    exit();
}

// use to highlight the current tabe in navigation bar
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>APRecycle Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
    <!-- for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: var(--color-gray-50);
            margin: 0;
            padding: 0;
        }

        /* Admin Header */
        .admin-header {
            background: var(--color-white);
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .admin-header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--space-4) var(--space-8);
            border-bottom: 1px solid var(--color-gray-200);
        }

        .admin-logo {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            text-decoration: none;
        }

        .admin-logo-icon {
            width: 50px;
            height: 50px;
            border-radius: var(--radius-md);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
        }

        .admin-logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .admin-logo-text h1 {
            font-size: var(--text-xl);
            color: var(--color-primary);
            margin: 0;
        }

        .admin-logo-text p {
            font-size: var(--text-xs);
            color: var(--color-gray-600);
            margin: 0;
        }

        .admin-user-info {
            display: flex;
            align-items: center;
            gap: var(--space-4);
        }

        .admin-user-profile {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-2) var(--space-4);
            background: var(--color-gray-100);
            border-radius: var(--radius-md);
        }

        .admin-user-avatar {
            width: 32px;
            height: 32px;
            background: var(--gradient-primary);
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: var(--text-sm);
        }

        .admin-user-details {
            display: flex;
            flex-direction: column;
        }

        .admin-user-name {
            font-weight: 600;
            font-size: var(--text-sm);
            color: var(--color-gray-800);
            line-height: 1.2;
        }

        .admin-user-role {
            font-size: var(--text-xs);
            color: var(--color-gray-600);
        }

        .btn-logout {
            padding: var(--space-2) var(--space-4);
            background: var(--color-error);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: var(--text-sm);
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
        }

        .btn-logout:hover {
            background: #DC2626;
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        /* Navigation Tabs */
        .admin-nav {
            background: var(--color-white);
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            position: relative; 
        }

        .mobile-nav-header {
            display: none;
            justify-content: space-between;
            align-items: center;
            padding: var(--space-3) var(--space-4);
            cursor: pointer;
            background: var(--color-white);
        }
        
        .mobile-nav-label {
            font-weight: 600;
            color: var(--color-gray-800);
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        .admin-nav-tabs {
            display: flex;
            gap: var(--space-1);
            padding: 0 var(--space-8);
            list-style: none;
            margin: 0;
            transition: all 0.3s ease;
        }

        .admin-nav-tab {
            position: relative;
        }

        .admin-nav-link {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-4) var(--space-5);
            color: var(--color-gray-600);
            text-decoration: none;
            font-weight: 500;
            font-size: var(--text-sm);
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .admin-nav-link:hover {
            color: var(--color-primary);
            background: var(--color-gray-50);
        }

        .admin-nav-link.active {
            color: var(--color-primary);
            border-bottom-color: var(--color-primary);
            font-weight: 600;
        }

        .admin-nav-icon {
            font-size: var(--text-base);
        }

        /* Main Content */
        .admin-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: var(--space-8);
        }

        @media (max-width: 768px) {
            .admin-header-top {
                padding: var(--space-3) var(--space-4);
            }

            .admin-logo-text h1 {
                font-size: var(--text-lg);
            }

            .admin-logo-text p {
                display: none;
            }

            .admin-user-details {
                display: none;
            }

            .mobile-nav-header {
                display: flex;
                border-bottom: 1px solid var(--color-gray-200);
            }

            .admin-nav-tabs {
                display: none;
                flex-direction: column;
                padding: 0;
                gap: 0;
                background: var(--color-white);
                border-bottom: 1px solid var(--color-gray-200);
            }

            .admin-nav-tabs.show {
                display: flex;
                animation: slideDown 0.3s ease-out;
            }

            @keyframes slideDown {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .admin-nav-link {
                padding: var(--space-4);
                width: 100%;
                border-bottom: 1px solid var(--color-gray-50);
                border-left: 4px solid transparent;
            }

            .admin-nav-link.active {
                border-bottom-color: var(--color-gray-50);
                border-left-color: var(--color-primary);
                background: var(--color-gray-50);
            }

            .admin-content {
                padding: var(--space-4);
            }
        }

        .page-header {
            margin-bottom: var(--space-6);
        }

        .page-title {
            font-size: var(--text-3xl);
            color: var(--color-gray-800);
            margin-bottom: var(--space-2);
        }

        .page-description {
            color: var(--color-gray-600);
            font-size: var(--text-base);
        }
    </style>
</head>

<body>
    <header class="admin-header">
        <div class="admin-header-top">
            <a href="dashboard.php" class="admin-logo">
                <div class="admin-logo-icon">
                    <img src="../assets/aprecycle-logo.png" alt="APRecycle Logo">
                </div>
                <div class="admin-logo-text">
                    <h1>APRecycle Admin</h1>
                    <p>Smart Recycling System for APU</p>
                </div>
            </a>

            <div class="admin-user-info">
                <div class="admin-user-profile">
                    <div class="admin-user-avatar">
                        <!-- display the first letter of username in the avatar-->
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                    <div class="admin-user-details">
                        <span class="admin-user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <span class="admin-user-role">Administrator</span>
                    </div>
                </div>
                <a href="../logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <nav class="admin-nav">
            <div class="mobile-nav-header" id="mobileMenuBtn">
                <div class="mobile-nav-label">
                    <i class="fas fa-bars"></i>
                    <span>Menu</span>
                </div>
                <i class="fas fa-chevron-down" id="menuArrow"></i>
            </div>

            <ul class="admin-nav-tabs" id="navLinks">
                <li class="admin-nav-tab">
                    <a href="dashboard.php"
                        class="admin-nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-home admin-nav-icon"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="admin-nav-tab">
                    <a href="challenges.php"
                        class="admin-nav-link <?php echo $current_page == 'challenges.php' ? 'active' : ''; ?>">
                        <i class="fas fa-trophy admin-nav-icon"></i>
                        <span>Challenges</span>
                    </a>
                </li>

                <li class="admin-nav-tab">
                    <a href="badges.php"
                        class="admin-nav-link <?php echo $current_page == 'badges.php' ? 'active' : ''; ?>">
                        <i class="fas fa-medal admin-nav-icon"></i>
                        <span>Badges</span>
                    </a>
                </li>

                <li class="admin-nav-tab">
                    <a href="rewards.php"
                        class="admin-nav-link <?php echo $current_page == 'rewards.php' ? 'active' : ''; ?>">
                        <i class="fas fa-gift admin-nav-icon"></i>
                        <span>Rewards</span>
                    </a>
                </li>

                <li class="admin-nav-tab">
                    <a href="materials.php"
                        class="admin-nav-link <?php echo $current_page == 'materials.php' ? 'active' : ''; ?>">
                        <i class="fas fa-recycle admin-nav-icon"></i>
                        <span>Materials</span>
                    </a>
                </li>

                <?php
                // Check if current page is Overview or any sub-page of Leaderboard
                $is_leaderboard = in_array($current_page, [
                    'leaderboard_overview.php',
                    'leaderboard_individual.php',
                    'leaderboard_team.php',
                    'leaderboard_challenges.php'
                ]);
                ?>
                <li class="admin-nav-tab">
                    <a href="leaderboard_overview.php"
                        class="admin-nav-link <?php echo $is_leaderboard ? 'active' : ''; ?>">
                        <i class="fas fa-ranking-star admin-nav-icon"></i>
                        <span>Leaderboard</span>
                    </a>
                </li>

                <li class="admin-nav-tab">
                    <a href="moderators.php"
                        class="admin-nav-link <?php echo $current_page == 'moderators.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user-shield admin-nav-icon"></i>
                        <span>Moderators</span>
                    </a>
                </li>

                <li class="admin-nav-tab">
                    <a href="analytics.php"
                        class="admin-nav-link <?php echo $current_page == 'analytics.php' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line admin-nav-icon"></i>
                        <span>Analytics</span>
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuBtn = document.getElementById('mobileMenuBtn');
            const navLinks = document.getElementById('navLinks');
            const arrow = document.getElementById('menuArrow');

            menuBtn.addEventListener('click', function() {
                navLinks.classList.toggle('show');
                
                // Rotate arrow animation
                if (navLinks.classList.contains('show')) {
                    arrow.style.transform = 'rotate(180deg)';
                } else {
                    arrow.style.transform = 'rotate(0deg)';
                }
            });
        });
    </script>

    <main class="admin-content">