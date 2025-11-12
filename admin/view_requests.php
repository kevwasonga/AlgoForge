<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Handle request approval/rejection
if (isset($_GET['action']) && isset($_GET['request_id'])) {
    $request_id = $_GET['request_id'];
    $action = $_GET['action'];
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    
    // Update request status
    $stmt = $conn->prepare("UPDATE requests SET status = ? WHERE id = ?");
    $stmt->execute([$status, $request_id]);
    
    // Get request details for notification
    $request_query = $conn->prepare("SELECT r.student_id, b.title FROM requests r JOIN books b ON r.book_id = b.id WHERE r.id = ?");
    $request_query->execute([$request_id]);
    $request_data = $request_query->fetch(PDO::FETCH_ASSOC);
    
    // Send notification to student
    $message = "Your request for '{$request_data['title']}' has been $status.";
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->execute([$request_data['student_id'], $message]);
    
    header('Location: view_requests.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Requests - Admin Panel</title>
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
        <h1>Book Checkout Requests</h1>
        

        
        <div class="card">
            <h2>Pending Requests</h2>
            <?php
            $pending_requests = $conn->query("
                SELECT r.id, r.timestamp, u.name as student_name, u.email, b.title, b.author 
                FROM requests r 
                JOIN users u ON r.student_id = u.id 
                JOIN books b ON r.book_id = b.id 
                WHERE r.status = 'pending' 
                ORDER BY r.timestamp DESC
            ");
            $pending_data = $pending_requests->fetchAll(PDO::FETCH_ASSOC);
            

            if (count($pending_data) > 0):
            ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Email</th>
                        <th>Book</th>
                        <th>Author</th>
                        <th>Request Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_data as $request): ?>
                    <tr>
                        <td><?= htmlspecialchars($request['student_name']) ?></td>
                        <td><?= htmlspecialchars($request['email']) ?></td>
                        <td><?= htmlspecialchars($request['title']) ?></td>
                        <td><?= htmlspecialchars($request['author']) ?></td>
                        <td><?= date('M j, Y', strtotime($request['timestamp'])) ?></td>
                        <td>
                            <a href="?action=approve&request_id=<?= $request['id'] ?>" class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Approve</a>
                            <a href="?action=reject&request_id=<?= $request['id'] ?>" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Reject</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>No pending requests.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>All Requests History</h2>
            <?php
            $all_requests = $conn->query("
                SELECT r.status, r.timestamp, u.name as student_name, b.title 
                FROM requests r 
                JOIN users u ON r.student_id = u.id 
                JOIN books b ON r.book_id = b.id 
                ORDER BY r.timestamp DESC 
                LIMIT 20
            ");
            $all_data = $all_requests->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($all_data) > 0):
            ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Book</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_data as $request): ?>
                    <tr>
                        <td><?= htmlspecialchars($request['student_name']) ?></td>
                        <td><?= htmlspecialchars($request['title']) ?></td>
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
            <p>No requests found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>