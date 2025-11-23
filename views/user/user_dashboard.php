<?php
require '../../config/database.php';

// 1. Cek Login User
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'user') {
    header("Location: ../../index.php"); exit;
}

$user_id = $_SESSION['user']['id'];

// 2. Logic Peminjaman (Jika tombol 'Yakin Pinjam' ditekan)
if (isset($_POST['pinjam'])) {
    $buku_id = $_POST['buku_id'];
    $tgl_skrg = date('Y-m-d');
    
    // Cek Stok dulu
    $stmt = $pdo->prepare("SELECT stok FROM buku WHERE id = ?");
    $stmt->execute([$buku_id]);
    $buku = $stmt->fetch();

    if ($buku['stok'] > 0) {
        // Kurangi Stok
        $pdo->prepare("UPDATE buku SET stok = stok - 1 WHERE id = ?")->execute([$buku_id]);
        
        // Masuk ke tabel peminjaman
        $stmt = $pdo->prepare("INSERT INTO peminjaman (user_id, buku_id, tanggal_pinjam, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$user_id, $buku_id, $tgl_skrg]);
        
        $sukses = "Berhasil mengajukan peminjaman! Silakan ambil buku di petugas.";
    } else {
        $error = "Yah, stok buku ini sedang habis!";
    }
}

// 3. Logic Search & Tampil Data
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
    <link rel="stylesheet" href="../../assets/style.css">
    <style>
        /* Agar kartu buku tingginya sama */
        .card-book { height: 100%; display: flex; flex-direction: column; }
        .card-img-top { height: 250px; object-fit: cover; }
        .card-body { flex-grow: 1; display: flex; flex-direction: column; }
        .mt-auto { margin-top: auto; }
    </style>
</head>
<body class="bg-light fade-in">

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top mb-4">
        <div class="container">
            <a class="navbar-brand text-primary fw-bold" href="#">📚 E-Perpus</a>
            <div class="d-flex align-items-center">
                <span class="text-muted me-3 d-none d-md-block">Halo, <?= htmlspecialchars($_SESSION['user']['nama']) ?></span>
                <a href="riwayat.php" class="btn btn-sm btn-info text-white me-2 rounded-pill">📜 Riwayat</a>
                <a href="profile.php" class="btn btn-sm btn-secondary me-2 rounded-pill">👤 Profil</a>
                <a href="../../logout.php" class="btn btn-sm btn-outline-danger rounded-pill">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        
        <?php if(isset($sukses)): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= $sukses ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= $error ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="row justify-content-center mb-4">
            <div class="col-md-6">
                <form method="GET" class="d-flex shadow-sm rounded-pill bg-white p-1">
                    <input type="text" name="q" class="form-control border-0 rounded-pill ps-3" placeholder="Cari judul, penulis, atau kategori..." value="<?= htmlspecialchars($keyword) ?>">
                    <button class="btn btn-primary rounded-pill px-4" type="submit">Cari</button>
                </form>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach($daftar_buku as $buku): ?>
            <div class="col">
                <div class="card card-book shadow-sm border-0 h-100">
                    <div class="position-relative">
                        <img src="../../assets/foto/<?= $buku['cover'] ?>" class="card-img-top" alt="Cover Buku">
                        <span class="badge bg-primary position-absolute top-0 end-0 m-2"><?= $buku['kategori'] ?></span>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title fw-bold text-dark mb-1"><?= htmlspecialchars($buku['judul']) ?></h6>
                        <p class="text-muted small mb-2"><?= htmlspecialchars($buku['penulis']) ?> (<?= $buku['tahun_terbit'] ?>)</p>
                        
                        <div class="mb-3">
                            <?php if($buku['stok'] > 0): ?>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success">Stok: <?= $buku['stok'] ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">Stok Habis</span>
                            <?php endif; ?>
                        </div>

                        <button type="button" class="btn btn-outline-primary w-100 mt-auto rounded-pill" data-bs-toggle="modal" data-bs-target="#modalBuku<?= $buku['id'] ?>">
                            Lihat Detail & Pinjam
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modalBuku<?= $buku['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header border-0">
                            <h5 class="modal-title fw-bold"><?= htmlspecialchars($buku['judul']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <img src="../../assets/foto/<?= $buku['cover'] ?>" class="img-fluid rounded shadow-sm w-100">
                                </div>
                                <div class="col-md-8">
                                    <h6 class="fw-bold text-primary">📝 Sinopsis</h6>
                                    <p class="text-secondary">
                                        <?= !empty($buku['sinopsis']) ? nl2br(htmlspecialchars($buku['sinopsis'])) : "Belum ada sinopsis untuk buku ini." ?>
                                    </p>
                                    
                                    <hr>
                                    
                                    <h6 class="fw-bold text-info">ℹ️ Cara Peminjaman</h6>
                                    <ol class="text-muted small">
                                        <li>Klik tombol <b>"Ajukan Peminjaman"</b> di bawah.</li>
                                        <li>Status peminjaman akan menjadi <b>Pending</b>.</li>
                                        <li>Datang ke perpustakaan dan temui petugas untuk konfirmasi.</li>
                                        <li>Buku resmi dipinjam setelah status berubah menjadi <b>Dipinjam</b>.</li>
                                    </ol>

                                    <div class="alert alert-warning small py-2">
                                        <i class="bi bi-info-circle"></i> Batas waktu peminjaman adalah 7 hari setelah dikonfirmasi.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Tutup</button>
                            
                            <?php if($buku['stok'] > 0): ?>
                                <form method="POST">
                                    <input type="hidden" name="buku_id" value="<?= $buku['id'] ?>">
                                    <button type="submit" name="pinjam" class="btn btn-primary rounded-pill px-4">
                                        ✅ Ajukan Peminjaman
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-secondary rounded-pill px-4" disabled>Stok Habis</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if(count($daftar_buku) == 0): ?>
            <div class="text-center py-5">
                <h3 class="text-muted">😢</h3>
                <p class="text-muted">Buku yang kamu cari tidak ditemukan.</p>
            </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/animate.js"></script>
</body>
</html>