<?php
require_once __DIR__ . '/includes/auth.php';

// Hapus semua variabel sesi
$_SESSION = [];

// Hapus cookie sesi
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan sesi
session_destroy();

// Redirect ke login
header("Location: login.php");
exit;
