<?php
// actions/favorit_toggle.php
// Toggle Favorit (add / remove)

session_start();
if (empty($_SESSION['user_id'])) {
    // belum login -> redirect ke login/landing
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php'; // pastikan menyediakan $pdo

function set_flash($key, $msg) {
    $_SESSION['flash'][$key] = $msg;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        set_flash('error', 'Metode tidak diizinkan.');
        header('Location: /');
        exit;
    }

    $user_id = (int) $_SESSION['user_id'];
    $buku_id = isset($_POST['buku_id']) ? (int) $_POST['buku_id'] : 0;
    $redirect = $_SERVER['HTTP_REFERER'] ?? '/';

    if ($buku_id <= 0) {
        set_flash('error', 'ID buku tidak valid.');
        header('Location: ' . $redirect);
        exit;
    }

    // Optional: pastikan buku ada
    $stmt = $pdo->prepare("SELECT id FROM buku WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $buku_id]);
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        set_flash('error', 'Buku tidak ditemukan.');
        header('Location: ' . $redirect);
        exit;
    }

    // cek favorit existing
    $q = $pdo->prepare("SELECT id FROM favorit WHERE user_id = :user_id AND buku_id = :buku_id LIMIT 1");
    $q->execute([':user_id' => $user_id, ':buku_id' => $buku_id]);
    $exists = $q->fetch(PDO::FETCH_ASSOC);

    if ($exists) {
        // hapus favorit
        $del = $pdo->prepare("DELETE FROM favorit WHERE id = :id");
        $del->execute([':id' => $exists['id']]);
        set_flash('success', 'Buku dihapus dari Favorit.');
    } else {
        // tambah favorit
        $ins = $pdo->prepare("INSERT INTO favorit (user_id, buku_id) VALUES (:user_id, :buku_id)");
        $ins->execute([':user_id' => $user_id, ':buku_id' => $buku_id]);
        set_flash('success', 'Buku disimpan ke Favorit.');
    }

    header('Location: ' . $redirect);
    exit;

} catch (PDOException $e) {
    error_log('favorit_toggle error: ' . $e->getMessage());
    set_flash('error', 'Terjadi kesalahan sistem. Coba lagi nanti.');
    header('Location: ' . ($redirect ?? '/'));
    exit;
}
