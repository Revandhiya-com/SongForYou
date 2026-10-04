<?php
// api/run_migration.php - Batched migration endpoint
// Call with ?batch=0, ?batch=1, ?batch=2 ... until done=true
set_time_limit(25);
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../Website-SFY/backend/koneksi.php";

if (!$conn || $dbDriver !== "pgsql") {
    echo json_encode(["error" => "PostgreSQL connection not active. dbDriver=" . ($dbDriver ?? 'none')]);
    exit;
}

$batch     = isset($_GET['batch']) ? (int)$_GET['batch'] : 0;
$batchSize = 80; // 80 songs per batch to stay within Vercel 10s timeout
$offset    = $batch * $batchSize;

// Embedded data - all songs from local MySQL
$allSongs = json_decode(<<<'JSONEOF'
SONGS_PLACEHOLDER
JSONEOF, true);

$allMessages = json_decode(<<<'JSONEOF'
MESSAGES_PLACEHOLDER
JSONEOF, true);

$totalSongs    = count($allSongs);
$totalMessages = count($allMessages);
$songBatch     = array_slice($allSongs, $offset, $batchSize);

$songsAdded = 0;
$errors = [];

// Insert songs for this batch
$stmtCheck  = $conn->prepare("SELECT id FROM songs WHERE spotify_id = :sid LIMIT 1");
$stmtInsert = $conn->prepare("
    INSERT INTO songs (spotify_id, title, artist, cover_url, meaning, spotify_url, preview_url, created_at)
    VALUES (:spotify_id, :title, :artist, :cover_url, :meaning, :spotify_url, :preview_url, :created_at)
    ON CONFLICT (spotify_id) DO NOTHING
");

foreach ($songBatch as $s) {
    try {
        $stmtInsert->execute([
            ':spotify_id'  => $s['spotify_id'],
            ':title'       => $s['title'],
            ':artist'      => $s['artist'],
            ':cover_url'   => $s['cover_url'],
            ':meaning'     => $s['meaning'],
            ':spotify_url' => $s['spotify_url'],
            ':preview_url' => $s['preview_url'],
            ':created_at'  => $s['created_at'] ?? date('Y-m-d H:i:s')
        ]);
        if ($stmtInsert->rowCount() > 0) $songsAdded++;
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

$isDone        = ($offset + $batchSize) >= $totalSongs;
$msgsAdded     = 0;
$totalInSupabase = (int)$conn->query("SELECT COUNT(*) FROM songs")->fetchColumn();

// Insert messages only when songs are all done (last batch)
if ($isDone) {
    $stmtCheckMsg  = $conn->prepare("SELECT id FROM messages WHERE slug = :slug LIMIT 1");
    $stmtCheckSong = $conn->prepare("SELECT id FROM songs WHERE spotify_id = :sid LIMIT 1");
    $stmtMsg = $conn->prepare("
        INSERT INTO messages (user_id, song_id, recipient_name, sender_name, message, images, slug, created_at)
        VALUES (:user_id, :song_id, :recipient_name, :sender_name, :message, :images, :slug, :created_at)
        ON CONFLICT (slug) DO NOTHING
    ");

    // Build spotify_id lookup from local songs
    $spotifyMap = [];
    foreach ($allSongs as $s) { $spotifyMap[$s['id']] = $s['spotify_id']; }

    foreach ($allMessages as $m) {
        $stmtCheckMsg->execute([':slug' => $m['slug']]);
        if ($stmtCheckMsg->fetch()) continue;

        $spotifyId = $spotifyMap[$m['song_id']] ?? null;
        if (!$spotifyId) continue;

        $stmtCheckSong->execute([':sid' => $spotifyId]);
        $row = $stmtCheckSong->fetch();
        if (!$row) continue;

        try {
            $stmtMsg->execute([
                ':user_id'        => (int)($m['user_id'] ?? 0),
                ':song_id'        => (int)$row['id'],
                ':recipient_name' => $m['recipient_name'],
                ':sender_name'    => $m['sender_name'],
                ':message'        => $m['message'],
                ':images'         => $m['images'] ?? '',
                ':slug'           => $m['slug'],
                ':created_at'     => $m['created_at'] ?? date('Y-m-d H:i:s')
            ]);
            if ($stmtMsg->rowCount() > 0) $msgsAdded++;
        } catch (Exception $e) {
            $errors[] = "msg:" . $e->getMessage();
        }
    }
}

echo json_encode([
    "batch"              => $batch,
    "songs_in_batch"     => count($songBatch),
    "songs_added"        => $songsAdded,
    "messages_added"     => $msgsAdded,
    "done"               => $isDone,
    "total_local_songs"  => $totalSongs,
    "total_in_supabase"  => $totalInSupabase,
    "next_batch"         => $isDone ? null : ($batch + 1),
    "errors"             => array_slice($errors, 0, 3)
], JSON_PRETTY_PRINT);
?>
