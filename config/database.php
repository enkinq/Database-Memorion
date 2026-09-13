<?php
require_once __DIR__ . '/config.php';

/**
 * Mendapatkan koneksi Database PDO Singleton
 * @return PDO
 * @throws PDOException
 */
function get_db_connection(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Jika request berupa API (Content-Type application/json atau path mengandung /api/)
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            if (strpos($uri, '/api/') !== false) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Gagal terhubung ke database. Periksa konfigurasi di config/config.php.'
                ]);
                exit;
            }

            die('<div style="font-family:sans-serif;padding:20px;background:#fee2e2;color:#991b1b;border-radius:8px;max-width:600px;margin:40px auto;border:1px solid #f87171;">'
                . '<h3 style="margin-top:0;">Database Connection Error</h3>'
                . '<p>Tidak dapat terhubung ke database MySQL. Pastikan kredensial di <code>config/config.php</code> sudah benar.</p>'
                . '<p><strong>Pesan:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>'
                . '</div>');
        }
    }

    return $pdo;
}
