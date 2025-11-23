<?php
require 'koneksi.php';
move_uploaded_file($tmp, "../../assets/foto/$cover");

if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$id = $_GET['id'];

// Ambil Data Lama untuk ditampilkan di form
$stmt = $pdo->prepare("SELECT * FROM buku WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (isset($_POST['update'])) {
    $judul = $_POST['judul'];
    $penulis = $_POST['penulis'];
    $tahun = $_POST['tahun'];
    $kategori = $_POST['kategori'];
    $stok = $_POST['stok'];
    $cover = $data['cover']; // Default cover lama

    // Cek jika ada upload gambar baru
    if ($_FILES['cover']['name'] != "") {
        $cover = $_FILES['cover']['name'];
        move_uploaded_file($_FILES['cover']['tmp_name'], "uploads/$cover");
    }

    $sql = "UPDATE buku SET judul=?, penulis=?, tahun_terbit=?, kategori=?, stok=?, cover=? WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$judul, $penulis, $tahun, $kategori, $stok, $cover, $id]);

    echo "<script>alert('Data Berhasil Diupdate!'); window.location='admin_dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Buku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h3>Edit Data Buku</h3>
    <form method="POST" enctype="multipart/form-data" class="card p-4">
        <div class="mb-3">
            <label>Judul Buku</label>
            <input type="text" name="judul" class="form-control" value="<?= $data['judul'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Penulis</label>
            <input type="text" name="penulis" class="form-control" value="<?= $data['penulis'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Tahun Terbit</label>
            <input type="number" name="tahun" class="form-control" value="<?= $data['tahun_terbit'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Kategori</label>
            <input type="text" name="kategori" class="form-control" value="<?= $data['kategori'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Stok</label>
            <input type="number" name="stok" class="form-control" value="<?= $data['stok'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Cover Saat Ini: </label><br>
            <img src="uploads/<?= $data['cover'] ?>" width="100">
            <input type="file" name="cover" class="form-control mt-2">
            <small class="text-muted">Biarkan kosong jika tidak ingin mengganti gambar.</small>
        </div>
        
        <button type="submit" name="update" class="btn btn-primary">Update Data</button>
        <a href="admin_dashboard.php" class="btn btn-secondary">Batal</a>
    </form>
</body>
</html>