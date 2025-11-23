<?php
// 1. Path Config: Mundur 2 langkah
require '../../config/database.php';

// 2. Cek Akses Admin (Keamanan)
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../../index.php"); exit;
}

// Logic: Ubah Status (ACC Peminjaman / Pengembalian)
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $aksi = $_GET['aksi'];

    try {
        if ($aksi == 'acc') {
            // Ubah status jadi 'dipinjam'
            $pdo->prepare("UPDATE peminjaman SET status='dipinjam' WHERE id=?")->execute([$id]);
            $msg = "Peminjaman berhasil disetujui!";
        } 
        elseif ($aksi == 'kembali') {
            // Ubah status jadi 'kembali' DAN update stok
            $pdo->prepare("UPDATE peminjaman SET status='kembali', tanggal_kembali=NOW() WHERE id=?")->execute([$id]);
            
            // Ambil ID Buku dari transaksi ini untuk kembalikan stok
            $stmt = $pdo->prepare("SELECT buku_id FROM peminjaman WHERE id=?");
            $stmt->execute([$id]);
            $trx = $stmt->fetch();
            
            if ($trx) {
                $pdo->prepare("UPDATE buku SET stok = stok + 1 WHERE id=?")->execute([$trx['buku_id']]);
            }
            $msg = "Buku telah dikembalikan dan stok diperbarui!";
        }
        
        // Redirect agar URL bersih kembali
        echo "<script>alert('$msg'); window.location='admin_pinjam.php';</script>";
        
    } catch (PDOException $e) {
        echo "<script>alert('Gagal memproses data!');</script>";
    }
}

// Ambil Data Peminjaman (Join 3 Tabel: Peminjaman, User, Buku)
$query = "SELECT p.*, u.nama as nama_peminjam, b.judul, b.cover 
          FROM peminjaman p 
          JOIN users u ON p.user_id = u.id 
          JOIN buku b ON p.buku_id = b.id 
          ORDER BY p.id DESC"; // Urutkan dari yang terbaru
$data = $pdo->query($query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Validasi Peminjaman</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body class="bg-light">

<div class="container mt-5 fade-in mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-primary fw-bold mb-0">📋 Validasi Peminjaman</h4>
            <p class="text-muted small mb-0">Kelola persetujuan dan pengembalian buku</p>
        </div>
        <a href="admin_dashboard.php" class="btn btn-secondary rounded-pill px-4">
            &larr; Kembali Dashboard
        </a>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            
            <?php if (count($data) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th class="ps-4">Peminjam</th>
                            <th>Buku</th>
                            <th>Tgl Request</th>
                            <th>Tgl Kembali</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-dark">
                                <?= htmlspecialchars($row['nama_peminjam']) ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="../../assets/foto/<?= $row['cover'] ?>" class="rounded me-2" width="40" height="55" style="object-fit:cover;">
                                    <span class="small"><?= htmlspecialchars($row['judul']) ?></span>
                                </div>
                            </td>
                            <td><?= date('d M Y', strtotime($row['tanggal_pinjam'])) ?></td>
                            <td>
                                <?= $row['tanggal_kembali'] ? date('d M Y', strtotime($row['tanggal_kembali'])) : '-' ?>
                            </td>
                            <td>
                                <?php 
                                $statusClass = '';
                                $statusLabel = '';
                                if ($row['status'] == 'pending') {
                                    $statusClass = 'bg-warning text-dark';
                                    $statusLabel = '⏳ Menunggu';
                                } elseif ($row['status'] == 'dipinjam') {
                                    $statusClass = 'bg-success';
                                    $statusLabel = '📖 Sedang Dipinjam';
                                } else {
                                    $statusClass = 'bg-secondary';
                                    $statusLabel = '✅ Selesai';
                                }
                                ?>
                                <span class="badge rounded-pill <?= $statusClass ?>">
                                    <?= $statusLabel ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($row['status'] == 'pending'): ?>
                                    <a href="?aksi=acc&id=<?= $row['id'] ?>" class="btn btn-primary btn-sm rounded-pill px-3" onclick="return confirm('Setujui peminjaman ini?')">
                                        Setujui
                                    </a>
                                <?php elseif ($row['status'] == 'dipinjam'): ?>
                                    <a href="?aksi=kembali&id=<?= $row['id'] ?>" class="btn btn-info text-white btn-sm rounded-pill px-3" onclick="return confirm('Proses pengembalian buku?')">
                                        Proses Kembali
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">Done</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <h4 class="text-muted">Belum ada data peminjaman</h4>
                    <p class="text-muted small">Data request peminjaman dari user akan muncul di sini.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="../../assets/animate.js"></script>
</body>
</html>