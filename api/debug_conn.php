<?php
// api/debug_conn.php - TEMPORARY: test IPv4 Supabase pooler hosts
header('Content-Type: application/json');

function getDbEnv($key, $default = '') {
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
    $val = getenv($key);
    if ($val !== false && $val !== '') return $val;
    return $default;
}

$rawHost = getDbEnv('DB_HOST');
$user    = getDbEnv('DB_USER', 'postgres');
$pass    = getDbEnv('DB_PASS');
$db      = getDbEnv('DB_NAME', 'postgres');

// Extract project ref
$projectRef = 'xaqvthzzzvecvqvjlcpg';
if (preg_match('/^db\.([a-z0-9]+)\.supabase\.co$/', $rawHost, $m)) {
    $projectRef = $m[1];
}

$poolerUser = "postgres.{$projectRef}";

// List of possible Supabase Pooler regional hosts (all IPv4 compatible)
$regions = [
    'ap-southeast-1', // Singapore (Most likely for Indo)
    'us-east-1',      // N. Virginia
    'us-west-1',      // N. California
    'eu-central-1',   // Frankfurt
    'ap-northeast-1', // Tokyo
    'ap-south-1',     // Mumbai
    'sa-east-1',      // Sao Paulo
    'ca-central-1',   // Canada
    'eu-west-1',      // Ireland
    'eu-west-2',      // London
    'eu-west-3',      // Paris
    'ap-southeast-2'  // Sydney
];

$results = [];

// If DB_HOST is already a pooler host, test it first
$hostsToTest = [];
if (strpos($rawHost, 'pooler.supabase.com') !== false) {
    $hostsToTest[] = $rawHost;
}

foreach ($regions as $r) {
    $hostsToTest[] = "aws-0-{$r}.pooler.supabase.com";
}

foreach ($hostsToTest as $h) {
    $dsn6543 = "pgsql:host={$h};port=6543;dbname={$db};sslmode=require";
    try {
        $conn = new PDO($dsn6543, $poolerUser, $pass, [PDO::ATTR_TIMEOUT => 3]);
        $results[] = ['host' => $h, 'port' => 6543, 'status' => 'SUCCESS', 'user' => $poolerUser];
        $conn = null;
        break; // Stop on first success!
    } catch (PDOException $e) {
        $results[] = ['host' => $h, 'port' => 6543, 'status' => 'FAIL', 'error' => $e->getMessage()];
    }
}

echo json_encode([
    'projectRef' => $projectRef,
    'poolerUser' => $poolerUser,
    'tested'     => $results
], JSON_PRETTY_PRINT);
?>
