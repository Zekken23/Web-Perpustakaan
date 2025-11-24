<?php
require '../../config/database.php';
session_start();

if (!isset($_POST['pinjam']) || !isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$buku_id = $_POST['buku_id'];

try {
    $stmt = $pdo->prepare("SELECT stok FROM buku WHERE id = ?");
    $stmt->execute([$buku_id]);
    $buku = $stmt->fetch();

    if ($buku && $buku['stok'] > 0) {
        $pdo->prepare("UPDATE buku SET stok = stok - 1 WHERE id = ?")->execute([$buku_id]);
        $insert = $pdo->prepare("INSERT INTO peminjaman (user_id, buku_id, tanggal_pinjam, status) VALUES (?, ?, NOW(), 'pending')");
        $insert->execute([$user_id, $buku_id]);

        echo "<script>
                alert('Permintaan pinjam berhasil! Tunggu konfirmasi Admin.');
                window.location = 'riwayat.php'; 
              </script>";
    } else {
        echo "<script>
                alert('Maaf, stok buku ini sedang habis.');
                window.location = 'pinjam_buku.php?id=$buku_id';
              </script>";
    }

} catch (Exception $e) {
    echo "Error Sistem: " . $e->getMessage();
}
?>