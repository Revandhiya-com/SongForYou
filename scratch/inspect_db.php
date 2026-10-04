<?php
require_once __DIR__ . '/../Website-SFY/backend/koneksi.php';

$stmt = $conn->query("SELECT COUNT(*) as total FROM songs");
$total = $stmt->fetch()['total'];
echo "Total songs in DB: " . $total . "\n";

$stmt2 = $conn->query("SELECT id, title, artist, meaning FROM songs LIMIT 20");
while ($row = $stmt2->fetch()) {
    echo "#{$row['id']} - {$row['title']} - {$row['artist']} => {$row['meaning']}\n";
}
?>
