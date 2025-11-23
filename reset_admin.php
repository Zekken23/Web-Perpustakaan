<?php
// Panggil koneksi database
require 'config/database.php';

// 1. Tentukan password baru
$password_baru = "admin123";

// 2. Enkripsi password menggunakan algoritma komputer kamu
$password_hash = password_hash($password_baru, PASSWORD_BCRYPT);

try {
    // 3. Update semua user yang role-nya 'admin'
    // Menggunakan Prepared Statement agar aman
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE role = 'admin'");
    $stmt->execute([$password_hash]);

    echo "<h1>✅ SUKSES!</h1>";
    echo "Password Admin berhasil di-reset.<br>";
    echo "Email: <b>admin@perpus.com</b><br>";
    echo "Password: <b>admin123</b><br><br>";
    echo "<a href='index.php'>Klik disini untuk Login</a>";

} catch (PDOException $e) {
    echo "Gagal: " . $e->getMessage();
}
?>