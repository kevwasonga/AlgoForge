#!/bin/bash

echo "Starting Library Management System..."
echo

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "ERROR: PHP is not installed or not in PATH"
    echo "Please install PHP first:"
    echo "Ubuntu/Debian: sudo apt install php"
    echo "macOS: brew install php"
    read -p "Press Enter to exit..."
    exit 1
fi

echo "PHP found. Starting server..."
echo
echo "Opening Library Management System in your browser..."
echo "URL: http://localhost:8080"
echo
echo "Press Ctrl+C to stop the server"
echo

# Start PHP server and open browser
if command -v xdg-open &> /dev/null; then
    xdg-open "http://localhost:8080" &
elif command -v open &> /dev/null; then
    open "http://localhost:8080" &
fi

php -S localhost:8080