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

// Hanya izinkan method POST
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

// 2. Ambil dan parse JSON Payload
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!is_array($data)) {
    // Fallback jika dikirim lewat form-urlencoded
    $data = $_POST;
}

// 3. Validasi Field Wajib
$playerCode = trim($data['player_code'] ?? $data['kode_pemain'] ?? '');
$stageName = trim($data['stage_name'] ?? $data['modul'] ?? '');
$puzzleId = trim($data['puzzle_id'] ?? $data['id_puzzle'] ?? '');

if (empty($playerCode) || empty($stageName) || empty($puzzleId)) {
    json_response([
        'status' => 'error',
        'message' => 'Field wajib tidak lengkap. Mohon sertakan player_code, stage_name, dan puzzle_id.',
        'received' => [
            'player_code' => $playerCode,
            'stage_name' => $stageName,
            'puzzle_id' => $puzzleId
        ]
    ], 400);
}

// 4. Sanitasi dan parsing data opsional
$jawabanTeks = isset($data['jawaban_teks']) ? trim((string)$data['jawaban_teks']) : (isset($data['jawaban']) ? trim((string)$data['jawaban']) : null);
$skorValidasi = isset($data['skor_validasi']) ? floatval($data['skor_validasi']) : (isset($data['skor']) ? floatval($data['skor']) : 0.00);
$durasiDetik = isset($data['durasi_detik']) ? intval($data['durasi_detik']) : (isset($data['durasi']) ? intval($data['durasi']) : 0);
$statusSelesai = isset($data['status_selesai']) ? (intval($data['status_selesai']) ? 1 : 0) : 1;

try {
    $pdo = get_db_connection();

    // Pastikan session pemain terdaftar/terupdate
    $stmtSession = $pdo->prepare("
        INSERT INTO player_sessions (player_code, created_at, updated_at)
        VALUES (:player_code, NOW(), NOW())
        ON DUPLICATE KEY UPDATE updated_at = NOW()
    ");
    $stmtSession->execute(['player_code' => $playerCode]);

    // Insert ke tabel puzzle_logs
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

    $logId = $pdo->lastInsertId();

    json_response([
        'status' => 'success',
        'message' => 'Jawaban teka-teki berhasil disimpan.',
        'data' => [
            'log_id' => (int)$logId,
            'player_code' => $playerCode,
            'stage_name' => $stageName,
            'puzzle_id' => $puzzleId,
            'skor_validasi' => $skorValidasi,
            'durasi_detik' => $durasiDetik,
            'status_selesai' => $statusSelesai,
            'client_name' => $auth['client']['client_name'] ?? 'Game Client',
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ], 200);

} catch (PDOException $e) {
    json_response([
        'status' => 'error',
        'message' => 'Gagal menyimpan data ke database: ' . $e->getMessage()
    ], 500);
}
