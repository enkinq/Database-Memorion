<?php
/**
 * Skrip Inisialisasi Database & Seeder MEMORion+
 * Jalankan file ini melalui terminal (php seed.php) atau buka di browser satu kali saat instalasi baru.
 */

require_once __DIR__ . '/config/database.php';

echo "<pre style='font-family: monospace; background: #0f172a; color: #38bdf8; padding: 20px; border-radius: 10px;'>\n";
echo "=====================================================\n";
echo "   INSIALISASI DATABASE & SEEDER MEMORion+\n";
echo "=====================================================\n\n";

try {
    $pdo = get_db_connection();
    echo "[✓] Berhasil terhubung ke database: " . DB_NAME . "\n\n";

    // 1. Buat Tabel Admins
    echo "[i] Membuat tabel 'admins'...";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `admins` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(50) NOT NULL UNIQUE,
            `password_hash` VARCHAR(255) NOT NULL,
            `nama_lengkap` VARCHAR(100) DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo " OK\n";

    // 2. Buat Tabel API Keys
    echo "[i] Membuat tabel 'api_keys'...";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `api_keys` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `api_key` VARCHAR(64) NOT NULL UNIQUE,
            `client_name` VARCHAR(100) NOT NULL,
            `status` ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo " OK\n";

    // 3. Buat Tabel Player Sessions
    echo "[i] Membuat tabel 'player_sessions'...";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `player_sessions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `player_code` VARCHAR(100) NOT NULL UNIQUE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_player_code` (`player_code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo " OK\n";

    // 4. Buat Tabel Puzzle Logs
    echo "[i] Membuat tabel 'puzzle_logs'...";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `puzzle_logs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `player_code` VARCHAR(100) NOT NULL,
            `stage_name` VARCHAR(100) NOT NULL,
            `puzzle_id` VARCHAR(100) NOT NULL,
            `jawaban_teks` TEXT DEFAULT NULL,
            `skor_validasi` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            `durasi_detik` INT NOT NULL DEFAULT 0,
            `status_selesai` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_log_player` (`player_code`),
            INDEX `idx_log_stage` (`stage_name`),
            INDEX `idx_log_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo " OK\n\n";

    // 5. Buat Default Admin jika belum ada
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = 'admin'");
    $stmtCheck->execute();
    if ($stmtCheck->fetchColumn() == 0) {
        $defaultPassHash = password_hash('password123', PASSWORD_DEFAULT);
        $stmtAdmin = $pdo->prepare("INSERT INTO admins (username, password_hash, nama_lengkap) VALUES ('admin', :pass, 'Administrator MEMORion+')");
        $stmtAdmin->execute(['pass' => $defaultPassHash]);
        echo "[+] Admin default berhasil dibuat (Username: admin | Password: password123)\n";
    } else {
        echo "[i] Admin default 'admin' sudah ada.\n";
    }

    // 6. Buat Sample API Key jika belum ada
    $stmtCheckKey = $pdo->prepare("SELECT COUNT(*) FROM api_keys WHERE client_name = 'Godot Client Game Build v1.0'");
    $stmtCheckKey->execute();
    if ($stmtCheckKey->fetchColumn() == 0) {
        $sampleKey = 'mem_sec_live_9f8a3c4e2b1d6e7f8091a2b3c4d5e6f7';
        $stmtKey = $pdo->prepare("INSERT INTO api_keys (api_key, client_name, status) VALUES (:key, 'Godot Client Game Build v1.0', 'aktif')");
        $stmtKey->execute(['key' => $sampleKey]);
        echo "[+] Sample API Key aktif dibuat: {$sampleKey}\n";
    } else {
        echo "[i] Sample API Key sudah ada.\n";
    }

    echo "\n=====================================================\n";
    echo "   [✓] SETUP SELESAI! SEMUA TABEL SIAP DIGUNAKAN.\n";
    echo "=====================================================\n";
    echo "</pre>";

} catch (Exception $e) {
    echo "\n[X] TERJADI KESALAHAN: " . $e->getMessage() . "\n";
    echo "</pre>";
}
