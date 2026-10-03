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

// Auto-build pooler username: postgres.{project-ref}
$poolerUser = $user;
if (preg_match('/^db\.([a-z0-9]+)\.supabase\.co$/', $host, $m)) {
    $poolerUser = 'postgres.' . $m[1];
} elseif (strpos($user, '.') !== false) {
    $poolerUser = $user; // already pooler format
}

$results = [];
$attempts = [
    ["pgsql:host={$host};port=6543;dbname={$db};sslmode=require", $poolerUser],
    ["pgsql:host={$host};port=6543;dbname={$db};sslmode=prefer",  $poolerUser],
    ["pgsql:host={$host};port=5432;dbname={$db};sslmode=require", $poolerUser],
    ["pgsql:host={$host};port=5432;dbname={$db};sslmode=require", $user],
    ["pgsql:host={$host};port=5432;dbname={$db};sslmode=prefer",  $user],
];

foreach ($attempts as [$dsn, $dbUser]) {
    try {
        $conn = new PDO($dsn, $dbUser, $pass, [PDO::ATTR_TIMEOUT => 5]);
        $results[] = ['dsn' => $dsn, 'user' => $dbUser, 'status' => 'SUCCESS'];
        $conn = null;
        break;
    } catch (PDOException $e) {
        $results[] = ['dsn' => $dsn, 'user' => $dbUser, 'status' => 'FAIL', 'error' => $e->getMessage()];
    }
}

echo json_encode([
    'host'       => substr($host, 0, 15) . '***',
    'poolerUser' => $poolerUser,
    'directUser' => $user,
    'attempts'   => $results
], JSON_PRETTY_PRINT);
?>
