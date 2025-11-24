<?php
require '../../config/database.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'user') {
    header("Location: dashboard.php");
    exit;
}

if (isset($_GET['id']) && isset($_GET['buku_id'])) {
    $peminjaman_id = $_GET['id'];
    $buku_id = $_GET['buku_id'];
    $user_id = $_SESSION['user']['id'];

    try {
        $check = $pdo->prepare("SELECT id FROM peminjaman WHERE id = ? AND user_id = ? AND status = 'dipinjam'");
        $check->execute([$peminjaman_id, $user_id]);
        
        if ($check->rowCount() > 0) {
            $updatePinjam = $pdo->prepare("UPDATE peminjaman SET status = 'kembali', tanggal_kembali = NOW() WHERE id = ?");
            $updatePinjam->execute([$peminjaman_id]);

            $updateStok = $pdo->prepare("UPDATE buku SET stok = stok + 1 WHERE id = ?");
            $updateStok->execute([$buku_id]);

            echo "<script>
                    alert('Buku berhasil dikembalikan! Terima kasih.');
                    window.location = 'riwayat.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Data tidak valid atau buku sudah dikembalikan.');
                    window.location = 'riwayat.php';
                  </script>";
        }

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    header("Location: riwayat.php");
}
?>