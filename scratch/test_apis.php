<?php
$tests = [
    ['title' => 'Bila', 'artist' => 'Raisa'],
    ['title' => 'Bila (Aku Jatuh Cinta)', 'artist' => 'Raisa'],
    ['title' => 'Usai Di Sini', 'artist' => 'Raisa'],
    ['title' => 'Kali Kedua', 'artist' => 'Raisa'],
    ['title' => 'Garis Terdepan', 'artist' => 'Fiersa Besari'],
    ['title' => 'Monokrom', 'artist' => 'Tulus'],
];

echo "=== TESTING ITUNES WITH country=ID ===\n";
foreach ($tests as $t) {
    $q = urlencode($t['title'] . ' ' . $t['artist']);
    $url = "https://itunes.apple.com/search?term={$q}&country=ID&media=music&entity=song&limit=5";
    $json = file_get_contents($url);
    $data = json_decode($json, true);
    
    echo "--- SEARCH: {$t['title']} - {$t['artist']} ---\n";
    if (!empty($data['results'])) {
        foreach ($data['results'] as $i => $track) {
            echo "  Result #$i: trackName='{$track['trackName']}', artistName='{$track['artistName']}', previewUrl='{$track['previewUrl']}'\n";
        }
    } else {
        echo "  NO RESULTS FOUND!\n";
    }
}

echo "\n=== TESTING DEEZER API ===\n";
foreach ($tests as $t) {
    $q = urlencode($t['title'] . ' ' . $t['artist']);
    $url = "https://api.deezer.com/search?q={$q}&limit=5";
    $json = file_get_contents($url);
    $data = json_decode($json, true);
    
    echo "--- DEEZER: {$t['title']} - {$t['artist']} ---\n";
    if (!empty($data['data'])) {
        foreach ($data['data'] as $i => $track) {
            echo "  Result #$i: title='{$track['title']}', artist='{$track['artist']['name']}', preview='{$track['preview']}'\n";
        }
    } else {
        echo "  NO RESULTS FOUND!\n";
    }
}
?>
