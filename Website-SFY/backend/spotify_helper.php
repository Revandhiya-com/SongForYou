<?php
// spotify_helper.php

require_once __DIR__ . '/spotify_config.php';
define('TOKEN_FILE', sys_get_temp_dir() . '/spotify_token.json');

/**
 * Berfungsi mengambil Access Token yang valid.
 * Jika token di file lokal sudah expired/belum ada, otomatis menembak API Spotify untuk memperbaruinya.
 */
function getValidAccessToken() {
    // 1. Cek apakah file penyimpanan token sudah ada
    if (file_exists(TOKEN_FILE)) {
        $tokenData = json_decode(@file_get_contents(TOKEN_FILE), true);
        
        // Cek apakah token masih berlaku (diberi buffer 10 detik untuk amannya)
        if (isset($tokenData['access_token']) && isset($tokenData['expires_at']) && time() < ($tokenData['expires_at'] - 10)) {
            return $tokenData['access_token'];
        }
    }

    // 2. Jika tidak ada atau sudah expired, ambil token baru dari API Spotify (Client Credentials Flow)
    return refreshClientAccessToken();
}

/**
 * Melakukan request token baru ke Spotify menggunakan Client Credentials Flow
 */
function refreshClientAccessToken() {
    $url = 'https://accounts.spotify.com/api/token';
    
    // Header Authorization menggunakan Base64 dari client_id:client_secret
    $authHeader = base64_encode(SPOTIFY_CLIENT_ID . ':' . SPOTIFY_CLIENT_SECRET);
    
    $headers = [
        'Authorization: Basic ' . $authHeader,
        'Content-Type: application/x-www-form-urlencoded'
    ];
    
    $body = http_build_query([
        'grant_type' => 'client_credentials'
    ]);

    // Eksekusi menggunakan cURL bawaan PHP Native
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        
        // Hitung timestamp kapan token ini akan expired (waktu sekarang + 3600 detik)
        $data['expires_at'] = time() + ($data['expires_in'] ?? 3600);
        
        // Simpan ke file json lokal agar request berikutnya tidak perlu tembak API akun lagi
        @file_put_contents(TOKEN_FILE, json_encode($data));
        
        return $data['access_token'] ?? null;
    } else {
        return null;
    }
}

/**
 * Fungsi pembantu (Helper) untuk mempermudah HTTP GET Request ke Spotify API
 */
function spotifyGetRequest($endpoint, $queryParams = []) {
    $accessToken = getValidAccessToken();
    if (!$accessToken) {
        return ['error' => ['status' => 500, 'message' => 'Gagal mengambil Spotify Access Token']];
    }
    
    $url = 'https://api.spotify.com' . $endpoint;
    if (!empty($queryParams)) {
        $url .= '?' . http_build_query($queryParams);
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}
