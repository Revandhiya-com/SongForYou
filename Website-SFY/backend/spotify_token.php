<?php
/*
 * backend/spotify_token.php
 * Endpoint untuk mengambil Spotify Access Token yang valid.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/spotify_helper.php';

$token = getValidAccessToken();

if ($token) {
    echo json_encode([
        'success'      => true,
        'access_token' => $token,
        'token_type'   => 'Bearer'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengambil access token Spotify'
    ]);
}
?>
