<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_auth();

$pdo = get_db_connection();

// Filter Parameters
$filterPlayer = trim($_GET['player_code'] ?? '');
$filterStage = trim($_GET['stage_name'] ?? '');
$filterStatus = $_GET['status_selesai'] ?? '';
$filterDateFrom = trim($_GET['date_from'] ?? '');
$filterDateTo = trim($_GET['date_to'] ?? '');

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

$query = "
    SELECT 
        id, 
        player_code, 
        stage_name, 
        puzzle_id, 
        jawaban_teks, 
        skor_validasi, 
        durasi_detik, 
        status_selesai, 
        created_at 
    FROM puzzle_logs 
    $whereClause 
    ORDER BY id ASC
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);

$filename = 'memorion_logs_' . date('Ymd_His') . '.csv';

// Set Headers untuk Download CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Buka output stream
$output = fopen('php://output', 'w');

// Tambahkan BOM UTF-8 agar karakter bahasa Indonesia rapi di MS Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Tulis Header Kolom CSV
fputcsv($output, [
    'ID',
    'Kode Pemain',
    'Modul / Stage',
    'Puzzle ID',
    'Jawaban Teks',
    'Skor Validasi',
    'Durasi (Detik)',
    'Status Selesai',
    'Waktu Pengerjaan'
]);

// Tulis Baris Data
while ($row = $stmt->fetch()) {
    fputcsv($output, [
        $row['id'],
        $row['player_code'],
        $row['stage_name'],
        $row['puzzle_id'],
        $row['jawaban_teks'] ?? '',
        number_format((float)$row['skor_validasi'], 2, '.', ''),
        $row['durasi_detik'],
        $row['status_selesai'] == 1 ? 'Selesai' : 'Belum Selesai',
        $row['created_at']
    ]);
}

fclose($output);
exit;
