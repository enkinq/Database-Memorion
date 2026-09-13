<?php
$pageTitle = 'Daftar Sesi Pemain';
require_once __DIR__ . '/../includes/header.php';

$pdo = get_db_connection();

// Handle Delete Session
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        set_flash('error', 'Sesi keamanan tidak valid.');
        header('Location: sessions.php');
        exit;
    }

    $action = $_POST['action'] ?? '';
    if ($action === 'delete_player') {
        $playerCode = trim($_POST['player_code'] ?? '');
        try {
            // Hapus log dan sesi
            $stmt1 = $pdo->prepare("DELETE FROM puzzle_logs WHERE player_code = :code");
            $stmt1->execute(['code' => $playerCode]);

            $stmt2 = $pdo->prepare("DELETE FROM player_sessions WHERE player_code = :code");
            $stmt2->execute(['code' => $playerCode]);

            set_flash('success', "Data pemain '$playerCode' beserta seluruh log pengerjaannya berhasil dihapus.");
        } catch (PDOException $e) {
            set_flash('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
        header('Location: sessions.php');
        exit;
    }
}

// Fetch players with aggregated statistics
$query = "
    SELECT 
        ps.id,
        ps.player_code,
        ps.created_at as first_seen,
        ps.updated_at as last_seen,
        COUNT(pl.id) as total_attempts,
        COALESCE(AVG(pl.skor_validasi), 0) as avg_score,
        COALESCE(SUM(pl.durasi_detik), 0) as total_duration
    FROM player_sessions ps
    LEFT JOIN puzzle_logs pl ON ps.player_code = pl.player_code
    GROUP BY ps.id, ps.player_code, ps.created_at, ps.updated_at
    ORDER BY ps.updated_at DESC
";
$stmt = $pdo->query($query);
$sessions = $stmt->fetchAll();
?>

<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-white p-6 rounded-2xl border border-slate-200 shadow-sm gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Sesi & Identitas Pemain</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                    <?= count($sessions) ?> Pemain
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-0.5">Daftar kode unik anak/pemain yang terhubung ke game MEMORion+ beserta ringkasan progresnya.</p>
        </div>
    </div>

    <!-- Players Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-100 text-slate-600 font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">#</th>
                        <th class="py-3.5 px-4">Kode Pemain</th>
                        <th class="py-3.5 px-4 text-center">Total Percobaan</th>
                        <th class="py-3.5 px-4 text-center">Rata-rata Skor</th>
                        <th class="py-3.5 px-4 text-center">Total Durasi</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Pertama Kali Masuk</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Aktivitas Terakhir</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($sessions)): ?>
                        <?php foreach ($sessions as $i => $item): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-4 text-center text-slate-400 font-mono">
                                    <?= $i + 1 ?>
                                </td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs">
                                            <?= strtoupper(substr($item['player_code'], 0, 2)) ?>
                                        </div>
                                        <span><?= e($item['player_code']) ?></span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center font-semibold text-slate-800">
                                    <?= number_format($item['total_attempts']) ?> teka-teki
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold <?= $item['avg_score'] >= 70 ? 'bg-emerald-100 text-emerald-800' : ($item['avg_score'] >= 40 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') ?>">
                                        <?= number_format($item['avg_score'], 1) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center text-slate-600 font-mono">
                                    <?= gmdate("H:i:s", (int)$item['total_duration']) ?>
                                </td>
                                <td class="py-3 px-4 text-slate-500 whitespace-nowrap">
                                    <?= format_datetime($item['first_seen']) ?>
                                </td>
                                <td class="py-3 px-4 text-slate-700 font-medium whitespace-nowrap">
                                    <?= format_datetime($item['last_seen']) ?>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="logs.php?player_code=<?= urlencode($item['player_code']) ?>" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-[11px] transition-colors" title="Lihat Detail Log Pemain Ini">
                                            Lihat Log
                                        </a>
                                        <form method="POST" action="sessions.php" onsubmit="return confirm('Hapus seluruh data dan riwayat log untuk pemain <?= e($item['player_code']) ?>?');" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="action" value="delete_player">
                                            <input type="hidden" name="player_code" value="<?= e($item['player_code']) ?>">
                                            <button type="submit" class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Pemain">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-400">
                                Belum ada data sesi pemain yang terdaftar.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
