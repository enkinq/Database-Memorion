<?php
/**
 * Konfigurasi Utama Sistem MEMORion+
 * Sesuaikan kredensial database di bawah ini dengan database cPanel / Localhost Anda.
 */

// Pengaturan Database
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'memorion_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_CHARSET', 'utf8mb4');

// Pengaturan Aplikasi
define('APP_NAME', 'MEMORion+ Dashboard');
define('APP_VERSION', '1.0.0');
define('APP_TIMEZONE', 'Asia/Jakarta');

// Base URL (Deteksi otomatis atau set manual)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$baseUrl = rtrim($protocol . $host . $scriptDir, '/\\');
define('BASE_URL', $baseUrl);

// Set Timezone
date_default_timezone_set(APP_TIMEZONE);

// Error Reporting (Ubah ke 0 saat sudah di live production)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
