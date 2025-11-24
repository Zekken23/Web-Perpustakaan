<?php
require 'config/database.php';

$nama = 'Ronny Admin';
$email = 'Ronny@admin.com';
$password_plain = 'ronny766';
$role = 'admin';

$password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

try {
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        $stmt = $pdo->prepare("UPDATE users SET nama=?, password=?, role=? WHERE email=?");
        $stmt->execute([$nama, $password_hash, $role, $email]);
        echo "✅ Akun sudah ada sebelumnya. Password & Role berhasil diperbarui!";
    } else {
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