<?php
require 'config/database.php';

if (isset($_POST['register'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = 'user';

    try {
        $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nama, $email, $password, $role]);
        echo "<script>alert('Pendaftaran Berhasil! Silakan Login'); window.location='index.php';</script>";
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $error = "Email sudah terdaftar! Gunakan email lain.";
        } else {
            $error = "Terjadi kesalahan sistem.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - E-Perpus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">

    <div class="card shadow border-0 p-4 fade-in" style="width: 100%; max-width: 400px;">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary">📝 Buat Akun</h3>
            <p class="text-muted small">Bergabunglah dengan E-Perpus sekarang</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label text-muted small fw-bold">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control py-2" placeholder="Masukkan nama lengkap" required>
            </div>

            <div class="mb-3">
                <label class="form-label text-muted small fw-bold">Email Address</label>
                <input type="email" name="email" class="form-control py-2" placeholder="contoh@email.com" required>
            </div>

            <div class="mb-4">
                <label class="form-label text-muted small fw-bold">Password</label>
                <input type="password" name="password" class="form-control py-2" placeholder="Buat password yang aman" required>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" name="register" class="btn btn-primary btn-custom fw-bold">
                    🚀 Daftar Sekarang
                </button>
            </div>

            <hr class="my-4">

            <div class="text-center">
                <p class="text-muted small mb-0">Sudah punya akun?</p>
                <a href="index.php" class="text-decoration-none fw-bold">Login disini</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/animate.js"></script>
</body>
</html>