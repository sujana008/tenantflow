<?php
// properties.php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'landlord') {
    header('Location: login.php');
    exit();
}

// Add new property
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_property'])) {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip_code = $_POST['zip_code'];
    $description = $_POST['description'];
    
    $pdo->query("INSERT INTO properties (owner_id, name, address, city, state, zip_code, description) 
                VALUES ({$_SESSION['user_id']}, '$name', '$address', '$city', '$state', '$zip_code', '$description')");
}

// Delete property
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->query("DELETE FROM properties WHERE id = $id AND owner_id = {$_SESSION['user_id']}");
    header('Location: properties.php');
    exit();
}

// Get all properties
$properties = $pdo->query("SELECT * FROM properties WHERE owner_id = {$_SESSION['user_id']}")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Properties - TenantFlow</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .header { background: #007bff; color: white; padding: 15px; display: flex; justify-content: space-between; }
        .container { max-width: 1200px; margin: 20px auto; padding: 0 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        button { background: #007bff; color: white; border: none; padding: 10px 15px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .actions a { margin-right: 10px; color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="header">
        <h1>TenantFlow</h1>
        <div>
            Welcome, <?= $_SESSION['username'] ?> | 
            <a href="dashboard.php">Dashboard</a> | 
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <h2>Properties</h2>
        
        <h3>Add New Property</h3>
        <form method="post">
            <div class="form-group">
                <label>Property Name:</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Address:</label>
                <textarea name="address" required></textarea>
            </div>
            <div class="form-group">
                <label>City:</label>
                <input type="text" name="city" required>
            </div>
            <div class="form-group">
                <label>State:</label>
                <input type="text" name="state" required>
            </div>
            <div class="form-group">
                <label>ZIP Code:</label>
                <input type="text" name="zip_code" required>
            </div>
            <div class="form-group">
                <label>Description:</label>
                <textarea name="description"></textarea>
            </div>
            <button type="submit" name="add_property">Add Property</button>
        </form>
        
        <h3>Your Properties</h3>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Address</th>
                    <th>City</th>
                    <th>State</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($properties as $property): ?>
                <tr>
                    <td><?= $property['name'] ?></td>
                    <td><?= $property['address'] ?></td>
                    <td><?= $property['city'] ?></td>
                    <td><?= $property['state'] ?></td>
                    <td class="actions">
                        <a href="units.php?property_id=<?= $property['id'] ?>">View Units</a>
                        <a href="properties.php?delete=<?= $property['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>