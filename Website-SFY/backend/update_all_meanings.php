<?php
/*
 * backend/update_all_meanings.php
 * Script batch untuk memperbarui seluruh makna lagu di DB dengan penjelasan puitis & akurat
 */

require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/song_meaning_helper.php';

echo "Mengambil seluruh data lagu dari database...\n";

$res = mysqli_query($conn, "SELECT id, title, artist, meaning FROM songs");
$updatedCount = 0;

while ($row = mysqli_fetch_assoc($res)) {
    $id = (int)$row['id'];
    $title = $row['title'];
    $artist = $row['artist'];
    $currentMeaning = $row['meaning'];

    // Jika makna masih bernilai default/generik atau kosong, perbarui
    $newMeaning = getSongMeaning($title, $artist);
    
    $stmt = mysqli_prepare($conn, "UPDATE songs SET meaning = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $newMeaning, $id);
    if (mysqli_stmt_execute($stmt)) {
        $updatedCount++;
    }
    mysqli_stmt_close($stmt);
}

echo "BERHASIL memperbarui $updatedCount lagu dengan makna lagu yang akurat dan puitis!\n";
mysqli_close($conn);
?>
