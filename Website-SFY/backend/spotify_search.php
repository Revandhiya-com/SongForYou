<?php
/*
 * backend/spotify_search.php
 * Endpoint PHP Native untuk pencarian lagu langsung dari Spotify Web API v1
 * Dilengkapi sinkronisasi otomatis ke Database MySQL `songs`
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/spotify_config.php';
require_once __DIR__ . '/song_meaning_helper.php';
@include_once __DIR__ . '/koneksi.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// ─── Function untuk mengambil Access Token Spotify ────────────────
function getSpotifyAccessToken() {
    $tokenFile = sys_get_temp_dir() . '/spotify_token.json';
    
    if (file_exists($tokenFile)) {
        $cache = json_decode(file_get_contents($tokenFile), true);
        if ($cache && isset($cache['access_token']) && isset($cache['expires_at']) && $cache['expires_at'] > time()) {
            return $cache['access_token'];
        }
    }

    $credentials = [
        ['id' => SPOTIFY_CLIENT_ID, 'secret' => SPOTIFY_CLIENT_SECRET],
        ['id' => '2a343d8fb9b14312af65ff93d65b530b', 'secret' => '9c635c0d86804c9aaccd79945965fdfa']
    ];

    foreach ($credentials as $cred) {
        if (empty($cred['id']) || empty($cred['secret'])) continue;

        $ch = curl_init('https://accounts.spotify.com/api/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode($cred['id'] . ':' . $cred['secret']),
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status === 200) {
            $data = json_decode($response, true);
            if ($data && isset($data['access_token'])) {
                $data['expires_at'] = time() + ($data['expires_in'] - 60);
                file_put_contents($tokenFile, json_encode($data));
                return $data['access_token'];
            }
        }
    }

    return null;
}

// Function pencari audio preview yang persis untuk lagu Spotify
function getExactAudioPreview($title, $artist) {
    $cleanTitle = trim(preg_replace('/\s*[\(\[\-].*$/', '', $title));
    $cleanArtist = trim(explode(',', explode('&', $artist)[0])[0]);
    $searchQuery = urlencode($cleanTitle . ' ' . $cleanArtist);

    // 1. iTunes API dengan country=ID
    $url = "https://itunes.apple.com/search?term={$searchQuery}&country=ID&media=music&entity=song&limit=5";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    $res = curl_exec($ch);
    curl_close($ch);
    
    if ($res) {
        $json = json_decode($res, true);
        if (!empty($json['results'])) {
            $tTitle = strtolower($cleanTitle);
            $tArtist = strtolower($cleanArtist);
            foreach ($json['results'] as $track) {
                if (empty($track['previewUrl'])) continue;
                $trTitle = strtolower($track['trackName'] ?? '');
                $trArtist = strtolower($track['artistName'] ?? '');
                if ((strpos($trTitle, $tTitle) !== false || strpos($tTitle, $trTitle) !== false) &&
                    (strpos($trArtist, $tArtist) !== false || strpos($tArtist, $trArtist) !== false)) {
                    return $track['previewUrl'];
                }
            }
            if (!empty($json['results'][0]['previewUrl'])) {
                return $json['results'][0]['previewUrl'];
            }
        }
    }

    // 2. Deezer API Fallback
    $url2 = "https://api.deezer.com/search?q={$searchQuery}&limit=5";
    $ch2 = curl_init($url2);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch2, CURLOPT_USERAGENT, 'Mozilla/5.0');
    $res2 = curl_exec($ch2);
    curl_close($ch2);

    if ($res2) {
        $json2 = json_decode($res2, true);
        if (!empty($json2['data'])) {
            foreach ($json2['data'] as $track) {
                if (!empty($track['preview'])) {
                    return $track['preview'];
                }
            }
        }
    }

    return null;
}

if (empty($q)) {
    $q = 'viral indonesia';
}

$accessToken = getSpotifyAccessToken();

if (!$accessToken) {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mendapatkan akses token dari Spotify API.',
        'tracks' => []
    ]);
    exit;
}

// ─── Tembak Spotify Search API ───────────────────────────────────
$searchUrl = 'https://api.spotify.com/v1/search?q=' . urlencode($q) . '&type=track&limit=10&market=ID';

$ch = curl_init($searchUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken
]);

$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($status !== 200) {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal melakukan pencarian di Spotify API.',
        'tracks' => []
    ]);
    exit;
}

$searchData = json_decode($response, true);
$tracks = [];

if (isset($searchData['tracks']['items']) && count($searchData['tracks']['items']) > 0) {
    foreach ($searchData['tracks']['items'] as $item) {
        $cover = !empty($item['album']['images']) ? $item['album']['images'][0]['url'] : '';
        $artists = array_map(function($a) { return $a['name']; }, $item['artists']);
        $artistName = implode(', ', $artists);
        $titleName = $item['name'];
        $spotifyUrl = $item['external_urls']['spotify'] ?? '';
        
        $previewUrl = $item['preview_url'] ?? null;
        // Tidak lagi memanggil iTunes API per lagu (menyebabkan loading lama)
        // Preview URL langsung dari Spotify, null jika tidak tersedia

        // Pencarian harus responsif; lirik lengkap diambil saat lagu benar-benar dipilih/disimpan.
        $meaning = getSongMeaning($titleName, $artistName, false);

        // Hasil pencarian tidak lagi langsung menjadi data database. Sebelumnya
        // setiap ketikan dapat membuat atau menimpa makna dengan placeholder
        // (atau makna lagu lain yang judulnya sama). Lagu hanya di-upsert di
        // submit_message.php setelah pengguna benar-benar memilihnya.

        $tracks[] = [
            'spotifyId'  => $item['id'],
            'title'      => $titleName,
            'artist'     => $artistName,
            'coverUrl'   => $cover,
            'spotifyUrl' => $spotifyUrl,
            'previewUrl' => $previewUrl,
            'meaning'    => $meaning
        ];
    }
}

// Fallback tracks jika API tidak mengembalikan lagu
if (empty($tracks)) {
    $fallbackTracks = [
        [
            'spotifyId'  => '2IVsRhKrx8hlQBOWy4qebo',
            'title'      => 'Mr. Loverman',
            'artist'     => 'Ricky Montgomery',
            'coverUrl'   => 'https://i.scdn.co/image/ab67616d0000b27367ee332af483acd134fd6fd0',
            'spotifyUrl' => 'https://open.spotify.com/track/2IVsRhKrx8hlQBOWy4qebo',
            'previewUrl' => 'https://audio-ssl.itunes.apple.com/itunes-assets/AudioPreview211/v4/f5/b7/30/f5b730b0-d435-519a-62a8-01101c99c026/mzaf_6838187573825044447.plus.aac.p.m4a',
            'meaning'    => 'Lagu ini menggambarkan perasaan cinta yang mendalam namun diiringi kerapuhan.'
        ],
        [
            'spotifyId'  => '4GfK1qOF3uBWidbPlTCQRL',
            'title'      => 'Monokrom',
            'artist'     => 'Tulus',
            'coverUrl'   => 'https://i.scdn.co/image/ab67616d0000b27371c65edbeed32af70b900637',
            'spotifyUrl' => 'https://open.spotify.com/track/4GfK1qOF3uBWidbPlTCQRL',
            'previewUrl' => 'https://audio-ssl.itunes.apple.com/itunes-assets/AudioPreview126/v4/b8/ed/83/b8ed8318-9ffb-063b-bca6-252ef5ef257f/mzaf_16151384645329084271.plus.aac.p.m4a',
            'meaning'    => 'Ucapan terima kasih yang tulus kepada orang-orang yang mewarnai lembaran hidup.'
        ],
        [
            'spotifyId'  => '2hHeGD57S0BcopfVcmehdl',
            'title'      => 'Hati-Hati di Jalan',
            'artist'     => 'Tulus',
            'coverUrl'   => 'https://i.scdn.co/image/ab67616d0000b273d34a0632f6861e8875d6899b',
            'spotifyUrl' => 'https://open.spotify.com/track/2hHeGD57S0BcopfVcmehdl',
            'previewUrl' => 'https://audio-ssl.itunes.apple.com/itunes-assets/AudioPreview116/v4/23/d1/56/23d156c9-75e1-2ce4-6b41-b3b250eb6f72/mzaf_8966903188186794785.plus.aac.p.m4a',
            'meaning'    => 'Pertemuan dua jiwa yang harus berpisah dan saling merelakan.'
        ],
        [
            'spotifyId'  => '13CwOTXUgBugeBByE9oIWb',
            'title'      => 'kota ini tak sama tanpamu',
            'artist'     => 'Nadhif Basalamah',
            'coverUrl'   => 'https://i.scdn.co/image/ab67616d0000b273f3e3f888fbfcc916ecce50a9',
            'spotifyUrl' => 'https://open.spotify.com/track/13CwOTXUgBugeBByE9oIWb',
            'previewUrl' => 'https://audio-ssl.itunes.apple.com/itunes-assets/AudioPreview211/v4/ec/8e/92/ec8e92e8-d388-e153-1f67-dead894bb7c1/mzaf_7438153273422131757.plus.aac.p.m4a',
            'meaning'    => 'Merindukan sosok yang mengubah suasana kota menjadi hampa saat ia pergi.'
        ],
        [
            'spotifyId'  => '0VjIjW4GlUZAMYd2vXMi3b',
            'title'      => 'Blinding Lights',
            'artist'     => 'The Weeknd',
            'coverUrl'   => 'https://i.scdn.co/image/ab67616d0000b2738863bc11d2aa12b54f5a86d7',
            'spotifyUrl' => 'https://open.spotify.com/track/0VjIjW4GlUZAMYd2vXMi3b',
            'previewUrl' => 'https://audio-ssl.itunes.apple.com/itunes-assets/AudioPreview125/v4/7e/fa/d3/7efad3ef-ea9e-c8aa-d5bb-1135ef58053a/mzaf_10526725838038753239.plus.aac.p.m4a',
            'meaning'    => 'Lagu tentang merindukan seseorang di tengah gemerlap kota.'
        ],
        [
            'spotifyId'  => '1bhOSXw91E7l4dC6r1zP3Z',
            'title'      => 'Perfect',
            'artist'     => 'Ed Sheeran',
            'coverUrl'   => 'https://i.scdn.co/image/ab67616d0000b273ba5db46f4b838ef6027e6f96',
            'spotifyUrl' => 'https://open.spotify.com/track/1bhOSXw91E7l4dC6r1zP3Z',
            'previewUrl' => 'https://audio-ssl.itunes.apple.com/itunes-assets/AudioPreview125/v4/bf/ea/be/bfeabe36-5389-91a1-30ec-a07ce0d96d9e/mzaf_12411603953531580970.plus.aac.p.m4a',
            'meaning'    => 'Cinta sejati dan komitmen masa depan bersama seseorang yang sempurna.'
        ]
    ];

    foreach ($fallbackTracks as $fb) {
        if (empty($q) || $q === 'viral indonesia' || stripos($fb['title'], $q) !== false || stripos($fb['artist'], $q) !== false) {
            $tracks[] = $fb;
        }
    }
    if (empty($tracks)) {
        $tracks = $fallbackTracks;
    }
}

echo json_encode([
    'success' => true,
    'count'   => count($tracks),
    'tracks'  => $tracks
]);
?>
