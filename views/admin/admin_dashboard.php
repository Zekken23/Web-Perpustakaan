<?php
// Mundur 2 folder untuk ke config
require '../../config/database.php';

// Cek Session
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../../index.php"); // Mundur ke index utama
    exit;
}

// Logic Hapus (Contoh Path)
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = $pdo->prepare("DELETE FROM buku WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin_dashboard.php");
}

$stmt = $pdo->query("SELECT * FROM buku ORDER BY id DESC");
$buku = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body class="container mt-5 fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-white rounded shadow-sm">
        <h4 class="mb-0 text-primary">Admin Dashboard</h4>
        <div>
            <a href="admin_tambah.php" class="btn btn-success btn-custom me-2">+ Tambah</a>
            <a href="admin_pinjam.php" class="btn btn-warning btn-custom me-2">Validasi Pinjam</a>
            <a href="../../logout.php" class="btn btn-outline-danger btn-custom">Logout</a>
        </div>
    </div>

    <div class="card p-4">
        <table class="table table-hover align-middle">
            <thead class="table-dark rounded">
                <tr>
                    <th>Cover</th>
                    <th>Judul</th>
                    <th>Penulis</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($buku as $row): ?>
                <tr>
                    <td><img src="../../assets/foto/<?= $row['cover'] ?>" class="rounded" width="50" height="70" style="object-fit:cover;"></td>
                    <td><?= htmlspecialchars($row['judul']) ?></td>
                    <td><?= htmlspecialchars($row['penulis']) ?></td>
                    <td><span class="badge bg-info"><?= $row['stok'] ?></span></td>
                    <td>
                        <a href="admin_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                        <a href="?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-danger">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <script src="../../assets/animate.js"></script>
</body>
</html>