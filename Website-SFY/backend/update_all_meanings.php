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
    $skippedCount = 0;

    foreach ($rows as $row) {
        $id     = (int)$row['id'];
        $title  = $row['title'];
        $artist = $row['artist'];
        $storedMeaning = trim((string)($row['meaning'] ?? ''));

        // Jangan menimpa makna yang sudah dikurasi. Batch ini hanya menangani
        // template lama/kosong dan tidak pernah menyimpan hasil yang belum
        // dapat diverifikasi.
        if (hasUsableSongMeaning($storedMeaning)) {
            $skippedCount++;
            continue;
        }
        $newMeaning = getSongMeaning($title, $artist);
        if (!hasUsableSongMeaning($newMeaning)) {
            $skippedCount++;
            continue;
        }

        $stmt = $conn->prepare("UPDATE songs SET meaning = :meaning WHERE id = :id");
        if ($stmt->execute([':meaning' => $newMeaning, ':id' => $id])) {
            $updatedCount++;
        }
    }

    echo "BERHASIL memperbarui $updatedCount makna terverifikasi. $skippedCount lagu dipertahankan karena maknanya sudah dikurasi atau belum dapat diverifikasi.\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
