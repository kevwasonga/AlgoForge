-- SQLite Schema for Library Management System

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
    uploaded_by INTEGER,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

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

-- Insert default admin user (password: 'password')
INSERT OR IGNORE INTO users (name, email, password, role) VALUES 
('Admin', 'admin@library.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample books for demo
INSERT OR IGNORE INTO books (title, author, category, file_link, uploaded_by) VALUES 
('Clean Code', 'Robert C. Martin', 'Programming', 'https://example.com/clean-code.pdf', 1),
('The Pragmatic Programmer', 'David Thomas', 'Programming', 'https://example.com/pragmatic.pdf', 1),
('Introduction to Algorithms', 'Thomas H. Cormen', 'Programming', 'https://example.com/algorithms.pdf', 1),
('Design Patterns', 'Gang of Four', 'Programming', 'https://example.com/patterns.pdf', 1),
('JavaScript: The Good Parts', 'Douglas Crockford', 'Programming', 'https://example.com/js-good-parts.pdf', 1),
('Python Crash Course', 'Eric Matthes', 'Programming', 'https://example.com/python-crash.pdf', 1),
('A Brief History of Time', 'Stephen Hawking', 'Science', 'https://example.com/brief-history.pdf', 1),
('The Origin of Species', 'Charles Darwin', 'Science', 'https://example.com/origin-species.pdf', 1),
('Cosmos', 'Carl Sagan', 'Science', 'https://example.com/cosmos.pdf', 1),
('To Kill a Mockingbird', 'Harper Lee', 'Literature', 'https://example.com/mockingbird.pdf', 1),
('1984', 'George Orwell', 'Literature', 'https://example.com/1984.pdf', 1),
('Pride and Prejudice', 'Jane Austen', 'Literature', 'https://example.com/pride-prejudice.pdf', 1),
('Calculus', 'James Stewart', 'Mathematics', 'https://example.com/calculus.pdf', 1),
('Linear Algebra', 'Gilbert Strang', 'Mathematics', 'https://example.com/linear-algebra.pdf', 1),
('The Art of War', 'Sun Tzu', 'History', 'https://example.com/art-of-war.pdf', 1),
('Sapiens', 'Yuval Noah Harari', 'History', 'https://example.com/sapiens.pdf', 1);