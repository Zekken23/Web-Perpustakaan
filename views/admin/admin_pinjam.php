<?php
require 'koneksi.php';

// Logic: Ubah Status (ACC Peminjaman / Pengembalian)
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $aksi = $_GET['aksi'];

    if ($aksi == 'acc') {
        // Ubah status jadi 'dipinjam'
        $pdo->prepare("UPDATE peminjaman SET status='dipinjam' WHERE id=?")->execute([$id]);
    } 
    elseif ($aksi == 'kembali') {
        // Ubah status jadi 'kembali' DAN kembalikan stok buku
        $pdo->prepare("UPDATE peminjaman SET status='kembali', tanggal_kembali=NOW() WHERE id=?")->execute([$id]);
        
        // Ambil ID Buku dari transaksi ini
        $stmt = $pdo->prepare("SELECT buku_id FROM peminjaman WHERE id=?");
        $stmt->execute([$id]);
        $trx = $stmt->fetch();
        
        // Tambah Stok +1
        $pdo->prepare("UPDATE buku SET stok = stok + 1 WHERE id=?")->execute([$trx['buku_id']]);
    }
    
    header("Location: admin_pinjam.php");
}

// Ambil Data Peminjaman (Join 3 Tabel: Peminjaman, User, Buku)
$query = "SELECT p.*, u.nama as nama_peminjam, b.judul 
          FROM peminjaman p 
          JOIN users u ON p.user_id = u.id 
          JOIN buku b ON p.buku_id = b.id 
          ORDER BY p.id DESC";
$data = $pdo->query($query)->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Peminjaman</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h3>📋 Validasi Peminjaman</h3>
    <a href="admin_dashboard.php" class="btn btn-secondary mb-3">Kembali ke Dashboard</a>
    
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Peminjam</th>
                <th>Buku</th>
                <th>Tgl Request</th>
                <th>Tgl Kembali</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
            <tr>
                <td><?= $row['nama_peminjam'] ?></td>
                <td><?= $row['judul'] ?></td>
                <td><?= $row['tanggal_pinjam'] ?></td>
                <td><?= $row['tanggal_kembali'] ?? '-' ?></td>
                <td>
                    <span class="badge bg-<?= $row['status'] == 'pending' ? 'warning' : ($row['status'] == 'dipinjam' ? 'success' : 'secondary') ?>">
                        <?= strtoupper($row['status']) ?>
                    </span>
                </td>
                <td>
                    <?php if ($row['status'] == 'pending'): ?>
                        <a href="?aksi=acc&id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">✅ Setujui</a>
                    <?php elseif ($row['status'] == 'dipinjam'): ?>
                        <a href="?aksi=kembali&id=<?= $row['id'] ?>" class="btn btn-sm btn-info text-white">🔄 Proses Kembali</a>
                    <?php else: ?>
                        <span class="text-muted">Selesai</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>