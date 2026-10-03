<?php
/*
 * backend/spotify_config.php
 * Konfigurasi API Spotify (PHP Native)
 */

if (!defined('SPOTIFY_CLIENT_ID')) {
    // Client ID dari Spotify Developer Dashboard Anda
    define('SPOTIFY_CLIENT_ID', '538fabff2f5043e88129694663ca5a36');
}

if (!defined('SPOTIFY_CLIENT_SECRET')) {
    // Masukkan Client Secret dari Spotify Developer Dashboard Anda di sini
    // (Jika belum ada, otomatis menggunakan fallback credential agar API tetap live)
    define('SPOTIFY_CLIENT_SECRET', '9c635c0d86804c9aaccd79945965fdfa');
}
?>
