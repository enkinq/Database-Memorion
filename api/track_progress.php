<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-KEY');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response([
        'status' => 'error',
        'message' => 'Method tidak diizinkan. Gunakan POST.'
    ], 405);
}

// 1. Validasi API Key
$auth = validate_api_key();
if (!$auth['valid']) {
    json_response([
        'status' => 'error',
        'message' => $auth['error'] ?? 'Autentikasi API Key gagal.'
    ], 401);
}

// 2. Ambil Payload
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!is_array($data)) {
    $data = $_POST;
}

$playerCode = trim($data['player_code'] ?? $data['kode_pemain'] ?? '');

if (empty($playerCode)) {
    json_response([
        'status' => 'error',
        'message' => 'Field player_code wajib diisi.'
    ], 400);
}

try {
    $pdo = get_db_connection();

    // 3. Upsert Player Session
    $stmtSession = $pdo->prepare("
        INSERT INTO player_sessions (player_code, created_at, updated_at)
        VALUES (:player_code, NOW(), NOW())
        ON DUPLICATE KEY UPDATE updated_at = NOW()
    ");
    $stmtSession->execute(['player_code' => $playerCode]);

    // 4. Jika menyertakan log stage/puzzle, simpan ke puzzle_logs
    $stageName = trim($data['stage_name'] ?? $data['modul'] ?? '');
    $puzzleId = trim($data['puzzle_id'] ?? $data['id_puzzle'] ?? '');
    $logId = null;

    if (!empty($stageName) && !empty($puzzleId)) {
        $jawabanTeks = isset($data['jawaban_teks']) ? trim((string)$data['jawaban_teks']) : null;
        $skorValidasi = isset($data['skor_validasi']) ? floatval($data['skor_validasi']) : 0.00;
        $durasiDetik = isset($data['durasi_detik']) ? intval($data['durasi_detik']) : 0;
        $statusSelesai = isset($data['status_selesai']) ? (intval($data['status_selesai']) ? 1 : 0) : 0;

        $stmtLog = $pdo->prepare("
            INSERT INTO puzzle_logs 
            (player_code, stage_name, puzzle_id, jawaban_teks, skor_validasi, durasi_detik, status_selesai, created_at)
            VALUES 
            (:player_code, :stage_name, :puzzle_id, :jawaban_teks, :skor_validasi, :durasi_detik, :status_selesai, NOW())
        ");
        $stmtLog->execute([
            'player_code' => $playerCode,
            'stage_name' => $stageName,
            'puzzle_id' => $puzzleId,
            'jawaban_teks' => $jawabanTeks,
            'skor_validasi' => $skorValidasi,
            'durasi_detik' => $durasiDetik,
            'status_selesai' => $statusSelesai
        ]);
        $logId = (int)$pdo->lastInsertId();
    }

    // 5. Ambil data agregat sesi pemain
    $stmtStats = $pdo->prepare("
        SELECT 
            COUNT(*) as total_puzzles,
            COALESCE(AVG(skor_validasi), 0) as avg_score,
            COALESCE(SUM(durasi_detik), 0) as total_duration
        FROM puzzle_logs 
        WHERE player_code = :player_code
    ");
    $stmtStats->execute(['player_code' => $playerCode]);
    $stats = $stmtStats->fetch();

    json_response([
        'status' => 'success',
        'message' => 'Progress tracking berhasil dicatat.',
        'data' => [
            'player_code' => $playerCode,
            'log_id' => $logId,
            'total_puzzles_completed' => (int)($stats['total_puzzles'] ?? 0),
            'average_score' => round((float)($stats['avg_score'] ?? 0), 2),
            'total_duration_seconds' => (int)($stats['total_duration'] ?? 0),
            'last_sync' => date('Y-m-d H:i:s')
        ]
    ], 200);

} catch (PDOException $e) {
    json_response([
        'status' => 'error',
        'message' => 'Kesalahan database: ' . $e->getMessage()
    ], 500);
}
