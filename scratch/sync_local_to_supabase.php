<?php
// scratch/sync_local_to_supabase.php - Migrate all songs and messages from local MySQL to Supabase Cloud
set_time_limit(300);
ini_set('memory_limit', '512M');

echo "=== MIGRATING LOCAL MYSQL TO SUPABASE CLOUD ===\n";

// 1. Connect to Local MySQL
try {
    $local = new PDO("mysql:host=localhost;dbname=songforyou;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "1. Connected to Local MySQL.\n";
} catch (Exception $e) {
    die("Error connecting to Local MySQL: " . $e->getMessage() . "\n");
}

// 2. Connect to Supabase PostgreSQL
$supaHost = "aws-0-ap-southeast-1.pooler.supabase.com";
$supaPort = 6543;
$supaUser = "postgres.xaqvthzzzvecvqvjlcpg";
$supaPass = "revandhiya23";
$supaDb   = "postgres";

try {
    $dsn = "pgsql:host={$supaHost};port={$supaPort};dbname={$supaDb};sslmode=require";
    $supa = new PDO($dsn, $supaUser, $supaPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    echo "2. Connected to Supabase PostgreSQL Cloud.\n";
} catch (Exception $e) {
    die("Error connecting to Supabase: " . $e->getMessage() . "\n");
}

// 3. Get songs and messages from local
$localSongs = $local->query("SELECT * FROM songs")->fetchAll();
$localMessages = $local->query("SELECT * FROM messages")->fetchAll();

echo "Local Songs Count: " . count($localSongs) . "\n";
echo "Local Messages Count: " . count($localMessages) . "\n";

// Map old song ID -> new song ID
$songIdMap = [];

// 4. Migrate Songs
echo "3. Migrating Songs...\n";
$stmtCheckSong = $supa->prepare("SELECT id FROM songs WHERE spotify_id = :spotify_id LIMIT 1");
$stmtInsertSong = $supa->prepare("
    INSERT INTO songs (spotify_id, title, artist, cover_url, meaning, spotify_url, preview_url, created_at)
    VALUES (:spotify_id, :title, :artist, :cover_url, :meaning, :spotify_url, :preview_url, :created_at)
    RETURNING id
");

$songSuccess = 0;
foreach ($localSongs as $s) {
    $stmtCheckSong->execute([':spotify_id' => $s['spotify_id']]);
    $existing = $stmtCheckSong->fetch();
    if ($existing) {
        $songIdMap[$s['id']] = (int)$existing['id'];
    } else {
        try {
            $stmtInsertSong->execute([
                ':spotify_id'  => $s['spotify_id'],
                ':title'       => $s['title'],
                ':artist'      => $s['artist'],
                ':cover_url'   => $s['cover_url'],
                ':meaning'     => $s['meaning'],
                ':spotify_url' => $s['spotify_url'],
                ':preview_url' => $s['preview_url'],
                ':created_at'  => $s['created_at'] ?? date('Y-m-d H:i:s')
            ]);
            $inserted = $stmtInsertSong->fetch();
            if ($inserted) {
                $songIdMap[$s['id']] = (int)$inserted['id'];
                $songSuccess++;
            }
        } catch (Exception $e) {
            // Echo error for song
        }
    }
}
echo "Migrated {$songSuccess} new songs into Supabase.\n";

// 5. Migrate Messages
echo "4. Migrating Messages...\n";
$stmtCheckMsg = $supa->prepare("SELECT id FROM messages WHERE slug = :slug LIMIT 1");
$stmtInsertMsg = $supa->prepare("
    INSERT INTO messages (user_id, song_id, recipient_name, sender_name, message, images, slug, created_at)
    VALUES (:user_id, :song_id, :recipient_name, :sender_name, :message, :images, :slug, :created_at)
");

$msgSuccess = 0;
foreach ($localMessages as $m) {
    $stmtCheckMsg->execute([':slug' => $m['slug']]);
    if (!$stmtCheckMsg->fetch()) {
        $newSongId = $songIdMap[$m['song_id']] ?? null;
        if (!$newSongId) {
            // Find song ID in supabase by local song spotify_id
            $localSong = array_filter($localSongs, fn($ls) => $ls['id'] == $m['song_id']);
            if ($localSong) {
                $ls = reset($localSong);
                $stmtCheckSong->execute([':spotify_id' => $ls['spotify_id']]);
                $ex = $stmtCheckSong->fetch();
                if ($ex) $newSongId = (int)$ex['id'];
            }
        }

        if ($newSongId) {
            try {
                $stmtInsertMsg->execute([
                    ':user_id'        => $m['user_id'] ?? 0,
                    ':song_id'        => $newSongId,
                    ':recipient_name' => $m['recipient_name'],
                    ':sender_name'    => $m['sender_name'],
                    ':message'        => $m['message'],
                    ':images'         => $m['images'],
                    ':slug'           => $m['slug'],
                    ':created_at'     => $m['created_at'] ?? date('Y-m-d H:i:s')
                ]);
                $msgSuccess++;
            } catch (Exception $e) {
                echo "Error inserting message {$m['slug']}: " . $e->getMessage() . "\n";
            }
        }
    }
}
echo "Migrated {$msgSuccess} messages into Supabase.\n";

// 6. Verify totals in Supabase
$supaSongsCount = $supa->query("SELECT COUNT(*) AS cnt FROM songs")->fetch()['cnt'];
$supaMsgsCount  = $supa->query("SELECT COUNT(*) AS cnt FROM messages")->fetch()['cnt'];

echo "=== MIGRATION COMPLETE ===\n";
echo "Total Songs in Supabase: {$supaSongsCount}\n";
echo "Total Messages in Supabase: {$supaMsgsCount}\n";
?>
