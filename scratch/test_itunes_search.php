<?php
$tests = [
    ['title' => 'Bila', 'artist' => 'Raisa'],
    ['title' => 'Bila (Aku Jatuh Cinta)', 'artist' => 'Raisa'],
    ['title' => 'Usai Di Sini', 'artist' => 'Raisa'],
    ['title' => 'Kali Kedua', 'artist' => 'Raisa'],
    ['title' => 'Garis Terdepan', 'artist' => 'Fiersa Besari'],
    ['title' => 'Monokrom', 'artist' => 'Tulus'],
];

foreach ($tests as $t) {
    $q = urlencode($t['title'] . ' ' . $t['artist']);
    $url = "https://itunes.apple.com/search?term={$q}&media=music&entity=song&limit=5";
    $json = file_get_contents($url);
    $data = json_decode($json, true);
    
    echo "=== SEARCH: {$t['title']} - {$t['artist']} ===\n";
    if (!empty($data['results'])) {
        foreach ($data['results'] as $i => $track) {
            echo "  Result #$i: trackName='{$track['trackName']}', artistName='{$track['artistName']}', collectionName='{$track['collectionName']}', previewUrl='{$track['previewUrl']}'\n";
        }
    } else {
        echo "  NO RESULTS FOUND!\n";
    }
    echo "\n";
}
?>
