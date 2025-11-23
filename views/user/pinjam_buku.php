<?php
// views/user/pinjam_buku.php
// Tampilan detail buku + tombol "Pinjam Buku"
// Pastikan user sudah login (auth middleware)
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php'; // harus menghasilkan $pdo (PDO instance)

session_start();
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: /index.php');
    exit;
}

// ambil id buku dari query string
$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$book_id) {
    echo "Buku tidak ditemukan.";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM buku WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $book_id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$book) {
    echo "Buku tidak ditemukan.";
    exit;
}

// tampilkan page
include __DIR__ . '/../../includes/header.php';
?>
<div class="container" style="max-width:900px;margin:20px auto;">
    <h2>Detail Buku</h2>
    <div style="display:flex;gap:20px;align-items:flex-start;">
        <div style="width:200px;">
            <?php if(!empty($book['cover'])): ?>
                <img src="/assets/uploads/<?php echo htmlspecialchars($book['cover']); ?>" alt="cover" style="width:100%;border:1px solid #ddd;padding:4px;">
            <?php else: ?>
                <div style="width:100%;height:260px;background:#f4f4f4;display:flex;align-items:center;justify-content:center;color:#888;">No Cover</div>
            <?php endif; ?>
        </div>

        <div style="flex:1;">
            <h3><?php echo htmlspecialchars($book['judul']); ?></h3>
            <p><strong>Penulis:</strong> <?php echo htmlspecialchars($book['penulis'] ?? '-'); ?></p>
            <p><strong>Tahun:</strong> <?php echo htmlspecialchars($book['tahun'] ?? '-'); ?></p>
            <p><strong>Stok:</strong> <?php echo (int)$book['stok']; ?></p>
            <p><?php echo nl2br(htmlspecialchars($book['deskripsi'] ?? '')); ?></p>
            
            

            <div style="margin-top:16px;">
                <?php if ((int)$book['stok'] <= 0): ?>
                    <button disabled style="padding:10px 18px;border-radius:6px;background:#ccc;border:0;cursor:not-allowed;">Tidak Tersedia</button>
                <?php else: ?>
                    <form method="post" action="/actions/pinjam_create.php" style="display:inline;">
                        <input type="hidden" name="buku_id" value="<?php echo (int)$book['id']; ?>">
                        <button type="submit" name="pinjam" style="padding:10px 18px;border-radius:6px;background:#2d9cdb;color:#fff;border:0;cursor:pointer;">
                            Pinjam Buku
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <div style="margin-top:10px;color:#666;font-size:13px;">
                <em>Catatan: Setelah menekan "Pinjam Buku" status akan menjadi <strong>Menunggu Konfirmasi</strong>. Admin akan meng-ACC saat user mengambil buku fisik.</em>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
