<?php
// api/debug_conn.php - TEMPORARY: test exact connection error
header('Content-Type: application/json');

function getDbEnv($key, $default = '') {
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
    $val = getenv($key);
    if ($val !== false && $val !== '') return $val;
    return $default;
}

$host = getDbEnv('DB_HOST');
$user = getDbEnv('DB_USER', 'postgres');
$pass = getDbEnv('DB_PASS');
$db   = getDbEnv('DB_NAME', 'postgres');
$port = getDbEnv('DB_PORT', '5432');

$results = [];

// Try each DSN
$attempts = [
    "pgsql:host={$host};port=5432;dbname={$db};sslmode=require",
    "pgsql:host={$host};port=6543;dbname={$db};sslmode=require",
    "pgsql:host={$host};port=5432;dbname={$db};sslmode=prefer",
    "pgsql:host={$host};port=6543;dbname={$db};sslmode=prefer",
];

foreach ($attempts as $dsn) {
    try {
        $conn = new PDO($dsn, $user, $pass, [PDO::ATTR_TIMEOUT => 5]);
        $results[] = ['dsn' => $dsn, 'status' => 'SUCCESS'];
        $conn = null;
        break;
    } catch (PDOException $e) {
        $results[] = ['dsn' => $dsn, 'status' => 'FAIL', 'error' => $e->getMessage()];
    }
}

echo json_encode(['host' => substr($host, 0, 12) . '***', 'attempts' => $results], JSON_PRETTY_PRINT);
?>
