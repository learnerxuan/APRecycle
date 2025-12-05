<?php
require_once '../php/config.php';

$page_title = 'Challenge Management';
$conn = getDBConnection();

// Handle delete challenge
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $challenge_id = (int)$_GET['delete'];

    // Delete related records first (user_challenge)
    $delete_user_challenges = mysqli_prepare($conn, "DELETE FROM user_challenge WHERE challenge_id = ?");
    mysqli_stmt_bind_param($delete_user_challenges, "i", $challenge_id);
    mysqli_stmt_execute($delete_user_challenges);
    mysqli_stmt_close($delete_user_challenges);

    // Delete the challenge
    $delete_stmt = mysqli_prepare($conn, "DELETE FROM challenge WHERE challenge_id = ?");
    mysqli_stmt_bind_param($delete_stmt, "i", $challenge_id);

    if (mysqli_stmt_execute($delete_stmt)) {
        $success_message = "Challenge deleted successfully!";
    } else {
        $error_message = "Error deleting challenge: " . mysqli_error($conn);
    }
    mysqli_stmt_close($delete_stmt);
}

// Fetch active challenges (currently running)
$active_query = "SELECT c.*,
                 b.badge_name,
                 r.reward_name,
                 COUNT(DISTINCT uc.user_id) as participant_count
                 FROM challenge c
                 LEFT JOIN badge b ON c.badge_id = b.badge_id
                 LEFT JOIN reward r ON c.reward_id = r.reward_id
                 LEFT JOIN user_challenge uc ON c.challenge_id = uc.challenge_id
                 WHERE c.start_date <= CURDATE() AND c.end_date >= CURDATE()
                 GROUP BY c.challenge_id
                 ORDER BY c.start_date DESC";
$active_result = mysqli_query($conn, $active_query);

// Fetch upcoming challenges (not started yet)
$upcoming_query = "SELECT c.*,
                   b.badge_name,
                   r.reward_name,
                   COUNT(DISTINCT uc.user_id) as participant_count
                   FROM challenge c
                   LEFT JOIN badge b ON c.badge_id = b.badge_id
                   LEFT JOIN reward r ON c.reward_id = r.reward_id
                   LEFT JOIN user_challenge uc ON c.challenge_id = uc.challenge_id
                   WHERE c.start_date > CURDATE()
                   GROUP BY c.challenge_id
                   ORDER BY c.start_date ASC";
$upcoming_result = mysqli_query($conn, $upcoming_query);

// Fetch past challenges for reference
$past_query = "SELECT c.*,
               b.badge_name,
               r.reward_name,
               COUNT(DISTINCT uc.user_id) as participant_count
               FROM challenge c
               LEFT JOIN badge b ON c.badge_id = b.badge_id
               LEFT JOIN reward r ON c.reward_id = r.reward_id
               LEFT JOIN user_challenge uc ON c.challenge_id = uc.challenge_id
               WHERE c.end_date < CURDATE()
               GROUP BY c.challenge_id
               ORDER BY c.end_date DESC
               LIMIT 5";
$past_result = mysqli_query($conn, $past_query);

// Include admin header
include 'includes/header.php';
?>

    <style>
        .page-header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-6);
            padding: var(--space-6);
            background: var(--color-white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }

        .page-header-actions h2 {
            color: var(--color-gray-800);
            margin: 0;
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        .btn {
            padding: var(--space-3) var(--space-6);
            border-radius: var(--radius-md);
            font-size: var(--text-base);
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--color-primary);
            color: white;
            box-shadow: var(--shadow-sm);
        }

        .btn-primary:hover {
            background: var(--color-primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-edit {
            background: var(--color-accent-blue);
            color: white;
            padding: var(--space-2) var(--space-4);
            font-size: var(--text-sm);
        }

        .btn-edit:hover {
            background: #2563EB;
            transform: translateY(-1px);
        }

        .btn-delete {
            background: var(--color-error);
            color: white;
            padding: var(--space-2) var(--space-4);
            font-size: var(--text-sm);
        }

        .btn-delete:hover {
            background: #DC2626;
            transform: translateY(-1px);
        }

        .alert {
            padding: var(--space-4) var(--space-6);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-6);
            display: flex;
            align-items: center;
            gap: var(--space-3);
        }

        .alert-success {
            background: var(--color-success-light);
            color: #065F46;
            border-left: 4px solid var(--color-success);
        }

        .alert-error {
            background: var(--color-error-light);
            color: #991B1B;
            border-left: 4px solid var(--color-error);
        }

        .section {
            background: var(--color-white);
            padding: var(--space-8);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: var(--space-8);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-6);
            padding-bottom: var(--space-4);
            border-bottom: 2px solid var(--color-gray-200);
        }

        .section-header h2 {
            color: var(--color-primary);
            display: flex;
            align-items: center;
            gap: var(--space-2);
            margin: 0;
        }

        .badge {
            display: inline-block;
            padding: var(--space-1) var(--space-3);
            border-radius: var(--radius-full);
            font-size: var(--text-sm);
            font-weight: 600;
        }

        .badge-active {
            background: var(--color-success-light);
            color: #065F46;
        }

        .badge-upcoming {
            background: var(--color-info-light);
            color: #1E40AF;
        }

        .badge-past {
            background: var(--color-gray-200);
            color: var(--color-gray-600);
        }

        .challenges-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: var(--space-6);
        }

        .challenge-card {
            border: 2px solid var(--color-gray-200);
            border-radius: var(--radius-lg);
            padding: var(--space-6);
            transition: all 0.3s ease;
            background: var(--color-white);
        }

        .challenge-card:hover {
            border-color: var(--color-secondary);
            box-shadow: var(--shadow-lg);
            transform: translateY(-4px);
        }

        .challenge-card-header {
            margin-bottom: var(--space-4);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .challenge-title {
            font-size: var(--text-xl);
            font-weight: 700;
            color: var(--color-gray-800);
            margin: 0;
        }

        .challenge-description {
            color: var(--color-gray-600);
            font-size: var(--text-base);
            line-height: 1.5;
            margin-bottom: var(--space-4);
        }

        .challenge-meta {
            display: grid;
            gap: var(--space-3);
            margin-bottom: var(--space-4);
            padding: var(--space-4);
            background: var(--color-gray-100);
            border-radius: var(--radius-md);
        }

        .challenge-meta-item {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            font-size: var(--text-sm);
        }

        .challenge-meta-item i {
            color: var(--color-primary);
            width: 16px;
        }

        .challenge-meta-item strong {
            color: var(--color-gray-700);
            min-width: 110px;
        }

        .challenge-meta-item span {
            color: var(--color-gray-800);
        }

        .challenge-actions {
            display: flex;
            gap: var(--space-3);
        }

        .empty-state {
            text-align: center;
            padding: var(--space-12) var(--space-8);
            color: var(--color-gray-600);
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: var(--space-4);
            opacity: 0.3;
        }

        .empty-state h3 {
            font-size: var(--text-xl);
            margin-bottom: var(--space-2);
            color: var(--color-gray-700);
        }

        .multiplier-badge {
            background: var(--color-warning-light);
            color: #92400E;
            padding: var(--space-1) var(--space-3);
            border-radius: var(--radius-full);
            font-weight: 700;
            font-size: var(--text-sm);
        }

        .stats-row {
            display: flex;
            gap: var(--space-4);
            margin-bottom: var(--space-6);
        }

        .stat-card {
            flex: 1;
            background: var(--gradient-primary);
            color: white;
            padding: var(--space-6);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card h3 {
            font-size: var(--text-4xl);
            margin: 0 0 var(--space-1) 0;
        }

        .stat-card p {
            opacity: 0.9;
            font-size: var(--text-base);
            margin: 0;
        }

        @media (max-width: 768px) {
            .page-header-actions {
                flex-direction: column;
                align-items: stretch;
                gap: var(--space-3);
            }

            .challenges-grid {
                grid-template-columns: 1fr;
            }

            .stats-row {
                flex-direction: column;
            }

            .challenge-actions {
                flex-direction: column;
            }

            .section {
                padding: var(--space-4);
            }
        }
    </style>

    <!-- Page Header -->
    <div class="page-header-actions">
        <h2>
            <i class="fas fa-trophy"></i> Challenge Management
        </h2>
        <a href="challenge_create.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New Challenge
        </a>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <!-- Statistics Row -->
    <div class="stats-row">
        <div class="stat-card">
            <h3><?php echo mysqli_num_rows($active_result); mysqli_data_seek($active_result, 0); ?></h3>
            <p>Active Challenges</p>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #3B82F6 0%, #60A5FA 100%);">
            <h3><?php echo mysqli_num_rows($upcoming_result); mysqli_data_seek($upcoming_result, 0); ?></h3>
            <p>Upcoming Challenges</p>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 100%);">
            <h3><?php echo mysqli_num_rows($past_result); mysqli_data_seek($past_result, 0); ?></h3>
            <p>Past Challenges</p>
        </div>
    </div>

    <!-- Active Challenges -->
    <div class="section">
        <div class="section-header">
            <h2><i class="fas fa-fire"></i> Active Challenges</h2>
            <span class="badge badge-active">Currently Running</span>
        </div>

        <?php if (mysqli_num_rows($active_result) > 0): ?>
            <div class="challenges-grid">
                <?php while ($challenge = mysqli_fetch_assoc($active_result)): ?>
                    <div class="challenge-card">
                        <div class="challenge-card-header">
                            <h3 class="challenge-title"><?php echo htmlspecialchars($challenge['title']); ?></h3>
                            <span class="badge badge-active">Active</span>
                        </div>

                        <p class="challenge-description">
                            <?php echo htmlspecialchars($challenge['description']); ?>
                        </p>

                        <div class="challenge-meta">
                            <div class="challenge-meta-item">
                                <i class="fas fa-calendar-alt"></i>
                                <strong>Start Date:</strong>
                                <span><?php echo date('M d, Y', strtotime($challenge['start_date'])); ?></span>
                            </div>
                            <div class="challenge-meta-item">
                                <i class="fas fa-calendar-check"></i>
                                <strong>End Date:</strong>
                                <span><?php echo date('M d, Y', strtotime($challenge['end_date'])); ?></span>
                            </div>
                            <div class="challenge-meta-item">
                                <i class="fas fa-bolt"></i>
                                <strong>Multiplier:</strong>
                                <span class="multiplier-badge">×<?php echo $challenge['point_multiplier']; ?></span>
                            </div>
                            <div class="challenge-meta-item">
                                <i class="fas fa-users"></i>
                                <strong>Participants:</strong>
                                <span><?php echo $challenge['participant_count']; ?> users</span>
                            </div>
                            <?php if ($challenge['badge_name']): ?>
                                <div class="challenge-meta-item">
                                    <i class="fas fa-medal"></i>
                                    <strong>Badge:</strong>
                                    <span><?php echo htmlspecialchars($challenge['badge_name']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($challenge['reward_name']): ?>
                                <div class="challenge-meta-item">
                                    <i class="fas fa-gift"></i>
                                    <strong>Reward:</strong>
                                    <span><?php echo htmlspecialchars($challenge['reward_name']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="challenge-actions">
                            <a href="challenge_edit.php?id=<?php echo $challenge['challenge_id']; ?>" class="btn btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="?delete=<?php echo $challenge['challenge_id']; ?>"
                               class="btn btn-delete"
                               onclick="return confirm('Are you sure you want to delete this challenge? This will also remove all participant data.');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-trophy"></i></div>
                <h3>No Active Challenges</h3>
                <p>There are currently no active challenges. Create one to get started!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Upcoming Challenges -->
    <div class="section">
        <div class="section-header">
            <h2><i class="fas fa-calendar-plus"></i> Upcoming Challenges</h2>
            <span class="badge badge-upcoming">Not Started</span>
        </div>

        <?php if (mysqli_num_rows($upcoming_result) > 0): ?>
            <div class="challenges-grid">
                <?php while ($challenge = mysqli_fetch_assoc($upcoming_result)): ?>
                    <div class="challenge-card">
                        <div class="challenge-card-header">
                            <h3 class="challenge-title"><?php echo htmlspecialchars($challenge['title']); ?></h3>
                            <span class="badge badge-upcoming">Upcoming</span>
                        </div>

                        <p class="challenge-description">
                            <?php echo htmlspecialchars($challenge['description']); ?>
                        </p>

                        <div class="challenge-meta">
                            <div class="challenge-meta-item">
                                <i class="fas fa-calendar-alt"></i>
                                <strong>Start Date:</strong>
                                <span><?php echo date('M d, Y', strtotime($challenge['start_date'])); ?></span>
                            </div>
                            <div class="challenge-meta-item">
                                <i class="fas fa-calendar-check"></i>
                                <strong>End Date:</strong>
                                <span><?php echo date('M d, Y', strtotime($challenge['end_date'])); ?></span>
                            </div>
                            <div class="challenge-meta-item">
                                <i class="fas fa-bolt"></i>
                                <strong>Multiplier:</strong>
                                <span class="multiplier-badge">×<?php echo $challenge['point_multiplier']; ?></span>
                            </div>
                            <div class="challenge-meta-item">
                                <i class="fas fa-users"></i>
                                <strong>Participants:</strong>
                                <span><?php echo $challenge['participant_count']; ?> users</span>
                            </div>
                            <?php if ($challenge['badge_name']): ?>
                                <div class="challenge-meta-item">
                                    <i class="fas fa-medal"></i>
                                    <strong>Badge:</strong>
                                    <span><?php echo htmlspecialchars($challenge['badge_name']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($challenge['reward_name']): ?>
                                <div class="challenge-meta-item">
                                    <i class="fas fa-gift"></i>
                                    <strong>Reward:</strong>
                                    <span><?php echo htmlspecialchars($challenge['reward_name']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="challenge-actions">
                            <a href="challenge_edit.php?id=<?php echo $challenge['challenge_id']; ?>" class="btn btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="?delete=<?php echo $challenge['challenge_id']; ?>"
                               class="btn btn-delete"
                               onclick="return confirm('Are you sure you want to delete this challenge?');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-calendar-day"></i></div>
                <h3>No Upcoming Challenges</h3>
                <p>Schedule new challenges to keep your recyclers engaged!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Past Challenges (Recent 5) -->
    <div class="section">
        <div class="section-header">
            <h2><i class="fas fa-history"></i> Recent Past Challenges</h2>
            <span class="badge badge-past">Completed</span>
        </div>

        <?php if (mysqli_num_rows($past_result) > 0): ?>
            <div class="challenges-grid">
                <?php while ($challenge = mysqli_fetch_assoc($past_result)): ?>
                    <div class="challenge-card" style="opacity: 0.8;">
                        <div class="challenge-card-header">
                            <h3 class="challenge-title"><?php echo htmlspecialchars($challenge['title']); ?></h3>
                            <span class="badge badge-past">Ended</span>
                        </div>

                        <p class="challenge-description">
                            <?php echo htmlspecialchars($challenge['description']); ?>
                        </p>

                        <div class="challenge-meta">
                            <div class="challenge-meta-item">
                                <i class="fas fa-calendar-alt"></i>
                                <strong>Start Date:</strong>
                                <span><?php echo date('M d, Y', strtotime($challenge['start_date'])); ?></span>
                            </div>
                            <div class="challenge-meta-item">
                                <i class="fas fa-calendar-check"></i>
                                <strong>End Date:</strong>
                                <span><?php echo date('M d, Y', strtotime($challenge['end_date'])); ?></span>
                            </div>
                            <div class="challenge-meta-item">
                                <i class="fas fa-users"></i>
                                <strong>Participants:</strong>
                                <span><?php echo $challenge['participant_count']; ?> users</span>
                            </div>
                        </div>

                        <div class="challenge-actions">
                            <a href="?delete=<?php echo $challenge['challenge_id']; ?>"
                               class="btn btn-delete"
                               onclick="return confirm('Are you sure you want to delete this past challenge? All historical data will be lost.');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-archive"></i></div>
                <h3>No Past Challenges</h3>
                <p>Past challenges will appear here once they are completed.</p>
            </div>
        <?php endif; ?>
    </div>

<?php
include 'includes/footer.php';
mysqli_close($conn);
?>
