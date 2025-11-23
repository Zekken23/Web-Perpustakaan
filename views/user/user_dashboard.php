<?php
require '../../config/database.php';

// 1. Cek Login User
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'user') {
    header("Location: ../../index.php"); exit;
}

$user_id = $_SESSION['user']['id'];

// --- LOGIC 1: PINJAM BUKU ---
if (isset($_POST['pinjam'])) {
    $buku_id = $_POST['buku_id'];
    $tgl_skrg = date('Y-m-d');
    
    // Cek Stok Realtime
    $stmt = $pdo->prepare("SELECT stok FROM buku WHERE id = ?");
    $stmt->execute([$buku_id]);
    $buku = $stmt->fetch();

    if ($buku['stok'] > 0) {
        // Kurangi Stok
        $pdo->prepare("UPDATE buku SET stok = stok - 1 WHERE id = ?")->execute([$buku_id]);
        // Insert Peminjaman
        $stmt = $pdo->prepare("INSERT INTO peminjaman (user_id, buku_id, tanggal_pinjam, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$user_id, $buku_id, $tgl_skrg]);
        
        $sukses = "Berhasil mengajukan peminjaman! Silakan ambil buku di petugas.";
    } else {
        $error = "Yah, stok buku ini baru saja habis!";
    }
}

// --- LOGIC 2: TAMBAH KE FAVORIT ---
if (isset($_POST['tambah_fav'])) {
    $buku_id = $_POST['buku_id'];

    // Cek apakah sudah ada di favorit?
    $cek = $pdo->prepare("SELECT id FROM favorit WHERE user_id = ? AND buku_id = ?");
    $cek->execute([$user_id, $buku_id]);

    if ($cek->rowCount() == 0) {
        // Jika belum ada, simpan
        $stmt = $pdo->prepare("INSERT INTO favorit (user_id, buku_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $buku_id]);
        $sukses = "Buku berhasil disimpan ke koleksi Favorit! ❤️";
    } else {
        $warning = "Buku ini sudah ada di koleksi favoritmu.";
    }
}

// --- LOGIC 3: CARI DATA ---
$keyword = isset($_GET['q']) ? $_GET['q'] : '';
$sql = "SELECT * FROM buku WHERE (judul LIKE ? OR penulis LIKE ? OR kategori LIKE ?) ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(["%$keyword%", "%$keyword%", "%$keyword%"]);
$daftar_buku = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard Anggota</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/style.css">
    <style>
        .card-book { transition: transform 0.2s; height: 100%; display: flex; flex-direction: column; }
        .card-book:hover { transform: translateY(-5px); }
        .card-img-top { height: 250px; object-fit: cover; }
        .card-body { flex-grow: 1; display: flex; flex-direction: column; }
        .btn-love { border: none; background: transparent; color: #dc3545; transition: 0.3s; }
        .btn-love:hover { transform: scale(1.2); }
    </style>
</head>
<body class="bg-light fade-in">

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top mb-4">
        <div class="container">
            <a class="navbar-brand text-primary fw-bold" href="#">📚 E-Perpus</a>
            <div class="d-flex align-items-center">
                <span class="text-muted me-3 d-none d-md-block">Halo, <?= htmlspecialchars($_SESSION['user']['nama']) ?></span>
                <a href="riwayat.php" class="btn btn-sm btn-info text-white me-2 rounded-pill"><i class="bi bi-clock-history"></i> Riwayat</a>
                <a href="favorit.php" class="btn btn-sm btn-danger text-white me-2 rounded-pill"><i class="bi bi-heart-fill"></i> Favorit</a>
                <a href="profile.php" class="btn btn-sm btn-secondary me-2 rounded-pill"><i class="bi bi-person-circle"></i> Profil</a>
                <a href="../../logout.php" class="btn btn-sm btn-outline-danger rounded-pill">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        
        <?php if(isset($sukses)): ?>
            <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill"></i> <?= $sukses ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if(isset($warning)): ?>
            <div class="alert alert-warning alert-dismissible fade show"><i class="bi bi-exclamation-circle-fill"></i> <?= $warning ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-x-circle-fill"></i> <?= $error ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="row justify-content-center mb-4">
            <div class="col-md-6">
                <form method="GET" class="d-flex shadow-sm rounded-pill bg-white p-1">
                    <input type="text" name="q" class="form-control border-0 rounded-pill ps-3" placeholder="Cari judul, penulis, atau kategori..." value="<?= htmlspecialchars($keyword) ?>">
                    <button class="btn btn-primary rounded-pill px-4" type="submit"><i class="bi bi-search"></i> Cari</button>
                </form>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach($daftar_buku as $buku): ?>
            <div class="col">
                <div class="card card-book shadow-sm border-0 h-100">
                    <div class="position-relative">
                        <img src="../../assets/foto/<?= $buku['cover'] ?>" class="card-img-top rounded-top" alt="Cover Buku">
                        <span class="badge bg-primary position-absolute top-0 start-0 m-2"><?= $buku['kategori'] ?></span>
                        
                        <form method="POST" class="position-absolute top-0 end-0 m-2">
                            <input type="hidden" name="buku_id" value="<?= $buku['id'] ?>">
                            <button type="submit" name="tambah_fav" class="btn btn-light btn-sm rounded-circle shadow-sm text-danger" title="Simpan ke Favorit">
                                <i class="bi bi-heart-fill"></i>
                            </button>
                        </form>
                    </div>
                    
                    <div class="card-body">
                        <h6 class="card-title fw-bold text-dark mb-1 text-truncate"><?= htmlspecialchars($buku['judul']) ?></h6>
                        <p class="text-muted small mb-2"><?= htmlspecialchars($buku['penulis']) ?> (<?= $buku['tahun_terbit'] ?>)</p>
                        
                        <div class="mb-3">
                            <?php if($buku['stok'] > 0): ?>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success">Stok: <?= $buku['stok'] ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">Stok Habis</span>
                            <?php endif; ?>
                        </div>

                        <button type="button" class="btn btn-outline-primary w-100 mt-auto rounded-pill" data-bs-toggle="modal" data-bs-target="#modalBuku<?= $buku['id'] ?>">
                            Lihat Detail
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modalBuku<?= $buku['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header border-0">
                            <h5 class="modal-title fw-bold"><?= htmlspecialchars($buku['judul']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-5">
                                    <img src="../../assets/foto/<?= $buku['cover'] ?>" class="img-fluid rounded shadow w-100">
                                </div>
                                <div class="col-md-7">
                                    <h6 class="fw-bold text-primary mt-3 mt-md-0"><i class="bi bi-file-text"></i> Sinopsis</h6>
                                    <p class="text-secondary small">
                                        <?= !empty($buku['sinopsis']) ? nl2br(htmlspecialchars($buku['sinopsis'])) : "Belum ada sinopsis untuk buku ini." ?>
                                    </p>
                                    
                                    <hr>
                                    
                                    <div class="d-flex gap-2 mt-4">
                                        <?php if($buku['stok'] > 0): ?>
                                            <form method="POST" class="flex-grow-1">
                                                <input type="hidden" name="buku_id" value="<?= $buku['id'] ?>">
                                                <button type="submit" name="pinjam" class="btn btn-primary w-100 rounded-pill py-2">
                                                    ✅ Ajukan Peminjaman
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-secondary flex-grow-1 rounded-pill py-2" disabled>Stok Habis</button>
                                        <?php endif; ?>

                                        <form method="POST">
                                            <input type="hidden" name="buku_id" value="<?= $buku['id'] ?>">
                                            <button type="submit" name="tambah_fav" class="btn btn-outline-danger rounded-pill py-2 px-3" title="Simpan ke Favorit">
                                                <i class="bi bi-heart-fill"></i>
                                            </button>
                                        </form>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if(count($daftar_buku) == 0): ?>
            <div class="text-center py-5">
                <h1 class="text-muted display-1"><i class="bi bi-search"></i></h1>
                <p class="text-muted">Buku yang kamu cari tidak ditemukan.</p>
            </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/animate.js"></script>
</body>
</html>