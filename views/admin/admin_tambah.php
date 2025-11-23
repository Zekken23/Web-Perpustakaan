<?php
require 'koneksi.php';
move_uploaded_file($tmp, "../../assets/foto/$cover");

if (isset($_POST['simpan'])) {
    $judul = $_POST['judul'];
    $penulis = $_POST['penulis'];
    $tahun = $_POST['tahun'];
    $kategori = $_POST['kategori'];
    $stok = $_POST['stok'];
    
    // Upload Gambar [cite: 36]
    $cover = $_FILES['cover']['name'];
    $tmp = $_FILES['cover']['tmp_name'];
    move_uploaded_file($tmp, "uploads/$cover");

    $stmt = $pdo->prepare("INSERT INTO buku (judul, penulis, tahun_terbit, kategori, stok, cover) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$judul, $penulis, $tahun, $kategori, $stok, $cover]);
    
    header("Location: admin_dashboard.php");
}
?>
<form method="POST" enctype="multipart/form-data">
    Judul: <input type="text" name="judul" required><br>
    Penulis: <input type="text" name="penulis" required><br>
    Tahun: <input type="number" name="tahun" required><br>
    Kategori: <input type="text" name="kategori" required><br>
    Stok: <input type="number" name="stok" required><br>
    Cover: <input type="file" name="cover"><br>
    <button type="submit" name="simpan">Simpan Buku</button>
</form>