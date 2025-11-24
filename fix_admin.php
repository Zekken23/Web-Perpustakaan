<?php
require 'config/database.php';

echo "<h3>Memulai Perbaikan Akun Admin...</h3>";

try {
    $stmt_hapus = $pdo->prepare("DELETE FROM users WHERE email = 'admin@perpus.com'");
    $stmt_hapus->execute();
    echo "✅ Akun admin lama dihapus (jika ada).<br>";

    $email = 'admin@perpus.com';
    $password_plain = 'admin123';
    $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);
    $nama = 'Super Admin';
    $role = 'admin';

    $stmt_buat = $pdo->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt_buat->execute([$nama, $email, $password_hash, $role]);

    echo "✅ Akun Admin BARU berhasil dibuat.<br><br>";
    echo "================================<br>";
    echo "Login Details:<br>";
    echo "Email: <b>admin@perpus.com</b><br>";
    echo "Password: <b>admin123</b><br>";
    echo "================================<br><br>";
    echo "<a href='index.php'>Klik disini untuk Login Admin</a>";

} catch (PDOException $e) {
    echo "❌ GAGAL: " . $e->getMessage();
}
?>