<?php
// scratch/build_batched_migration.php
// Builds api/run_migration.php with embedded local MySQL data

$local = new PDO("mysql:host=localhost;dbname=songforyou;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$songs    = $local->query("SELECT * FROM songs ORDER BY id")->fetchAll();
$messages = $local->query("SELECT * FROM messages ORDER BY id")->fetchAll();

$songsJson    = json_encode($songs, JSON_UNESCAPED_UNICODE);
$messagesJson = json_encode($messages, JSON_UNESCAPED_UNICODE);

$template = file_get_contents(__DIR__ . '/migration_template.php');

// Replace placeholders
$output = str_replace('SONGS_PLACEHOLDER', addcslashes($songsJson, '\\'), $template);
$output = str_replace('MESSAGES_PLACEHOLDER', addcslashes($messagesJson, '\\'), $output);

$outPath = dirname(__DIR__) . '/api/run_migration.php';
file_put_contents($outPath, $output);

$sizeMB = round(filesize($outPath) / 1024 / 1024, 2);
echo "BUILT: api/run_migration.php ({$sizeMB} MB)\n";
echo "Songs embedded: " . count($songs) . "\n";
echo "Messages embedded: " . count($messages) . "\n";
echo "Total batches needed: " . ceil(count($songs) / 80) . "\n";
?>
