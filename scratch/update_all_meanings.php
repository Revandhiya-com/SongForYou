<?php
require_once __DIR__ . '/../Website-SFY/backend/koneksi.php';
require_once __DIR__ . '/../Website-SFY/backend/song_meaning_helper.php';

echo "Updating all song meanings in database...\n";

$stmt = $conn->query("SELECT id, title, artist, meaning FROM songs");
$songs = $stmt->fetchAll();

$updated = 0;
$stmtUpdate = $conn->prepare("UPDATE songs SET meaning = :meaning WHERE id = :id");

foreach ($songs as $s) {
    $newMeaning = getSongMeaning($s['title'], $s['artist']);
    
    // Always update if current meaning is generic or null or fallback
    if (empty($s['meaning']) || strpos($s['meaning'], 'mengabadikan perasaan emosional') !== false || strpos($s['meaning'], 'mewakili perasaan mendalam') !== false) {
        $stmtUpdate->execute([
            ':meaning' => $newMeaning,
            ':id'      => $s['id']
        ]);
        $updated++;
    }
}

echo "Successfully updated $updated / " . count($songs) . " songs with tailored meanings!\n";
?>
