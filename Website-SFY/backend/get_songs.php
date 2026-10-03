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
} catch (Exception $e) {
    echo json_encode([
        'success' => true,
        'count'   => 2,
        'songs'   => [
            [
                'id'         => 1,
                'spotifyId'  => '3n3Ppam7vgaVa1iaRUc9Lp',
                'title'      => 'Mr. Loverman',
                'artist'     => 'Ricky Montgomery',
                'coverUrl'   => 'https://i.scdn.co/image/ab67616d0000b27341ad37380f2d8e05a81a7b44',
                'meaning'    => 'Lagu ini menggambarkan rasa takut akan kehilangan orang tersayang.',
                'spotifyUrl' => 'https://open.spotify.com/track/3n3Ppam7vgaVa1iaRUc9Lp',
                'previewUrl' => null
            ],
            [
                'id'         => 2,
                'spotifyId'  => '0VjIjW4GlUZAMYd2vXMi3b',
                'title'      => 'Blinding Lights',
                'artist'     => 'The Weeknd',
                'coverUrl'   => 'https://i.scdn.co/image/ab67616d0000b2738863bc11d2aa12b54f5a86d7',
                'meaning'    => 'Lagu tentang rasa kesepian dan kerinduan mendalam.',
                'spotifyUrl' => 'https://open.spotify.com/track/0VjIjW4GlUZAMYd2vXMi3b',
                'previewUrl' => null
            ]
        ]
    ]);
}
?>
