<?php
/**
 * KASIR - Sistem Kasir Digital
 * Konfigurasi Database
 * 
 * Edit file ini sesuai dengan konfigurasi MySQL Anda
 */

// ========== KONFIGURASI DATABASE ==========
define('DB_HOST', 'localhost');
define('DB_NAME', 'kasir');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ========== AUTO-SETUP ==========
// Set ini ke true jika ingin auto-setup database saat pertama kali
define('AUTO_SETUP', true);

// ========== INFO APLIKASI ==========
define('APP_NAME', 'KASIR - Kasir Digital');
define('APP_VERSION', '1.0.0');
define('APP_TOKO', 'Toko Makmur');

?>
