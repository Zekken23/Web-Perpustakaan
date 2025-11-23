<?php
// 1. PERBAIKAN PATH: Mundur 2 folder untuk akses config
require '../../config/database.php';

// Cek sesi admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../../index.php"); exit;
}

if (isset($_POST['simpan'])) {
    $judul = htmlspecialchars($_POST['judul']);
    $penulis = htmlspecialchars($_POST['penulis']);
    $tahun = $_POST['tahun'];
    $kategori = $_POST['kategori'];
    $stok = $_POST['stok'];
    
    // 2. PERBAIKAN UPLOAD:
    // Cek apakah ada file yang diupload?
    $cover = "default.jpg"; // Gambar default jika tidak upload
    if (isset($_FILES['cover']['name']) && $_FILES['cover']['name'] != "") {
        $nama_file = $_FILES['cover']['name'];
        $tmp_file = $_FILES['cover']['tmp_name'];
        
        // Buat nama unik agar tidak bentrok
        $cover_baru = date('dmYHis') . '_' . $nama_file;
        
        // Path tujuan: Mundur 2 folder -> assets -> foto
        $path = "../../assets/foto/" . $cover_baru;
        
        if (move_uploaded_file($tmp_file, $path)) {
            $cover = $cover_baru;
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO buku (judul, penulis, tahun_terbit, kategori, stok, cover) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$judul, $penulis, $tahun, $kategori, $stok, $cover]);
        
        echo "<script>alert('Buku berhasil ditambahkan!'); window.location='admin_dashboard.php';</script>";
    } catch (PDOException $e) {
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Tambah Buku Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body class="bg-light">

<div class="container mt-5 fade-in mb-5" style="max-width: 700px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-primary fw-bold">📚 Tambah Koleksi Buku</h4>
        <a href="admin_dashboard.php" class="btn btn-secondary btn-sm rounded-pill px-3">&larr; Kembali</a>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-4">
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label text-muted small fw-bold">Judul Buku</label>
                        <input type="text" name="judul" class="form-control" placeholder="Contoh: Belajar PHP Dasar" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small fw-bold">Penulis</label>
                        <input type="text" name="penulis" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small fw-bold">Tahun Terbit</label>
                        <input type="number" name="tahun" class="form-control" placeholder="2024" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small fw-bold">Kategori</label>
                        <select name="kategori" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="IT & Komputer">IT & Komputer</option>
                            <option value="Novel">Novel</option>
                            <option value="Sains">Sains</option>
                            <option value="Bisnis">Bisnis</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small fw-bold">Stok Awal</label>
                        <input type="number" name="stok" class="form-control" value="1" min="1" required>
                    </div>

                    <div class="col-md-12 mb-4">
                        <label class="form-label text-muted small fw-bold">Cover Buku</label>
                        <input type="file" name="cover" class="form-control" accept=".jpg, .jpeg, .png">
                        <small class="text-muted">Format: JPG/PNG. Biarkan kosong jika tidak ada gambar.</small>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" name="simpan" class="btn btn-primary btn-custom py-2 fw-bold">
                        💾 Simpan Data Buku
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../../assets/animate.js"></script>
</body>
</html>