<?php
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.html");
    exit();
}

// Get medicines for search
$stmt = $pdo->query("SELECT * FROM medicines WHERE quantity > 0 ORDER BY name");
$medicines = $stmt->fetchAll();

// Process bill submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medicine_id = $_POST['medicine_id'];
    $quantity = $_POST['quantity'];
    
    // Get medicine details
    $stmt = $pdo->prepare("SELECT * FROM medicines WHERE id = ?");
    $stmt->execute([$medicine_id]);
    $medicine = $stmt->fetch();
    
    if ($medicine && $medicine['quantity'] >= $quantity) {
        $total_price = $medicine['price'] * $quantity;
        
        // Update medicine quantity
        $stmt = $pdo->prepare("UPDATE medicines SET quantity = quantity - ? WHERE id = ?");
        $stmt->execute([$quantity, $medicine_id]);
        
        // Add to bills
        $stmt = $pdo->prepare("INSERT INTO bills (medicine_id, qty, total_price) VALUES (?, ?, ?)");
        $stmt->execute([$medicine_id, $quantity, $total_price]);
        
        header("Location: bill.php?success=1");
        exit();
    } else {
        $error = "Insufficient quantity available";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Bill - Medical Store</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body>
    <div class="container">
        <header>
            <h1>Create Bill</h1>
            <a href="dashboard.php" class="btn">Back to Dashboard</a>
        </header>

        <?php if (isset($error)): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">Bill created successfully!</div>
        <?php endif; ?>

        <div class="bill-container">
            <form method="POST" class="bill-form">
                <div class="form-group">
                    <label for="medicine_search">Search Medicine</label>
                    <input type="text" id="medicine_search" placeholder="Type to search...">
                </div>

                <div class="form-group">
                    <label for="medicine_id">Select Medicine</label>
                    <select id="medicine_id" name="medicine_id" required>
                        <option value="">Select a medicine</option>
                        <?php foreach ($medicines as $medicine): ?>
                            <option value="<?php echo $medicine['id']; ?>" 
                                    data-price="<?php echo $medicine['price']; ?>"
                                    data-quantity="<?php echo $medicine['quantity']; ?>">
                                <?php echo htmlspecialchars($medicine['name']); ?> 
                                (₹<?php echo number_format($medicine['price'], 2); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" min="1" required>
                </div>

                <div class="form-group">
                    <label>Total Price</label>
                    <div id="total_price">₹0.00</div>
                </div>

                <button type="submit" class="btn">Create Bill</button>
            </form>

            <div id="bill_preview" class="bill-preview">
                <h2>Bill Preview</h2>
                <div class="bill-details">
                    <p>Date: <span id="bill_date"><?php echo date('Y-m-d H:i:s'); ?></span></p>
                    <p>Medicine: <span id="bill_medicine">-</span></p>
                    <p>Quantity: <span id="bill_quantity">-</span></p>
                    <p>Price per unit: <span id="bill_unit_price">-</span></p>
                    <p>Total Price: <span id="bill_total">-</span></p>
                </div>
                <button id="export_pdf" class="btn">Export as PDF</button>
            </div>
        </div>
    </div>

    <script>
        // Medicine search functionality
        document.getElementById('medicine_search').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const options = document.getElementById('medicine_id').options;
            
            for (let i = 1; i < options.length; i++) {
                const option = options[i];
                const text = option.text.toLowerCase();
                option.style.display = text.includes(searchTerm) ? '' : 'none';
            }
        });

        // Calculate total price
        document.getElementById('medicine_id').addEventListener('change', updateTotal);
        document.getElementById('quantity').addEventListener('input', updateTotal);

        function updateTotal() {
            const medicineSelect = document.getElementById('medicine_id');
            const quantity = document.getElementById('quantity').value;
            const selectedOption = medicineSelect.options[medicineSelect.selectedIndex];
            
            if (selectedOption.value && quantity) {
                const price = selectedOption.dataset.price;
                const total = price * quantity;
                document.getElementById('total_price').textContent = '₹' + total.toFixed(2);
                
                // Update bill preview
                document.getElementById('bill_medicine').textContent = selectedOption.text.split(' (')[0];
                document.getElementById('bill_quantity').textContent = quantity;
                document.getElementById('bill_unit_price').textContent = '₹' + price;
                document.getElementById('bill_total').textContent = '₹' + total.toFixed(2);
            }
        }

        // Export to PDF
        document.getElementById('export_pdf').addEventListener('click', function() {
            const element = document.getElementById('bill_preview');
            const opt = {
                margin: 1,
                filename: 'bill.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        });
    </script>
</body>
</html> 