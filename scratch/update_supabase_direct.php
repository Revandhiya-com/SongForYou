<?php
// Set env vars for Supabase connection from koneksi.php
$_ENV['DB_HOST'] = 'db.aifeklpxqoxwixwghbva.supabase.co';
$_ENV['DB_USER'] = 'postgres';
$_ENV['DB_PASS'] = 'SongForYou2026!'; // Let's check DB_PASS or connect via pooler

require_once __DIR__ . '/../Website-SFY/backend/koneksi.php';

echo "Connected to driver: " . ($dbDriver ?? 'none') . "\n";

if ($conn && $dbDriver === 'pgsql') {
    // 1. Update preview_url for Bila - Raisa
    $stmt = $conn->prepare("UPDATE songs SET preview_url = :purl WHERE title ILIKE '%Bila%' AND artist ILIKE '%Raisa%'");
    $stmt->execute([':purl' => 'https://audio-ssl.itunes.apple.com/itunes-assets/AudioPreview221/v4/2b/77/59/2b77594c-8cc6-c252-9f90-d4be3af9d30d/mzaf_11665507368448228605.plus.aac.p.m4a']);
    echo "Updated 'Bila - Raisa' in Supabase! Rows affected: " . $stmt->rowCount() . "\n";

    // 2. Also update all other songs with missing/bad preview URLs in Supabase
    $stmtSongs = $conn->query("SELECT id, title, artist, preview_url FROM songs");
    $songs = $stmtSongs->fetchAll();
    
    $updated = 0;
    $stmtUpd = $conn->prepare("UPDATE songs SET preview_url = :purl WHERE id = :id");

    function getPreview($title, $artist) {
        $cleanTitle = trim(preg_replace('/\s*[\(\[\-].*$/', '', $title));
        $cleanArtist = trim(explode(',', explode('&', $artist)[0])[0]);
        $q = urlencode($cleanTitle . ' ' . $cleanArtist);
        $url = "https://itunes.apple.com/search?term={$q}&country=ID&media=music&entity=song&limit=5";
        $res = @file_get_contents($url);
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
                if (!empty($json['results'][0]['previewUrl'])) return $json['results'][0]['previewUrl'];
            }
        }
        return null;
    }

    foreach ($songs as $s) {
        if (empty($s['preview_url']) || strpos($s['preview_url'], 'mzaf_1404835599913133440') !== false) {
            $purl = getPreview($s['title'], $s['artist']);
            if ($purl) {
                $stmtUpd->execute([':purl' => $purl, ':id' => $s['id']]);
                $updated++;
            }
        }
    }
    echo "Updated $updated songs in Supabase!\n";
} else {
    echo "Could not connect to PostgreSQL Supabase directly. DB driver is $dbDriver\n";
}
?>
