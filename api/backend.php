<?php
// api/backend.php - Proxy untuk backend PHP files
$root = dirname(__DIR__);
$endpoint = $_GET['endpoint'] ?? '';

// Whitelist endpoint yang diizinkan
$allowed = [
    'get_messages.php',
    'get_songs.php',
    'submit_message.php',
    'delete_message.php',
    'spotify_search.php',
    'spotify_token.php',
    'update_all_meanings.php',
];

if (!in_array(basename($endpoint), $allowed)) {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
    exit;
}

chdir($root . '/Website-SFY/backend');
require $root . '/Website-SFY/backend/' . basename($endpoint);
