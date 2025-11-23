<?php
require 'koneksi.php';
// Fitur 4: Tambah ke Favorit
if (!isset($_SESSION['user'])) { header("Location: index.php"); exit; }

$user_id = $_SESSION['user']['id'];
$buku_id = $_GET['id'];

// Cek apakah sudah ada di favorit?
$cek = $pdo->prepare("SELECT * FROM favorit WHERE user_id=? AND buku_id=?");
$cek->execute([$user_id, $buku_id]);

if ($cek->rowCount() == 0) {
    // Jika belum ada, simpan
    $stmt = $pdo->prepare("INSERT INTO favorit (user_id, buku_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $buku_id]);
    echo "<script>alert('Buku masuk koleksi Favorit!'); window.location='user_dashboard.php';</script>";
} else {
    echo "<script>alert('Buku sudah ada di favorit kamu.'); window.location='user_dashboard.php';</script>";
}
?>