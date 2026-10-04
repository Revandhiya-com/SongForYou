<?php
// Shared migration logic
require_once __DIR__ . "/../Website-SFY/backend/koneksi.php";
if (!$conn || $dbDriver !== "pgsql") {
    echo json_encode(["error" => "PostgreSQL not active"]);
    exit;
}
$songsAdded = 0;
$errors = [];
$stmtInsert = $conn->prepare("
    INSERT INTO songs (spotify_id, title, artist, cover_url, meaning, spotify_url, preview_url, created_at)
    VALUES (:spotify_id, :title, :artist, :cover_url, :meaning, :spotify_url, :preview_url, :created_at)
    ON CONFLICT (spotify_id) DO NOTHING
");
foreach ($batchSongs as $s) {
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
    } catch (Exception $e) { $errors[] = substr($e->getMessage(), 0, 80); }
}
$msgsAdded = 0;
if ($isLastBatch) {
    $spotifyMap = [];
    foreach ($allLocalSongs as $s) { $spotifyMap[$s["id"]] = $s["spotify_id"]; }
    $stmtCheckSong = $conn->prepare("SELECT id FROM songs WHERE spotify_id = :sid LIMIT 1");
    $stmtMsg = $conn->prepare("INSERT INTO messages (user_id, song_id, recipient_name, sender_name, message, images, slug, created_at) VALUES (:user_id, :song_id, :recipient_name, :sender_name, :message, :images, :slug, :created_at) ON CONFLICT (slug) DO NOTHING");
    foreach ($allMessages as $m) {
        $spid = $spotifyMap[$m["song_id"]] ?? null;
        if (!$spid) continue;
        $stmtCheckSong->execute([":sid" => $spid]);
        $row = $stmtCheckSong->fetch();
        if (!$row) continue;
        try {
            $stmtMsg->execute([":user_id" => 0, ":song_id" => (int)$row["id"], ":recipient_name" => $m["recipient_name"], ":sender_name" => $m["sender_name"], ":message" => $m["message"], ":images" => $m["images"] ?? "", ":slug" => $m["slug"], ":created_at" => $m["created_at"] ?? date("Y-m-d H:i:s")]);
            if ($stmtMsg->rowCount() > 0) $msgsAdded++;
        } catch (Exception $e) { $errors[] = "msg:" . substr($e->getMessage(), 0, 50); }
    }
}
$totalInSupabase = (int)$conn->query("SELECT COUNT(*) FROM songs")->fetchColumn();
$msgsInSupabase  = (int)$conn->query("SELECT COUNT(*) FROM messages")->fetchColumn();
echo json_encode(["batch" => $batchNum, "songs_added" => $songsAdded, "messages_added" => $msgsAdded, "done" => $isLastBatch, "total_in_supabase" => $totalInSupabase, "msgs_in_supabase" => $msgsInSupabase, "next_batch" => $isLastBatch ? null : ($batchNum + 1), "errors" => array_slice($errors, 0, 3)], JSON_PRETTY_PRINT);
?>