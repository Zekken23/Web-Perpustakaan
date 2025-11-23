<?php
require 'config/database.php';

// Data Admin Baru
$nama = 'Ronny Admin';
$email = 'Ronny@admin.com';
$password_plain = 'ronny766';
$role = 'admin';

// Enkripsi Password (Wajib agar bisa login)
$password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

try {
    // 1. Cek dulu apakah email ini sudah ada?
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        // Jika SUDAH ADA, kita update saja password & role-nya
        $stmt = $pdo->prepare("UPDATE users SET nama=?, password=?, role=? WHERE email=?");
        $stmt->execute([$nama, $password_hash, $role, $email]);
        echo "✅ Akun sudah ada sebelumnya. Password & Role berhasil diperbarui!";
    } else {
        // Jika BELUM ADA, kita buat baru (Insert)
        $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nama, $email, $password_hash, $role]);
        echo "✅ Akun Admin Baru BERHASIL dibuat!";
    }

    echo "<h3>Detail Login:</h3>";
    echo "Email: <b>$email</b><br>";
    echo "Password: <b>$password_plain</b><br><br>";
    echo "<a href='index.php'>👉 Klik disini untuk Login</a>";

} catch (PDOException $e) {
    echo "❌ Gagal: " . $e->getMessage();
}
?>