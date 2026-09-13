# MEMORion+ Backend & Progress Tracking Dashboard 🎮📊

Sistem backend berbasis **PHP Native** dan **MySQL** untuk menangani progress tracking, pencatatan log jawaban anak, validasi skor, manajemen API Key, serta Dashboard monitoring interaktif untuk tim **MEMORion+ (Vixies Studio)**.

---

## 📋 Fitur Utama

1. **Dashboard Monitoring Interaktif (Tailwind CSS CDN)**:
   - Statistik real-time (Total sesi pemain terdaftar, total teka-teki diselesaikan, rata-rata skor akurasi, total durasi pengerjaan).
   - Tabel 10 aktivitas respon log terbaru dari game engine.
2. **Data Log Respon & Jawaban Anak**:
   - Filter komprehensif berdasarkan: *Kode Pemain*, *Modul / Stage*, *Status Selesai*, dan *Rentang Tanggal*.
   - Pagination dinamis dan tampilan teks jawaban asli anak.
3. **Ekspor Data ke CSV / Excel**:
   - Download data log pengerjaan yang sudah difilter dalam format CSV (dilengkapi UTF-8 BOM untuk kompatibilitas penuh Microsoft Excel).
4. **Sistem Autentikasi Admin**:
   - Login sesi aman (`session_start()`, `password_verify()`, `password_hash()`, CSRF Protection).
   - Proteksi seluruh halaman dashboard dan sesi timeout.
5. **Manajemen API Key Aman**:
   - Generate API Key acak (`bin2hex(random_bytes(24))`).
   - Aktifkan / Nonaktifkan (Revoke) akses klien game kapan saja tanpa restart server.
   - Tombol cepat salin API Key ke clipboard.
6. **REST API Endpoint Game (Godot Ready)**:
   - Validasi header wajib `X-API-KEY`.
   - Menerima payload JSON dari game engine.
   - Status response standar HTTP (200 OK, 400 Bad Request, 401 Unauthorized, 500 Internal Server Error).

---

## 🗄️ Skema Database (MySQL / MariaDB)

| Tabel | Kolom | Keterangan |
|---|---|---|
| `admins` | `id`, `username`, `password_hash`, `nama_lengkap`, `created_at` | Akun administrator dashboard tim |
| `api_keys` | `id`, `api_key`, `client_name`, `status` (aktif/nonaktif), `created_at` | Kunci otentikasi engine game |
| `player_sessions` | `id`, `player_code`, `created_at`, `updated_at` | Identitas dan sesi unik anak/pemain |
| `puzzle_logs` | `id`, `player_code`, `stage_name`, `puzzle_id`, `jawaban_teks`, `skor_validasi`, `durasi_detik`, `status_selesai`, `created_at` | Log rekaman jawaban dan skor per puzzle |

---

## 🚀 Panduan Instalasi di Hosting / cPanel

### 1. Upload File ke File Manager
1. Masuk ke **cPanel Hosting** -> Buka **File Manager**.
2. Masuk ke folder root domain / subdomain Anda (misal `public_html` atau `public_html/api-memorion`).
3. Upload seluruh file repository ini ke direktori tersebut.

### 2. Buat Database MySQL & Import Schema
1. Di cPanel, buka menu **MySQL Databases**:
   - Buat database baru (misal: `u12345_memorion`).
   - Buat user database baru dan buat password yang kuat.
   - Hubungkan user ke database dengan checklist **ALL PRIVILEGES**.
2. Buka **phpMyAdmin**:
   - Pilih database yang baru dibuat.
   - Klik tab **Import** -> Pilih file [`schema.sql`](file:///home/enki/Memoorion/Database-Memorion/schema.sql) -> Klik **Go**.
   *(Atau alternatifnya: Akses `https://domain-anda.com/seed.php` sekali di browser untuk membuat tabel otomatis)*.

### 3. Konfigurasi Koneksi Database
Buka file [`config/config.php`](file:///home/enki/Memoorion/Database-Memorion/config/config.php) dan sesuaikan kredensial:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'nama_database_anda');
define('DB_USER', 'user_database_anda');
define('DB_PASS', 'password_database_anda');
define('DB_PORT', '3306');
```

### 4. Login ke Dashboard Pertama Kali
Buka `https://domain-anda.com/login.php` di browser:
- **Username Default**: `admin`
- **Password Default**: `password123`

> ⚠️ **Saran Keamanan**: Setelah berhasil login, buat API Key baru pada menu **API Keys** dan simpan kunci tersebut untuk konfigurasi di Godot.

---

## 📡 Dokumentasi Endpoint REST API

### 1. Submit Jawaban Teka-Teki (`/api/submit_answer.php`)
Digunakan setiap kali anak mengirimkan jawaban atau menyelesaikan puzzle.

- **Method**: `POST`
- **URL**: `https://domain-anda.com/api/submit_answer.php`
- **Headers**:
  ```http
  Content-Type: application/json
  X-API-KEY: mem_live_xxxx...
  ```
- **Payload Request (JSON)**:
  ```json
  {
    "player_code": "ANAK_001",
    "stage_name": "Stage 1 - Memori Visual",
    "puzzle_id": "PUZZLE_01",
    "jawaban_teks": "kucing warna putih berada di atas meja",
    "skor_validasi": 85.50,
    "durasi_detik": 24,
    "status_selesai": 1
  }
  ```
- **Response Sukses (200 OK)**:
  ```json
  {
    "status": "success",
    "message": "Jawaban teka-teki berhasil disimpan.",
    "data": {
      "log_id": 1,
      "player_code": "ANAK_001",
      "stage_name": "Stage 1 - Memori Visual",
      "puzzle_id": "PUZZLE_01",
      "skor_validasi": 85.5,
      "durasi_detik": 24,
      "status_selesai": 1,
      "client_name": "Godot Client Game Build v1.0",
      "timestamp": "2026-09-13 11:50:00"
    }
  }
  ```

---

### 2. Tracking Progres Sesi (`/api/track_progress.php`)
Digunakan untuk sinkronisasi awal sesi bermain anak atau heartbeat progres.

- **Method**: `POST`
- **URL**: `https://domain-anda.com/api/track_progress.php`
- **Headers**:
  ```http
  Content-Type: application/json
  X-API-KEY: mem_live_xxxx...
  ```
- **Payload Request (JSON)**:
  ```json
  {
    "player_code": "ANAK_001"
  }
  ```
- **Response Sukses (200 OK)**:
  ```json
  {
    "status": "success",
    "message": "Progress tracking berhasil dicatat.",
    "data": {
      "player_code": "ANAK_001",
      "log_id": null,
      "total_puzzles_completed": 5,
      "average_score": 88.4,
      "total_duration_seconds": 180,
      "last_sync": "2026-09-13 11:50:00"
    }
  }
  ```

---

## 🕹️ Contoh Integrasi di Godot Engine (GDScript 4.x)

Tambahkan node `HTTPRequest` di scene game Anda atau attach script AI Manager berikut:

```gdscript
extends Node

const BASE_URL: String = "https://domain-anda.com/api"
const API_KEY: String = "mem_live_xxxx_masukkan_api_key_anda"

@onready var http_request: HTTPRequest = HTTPRequest.new()

func _ready() -> void:
	add_child(http_request)
	http_request.request_completed.connect(_on_request_completed)

# Fungsi untuk mengirim hasil pengerjaan puzzle anak ke server
func kirim_jawaban(player_code: String, stage: String, puzzle_id: String, jawaban: String, skor: float, durasi: int, selesai: bool = true) -> void:
	var url: String = BASE_URL + "/submit_answer.php"
	var headers: PackedStringArray = [
		"Content-Type: application/json",
		"X-API-KEY: " + API_KEY
	]
	
	var payload: Dictionary = {
		"player_code": player_code,
		"stage_name": stage,
		"puzzle_id": puzzle_id,
		"jawaban_teks": jawaban,
		"skor_validasi": skor,
		"durasi_detik": durasi,
		"status_selesai": 1 if selesai else 0
	}
	
	var json_data: String = JSON.stringify(payload)
	var error: Error = http_request.request(url, headers, HTTPClient.METHOD_POST, json_data)
	
	if error != OK:
		push_error("Gagal mengirim HTTP Request ke server MEMORion+: " + str(error))
	else:
		print("Mengirim data log jawaban untuk ", player_code, "...")

func _on_request_completed(result: int, response_code: int, headers: PackedStringArray, body: PackedByteArray) -> void:
	if response_code == 200:
		var response_text: String = body.get_string_from_utf8()
		var json_parse = JSON.parse_string(response_text)
		print("Berhasil disimpan ke server: ", json_parse)
	elif response_code == 401:
		push_error("Gagal Autentikasi: API Key tidak valid atau dinonaktifkan!")
	else:
		push_error("Server Error [HTTP %d]: %s" % [response_code, body.get_string_from_utf8()])
```

---

## 📁 Struktur Direktori Proyek

```text
Database-Memorion/
├── api/
│   ├── submit_answer.php     # Endpoint POST submit jawaban & log puzzle
│   └── track_progress.php    # Endpoint POST sinkronisasi progres & sesi pemain
├── config/
│   ├── config.php            # Pengaturan DB, Timezone, URL & Konstanta
│   └── database.php          # Singleton koneksi PDO MySQL
├── dashboard/
│   ├── index.php             # Overview & statistik ringkasan
│   ├── logs.php              # Tabel log jawaban & filter pencarian
│   ├── keys.php              # Manajemen & generator API Key
│   ├── sessions.php          # Daftar kode pemain & total progres
│   └── export.php            # Generator unduhan CSV/Excel
├── includes/
│   ├── auth.php              # Middleware autentikasi sesi admin
│   ├── functions.php         # Validasi API key, CSRF, formatting & helpers
│   ├── header.php            # Komponen navbar & styling Tailwind
│   └── footer.php            # Komponen footer & script clipboard
├── login.php                 # Form login admin
├── logout.php                # Handler logout & pembersihan sesi
├── index.php                 # Routing entry point utama
├── schema.sql                # Skema database MySQL siap import phpMyAdmin
├── seed.php                  # Skrip instalasi & seeder otomatis
├── .htaccess                 # Konfigurasi keamanan Apache / cPanel
└── README.md                 # Dokumentasi teknis & panduan lengkap
```

---
**MEMORion+** &copy; <?= date('Y') ?> Vixies Studio.
