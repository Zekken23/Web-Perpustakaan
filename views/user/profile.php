<?php
require '../../config/database.php';

// Cek Login
if (!isset($_SESSION['user'])) {
    header("Location: ../../index.php"); exit;
}

$user = $_SESSION['user'];
$id_user = $user['id'];
$sukses = "";
$error = "";

// --- 1. LOGIC UPDATE BIODATA ---
if (isset($_POST['update_profil'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $foto_final = $user['foto']; 

    // Cek Upload Foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $nama_file = $_FILES['foto']['name'];
        $tmp_file  = $_FILES['foto']['tmp_name'];
        $ext       = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
        $valid_ext = ['jpg', 'jpeg', 'png'];

        if (in_array($ext, $valid_ext)) {
            $nama_file_baru = "profile_" . time() . "_" . rand(100, 999) . "." . $ext;
            $path_upload = "../../assets/foto/" . $nama_file_baru;
            if (move_uploaded_file($tmp_file, $path_upload)) {
                $foto_final = $nama_file_baru;
            }
        } else {
            $error = "Format file harus JPG, JPEG, atau PNG!";
        }
    }

    if (empty($error)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET nama=?, foto=? WHERE id=?");
            $stmt->execute([$nama, $foto_final, $id_user]);
            
            $_SESSION['user']['nama'] = $nama;
            $_SESSION['user']['foto'] = $foto_final;
            $user = $_SESSION['user']; 
            $sukses = "Biodata berhasil diperbarui!";
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan sistem.";
        }
    }
}

// --- 2. LOGIC GANTI PASSWORD ---
if (isset($_POST['update_password'])) {
    $pass_lama  = $_POST['pass_lama'];
    $pass_baru  = $_POST['pass_baru'];
    $pass_konfirm = $_POST['pass_konfirm'];

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$id_user]);
    $data_db = $stmt->fetch();

    if (password_verify($pass_lama, $data_db['password'])) {
        if ($pass_baru === $pass_konfirm) {
            $pass_hash = password_hash($pass_baru, PASSWORD_BCRYPT);
            $stmt_update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt_update->execute([$pass_hash, $id_user]);
            $sukses = "Password berhasil diubah!";
        } else {
            $error = "Konfirmasi password tidak cocok!";
        }
    } else {
        $error = "Password lama salah!";
    }
}

// --- 3. LOGIC RIWAYAT ---
$query_pinjam = "SELECT p.*, b.judul, b.cover FROM peminjaman p JOIN buku b ON p.buku_id = b.id WHERE p.user_id = ? ORDER BY p.id DESC";
$stmt_pinjam = $pdo->prepare($query_pinjam);
$stmt_pinjam->execute([$id_user]);
$riwayat_buku = $stmt_pinjam->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Profil User - E-Perpus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/style.css">
    
    <style>
        body { background-color: #f0f2f5; }
        
        .card-profile { border: none; border-radius: 15px; background: white; overflow: hidden; }
        .profile-header-bg { height: 110px; background: linear-gradient(45deg, #4e73df, #224abe); }
        .profile-img-wrap { width: 120px; height: 120px; margin: -60px auto 10px; position: relative; }
        .profile-img { width: 100%; height: 100%; object-fit: cover; border: 4px solid white; box-shadow: 0 5px 10px rgba(0,0,0,0.1); }
        
        /* Overlay Kamera hanya muncul saat Mode Edit Aktif */
        .camera-overlay {
            position: absolute; bottom: 0; right: 0;
            background: #fff; width: 35px; height: 35px;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2); cursor: pointer;
            display: none; /* Default Hidden */
        }
        
        /* Saat mode edit aktif, class .editable ditambahkan ke body/wrapper */
        .editable .camera-overlay { display: flex; animation: fadeIn 0.3s; }
        .editable .profile-img-wrap { cursor: pointer; }

        .list-group-item.active { background-color: #4e73df; border-color: #4e73df; }
        
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.8); } to { opacity: 1; transform: scale(1); } }
    </style>
</head>
<body>

<div class="container mt-5 mb-5 fade-in">
    <div class="row">
        
        <div class="col-lg-4 mb-4">
            <div class="card card-profile shadow-sm text-center mb-3">
                <div class="profile-header-bg"></div>
                
                <div id="photoWrapper" class="profile-img-wrap rounded-circle">
                    <input type="file" id="fileInput" name="foto" form="formBiodata" class="d-none" onchange="previewImage()" accept="image/*" disabled>
                    
                    <?php $img = !empty($user['foto']) ? "../../assets/foto/".$user['foto'] : "https://ui-avatars.com/api/?name=".urlencode($user['nama'])."&background=random&color=fff"; ?>
                    <img src="<?= $img ?>" id="previewFoto" class="rounded-circle profile-img">
                    
                    <div class="camera-overlay" onclick="document.getElementById('fileInput').click()">
                        <i class="bi bi-camera-fill text-dark"></i>
                    </div>
                </div>

                <h5 class="fw-bold mb-1"><?= htmlspecialchars($user['nama']) ?></h5>
                <p class="text-muted small mb-3"><?= htmlspecialchars($user['email']) ?></p>
                
                <div class="mb-4">
                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                        <i class="bi bi-patch-check-fill me-1"></i> Akun Aktif
                    </span>
                </div>
            </div>

            <div class="list-group shadow-sm border-0 rounded-3 overflow-hidden">
                <a class="list-group-item list-group-item-action active border-0 py-3" data-bs-toggle="list" href="#biodata">
                    <i class="bi bi-person-gear me-2"></i> Biodata Diri
                </a>
                <a class="list-group-item list-group-item-action border-0 py-3" data-bs-toggle="list" href="#security">
                    <i class="bi bi-shield-lock me-2"></i> Password & Keamanan
                </a>
                <a class="list-group-item list-group-item-action border-0 py-3" data-bs-toggle="list" href="#history">
                    <i class="bi bi-clock-history me-2"></i> Riwayat Peminjaman
                </a>
                <a href="user_dashboard.php" class="list-group-item list-group-item-action border-0 py-3 text-danger">
                    <i class="bi bi-arrow-left-circle me-2"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-4">
                    
                    <?php if($sukses): ?>
                        <div class="alert alert-success d-flex align-items-center"><i class="bi bi-check-circle-fill me-2"></i> <?= $sukses ?></div>
                    <?php endif; ?>
                    <?php if($error): ?>
                        <div class="alert alert-danger d-flex align-items-center"><i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $error ?></div>
                    <?php endif; ?>

                    <div class="tab-content">

                        <div class="tab-pane fade show active" id="biodata">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold text-primary m-0">📂 Informasi Pribadi</h5>
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" id="btnEnableEdit" onclick="enableEditMode()">
                                    <i class="bi bi-pencil-square me-1"></i> Edit Profil
                                </button>
                            </div>

                            <form method="POST" enctype="multipart/form-data" id="formBiodata">
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">NAMA LENGKAP</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                        <input type="text" name="nama" id="inputNama" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" required disabled>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">EMAIL (Read Only)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                        <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($user['email']) ?>" readonly disabled>
                                    </div>
                                </div>

                                <div id="actionButtons" class="d-none text-end mt-4 fade-in">
                                    <button type="button" class="btn btn-light me-2" onclick="cancelEditMode()">Batal</button>
                                    <button type="submit" name="update_profil" class="btn btn-primary px-4">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="security">
                            <h5 class="fw-bold mb-4 text-primary">🔒 Ubah Password</h5>
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Password Lama</label>
                                    <input type="password" name="pass_lama" class="form-control" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Password Baru</label>
                                        <input type="password" name="pass_baru" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Ulangi Password Baru</label>
                                        <input type="password" name="pass_konfirm" class="form-control" required>
                                    </div>
                                </div>
                                <div class="text-end mt-2">
                                    <button type="submit" name="update_password" class="btn btn-danger px-4">Ganti Password</button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="history">
                            <h5 class="fw-bold mb-4 text-primary">📚 Riwayat Peminjaman</h5>
                            <?php if(count($riwayat_buku) > 0): ?>
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr><th>Buku</th><th>Tanggal</th><th>Status</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($riwayat_buku as $row): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php $cover = $row['cover'] ? "../../uploads/".$row['cover'] : "https://via.placeholder.com/40"; ?>
                                                    <img src="<?= $cover ?>" class="rounded me-2" width="35">
                                                    <span class="small fw-bold"><?= htmlspecialchars($row['judul']) ?></span>
                                                </div>
                                            </td>
                                            <td class="small"><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])) ?></td>
                                            <td>
                                                <?php 
                                                if($row['status'] == 'pending') echo '<span class="badge bg-warning text-dark">Pending</span>';
                                                elseif($row['status'] == 'dipinjam') echo '<span class="badge bg-success">Dipinjam</span>';
                                                else echo '<span class="badge bg-secondary">Selesai</span>';
                                                ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted">Belum ada riwayat.</div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/animate.js"></script>

<script>
    // --- FITUR MODE EDIT ---
    
    function enableEditMode() {
        // 1. Sembunyikan tombol "Edit Profil"
        document.getElementById('btnEnableEdit').classList.add('d-none');
        
        // 2. Munculkan tombol "Simpan" & "Batal"
        document.getElementById('actionButtons').classList.remove('d-none');
        
        // 3. Aktifkan Input Nama
        document.getElementById('inputNama').disabled = false;
        document.getElementById('inputNama').focus();

        // 4. Aktifkan Input File Foto
        document.getElementById('fileInput').disabled = false;

        // 5. Munculkan Ikon Kamera di Foto (via CSS class)
        document.getElementById('photoWrapper').classList.add('editable');
    }

    function cancelEditMode() {
        // 1. Munculkan kembali tombol "Edit Profil"
        document.getElementById('btnEnableEdit').classList.remove('d-none');
        
        // 2. Sembunyikan tombol aksi
        document.getElementById('actionButtons').classList.add('d-none');
        
        // 3. Matikan Input Nama & Foto
        document.getElementById('inputNama').disabled = true;
        document.getElementById('fileInput').disabled = true;
        
        // 4. Hilangkan mode edit foto
        document.getElementById('photoWrapper').classList.remove('editable');

        // 5. (Opsional) Reset nilai form jika batal (reload halaman simple)
        location.reload(); 
    }

    function previewImage() {
        const input = document.getElementById('fileInput');
        const preview = document.getElementById('previewFoto');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) { preview.src = e.target.result; }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

</body>
</html>