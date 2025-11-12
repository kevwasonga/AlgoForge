# Flat Modern Library Management System

A lightweight, modern library management system built with PHP and SQLite featuring a clean, flat design.

## Features

### Admin Features
- Upload and manage books
- View and approve/reject student requests
- Send notifications to students
- Dashboard with statistics

### Student Features
- Browse books by category
- Search books by title/author
- Request book checkout
- View request history
- Receive notifications

## Setup Instructions

### Prerequisites
- XAMPP or WAMP server (or any PHP server)
- PHP 7.4 or higher with PDO SQLite extension
- SQLite 3.x (usually included with PHP)

### Installation Steps

1. **Install XAMPP/WAMP**
   - Download and install XAMPP from https://www.apachefriends.org/
   - Start Apache and MySQL services

2. **Setup Project**
   ```bash
   # Copy the 'library' folder to your htdocs directory
   # For XAMPP: C:\xampp\htdocs\library
   # For WAMP: C:\wamp64\www\library
   ```

3. **Database Setup**
   - The SQLite database will be created automatically when you first access the system
   - Database file: `library.db` (created in the project root)
   - Optional: You can manually run `schema_sqlite.sql` if needed

4. **Database Configuration**
   - No configuration needed! SQLite database is automatically created
   - Database file location: `library.db` in the project root
   - All tables and default admin user are created automatically

5. **Access the System**
   
   **Option A: Automated Startup (Recommended)**
   
   **Windows:**
   - Double-click `run.vbs` file (recommended) OR
   - Right-click `start.bat` → "Run as administrator" OR
   - Open Command Prompt and run `start.bat`
   
   **Linux/macOS:**
   - Double-click `start.sh` file OR
   - Right-click `start.sh` → "Run in Terminal" OR
   - Run `./start.sh` in terminal
   
   **Option B: Manual Startup**
   - Open command prompt/terminal in the library folder
   - Run: `php -S localhost:8080`
   - Open browser and go to: http://localhost:8080/
   
   **Option C: Using XAMPP/WAMP**
   - Copy folder to htdocs and go to: http://localhost/library/
   
   **Default admin login:**
   - Email: admin@library.com
   - Password: password

## Project Structure

```
library/
├── index.php              # Homepage
├── login.php              # Login/Register page
├── feedback.php           # Feedback page
├── db.php                 # Database connection
├── schema.sql             # MySQL schema (legacy)
├── schema_sqlite.sql      # SQLite schema
├── library.db             # SQLite database file (auto-created)
├── admin/                 # Admin panel files
│   ├── dashboard.php
│   ├── upload_book.php
│   ├── view_requests.php
│   └── notifications.php
├── student/               # Student panel files
│   ├── dashboard.php
│   ├── view_books.php
│   ├── request_book.php
│   └── notifications.php
└── assets/                # Static assets
    ├── css/style.css
    ├── scripts.js
    └── images/
```

## Default Login Credentials

**Admin Account:**
- Email: admin@library.com
- Password: password

**Student Account:**
- Register a new student account through the login page

## Usage

1. **Admin Workflow:**
   - Login as admin
   - Upload books via "Upload Book"
   - Monitor requests in "Requests"
   - Approve/reject requests
   - Send notifications to students

2. **Student Workflow:**
   - Register/login as student
   - Browse available books
   - Request books for checkout
   - Check notifications for approval status
   - View request history

## Automated Startup

The project includes automated startup scripts for easy deployment:

- **`run.vbs`** - Windows VBScript (double-click to run)
- **`start.bat`** - Windows batch file (right-click to run)
- **`start.sh`** - Linux/macOS shell script (double-click or `./start.sh`)
- **`INSTRUCTIONS.txt`** - Detailed startup guide for all platforms

**Features:**
- Automatically checks for PHP installation
- Starts PHP built-in server on localhost:8080
- Opens the application in your default browser
- Cross-platform compatible file paths
- No manual configuration required

## Technologies Used

- **Backend:** PHP 7.4+, SQLite
- **Frontend:** HTML5, CSS3, JavaScript
- **Design:** Flat, modern design with Poppins font
- **Security:** Prepared statements, password hashing
- **Automation:** Cross-platform startup scripts

## Color Scheme

- Background: #f8f9fa
- Primary: #007BFF
- Dark: #333
- Success: #28a745
- Danger: #dc3545

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## License

This project is open source and available under the MIT License.