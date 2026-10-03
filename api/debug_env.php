<?php
// api/debug_env.php - TEMPORARY: debug env vars (no sensitive values exposed)
header('Content-Type: application/json');
echo json_encode([
    'DB_HOST_SET'  => !empty(getenv('DB_HOST'))   ? substr(getenv('DB_HOST'), 0, 8) . '***' : 'NOT SET',
    'DB_USER_SET'  => !empty(getenv('DB_USER'))   ? 'SET ('  . getenv('DB_USER') . ')' : 'NOT SET',
    'DB_PASS_SET'  => !empty(getenv('DB_PASS'))   ? 'SET (length: ' . strlen(getenv('DB_PASS')) . ')' : 'NOT SET',
    'DB_NAME_SET'  => !empty(getenv('DB_NAME'))   ? 'SET ('  . getenv('DB_NAME') . ')' : 'NOT SET',
    'DB_PORT_SET'  => !empty(getenv('DB_PORT'))   ? 'SET ('  . getenv('DB_PORT') . ')' : 'NOT SET',
    'PHP_VERSION'  => PHP_VERSION,
    'PDO_DRIVERS'  => PDO::getAvailableDrivers(),
]);
?>
