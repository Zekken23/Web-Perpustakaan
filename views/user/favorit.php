<?php
// views/user/favorit.php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';

session_start();
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: /index.php');
    exit;
}

// ambil daftar favorit user
$sql = "
    SELECT f.id as favorit_id, b.id as buku_id, b.judul, b.penulis, b.cover, b.stok
    FROM favorit f
    JOIN buku b ON f.buku_id = b.id
    WHERE f.user_id = :user_id
    ORDER BY f.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $user_id]);
$favorit = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>

<div class="container" style="max-width:1000px;margin:20px auto;">
    <h2>Favorit Saya</h2>

    <?php if (!empty($_SESSION['flash'])): ?>
        <?php foreach ($_SESSION['flash'] as $type => $msg): ?>
            <div class="flash <?php echo htmlspecialchars($type); ?>" style="padding:10px;border-radius:6px;margin-bottom:12px;background:#f0f8ff;">
                <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endforeach; unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if (count($favorit) === 0): ?>
        <p>Belum ada buku di favorit. Jelajahi katalog dan klik ikon hati untuk menyimpan buku.</p>
    <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;">
            <?php foreach ($favorit as $item): ?>
                <div style="border:1px solid #e7e7e7;border-radius:8px;padding:12px;background:#fff;">
                    <div style="display:flex;gap:12px;">
                        <?php if (!empty($item['cover'])): ?>
                            <img src="/assets/uploads/<?php echo htmlspecialchars($item['cover']); ?>" alt="cover" style="width:72px;height:100px;object-fit:cover;border:1px solid #ddd;">
                        <?php else: ?>
                            <div style="width:72px;height:100px;background:#f4f4f4;display:flex;align-items:center;justify-content:center;color:#999;">No</div>
                        <?php endif; ?>

                        <div style="flex:1;">
                            <div style="font-weight:600;"><?php echo htmlspecialchars($item['judul']); ?></div>
                            <div style="font-size:13px;color:#666;margin-bottom:8px;"><?php echo htmlspecialchars($item['penulis'] ?? '-'); ?></div>

                            <div style="display:flex;gap:8px;align-items:center;">
                                <a href="/views/user/pinjam_buku.php?id=<?php echo (int)$item['buku_id']; ?>" style="padding:6px 10px;border-radius:6px;border:1px solid #2d9cdb;color:#2d9cdb;text-decoration:none;font-size:14px;">Detail</a>

                                <form method="post" action="/actions/favorit_toggle.php" style="display:inline;">
                                    <input type="hidden" name="buku_id" value="<?php echo (int)$item['buku_id']; ?>">
                                    <button type="submit" style="padding:6px 10px;border-radius:6px;border:1px solid #e74c3c;background:#fff;color:#e74c3c;cursor:pointer;">
                                        Hapus dari Favorit
                                    </button>
                                </form>

                                <?php if ((int)$item['stok'] > 0): ?>
                                    <form method="post" action="/actions/pinjam_create.php" style="display:inline;">
                                        <input type="hidden" name="buku_id" value="<?php echo (int)$item['buku_id']; ?>">
                                        <button type="submit" style="padding:6px 10px;border-radius:6px;background:#2d9cdb;color:#fff;border:0;cursor:pointer;">
                                            Pinjam Buku
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button disabled style="padding:6px 10px;border-radius:6px;background:#ccc;color:#fff;border:0;">
                                        Tidak Tersedia
                                    </button>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
