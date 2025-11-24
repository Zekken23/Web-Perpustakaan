<?php
require '../../config/database.php';

// Autentikasi sederhana
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'user') {
    // header("Location: ../../index.php"); exit; // Uncomment jika live
}

// Data Dummy untuk Preview (Hapus ini jika database sudah connect real)
// $user_id = $_SESSION['user']['id'];
// ... (Bagian PHP Anda tetap sama, saya hanya menyisipkannya kembali di bawah)

$user_id = $_SESSION['user']['id'] ?? 1; // Fallback dummy

// Fetch books logic (PHP Asli Anda)
$keyword = isset($_GET['q']) ? $_GET['q'] : '';
$sql = "SELECT * FROM buku WHERE (judul LIKE ? OR penulis LIKE ? OR kategori LIKE ?) ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(["%$keyword%","%$keyword%","%$keyword%"]);
$daftar_buku = $stmt->fetchAll();

// Jika data kosong (untuk testing tampilan), kita buat array kosong atau dummy
if(!$daftar_buku) $daftar_buku = []; 

$recently = array_slice($daftar_buku, 0, 8);
$recommended = array_slice($daftar_buku, 8, 24);

// Friends
$friendsStmt = $pdo->prepare("SELECT nama FROM users WHERE role='user' AND id != ? LIMIT 4");
$friendsStmt->execute([$user_id]);
$friends = $friendsStmt->fetchAll();
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard — E-Perpus</title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
        --sidebar-width: 250px;
        --header-height: 64px;
        --primary-color: #0d6efd;
        --bg-color: #f8f9fa;
    }

    body {
        background-color: var(--bg-color);
        font-family: 'Inter', sans-serif;
        font-size: 0.925rem; /* Ukuran font standar web app */
        color: #343a40;
        overflow-x: hidden; /* Mencegah scroll horizontal */
    }

    /* 1. HEADER (FIXED TOP) */
    .navbar-custom {
        height: var(--header-height);
        background: white;
        border-bottom: 1px solid #e9ecef;
        position: fixed;
        top: 0; left: 0; right: 0;
        z-index: 1030;
        padding: 0 1.5rem;
    }

    /* 2. SIDEBAR (FIXED LEFT) */
    .sidebar {
        width: var(--sidebar-width);
        position: fixed;
        top: var(--header-height); /* Mulai di bawah header */
        bottom: 0;
        left: 0;
        z-index: 100;
        padding: 1.5rem 1rem;
        background: white;
        border-right: 1px solid #e9ecef;
        overflow-y: auto; /* Sidebar bisa discroll sendiri */
    }

    .nav-link {
        color: #6c757d;
        padding: 0.7rem 1rem;
        border-radius: 6px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.2s;
    }
    .nav-link:hover, .nav-link.active {
        background-color: #eef2ff;
        color: var(--primary-color);
    }
    .nav-link i { font-size: 1.1rem; }

    /* 3. MAIN CONTENT (FLUID) */
    .main-wrapper {
        margin-top: var(--header-height);
        margin-left: var(--sidebar-width); /* Geser konten ke kanan selebar sidebar */
        padding: 1.5rem 2rem;
        min-height: calc(100vh - var(--header-height));
    }

    /* Search Input Styling */
    .search-box {
        max-width: 480px;
        width: 100%;
    }
    .form-control-search {
        background-color: #f1f3f5;
        border: none;
        padding: 0.6rem 1rem;
    }
    .form-control-search:focus {
        background-color: white;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
    }

    /* Card & Book Styling */
    .section-title {
        font-weight: 700;
        font-size: 1.1rem;
        color: #212529;
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Grid System untuk Buku */
    .grid-books {
        display: grid;
        /* Responsive grid: min 140px, max menyesuaikan space */
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 1.5rem;
    }

    .book-card {
        background: transparent;
        position: relative;
        transition: transform 0.2s;
    }
    .book-card:hover {
        transform: translateY(-4px);
        z-index: 2;
    }
    .cover-img {
        width: 100%;
        aspect-ratio: 2/3; /* Rasio standar buku */
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        margin-bottom: 10px;
    }
    .book-info h6 {
        font-size: 0.9rem;
        font-weight: 600;
        line-height: 1.4;
        margin-bottom: 2px;
        display: -webkit-box;
        -webkit-line-clamp: 2; 
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .book-info small {
        font-size: 0.8rem;
        color: #868e96;
    }

    /* Right Panel (Friends) */
    .right-panel-card {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 1.25rem;
    }

    /* RESPONSIF: Tablet & Mobile */
    @media (max-width: 992px) {
        .sidebar { display: none; } /* Sembunyikan sidebar di tablet */
        .main-wrapper { margin-left: 0; } /* Konten full width */
    }
  </style>
</head>
<body>

  <nav class="navbar-custom d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-4 flex-grow-1">
          <a href="#" class="text-decoration-none d-flex align-items-center gap-2 text-primary">
            <i class="bi bi-book-half fs-4"></i>
            <span class="fw-bold fs-5 text-dark">E-Perpus</span>
          </a>

          <form method="GET" class="search-box d-none d-md-block ms-4">
            <div class="input-group">
                <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control form-control-search" placeholder="Cari judul buku, penulis..." value="<?= htmlspecialchars($keyword) ?>">
            </div>
          </form>
      </div>

      <div class="d-flex align-items-center gap-3">
          <a href="#" class="btn btn-light rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px"><i class="bi bi-bell"></i></a>
          <div class="vr h-50 my-auto text-muted"></div>
          <div class="dropdown">
              <a href="#" class="d-flex align-items-center text-decoration-none text-dark gap-2" data-bs-toggle="dropdown">
                  <img src="../../assets/avatar.png" class="rounded-circle" width="36" height="36" style="object-fit:cover; border:1px solid #ddd;">
                  <div class="d-none d-sm-block text-start">
                      <div class="fw-semibold" style="font-size:0.85rem; line-height:1.2"><?= htmlspecialchars($_SESSION['user']['nama'] ?? 'Member') ?></div>
                      <div class="text-muted" style="font-size:0.7rem;">User</div>
                  </div>
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
          <a href="#" class="nav-link active"><i class="bi bi-grid"></i> Dashboard</a>
          <a href="jelajahi.php" class="nav-link"><i class="bi bi-compass"></i> Jelajahi</a>
          <a href="bookmark.php" class="nav-link"><i class="bi bi-bookmark"></i> Bookmark</a>
      </nav>

      <small class="text-uppercase text-muted fw-bold ps-3 mb-2" style="font-size: 0.7rem; letter-spacing: 1px;">Pribadi</small>
      <nav class="nav flex-column mb-4">
          <a href="riwayat.php" class="nav-link"><i class="bi bi-clock-history"></i> Riwayat Baca</a>
      </nav>

      <div class="mt-auto p-3 bg-primary bg-opacity-10 rounded-3 mx-2">
          <h6 class="fw-bold text-primary mb-1 fs-6">Premium?</h6>
          <p class="text-muted small mb-2" style="font-size:0.75rem; line-height:1.3">Akses buku eksklusif tanpa batas.</p>
          <button class="btn btn-primary btn-sm w-100 py-1" style="font-size:0.75rem">Upgrade</button>
      </div>
  </aside>

  <main class="main-wrapper">
    <div class="container-fluid p-0">
        <div class="row g-4">
            
            <div class="col-lg-9 col-12">
                
                <div class="mb-4">
                    <h4 class="fw-bold mb-1">Selamat Pagi, <?= htmlspecialchars(explode(' ', $_SESSION['user']['nama'] ?? 'Reader')[0]) ?>! 👋</h4>
                    <p class="text-muted">Ada 12 buku baru yang mungkin kamu suka hari ini.</p>
                </div>

                <section class="mb-5">
                    <div class="section-title">
                        <span>Baru Ditambahkan</span>
                        <a href="jelajahi.php" class="text-decoration-none fs-6 fw-semibold">Lihat Semua</a>
                    </div>
                   <div class="d-flex gap-3 overflow-auto pb-3" style="scrollbar-width: thin;">
                   <?php foreach($recently as $b): ?>
                    <div style="min-width: 140px; width: 140px;">
                         <a href="pinjam_buku.php?id=<?= $b['id'] ?>" class="book-card text-decoration-none d-block text-dark">
            
                    <div class="book-cover-wrapper">
                 <img src="../../assets/foto/<?= $b['cover'] ?>" class="cover-img" alt="cover">
            </div>
            
            <div class="book-info mt-2">
                <h6 class="text-dark fw-bold mb-1" style="font-size:0.9rem;">
                    <?= htmlspecialchars($b['judul']) ?>
                </h6>
                <small class="text-muted"><?= htmlspecialchars($b['penulis']) ?></small>
            </div>

        </a>
    </div>
    <?php endforeach; ?>
</div>
                </section>

                <section>
                    <div class="section-title">
                        <span>Rekomendasi Pilihan</span>
                    </div>
<div class="grid-books">
    <?php foreach($recommended as $b): ?>
        <a href="pinjam_buku.php?id=<?= $b['id'] ?>" class="book-card text-decoration-none d-block text-dark">
            
            <div class="book-cover-wrapper">
                <img src="../../assets/foto/<?= $b['cover'] ?>" class="cover-img" alt="cover">
            </div>
            
            <div class="book-info mt-2">
                <h6 class="text-dark fw-bold mb-1" style="font-size:0.9rem;">
                    <?= htmlspecialchars($b['judul']) ?>
                </h6>
                <small class="text-muted"><?= htmlspecialchars($b['penulis']) ?></small>
            </div>

        </a>
    <?php endforeach; ?>
</div>
                </section>

            </div>

            <div class="col-lg-3 col-12">
                <div class="d-flex flex-column gap-4 position-sticky" style="top: 80px;">
                    
                    <div class="right-panel-card">
                        <h6 class="fw-bold mb-3">Aktivitas Teman</h6>
                        <?php if(count($friends) > 0): ?>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach($friends as $f): ?>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="../../assets/avatar.png" class="rounded-circle" width="32" height="32">
                                    <div class="overflow-hidden">
                                        <div class="fw-bold small text-truncate"><?= htmlspecialchars($f['nama']) ?></div>
                                        <div class="text-muted" style="font-size:10px">Sedang membaca...</div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted small text-center py-2">Belum ada teman.</p>
                        <?php endif; ?>
                        <button class="btn btn-outline-primary btn-sm w-100 mt-3">Undang Teman</button>
                    </div>

                    <div class="right-panel-card bg-dark text-white border-0">
                        <h6 class="fw-bold mb-3 text-white"><i class="bi bi-trophy text-warning me-2"></i>Target Baca</h6>
                        <div class="progress mb-2" style="height: 6px; background: rgba(255,255,255,0.2);">
                            <div class="progress-bar bg-warning" style="width: 70%"></div>
                        </div>
                        <div class="d-flex justify-content-between small text-white-50">
                            <span>7 Buku</span>
                            <span>10 Target</span>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>