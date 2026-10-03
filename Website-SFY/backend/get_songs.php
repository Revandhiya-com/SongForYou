<?php
/*
 * get_songs.php
 * Mengambil daftar lagu dari tabel songs.
 *
 * Method  : GET
 * Response: JSON { success, songs[] }
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/koneksi.php';

try {
    $stmt = $conn->query(
        "SELECT id, spotify_id, title, artist, cover_url, meaning, spotify_url, preview_url
         FROM songs
         ORDER BY id DESC"
    );
    $rows = $stmt->fetchAll();

    $songs = [];
    foreach ($rows as $row) {
        $songs[] = [
            'id'         => (int)$row['id'],
            'spotifyId'  => $row['spotify_id'],
            'title'      => $row['title'],
            'artist'     => $row['artist'],
            'coverUrl'   => $row['cover_url'],
            'meaning'    => $row['meaning'],
            'spotifyUrl' => $row['spotify_url'],
            'previewUrl' => $row['preview_url']
        ];
    }

    echo json_encode([
        'success' => true,
        'count'   => count($songs),
        'songs'   => $songs
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Query gagal: ' . $e->getMessage()]);
}
?>
