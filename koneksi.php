<?php
// Konfigurasi Database
$host = 'localhost'; // Sesuaikan dengan host database Anda
$dbname = 'kasir'; // Sesuaikan dengan nama database Anda
$username = 'root';
$password = ''; // Default XAMPP adalah kosong

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Koneksi Database Gagal: ' . $e->getMessage()]);
    exit;
}
?>
