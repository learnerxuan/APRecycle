<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'recycler') {
    header('Location: ../login.php');
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>APRecycle</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: var(--color-gray-50);
            margin: 0;
            padding: 0;
        }

        .recycler-header {
            background: var(--color-white);
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .recycler-header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--space-4) var(--space-8);
            border-bottom: 1px solid var(--color-gray-200);
        }

        .recycler-logo {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            text-decoration: none;
        }

        .recycler-logo-icon {
            width: 40px;
            height: 40px;
            background: transparent;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .recycler-logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .recycler-logo-text h1 {
            font-size: var(--text-xl);
            color: var(--color-primary);
            margin: 0;
        }

        .recycler-logo-text p {
            font-size: var(--text-xs);
            color: var(--color-gray-600);
            margin: 0;
        }

        .recycler-user-info {
            display: flex;
            align-items: center;
            gap: var(--space-4);
        }

        .profile-link {
            text-decoration: none;
            color: inherit;
            display: block;
            border-radius: var(--radius-md);
            transition: all 0.2s ease;
        }

        .recycler-user-profile {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-2) var(--space-4);
            background: var(--color-gray-100);
            border-radius: var(--radius-md);
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .profile-link:hover .recycler-user-profile {
            background: var(--color-white);
            border-color: var(--color-gray-200);
            box-shadow: var(--shadow-sm);
            transform: translateY(-2px);
        }

        .profile-link:active .recycler-user-profile {
            transform: translateY(0);
            background: var(--color-gray-100);
            box-shadow: none;
        }

        .recycler-user-avatar {
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

        .recycler-user-details {
            display: flex;
            flex-direction: column;
        }

        .recycler-user-name {
            font-weight: 600;
            font-size: var(--text-sm);
            color: var(--color-gray-800);
            line-height: 1.2;
        }

        .recycler-user-role {
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

        .recycler-nav {
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

        .recycler-nav-tabs {
            display: flex;
            gap: var(--space-1);
            padding: 0 var(--space-8);
            list-style: none;
            margin: 0;
            transition: all 0.3s ease;
        }

        .recycler-nav-tab {
            position: relative;
        }

        .recycler-nav-link {
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

        .recycler-nav-link:hover {
            color: var(--color-primary);
            background: var(--color-gray-50);
        }

        .recycler-nav-link.active {
            color: var(--color-primary);
            border-bottom-color: var(--color-primary);
            font-weight: 600;
        }

        .recycler-nav-icon {
            font-size: var(--text-base);
        }

        .recycler-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: var(--space-8);
        }

        @media (max-width: 768px) {
            .recycler-header-top {
                padding: var(--space-3) var(--space-4);
            }

            .recycler-logo-text h1 {
                font-size: var(--text-lg);
            }

            .recycler-logo-text p {
                display: none;
            }

            .recycler-user-details {
                display: none;
            }

            .mobile-nav-header {
                display: flex;
                border-bottom: 1px solid var(--color-gray-200);
            }

            .recycler-nav-tabs {
                display: none;
                flex-direction: column;
                padding: 0;
                gap: 0;
                background: var(--color-white);
                border-bottom: 1px solid var(--color-gray-200);
            }

            .recycler-nav-tabs.show {
                display: flex;
                animation: slideDown 0.3s ease-out;
            }

            @keyframes slideDown {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .recycler-nav-link {
                padding: var(--space-4);
                width: 100%;
                border-bottom: 1px solid var(--color-gray-50);
                border-left: 4px solid transparent;
            }

            .recycler-nav-link.active {
                border-bottom-color: var(--color-gray-50);
                border-left-color: var(--color-primary);
                background: var(--color-gray-50);
            }

            .recycler-content {
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
    <header class="recycler-header">
        <div class="recycler-header-top">
            <a href="dashboard.php" class="recycler-logo">
                <div class="recycler-logo-icon">
                    <img src="../assets/aprecycle-logo.png" alt="Logo">
                </div>
                <div class="recycler-logo-text">
                    <h1>APRecycle</h1>
                    <p>Smart Recycling for APU</p>
                </div>
            </a>

            <div class="recycler-user-info">
                <a href="profile.php" class="profile-link">
                    <div class="recycler-user-profile">
                        <div class="recycler-user-avatar">
                            <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                        </div>
                        <div class="recycler-user-details">
                            <span
                                class="recycler-user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            <span class="recycler-user-role">Recycler</span>
                        </div>
                    </div>
                </a>
                <a href="../logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <nav class="recycler-nav">
            <div class="mobile-nav-header" id="mobileMenuBtn">
                <div class="mobile-nav-label">
                    <i class="fas fa-bars"></i>
                    <span>Menu</span>
                </div>
                <i class="fas fa-chevron-down" id="menuArrow"></i>
            </div>

            <ul class="recycler-nav-tabs" id="navLinks">
                <li class="recycler-nav-tab">
                    <a href="dashboard.php"
                        class="recycler-nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-home recycler-nav-icon"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="recycler-nav-tab">
                    <a href="challenges.php"
                        class="recycler-nav-link <?php echo $current_page == 'challenges.php' ? 'active' : ''; ?>">
                        <i class="fas fa-trophy recycler-nav-icon"></i>
                        <span>Challenges</span>
                    </a>
                </li>
                <li class="recycler-nav-tab">
                    <a href="teams.php"
                        class="recycler-nav-link <?php echo $current_page == 'teams.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users recycler-nav-icon"></i>
                        <span>Teams</span>
                    </a>
                </li>
                <li class="recycler-nav-tab">
                    <a href="leaderboard.php"
                        class="recycler-nav-link <?php echo $current_page == 'leaderboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line recycler-nav-icon"></i>
                        <span>Leaderboard</span>
                    </a>
                </li>
                <li class="recycler-nav-tab">
                    <a href="educational_content.php"
                        class="recycler-nav-link <?php echo $current_page == 'educational_content.php' ? 'active' : ''; ?>">
                        <i class="fas fa-book recycler-nav-icon"></i>
                        <span>Educational Content</span>
                    </a>
                </li>
                <li class="recycler-nav-tab">
                    <a href="inbox.php"
                        class="recycler-nav-link <?php echo $current_page == 'inbox.php' ? 'active' : ''; ?>">
                        <i class="fas fa-inbox recycler-nav-icon"></i>
                        <span>Inbox</span>
                    </a>
                </li>
                <li class="recycler-nav-tab">
                    <a href="achievements.php"
                        class="recycler-nav-link <?php echo $current_page == 'achievements.php' ? 'active' : ''; ?>">
                        <i class="fas fa-medal recycler-nav-icon"></i>
                        <span>Achievements</span>
                    </a>
                </li>
                <li class="recycler-nav-tab">
                    <a href="profile.php"
                        class="recycler-nav-link <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user-circle recycler-nav-icon"></i>
                        <span>Profile</span>
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
                
                if (navLinks.classList.contains('show')) {
                    arrow.style.transform = 'rotate(180deg)';
                } else {
                    arrow.style.transform = 'rotate(0deg)';
                }
            });
        });
    </script>

    <main class="recycler-content">