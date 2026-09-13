<?php
if (session_status() === PHP_SESSION_NONE) {
    // Gunakan konfigurasi session yang aman
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

/**
 * Cek apakah admin sudah login
 */
function is_logged_in(): bool {
    return !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && !empty($_SESSION['admin_id']);
}

/**
 * Mendapatkan data admin yang sedang login
 */
function get_current_admin(): ?array {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id' => $_SESSION['admin_id'] ?? null,
        'username' => $_SESSION['admin_username'] ?? '',
        'nama_lengkap' => $_SESSION['admin_name'] ?? $_SESSION['admin_username'] ?? 'Admin'
    ];
}

/**
 * Wajibkan login sebelum mengakses halaman dashboard
 */
function require_auth(): void {
    if (!is_logged_in()) {
        $loginUrl = '../login.php';
        // Sesuaikan path jika dipanggil dari root
        if (file_exists('login.php')) {
            $loginUrl = 'login.php';
        }
        header("Location: " . $loginUrl);
        exit;
    }
}
