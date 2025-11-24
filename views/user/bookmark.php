<?php
require '../../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'user') {
}

$user_id = $_SESSION['user']['id'] ?? 1;

$sql = "SELECT buku.* FROM buku 
        JOIN koleksi_favorit ON buku.id = koleksi_favorit.buku_id 
        WHERE koleksi_favorit.user_id = ? 
        ORDER BY koleksi_favorit.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$bookmarks = $stmt->fetchAll();
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bookmark — E-Perpus</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <style>
    :root { --sidebar-width: 250px; --header-height: 64px; --bg-color: #f8f9fa; }
    body { background-color: var(--bg-color); font-family: 'Inter', sans-serif; }
    
    .sidebar {
        width: var(--sidebar-width); position: fixed; top: 0; bottom: 0; left: 0;
        padding: 1.5rem 1rem; background: white; border-right: 1px solid #e9ecef; z-index: 100;
    }
    .main-wrapper { margin-left: var(--sidebar-width); padding: 2rem; }
    .nav-link { color: #6c757d; padding: 0.7rem 1rem; border-radius: 6px; display: flex; gap: 12px; }
    .nav-link.active { background-color: #eef2ff; color: #0d6efd; font-weight: 600; }
    
    .book-card { transition: transform 0.2s; }
    .book-card:hover { transform: translateY(-5px); }
    .cover-img { width: 100%; aspect-ratio: 2/3; object-fit: cover; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    
    @media (max-width: 992px) { .sidebar { display: none; } .main-wrapper { margin-left: 0; } }
  </style>
</head>
<body>

  <aside class="sidebar">
      <h4 class="fw-bold text-primary mb-4 ps-3"><i class="bi bi-book-half"></i> E-Perpus</h4>
      <nav class="nav flex-column gap-1">
          <a href="user_dashboard.php" class="nav-link"><i class="bi bi-grid"></i> Dashboard</a>
          <a href="jelajahi.php" class="nav-link"><i class="bi bi-compass"></i> Jelajahi</a>
          <a href="bookmark.php" class="nav-link active"><i class="bi bi-bookmark-fill"></i> Bookmark</a>
          <hr>
          <a href="riwayat.php" class="nav-link"><i class="bi bi-clock-history"></i> Riwayat Baca</a>
          <a href="../../logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
      </nav>
  </aside>

  <main class="main-wrapper">
    <div class="container-fluid">
        <h3 class="fw-bold mb-4">Koleksi Favorit Kamu ❤️</h3>

        <?php if(count($bookmarks) > 0): ?>
            <div class="row row-cols-2 row-cols-md-4 row-cols-lg-5 g-4">
                <?php foreach($bookmarks as $b): ?>
                    <div class="col">
                        <a href="pinjam_buku.php?id=<?= $b['id'] ?>" class="text-decoration-none text-dark book-card d-block">
                            <img src="../../assets/foto/<?= $b['cover'] ?>" class="cover-img mb-2">
                            <h6 class="fw-bold mb-1 text-truncate"><?= htmlspecialchars($b['judul']) ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($b['penulis']) ?></small>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-heart-break fs-1"></i>
                <p class="mt-3">Belum ada buku yang difavoritkan.</p>
                <a href="jelajahi.php" class="btn btn-primary btn-sm rounded-pill px-4">Cari Buku</a>
            </div>
        <?php endif; ?>
    </div>
  </main>

</body>
</html>