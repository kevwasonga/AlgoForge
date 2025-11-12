<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

$student_id = $_SESSION['user_id'];

// Mark notification as read
if (isset($_GET['mark_read'])) {
    $notification_id = $_GET['mark_read'];
    $stmt = $conn->prepare("UPDATE notifications SET status = 'read' WHERE id = ? AND user_id = ?");
    $stmt->execute([$notification_id, $student_id]);
    header('Location: notifications.php');
    exit;
}

// Mark all as read
if (isset($_GET['mark_all_read'])) {
    $stmt = $conn->prepare("UPDATE notifications SET status = 'read' WHERE user_id = ?");
    $stmt->execute([$student_id]);
    header('Location: notifications.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Student Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo"><a href="../index.php" style="color: inherit; text-decoration: none;">Library System</a></div>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="view_books.php">Browse Books</a></li>
                <li><a href="request_book.php">My Requests</a></li>
                <li><a href="notifications.php">Notifications</a></li>
                <li><a href="../index.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h1>Notifications</h1>
            <a href="?mark_all_read=1" class="btn">Mark All as Read</a>
        </div>
        
        <div class="card">
            <h2>Unread Notifications</h2>
            <?php
            $stmt = $conn->prepare("
                SELECT id, message, timestamp 
                FROM notifications 
                WHERE user_id = ? AND status = 'unread' 
                ORDER BY timestamp DESC
            ");
            $stmt->execute([$student_id]);
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

        <div class="card">
            <h2>All Notifications</h2>
            <?php
            $stmt = $conn->prepare("
                SELECT message, timestamp, status 
                FROM notifications 
                WHERE user_id = ? 
                ORDER BY timestamp DESC 
                LIMIT 20
            ");
            $stmt->execute([$student_id]);
            $all_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($all_data) > 0):
            ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_data as $notification): ?>
                    <tr style="<?= $notification['status'] === 'unread' ? 'background-color: #fff3cd;' : '' ?>">
                        <td><?= htmlspecialchars($notification['message']) ?></td>
                        <td><?= date('M j, Y H:i', strtotime($notification['timestamp'])) ?></td>
                        <td>
                            <span style="color: <?= $notification['status'] === 'unread' ? 'orange' : 'green' ?>">
                                <?= ucfirst($notification['status']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>No notifications yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>