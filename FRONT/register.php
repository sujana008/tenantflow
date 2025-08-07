<?php
// register.php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_type = $_POST['user_type'];
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, user_type, full_name, phone) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password, $user_type, $full_name, $phone]);
        
        header('Location: login.php');
        exit();
    } catch (PDOException $e) {
        $error = "Registration failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - TenantFlow</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 0 auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, select { width: 100%; padding: 8px; box-sizing: border-box; }
        button { background: #007bff; color: white; border: none; padding: 10px 15px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Register</h1>
    <?php if (isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
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
            <label>User Type:</label>
            <select name="user_type" required>
                <option value="landlord">Landlord</option>
                <option value="tenant">Tenant</option>
            </select>
        </div>
        <div class="form-group">
            <label>Full Name:</label>
            <input type="text" name="full_name" required>
        </div>
        <div class="form-group">
            <label>Phone:</label>
            <input type="text" name="phone">
        </div>
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login</a></p>
</body>
</html>