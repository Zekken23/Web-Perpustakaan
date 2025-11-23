<?php
// 1. Path Config: Mundur 2 langkah
require '../../config/database.php';

// Cek Sesi Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../../index.php"); exit;
}

// Cek ID di URL
if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php"); exit;
}

$id = $_GET['id'];

// Ambil Data Lama
$stmt = $pdo->prepare("SELECT * FROM buku WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    echo "Data buku tidak ditemukan!"; exit;
}

// PROSES UPDATE DATA
if (isset($_POST['update'])) {
    $judul = htmlspecialchars($_POST['judul']);
    $penulis = htmlspecialchars($_POST['penulis']);
    $tahun = $_POST['tahun'];
    $kategori = $_POST['kategori'];
    $stok = $_POST['stok'];
    $sinopsis = htmlspecialchars($_POST['sinopsis']); // Tambahan Sinopsis
    
    $cover = $data['cover']; // Default pakai cover lama

    // Logika Ganti Gambar
    if (isset($_FILES['cover']['name']) && $_FILES['cover']['name'] != "") {
        $nama_file = $_FILES['cover']['name'];
        $tmp_file = $_FILES['cover']['tmp_name'];
        $ext = pathinfo($nama_file, PATHINFO_EXTENSION);
        
        // Validasi format
        if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])) {
            // Nama unik: timestamp_namafile
            $cover_baru = date('dmYHis') . '_' . rand(100,999) . '.' . $ext;
            $path = "../../assets/foto/" . $cover_baru;
            
            if (move_uploaded_file($tmp_file, $path)) {
                $cover = $cover_baru; // Update variabel cover
                
                // (Opsional) Hapus file lama jika bukan default
                if ($data['cover'] != 'default.jpg' && file_exists("../../assets/foto/" . $data['cover'])) {
                    unlink("../../assets/foto/" . $data['cover']);
                }
            }
        }
    }

    try {
        // Query Update Lengkap (termasuk Sinopsis)
        $sql = "UPDATE buku SET judul=?, penulis=?, tahun_terbit=?, kategori=?, stok=?, sinopsis=?, cover=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$judul, $penulis, $tahun, $kategori, $stok, $sinopsis, $cover, $id]);

        echo "<script>alert('Data Berhasil Diupdate!'); window.location='admin_dashboard.php';</script>";
    } catch (PDOException $e) {
        $error = "Gagal update: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Buku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body class="bg-light">

<div class="container mt-5 fade-in mb-5" style="max-width: 800px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-primary fw-bold">✏️ Edit Data Buku</h4>
        <a href="admin_dashboard.php" class="btn btn-secondary btn-sm rounded-pill px-3">&larr; Batal</a>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-4">
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Judul Buku</label>
                            <input type="text" name="judul" class="form-control" value="<?= $data['judul'] ?>" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small fw-bold">Penulis</label>
                                <input type="text" name="penulis" class="form-control" value="<?= $data['penulis'] ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small fw-bold">Tahun Terbit</label>
                                <input type="number" name="tahun" class="form-control" value="<?= $data['tahun_terbit'] ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small fw-bold">Kategori</label>
                                <select name="kategori" class="form-select" required>
                                    <option value="<?= $data['kategori'] ?>" selected><?= $data['kategori'] ?> (Saat ini)</option>
                                    <option value="IT & Komputer">IT & Komputer</option>
                                    <option value="Novel">Novel</option>
                                    <option value="Sains">Sains</option>
                                    <option value="Bisnis">Bisnis</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small fw-bold">Stok</label>
                                <input type="number" name="stok" class="form-control" value="<?= $data['stok'] ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Sinopsis</label>
                            <textarea name="sinopsis" class="form-control" rows="4" placeholder="Tulis ringkasan cerita..."><?= isset($data['sinopsis']) ? $data['sinopsis'] : '' ?></textarea>
                        </div>
                    </div>

                    <div class="col-md-4 text-center">
                        <label class="form-label text-muted small fw-bold d-block text-start">Cover Saat Ini</label>
                        <div class="border rounded p-2 mb-2 bg-white">
                            <img src="../../assets/foto/<?= $data['cover'] ?>" class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                        
                        <label class="btn btn-outline-primary btn-sm w-100 mt-2" for="uploadCover">
                            Ganti Gambar
                        </label>
                        <input type="file" name="cover" id="uploadCover" class="d-none" onchange="previewImage(event)">
                        <small class="text-muted d-block mt-2" id="namaFile">Tidak ada file baru dipilih</small>
                    </div>
                </div>

                <hr class="my-4">
                
                <div class="d-grid">
                    <button type="submit" name="update" class="btn btn-primary btn-custom fw-bold py-2">
                        💾 Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../../assets/animate.js"></script>
<script>
    // Script Sederhana untuk Preview Nama File saat dipilih
    function previewImage(event) {
        const input = event.target;
        const label = document.getElementById('namaFile');
        if (input.files && input.files[0]) {
            label.innerText = "File dipilih: " + input.files[0].name;
            label.classList.add("text-success");
            label.classList.remove("text-muted");
        }
    }
</script>
</body>
</html>