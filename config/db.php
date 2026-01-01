<?php
// Database connection (adjust credentials as needed)
$DB_HOST = 'localhost';
$DB_NAME = 'todo_app';
$DB_USER = 'root';
$DB_PASS = ''; // set your MySQL root password if any

try {
    $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    // Log error in production instead of echoing raw details
    error_log('Database connection failed: ' . $e->getMessage());
    die('Terjadi kesalahan pada koneksi database. Silakan coba beberapa saat lagi.');
}

return $pdo;
