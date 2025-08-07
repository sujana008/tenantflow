<?php
// units.php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'landlord') {
    header('Location: login.php');
    exit();
}

$property_id = $_GET['property_id'] ?? null;

// Add new unit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_unit'])) {
    $unit_number = $_POST['unit_number'];
    $type = $_POST['type'];
    $bedrooms = $_POST['bedrooms'];
    $bathrooms = $_POST['bathrooms'];
    $square_feet = $_POST['square_feet'];
    $rent_amount = $_POST['rent_amount'];
    $deposit_amount = $_POST['deposit_amount'];
    $features = $_POST['features'];
    
    $pdo->query("INSERT INTO units (property_id, unit_number, type, bedrooms, bathrooms, square_feet, rent_amount, deposit_amount, features) 
                VALUES ($property_id, '$unit_number', '$type', $bedrooms, $bathrooms, $square_feet, $rent_amount, $deposit_amount, '$features')");
}

// Delete unit
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->query("DELETE FROM units WHERE id = $id AND property_id = $property_id");
    header("Location: units.php?property_id=$property_id");
    exit();
}

// Get property info
$property = $pdo->query("SELECT * FROM properties WHERE id = $property_id")->fetch();

// Get all units for this property
$units = $pdo->query("SELECT * FROM units WHERE property_id = $property_id")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Units - TenantFlow</title>
    <style>
        /* Same styles as properties.php */
    </style>
</head>
<body>
    <div class="header">
        <h1>TenantFlow</h1>
        <div>
            Welcome, <?= $_SESSION['username'] ?> | 
            <a href="dashboard.php">Dashboard</a> | 
            <a href="properties.php">Properties</a> | 
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <h2>Units for <?= $property['name'] ?></h2>
        
        <h3>Add New Unit</h3>
        <form method="post">
            <div class="form-group">
                <label>Unit Number:</label>
                <input type="text" name="unit_number" required>
            </div>
            <div class="form-group">
                <label>Type:</label>
                <input type="text" name="type" required>
            </div>
            <div class="form-group">
                <label>Bedrooms:</label>
                <input type="number" name="bedrooms" required>
            </div>
            <div class="form-group">
                <label>Bathrooms:</label>
                <input type="number" step="0.1" name="bathrooms" required>
            </div>
            <div class="form-group">
                <label>Square Feet:</label>
                <input type="number" name="square_feet">
            </div>
            <div class="form-group">
                <label>Rent Amount:</label>
                <input type="number" step="0.01" name="rent_amount" required>
            </div>
            <div class="form-group">
                <label>Deposit Amount:</label>
                <input type="number" step="0.01" name="deposit_amount" required>
            </div>
            <div class="form-group">
                <label>Features:</label>
                <textarea name="features"></textarea>
            </div>
            <button type="submit" name="add_unit">Add Unit</button>
        </form>
        
        <h3>Units List</h3>
        <table>
            <thead>
                <tr>
                    <th>Unit #</th>
                    <th>Type</th>
                    <th>Bed/Bath</th>
                    <th>Rent</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($units as $unit): ?>
                <tr>
                    <td><?= $unit['unit_number'] ?></td>
                    <td><?= $unit['type'] ?></td>
                    <td><?= $unit['bedrooms'] ?> / <?= $unit['bathrooms'] ?></td>
                    <td>$<?= number_format($unit['rent_amount'], 2) ?></td>
                    <td><?= ucfirst($unit['status']) ?></td>
                    <td class="actions">
                        <a href="tenants.php?unit_id=<?= $unit['id'] ?>">View Tenants</a>
                        <a href="units.php?property_id=<?= $property_id ?>&delete=<?= $unit['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>