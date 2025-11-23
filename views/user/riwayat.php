<?php
// 1. Path Config: Mundur 2 langkah
require '../../config/database.php';

// 2. Cek Login User
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'user') {
    header("Location: ../../index.php"); exit;
}

$user_id = $_SESSION['user']['id'];

$query = "SELECT p.*, b.judul, b.cover 
          FROM peminjaman p 
          JOIN buku b ON p.buku_id = b.id 
          WHERE p.user_id = ? 
          ORDER BY p.id DESC"; // Urutkan dari yang paling baru
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$riwayat = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Riwayat Peminjaman</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body class="bg-light fade-in">

<div class="container mt-5 mb-5" style="max-width: 900px;">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-primary fw-bold mb-0"><i class="bi bi-clock-history"></i> Riwayat Peminjaman</h4>
            <p class="text-muted small mb-0">Daftar buku yang pernah atau sedang kamu pinjam</p>
        </div>
        <a href="user_dashboard.php" class="btn btn-secondary rounded-pill px-4">
            &larr; Kembali ke Dashboard
        </a>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            
            <?php if (count($riwayat) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th class="ps-4">No</th>
                            <th>Buku</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($riwayat as $index => $row): ?>
                        <tr>
                            <td class="ps-4 text-muted"><?= $index + 1 ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="../../assets/foto/<?= $row['cover'] ?>" class="rounded me-3 border" width="45" height="60" style="object-fit:cover;">
                                    <div>
                                        <span class="fw-bold d-block text-dark"><?= htmlspecialchars($row['judul']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <i class="bi bi-calendar-event text-muted"></i> 
                                <?= date('d M Y', strtotime($row['tanggal_pinjam'])) ?>
                            </td>
                            <td>
                                <?php if($row['tanggal_kembali']): ?>
                                    <i class="bi bi-check-circle text-success"></i> 
                                    <?= date('d M Y', strtotime($row['tanggal_kembali'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    if ($row['status'] == 'pending') {
                                        echo '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Menunggu Konfirmasi</span>';
                                    } elseif ($row['status'] == 'dipinjam') {
                                        echo '<span class="badge bg-success"><i class="bi bi-book"></i> Sedang Dipinjam</span>';
                                    } elseif ($row['status'] == 'kembali') {
                                        echo '<span class="badge bg-secondary"><i class="bi bi-archive"></i> Sudah Dikembalikan</span>';
                                    }
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <h1 class="text-muted display-4"><i class="bi bi-journal-x"></i></h1>
                    <h5 class="text-muted">Belum ada riwayat peminjaman.</h5>
                    <p class="text-secondary small">Ayo pinjam buku pertamamu sekarang!</p>
                    <a href="user_dashboard.php" class="btn btn-primary rounded-pill mt-2">Cari Buku</a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="../../assets/animate.js"></script>
</body>
</html>