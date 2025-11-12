<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

$message = '';

// Handle book request
if (isset($_POST['request_book'])) {
    $book_id = $_POST['book_id'];
    $student_id = $_SESSION['user_id'];
    

    
    // Check if already requested
    $stmt = $conn->prepare("SELECT id FROM requests WHERE student_id = ? AND book_id = ? AND status = 'pending'");
    $stmt->execute([$student_id, $book_id]);
    
    if ($stmt->fetchColumn()) {
        $message = 'You have already requested this book.';
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO requests (student_id, book_id) VALUES (?, ?)");
            
            if ($stmt->execute([$student_id, $book_id])) {
                // Get book and student details for admin notification
                $book_stmt = $conn->prepare("SELECT title FROM books WHERE id = ?");
                $book_stmt->execute([$book_id]);
                $book_title = $book_stmt->fetchColumn();
                
                $student_stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
                $student_stmt->execute([$student_id]);
                $student_name = $student_stmt->fetchColumn();
                
                // Send notification to admin (user_id = 1)
                $admin_message = "New book request: '$book_title' requested by $student_name";
                $admin_stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (1, ?)");
                $admin_stmt->execute([$admin_message]);
                
                $message = 'Book request submitted successfully!';
            } else {
                $message = 'Failed to submit request. Error info: ' . print_r($stmt->errorInfo(), true);
            }
        } catch (Exception $e) {
            $message = 'Exception: ' . $e->getMessage();
        }
    }
}

// Filter books
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$query = "SELECT * FROM books WHERE 1=1";
$params = [];
if ($category_filter) {
    $query .= " AND category = ?";
    $params[] = $category_filter;
}
if ($search) {
    $query .= " AND (title LIKE ? OR author LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$query .= " ORDER BY title";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$books_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = $conn->query("SELECT DISTINCT category FROM books ORDER BY category");
$categories_data = $categories->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Books - Student Panel</title>
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
        <h1>Browse Books</h1>
        
        <?php if ($message): ?>
            <div class="alert <?= strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-error' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>Search & Filter</h2>
            <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: end;">
                <div class="form-group" style="flex: 1; min-width: 200px;">
                    <label>Search Books:</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by title or author...">
                </div>
                
                <div class="form-group" style="min-width: 150px;">
                    <label>Category:</label>
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories_data as $cat): ?>
                        <option value="<?= $cat['category'] ?>" <?= $category_filter === $cat['category'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['category']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn">Filter</button>
                <a href="view_books.php" class="btn" style="background: #6c757d;">Clear</a>
            </form>
        </div>

        <div class="card">
            <h2>Available Books</h2>
            <?php if (count($books_data) > 0): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem;">
                <?php foreach ($books_data as $book): ?>
                <div class="card" style="margin: 0;">
                    <?php if ($book['image_url']): ?>
                    <img src="<?= htmlspecialchars($book['image_url']) ?>" alt="Book Cover" style="width: 100%; height: 200px; object-fit: cover; margin-bottom: 1rem; border-radius: 4px;">
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($book['title']) ?></h3>
                    <p><strong>Author:</strong> <?= htmlspecialchars($book['author']) ?></p>
                    <p><strong>Category:</strong> <?= htmlspecialchars($book['category']) ?></p>
                    
                    <?php if ($book['image_url']): ?>
                    <p><a href="<?= htmlspecialchars($book['image_url']) ?>" target="_blank" class="btn" style="padding: 0.5rem 1rem; font-size: 0.9rem;">View Book</a></p>
                    <?php endif; ?>
                    
                    <form method="POST" style="margin-top: 1rem;">
                        <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                        <button type="submit" name="request_book" class="btn" style="width: 100%;">Request Book</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p>No books found matching your criteria.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>