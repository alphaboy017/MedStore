<?php
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.html");
    exit();
}

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM medicines WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}

header("Location: dashboard.php");
exit();
?>