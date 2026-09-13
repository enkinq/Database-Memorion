<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Mengirim response JSON dengan status code yang sesuai
 */
function json_response(array $data, int $statusCode = 200): void {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-KEY');
    
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Mendapatkan API Key dari Header Request
 */
function get_request_api_key(): ?string {
    // 1. Cek $_SERVER['HTTP_X_API_KEY']
    if (!empty($_SERVER['HTTP_X_API_KEY'])) {
        return trim($_SERVER['HTTP_X_API_KEY']);
    }

    // 2. Cek getallheaders()
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        foreach ($headers as $key => $value) {
            if (strcasecmp($key, 'X-API-KEY') === 0 || strcasecmp($key, 'X-Api-Key') === 0) {
                return trim($value);
            }
        }
    }

    // 3. Cek Bearer Token di Authorization Header
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (!empty($authHeader) && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        return trim($matches[1]);
    }

    return null;
}

/**
 * Validasi API Key ke Database
 * @return array{valid: bool, client: ?array, error: ?string}
 */
function validate_api_key(): array {
    $apiKey = get_request_api_key();

    if (!$apiKey) {
        return [
            'valid' => false,
            'client' => null,
            'error' => 'Header API Key (X-API-KEY) tidak ditemukan dalam request.'
        ];
    }

    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT * FROM api_keys WHERE api_key = :api_key LIMIT 1");
        $stmt->execute(['api_key' => $apiKey]);
        $client = $stmt->fetch();

        if (!$client) {
            return [
                'valid' => false,
                'client' => null,
                'error' => 'API Key tidak valid.'
            ];
        }

        if ($client['status'] !== 'aktif') {
            return [
                'valid' => false,
                'client' => $client,
                'error' => 'API Key ini telah dinonaktifkan (Revoked).'
            ];
        }

        return [
            'valid' => true,
            'client' => $client,
            'error' => null
        ];
    } catch (PDOException $e) {
        return [
            'valid' => false,
            'client' => null,
            'error' => 'Kesalahan database saat validasi API Key: ' . $e->getMessage()
        ];
    }
}

/**
 * Menghasilkan API Key acak yang aman
 */
function generate_api_key(): string {
    return 'mem_live_' . bin2hex(random_bytes(24));
}

/**
 * Sanitasi string output HTML (Cegah XSS)
 */
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Format tanggal dan waktu untuk tampilan user
 */
function format_datetime(?string $datetime): string {
    if (!$datetime) return '-';
    $timestamp = strtotime($datetime);
    if (!$timestamp) return $datetime;
    return date('d M Y, H:i:s', $timestamp);
}

/**
 * Generate CSRF Token
 */
function csrf_token(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifikasi CSRF Token
 */
function verify_csrf_token(?string $token): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Set Flash Message
 */
function set_flash(string $type, string $message): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = [
        'type' => $type, // success, error, warning, info
        'message' => $message
    ];
}

/**
 * Ambil dan bersihkan Flash Message
 */
function get_flash(): ?array {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
