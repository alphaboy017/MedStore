<?php
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.html");
    exit();
}

// Get medicines list
$stmt = $pdo->query("SELECT * FROM medicines ORDER BY name");
$medicines = $stmt->fetchAll();

// Get medicines expiring in next 30 days
$stmt = $pdo->prepare("SELECT * FROM medicines WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) ORDER BY expiry_date");
$stmt->execute();
$expiring_medicines = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Medical Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Medical Store Dashboard</h1>
            <div class="user-info">
                Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </header>

        <nav>
            <a href="add_medicine.php" class="btn">Add New Medicine</a>
            <a href="bill.php" class="btn">Create Bill</a>
            <a href="analytics.php" class="btn">View Analytics</a>
            <a href="expiry_prediction.php" class="btn">Expiry Prediction</a>
        </nav>

        <section class="expiry-alerts">
            <h2>Expiry Alerts (Next 30 Days)</h2>
            <?php if (count($expiring_medicines) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Medicine</th>
                            <th>Batch No</th>
                            <th>Quantity</th>
                            <th>Expiry Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expiring_medicines as $medicine): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($medicine['name']); ?></td>
                                <td><?php echo htmlspecialchars($medicine['batch_no']); ?></td>
                                <td><?php echo $medicine['quantity']; ?></td>
                                <td><?php echo $medicine['expiry_date']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No medicines expiring in the next 30 days.</p>
            <?php endif; ?>
        </section>

        <section class="medicine-list">
            <h2>All Medicines</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Batch No</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Expiry Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($medicines as $medicine): ?>
                        <tr>
                            <td><?php echo $medicine['id']; ?></td>
                            <td><?php echo htmlspecialchars($medicine['name']); ?></td>
                            <td><?php echo htmlspecialchars($medicine['batch_no']); ?></td>
                            <td>₹<?php echo number_format($medicine['price'], 2); ?></td>
                            <td><?php echo $medicine['quantity']; ?></td>
                            <td><?php echo $medicine['expiry_date']; ?></td>
                            <td>
                                <a href="edit_medicine.php?id=<?php echo $medicine['id']; ?>" class="btn-small">Edit</a>
                                <a href="delete_medicine.php?id=<?php echo $medicine['id']; ?>" class="btn-small delete" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html> 