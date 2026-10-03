<?php
// api/backend.php - Proxy untuk backend PHP files
$root = dirname(__DIR__);
$endpoint = $_GET['endpoint'] ?? '';

$allowed = [
    'get_messages.php',
    'get_songs.php',
    'submit_message.php',
    'delete_message.php',
    'spotify_search.php',
    'spotify_token.php',
    'update_all_meanings.php',
];

$cleanEndpoint = basename($endpoint);
if (empty($cleanEndpoint) || !in_array($cleanEndpoint, $allowed)) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Endpoint not found', 'requested' => $endpoint]);
    exit;
}

chdir($root . '/Website-SFY/backend');
set_include_path($root . '/Website-SFY/backend' . PATH_SEPARATOR . $root . '/Website-SFY');
require $root . '/Website-SFY/backend/' . $cleanEndpoint;
