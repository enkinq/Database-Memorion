-- ==========================================================
-- Database Schema untuk Sistem Monitoring & Tracking MEMORion+
-- Engine: MySQL / MariaDB (Kompatibel phpMyAdmin cPanel)
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `memorion_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `memorion_db`;

-- ----------------------------------------------------------
-- 1. Tabel Admins (Autentikasi Dashboard Tim)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `nama_lengkap` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 2. Tabel API Keys (Manajemen Akses Game Engine)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `api_keys` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `api_key` VARCHAR(64) NOT NULL UNIQUE,
    `client_name` VARCHAR(100) NOT NULL,
    `status` ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 3. Tabel Player Sessions (Sesi & Identitas Pemain/Anak)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `player_sessions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `player_code` VARCHAR(100) NOT NULL UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_player_code` (`player_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 4. Tabel Puzzle Logs (Rekap Respon, Jawaban, Skor & Durasi)
-- ----------------------------------------------------------
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

-- ----------------------------------------------------------
-- Data Awal Default Admin & Sample API Key
-- Default Login:
-- Username: admin
-- Password: password123 (Silakan diganti setelah setup)
-- ----------------------------------------------------------
INSERT INTO `admins` (`id`, `username`, `password_hash`, `nama_lengkap`, `created_at`) 
VALUES (1, 'admin', '$2y$10$wT525U4fFq.B3Fsq98553Oeq/6n.55t18kK.6kFkW6sPqK1eTqNKy', 'Administrator MEMORion+', NOW())
ON DUPLICATE KEY UPDATE `id`=`id`;

INSERT INTO `api_keys` (`id`, `api_key`, `client_name`, `status`, `created_at`)
VALUES (1, 'mem_sec_live_9f8a3c4e2b1d6e7f8091a2b3c4d5e6f7', 'Godot Client Game Build v1.0', 'aktif', NOW())
ON DUPLICATE KEY UPDATE `id`=`id`;
