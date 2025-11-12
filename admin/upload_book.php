<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$message = '';
$edit_book = null;

// Handle edit request
if (isset($_GET['edit'])) {
    $book_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$book_id]);
    $edit_book = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_POST) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $file_link = $_POST['file_link'];
    $uploaded_by = $_SESSION['user_id'];
    $image_url = '';
    
    // Handle image upload
    if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['book_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $upload_path = '..' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $new_filename;
            
            if (move_uploaded_file($_FILES['book_image']['tmp_name'], $upload_path)) {
                $image_url = 'uploads' . DIRECTORY_SEPARATOR . $new_filename;
                $message .= ' Image uploaded successfully!';
            } else {
                $message .= ' Image upload failed!';
            }
        } else {
            $message .= ' Invalid image format!';
        }
    } else if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] != 4) {
        $message .= ' Image upload error: ' . $_FILES['book_image']['error'];
    }
    
    if (isset($_POST['book_id']) && $_POST['book_id']) {
        // Update existing book
        $book_id = $_POST['book_id'];
        if ($image_url) {
            $stmt = $conn->prepare("UPDATE books SET title = ?, author = ?, category = ?, file_link = ?, image_url = ? WHERE id = ?");
            $stmt->execute([$title, $author, $category, $file_link, $image_url, $book_id]);
        } else {
            $stmt = $conn->prepare("UPDATE books SET title = ?, author = ?, category = ?, file_link = ? WHERE id = ?");
            $stmt->execute([$title, $author, $category, $file_link, $book_id]);
        }
        $message = 'Book updated successfully!';
    } else {
        // Insert new book
        $stmt = $conn->prepare("INSERT INTO books (title, author, category, file_link, image_url, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$title, $author, $category, $file_link, $image_url, $uploaded_by])) {
            $message = 'Book uploaded successfully!';
        } else {
            $message = 'Failed to upload book.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Book - Admin Panel</title>
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
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <h2><?= $edit_book ? 'Edit Book' : 'Upload New Book' ?></h2>
            
            <?php if ($message): ?>
                <div class="alert <?= strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-error' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <?php if ($edit_book): ?>
                <input type="hidden" name="book_id" value="<?= $edit_book['id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Book Title:</label>
                    <input type="text" name="title" value="<?= $edit_book ? htmlspecialchars($edit_book['title']) : '' ?>" required>
                </div>

                <div class="form-group">
                    <label>Author:</label>
                    <input type="text" name="author" value="<?= $edit_book ? htmlspecialchars($edit_book['author']) : '' ?>" required>
                </div>

                <div class="form-group">
                    <label>Category:</label>
                    <select name="category" required>
                        <option value="">Select Category</option>
                        <option value="Programming" <?= $edit_book && $edit_book['category'] === 'Programming' ? 'selected' : '' ?>>Programming</option>
                        <option value="Science" <?= $edit_book && $edit_book['category'] === 'Science' ? 'selected' : '' ?>>Science</option>
                        <option value="Literature" <?= $edit_book && $edit_book['category'] === 'Literature' ? 'selected' : '' ?>>Literature</option>
                        <option value="History" <?= $edit_book && $edit_book['category'] === 'History' ? 'selected' : '' ?>>History</option>
                        <option value="Mathematics" <?= $edit_book && $edit_book['category'] === 'Mathematics' ? 'selected' : '' ?>>Mathematics</option>
                        <option value="Technology" <?= $edit_book && $edit_book['category'] === 'Technology' ? 'selected' : '' ?>>Technology</option>
                        <option value="Other" <?= $edit_book && $edit_book['category'] === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>File Link (URL or Path):</label>
                    <input type="text" name="file_link" value="<?= $edit_book ? htmlspecialchars($edit_book['file_link']) : '' ?>" placeholder="e.g., https://example.com/book.pdf or /uploads/book.pdf">
                </div>

                <div class="form-group">
                    <label>Book Cover Image:</label>
                    <input type="file" name="book_image" accept="image/*">
                    <small style="color: #6c757d;">Optional: Upload JPG, PNG, or GIF image</small>
                </div>

                <button type="submit" class="btn" style="width: 100%;"><?= $edit_book ? 'Update Book' : 'Upload Book' ?></button>
                <?php if ($edit_book): ?>
                <a href="upload_book.php" class="btn" style="width: 100%; margin-top: 0.5rem; background: #6c757d;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <h3>All Books</h3>
            <?php
            $books = $conn->query("SELECT * FROM books ORDER BY id DESC");
            $books_data = $books->fetchAll(PDO::FETCH_ASSOC);
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
                            <a href="?edit=<?= $book['id'] ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Edit</a>
                            <a href="?delete=<?= $book['id'] ?>" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="return confirm('Delete this book?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>No books available.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php
    if (isset($_GET['delete'])) {
        $book_id = $_GET['delete'];
        $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
        $stmt->execute([$book_id]);
        echo "<script>window.location.href = 'upload_book.php';</script>";
    }
    ?>
</body>
</html>