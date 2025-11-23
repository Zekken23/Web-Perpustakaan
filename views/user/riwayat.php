<?php
require 'koneksi.php';
$user_id = $_SESSION['user']['id'];

// Join Table untuk mengambil judul buku dari tabel buku berdasarkan id di tabel peminjaman
$sql = "SELECT p.*, b.judul FROM peminjaman p 
        JOIN buku b ON p.buku_id = b.id 
        WHERE p.user_id = ? ORDER BY p.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$riwayat = $stmt->fetchAll();
?>

<h3>Riwayat Peminjaman Anda</h3>
<table border="1" cellpadding="10">
    <tr>
        <th>Judul Buku</th>
        <th>Tanggal Pinjam</th>
        <th>Status</th>
    </tr>
    <?php foreach ($riwayat as $row): ?>
    <tr>
        <td><?= $row['judul'] ?></td>
        <td><?= $row['tanggal_pinjam'] ?></td>
        <td>
            <?php 
            if($row['status'] == 'pending') echo "🟡 Menunggu Konfirmasi";
            elseif($row['status'] == 'dipinjam') echo "🟢 Sedang Dipinjam";
            else echo "🔴 Kembali";
            ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<a href="user_dashboard.php">Kembali</a>