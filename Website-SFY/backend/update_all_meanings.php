<?php
/*
 * backend/update_all_meanings.php
 * Script batch untuk memperbarui seluruh makna lagu di DB dengan penjelasan puitis & akurat
 */

require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/song_meaning_helper.php';

echo "Mengambil seluruh data lagu dari database...\n";

try {
    $res = $conn->query("SELECT id, title, artist, meaning FROM songs");
    $rows = $res->fetchAll();
    $updatedCount = 0;

    foreach ($rows as $row) {
        $id     = (int)$row['id'];
        $title  = $row['title'];
        $artist = $row['artist'];

        $newMeaning = getSongMeaning($title, $artist);

        $stmt = $conn->prepare("UPDATE songs SET meaning = :meaning WHERE id = :id");
        if ($stmt->execute([':meaning' => $newMeaning, ':id' => $id])) {
            $updatedCount++;
        }
    }

    echo "BERHASIL memperbarui $updatedCount lagu dengan makna lagu yang akurat dan puitis!\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
