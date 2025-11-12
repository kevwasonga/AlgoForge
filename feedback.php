<?php
session_start();
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - Library System</title>
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
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <h2>Feedback & Support</h2>
            <p>We value your feedback! Please let us know how we can improve our library management system.</p>
            
            <form>
                <div class="form-group">
                    <label>Your Name:</label>
                    <input type="text" name="name" required>
                </div>

                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>Feedback Type:</label>
                    <select name="type" required>
                        <option value="">Select Type</option>
                        <option value="suggestion">Suggestion</option>
                        <option value="bug_report">Bug Report</option>
                        <option value="feature_request">Feature Request</option>
                        <option value="general">General Feedback</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Message:</label>
                    <textarea name="message" rows="5" required placeholder="Please share your feedback..."></textarea>
                </div>

                <button type="submit" class="btn" style="width: 100%;">Submit Feedback</button>
            </form>
        </div>

        <div class="card">
            <h3>Contact Information</h3>
            <p><strong>Email:</strong> support@library.com</p>
            <p><strong>Phone:</strong> +2570000000</p>
            <p><strong>Address:</strong> Makasembo, Raila Odinga Avenue, Kisumu</p>
        </div>
    </div>

    <?php
    if ($_POST) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $type = $_POST['type'];
        $message = $_POST['message'];
        
        // Send notification to admin (user_id = 1)
        $admin_message = "New feedback from $name ($email): $type - $message";
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (1, ?)");
        $stmt->execute([$admin_message]);
        
        echo "<script>alert('Thank you for your feedback! We will review it and get back to you soon.'); window.location.href='feedback.php';</script>";
        exit;
    }
    ?>
    
    <script>
        // Form is now handled by PHP above
    </script>
</body>
</html>