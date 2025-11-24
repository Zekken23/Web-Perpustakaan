<?php
// File: views/user/jelajahi.php
require '../../config/database.php';

// 1. Cek Sesi
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'user') {
    // header("Location: ../../index.php"); exit; 
}

$user_id = $_SESSION['user']['id'] ?? 1;

// 2. Logika Filter & Pencarian
$keyword = isset($_GET['q']) ? $_GET['q'] : '';
$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';

// Query dinamis berdasarkan filter
$sql = "SELECT * FROM buku WHERE (judul LIKE ? OR penulis LIKE ?)";
$params = ["%$keyword%", "%$keyword%"];

if (!empty($kategori)) {
    $sql .= " AND kategori = ?";
    $params[] = $kategori;
}

$sql .= " ORDER BY id DESC"; // Tampilkan buku terbaru di atas

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$semua_buku = $stmt->fetchAll();

// Dummy data jika database kosong (agar tampilan tidak rusak saat preview)
if(!$semua_buku) $semua_buku = []; 

// Daftar Kategori (Bisa diambil dari DB, tapi ini hardcode untuk UI dulu)
$list_kategori = ['Semua', 'Fiksi', 'Sains', 'Sejarah', 'Teknologi', 'Bisnis', 'Biografi', 'Komik'];
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jelajahi — E-Perpus</title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    /* --- Menggunakan Style yang SAMA dengan Dashboard --- */
    :root {
        --sidebar-width: 250px;
        --header-height: 64px;
        --primary-color: #0d6efd;
        --bg-color: #f8f9fa;
    }

    body {
        background-color: var(--bg-color);
        font-family: 'Inter', sans-serif;
        font-size: 0.925rem;
        color: #343a40;
        overflow-x: hidden;
    }

    /* HEADER & SIDEBAR (Sama persis) */
    .navbar-custom {
        height: var(--header-height);
        background: white;
        border-bottom: 1px solid #e9ecef;
        position: fixed;
        top: 0; left: 0; right: 0;
        z-index: 1030;
        padding: 0 1.5rem;
    }

    .sidebar {
        width: var(--sidebar-width);
        position: fixed;
        top: var(--header-height);
        bottom: 0; left: 0;
        z-index: 100;
        padding: 1.5rem 1rem;
        background: white;
        border-right: 1px solid #e9ecef;
        overflow-y: auto;
    }

    .nav-link {
        color: #6c757d;
        padding: 0.7rem 1rem;
        border-radius: 6px;
        font-weight: 500;
        display: flex; align-items: center; gap: 12px;
        transition: all 0.2s;
    }
    .nav-link:hover, .nav-link.active {
        background-color: #eef2ff;
        color: var(--primary-color);
    }
    .nav-link i { font-size: 1.1rem; }

    .main-wrapper {
        margin-top: var(--header-height);
        margin-left: var(--sidebar-width);
        padding: 1.5rem 2rem;
        min-height: calc(100vh - var(--header-height));
    }

    /* Search Box */
    .search-box { max-width: 480px; width: 100%; }
    .form-control-search {
        background-color: #f1f3f5; border: none; padding: 0.6rem 1rem;
    }
    .form-control-search:focus {
        background-color: white; box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
    }

    /* CARD BUKU (Sama persis) */
    .grid-books {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); /* Sedikit lebih lebar di jelajahi */
        gap: 2rem;
    }

    .book-card {
        background: transparent; position: relative; transition: transform 0.2s;
    }
    .book-card:hover { transform: translateY(-4px); z-index: 2; }
    
    .cover-img {
        width: 100%; aspect-ratio: 2/3; object-fit: cover;
        border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        margin-bottom: 12px;
    }
    .book-info h6 {
        font-size: 0.95rem; font-weight: 700; line-height: 1.4; margin-bottom: 4px;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .book-info small { font-size: 0.85rem; color: #868e96; }

    /* TAMBAHAN KHUSUS HALAMAN JELAJAHI: Category Chips */
    .category-scroll {
        display: flex; gap: 10px; overflow-x: auto; padding-bottom: 5px;
        scrollbar-width: none; /* Firefox */
    }
    .category-scroll::-webkit-scrollbar { display: none; }
    
    .cat-chip {
        padding: 0.5rem 1.2rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 500;
        text-decoration: none;
        white-space: nowrap;
        border: 1px solid #e9ecef;
        color: #495057;
        background: white;
        transition: all 0.2s;
    }
    .cat-chip:hover { background-color: #f8f9fa; border-color: #ced4da; color: #212529; }
    .cat-chip.active {
        background-color: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    @media (max-width: 992px) {
        .sidebar { display: none; }
        .main-wrapper { margin-left: 0; }
    }
  </style>
</head>
<body>

  <nav class="navbar-custom d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-4 flex-grow-1">
          <a href="dashboard.php" class="text-decoration-none d-flex align-items-center gap-2 text-primary">
            <i class="bi bi-book-half fs-4"></i>
            <span class="fw-bold fs-5 text-dark">E-Perpus</span>
          </a>

          <form action="" method="GET" class="search-box d-none d-md-block ms-4">
            <div class="input-group">
                <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control form-control-search" placeholder="Cari judul, penulis, kategori..." value="<?= htmlspecialchars($keyword) ?>">
                <?php if(!empty($kategori)): ?>
                    <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori) ?>">
                <?php endif; ?>
            </div>
          </form>
      </div>

      <div class="d-flex align-items-center gap-3">
          <a href="#" class="btn btn-light rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px"><i class="bi bi-bell"></i></a>
          <div class="vr h-50 my-auto text-muted"></div>
          <div class="dropdown">
              <a href="#" class="d-flex align-items-center text-decoration-none text-dark gap-2" data-bs-toggle="dropdown">
                  <img src="../../assets/avatar.png" class="rounded-circle" width="36" height="36" style="object-fit:cover; border:1px solid #ddd;">
              </a>
              <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                  <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item text-danger" href="../../logout.php">Logout</a></li>
              </ul>
          </div>
      </div>
  </nav>

  <aside class="sidebar custom-scrollbar">
      <small class="text-uppercase text-muted fw-bold ps-3 mb-2" style="font-size: 0.7rem; letter-spacing: 1px;">Menu Utama</small>
      <nav class="nav flex-column mb-4">
          <a href="user_dashboard.php" class="nav-link"><i class="bi bi-grid"></i> Dashboard</a>
          
          <a href="jelajahi.php" class="nav-link active"><i class="bi bi-compass"></i> Jelajahi</a>
          
          <a href="bookmark.php" class="nav-link"><i class="bi bi-bookmark"></i> Bookmark</a>
      </nav>

      <small class="text-uppercase text-muted fw-bold ps-3 mb-2" style="font-size: 0.7rem; letter-spacing: 1px;">Pribadi</small>
      <nav class="nav flex-column mb-4">
          <a href="riwayat.php" class="nav-link"><i class="bi bi-clock-history"></i> Riwayat Baca</a>
      </nav>
  </aside>

  <main class="main-wrapper">
    <div class="container-fluid p-0">
        
        <div class="mb-4">
            <h4 class="fw-bold mb-3">Jelajahi Koleksi</h4>
            
            <div class="category-scroll">
                <?php foreach($list_kategori as $kat): ?>
                    <?php 
                        $isActive = ($kat == 'Semua' && empty($kategori)) || ($kat == $kategori);
                        $link = $kat == 'Semua' ? 'jelajahi.php' : '?kategori='.$kat;
                    ?>
                    <a href="<?= $link ?>" class="cat-chip <?= $isActive ? 'active' : '' ?>">
                        <?= $kat ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if(!empty($keyword)): ?>
            <p class="text-muted mb-4">Menampilkan hasil untuk "<strong><?= htmlspecialchars($keyword) ?></strong>"</p>
        <?php endif; ?>

        <?php if(count($semua_buku) > 0): ?>
            <div class="grid-books">
                <?php foreach($semua_buku as $b): ?>
                    <a href="pinjam_buku.php?id=<?= $b['id'] ?>" class="book-card text-decoration-none d-block text-dark">
                        <div class="book-cover-wrapper">
                            <img src="../../assets/foto/<?= $b['cover'] ?>" class="cover-img" alt="cover">
                        </div>
                        <div class="book-info mt-2">
                            <h6 class="text-dark fw-bold mb-1">
                                <?= htmlspecialchars($b['judul']) ?>
                            </h6>
                            <small class="text-muted"><?= htmlspecialchars($b['penulis']) ?></small>
                            <div class="mt-2">
                                <span class="badge bg-light text-secondary border fw-normal" style="font-size: 0.7rem;"><?= htmlspecialchars($b['kategori']) ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-journal-x text-muted" style="font-size: 4rem;"></i>
                <p class="mt-3 text-muted fw-medium">Tidak ada buku ditemukan.</p>
                <a href="jelajahi.php" class="btn btn-outline-primary btn-sm">Reset Filter</a>
            </div>
        <?php endif; ?>

    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>