<?php
// dashboard.php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'landlord') {
    header('Location: login.php');
    exit();
}

// Get properties count
$properties = $pdo->query("SELECT COUNT(*) FROM properties WHERE owner_id = {$_SESSION['user_id']}")->fetchColumn();
$tenants = $pdo->query("SELECT COUNT(*) FROM tenants t JOIN users u ON t.user_id = u.id WHERE u.user_type = 'tenant'")->fetchColumn();
$payments = $pdo->query("SELECT SUM(amount) FROM payments")->fetchColumn();
$maintenance = $pdo->query("SELECT COUNT(*) FROM maintenance_requests WHERE status != 'completed'")->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Landlord Dashboard - TenantFlow</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .header { background: #007bff; color: white; padding: 15px; display: flex; justify-content: space-between; }
        .container { max-width: 1200px; margin: 20px auto; padding: 0 15px; }
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); padding: 20px; text-align: center; }
        .stat-card h3 { margin-top: 0; }
        .stat-card .number { font-size: 24px; font-weight: bold; }
        .menu { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
        .menu-item { background: white; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); padding: 20px; text-align: center; }
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
        <h2>Dashboard</h2>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Properties</h3>
                <div class="number"><?= $properties ?></div>
            </div>
            <div class="stat-card">
                <h3>Tenants</h3>
                <div class="number"><?= $tenants ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Payments</h3>
                <div class="number">$<?= number_format($payments, 2) ?></div>
            </div>
            <div class="stat-card">
                <h3>Open Maintenance</h3>
                <div class="number"><?= $maintenance ?></div>
            </div>
        </div>
        
        <div class="menu">
            <a href="properties.php" class="menu-item">
                <h3>Properties</h3>
                <p>Manage your properties</p>
            </a>
            <a href="tenants.php" class="menu-item">
                <h3>Tenants</h3>
                <p>Manage tenants</p>
            </a>
            <a href="payments.php" class="menu-item">
                <h3>Payments</h3>
                <p>View payment records</p>
            </a>
            <a href="units.php" class="menu-item">
                <h3>Units</h3>
                <p>Manage rental units</p>
            </a>
            <a href="maintenance.php" class="menu-item">
                <h3>Maintenance</h3>
                <p>Handle maintenance requests</p>
            </a>
            <a href="reminders.php" class="menu-item">
                <h3>Reminders</h3>
                <p>Set up reminders</p>
            </a>
        </div>
    </div>
</body>
</html>