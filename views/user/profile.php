<?php
require '../../config/database.php';

// Cek Login
if (!isset($_SESSION['user'])) {
    header("Location: ../../index.php"); exit;
}

$user = $_SESSION['user'];
$id_user = $user['id'];

// Logika Simpan Data
if (isset($_POST['update'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $password_baru = $_POST['password'];
    
    // Ambil foto lama sebagai default jika tidak ada upload baru
    $foto_final = $user['foto']; 

    // 1. Cek Apakah Ada File Foto yang Diupload?
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $nama_file = $_FILES['foto']['name'];
        $tmp_file  = $_FILES['foto']['tmp_name'];
        $ext       = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
        $valid_ext = ['jpg', 'jpeg', 'png'];

        // Cek ekstensi file
        if (in_array($ext, $valid_ext)) {
            // Beri nama unik agar tidak bentrok (time + random number)
            $nama_file_baru = "profile_" . time() . "_" . rand(100, 999) . "." . $ext;
            $path_upload = "../../assets/foto/" . $nama_file_baru;

            // Upload file ke folder assets/foto
            if (move_uploaded_file($tmp_file, $path_upload)) {
                // Jika sukses upload, set foto_final jadi nama file baru
                // (Opsional: Hapus foto lama jika bukan default/null bisa ditambahkan di sini)
                $foto_final = $nama_file_baru;
            }
        } else {
            $error = "Format file harus JPG, JPEG, atau PNG!";
        }
    }

    // 2. Proses Update Database (Jika tidak ada error file)
    if (!isset($error)) {
        try {
            if (!empty($password_baru)) {
                // Update Nama, Password, dan Foto
                $password_hash = password_hash($password_baru, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET nama=?, password=?, foto=? WHERE id=?");
                $stmt->execute([$nama, $password_hash, $foto_final, $id_user]);
            } else {
                // Update Nama dan Foto saja
                $stmt = $pdo->prepare("UPDATE users SET nama=?, foto=? WHERE id=?");
                $stmt->execute([$nama, $foto_final, $id_user]);
            }

            // 3. Update Session agar perubahan langsung terlihat
            $_SESSION['user']['nama'] = $nama;
            $_SESSION['user']['foto'] = $foto_final;
            
            // Refresh variabel $user untuk tampilan saat ini
            $user = $_SESSION['user']; 
            $sukses = "Profil berhasil diperbarui!";
        } catch (PDOException $e) {
            $error = "Gagal update database: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body class="bg-light">

<div class="container mt-5 fade-in" style="max-width: 600px;">
    <a href="user_dashboard.php" class="text-decoration-none text-secondary mb-3 d-inline-block">&larr; Kembali ke Dashboard</a>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 pt-4 pb-2 text-center">
            
            <div class="mb-3 position-relative d-inline-block">
                <?php if (!empty($user['foto'])): ?>
                    <img src="../../assets/foto/<?= $user['foto'] ?>" class="rounded-circle shadow-sm" style="width: 120px; height: 120px; object-fit: cover;">
                <?php else: ?>
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['nama']) ?>&background=4e73df&color=fff&size=120" class="rounded-circle shadow-sm">
                <?php endif; ?>
            </div>

            <h4 class="mb-0 text-primary fw-bold">Edit Profil</h4>
        </div>
        
        <div class="card-body p-4">
            <?php if(isset($sukses)) echo "<div class='alert alert-success'>$sukses</div>"; ?>
            <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

            <form method="POST" enctype="multipart/form-data">
                
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">Ganti Foto Profil</label>
                    <input type="file" name="foto" class="form-control" accept=".jpg, .jpeg, .png">
                    <div class="form-text">Format: JPG, JPEG, PNG. Maksimal 2MB.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" value="<?= $user['nama'] ?>" required>
                </div>

                <hr class="my-4">

                <div class="mb-4">
                    <label class="form-label text-muted small fw-bold">Password Baru</label>
                    <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin ganti">
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" name="update" class="btn btn-primary btn-custom py-2">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../../assets/animate.js"></script>
</body>
</html>