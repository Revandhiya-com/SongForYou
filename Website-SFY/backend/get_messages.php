<?php
/*
 * get_messages.php
 * Mengambil semua pesan dari DB (JOIN dengan tabel songs).
 * Bisa difilter berdasarkan nama penerima.
 *
 * Method  : GET
 * Params  : ?search=<nama> (opsional)
 * Response: JSON { success, count, messages[] }
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
require_once __DIR__ . '/song_meaning_helper.php';

$search = isset($_GET['search']) ? trim(strtolower($_GET['search'])) : '';

try {
    if ($search !== '') {
        $stmt = $conn->prepare(
            "SELECT
                m.id,
                m.recipient_name,
                m.sender_name,
                m.message,
                m.lyric_excerpt,
                m.lyric_section,
                m.clip_start,
                m.images,
                m.slug,
                m.created_at,
                s.spotify_id  AS song_key,
                s.title       AS song_title,
                s.artist      AS song_artist,
                s.cover_url   AS song_cover,
                s.meaning     AS song_meaning,
                s.spotify_url AS song_spotify_url,
                s.preview_url AS song_preview_url
             FROM messages m
             LEFT JOIN songs s ON m.song_id = s.id
             WHERE LOWER(m.recipient_name) LIKE :search
             ORDER BY m.id DESC"
        );
        $stmt->execute([':search' => '%' . $search . '%']);
    } else {
        $stmt = $conn->prepare(
            "SELECT
                m.id,
                m.recipient_name,
                m.sender_name,
                m.message,
                m.lyric_excerpt,
                m.lyric_section,
                m.clip_start,
                m.images,
                m.slug,
                m.created_at,
                s.spotify_id  AS song_key,
                s.title       AS song_title,
                s.artist      AS song_artist,
                s.cover_url   AS song_cover,
                s.meaning     AS song_meaning,
                s.spotify_url AS song_spotify_url,
                s.preview_url AS song_preview_url
             FROM messages m
             LEFT JOIN songs s ON m.song_id = s.id
             ORDER BY m.id DESC"
        );
        $stmt->execute();
    }

    $rows = $stmt->fetchAll();
    $messages = [];

    foreach ($rows as $row) {
        $img = $row['images'];
        if (!empty($img) && preg_match('#^/SFY/(uploads/.+)$#', $img, $m)) {
            $img = $m[1];
        }
        $pUrl = $row['song_preview_url'];
        if ((stripos($row['song_title'], 'Bila') !== false && stripos($row['song_artist'], 'Raisa') !== false) || strpos($pUrl, 'mzaf_1404835599913133440') !== false) {
            $pUrl = 'https://audio-ssl.itunes.apple.com/itunes-assets/AudioPreview221/v4/2b/77/59/2b77594c-8cc6-c252-9f90-d4be3af9d30d/mzaf_11665507368448228605.plus.aac.p.m4a';
        }

        $storedMeaning = trim((string)($row['song_meaning'] ?? ''));
        $title = $row['song_title'] ?? '';
        $artist = $row['song_artist'] ?? '';
        $catalogMeaning = getSongMeaning($title, $artist, false, $row['song_key'] ?? '');
        $isLegacyCopy = preg_match(
            '/^Lagu ini menggambarkan rasa takut akan kehilangan orang tersayang.*hati merasa kesepian\.$/u',
            $storedMeaning
        ) && $catalogMeaning === '';
        $needsRefresh = !hasUsableSongMeaning($storedMeaning) || $isLegacyCopy;
        $meaning = $catalogMeaning !== '' ? $catalogMeaning : $storedMeaning;

        // Pesan lama yang masih memakai template diperbarui saat dibaca. Makna
        // yang lolos verifikasi juga disimpan kembali agar request berikutnya
        // tidak perlu menghitung ulang.
        if ($needsRefresh) {
            $refreshedMeaning = getSongMeaning($title, $artist, true, $row['song_key'] ?? '');
            $meaning = $refreshedMeaning;
            if (hasUsableSongMeaning($refreshedMeaning) && !empty($row['song_key'])) {
                try {
                    $stmtMeaning = $conn->prepare('UPDATE songs SET meaning = :meaning WHERE spotify_id = :spotify_id');
                    $stmtMeaning->execute([
                        ':meaning' => $refreshedMeaning,
                        ':spotify_id' => $row['song_key'],
                    ]);
                } catch (Throwable $ignored) {
                    // Pesan tetap dapat ditampilkan meski sinkronisasi gagal.
                }
            }
        } elseif ($catalogMeaning !== '' && $catalogMeaning !== $storedMeaning && !empty($row['song_key'])) {
            // Perbaiki data lama yang sudah terlanjur berisi makna umum.
            try {
                $stmtMeaning = $conn->prepare('UPDATE songs SET meaning = :meaning WHERE spotify_id = :spotify_id');
                $stmtMeaning->execute([
                    ':meaning' => $catalogMeaning,
                    ':spotify_id' => $row['song_key'],
                ]);
            } catch (Throwable $ignored) {
                // Pesan tetap dapat ditampilkan meski sinkronisasi gagal.
            }
        }

        $messages[] = [
            'id'             => (int)$row['id'],
            'receiver'       => $row['recipient_name'],
            'senderName'     => $row['sender_name'],
            'songKey'        => $row['song_key'],
            'songTitle'      => $row['song_title'],
            'songArtist'     => $row['song_artist'],
            'songCover'      => $row['song_cover'],
            'songMeaning'    => $meaning,
            'songSpotifyUrl' => $row['song_spotify_url'],
            'previewUrl'     => $pUrl,
            'message'        => $row['message'],
            'lyricExcerpt'   => $row['lyric_excerpt'] ?? '',
            'lyricSection'   => $row['lyric_section'] ?? '',
            'clipStart'      => (int)($row['clip_start'] ?? 0),
            'images'         => $img,
            'slug'           => $row['slug'],
            'timestamp'      => strtotime($row['created_at']) * 1000
        ];
    }

    echo json_encode([
        'success'  => true,
        'count'    => count($messages),
        'messages' => $messages
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Query gagal: ' . $e->getMessage()]);
}
?>
