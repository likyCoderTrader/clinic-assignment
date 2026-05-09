<?php
session_start();
require_once 'includes/file_helper.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'staff';
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $users = readJsonFile('storage/users.json');
        
        foreach ($users as $u) {
            if ($u['email'] === $email) {
                $error = 'This email is already registered';
                break;
            }
        }
        
        if (!$error) {
            $newUser = [
                'id' => 'USR' . strtoupper(substr(uniqid(), -6)),
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $users[] = $newUser;
            writeJsonFile('storage/users.json', $users);
            $success = 'Your account has been created. You can now sign in.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - City Clinic</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>Create Account</h1>
            <p class="subtitle">Join the clinic system</p>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="register.php">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required autocomplete="name">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required autocomplete="email">
                </div>
                
                <div class="form-group">
                    <label for="role">Position</label>
                    <select id="role" name="role">
                        <option value="staff" <?= ($_POST['role'] ?? '') === 'staff' ? 'selected' : '' ?>>Reception Staff</option>
                        <option value="doctor" <?= ($_POST['role'] ?? '') === 'doctor' ? 'selected' : '' ?>>Doctor</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="new-password">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                </div>
                
                <button type="submit" class="btn btn-primary">Create Account</button>
            </form>
            
            <div class="login-footer">
                <p>Already have an account? <a href="index.php">Sign in</a></p>
            </div>
        </div>
    </div>
</body>
</html>