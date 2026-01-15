<?php
//check who the current user is
session_start();
$page_title = "Leaderboard Hub";
include('includes/header.php');
?>

<div class="page-header" style="text-align: center; margin-bottom: var(--space-10);">
    <h1 class="page-title" style="font-size: var(--text-4xl); margin-bottom: var(--space-2);">Leaderboard Hub</h1>
    <p class="page-description" style="max-width: 600px; margin: 0 auto;">
        Track your progress, celebrate your team's success, and stay updated on the latest recycling challenges.
    </p>
</div>

<div
    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: var(--space-8); margin-bottom: var(--space-12);">

    <div class="card"
        style="display: flex; flex-direction: column; transition: transform 0.3s ease; border-top: 5px solid var(--color-primary);">
        <div style="padding: var(--space-6); flex-grow: 1; text-align: center;">
            <div
                style="background: var(--color-gray-50); width: 80px; height: 80px; border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4); font-size: 2.5rem;">
                🥇
            </div>
            <h2 style="font-size: var(--text-xl); margin-bottom: var(--space-3); color: var(--color-gray-800);">
                Individual Rankings</h2>
            <p style="color: var(--color-gray-600); font-size: var(--text-sm); line-height: 1.6;">
                Compete with fellow students to become the top recycler at APU. View monthly and lifetime high scores.
            </p>
        </div>
        <div style="padding: var(--space-6); border-top: 1px solid var(--color-gray-100);">
            <a href="leaderboard_individual.php" class="btn btn-primary"
                style="width: 100%; display: flex; align-items: center; justify-content: center; gap: var(--space-2); text-decoration: none;">
                <span>View Rankings</span>
                <i class="fas fa-chevron-right" style="font-size: var(--text-xs);"></i>
            </a>
        </div>
    </div>

    <div class="card"
        style="display: flex; flex-direction: column; transition: transform 0.3s ease; border-top: 5px solid var(--color-secondary);">
        <div style="padding: var(--space-6); flex-grow: 1; text-align: center;">
            <div
                style="background: var(--color-gray-50); width: 80px; height: 80px; border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4); font-size: 2.5rem;">
                👥
            </div>
            <h2 style="font-size: var(--text-xl); margin-bottom: var(--space-3); color: var(--color-gray-800);">Team
                Rankings</h2>
            <p style="color: var(--color-gray-600); font-size: var(--text-sm); line-height: 1.6;">
                Sustainability is a team effort! Check how your team stands against others in the collective points
                table.
            </p>
        </div>
        <div style="padding: var(--space-6); border-top: 1px solid var(--color-gray-100);">
            <a href="leaderboard_team.php" class="btn btn-primary"
                style="width: 100%; display: flex; align-items: center; justify-content: center; gap: var(--space-2); text-decoration: none;">
                <span>View Rankings</span>
                <i class="fas fa-chevron-right" style="font-size: var(--text-xs);"></i>
            </a>
        </div>
    </div>

    <div class="card"
        style="display: flex; flex-direction: column; transition: transform 0.3s ease; border-top: 5px solid var(--color-accent-yellow);">
        <div style="padding: var(--space-6); flex-grow: 1; text-align: center;">
            <div
                style="background: var(--color-gray-50); width: 80px; height: 80px; border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4); font-size: 2.5rem;">
                🏆
            </div>
            <h2 style="font-size: var(--text-xl); margin-bottom: var(--space-3); color: var(--color-gray-800);">
                Challenge Standings</h2>
            <p style="color: var(--color-gray-600); font-size: var(--text-sm); line-height: 1.6;">
                The race for badges! See current leaders for active monthly challenges and special recycling events.
            </p>
        </div>
        <div style="padding: var(--space-6); border-top: 1px solid var(--color-gray-100);">
            <a href="leaderboard_challenges.php" class="btn btn-primary"
                style="width: 100%; display: flex; align-items: center; justify-content: center; gap: var(--space-2); text-decoration: none;">
                <span>View Rankings</span>
                <i class="fas fa-chevron-right" style="font-size: var(--text-xs);"></i>
            </a>
        </div>
    </div>

</div>

<?php include('includes/footer.php'); ?>