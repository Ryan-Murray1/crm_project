<?php
    // Use dotenv to load credentials from a .env file (recommended for security)
    // require_once 'vendor/autoload.php'; // Uncomment if using Composer

    // Fetch credentials from environment variables (if set)
    $host = getenv('DB_HOST') ?: 'localhost';
    $user = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
    $database = getenv('DB_NAME') ?: 'CRM';

    try {
        // Create a new MySQLi connection
        $conn = new mysqli($host, $user, $password, $database);

        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

    } catch (Exception $e) {
        // Log the error (you can log this to a file for debugging purposes)
        error_log($e->getMessage());
        // Display a user-friendly message
        die("Database connection error. Please try again later.");
    }
?>
