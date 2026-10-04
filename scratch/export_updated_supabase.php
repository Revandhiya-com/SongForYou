<?php
require_once __DIR__ . '/../Website-SFY/backend/koneksi.php';

echo "Exporting updated songs and messages to database_supabase.sql...\n";

$stmtSongs = $conn->query("SELECT * FROM songs ORDER BY id ASC");
$songs = $stmtSongs->fetchAll();

$stmtMsgs = $conn->query("SELECT * FROM messages ORDER BY id ASC");
$msgs = $stmtMsgs->fetchAll();

$sql = "-- Database Supabase Export SFY\n";
$sql .= "DROP TABLE IF EXISTS messages CASCADE;\n";
$sql .= "DROP TABLE IF EXISTS songs CASCADE;\n";
$sql .= "DROP TABLE IF EXISTS users CASCADE;\n\n";

$sql .= "CREATE TABLE songs (\n";
$sql .= "    id SERIAL PRIMARY KEY,\n";
$sql .= "    spotify_id VARCHAR(255) NOT NULL UNIQUE,\n";
$sql .= "    title VARCHAR(255) NOT NULL,\n";
$sql .= "    artist VARCHAR(255) NOT NULL,\n";
$sql .= "    cover_url TEXT NOT NULL,\n";
$sql .= "    meaning TEXT DEFAULT NULL,\n";
$sql .= "    spotify_url TEXT NOT NULL,\n";
$sql .= "    preview_url TEXT DEFAULT NULL,\n";
$sql .= "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n";
$sql .= ");\n\n";

$sql .= "CREATE TABLE users (\n";
$sql .= "    id SERIAL PRIMARY KEY,\n";
$sql .= "    username VARCHAR(100) NOT NULL UNIQUE,\n";
$sql .= "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n";
$sql .= ");\n\n";

$sql .= "CREATE TABLE messages (\n";
$sql .= "    id SERIAL PRIMARY KEY,\n";
$sql .= "    user_id INT DEFAULT 0,\n";
$sql .= "    song_id INT NOT NULL,\n";
$sql .= "    recipient_name VARCHAR(100) NOT NULL,\n";
$sql .= "    sender_name VARCHAR(100) DEFAULT NULL,\n";
$sql .= "    message TEXT NOT NULL,\n";
$sql .= "    images TEXT DEFAULT NULL,\n";
$sql .= "    slug VARCHAR(255) NOT NULL UNIQUE,\n";
$sql .= "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
$sql .= "    CONSTRAINT fk_messages_songs FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE\n";
$sql .= ");\n\n";

foreach ($songs as $s) {
    $spId = addslashes($s['spotify_id']);
    $title = addslashes($s['title']);
    $artist = addslashes($s['artist']);
    $cover = addslashes($s['cover_url']);
    $meaning = isset($s['meaning']) ? "'" . addslashes($s['meaning']) . "'" : "NULL";
    $surl = addslashes($s['spotify_url']);
    $purl = isset($s['preview_url']) && $s['preview_url'] !== '' ? "'" . addslashes($s['preview_url']) . "'" : "NULL";
    
    $sql .= "INSERT INTO songs (id, spotify_id, title, artist, cover_url, meaning, spotify_url, preview_url) VALUES ({$s['id']}, '{$spId}', '{$title}', '{$artist}', '{$cover}', {$meaning}, '{$surl}', {$purl}) ON CONFLICT (id) DO UPDATE SET meaning = EXCLUDED.meaning, preview_url = EXCLUDED.preview_url;\n";
}

foreach ($msgs as $m) {
    $uid = (int)($m['user_id'] ?? 0);
    $sid = (int)$m['song_id'];
    $rec = addslashes($m['recipient_name']);
    $snd = isset($m['sender_name']) ? "'" . addslashes($m['sender_name']) . "'" : "NULL";
    $msg = addslashes($m['message']);
    $img = isset($m['images']) && $m['images'] !== '' ? "'" . addslashes($m['images']) . "'" : "NULL";
    $slug = addslashes($m['slug']);

    $sql .= "INSERT INTO messages (id, user_id, song_id, recipient_name, sender_name, message, images, slug) VALUES ({$m['id']}, {$uid}, {$sid}, '{$rec}', {$snd}, '{$msg}', {$img}, '{$slug}') ON CONFLICT (id) DO NOTHING;\n";
}

file_put_contents(__DIR__ . '/../Website-SFY/database_supabase.sql', $sql);
echo "Successfully updated Website-SFY/database_supabase.sql!\n";
?>
