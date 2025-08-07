<?php
// reminders.php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Add new reminder
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_reminder'])) {
    $related_id = $_POST['related_id'] ?: null;
    $reminder_type = $_POST['reminder_type'];
    $message = $_POST['message'];
    $send_date = $_POST['send_date'];
    
    $pdo->query("INSERT INTO reminders (user_id, related_id, reminder_type, message, send_date) 
                VALUES ({$_SESSION['user_id']}, $related_id, '$reminder_type', '$message', '$send_date')");
}

// Delete reminder
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->query("DELETE FROM reminders WHERE id = $id AND user_id = {$_SESSION['user_id']}");
    header("Location: reminders.php");
    exit();
}

// Get reminders
$reminders = $pdo->query("SELECT * FROM reminders WHERE user_id = {$_SESSION['user_id']} ORDER BY send_date")->fetchAll();

// Get related items for dropdown
if ($_SESSION['user_type'] === 'landlord') {
    $properties = $pdo->query("SELECT * FROM properties WHERE owner_id = {$_SESSION['user_id']}")->fetchAll();
    $tenants = $pdo->query("SELECT t.id, u.full_name FROM tenants t JOIN users u ON t.user_id = u.id")->fetchAll();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reminders - TenantFlow</title>
    <style>
        /* Same styles as properties.php */
        .reminder-pending { color: #007bff; }
        .reminder-sent { color: green; }
        .reminder-cancelled { color: gray; }
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
        <h2>Reminders</h2>
        
        <h3>Create New Reminder</h3>
        <form method="post">
            <div class="form-group">
                <label>Reminder Type:</label>
                <select name="reminder_type" required>
                    <option value="rent_due">Rent Due</option>
                    <option value="lease_renewal">Lease Renewal</option>
                    <option value="inspection">Inspection</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <?php if ($_SESSION['user_type'] === 'landlord'): ?>
                <div class="form-group">
                    <label>Related To (Optional):</label>
                    <select name="related_id">
                        <option value="">-- None --</option>
                        <optgroup label="Properties">
                            <?php foreach ($properties as $property): ?>
                                <option value="prop_<?= $property['id'] ?>"><?= $property['name'] ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Tenants">
                            <?php foreach ($tenants as $tenant): ?>
                                <option value="tenant_<?= $tenant['id'] ?>"><?= $tenant['full_name'] ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label>Message:</label>
                <textarea name="message" required></textarea>
            </div>
            <div class="form-group">
                <label>Send Date/Time:</label>
                <input type="datetime-local" name="send_date" required>
            </div>
            <button type="submit" name="add_reminder">Create Reminder</button>
        </form>
        
        <h3>Your Reminders</h3>
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Message</th>
                    <th>Send Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reminders as $reminder): ?>
                <tr>
                    <td><?= ucfirst(str_replace('_', ' ', $reminder['reminder_type'])) ?></td>
                    <td><?= $reminder['message'] ?></td>
                    <td><?= $reminder['send_date'] ?></td>
                    <td class="reminder-<?= $reminder['status'] ?>"><?= ucfirst($reminder['status']) ?></td>
                    <td class="actions">
                        <a href="reminders.php?delete=<?= $reminder['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>