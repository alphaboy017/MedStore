<?php
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.html");
    exit();
}

// Get sales data for the last 30 days
$stmt = $pdo->query("
    SELECT DATE(date) as sale_date, SUM(total_price) as daily_sales
    FROM bills
    WHERE date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(date)
    ORDER BY sale_date
");
$sales_data = $stmt->fetchAll();

// Get top selling medicines
$stmt = $pdo->query("
    SELECT m.name, COUNT(b.id) as sales_count, SUM(b.qty) as total_quantity
    FROM bills b
    JOIN medicines m ON b.medicine_id = m.id
    GROUP BY m.id
    ORDER BY sales_count DESC
    LIMIT 5
");
$top_medicines = $stmt->fetchAll();

// Get low stock alerts
$stmt = $pdo->query("
    SELECT name, quantity, price
    FROM medicines
    WHERE quantity < 20
    ORDER BY quantity ASC
");
$low_stock = $stmt->fetchAll();

// Get revenue trends
$stmt = $pdo->query("
    SELECT 
        DATE_FORMAT(date, '%Y-%m') as month,
        SUM(total_price) as monthly_revenue
    FROM bills
    GROUP BY DATE_FORMAT(date, '%Y-%m')
    ORDER BY month DESC
    LIMIT 6
");
$revenue_trends = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - Medical Store</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <header>
            <h1>Analytics Dashboard</h1>
            <div class="user-info">
                <a href="dashboard.php" class="btn">Back to Dashboard</a>
            </div>
        </header>

        <div class="analytics-grid">
            <!-- Sales Chart -->
            <div class="analytics-card">
                <h2>Daily Sales (Last 30 Days)</h2>
                <canvas id="salesChart"></canvas>
            </div>

            <!-- Top Selling Medicines -->
            <div class="analytics-card">
                <h2>Top Selling Medicines</h2>
                <canvas id="topMedicinesChart"></canvas>
            </div>

            <!-- Revenue Trends -->
            <div class="analytics-card">
                <h2>Monthly Revenue Trends</h2>
                <canvas id="revenueChart"></canvas>
            </div>

            <!-- Low Stock Alerts -->
            <div class="analytics-card">
                <h2>Low Stock Alerts</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Medicine</th>
                            <th>Quantity</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($low_stock as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td class="<?php echo $item['quantity'] < 10 ? 'critical' : 'warning'; ?>">
                                    <?php echo $item['quantity']; ?>
                                </td>
                                <td>₹<?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Sales Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($sales_data, 'sale_date')); ?>,
                datasets: [{
                    label: 'Daily Sales',
                    data: <?php echo json_encode(array_column($sales_data, 'daily_sales')); ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Top Medicines Chart
        const medicinesCtx = document.getElementById('topMedicinesChart').getContext('2d');
        new Chart(medicinesCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($top_medicines, 'name')); ?>,
                datasets: [{
                    label: 'Total Quantity Sold',
                    data: <?php echo json_encode(array_column($top_medicines, 'total_quantity')); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($revenue_trends, 'month')); ?>,
                datasets: [{
                    label: 'Monthly Revenue',
                    data: <?php echo json_encode(array_column($revenue_trends, 'monthly_revenue')); ?>,
                    borderColor: 'rgb(255, 99, 132)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html> 