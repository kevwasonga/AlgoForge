<?php
session_start();
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo">Library System</div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="#books">Books</a></li>
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
        <div class="card text-center">
            <h1>Welcome to Library Management System</h1>
            <p>A modern, flat design library system for seamless book management</p>
            
            <div style="margin: 2rem 0;">
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'student'): ?>
                    <a href="student/view_books.php" class="btn">Get Started</a>
                <?php else: ?>
                    <a href="login.php" class="btn">Get Started</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="card" id="books">
            <h2>Featured Books</h2>
            <p>Discover our collection of books across various categories including Programming, Science, Literature, and more.</p>
            
            <?php
            $books = $conn->query("SELECT * FROM books ORDER BY id DESC LIMIT 12");
            $books_data = $books->fetchAll(PDO::FETCH_ASSOC);
            if (count($books_data) > 0):
            ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; margin: 2rem 0;">
                <?php foreach ($books_data as $book): ?>
                <div class="card" style="margin: 0; text-align: center;">
                    <?php if ($book['image_url']): ?>
                    <img src="<?= htmlspecialchars($book['image_url']) ?>" alt="Book Cover" style="width: 100%; height: 150px; object-fit: cover; margin-bottom: 1rem; border-radius: 4px;">
                    <?php else: ?>
                    <div style="width: 100%; height: 150px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; border-radius: 4px; color: #6c757d;">
                        No Image
                    </div>
                    <?php endif; ?>
                    <h4><?= htmlspecialchars($book['title']) ?></h4>
                    <p><strong><?= htmlspecialchars($book['author']) ?></strong></p>
                    <p style="color: #6c757d; font-size: 0.9rem;"><?= htmlspecialchars($book['category']) ?></p>
                    <?php if ($book['image_url']): ?>
                    <a href="<?= htmlspecialchars($book['image_url']) ?>" target="_blank" class="btn" style="padding: 0.5rem 1rem; font-size: 0.8rem;">View Book</a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <div style="text-align: center; margin-top: 2rem;">
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'student'): ?>
                    <a href="student/view_books.php" class="btn">Browse All Books</a>
                <?php else: ?>
                    <a href="login.php" class="btn">Browse All Books</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>