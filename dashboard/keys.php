<?php
$pageTitle = 'Manajemen API Key';
require_once __DIR__ . '/../includes/header.php';

$pdo = get_db_connection();

// Handle Actions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        set_flash('error', 'Sesi keamanan tidak valid. Silakan coba kembali.');
        header('Location: keys.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    // 1. Generate API Key Baru
    if ($action === 'create') {
        $clientName = trim($_POST['client_name'] ?? '');
        if (empty($clientName)) {
            set_flash('error', 'Nama client / perangkat wajib diisi.');
        } else {
            $newKey = generate_api_key();
            try {
                $stmt = $pdo->prepare("INSERT INTO api_keys (api_key, client_name, status, created_at) VALUES (:key, :name, 'aktif', NOW())");
                $stmt->execute(['key' => $newKey, 'name' => $clientName]);
                set_flash('success', "API Key baru untuk '$clientName' berhasil dibuat!");
            } catch (PDOException $e) {
                set_flash('error', 'Gagal membuat API Key: ' . $e->getMessage());
            }
        }
        header('Location: keys.php');
        exit;
    }

    // 2. Toggle Status (Aktif / Nonaktif / Revoke)
    if ($action === 'toggle_status') {
        $keyId = intval($_POST['key_id'] ?? 0);
        $currentStatus = $_POST['current_status'] ?? '';
        $newStatus = ($currentStatus === 'aktif') ? 'nonaktif' : 'aktif';

        try {
            $stmt = $pdo->prepare("UPDATE api_keys SET status = :status WHERE id = :id");
            $stmt->execute(['status' => $newStatus, 'id' => $keyId]);
            set_flash('success', "Status API Key berhasil diubah menjadi '$newStatus'.");
        } catch (PDOException $e) {
            set_flash('error', 'Gagal memperbarui status API Key: ' . $e->getMessage());
        }
        header('Location: keys.php');
        exit;
    }

    // 3. Regenerate Key Baru untuk Client yang Sama
    if ($action === 'regenerate') {
        $keyId = intval($_POST['key_id'] ?? 0);
        $newKey = generate_api_key();

        try {
            $stmt = $pdo->prepare("UPDATE api_keys SET api_key = :new_key, status = 'aktif' WHERE id = :id");
            $stmt->execute(['new_key' => $newKey, 'id' => $keyId]);
            set_flash('success', 'API Key berhasil diregenerasi dengan kunci baru.');
        } catch (PDOException $e) {
            set_flash('error', 'Gagal meregenerasi API Key: ' . $e->getMessage());
        }
        header('Location: keys.php');
        exit;
    }

    // 4. Hapus API Key
    if ($action === 'delete') {
        $keyId = intval($_POST['key_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM api_keys WHERE id = :id");
            $stmt->execute(['id' => $keyId]);
            set_flash('success', 'API Key berhasil dihapus dari database.');
        } catch (PDOException $e) {
            set_flash('error', 'Gagal menghapus API Key: ' . $e->getMessage());
        }
        header('Location: keys.php');
        exit;
    }
}

// Fetch all keys
$stmt = $pdo->query("SELECT * FROM api_keys ORDER BY id DESC");
$apiKeys = $stmt->fetchAll();
?>

<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-white p-6 rounded-2xl border border-slate-200 shadow-sm gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen API Key</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola kunci akses komunikasi data antara Game Engine (Godot) dan backend server.</p>
        </div>
        <button onclick="document.getElementById('modal-create-key').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-sm transition-colors">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Generate API Key Baru
        </button>
    </div>

    <!-- Security Information Box -->
    <div class="bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 p-5 rounded-2xl flex items-start space-x-3">
        <div class="p-2 bg-emerald-500 text-white rounded-xl flex-shrink-0 mt-0.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="text-xs text-emerald-900 leading-relaxed">
            <span class="font-bold text-sm block mb-1">Panduan Penggunaan API Key di Game Engine</span>
            Lampirkan API Key yang berstatus <strong>Aktif</strong> pada request header <code>X-API-KEY: [API_KEY]</code> saat engine game mengirim data ke <code>/api/submit_answer.php</code> atau <code>/api/track_progress.php</code>. Jika status diubah menjadi <strong>Nonaktif</strong>, game tidak akan bisa mengirim data.
        </div>
    </div>

    <!-- API Keys Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-100 text-slate-600 font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">ID</th>
                        <th class="py-3.5 px-4">Nama Client / Perangkat</th>
                        <th class="py-3.5 px-4 min-w-[280px]">API Key Hash (Klik Salin)</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Dibuat Pada</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($apiKeys)): ?>
                        <?php foreach ($apiKeys as $key): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-4 text-center text-slate-400 font-mono">
                                    <?= $key['id'] ?>
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-900">
                                    <?= e($key['client_name']) ?>
                                </td>
                                <td class="py-3 px-4 font-mono">
                                    <div class="flex items-center space-x-2 bg-slate-50 border border-slate-200 rounded-lg p-1.5 max-w-md">
                                        <input type="password" value="<?= e($key['api_key']) ?>" readonly id="key-input-<?= $key['id'] ?>" class="bg-transparent border-0 text-slate-700 text-xs flex-1 focus:outline-none font-mono select-all">
                                        <button type="button" onclick="const input = document.getElementById('key-input-<?= $key['id'] ?>'); input.type = input.type === 'password' ? 'text' : 'password';" class="text-slate-400 hover:text-slate-600 p-1" title="Lihat/Sembunyikan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                        <button type="button" onclick="copyToClipboard('<?= e($key['api_key']) ?>', 'copy-btn-<?= $key['id'] ?>')" id="copy-btn-<?= $key['id'] ?>" class="px-2 py-1 bg-white hover:bg-slate-100 border border-slate-200 rounded text-[11px] font-semibold text-slate-700 transition-colors shadow-xs">
                                            Salin
                                        </button>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <?php if ($key['status'] === 'aktif'): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                            Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span>
                                            Nonaktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-slate-500 whitespace-nowrap">
                                    <?= format_datetime($key['created_at']) ?>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center space-x-1.5">
                                        <!-- Toggle Status Form -->
                                        <form method="POST" action="keys.php" onsubmit="return confirm('Ubah status API Key ini?');" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="key_id" value="<?= $key['id'] ?>">
                                            <input type="hidden" name="current_status" value="<?= $key['status'] ?>">
                                            <button type="submit" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold <?= $key['status'] === 'aktif' ? 'bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200' ?>">
                                                <?= $key['status'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?>
                                            </button>
                                        </form>

                                        <!-- Regenerate Key Form -->
                                        <form method="POST" action="keys.php" onsubmit="return confirm('PERINGATAN: Kunci lama akan diganti dengan kunci baru. Anda harus mengupdate settingan di game engine. Lanjutkan?');" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="action" value="regenerate">
                                            <input type="hidden" name="key_id" value="<?= $key['id'] ?>">
                                            <button type="submit" class="p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors" title="Regenerate Key">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            </button>
                                        </form>

                                        <!-- Delete Form -->
                                        <form method="POST" action="keys.php" onsubmit="return confirm('Apakah Anda yakin ingin menghapus API Key ini secara permanen?');" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="key_id" value="<?= $key['id'] ?>">
                                            <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Key">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400">
                                Belum ada API Key yang dibuat. Klik tombol "Generate API Key Baru" di atas.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Dialog: Create New Key -->
<div id="modal-create-key" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-100 transform transition-all">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-slate-900">Buat API Key Baru</h3>
            <button onclick="document.getElementById('modal-create-key').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="keys.php" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="create">

            <div>
                <label for="client_name" class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Client / Perangkat</label>
                <input type="text" id="client_name" name="client_name" required placeholder="Contoh: Godot Build Android / Tablet Lab 1"
                       class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                <p class="text-[11px] text-slate-500 mt-1">Gunakan nama yang mudah dikenali untuk mengidentifikasi asal perangkat pengujian.</p>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-create-key').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-sm transition-colors">
                    Generate Key
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
