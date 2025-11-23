<?php
$host = 'localhost';
$db   = 'tugasweb'; 
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi Gagal: " . $e->getMessage());
}

// Memulai session secara otomatis agar tidak perlu session_start() berulang kali
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>