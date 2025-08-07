<?php
// payments.php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$tenant_id = $_GET['tenant_id'] ?? null;

// For landlords adding payments
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_payment'])) {
    $amount = $_POST['amount'];
    $payment_date = $_POST['payment_date'];
    $due_date = $_POST['due_date'];
    $method = $_POST['method'];
    $reference_number = $_POST['reference_number'];
    $status = $_POST['status'];
    $notes = $_POST['notes'];
    
    $pdo->query("INSERT INTO payments (tenant_id, amount, payment_date, due_date, method, reference_number, status, notes) 
                VALUES ($tenant_id, $amount, '$payment_date', '$due_date', '$method', '$reference_number', '$status', '$notes')");
}

// Get tenant info
if ($tenant_id) {
    $tenant = $pdo->query("SELECT t.*, u.* FROM tenants t JOIN users u ON t.user_id = u.id WHERE t.id = $tenant_id")->fetch();
    $unit = $pdo->query("SELECT * FROM units WHERE id = {$tenant['unit_id']}")->fetch();
    $property = $pdo->query("SELECT * FROM properties WHERE id = {$unit['property_id']}")->fetch();
}

// Get payments
if ($_SESSION['user_type'] === 'landlord' && $tenant_id) {
    $payments = $pdo->query("SELECT * FROM payments WHERE tenant_id = $tenant_id")->fetchAll();
} else {
    // For tenants, show their own payments
    $tenant = $pdo->query("SELECT * FROM tenants WHERE user_id = {$_SESSION['user_id']}")->fetch();
    $payments = $pdo->query("SELECT * FROM payments WHERE tenant_id = {$tenant['id']}")->fetchAll();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payments - TenantFlow</title>
    <style>
        /* Same styles as properties.php */
    </style>
</head>
<body>
    <div class="header">
        <h1>TenantFlow</h1>
        <div>
            Welcome, <?= $_SESSION['username'] ?> | 
            <?php if ($_SESSION['user_type'] === 'landlord'): ?>
                <a href="dashboard.php">Dashboard</a> | 
                <a href="properties.php">Properties</a> | 
                <a href="units.php?property_id=<?= $property['id'] ?>">Units</a> | 
                <a href="tenants.php?unit_id=<?= $tenant['unit_id'] ?>">Tenants</a> | 
            <?php else: ?>
                <a href="tenant-dashboard.php">Dashboard</a> | 
            <?php endif; ?>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <?php if ($_SESSION['user_type'] === 'landlord' && $tenant_id): ?>
            <h2>Payments for <?= $tenant['full_name'] ?> (Unit <?= $unit['unit_number'] ?>)</h2>
            
            <h3>Record New Payment</h3>
            <form method="post">
                <div class="form-group">
                    <label>Amount:</label>
                    <input type="number" step="0.01" name="amount" required>
                </div>
                <div class="form-group">
                    <label>Payment Date:</label>
                    <input type="date" name="payment_date" required>
                </div>
                <div class="form-group">
                    <label>Due Date:</label>
                    <input type="date" name="due_date" required>
                </div>
                <div class="form-group">
                    <label>Payment Method:</label>
                    <select name="method" required>
                        <option value="cash">Cash</option>
                        <option value="check">Check</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="online">Online</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Reference Number:</label>
                    <input type="text" name="reference_number">
                </div>
                <div class="form-group">
                    <label>Status:</label>
                    <select name="status" required>
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                        <option value="late">Late</option>
                        <option value="partial">Partial</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Notes:</label>
                    <textarea name="notes"></textarea>
                </div>
                <button type="submit" name="add_payment">Record Payment</button>
            </form>
        <?php elseif ($_SESSION['user_type'] === 'tenant'): ?>
            <h2>Your Payment History</h2>
        <?php endif; ?>
        
        <h3>Payment Records</h3>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Due Date</th>
                    <th>Method</th>
                    <th>Status</th>
                    <?php if ($_SESSION['user_type'] === 'landlord'): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $payment): ?>
                <tr>
                    <td><?= $payment['payment_date'] ?></td>
                    <td>$<?= number_format($payment['amount'], 2) ?></td>
                    <td><?= $payment['due_date'] ?></td>
                    <td><?= ucfirst(str_replace('_', ' ', $payment['method'])) ?></td>
                    <td><?= ucfirst($payment['status']) ?></td>
                    <?php if ($_SESSION['user_type'] === 'landlord'): ?>
                        <td class="actions">
                            <a href="#" onclick="alert('Edit functionality not implemented')">Edit</a>
                        </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>