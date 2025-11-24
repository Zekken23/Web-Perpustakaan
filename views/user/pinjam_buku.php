<?php
require_once __DIR__ . '/../../config/database.php'; 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: ../../index.php');
    exit;
}

$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (isset($_POST['toggle_favorite'])) {
    $checkFav = $pdo->prepare("SELECT id FROM koleksi_favorit WHERE user_id = ? AND buku_id = ?");
    $checkFav->execute([$user_id, $book_id]);
    $existingFav = $checkFav->fetch();

    if ($existingFav) {
        $del = $pdo->prepare("DELETE FROM koleksi_favorit WHERE user_id = ? AND buku_id = ?");
        $del->execute([$user_id, $book_id]);
    } else {
        $ins = $pdo->prepare("INSERT INTO koleksi_favorit (user_id, buku_id) VALUES (?, ?)");
        $ins->execute([$user_id, $book_id]);
    }

    header("Location: pinjam_buku.php?id=" . $book_id);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM buku WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $book_id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    echo "<div style='padding:20px; text-align:center;'>Buku tidak ditemukan. <a href='jelajahi.php'>Kembali</a></div>";
    exit;
}

$stmtFav = $pdo->prepare("SELECT id FROM koleksi_favorit WHERE user_id = ? AND buku_id = ?");
$stmtFav->execute([$user_id, $book_id]);
$isFavorited = $stmtFav->fetch();
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($book['judul']) ?> — Detail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
        .book-detail-card { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
        .cover-img-lg { width: 100%; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .btn-fav { border-radius: 50px; padding: 10px 24px; font-weight: 600; transition: all 0.3s; }
        .btn-fav:hover { transform: translateY(-2px); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg bg-white border-bottom mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold text-primary" href="user_dashboard.php"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
  </div>
</nav>

<div class="container py-4">
    <div class="book-detail-card p-4 p-md-5">
        <div class="row g-5">
            <div class="col-md-4 col-lg-3 text-center">
                <?php if(!empty($book['cover'])): ?>
                    <img src="../../assets/foto/<?= htmlspecialchars($book['cover']); ?>" class="cover-img-lg" alt="Cover Buku">
                <?php else: ?>
                    <div class="bg-light d-flex align-items-center justify-content-center text-muted rounded" style="height: 300px;">
                        No Cover
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-8 col-lg-9">
                <div class="mb-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                        <?= htmlspecialchars($book['kategori'] ?? 'Umum') ?>
                    </span>
                    <span class="ms-2 text-muted"><i class="bi bi-calendar3 me-1"></i> <?= htmlspecialchars($book['tahun'] ?? '-') ?></span>
                </div>

                <h1 class="fw-bold mb-2"><?= htmlspecialchars($book['judul']) ?></h1>
                <p class="text-muted fs-5 mb-4">Oleh <span class="text-dark fw-semibold"><?= htmlspecialchars($book['penulis'] ?? '-') ?></span></p>

                <div class="row mb-4">
                    <div class="col-6 col-md-3">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Stok Buku</small>
                        <div class="fs-4 fw-bold text-dark"><?= (int)$book['stok'] ?> <span class="fs-6 fw-normal text-muted">Eks</span></div>
                    </div>
                    </div>

                <div class="mb-4">
                    <h5 class="fw-bold">Sinopsis</h5>
                    <p class="text-secondary" style="line-height: 1.8;">
                        <?= nl2br(htmlspecialchars($book['deskripsi'] ?? 'Belum ada deskripsi untuk buku ini.')) ?>
                    </p>
                </div>

                <hr class="my-4">

                <div class="d-flex flex-wrap gap-3">
                    
                    <?php if ((int)$book['stok'] <= 0): ?>
                        <button class="btn btn-secondary btn-lg px-4" disabled>
                            <i class="bi bi-x-circle me-2"></i>Stok Habis
                        </button>
                    <?php else: ?>
                       <form method="post" action="proses_pinjam.php">
                            <input type="hidden" name="buku_id" value="<?= (int)$book['id'] ?>">
                            <button type="submit" name="pinjam" class="btn btn-primary btn-lg px-5 shadow-sm">
                                <i class="bi bi-book me-2"></i>Pinjam Buku
                            </button>
                        </form>
                    <?php endif; ?>

                    <form method="post">
                        <input type="hidden" name="toggle_favorite" value="1">
                        <?php if ($isFavorited): ?>
                            <button type="submit" class="btn btn-danger btn-lg btn-fav border-0 shadow-sm">
                                <i class="bi bi-heart-fill me-2"></i>Tersimpan
                            </button>
                        <?php else: ?>
                            <button type="submit" class="btn btn-outline-danger btn-lg btn-fav">
                                <i class="bi bi-heart me-2"></i>Simpan ke Favorit
                            </button>
                        <?php endif; ?>
                    </form>

                </div>

                <div class="mt-3 text-muted small">
                    <i class="bi bi-info-circle me-1"></i> 
                    Setelah klik "Pinjam", status peminjaman akan masuk ke dashboard admin untuk verifikasi.
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>