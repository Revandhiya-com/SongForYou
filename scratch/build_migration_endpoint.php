<?php
// scratch/build_migration_endpoint.php - Generate api/run_migration.php with local MySQL data
$local = new PDO("mysql:host=localhost;dbname=songforyou;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$songs = $local->query("SELECT * FROM songs")->fetchAll();
$messages = $local->query("SELECT * FROM messages")->fetchAll();

$songsJson = json_encode($songs);
$messagesJson = json_encode($messages);

$phpCode = '<?php
// api/run_migration.php - Self-contained migration endpoint on Vercel
set_time_limit(300);
ini_set("memory_limit", "512M");
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../Website-SFY/backend/koneksi.php";

if (!$conn || $dbDriver !== "pgsql") {
    echo json_encode(["error" => "PostgreSQL connection not active"]);
    exit;
}

$songs = json_decode(' . var_export($songsJson, true) . ', true);
$messages = json_decode(' . var_export($messagesJson, true) . ', true);

$songIdMap = [];
$stmtCheckSong = $conn->prepare("SELECT id FROM songs WHERE spotify_id = :spotify_id LIMIT 1");
$stmtInsertSong = $conn->prepare("
    INSERT INTO songs (spotify_id, title, artist, cover_url, meaning, spotify_url, preview_url, created_at)
    VALUES (:spotify_id, :title, :artist, :cover_url, :meaning, :spotify_url, :preview_url, :created_at)
    RETURNING id
");

$songSuccess = 0;
foreach ($songs as $s) {
    $stmtCheckSong->execute([":spotify_id" => $s["spotify_id"]]);
    $existing = $stmtCheckSong->fetch();
    if ($existing) {
        $songIdMap[$s["id"]] = (int)$existing["id"];
    } else {
        try {
            $stmtInsertSong->execute([
                ":spotify_id"  => $s["spotify_id"],
                ":title"       => $s["title"],
                ":artist"      => $s["artist"],
                ":cover_url"   => $s["cover_url"],
                ":meaning"     => $s["meaning"],
                ":spotify_url" => $s["spotify_url"],
                ":preview_url" => $s["preview_url"],
                ":created_at"  => $s["created_at"] ?? date("Y-m-d H:i:s")
            ]);
            $inserted = $stmtInsertSong->fetch();
            if ($inserted) {
                $songIdMap[$s["id"]] = (int)$inserted["id"];
                $songSuccess++;
            }
        } catch (Exception $e) {}
    }
}

$stmtCheckMsg = $conn->prepare("SELECT id FROM messages WHERE slug = :slug LIMIT 1");
$stmtInsertMsg = $conn->prepare("
    INSERT INTO messages (user_id, song_id, recipient_name, sender_name, message, images, slug, created_at)
    VALUES (:user_id, :song_id, :recipient_name, :sender_name, :message, :images, :slug, :created_at)
");

$msgSuccess = 0;
foreach ($messages as $m) {
    $stmtCheckMsg->execute([":slug" => $m["slug"]]);
    if (!$stmtCheckMsg->fetch()) {
        $newSongId = $songIdMap[$m["song_id"]] ?? null;
        if (!$newSongId) {
            $localSong = array_filter($songs, fn($ls) => $ls["id"] == $m["song_id"]);
            if ($localSong) {
                $ls = reset($localSong);
                $stmtCheckSong->execute([":spotify_id" => $ls["spotify_id"]]);
                $ex = $stmtCheckSong->fetch();
                if ($ex) $newSongId = (int)$ex["id"];
            }
        }

        if ($newSongId) {
            try {
                $stmtInsertMsg->execute([
                    ":user_id"        => $m["user_id"] ?? 0,
                    ":song_id"        => $newSongId,
                    ":recipient_name" => $m["recipient_name"],
                    ":sender_name"    => $m["sender_name"],
                    ":message"        => $m["message"],
                    ":images"         => $m["images"],
                    ":slug"           => $m["slug"],
                    ":created_at"     => $m["created_at"] ?? date("Y-m-d H:i:s")
                ]);
                $msgSuccess++;
            } catch (Exception $e) {}
        }
    }
}

$totalSongs = $conn->query("SELECT COUNT(*) AS cnt FROM songs")->fetch()["cnt"];
$totalMsgs  = $conn->query("SELECT COUNT(*) AS cnt FROM messages")->fetch()["cnt"];

echo json_encode([
    "success" => true,
    "songs_added" => $songSuccess,
    "messages_added" => $msgSuccess,
    "total_songs_in_supabase" => $totalSongs,
    "total_messages_in_supabase" => $totalMsgs
], JSON_PRETTY_PRINT);
?>';

file_put_contents(dirname(__DIR__) . '/api/run_migration.php', $phpCode);
echo "SUCCESSFULLY GENERATED api/run_migration.php (" . strlen($phpCode) . " bytes)\n";
?>
