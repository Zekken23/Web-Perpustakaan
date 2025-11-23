<?php
// 1. Path Config: Mundur 2 langkah
require '../../config/database.php';

// 2. Cek Login User
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'user') {
    header("Location: ../../index.php"); exit;
}

$user_id = $_SESSION['user']['id'];

// --- LOGIC HAPUS DARI FAVORIT ---
if (isset($_POST['hapus'])) {
    $id_fav = $_POST['id_fav'];
    $stmt = $pdo->prepare("DELETE FROM favorit WHERE id = ? AND user_id = ?");
    $stmt->execute([$id_fav, $user_id]);
    
    echo "<script>alert('Buku dihapus dari koleksi favorit!'); window.location='favorit.php';</script>";
}

// --- LOGIC PINJAM DARI HALAMAN FAVORIT ---
if (isset($_POST['pinjam'])) {
    $buku_id = $_POST['buku_id'];
    $tgl_skrg = date('Y-m-d');

    // Cek Stok
    $cek = $pdo->prepare("SELECT stok FROM buku WHERE id = ?");
    $cek->execute([$buku_id]);
    $stok_buku = $cek->fetchColumn();

    if ($stok_buku > 0) {
        // Kurangi Stok
        $pdo->prepare("UPDATE buku SET stok = stok - 1 WHERE id = ?")->execute([$buku_id]);
        // Insert Peminjaman
        $stmt = $pdo->prepare("INSERT INTO peminjaman (user_id, buku_id, tanggal_pinjam, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$user_id, $buku_id, $tgl_skrg]);
        
        $sukses = "Berhasil mengajukan peminjaman!";
    } else {
        $error = "Stok buku habis!";
    }
}

// --- AMBIL DATA FAVORIT (JOIN dengan tabel Buku) ---
$query = "SELECT f.id as id_fav, b.* FROM favorit f 
          JOIN buku b ON f.buku_id = b.id 
          WHERE f.user_id = ? 
          ORDER BY f.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$favorit = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Koleksi Favorit Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/style.css">
    <style>
        .card-fav { transition: transform 0.2s; }
        .card-fav:hover { transform: translateY(-5px); }
        .img-fav { height: 180px; object-fit: cover; width: 100%; }
    </style>
</head>
<body class="bg-light fade-in">

<div class="container mt-5 mb-5" style="max-width: 1000px;">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-primary fw-bold mb-0">❤️ Koleksi Favorit</h4>
            <p class="text-muted small mb-0">Simpan buku yang ingin kamu baca nanti</p>
        </div>
        <a href="user_dashboard.php" class="btn btn-secondary rounded-pill px-4">
            &larr; Kembali ke Dashboard
        </a>
    </div>

    <?php if(isset($sukses)): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= $sukses ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if(isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= $error ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <?php if (count($favorit) > 0): ?>
        <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($favorit as $item): ?>
            <div class="col">
                <div class="card card-fav shadow-sm border-0 h-100">
                    <div class="position-relative">
                        <img src="../../assets/foto/<?= $item['cover'] ?>" class="img-fav rounded-top" alt="Cover">
                        <span class="badge bg-primary position-absolute top-0 end-0 m-2"><?= $item['kategori'] ?></span>
                    </div>
                    
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title fw-bold text-dark mb-1"><?= htmlspecialchars($item['judul']) ?></h6>
                        <p class="text-muted small mb-2"><?= htmlspecialchars($item['penulis']) ?></p>
                        
                        <div class="mb-3">
                            <small class="<?= $item['stok'] > 0 ? 'text-success' : 'text-danger' ?> fw-bold">
                                <?= $item['stok'] > 0 ? '✅ Tersedia: '.$item['stok'] : '❌ Stok Habis' ?>
                            </small>
                        </div>

                        <div class="mt-auto d-grid gap-2">
                            <?php if($item['stok'] > 0): ?>
                            <form method="POST">
                                <input type="hidden" name="buku_id" value="<?= $item['id'] ?>">
                                <button type="submit" name="pinjam" class="btn btn-primary btn-sm w-100 rounded-pill" onclick="return confirm('Ajukan peminjaman buku ini?')">
                                    📖 Pinjam
                                </button>
                            </form>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-sm rounded-pill" disabled>Stok Habis</button>
                            <?php endif; ?>

                            <form method="POST">
                                <input type="hidden" name="id_fav" value="<?= $item['id_fav'] ?>">
                                <button type="submit" name="hapus" class="btn btn-outline-danger btn-sm w-100 rounded-pill" onclick="return confirm('Hapus dari favorit?')">
                                    🗑️ Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5 bg-white rounded shadow-sm">
            <h1 class="display-4">💔</h1>
            <h5 class="text-muted mt-3">Belum ada buku favorit.</h5>
            <p class="text-secondary">Jelajahi dashboard dan klik tombol simpan untuk menambah koleksi.</p>
            <a href="user_dashboard.php" class="btn btn-primary mt-2 rounded-pill px-4">Cari Buku Sekarang</a>
        </div>
    <?php endif; ?>

</div>

<script src="../../assets/animate.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>