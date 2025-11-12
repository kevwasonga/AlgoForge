@echo off
echo Starting Library Management System...
echo.

REM Check if PHP is installed
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: PHP is not installed or not in PATH
    echo Please install PHP and add it to your system PATH
    echo Download PHP from: https://www.php.net/downloads
    pause
    exit /b 1
)

echo PHP found. Starting server...
echo.
echo Opening Library Management System in your browser...
echo URL: http://localhost:8080
echo.
echo Press Ctrl+C to stop the server
echo.

REM Start PHP server in background and open browser
start "" "http://localhost:8080"
php -S localhost:8080

pause