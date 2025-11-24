<?php
require '../../config/database.php';

// Cek Session
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../../index.php"); exit;
}

// --- 1. LOGIC HAPUS BUKU ---
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    // Hapus gambar lama
    $stmt_img = $pdo->prepare("SELECT cover FROM buku WHERE id = ?");
    $stmt_img->execute([$id]);
    $img = $stmt_img->fetch();
    if($img && $img['cover'] != 'default.jpg' && file_exists("../../assets/foto/".$img['cover'])){
        unlink("../../assets/foto/".$img['cover']);
    }
    // Hapus data
    $stmt = $pdo->prepare("DELETE FROM buku WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin_dashboard.php");
    exit;
}

// --- 2. LOGIC IMPORT (SEARCH MANUAL & RANDOM) ---
$import_status = "";

// Cek apakah tombol cari diklik ATAU tombol random diklik
if ((isset($_GET['keyword_google']) && !empty($_GET['keyword_google'])) || isset($_GET['random_import'])) {
    
    // Tambah durasi eksekusi maksimum jadi 5 menit
    set_time_limit(300); 

    // TENTUKAN KATA KUNCI
    if (isset($_GET['random_import'])) {
        // Daftar topik random biar variatif
        $topik_random = [
            'Teknologi', 'Sejarah Indonesia', 'Novel Best Seller', 'Bisnis & Keuangan', 
            'Sains Populer', 'Psikologi', 'Kesehatan', 'Pemrograman', 'Kuliner Nusantara', 
            'Pengembangan Diri', 'Komik', 'Biografi Tokoh', 'Pendidikan'
        ];
        // Pilih satu secara acak
        $keyword = $topik_random[array_rand($topik_random)];
        $mode_pesan = "Mode Random (Topik: <b>$keyword</b>)";
    } else {
        $keyword = $_GET['keyword_google'];
        $mode_pesan = "Hasil Pencarian: <b>$keyword</b>";
    }

    $encoded_key = urlencode($keyword);
    
    $berhasil = 0;
    $dilewati = 0;
    $total_target = 100; // Target ambil 100 buku
    $max_per_page = 40;  // Limit Google per request

    // LOOPING API (Pagination)
    for ($startIndex = 0; $startIndex < $total_target; $startIndex += $max_per_page) {
        
        $api_url = "https://www.googleapis.com/books/v1/volumes?q=$encoded_key&maxResults=$max_per_page&startIndex=$startIndex&langRestrict=id";
        
        $response = @file_get_contents($api_url);
        if ($response) {
            $json = json_decode($response, true);
            
            if (isset($json['items'])) {
                foreach ($json['items'] as $item) {
                    $info = $item['volumeInfo'];
                    
                    // Ambil Data
                    $judul    = $info['title'] ?? 'Tanpa Judul';
                    $penulis  = isset($info['authors']) ? implode(', ', $info['authors']) : 'Unknown';
                    $tahun    = isset($info['publishedDate']) ? substr($info['publishedDate'], 0, 4) : date('Y');
                    $sinopsis = isset($info['description']) ? substr($info['description'], 0, 500).'...' : '-';
                    
                    // Kategori Pintar
                    $kategori_api = isset($info['categories']) ? $info['categories'][0] : 'Umum';
                    if (stripos($kategori_api, 'Computer') !== false || stripos($kategori_api, 'Technology') !== false) $kategori = 'IT & Komputer';
                    elseif (stripos($kategori_api, 'Fiction') !== false) $kategori = 'Novel';
                    elseif (stripos($kategori_api, 'Science') !== false) $kategori = 'Sains';
                    elseif (stripos($kategori_api, 'Business') !== false) $kategori = 'Bisnis';
                    else $kategori = 'Lainnya';

                    $stok = 5; 

                    // Cek Duplikat
                    $stmt_cek = $pdo->prepare("SELECT id FROM buku WHERE judul = ?");
                    $stmt_cek->execute([$judul]);
                    
                    if ($stmt_cek->rowCount() == 0) {
                        // Download Cover
                        $cover_final = 'default.jpg';
                        if (isset($info['imageLinks']['thumbnail'])) {
                            $url_img = str_replace('http://', 'https://', $info['imageLinks']['thumbnail']);
                            $nama_cover = 'google_' . time() . '_' . rand(1000,9999) . '.jpg';
                            $path_cover = '../../assets/foto/' . $nama_cover;
                            
                            $content = @file_get_contents($url_img);
                            if ($content !== false) {
                                file_put_contents($path_cover, $content);
                                $cover_final = $nama_cover;
                            }
                        }

                        // Insert DB
                        try {
                            $stmt = $pdo->prepare("INSERT INTO buku (judul, penulis, tahun_terbit, kategori, stok, cover, sinopsis) VALUES (?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([$judul, $penulis, $tahun, $kategori, $stok, $cover_final, $sinopsis]);
                            $berhasil++;
                        } catch (Exception $e) { }
                    } else {
                        $dilewati++;
                    }
                }
            } else {
                break;
            }
        }
    }

    $import_status = "
    <div class='alert alert-success alert-dismissible fade show' role='alert'>
        <h5 class='alert-heading'><i class='bi bi-check-circle-fill'></i> Import Selesai!</h5>
        <p class='mb-0'>$mode_pesan</p>
        <hr>
        <div class='d-flex justify-content-between fw-bold'>
            <span>📥 Masuk: $berhasil Buku</span>
            <span>⏭️ Skip: $dilewati Buku</span>
        </div>
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";
}

// --- 3. HITUNG DATA HARI INI ---
try {
    $stmt_today = $pdo->query("SELECT COUNT(*) FROM buku WHERE DATE(created_at) = CURDATE()");
    $count_today = $stmt_today->fetchColumn();
} catch (Exception $e) {
    $count_today = 0;
}

// --- 4. FILTER TAMPILAN ---
$filter_mode = isset($_GET['filter']) ? $_GET['filter'] : 'all';

if ($filter_mode == 'today') {
    $stmt = $pdo->query("SELECT * FROM buku WHERE DATE(created_at) = CURDATE() ORDER BY id DESC");
    $judul_tabel = "📚 Buku Masuk Hari Ini (" . date('d M Y') . ")";
} else {
    $stmt = $pdo->query("SELECT * FROM buku ORDER BY id DESC");
    $judul_tabel = "📚 Semua Koleksi Perpustakaan";
}
$buku_lokal = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Admin Dashboard - Turbo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/style.css">
    <style>
        .bg-gradient-primary { background: linear-gradient(45deg, #4e73df, #224abe); color: white; }
        .bg-gradient-success { background: linear-gradient(45deg, #1cc88a, #13855c); color: white; }
        .bg-gradient-warning { background: linear-gradient(45deg, #f6c23e, #e0a800); color: white; }
    </style>
</head>
<body class="bg-light container mt-4 mb-5 fade-in">
    
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm border">
        <div>
            <h4 class="mb-0 fw-bold text-primary"><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</h4>
            <small class="text-muted">Mode Admin Super Efisien</small>
        </div>
        <div>
            <a href="admin_tambah.php" class="btn btn-outline-primary btn-sm me-1">+ Manual</a>
            <a href="admin_pinjam.php" class="btn btn-warning text-white btn-sm me-1"><i class="bi bi-clipboard-check"></i> Validasi</a>
            <a href="../../logout.php" class="btn btn-danger btn-sm"><i class="bi bi-power"></i> Logout</a>
        </div>
    </div>

    <!-- STATISTIK HARI INI -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm bg-gradient-success text-white overflow-hidden">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <h6 class="text-uppercase mb-1 opacity-75">Buku Ditambahkan Hari Ini</h6>
                        <h2 class="mb-0 fw-bold">+<?= $count_today ?> <span class="fs-6 fw-normal">Buku Baru</span></h2>
                    </div>
                    <div>
                        <i class="bi bi-calendar-check fs-1 opacity-50"></i>
                    </div>
                </div>
                <div class="card-footer bg-white bg-opacity-10 border-0 text-end">
                    <?php if($filter_mode != 'today'): ?>
                        <a href="?filter=today" class="text-white text-decoration-none small">Lihat Detail <i class="bi bi-arrow-right"></i></a>
                    <?php else: ?>
                        <a href="admin_dashboard.php" class="text-white text-decoration-none small"><i class="bi bi-arrow-left"></i> Kembali ke Semua Data</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- IMPORT OTOMATIS (SEARCH & RANDOM) -->
    <div class="card border-0 shadow-sm mb-5 overflow-hidden">
        <div class="card-header bg-gradient-primary border-0 py-3">
            <h5 class="mb-0 text-white"><i class="bi bi-cloud-download-fill me-2"></i>Import Massal (Max 100 Buku)</h5>
        </div>
        <div class="card-body bg-white">
            <?= $import_status ?>
            
            <form method="GET" action="" class="row g-2 mb-3 align-items-center">
                <!-- Input Pencarian Manual -->
                <div class="col-md-7">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" name="keyword_google" class="form-control" placeholder="Ketik topik spesifik (opsional)...">
                    </div>
                </div>
                
                <!-- Tombol Cari Manual -->
                <div class="col-md-2">
                    <button type="submit" class="btn btn-dark w-100 fw-bold">
                        <i class="bi bi-download me-1"></i> Cari
                    </button>
                </div>

                <!-- TOMBOL RANDOM (FITUR BARU) -->
                <div class="col-md-3">
                    <button type="submit" name="random_import" value="1" class="btn btn-warning text-white w-100 fw-bold shadow-sm">
                        <i class="bi bi-shuffle me-1"></i> 🎲 Random 100
                    </button>
                </div>
            </form>

            <div class="mt-3 text-muted small">
                <i class="bi bi-info-circle me-1"></i> 
                Klik <b>"Cari"</b> untuk topik spesifik, atau klik <b>"🎲 Random 100"</b> untuk membiarkan sistem memilihkan topik acak (Teknologi, Sejarah, Bisnis, dll) dan otomatis menarik 100 buku.
            </div>
        </div>
    </div>

    <!-- TABEL DATA -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark"><?= $judul_tabel ?></h5>
            <span class="badge bg-primary rounded-pill"><?= count($buku_lokal) ?> Data</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Cover</th>
                            <th>Info Buku</th>
                            <th>Tgl Masuk</th>
                            <th>Stok</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($buku_lokal as $row): ?>
                        <tr>
                            <td class="ps-4">
                                <?php $img_src = (strpos($row['cover'], 'http') !== false) ? $row['cover'] : "../../assets/foto/" . $row['cover']; ?>
                                <img src="<?= $img_src ?>" class="rounded border" width="45" height="60" style="object-fit:cover;">
                            </td>
                            <td>
                                <div class="fw-bold text-dark text-truncate" style="max-width: 250px;"><?= htmlspecialchars($row['judul']) ?></div>
                                <small class="text-muted"><?= $row['penulis'] ?> • <?= $row['kategori'] ?></small>
                            </td>
                            <td>
                                <?php $tgl = isset($row['created_at']) ? date('d/m H:i', strtotime($row['created_at'])) : '-'; ?>
                                <small class="text-secondary fw-bold bg-light px-2 py-1 rounded"><?= $tgl ?></small>
                            </td>
                            <td>
                                <?php if($row['stok'] > 0): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2"><?= $row['stok'] ?> Ada</span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2">Habis</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="admin_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary border-0"><i class="bi bi-pencil-square"></i></a>
                                <a href="?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('Hapus?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if(count($buku_lokal) == 0): ?>
                <div class="text-center py-5 text-muted">Belum ada data.</div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../../assets/animate.js"></script>
</body>
</html>