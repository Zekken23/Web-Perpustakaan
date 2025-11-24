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

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>