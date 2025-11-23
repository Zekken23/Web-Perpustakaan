<?php
// views/user/riwayat.php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';

session_start();
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: /index.php');
    exit;
}

// Ambil riwayat peminjaman user
$sql = "
    SELECT p.*, b.judul, b.penulis, b.cover
    FROM peminjaman p
    LEFT JOIN buku b ON p.buku_id = b.id
    WHERE p.user_id = :user_id
    ORDER BY p.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $user_id]);
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>

<div class="container" style="max-width:1000px;margin:20px auto;">
    <h2>Peminjaman Saya</h2>
    <?php if (count($riwayat) === 0): ?>
        <p>Belum ada peminjaman. Jelajahi katalog dan lakukan booking jika ingin meminjam.</p>
    <?php else: ?>
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="text-align:left;border-bottom:2px solid #eaeaea;">
                    <th style="padding:8px;">#</th>
                    <th style="padding:8px;">Buku</th>
                    <th style="padding:8px;">Tanggal Pinjam</th>
                    <th style="padding:8px;">Tanggal Kembali</th>
                    <th style="padding:8px;">Status</th>
                    <th style="padding:8px;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach($riwayat as $row): 
                    // Standardize status label (toleransi)
                    $status_raw = strtolower($row['status'] ?? '');
                    $status_label = '';
                    $badge = '';
                    $today = new DateTimeImmutable('now');
                    $tgl_kembali = !empty($row['tanggal_kembali']) ? new DateTimeImmutable($row['tanggal_kembali']) : null;

                    if ($status_raw === 'pending' || $status_raw === 'menunggu' || $status_raw === 'menunggu konfirmasi') {
                        $status_label = 'Menunggu Konfirmasi';
                        $badge = '🟡';
                    } elseif ($status_raw === 'dipinjam' || $status_raw === 'approved') {
                        // cek terlambat
                        if ($tgl_kembali && $today > $tgl_kembali) {
                            $status_label = 'Terlambat';
                            $badge = '🔴';
                        } else {
                            $status_label = 'Sedang Dipinjam';
                            $badge = '🟢';
                        }
                    } elseif ($status_raw === 'kembali' || $status_raw === 'selesai' || $status_raw === 'returned') {
                        $status_label = 'Selesai';
                        $badge = '⚪';
                    } else {
                        // fallback
                        $status_label = ucfirst($row['status'] ?? 'Unknown');
                        $badge = '⚪';
                    }
                ?>
                <tr style="border-bottom:1px solid #f0f0f0;">
                    <td style="padding:10px;vertical-align:middle;"><?php echo $i++; ?></td>
                    <td style="padding:10px;vertical-align:middle;">
                        <div style="display:flex;gap:10px;align-items:center;">
                            <?php if(!empty($row['cover'])): ?>
                                <img src="/assets/uploads/<?php echo htmlspecialchars($row['cover']); ?>" alt="cover" style="width:48px;height:68px;object-fit:cover;border:1px solid #ddd;">
                            <?php else: ?>
                                <div style="width:48px;height:68px;background:#f4f4f4;display:flex;align-items:center;justify-content:center;color:#999;font-size:12px;">No</div>
                            <?php endif; ?>
                            <div>
                                <div style="font-weight:600;"><?php echo htmlspecialchars($row['judul'] ?? '—'); ?></div>
                                <div style="font-size:13px;color:#666;"><?php echo htmlspecialchars($row['penulis'] ?? '-'); ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="padding:10px;vertical-align:middle;"><?php echo !empty($row['tanggal_pinjam']) ? date('d M Y', strtotime($row['tanggal_pinjam'])) : '-'; ?></td>
                    <td style="padding:10px;vertical-align:middle;"><?php echo !empty($row['tanggal_kembali']) ? date('d M Y', strtotime($row['tanggal_kembali'])) : '-'; ?></td>
                    <td style="padding:10px;vertical-align:middle;">
                        <span style="display:inline-block;padding:6px 10px;border-radius:999px;background:#f5f5f5;">
                            <?php echo $badge . ' ' . $status_label; ?>
                        </span>
                    </td>
                    <td style="padding:10px;vertical-align:middle;color:#444;font-size:14px;">
                        <?php
                            // Tambahan keterangan singkat:
                            if ($status_label === 'Menunggu Konfirmasi') {
                                echo 'Sedang menunggu persetujuan admin.';
                            } elseif ($status_label === 'Sedang Dipinjam') {
                                echo 'Ambil buku di perpustakaan dan jangan lupa kembalikan tepat waktu.';
                            } elseif ($status_label === 'Terlambat') {
                                echo 'Melebihi tanggal kembali — segera hubungi admin untuk denda / perpanjangan.';
                            } elseif ($status_label === 'Selesai') {
                                echo 'Telah dikembalikan.';
                            } else {
                                echo htmlspecialchars($row['keterangan'] ?? '-');
                            }
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
