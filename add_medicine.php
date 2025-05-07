<?php
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.html");
    exit();
}

$medicine = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM medicines WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $medicine = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $batch_no = $_POST['batch_no'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $expiry_date = $_POST['expiry_date'];

    if (isset($_POST['id'])) {
        // Update existing medicine
        $stmt = $pdo->prepare("UPDATE medicines SET name = ?, batch_no = ?, price = ?, quantity = ?, expiry_date = ? WHERE id = ?");
        $stmt->execute([$name, $batch_no, $price, $quantity, $expiry_date, $_POST['id']]);
    } else {
        // Add new medicine
        $stmt = $pdo->prepare("INSERT INTO medicines (name, batch_no, price, quantity, expiry_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $batch_no, $price, $quantity, $expiry_date]);
    }

    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $medicine ? 'Edit' : 'Add'; ?> Medicine - Medical Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><?php echo $medicine ? 'Edit' : 'Add'; ?> Medicine</h1>
            <a href="dashboard.php" class="btn">Back to Dashboard</a>
        </header>

        <form method="POST" class="medicine-form">
            <?php if ($medicine): ?>
                <input type="hidden" name="id" value="<?php echo $medicine['id']; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="name">Medicine Name</label>
                <input type="text" id="name" name="name" required 
                       value="<?php echo $medicine ? htmlspecialchars($medicine['name']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="batch_no">Batch Number</label>
                <input type="text" id="batch_no" name="batch_no" required
                       value="<?php echo $medicine ? htmlspecialchars($medicine['batch_no']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="price">Price (₹)</label>
                <input type="number" id="price" name="price" step="0.01" required
                       value="<?php echo $medicine ? $medicine['price'] : ''; ?>">
            </div>

            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" required
                       value="<?php echo $medicine ? $medicine['quantity'] : ''; ?>">
            </div>

            <div class="form-group">
                <label for="expiry_date">Expiry Date</label>
                <input type="date" id="expiry_date" name="expiry_date" required
                       value="<?php echo $medicine ? $medicine['expiry_date'] : ''; ?>">
            </div>

            <button type="submit" class="btn"><?php echo $medicine ? 'Update' : 'Add'; ?> Medicine</button>
        </form>
    </div>
</body>
</html> 