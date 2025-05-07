<?php
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.html");
    exit();
}

// Get all medicines with their expiry dates
$stmt = $pdo->query("
    SELECT id, name, batch_no, quantity, expiry_date,
           DATEDIFF(expiry_date, CURDATE()) as days_until_expiry
    FROM medicines
    ORDER BY days_until_expiry ASC
");
$medicines = $stmt->fetchAll();

// Function to predict reorder date based on historical data
function predictReorderDate($days_until_expiry, $quantity) {
    // Simple prediction model (can be enhanced with ML)
    $average_daily_usage = 5; // This should be calculated from historical data
    $safety_stock = 20; // Minimum quantity to maintain
    
    $days_of_stock = $quantity / $average_daily_usage;
    $reorder_point = $safety_stock / $average_daily_usage;
    
    if ($days_of_stock < $reorder_point) {
        return "Immediate";
    } elseif ($days_until_expiry < $days_of_stock) {
        return "Before " . date('Y-m-d', strtotime("+$days_until_expiry days"));
    } else {
        return "Before " . date('Y-m-d', strtotime("+" . ($days_of_stock - $reorder_point) . " days"));
    }
}

// Function to get expiry risk level
function getExpiryRisk($days_until_expiry) {
    if ($days_until_expiry <= 30) {
        return ['level' => 'high', 'color' => 'red'];
    } elseif ($days_until_expiry <= 90) {
        return ['level' => 'medium', 'color' => 'orange'];
    } else {
        return ['level' => 'low', 'color' => 'green'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Expiry Prediction - Medical Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Medicine Expiry Prediction</h1>
            <div class="user-info">
                <a href="dashboard.php" class="btn">Back to Dashboard</a>
            </div>
        </header>

        <div class="prediction-grid">
            <div class="prediction-card">
                <h2>Expiry Risk Analysis</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Medicine</th>
                            <th>Batch No</th>
                            <th>Quantity</th>
                            <th>Expiry Date</th>
                            <th>Days Until Expiry</th>
                            <th>Risk Level</th>
                            <th>Reorder Suggestion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($medicines as $medicine): 
                            $risk = getExpiryRisk($medicine['days_until_expiry']);
                            $reorder_date = predictReorderDate($medicine['days_until_expiry'], $medicine['quantity']);
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($medicine['name']); ?></td>
                                <td><?php echo htmlspecialchars($medicine['batch_no']); ?></td>
                                <td><?php echo $medicine['quantity']; ?></td>
                                <td><?php echo $medicine['expiry_date']; ?></td>
                                <td><?php echo $medicine['days_until_expiry']; ?></td>
                                <td class="risk-<?php echo $risk['level']; ?>">
                                    <?php echo ucfirst($risk['level']); ?>
                                </td>
                                <td><?php echo $reorder_date; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="prediction-card">
                <h2>Expiry Risk Distribution</h2>
                <div class="risk-distribution">
                    <?php
                    $high_risk = 0;
                    $medium_risk = 0;
                    $low_risk = 0;
                    
                    foreach ($medicines as $medicine) {
                        $risk = getExpiryRisk($medicine['days_until_expiry']);
                        if ($risk['level'] == 'high') $high_risk++;
                        elseif ($risk['level'] == 'medium') $medium_risk++;
                        else $low_risk++;
                    }
                    ?>
                    <div class="risk-bar high" style="width: <?php echo ($high_risk / count($medicines)) * 100; ?>%">
                        High Risk: <?php echo $high_risk; ?>
                    </div>
                    <div class="risk-bar medium" style="width: <?php echo ($medium_risk / count($medicines)) * 100; ?>%">
                        Medium Risk: <?php echo $medium_risk; ?>
                    </div>
                    <div class="risk-bar low" style="width: <?php echo ($low_risk / count($medicines)) * 100; ?>%">
                        Low Risk: <?php echo $low_risk; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 