<?php
// documents.php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Upload document
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $document_type = $_POST['document_type'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $unit_id = $_POST['unit_id'] ?: null;
    
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file_name = time() . '_' . basename($_FILES['document']['name']);
    $file_path = $upload_dir . $file_name;
    
    if (move_uploaded_file($_FILES['document']['tmp_name'], $file_path)) {
        $pdo->query("INSERT INTO documents (user_id, unit_id, document_type, title, file_path, description) 
                    VALUES ({$_SESSION['user_id']}, $unit_id, '$document_type', '$title', '$file_path', '$description')");
    } else {
        $error = "File upload failed";
    }
}

// Delete document
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $document = $pdo->query("SELECT * FROM documents WHERE id = $id")->fetch();
    
    if ($document && ($_SESSION['user_type'] === 'landlord' || $document['user_id'] == $_SESSION['user_id'])) {
        if (file_exists($document['file_path'])) {
            unlink($document['file_path']);
        }
        $pdo->query("DELETE FROM documents WHERE id = $id");
    }
    
    header("Location: documents.php");
    exit();
}

// Get documents
if ($_SESSION['user_type'] === 'landlord') {
    $documents = $pdo->query("SELECT d.*, u.full_name, un.unit_number, p.name as property_name 
                            FROM documents d
                            LEFT JOIN users u ON d.user_id = u.id
                            LEFT JOIN units un ON d.unit_id = un.id
                            LEFT JOIN properties p ON un.property_id = p.id
                            ORDER BY d.uploaded_at DESC")->fetchAll();
} else {
    $documents = $pdo->query("SELECT d.*, un.unit_number, p.name as property_name 
                            FROM documents d
                            LEFT JOIN units un ON d.unit_id = un.id
                            LEFT JOIN properties p ON un.property_id = p.id
                            WHERE d.user_id = {$_SESSION['user_id']}
                            ORDER BY d.uploaded_at DESC")->fetchAll();
}

// Get units for dropdown
if ($_SESSION['user_type'] === 'landlord') {
    $units = $pdo->query("SELECT u.id, u.unit_number, p.name as property_name 
                         FROM units u
                         JOIN properties p ON u.property_id = p.id
                         WHERE p.owner_id = {$_SESSION['user_id']}")->fetchAll();
} else {
    $tenant = $pdo->query("SELECT * FROM tenants WHERE user_id = {$_SESSION['user_id']}")->fetch();
    $units = $pdo->query("SELECT u.id, u.unit_number, p.name as property_name 
                         FROM units u
                         JOIN properties p ON u.property_id = p.id
                         WHERE u.id = {$tenant['unit_id']}")->fetchAll();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Documents - TenantFlow</title>
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
            <?php else: ?>
                <a href="tenant-dashboard.php">Dashboard</a> | 
            <?php endif; ?>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <h2>Documents</h2>
        
        <h3>Upload New Document</h3>
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Document Type:</label>
                <select name="document_type" required>
                    <option value="lease">Lease Agreement</option>
                    <option value="invoice">Invoice</option>
                    <option value="receipt">Receipt</option>
                    <option value="policy">Policy</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label>Title:</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Description:</label>
                <textarea name="description"></textarea>
            </div>
            <div class="form-group">
                <label>Related Unit (Optional):</label>
                <select name="unit_id">
                    <option value="">-- None --</option>
                    <?php foreach ($units as $unit): ?>
                        <option value="<?= $unit['id'] ?>"><?= $unit['property_name'] ?> - <?= $unit['unit_number'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Document File:</label>
                <input type="file" name="document" required>
            </div>
            <button type="submit">Upload Document</button>
        </form>
        
        <h3>Document Library</h3>
        <table>
            <thead>
                <tr>
                    <?php if ($_SESSION['user_type'] === 'landlord'): ?>
                        <th>Uploaded By</th>
                    <?php endif; ?>
                    <th>Title</th>
                    <th>Type</th>
                    <?php if ($_SESSION['user_type'] === 'landlord'): ?>
                        <th>Property/Unit</th>
                    <?php endif; ?>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documents as $document): ?>
                <tr>
                    <?php if ($_SESSION['user_type'] === 'landlord'): ?>
                        <td><?= $document['full_name'] ?? 'System' ?></td>
                    <?php endif; ?>
                    <td><?= $document['title'] ?></td>
                    <td><?= ucfirst($document['document_type']) ?></td>
                    <?php if ($_SESSION['user_type'] === 'landlord'): ?>
                        <td>
                            <?php if ($document['unit_id']): ?>
                                <?= $document['property_name'] ?> - <?= $document['unit_number'] ?>
                            <?php else: ?>
                                --
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                    <td><?= $document['uploaded_at'] ?></td>
                    <td class="actions">
                        <a href="<?= $document['file_path'] ?>" target="_blank">View</a>
                        <a href="documents.php?delete=<?= $document['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>