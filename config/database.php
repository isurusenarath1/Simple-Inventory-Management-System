<?php
/**
 * Database Connection Configuration
 * Uses PHP PDO for secure, prepared statement-driven queries.
 */

$host = 'localhost';
$db   = 'inventory_management';
$user = 'root';
$pass = ''; // Default XAMPP MySQL password is empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // In production/assignment demo, do not show raw DB error to users, but log it or show a clean message.
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please ensure XAMPP MySQL is running and the database has been imported.");
}
?>
