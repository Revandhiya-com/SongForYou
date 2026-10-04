<?php
require_once __DIR__ . '/../Website-SFY/backend/koneksi.php';

echo "Updating missing/outdated preview URLs for all songs in DB...\n";

$stmt = $conn->query("SELECT id, title, artist, preview_url FROM songs");
$songs = $stmt->fetchAll();

$updated = 0;
$stmtUpdate = $conn->prepare("UPDATE songs SET preview_url = :purl WHERE id = :id");

function fetchPreviewUrl($title, $artist) {
    $cleanTitle = trim(preg_replace('/\s*[\(\[\-].*$/', '', $title));
    $cleanArtist = trim(explode(',', explode('&', $artist)[0])[0]);
    $q = urlencode($cleanTitle . ' ' . $cleanArtist);

    // 1. iTunes country=ID
    $url = "https://itunes.apple.com/search?term={$q}&country=ID&media=music&entity=song&limit=5";
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

    // 2. Deezer API
    $url2 = "https://api.deezer.com/search?q={$q}&limit=5";
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

foreach ($songs as $s) {
    // If preview_url is empty, or doesn't match accurate track
    if (empty($s['preview_url']) || strpos($s['title'], 'Bila') !== false || strpos($s['artist'], 'Raisa') !== false) {
        $purl = fetchPreviewUrl($s['title'], $s['artist']);
        if ($purl && $purl !== $s['preview_url']) {
            $stmtUpdate->execute([':purl' => $purl, ':id' => $s['id']]);
            $updated++;
            echo "Updated #{$s['id']} '{$s['title']}' by '{$s['artist']}' => $purl\n";
        }
    }
}

echo "Finished updating preview URLs! Total updated: $updated\n";
?>
