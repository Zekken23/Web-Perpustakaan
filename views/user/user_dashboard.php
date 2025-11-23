<?php
require '../../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'user') {
    header("Location: ../../index.php"); exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body class="container mt-4 fade-in">
    <nav class="d-flex justify-content-between align-items-center mb-4 py-3 px-4 bg-white rounded shadow-sm">
        <div class="d-flex align-items-center">
            <h5 class="mb-0 text-primary me-3">E-Perpus</h5>
            <span class="text-muted">| Halo, <?= $_SESSION['user']['nama'] ?></span>
        </div>
        <div>
            <a href="riwayat.php" class="btn btn-sm btn-info text-white me-2">Riwayat</a>
            <a href="profile.php" class="btn btn-sm btn-secondary me-2">Profil</a>
            <a href="../../logout.php" class="btn btn-sm btn-outline-danger">Logout</a>
        </div>
    </nav>
    
    <script src="../../assets/animate.js"></script>
</body>
</html>