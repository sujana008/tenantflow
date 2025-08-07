<?php
// tenants.php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$unit_id = $_GET['unit_id'] ?? null;

// For landlords only
if ($_SESSION['user_type'] === 'landlord') {
    // Add new tenant
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_tenant'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $full_name = $_POST['full_name'];
        $phone = $_POST['phone'];
        $emergency_contact_name = $_POST['emergency_contact_name'];
        $emergency_contact_phone = $_POST['emergency_contact_phone'];
        $lease_start = $_POST['lease_start'];
        $lease_end = $_POST['lease_end'];
        
        // Create user first
        $pdo->query("INSERT INTO users (username, email, password, user_type, full_name, phone) 
                    VALUES ('$username', '$email', '$password', 'tenant', '$full_name', '$phone')");
        $user_id = $pdo->lastInsertId();
        
        // Then create tenant
        $pdo->query("INSERT INTO tenants (user_id, unit_id, emergency_contact_name, emergency_contact_phone, lease_start, lease_end) 
                    VALUES ($user_id, $unit_id, '$emergency_contact_name', '$emergency_contact_phone', '$lease_start', '$lease_end')");
        
        // Update unit status
        $pdo->query("UPDATE units SET status = 'occupied' WHERE id = $unit_id");
    }

    // Delete tenant
    if (isset($_GET['delete'])) {
        $id = $_GET['delete'];
        $tenant = $pdo->query("SELECT * FROM tenants WHERE id = $id")->fetch();
        $pdo->query("DELETE FROM users WHERE id = {$tenant['user_id']}");
        $pdo->query("DELETE FROM tenants WHERE id = $id");
        
        // Update unit status
        $pdo->query("UPDATE units SET status = 'vacant' WHERE id = $unit_id");
        
        header("Location: tenants.php?unit_id=$unit_id");
        exit();
    }
}

// Get unit info
$unit = $pdo->query("SELECT * FROM units WHERE id = $unit_id")->fetch();
$property = $pdo->query("SELECT * FROM properties WHERE id = {$unit['property_id']}")->fetch();

// Get all tenants for this unit
$tenants = $pdo->query("SELECT t.*, u.* FROM tenants t JOIN users u ON t.user_id = u.id WHERE t.unit_id = $unit_id")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tenants - TenantFlow</title>
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
            <?php else: ?>
                <a href="tenant-dashboard.php">Dashboard</a> | 
            <?php endif; ?>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <h2>Tenants for Unit <?= $unit['unit_number'] ?> - <?= $property['name'] ?></h2>
        
        <?php if ($_SESSION['user_type'] === 'landlord'): ?>
            <h3>Add New Tenant</h3>
            <form method="post">
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Full Name:</label>
                    <input type="text" name="full_name" required>
                </div>
                <div class="form-group">
                    <label>Phone:</label>
                    <input type="text" name="phone">
                </div>
                <div class="form-group">
                    <label>Emergency Contact Name:</label>
                    <input type="text" name="emergency_contact_name">
                </div>
                <div class="form-group">
                    <label>Emergency Contact Phone:</label>
                    <input type="text" name="emergency_contact_phone">
                </div>
                <div class="form-group">
                    <label>Lease Start:</label>
                    <input type="date" name="lease_start" required>
                </div>
                <div class="form-group">
                    <label>Lease End:</label>
                    <input type="date" name="lease_end" required>
                </div>
                <button type="submit" name="add_tenant">Add Tenant</button>
            </form>
        <?php endif; ?>
        
        <h3>Current Tenants</h3>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Lease Period</th>
                    <?php if ($_SESSION['user_type'] === 'landlord'): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tenants as $tenant): ?>
                <tr>
                    <td><?= $tenant['full_name'] ?></td>
                    <td><?= $tenant['email'] ?></td>
                    <td><?= $tenant['phone'] ?></td>
                    <td><?= $tenant['lease_start'] ?> to <?= $tenant['lease_end'] ?></td>
                    <?php if ($_SESSION['user_type'] === 'landlord'): ?>
                        <td class="actions">
                            <a href="payments.php?tenant_id=<?= $tenant['id'] ?>">Payments</a>
                            <a href="tenants.php?unit_id=<?= $unit_id ?>&delete=<?= $tenant['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>