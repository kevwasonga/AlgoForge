<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

$student_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Requests - Student Panel</title>
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
        <h1>My Book Requests</h1>
        
        <div class="card">
            <h2>Pending Requests</h2>
            <?php
            $stmt = $conn->prepare("
                SELECT r.id, r.timestamp, b.title, b.author, b.category 
                FROM requests r 
                JOIN books b ON r.book_id = b.id 
                WHERE r.student_id = ? AND r.status = 'pending' 
                ORDER BY r.timestamp DESC
            ");
            $stmt->execute([$student_id]);
            $pending_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($pending_data) > 0):
            ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Request Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_data as $request): ?>
                    <tr>
                        <td><?= htmlspecialchars($request['title']) ?></td>
                        <td><?= htmlspecialchars($request['author']) ?></td>
                        <td><?= htmlspecialchars($request['category']) ?></td>
                        <td><?= date('M j, Y', strtotime($request['timestamp'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>No pending requests. <a href="view_books.php">Browse books</a> to make a request!</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Approved Requests</h2>
            <?php
            $stmt = $conn->prepare("
                SELECT r.timestamp, b.title, b.author, b.file_link 
                FROM requests r 
                JOIN books b ON r.book_id = b.id 
                WHERE r.student_id = ? AND r.status = 'approved' 
                ORDER BY r.timestamp DESC
            ");
            $stmt->execute([$student_id]);
            $approved_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($approved_data) > 0):
            ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Approved Date</th>
                        <th>Access</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($approved_data as $request): ?>
                    <tr>
                        <td><?= htmlspecialchars($request['title']) ?></td>
                        <td><?= htmlspecialchars($request['author']) ?></td>
                        <td><?= date('M j, Y', strtotime($request['timestamp'])) ?></td>
                        <td>
                            <?php if ($request['file_link']): ?>
                            <a href="<?= htmlspecialchars($request['file_link']) ?>" target="_blank" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View Book</a>
                            <?php else: ?>
                            <span style="color: #6c757d;">No link available</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>No approved requests yet.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Request History</h2>
            <?php
            $stmt = $conn->prepare("
                SELECT r.status, r.timestamp, b.title, b.author 
                FROM requests r 
                JOIN books b ON r.book_id = b.id 
                WHERE r.student_id = ? 
                ORDER BY r.timestamp DESC
            ");
            $stmt->execute([$student_id]);
            $all_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($all_data) > 0):
            ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_data as $request): ?>
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
            <p>No requests made yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>