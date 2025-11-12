<?php
session_start();
require_once 'db.php';

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$message = '';

if ($_POST) {
    $action = $_POST['action'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    if ($action === 'login') {
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ? AND role = ?");
        $stmt->execute([$email, $role]);
        
        if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                
                header('Location: index.php');
                exit;
            } else {
                $message = 'Invalid credentials';
            }
        } else {
            $message = 'User not found';
        }
    } elseif ($action === 'register') {
        $name = $_POST['name'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        
        if ($stmt->execute([$name, $email, $hashed_password, $role])) {
            $message = 'Registration successful! Please login.';
        } else {
            $message = 'Registration failed. Email might already exist.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Library System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo"><a href="index.php" style="color: inherit; text-decoration: none;">Library System</a></div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php#books">Books</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="<?= $_SESSION['user_role'] === 'admin' ? 'admin/dashboard.php' : 'student/dashboard.php' ?>">Dashboard</a></li>
                    <li><a href="login.php?logout=1">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login/Register</a></li>
                <?php endif; ?>
                <li><a href="feedback.php">Feedback</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="card" style="max-width: 500px; margin: 2rem auto;">
            <h2 class="text-center">Login / Register</h2>
            
            <?php if ($message): ?>
                <div class="alert <?= strpos($message, 'successful') !== false ? 'alert-success' : 'alert-error' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="authForm">
                <div class="form-group">
                    <label>Action:</label>
                    <select name="action" id="action" onchange="toggleForm()">
                        <option value="login">Login</option>
                        <option value="register">Register</option>
                    </select>
                </div>

                <div class="form-group" id="nameGroup" style="display: none;">
                    <label>Full Name:</label>
                    <input type="text" name="name" id="name">
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
                    <label>Role:</label>
                    <select name="role" required>
                        <option value="student">Student</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <button type="submit" class="btn" style="width: 100%;">Submit</button>
            </form>
        </div>
    </div>

    <script>
        function toggleForm() {
            const action = document.getElementById('action').value;
            const nameGroup = document.getElementById('nameGroup');
            const nameInput = document.getElementById('name');
            
            if (action === 'register') {
                nameGroup.style.display = 'block';
                nameInput.required = true;
            } else {
                nameGroup.style.display = 'none';
                nameInput.required = false;
            }
        }
    </script>
</body>
</html>