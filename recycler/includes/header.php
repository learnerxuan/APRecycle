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

        /* Recycler Header */
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
            width: 40px; /* Adjusted to fit logo better */
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

        /* --- NEW: Profile Link Styling --- */
        .profile-link {
            text-decoration: none; /* Removes the underline */
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
            border: 1px solid transparent; /* Placeholder for border transition */
            transition: all 0.2s ease;
        }

        /* Interactive States */
        .profile-link:hover .recycler-user-profile {
            background: var(--color-white);
            border-color: var(--color-gray-200);
            box-shadow: var(--shadow-sm);
            transform: translateY(-2px); /* Slight lift effect */
        }

        .profile-link:active .recycler-user-profile {
            transform: translateY(0);
            background: var(--color-gray-100);
            box-shadow: none;
        }
        /* -------------------------------- */

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

        /* Navigation Tabs */
        .recycler-nav {
            background: var(--color-white);
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .recycler-nav-tabs {
            display: flex;
            gap: var(--space-1);
            padding: 0 var(--space-8);
            list-style: none;
            margin: 0;
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

        /* Main Content */
        .recycler-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: var(--space-8);
        }

        /* Mobile Responsive */
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

            .recycler-nav-tabs {
                padding: 0 var(--space-4);
            }

            .recycler-nav-link {
                padding: var(--space-3) var(--space-4);
                font-size: var(--text-xs);
            }

            .recycler-content {
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
    <header class="recycler-header">
        <div class="recycler-header-top">
            <a href="dashboard.php" class="recycler-logo">
                <div class="recycler-logo-icon">
                    <img src="../assets/aprecycle-logo.png" alt="Logo">
                </div>
                <div class="recycler-logo-text">
                    <h1>APRecycle</h1>
                    <p>Smart Recycling  for APU</p>
                </div>
            </a>

            <div class="recycler-user-info">
                <a href="profile.php" class="profile-link">
                    <div class="recycler-user-profile">
                        <div class="recycler-user-avatar">
                            <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                        </div>
                        <div class="recycler-user-details">
                            <span class="recycler-user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
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
            <ul class="recycler-nav-tabs">
                <li class="recycler-nav-tab">
                    <a href="dashboard.php" class="recycler-nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-home recycler-nav-icon"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="recycler-nav-tab">
                    <a href="submit.php" class="recycler-nav-link <?php echo $current_page == 'submit.php' ? 'active' : ''; ?>">
                        <i class="fas fa-upload recycler-nav-icon"></i>
                        <span>Submit Item</span>
                    </a>
                </li>
                <li class="recycler-nav-tab">
                    <a href="challenges.php" class="recycler-nav-link <?php echo $current_page == 'challenges.php' ? 'active' : ''; ?>">
                        <i class="fas fa-trophy recycler-nav-icon"></i>
                        <span>Challenges</span>
                    </a>
                </li>
                <li class="recycler-nav-tab">
                    <a href="teams.php" class="recycler-nav-link <?php echo $current_page == 'teams.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users recycler-nav-icon"></i>
                        <span>Teams</span>
                    </a>
                </li>
                <li class="recycler-nav-tab">
                    <a href="leaderboard.php" class="recycler-nav-link <?php echo $current_page == 'leaderboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line recycler-nav-icon"></i>
                        <span>Leaderboard</span>
                    </a>
                </li>
                <li class="recycler-nav-tab">
                    <a href="inbox.php" class="recycler-nav-link <?php echo $current_page == 'inbox.php' ? 'active' : ''; ?>">
                        <i class="fas fa-inbox recycler-nav-icon"></i>
                        <span>Inbox</span>
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <main class="recycler-content">