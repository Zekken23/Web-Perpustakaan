<?php
require 'koneksi.php';

if (isset($_POST['register'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = 'user';

    try {
        $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nama, $email, $password, $role]);
        echo "<script>alert('Pendaftaran Berhasil! Silakan Login'); window.location='index.php';</script>";
    } catch (Exception $e) {
        $error = "Email sudah terdaftar!";
    }
}
?>
<form method="POST">
    <input type="text" name="nama" placeholder="Nama Lengkap" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit" name="register">Daftar Sekarang</button>
</form>