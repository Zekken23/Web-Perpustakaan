<?php
require 'config/database.php'; 

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        if ($user['role'] == 'admin') {
            header("Location: views/admin/admin_dashboard.php");
        } else {
            header("Location: views/user/user_dashboard.php");
        }
        exit;
    } else {
        $error = "Email atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">
    <div class="card p-4 fade-in" style="width: 400px;">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary">📚 E-Perpus</h3>
            <p class="text-muted">Silakan login untuk melanjutkan</p>
        </div>
        
        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        
        <form method="POST">
            <div class="mb-3">
                <input type="email" name="email" class="form-control py-2" placeholder="Email Address" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control py-2" placeholder="Password" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100 btn-custom">Masuk</button>
            <div class="text-center mt-3">
                <a href="register.php" class="text-decoration-none">Belum punya akun? Daftar</a>
            </div>
        </form>
    </div>
    <script src="assets/animate.js"></script>
</body>
</html>