<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

require_auth();

$admin = get_current_admin();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?><?= APP_NAME ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="h-full flex flex-col antialiased text-slate-800">

    <!-- Top Navigation Bar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Left: Logo & Title -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-emerald-400 flex items-center justify-center text-white shadow-md shadow-brand-500/20 font-extrabold text-lg">
                        M+
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="font-bold text-lg text-slate-900 tracking-tight">MEMORion<span class="text-brand-600">+</span></span>
                            <span class="px-2 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded-full">v1.0</span>
                        </div>
                        <p class="text-xs text-slate-500 hidden sm:block">Sistem Monitoring & Tracking Progres Game</p>
                    </div>
                </div>

                <!-- Center: Navigation Links -->
                <nav class="hidden md:flex items-center space-x-1">
                    <a href="index.php" class="px-3 py-2 rounded-lg text-sm font-medium transition-colors <?= $currentPage === 'index' ? 'bg-slate-100 text-brand-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' ?>">
                        <div class="flex items-center space-x-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span>Ringkasan</span>
                        </div>
                    </a>
                    <a href="logs.php" class="px-3 py-2 rounded-lg text-sm font-medium transition-colors <?= $currentPage === 'logs' ? 'bg-slate-100 text-brand-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' ?>">
                        <div class="flex items-center space-x-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Data Log Jawaban</span>
                        </div>
                    </a>
                    <a href="sessions.php" class="px-3 py-2 rounded-lg text-sm font-medium transition-colors <?= $currentPage === 'sessions' ? 'bg-slate-100 text-brand-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' ?>">
                        <div class="flex items-center space-x-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <span>Sesi Pemain</span>
                        </div>
                    </a>
                    <a href="keys.php" class="px-3 py-2 rounded-lg text-sm font-medium transition-colors <?= $currentPage === 'keys' ? 'bg-slate-100 text-brand-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' ?>">
                        <div class="flex items-center space-x-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                            <span>API Keys</span>
                        </div>
                    </a>
                </nav>

                <!-- Right: Admin Profile & Logout -->
                <div class="flex items-center space-x-3">
                    <div class="hidden sm:flex items-center space-x-2 bg-slate-100 px-3 py-1.5 rounded-full border border-slate-200 text-xs">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        <span class="font-medium text-slate-700"><?= e($admin['nama_lengkap']) ?></span>
                        <span class="text-slate-400">(@<?= e($admin['username']) ?>)</span>
                    </div>
                    <a href="../logout.php" class="inline-flex items-center px-3 py-1.5 border border-rose-200 text-xs font-semibold rounded-lg text-rose-700 bg-rose-50 hover:bg-rose-100 transition-colors shadow-sm">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Subbar -->
        <div class="md:hidden border-t border-slate-200 bg-slate-50 px-4 py-2 flex justify-around">
            <a href="index.php" class="text-xs font-medium <?= $currentPage === 'index' ? 'text-brand-700 font-bold' : 'text-slate-600' ?>">Ringkasan</a>
            <a href="logs.php" class="text-xs font-medium <?= $currentPage === 'logs' ? 'text-brand-700 font-bold' : 'text-slate-600' ?>">Log Jawaban</a>
            <a href="sessions.php" class="text-xs font-medium <?= $currentPage === 'sessions' ? 'text-brand-700 font-bold' : 'text-slate-600' ?>">Sesi Pemain</a>
            <a href="keys.php" class="text-xs font-medium <?= $currentPage === 'keys' ? 'text-brand-700 font-bold' : 'text-slate-600' ?>">API Keys</a>
        </div>
    </header>

    <!-- Flash Message Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        <?php if ($flash): ?>
            <?php
                $bgColor = 'bg-emerald-50 border-emerald-200 text-emerald-800';
                $icon = '<svg class="w-5 h-5 text-emerald-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                if ($flash['type'] === 'error') {
                    $bgColor = 'bg-rose-50 border-rose-200 text-rose-800';
                    $icon = '<svg class="w-5 h-5 text-rose-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                } elseif ($flash['type'] === 'warning') {
                    $bgColor = 'bg-amber-50 border-amber-200 text-amber-800';
                    $icon = '<svg class="w-5 h-5 text-amber-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>';
                }
            ?>
            <div class="flex items-center p-4 rounded-xl border <?= $bgColor ?> shadow-sm">
                <?= $icon ?>
                <span class="text-sm font-medium"><?= e($flash['message']) ?></span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main Content Body -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">
