<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Get statistics
$books_count = $conn->query("SELECT COUNT(*) FROM books")->fetchColumn() ?: 0;
$pending_requests = $conn->query("SELECT COUNT(*) FROM requests WHERE status = 'pending'")->fetchColumn() ?: 0;
$total_students = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn() ?: 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Library System</title>
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
                <li><a href="view_requests.php">Requests (<?= $pending_requests ?>)</a></li>
                <li><a href="notifications.php">Notifications</a></li>
                <li><a href="../index.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1>Welcome, <?= $_SESSION['user_name'] ?>!</h1>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin: 2rem 0;">
            <div class="card text-center">
                <h3><?= $books_count ?></h3>
                <p>Total Books</p>
            </div>
            <div class="card text-center">
                <h3><?= $pending_requests ?></h3>
                <p>Pending Requests</p>
            </div>
            <div class="card text-center">
                <h3><?= $total_students ?></h3>
                <p>Total Students</p>
            </div>
        </div>

        <div class="card">
            <h2>Quick Actions</h2>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="upload_book.php" class="btn">Upload New Book</a>
                <a href="view_requests.php" class="btn">View Requests</a>
                <a href="notifications.php" class="btn">Send Notification</a>
            </div>
        </div>

        <div class="card">
            <h2>Recent Books</h2>
            <?php
            $recent_books = $conn->query("SELECT * FROM books ORDER BY id DESC LIMIT 5");
            $books_data = $recent_books->fetchAll(PDO::FETCH_ASSOC);
            if (count($books_data) > 0):
            ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books_data as $book): ?>
                    <tr>
                        <td><?= htmlspecialchars($book['title']) ?></td>
                        <td><?= htmlspecialchars($book['author']) ?></td>
                        <td><?= htmlspecialchars($book['category']) ?></td>
                        <td>
                            <a href="edit_book.php?id=<?= $book['id'] ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>No books uploaded yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>