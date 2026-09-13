<?php
$pageTitle = 'Data Log Respon Anak';
require_once __DIR__ . '/../includes/header.php';

$pdo = get_db_connection();

// Filter Parameters
$filterPlayer = trim($_GET['player_code'] ?? '');
$filterStage = trim($_GET['stage_name'] ?? '');
$filterStatus = $_GET['status_selesai'] ?? '';
$filterDateFrom = trim($_GET['date_from'] ?? '');
$filterDateTo = trim($_GET['date_to'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

// Bangun Query Filter
$where = [];
$params = [];

if (!empty($filterPlayer)) {
    $where[] = "player_code LIKE :player_code";
    $params['player_code'] = '%' . $filterPlayer . '%';
}

if (!empty($filterStage)) {
    $where[] = "stage_name = :stage_name";
    $params['stage_name'] = $filterStage;
}

if ($filterStatus !== '') {
    $where[] = "status_selesai = :status_selesai";
    $params['status_selesai'] = intval($filterStatus);
}

if (!empty($filterDateFrom)) {
    $where[] = "DATE(created_at) >= :date_from";
    $params['date_from'] = $filterDateFrom;
}

if (!empty($filterDateTo)) {
    $where[] = "DATE(created_at) <= :date_to";
    $params['date_to'] = $filterDateTo;
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Hitung Total Data untuk Pagination
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM puzzle_logs $whereClause");
$stmtCount->execute($params);
$totalRecords = (int)$stmtCount->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

// Query Data Logs
$query = "
    SELECT id, player_code, stage_name, puzzle_id, jawaban_teks, skor_validasi, durasi_detik, status_selesai, created_at 
    FROM puzzle_logs 
    $whereClause 
    ORDER BY id DESC 
    LIMIT $limit OFFSET $offset
";
$stmtLogs = $pdo->prepare($query);
$stmtLogs->execute($params);
$logs = $stmtLogs->fetchAll();

// Ambil list stage unik untuk dropdown filter
$stmtStages = $pdo->query("SELECT DISTINCT stage_name FROM puzzle_logs ORDER BY stage_name ASC");
$stageOptions = $stmtStages->fetchAll(PDO::FETCH_COLUMN);

// Build export query string
$exportQuery = http_build_query([
    'player_code' => $filterPlayer,
    'stage_name' => $filterStage,
    'status_selesai' => $filterStatus,
    'date_from' => $filterDateFrom,
    'date_to' => $filterDateTo
]);
?>

<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-white p-6 rounded-2xl border border-slate-200 shadow-sm gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Log Respon & Jawaban Anak</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                    Total: <?= number_format($totalRecords) ?> Data
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-0.5">Daftar rekaman jawaban teka-teki, skor akurasi validasi, dan durasi pengerjaan anak.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="export.php?<?= $exportQuery ?>" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export CSV Sesuai Filter
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="logs.php" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <!-- Filter Player Code -->
            <div class="lg:col-span-1">
                <label for="player_code" class="block text-xs font-semibold text-slate-600 mb-1">Kode Pemain</label>
                <input type="text" id="player_code" name="player_code" value="<?= e($filterPlayer) ?>" placeholder="Contoh: ANAK_01"
                       class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
            </div>

            <!-- Filter Stage Name -->
            <div class="lg:col-span-1">
                <label for="stage_name" class="block text-xs font-semibold text-slate-600 mb-1">Modul / Stage</label>
                <select id="stage_name" name="stage_name" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    <option value="">Semua Stage</option>
                    <?php foreach ($stageOptions as $st): ?>
                        <option value="<?= e($st) ?>" <?= $filterStage === $st ? 'selected' : '' ?>><?= e($st) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Status Selesai -->
            <div class="lg:col-span-1">
                <label for="status_selesai" class="block text-xs font-semibold text-slate-600 mb-1">Status Selesai</label>
                <select id="status_selesai" name="status_selesai" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="1" <?= $filterStatus === '1' ? 'selected' : '' ?>>Selesai (1)</option>
                    <option value="0" <?= $filterStatus === '0' ? 'selected' : '' ?>>Belum Selesai (0)</option>
                </select>
            </div>

            <!-- Filter Date From -->
            <div class="lg:col-span-1">
                <label for="date_from" class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                <input type="date" id="date_from" name="date_from" value="<?= e($filterDateFrom) ?>"
                       class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
            </div>

            <!-- Filter Date To -->
            <div class="lg:col-span-1">
                <label for="date_to" class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                <input type="date" id="date_to" name="date_to" value="<?= e($filterDateTo) ?>"
                       class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
            </div>

            <!-- Filter Buttons -->
            <div class="lg:col-span-1 flex items-center space-x-2">
                <button type="submit" class="w-full py-2 px-3 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-xl transition-colors flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Filter
                </button>
                <a href="logs.php" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl transition-colors" title="Reset Filter">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </a>
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-100 text-slate-600 font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">#</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Waktu Pengujian</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Kode Pemain</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Modul / Stage</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Puzzle ID</th>
                        <th class="py-3.5 px-4 min-w-[200px]">Teks Jawaban Anak</th>
                        <th class="py-3.5 px-4 text-center whitespace-nowrap">Skor Validasi</th>
                        <th class="py-3.5 px-4 text-center whitespace-nowrap">Durasi</th>
                        <th class="py-3.5 px-4 text-center whitespace-nowrap">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($logs)): ?>
                        <?php foreach ($logs as $i => $log): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-4 text-center text-slate-400 font-mono">
                                    <?= $offset + $i + 1 ?>
                                </td>
                                <td class="py-3 px-4 text-slate-500 whitespace-nowrap font-mono text-[11px]">
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
                                <td class="py-3 px-4 text-slate-800">
                                    <?php if (!empty($log['jawaban_teks'])): ?>
                                        <div class="bg-slate-50 p-2 rounded-lg border border-slate-200 text-slate-700 font-normal">
                                            <?= nl2br(e($log['jawaban_teks'])) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-slate-400 italic">(Tidak ada jawaban teks)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-slate-900 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $log['skor_validasi'] >= 70 ? 'bg-emerald-100 text-emerald-800' : ($log['skor_validasi'] >= 40 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') ?>">
                                        <?= number_format($log['skor_validasi'], 1) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center text-slate-600 whitespace-nowrap font-mono">
                                    <?= e((string)$log['durasi_detik']) ?> detik
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <?php if ($log['status_selesai']): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                            Selesai
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700">
                                            Belum
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <svg class="w-10 h-10 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="font-medium text-slate-600">Tidak ada data log yang sesuai dengan filter.</p>
                                <p class="text-xs text-slate-400 mt-1">Coba ubah kriteria pencarian atau klik reset filter.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        <?php if ($totalPages > 1): ?>
            <div class="p-4 border-t border-slate-100 bg-slate-50 flex items-center justify-between">
                <p class="text-xs text-slate-500">
                    Menampilkan Halaman <span class="font-bold text-slate-800"><?= $page ?></span> dari <span class="font-bold text-slate-800"><?= $totalPages ?></span>
                </p>
                <div class="flex items-center space-x-1">
                    <?php if ($page > 1): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="px-3 py-1.5 bg-white border border-slate-200 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-100">
                            Sebelumnya
                        </a>
                    <?php endif; ?>

                    <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>" class="px-3 py-1.5 rounded-lg text-xs font-semibold <?= $p === $page ? 'bg-emerald-600 text-white' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-100' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="px-3 py-1.5 bg-white border border-slate-200 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-100">
                            Selanjutnya
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
