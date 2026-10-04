<?php
// scratch/generate_supabase_sql.php
// Generates a complete PostgreSQL SQL file to import ALL local data into Supabase

$local = new PDO("mysql:host=localhost;dbname=songforyou;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$songs    = $local->query("SELECT * FROM songs ORDER BY id")->fetchAll();
$messages = $local->query("SELECT * FROM messages ORDER BY id")->fetchAll();

$out  = "-- =====================================================================\n";
$out .= "-- SongForYou Full Migration: MySQL Local -> Supabase PostgreSQL\n";
$out .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
$out .= "-- Songs: " . count($songs) . "  Messages: " . count($messages) . "\n";
$out .= "-- =====================================================================\n\n";

// Truncate existing data
$out .= "-- Step 1: Clear existing data\n";
$out .= "TRUNCATE TABLE messages RESTART IDENTITY CASCADE;\n";
$out .= "TRUNCATE TABLE songs RESTART IDENTITY CASCADE;\n\n";

// Helper: escape a string for PostgreSQL
function pgEscape($v) {
    if ($v === null) return 'NULL';
    return "'" . str_replace(["'", "\\"], ["''", "\\\\"], $v) . "'";
}

// Insert songs in batches of 50
$out .= "-- Step 2: Insert Songs (" . count($songs) . " records)\n";
$chunks = array_chunk($songs, 50);
foreach ($chunks as $chunk) {
    $out .= "INSERT INTO songs (id, spotify_id, title, artist, cover_url, meaning, spotify_url, preview_url, created_at) VALUES\n";
    $rows = [];
    foreach ($chunk as $s) {
        $rows[] = "(" . (int)$s['id'] . ", " . pgEscape($s['spotify_id']) . ", " . pgEscape($s['title']) . ", " . pgEscape($s['artist']) . ", " . pgEscape($s['cover_url']) . ", " . pgEscape($s['meaning']) . ", " . pgEscape($s['spotify_url']) . ", " . pgEscape($s['preview_url']) . ", " . pgEscape($s['created_at']) . ")";
    }
    $out .= implode(",\n", $rows) . "\nON CONFLICT (spotify_id) DO NOTHING;\n\n";
}

// Insert messages
$out .= "-- Step 3: Insert Messages (" . count($messages) . " records)\n";
if (count($messages) > 0) {
    $msgChunks = array_chunk($messages, 50);
    foreach ($msgChunks as $chunk) {
        $out .= "INSERT INTO messages (id, user_id, song_id, recipient_name, sender_name, message, images, slug, created_at) VALUES\n";
        $rows = [];
        foreach ($chunk as $m) {
            $rows[] = "(" . (int)$m['id'] . ", " . (int)$m['user_id'] . ", " . (int)$m['song_id'] . ", " . pgEscape($m['recipient_name']) . ", " . pgEscape($m['sender_name']) . ", " . pgEscape($m['message']) . ", " . pgEscape($m['images']) . ", " . pgEscape($m['slug']) . ", " . pgEscape($m['created_at']) . ")";
        }
        $out .= implode(",\n", $rows) . "\nON CONFLICT (slug) DO NOTHING;\n\n";
    }
}

// Reset sequences
$out .= "-- Step 4: Reset sequences to correct values\n";
$maxSongId = max(array_column($songs, 'id'));
$maxMsgId  = count($messages) > 0 ? max(array_column($messages, 'id')) : 0;
$out .= "SELECT setval('songs_id_seq', " . $maxSongId . ", true);\n";
if ($maxMsgId > 0) {
    $out .= "SELECT setval('messages_id_seq', " . $maxMsgId . ", true);\n";
}
$out .= "\n-- Done!\n";

$outFile = dirname(__DIR__) . '/scratch/supabase_import.sql';
file_put_contents($outFile, $out);

$sizeMB = round(strlen($out) / 1024 / 1024, 2);
echo "GENERATED: scratch/supabase_import.sql\n";
echo "Size: {$sizeMB} MB\n";
echo "Songs: " . count($songs) . "\n";
echo "Messages: " . count($messages) . "\n";
?>
