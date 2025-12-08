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

        /* Moderator Header */
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

        /* Navigation Tabs */
        .moderator-nav {
            background: var(--color-white);
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .moderator-nav-tabs {
            display: flex;
            gap: var(--space-1);
            padding: 0 var(--space-8);
            list-style: none;
            margin: 0;
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

        /* Main Content */
        .moderator-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: var(--space-8);
        }

        /* Mobile Responsive */
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

            .moderator-nav-tabs {
                padding: 0 var(--space-4);
            }

            .moderator-nav-link {
                padding: var(--space-3) var(--space-4);
                font-size: var(--text-xs);
            }

            .moderator-content {
                padding: var(--space-4);
            }
        }

        /* Utility Classes */
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
        <!-- Top Bar -->
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

        <!-- Navigation Tabs -->
        <nav class="moderator-nav">
            <ul class="moderator-nav-tabs">
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
                    <a href="content-creation.php"
                        class="moderator-nav-link <?php echo $current_page == 'content-creation.php' ? 'active' : ''; ?>">
                        <i class="fas fa-pen-nib moderator-nav-icon"></i>
                        <span>Content Creation</span>
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <main class="moderator-content">