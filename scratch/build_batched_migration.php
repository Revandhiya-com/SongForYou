<?php
// scratch/build_batched_migration.php
// Builds api/run_migration.php with PHP arrays embedded via var_export (no JSON decode issues)

$local = new PDO("mysql:host=localhost;dbname=songforyou;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$songs    = $local->query("SELECT * FROM songs ORDER BY id")->fetchAll();
$messages = $local->query("SELECT * FROM messages ORDER BY id")->fetchAll();

$songsExport    = var_export($songs, true);
$messagesExport = var_export($messages, true);

$totalSongs = count($songs);
$batchSize  = 80;
$totalBatches = ceil($totalSongs / $batchSize);

$php = '<?php
// api/run_migration.php - Batched migration. Call with ?batch=0, ?batch=1 ... ?batch=' . ($totalBatches - 1) . '
set_time_limit(25);
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../Website-SFY/backend/koneksi.php";

if (!$conn || $dbDriver !== "pgsql") {
    echo json_encode(["error" => "PostgreSQL not active", "dbDriver" => $dbDriver ?? "none"]);
    exit;
}

$batch     = isset($_GET["batch"]) ? (int)$_GET["batch"] : 0;
$batchSize = ' . $batchSize . ';
$offset    = $batch * $batchSize;

$allSongs = ' . $songsExport . ';

$allMessages = ' . $messagesExport . ';

$totalSongs    = count($allSongs);
$totalMessages = count($allMessages);
$songBatch     = array_slice($allSongs, $offset, $batchSize);
$songsAdded    = 0;
$errors        = [];

$stmtInsert = $conn->prepare("
    INSERT INTO songs (spotify_id, title, artist, cover_url, meaning, spotify_url, preview_url, created_at)
    VALUES (:spotify_id, :title, :artist, :cover_url, :meaning, :spotify_url, :preview_url, :created_at)
    ON CONFLICT (spotify_id) DO NOTHING
");

foreach ($songBatch as $s) {
    try {
        $stmtInsert->execute([
            ":spotify_id"  => $s["spotify_id"],
            ":title"       => $s["title"],
            ":artist"      => $s["artist"],
            ":cover_url"   => $s["cover_url"],
            ":meaning"     => $s["meaning"],
            ":spotify_url" => $s["spotify_url"],
            ":preview_url" => $s["preview_url"],
            ":created_at"  => $s["created_at"] ?? date("Y-m-d H:i:s")
        ]);
        if ($stmtInsert->rowCount() > 0) $songsAdded++;
    } catch (Exception $e) {
        $errors[] = substr($e->getMessage(), 0, 100);
    }
}

$isDone = ($offset + $batchSize) >= $totalSongs;
$msgsAdded = 0;

if ($isDone) {
    $spotifyMap = [];
    foreach ($allSongs as $s) { $spotifyMap[$s["id"]] = $s["spotify_id"]; }

    $stmtCheckSong = $conn->prepare("SELECT id FROM songs WHERE spotify_id = :sid LIMIT 1");
    $stmtMsg = $conn->prepare("
        INSERT INTO messages (user_id, song_id, recipient_name, sender_name, message, images, slug, created_at)
        VALUES (:user_id, :song_id, :recipient_name, :sender_name, :message, :images, :slug, :created_at)
        ON CONFLICT (slug) DO NOTHING
    ");

    foreach ($allMessages as $m) {
        $spotifyId = $spotifyMap[$m["song_id"]] ?? null;
        if (!$spotifyId) continue;
        $stmtCheckSong->execute([":sid" => $spotifyId]);
        $row = $stmtCheckSong->fetch();
        if (!$row) continue;
        try {
            $stmtMsg->execute([
                ":user_id"        => (int)($m["user_id"] ?? 0),
                ":song_id"        => (int)$row["id"],
                ":recipient_name" => $m["recipient_name"],
                ":sender_name"    => $m["sender_name"],
                ":message"        => $m["message"],
                ":images"         => $m["images"] ?? "",
                ":slug"           => $m["slug"],
                ":created_at"     => $m["created_at"] ?? date("Y-m-d H:i:s")
            ]);
            if ($stmtMsg->rowCount() > 0) $msgsAdded++;
        } catch (Exception $e) {
            $errors[] = "msg:" . substr($e->getMessage(), 0, 80);
        }
    }
}

$totalInSupabase = (int)$conn->query("SELECT COUNT(*) FROM songs")->fetchColumn();
$msgsInSupabase  = (int)$conn->query("SELECT COUNT(*) FROM messages")->fetchColumn();

echo json_encode([
    "batch"             => $batch,
    "songs_added"       => $songsAdded,
    "messages_added"    => $msgsAdded,
    "done"              => $isDone,
    "total_local"       => $totalSongs,
    "total_in_supabase" => $totalInSupabase,
    "msgs_in_supabase"  => $msgsInSupabase,
    "next_batch"        => $isDone ? null : ($batch + 1),
    "errors"            => array_slice($errors, 0, 3)
], JSON_PRETTY_PRINT);
?>';

$outPath = dirname(__DIR__) . '/api/run_migration.php';
file_put_contents($outPath, $php);

$sizeMB = round(filesize($outPath) / 1024 / 1024, 2);
echo "BUILT: api/run_migration.php ({$sizeMB} MB)\n";
echo "Songs embedded: " . count($songs) . "\n";
echo "Messages embedded: " . count($messages) . "\n";
echo "Total batches: {$totalBatches}\n";
?>
