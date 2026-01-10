<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'eco-moderator') {
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
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>APRecycle Eco-Moderator</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: var(--color-gray-50);
            margin: 0;
            padding: 0;
        }

        .moderator-header {
            background: var(--color-white);
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .moderator-header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--space-4) var(--space-8);
            border-bottom: 1px solid var(--color-gray-200);
        }

        .moderator-logo {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            text-decoration: none;
        }

        .moderator-logo-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient-primary);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: var(--text-xl);
        }

        .moderator-logo-text h1 {
            font-size: var(--text-xl);
            color: var(--color-primary);
            margin: 0;
        }

        .moderator-logo-text p {
            font-size: var(--text-xs);
            color: var(--color-gray-600);
            margin: 0;
        }

        .moderator-user-info {
            display: flex;
            align-items: center;
            gap: var(--space-4);
        }

        .moderator-user-profile {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-2) var(--space-4);
            background: var(--color-gray-100);
            border-radius: var(--radius-md);
        }

        .moderator-user-avatar {
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

        .moderator-user-details {
            display: flex;
            flex-direction: column;
        }

        .moderator-user-name {
            font-weight: 600;
            font-size: var(--text-sm);
            color: var(--color-gray-800);
            line-height: 1.2;
        }

        .moderator-user-role {
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

        .moderator-nav {
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

        .moderator-nav-tabs {
            display: flex;
            gap: var(--space-1);
            padding: 0 var(--space-8);
            list-style: none;
            margin: 0;
            transition: all 0.3s ease;
        }

        .moderator-nav-tab {
            position: relative;
        }

        .moderator-nav-link {
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

        .moderator-nav-link:hover {
            color: var(--color-primary);
            background: var(--color-gray-50);
        }

        .moderator-nav-link.active {
            color: var(--color-primary);
            border-bottom-color: var(--color-primary);
            font-weight: 600;
        }

        .moderator-nav-icon {
            font-size: var(--text-base);
        }

        .moderator-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: var(--space-8);
        }

        @media (max-width: 768px) {
            .moderator-header-top {
                padding: var(--space-3) var(--space-4);
            }

            .moderator-logo-text h1 {
                font-size: var(--text-lg);
            }

            .moderator-logo-text p {
                display: none;
            }

            .moderator-user-details {
                display: none;
            }

            .mobile-nav-header {
                display: flex;
                border-bottom: 1px solid var(--color-gray-200);
            }

            .moderator-nav-tabs {
                display: none;
                flex-direction: column;
                padding: 0;
                gap: 0;
                background: var(--color-white);
                border-bottom: 1px solid var(--color-gray-200);
            }

            .moderator-nav-tabs.show {
                display: flex;
                animation: slideDown 0.3s ease-out;
            }

            @keyframes slideDown {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .moderator-nav-link {
                padding: var(--space-4);
                width: 100%;
                border-bottom: 1px solid var(--color-gray-50);
                border-left: 4px solid transparent;
            }

            .moderator-nav-link.active {
                border-bottom-color: var(--color-gray-50);
                border-left-color: var(--color-primary);
                background: var(--color-gray-50);
            }

            .moderator-content {
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
    <header class="moderator-header">
        <div class="moderator-header-top">
            <a href="dashboard.php" class="moderator-logo">
                <div class="moderator-logo-icon">
                    <i class="fas fa-recycle"></i>
                </div>
                <div class="moderator-logo-text">
                    <h1>APRecycle</h1>
                    <p>Eco-Moderator Panel</p>
                </div>
            </a>

            <div class="moderator-user-info">
                <div class="moderator-user-profile">
                    <div class="moderator-user-avatar">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                    <div class="moderator-user-details">
                        <span class="moderator-user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <span class="moderator-user-role">Eco-Moderator</span>
                    </div>
                </div>
                <a href="../logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <nav class="moderator-nav">
            <div class="mobile-nav-header" id="mobileMenuBtn">
                <div class="mobile-nav-label">
                    <i class="fas fa-bars"></i>
                    <span>Menu</span>
                </div>
                <i class="fas fa-chevron-down" id="menuArrow"></i>
            </div>

            <ul class="moderator-nav-tabs" id="navLinks">
                <li class="moderator-nav-tab">
                    <a href="dashboard.php"
                        class="moderator-nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-home moderator-nav-icon"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="moderator-nav-tab">
                    <a href="review-queue.php"
                        class="moderator-nav-link <?php echo $current_page == 'review-queue.php' ? 'active' : ''; ?>">
                        <i class="fas fa-check-double moderator-nav-icon"></i>
                        <span>Review Queue</span>
                    </a>
                </li>
                <li class="moderator-nav-tab">
                    <a href="educational_content.php"
                        class="moderator-nav-link <?php echo ($current_page == 'educational_content.php' || $current_page == 'content-creation.php' || $current_page == 'content-edit.php') ? 'active' : ''; ?>">
                        <i class="fas fa-book moderator-nav-icon"></i>
                        <span>Educational Content</span>
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

    <main class="moderator-content">