<?php
require_once '../php/config.php';

// Check role permissions
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrator') {
    header('Location: ../login.php');
    exit();
}

$conn = getDBConnection();

// 1. KEY METRICS

// A. Total Items Recycled (Approved only)
$total_items_query = "SELECT SUM(sm.quantity) as count 
                      FROM submission_material sm
                      JOIN recycling_submission rs ON sm.submission_id = rs.submission_id
                      WHERE rs.status = 'Approved'";
$total_items_result = mysqli_query($conn, $total_items_query);
$total_items = mysqli_fetch_assoc($total_items_result)['count'];
$total_items = $total_items ? $total_items : 0;

// B. Active Users (Users who made a submission in the last 30 days)
$active_users_query = "SELECT COUNT(DISTINCT user_id) as count 
                       FROM recycling_submission 
                       WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$active_users_result = mysqli_query($conn, $active_users_query);
$active_users = mysqli_fetch_assoc($active_users_result)['count'];

// C. Total Users (for Participation Rate)
$total_users_query = "SELECT COUNT(*) as count FROM user WHERE role = 'recycler'";
$total_users_result = mysqli_query($conn, $total_users_query);
$total_recyclers = mysqli_fetch_assoc($total_users_result)['count'];

// Participation Rate calculation
$participation_rate = $total_recyclers > 0 ? round(($active_users / $total_recyclers) * 100, 1) : 0;

// D. Environmental Impact (CO2)
// Estimation: Average 0.15kg CO2 saved per item recycled
$co2_saved = round($total_items * 0.15, 2);


// 2. CHART DATA: Material Breakdown 
$material_query = "SELECT m.material_name, SUM(sm.quantity) as count
                   FROM submission_material sm
                   JOIN material m ON sm.material_id = m.material_id
                   JOIN recycling_submission rs ON sm.submission_id = rs.submission_id
                   WHERE rs.status = 'Approved'
                   GROUP BY m.material_name";
$material_result = mysqli_query($conn, $material_query);

$material_labels = [];
$material_data = [];

while ($row = mysqli_fetch_assoc($material_result)) {
    $material_labels[] = $row['material_name'];
    $material_data[] = $row['count'];
}


// 3. CHART DATA: Participation Trends (Last 7 Days)
$trend_query = "SELECT DATE(created_at) as submit_date, COUNT(*) as count
                FROM recycling_submission
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY DATE(created_at)
                ORDER BY submit_date ASC";
$trend_result = mysqli_query($conn, $trend_query);

$trend_data_map = [];
while ($row = mysqli_fetch_assoc($trend_result)) {
    $trend_data_map[$row['submit_date']] = $row['count'];
}

// Fill in missing days with 0
$trend_labels = [];
$trend_counts = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $trend_labels[] = date('d M', strtotime($date)); // Format: 24 Nov
    $trend_counts[] = isset($trend_data_map[$date]) ? $trend_data_map[$date] : 0;
}


// 4. TOP CONTRIBUTORS (This Month)
$top_users_query = "SELECT u.username, u.lifetime_points, SUM(sm.quantity) as items_month
                    FROM user u
                    JOIN recycling_submission rs ON u.user_id = rs.user_id
                    JOIN submission_material sm ON rs.submission_id = sm.submission_id
                    WHERE rs.status = 'Approved' 
                    AND MONTH(rs.created_at) = MONTH(CURRENT_DATE())
                    AND YEAR(rs.created_at) = YEAR(CURRENT_DATE())
                    GROUP BY u.user_id
                    ORDER BY items_month DESC
                    LIMIT 5";
$top_users_result = mysqli_query($conn, $top_users_query);

$page_title = "System Analytics";
include 'includes/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    /* Responsive Grid for Stats*/
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--space-6);
        margin-bottom: var(--space-8);
    }

    .stat-card {
        background: var(--color-white);
        padding: var(--space-6);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        text-align: center;
        border: 1px solid var(--color-gray-200);
        min-width: 0; 
    }

    .stat-value {
        font-size: clamp(1.5rem, 4vw, 2.5rem);
        font-weight: 700;
        color: var(--color-primary);
        margin-bottom: var(--space-2);
        line-height: 1.2;
    }

    .stat-label {
        font-size: var(--text-sm);
        color: var(--color-gray-600);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Responsive Grid for Charts */
    .charts-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--space-6);
        margin-bottom: var(--space-8);
    }

    .chart-container {
        background: var(--color-white);
        padding: var(--space-6);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        border: 1px solid var(--color-gray-200);
    }

    .chart-title {
        font-size: var(--text-lg);
        font-weight: 700;
        color: var(--color-gray-800);
        margin-bottom: var(--space-4);
        padding-bottom: var(--space-2);
        border-bottom: 1px solid var(--color-gray-100);
    }

    .chart-canvas-wrapper {
        position: relative;
        height: 300px;
        width: 100%;
    }

    /* Responsive Table*/
    .table-container {
        background: var(--color-white);
        padding: var(--space-6);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        border: 1px solid var(--color-gray-200);
        margin-bottom: var(--space-8);
        overflow-x: auto; 
        -webkit-overflow-scrolling: touch;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 500px;
    }

    th {
        text-align: left;
        padding: var(--space-3);
        color: var(--color-gray-600);
        font-size: var(--text-sm);
        border-bottom: 2px solid var(--color-gray-100);
        white-space: nowrap;
    }

    td {
        padding: var(--space-4) var(--space-3);
        border-bottom: 1px solid var(--color-gray-100);
        color: var(--color-gray-800);
    }

    tr:last-child td {
        border-bottom: none;
    }

    .rank-badge {
        background: var(--color-primary-light);
        color: white;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
    }

    .page-header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: var(--space-4);
        flex-wrap: wrap;
    }

    @media (max-width: 900px) {
        .charts-row {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 600px) {
        .page-header-content {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .page-header-content button {
            width: 100%;
        }

        .stat-card {
            padding: var(--space-4);
        }
        
        .chart-canvas-wrapper {
            height: 250px;
        }
    }
</style>

<div class="page-header">
    <div class="page-header-content">
        <div>
            <h2 class="page-title"><i class="fas fa-chart-line" style="margin-right: 10px;"></i> System Analytics</h2>
            <p class="page-description">Campus-wide recycling statistics and trends</p>
        </div>
        <button class="btn-primary" onclick="window.print()">
            <i class="fas fa-file-pdf"></i> Generate Report
        </button>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?php echo number_format($total_items); ?></div>
        <div class="stat-label">Total Items</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo number_format($active_users); ?></div>
        <div class="stat-label">Active Users</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $co2_saved; ?><span style="font-size: 0.6em">kg</span></div>
        <div class="stat-label">CO₂ Reduced</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $participation_rate; ?><span style="font-size: 0.6em">%</span></div>
        <div class="stat-label">Participation</div>
    </div>
</div>

<div class="charts-row">
    <div class="chart-container">
        <h3 class="chart-title">Material Category Breakdown</h3>
        <div class="chart-canvas-wrapper">
            <canvas id="materialChart"></canvas>
        </div>
    </div>

    <div class="chart-container">
        <h3 class="chart-title">Participation Trends (7 Days)</h3>
        <div class="chart-canvas-wrapper">
            <canvas id="trendChart"></canvas>
        </div>
    </div>
</div>

<div class="table-container">
    <h3 class="chart-title">Top 5 Contributors (This Month)</h3>
    <?php if (mysqli_num_rows($top_users_result) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th width="10%">Rank</th>
                    <th width="40%">Recycler</th>
                    <th width="25%">Items Recycled</th>
                    <th width="25%">Lifetime Points</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $rank = 1;
                while ($user = mysqli_fetch_assoc($top_users_result)): 
                ?>
                    <tr>
                        <td><span class="rank-badge"><?php echo $rank++; ?></span></td>
                        <td>
                            <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                        </td>
                        <td><?php echo number_format($user['items_month']); ?></td>
                        <td>
                            <i class="fas fa-star" style="color: var(--color-accent-yellow);"></i> 
                            <?php echo number_format($user['lifetime_points']); ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align: center; color: var(--color-gray-500); padding: 20px;">No recycling activity recorded this month yet.</p>
    <?php endif; ?>
</div>

<script>
    // Material Chart Configuration
    const materialCtx = document.getElementById('materialChart').getContext('2d');
    const materialChart = new Chart(materialCtx, {
        type: 'doughnut', 
        data: {
            labels: <?php echo json_encode($material_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($material_data); ?>,
                backgroundColor: [
                    '#48BB78', '#4299E1', '#F6E05E', '#F56565', '#ED8936', '#805AD5'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12
                    }
                }
            }
        }
    });

    // Trend Chart Configuration
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    const trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($trend_labels); ?>,
            datasets: [{
                label: 'Submissions',
                data: <?php echo json_encode($trend_counts); ?>,
                borderColor: '#2D5D3F',
                backgroundColor: 'rgba(45, 93, 63, 0.1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
</script>

<?php 
include 'includes/footer.php'; 
mysqli_close($conn);
?>