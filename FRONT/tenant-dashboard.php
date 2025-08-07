<?php
// tenant-dashboard.php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tenant') {
    header('Location: login.php');
    exit();
}

// Get tenant info
$tenant = $pdo->query("SELECT t.*, u.* FROM tenants t JOIN users u ON t.user_id = u.id WHERE t.user_id = {$_SESSION['user_id']}")->fetch();
$unit = $pdo->query("SELECT * FROM units WHERE id = {$tenant['unit_id']}")->fetch();
$property = $pdo->query("SELECT * FROM properties WHERE id = {$unit['property_id']}")->fetch();
$payments = $pdo->query("SELECT SUM(amount) FROM payments WHERE tenant_id = {$tenant['id']}")->fetchColumn();
$maintenance = $pdo->query("SELECT COUNT(*) FROM maintenance_requests WHERE tenant_id = {$tenant['id']} AND status != 'completed'")->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tenant Dashboard - TenantFlow</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .header { background: #007bff; color: white; padding: 15px; display: flex; justify-content: space-between; }
        .container { max-width: 1200px; margin: 20px auto; padding: 0 15px; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); padding: 20px; }
        .menu { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
        .menu-item { background: white; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); padding: 20px; }
        .menu-item:hover { background: #f8f9fa; }
        a { text-decoration: none; color: inherit; }
    </style>
</head>
<body>
    <div class="header">
        <h1>TenantFlow</h1>
        <div>
            Welcome, <?= $_SESSION['username'] ?> | 
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <h2>Tenant Dashboard</h2>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Your Unit</h3>
                <p><?= $unit['unit_number'] ?> - <?= $unit['type'] ?></p>
                <p><?= $property['name'] ?>, <?= $property['city'] ?></p>
                <p>Rent: $<?= number_format($unit['rent_amount'], 2) ?></p>
            </div>
            <div class="stat-card">
                <h3>Lease Information</h3>
                <p>Start: <?= $tenant['lease_start'] ?></p>
                <p>End: <?= $tenant['lease_end'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Payments</h3>
                <p>Total Paid: $<?= number_format($payments, 2) ?></p>
                <p>Open Maintenance Requests: <?= $maintenance ?></p>
            </div>
        </div>
        
        <div class="menu">
            <a href="payments.php" class="menu-item">
                <h3>Payments</h3>
                <p>View your payment history</p>
            </a>
            <a href="maintenance.php" class="menu-item">
                <h3>Maintenance</h3>
                <p>Request maintenance</p>
            </a>
            <a href="documents.php" class="menu-item">
                <h3>Documents</h3>
                <p>View your lease and documents</p>
            </a>
            <a href="reminders.php" class="menu-item">
                <h3>Reminders</h3>
                <p>View upcoming reminders</p>
            </a>
        </div>
    </div>
</body>
</html>