<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$message = '';

// Mark notification as read
if (isset($_GET['mark_read'])) {
    $notification_id = $_GET['mark_read'];
    $admin_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("UPDATE notifications SET status = 'read' WHERE id = ? AND user_id = ?");
    $stmt->execute([$notification_id, $admin_id]);
    header('Location: notifications.php');
    exit;
}

if ($_POST) {
    $notification_message = $_POST['message'];
    $target = $_POST['target'];
    
    if ($target === 'all') {
        $students = $conn->query("SELECT id FROM users WHERE role = 'student'");
        $students_data = $students->fetchAll(PDO::FETCH_ASSOC);
        foreach ($students_data as $student) {
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $stmt->execute([$student['id'], $notification_message]);
        }
        $message = 'Notification sent to all students!';
    } else {
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        if ($stmt->execute([$target, $notification_message])) {
            $message = 'Notification sent successfully!';
        } else {
            $message = 'Failed to send notification.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo"><a href="../index.php" style="color: inherit; text-decoration: none;">Library System</a></div>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="upload_book.php">Upload Book</a></li>
                <li><a href="view_requests.php">Requests</a></li>
                <li><a href="notifications.php">Notifications</a></li>
                <li><a href="../index.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="card">
            <h2>Unread Notifications</h2>
            <?php
            $admin_id = $_SESSION['user_id'];
            $stmt = $conn->prepare("
                SELECT id, message, timestamp 
                FROM notifications 
                WHERE user_id = ? AND status = 'unread' 
                ORDER BY timestamp DESC
            ");
            $stmt->execute([$admin_id]);
            $unread_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($unread_data) > 0):
            ?>
            <div style="space-y: 1rem;">
                <?php foreach ($unread_data as $notification): ?>
                <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1;">
                            <p><?= htmlspecialchars($notification['message']) ?></p>
                            <small style="color: #6c757d;"><?= date('M j, Y H:i', strtotime($notification['timestamp'])) ?></small>
                        </div>
                        <a href="?mark_read=<?= $notification['id'] ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Mark as Read</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p>No unread notifications.</p>
            <?php endif; ?>
        </div>
        
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <h2>Send Notification</h2>
            
            <?php if ($message): ?>
                <div class="alert <?= strpos($message, 'successfully') !== false || strpos($message, 'sent to all') !== false ? 'alert-success' : 'alert-error' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Send To:</label>
                    <select name="target" required>
                        <option value="">Select Recipient</option>
                        <option value="all">All Students</option>
                        <?php
                        $students = $conn->query("SELECT id, name, email FROM users WHERE role = 'student'");
                        $students_data = $students->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($students_data as $student):
                        ?>
                        <option value="<?= $student['id'] ?>"><?= htmlspecialchars($student['name']) ?> (<?= htmlspecialchars($student['email']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Message:</label>
                    <textarea name="message" rows="4" required placeholder="Enter your notification message..."></textarea>
                </div>

                <button type="submit" class="btn" style="width: 100%;">Send Notification</button>
            </form>
        </div>

        <div class="card">
            <h3>Recent Notifications</h3>
            <?php
            $recent_notifications = $conn->query("
                SELECT n.message, n.timestamp, u.name as recipient_name 
                FROM notifications n 
                JOIN users u ON n.user_id = u.id 
                ORDER BY n.timestamp DESC 
                LIMIT 10
            ");
            $recent_data = $recent_notifications->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($recent_data) > 0):
            ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Recipient</th>
                        <th>Message</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_data as $notification): ?>
                    <tr>
                        <td><?= htmlspecialchars($notification['recipient_name']) ?></td>
                        <td><?= htmlspecialchars($notification['message']) ?></td>
                        <td><?= date('M j, Y H:i', strtotime($notification['timestamp'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>No notifications sent yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>