<?php
$pageTitle = 'Dashboard Overview';
require_once __DIR__ . '/../includes/header.php';

try {
    $pdo = get_db_connection();

    // 1. Statistik Total Pemain
    $stmtPlayers = $pdo->query("SELECT COUNT(*) FROM player_sessions");
    $totalPlayers = (int)$stmtPlayers->fetchColumn();

    // 2. Statistik Total Log Jawaban
    $stmtLogs = $pdo->query("SELECT COUNT(*) FROM puzzle_logs");
    $totalLogs = (int)$stmtLogs->fetchColumn();

    // 3. Rata-rata Skor Validasi
    $stmtAvgScore = $pdo->query("SELECT COALESCE(AVG(skor_validasi), 0) FROM puzzle_logs");
    $avgScore = round((float)$stmtAvgScore->fetchColumn(), 2);

    // 4. API Keys Aktif
    $stmtKeys = $pdo->query("SELECT COUNT(*) FROM api_keys WHERE status = 'aktif'");
    $activeKeys = (int)$stmtKeys->fetchColumn();

    // 5. Log Terbaru (10 record terakhir)
    $stmtRecent = $pdo->query("
        SELECT id, player_code, stage_name, puzzle_id, jawaban_teks, skor_validasi, durasi_detik, status_selesai, created_at 
        FROM puzzle_logs 
        ORDER BY id DESC 
        LIMIT 10
    ");
    $recentLogs = $stmtRecent->fetchAll();

} catch (PDOException $e) {
    $dbError = $e->getMessage();
}
?>

<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-white p-6 rounded-2xl border border-slate-200 shadow-sm gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Ringkasan Sistem Monitoring</h1>
            <p class="text-sm text-slate-500 mt-0.5">Pantau aktivitas pengujian, respon anak, dan log game MEMORion+ secara real-time.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="logs.php" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-colors">
                <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Lihat Semua Log
            </a>
            <a href="export.php" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export CSV/Excel
            </a>
        </div>
    </div>

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Pemain -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Pemain/Sesi</span>
                <div class="p-2.5 rounded-xl bg-blue-50 text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-extrabold text-slate-900"><?= number_format($totalPlayers ?? 0) ?></div>
                <p class="text-xs text-slate-400 mt-1">Kode pemain terdaftar</p>
            </div>
        </div>

        <!-- Card 2: Total Log Jawaban -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Log Jawaban</span>
                <div class="p-2.5 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-extrabold text-slate-900"><?= number_format($totalLogs ?? 0) ?></div>
                <p class="text-xs text-slate-400 mt-1">Percobaan teka-teki terekam</p>
            </div>
        </div>

        <!-- Card 3: Rata-rata Skor -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Rata-rata Skor</span>
                <div class="p-2.5 rounded-xl bg-amber-50 text-amber-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-extrabold text-slate-900"><?= $avgScore ?? '0.00' ?></div>
                <p class="text-xs text-slate-400 mt-1">Skala 0 - 100 poin</p>
            </div>
        </div>

        <!-- Card 4: API Keys Aktif -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">API Keys Aktif</span>
                <div class="p-2.5 rounded-xl bg-purple-50 text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-extrabold text-slate-900"><?= number_format($activeKeys ?? 0) ?></div>
                <p class="text-xs text-slate-400 mt-1">Klien game terhubung</p>
            </div>
        </div>
    </div>

    <!-- Recent Logs Table Section -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
            <div>
                <h2 class="text-base font-bold text-slate-900">Aktivitas Respon Terkini</h2>
                <p class="text-xs text-slate-500">10 data log jawaban anak yang paling baru masuk dari engine game.</p>
            </div>
            <a href="logs.php" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 flex items-center">
                Lihat Semua
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-100 text-slate-600 font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Waktu</th>
                        <th class="py-3.5 px-4">Kode Pemain</th>
                        <th class="py-3.5 px-4">Modul / Stage</th>
                        <th class="py-3.5 px-4">Puzzle ID</th>
                        <th class="py-3.5 px-4">Teks Jawaban</th>
                        <th class="py-3.5 px-4 text-center">Skor</th>
                        <th class="py-3.5 px-4 text-center">Durasi</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($recentLogs)): ?>
                        <?php foreach ($recentLogs as $log): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-4 text-slate-500 whitespace-nowrap">
                                    <?= format_datetime($log['created_at']) ?>
                                </td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                    <span class="bg-slate-100 px-2 py-0.5 rounded border border-slate-200"><?= e($log['player_code']) ?></span>
                                </td>
                                <td class="py-3 px-4 text-slate-700 font-medium whitespace-nowrap">
                                    <?= e($log['stage_name']) ?>
                                </td>
                                <td class="py-3 px-4 text-slate-500 font-mono">
                                    <?= e($log['puzzle_id']) ?>
                                </td>
                                <td class="py-3 px-4 text-slate-800 max-w-xs truncate" title="<?= e($log['jawaban_teks']) ?>">
                                    <?= !empty($log['jawaban_teks']) ? e($log['jawaban_teks']) : '<span class="text-slate-400 italic">(Kosong)</span>' ?>
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-slate-900">
                                    <span class="px-2 py-0.5 rounded-full <?= $log['skor_validasi'] >= 70 ? 'bg-emerald-100 text-emerald-800' : ($log['skor_validasi'] >= 40 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') ?>">
                                        <?= number_format($log['skor_validasi'], 1) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center text-slate-600 whitespace-nowrap">
                                    <?= e((string)$log['durasi_detik']) ?>s
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <?php if ($log['status_selesai']): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                            Selesai
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                            Belum
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                <svg class="w-8 h-8 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                Belum ada log data teka-teki yang masuk dari game client.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
