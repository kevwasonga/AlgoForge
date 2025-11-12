<?php
try {
    $db_path = __DIR__ . '/library.db';
    $conn = new PDO('sqlite:' . $db_path);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create tables if they don't exist
    $conn->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT NOT NULL CHECK(role IN ('admin', 'student'))
        );
        
        CREATE TABLE IF NOT EXISTS books (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            author TEXT NOT NULL,
            category TEXT NOT NULL,
            file_link TEXT,
            image_url TEXT,
            uploaded_by INTEGER,
            FOREIGN KEY (uploaded_by) REFERENCES users(id)
        );
    ");
    
    // Add image_url column if it doesn't exist
    try {
        $conn->exec("ALTER TABLE books ADD COLUMN image_url TEXT");
    } catch (PDOException $e) {
        // Column already exists, ignore error
    }
    
    $conn->exec("
        CREATE TABLE IF NOT EXISTS requests (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            student_id INTEGER NOT NULL,
            book_id INTEGER NOT NULL,
            status TEXT DEFAULT 'pending' CHECK(status IN ('pending', 'approved', 'rejected')),
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES users(id),
            FOREIGN KEY (book_id) REFERENCES books(id)
        );
        
        CREATE TABLE IF NOT EXISTS notifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            message TEXT NOT NULL,
            status TEXT DEFAULT 'unread' CHECK(status IN ('unread', 'read')),
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        );
    ");
    
    // Insert default admin user if not exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = 'admin@library.com'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Admin', 'admin@library.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin']);
    }
    
    // Insert sample books if none exist
    $stmt = $conn->prepare("SELECT COUNT(*) FROM books");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $sample_books = [
            ['Clean Code', 'Robert C. Martin', 'Programming', 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf', 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=300&h=400&fit=crop'],
            ['The Pragmatic Programmer', 'David Thomas', 'Programming', 'https://www.africau.edu/images/default/sample.pdf', 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=300&h=400&fit=crop'],
            ['Introduction to Algorithms', 'Thomas H. Cormen', 'Programming', 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf', 'https://images.unsplash.com/photo-1509228468518-180dd4864904?w=300&h=400&fit=crop'],
            ['Design Patterns', 'Gang of Four', 'Programming', 'https://www.africau.edu/images/default/sample.pdf', 'https://images.unsplash.com/photo-1555949963-aa79dcee981c?w=300&h=400&fit=crop'],
            ['JavaScript: The Good Parts', 'Douglas Crockford', 'Programming', 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf', 'https://images.unsplash.com/photo-1627398242454-45a1465c2479?w=300&h=400&fit=crop'],
            ['Python Crash Course', 'Eric Matthes', 'Programming', 'https://www.africau.edu/images/default/sample.pdf', 'https://images.unsplash.com/photo-1526379095098-d400fd0bf935?w=300&h=400&fit=crop'],
            ['A Brief History of Time', 'Stephen Hawking', 'Science', 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf', 'https://images.unsplash.com/photo-1446776653964-20c1d3a81b06?w=300&h=400&fit=crop'],
            ['The Origin of Species', 'Charles Darwin', 'Science', 'https://www.africau.edu/images/default/sample.pdf', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=400&fit=crop'],
            ['Cosmos', 'Carl Sagan', 'Science', 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf', 'https://images.unsplash.com/photo-1446776877081-d282a0f896e2?w=300&h=400&fit=crop'],
            ['To Kill a Mockingbird', 'Harper Lee', 'Literature', 'https://www.africau.edu/images/default/sample.pdf', 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=300&h=400&fit=crop'],
            ['1984', 'George Orwell', 'Literature', 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf', 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=300&h=400&fit=crop'],
            ['Pride and Prejudice', 'Jane Austen', 'Literature', 'https://www.africau.edu/images/default/sample.pdf', 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=300&h=400&fit=crop'],
            ['Calculus', 'James Stewart', 'Mathematics', 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf', 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?w=300&h=400&fit=crop'],
            ['Linear Algebra', 'Gilbert Strang', 'Mathematics', 'https://www.africau.edu/images/default/sample.pdf', 'https://images.unsplash.com/photo-1596495578065-6e0763fa1178?w=300&h=400&fit=crop'],
            ['The Art of War', 'Sun Tzu', 'History', 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf', 'https://images.unsplash.com/photo-1589829085413-56de8ae18c73?w=300&h=400&fit=crop'],
            ['Sapiens', 'Yuval Noah Harari', 'History', 'https://www.africau.edu/images/default/sample.pdf', 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=300&h=400&fit=crop']
        ];
        
        $stmt = $conn->prepare("INSERT INTO books (title, author, category, file_link, image_url, uploaded_by) VALUES (?, ?, ?, ?, ?, 1)");
        foreach ($sample_books as $book) {
            $stmt->execute([$book[0], $book[1], $book[2], $book[3], $book[4]]);
        }
        
        // Add test notification for admin user (ID 1)
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $stmt->execute([1, 'Welcome to the Library Management System! You can now manage books and student requests.']);
    }
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage() . " (DB Path: $db_path)");
}
?>