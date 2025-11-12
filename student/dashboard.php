<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

// Get student statistics
$student_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT COUNT(*) FROM requests WHERE student_id = ?");
$stmt->execute([$student_id]);
$total_requests = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM requests WHERE student_id = ? AND status = 'approved'");
$stmt->execute([$student_id]);
$approved_requests = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM requests WHERE student_id = ? AND status = 'pending'");
$stmt->execute([$student_id]);
$pending_requests = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND status = 'unread'");
$stmt->execute([$student_id]);
$unread_notifications = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Library System</title>
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
                <li><a href="notifications.php">Notifications (<?= $unread_notifications ?>)</a></li>
                <li><a href="../index.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1>Welcome, <?= $_SESSION['user_name'] ?>!</h1>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin: 2rem 0;">
            <div class="card text-center">
                <h3><?= $total_requests ?></h3>
                <p>Total Requests</p>
            </div>
            <div class="card text-center">
                <h3><?= $approved_requests ?></h3>
                <p>Approved</p>
            </div>
            <div class="card text-center">
                <h3><?= $pending_requests ?></h3>
                <p>Pending</p>
            </div>
            <div class="card text-center">
                <h3><?= $unread_notifications ?></h3>
                <p>New Notifications</p>
            </div>
        </div>

        <div class="card">
            <h2>Quick Actions</h2>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="view_books.php" class="btn">Browse Books</a>
                <a href="request_book.php" class="btn">View My Requests</a>
                <a href="notifications.php" class="btn">Check Notifications</a>
            </div>
        </div>

        <div class="card">
            <h2>Recent Requests</h2>
            <?php
            $stmt = $conn->prepare("
                SELECT r.status, r.timestamp, b.title, b.author 
                FROM requests r 
                JOIN books b ON r.book_id = b.id 
                WHERE r.student_id = ? 
                ORDER BY r.timestamp DESC 
                LIMIT 5
            ");
            $stmt->execute([$student_id]);
            $recent_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($recent_data) > 0):
            ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Request Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_data as $request): ?>
                    <tr>
                        <td><?= htmlspecialchars($request['title']) ?></td>
                        <td><?= htmlspecialchars($request['author']) ?></td>
                        <td>
                            <span style="color: <?= $request['status'] === 'approved' ? 'green' : ($request['status'] === 'rejected' ? 'red' : 'orange') ?>">
                                <?= ucfirst($request['status']) ?>
                            </span>
                        </td>
                        <td><?= date('M j, Y', strtotime($request['timestamp'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>No requests made yet. <a href="view_books.php">Browse books</a> to get started!</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>