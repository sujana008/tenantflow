<?php
// maintenance.php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// For tenants submitting requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $issue_type = $_POST['issue_type'];
    $priority = $_POST['priority'];
    
    $tenant = $pdo->query("SELECT * FROM tenants WHERE user_id = {$_SESSION['user_id']}")->fetch();
    
    $pdo->query("INSERT INTO maintenance_requests (tenant_id, unit_id, title, description, issue_type, priority) 
                VALUES ({$tenant['id']}, {$tenant['unit_id']}, '$title', '$description', '$issue_type', '$priority')");
}

// For landlords updating requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_request'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $assigned_to = $_POST['assigned_to'];
    $completion_date = $_POST['completion_date'];
    $cost = $_POST['cost'];
    $notes = $_POST['notes'];
    
    $pdo->query("UPDATE maintenance_requests SET 
                status = '$status', 
                assigned_to = $assigned_to, 
                completion_date = '$completion_date', 
                cost = $cost, 
                notes = '$notes',
                updated_at = NOW()
                WHERE id = $id");
}

// Get requests based on user type
if ($_SESSION['user_type'] === 'landlord') {
    $requests = $pdo->query("SELECT mr.*, u.full_name, un.unit_number, p.name as property_name 
                            FROM maintenance_requests mr
                            JOIN tenants t ON mr.tenant_id = t.id
                            JOIN users u ON t.user_id = u.id
                            JOIN units un ON mr.unit_id = un.id
                            JOIN properties p ON un.property_id = p.id
                            ORDER BY mr.created_at DESC")->fetchAll();
} else {
    $tenant = $pdo->query("SELECT * FROM tenants WHERE user_id = {$_SESSION['user_id']}")->fetch();
    $requests = $pdo->query("SELECT mr.*, un.unit_number, p.name as property_name 
                            FROM maintenance_requests mr
                            JOIN units un ON mr.unit_id = un.id
                            JOIN properties p ON un.property_id = p.id
                            WHERE mr.tenant_id = {$tenant['id']}
                            ORDER BY mr.created_at DESC")->fetchAll();
}

// Get staff for assignment dropdown
$staff = $pdo->query("SELECT * FROM users WHERE user_type = 'landlord'")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Maintenance - TenantFlow</title>
    <style>
        /* Same styles as properties.php */
        .priority-low { color: green; }
        .priority-medium { color: orange; }
        .priority-high { color: red; }
        .priority-emergency { font-weight: bold; color: darkred; }
    </style>
</head>
<body>
    <div class="header">
        <h1>TenantFlow</h1>
        <div>
            Welcome, <?= $_SESSION['username'] ?> | 
            <?php if ($_SESSION['user_type'] === 'landlord'): ?>
                <a href="dashboard.php">Dashboard</a> | 
            <?php else: ?>
                <a href="tenant-dashboard.php">Dashboard</a> | 
            <?php endif; ?>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <h2>Maintenance Requests</h2>
        
        <?php if ($_SESSION['user_type'] === 'tenant'): ?>
            <h3>Submit New Request</h3>
            <form method="post">
                <div class="form-group">
                    <label>Title:</label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>Description:</label>
                    <textarea name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label>Issue Type:</label>
                    <select name="issue_type" required>
                        <option value="plumbing">Plumbing</option>
                        <option value="electrical">Electrical</option>
                        <option value="appliance">Appliance</option>
                        <option value="structural">Structural</option>
                        <option value="heating">Heating</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Priority:</label>
                    <select name="priority" required>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="emergency">Emergency</option>
                    </select>
                </div>
                <button type="submit" name="submit_request">Submit Request</button>
            </form>
        <?php endif; ?>
        
        <h3><?= $_SESSION['user_type'] === 'landlord' ? 'All' : 'Your' ?> Requests</h3>
        <table>
            <thead>
                <tr>
                    <?php if ($_SESSION['user_type'] === 'landlord'): ?>
                        <th>Tenant</th>
                        <th>Property/Unit</th>
                    <?php endif; ?>
                    <th>Title</th>
                    <th>Issue Type</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Date</th>
                    <?php if ($_SESSION['user_type'] === 'landlord'): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $request): ?>
                <tr>
                    <?php if ($_SESSION['user_type'] === 'landlord'): ?>
                        <td><?= $request['full_name'] ?></td>
                        <td><?= $request['property_name'] ?> - <?= $request['unit_number'] ?></td>
                    <?php endif; ?>
                    <td><?= $request['title'] ?></td>
                    <td><?= ucfirst($request['issue_type']) ?></td>
                    <td class="priority-<?= $request['priority'] ?>"><?= ucfirst($request['priority']) ?></td>
                    <td><?= ucfirst(str_replace('_', ' ', $request['status'])) ?></td>
                    <td><?= $request['created_at'] ?></td>
                    <?php if ($_SESSION['user_type'] === 'landlord'): ?>
                        <td class="actions">
                            <a href="#" onclick="document.getElementById('modal-<?= $request['id'] ?>').style.display='block'">Manage</a>
                        </td>
                    <?php else: ?>
                        <td class="actions">
                            <a href="#" onclick="alert('Details: <?= addslashes($request['description']) ?>')">View</a>
                        </td>
                    <?php endif; ?>
                </tr>
                
                <?php if ($_SESSION['user_type'] === 'landlord'): ?>
                <!-- Modal for managing request -->
                <div id="modal-<?= $request['id'] ?>" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; padding:20px; box-sizing:border-box;">
                    <div style="background:white; padding:20px; max-width:600px; margin:50px auto; border-radius:5px;">
                        <h3>Manage Request: <?= $request['title'] ?></h3>
                        <p><strong>Description:</strong> <?= $request['description'] ?></p>
                        
                        <form method="post">
                            <input type="hidden" name="id" value="<?= $request['id'] ?>">
                            <div class="form-group">
                                <label>Status:</label>
                                <select name="status" required>
                                    <option value="open" <?= $request['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                                    <option value="in_progress" <?= $request['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="completed" <?= $request['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled" <?= $request['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Assign To:</label>
                                <select name="assigned_to">
                                    <option value="">-- Select --</option>
                                    <?php foreach ($staff as $person): ?>
                                        <option value="<?= $person['id'] ?>" <?= $request['assigned_to'] == $person['id'] ? 'selected' : '' ?>>
                                            <?= $person['full_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Completion Date:</label>
                                <input type="date" name="completion_date" value="<?= $request['completion_date'] ?>">
                            </div>
                            <div class="form-group">
                                <label>Cost:</label>
                                <input type="number" step="0.01" name="cost" value="<?= $request['cost'] ?>">
                            </div>
                            <div class="form-group">
                                <label>Notes:</label>
                                <textarea name="notes"><?= $request['notes'] ?></textarea>
                            </div>
                            <button type="submit" name="update_request">Update</button>
                            <button type="button" onclick="document.getElementById('modal-<?= $request['id'] ?>').style.display='none'">Close</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>