<?php
/*
 * submit_message.php
 * Menyimpan pesan baru ke database & menyinkronkan data lagu Spotify.
 *
 * Method  : POST
 * Body    : JSON {
 *               receiver      : string,
 *               senderName    : string?,
 *               songKey       : string,
 *               message       : string,
 *               lyricExcerpt  : string?,
 *               lyricSection  : string?,
 *               images        : string?
 *               songDetails   : object
 *           }
 * Response: JSON { success, id, slug }
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Gunakan POST.']);
    exit;
}

require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/song_meaning_helper.php';

$body = file_get_contents('php://input');
$data = json_decode($body, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Request body harus berformat JSON.']);
    exit;
}

// ─── Ambil & validasi field ──────────────────────────────
$receiver    = isset($data['receiver'])    ? trim($data['receiver'])    : '';
$senderName  = isset($data['senderName'])  ? trim($data['senderName'])  : 'Anonim';
$songKey     = isset($data['songKey'])     ? trim($data['songKey'])     : '';
$message     = isset($data['message'])     ? trim($data['message'])     : '';
$lyricExcerpt = isset($data['lyricExcerpt']) ? trim($data['lyricExcerpt']) : '';
$lyricSection = isset($data['lyricSection']) ? trim($data['lyricSection']) : '';
$clipStart = isset($data['clipStart']) ? max(0, min(30, (int)$data['clipStart'])) : 0;
$images      = isset($data['images'])      ? trim($data['images'])      : '';
$songDetails = isset($data['songDetails']) ? $data['songDetails']       : null;

if (empty($receiver)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Nama penerima tidak boleh kosong.']);
    exit;
}
if (strlen($receiver) > 100) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Nama penerima maks 100 karakter.']);
    exit;
}
if (empty($songKey)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Lagu harus dipilih.']);
    exit;
}
if (empty($message)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Pesan tidak boleh kosong.']);
    exit;
}
if (strlen($message) > 5000) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Pesan terlalu panjang (maks 5000 karakter).']);
    exit;
}
if (strlen($lyricExcerpt) > 280 || strlen($lyricSection) > 30) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Cuplikan lirik atau bagian lagu terlalu panjang.']);
    exit;
}

try {
    // ─── Synchronize / Upsert ke Tabel `songs` ────────────────
    $s_title       = isset($songDetails['title'])      ? trim($songDetails['title'])      : 'Lagu Pilihan';
    $s_artist      = isset($songDetails['artist'])     ? trim($songDetails['artist'])     : 'Spotify Artist';
    $s_cover       = isset($songDetails['coverUrl'])   ? trim($songDetails['coverUrl'])   : '';
    $s_spotify_url = isset($songDetails['spotifyUrl']) ? trim($songDetails['spotifyUrl']) : ('https://open.spotify.com/track/' . $songKey);
    // Makna selalu dibuat di server dari judul dan artis. Jangan memakai teks generik
    // yang dikirim browser atau dari hasil pencarian pihak ketiga.
    $s_meaning     = getSongMeaning($s_title, $s_artist);
    $s_preview_url = isset($songDetails['previewUrl']) ? trim($songDetails['previewUrl']) : null;
    if (empty($s_preview_url)) {
        $cleanTitle = trim(preg_replace('/\s*[\(\[\-].*$/', '', $s_title));
        $cleanArtist = trim(explode(',', explode('&', $s_artist)[0])[0]);
        $q = urlencode($cleanTitle . ' ' . $cleanArtist);
        $resPreview = @file_get_contents("https://itunes.apple.com/search?term={$q}&country=ID&media=music&entity=song&limit=5");
        if ($resPreview) {
            $jsonPreview = json_decode($resPreview, true);
            if (!empty($jsonPreview['results'])) {
                foreach ($jsonPreview['results'] as $tr) {
                    if (!empty($tr['previewUrl'])) {
                        $s_preview_url = $tr['previewUrl'];
                        break;
                    }
                }
            }
        }
    }

    // Cek apakah lagu sudah ada
    $stmtSong = $conn->prepare("SELECT id FROM songs WHERE spotify_id = :spotify_id LIMIT 1");
    $stmtSong->execute([':spotify_id' => $songKey]);
    $songRow = $stmtSong->fetch();

    $songId = 0;

    if ($songRow) {
        $songId = (int)$songRow['id'];

        // Update data lagu agar selalu fresh
        $stmtUpdate = $conn->prepare(
            "UPDATE songs SET
                title = :title,
                artist = :artist,
                cover_url = COALESCE(NULLIF(:cover, ''), cover_url),
                meaning = :meaning,
                spotify_url = COALESCE(NULLIF(:surl, ''), spotify_url),
                preview_url = COALESCE(NULLIF(:purl, ''), preview_url)
             WHERE id = :id"
        );
        $stmtUpdate->execute([
            ':title'  => $s_title,
            ':artist' => $s_artist,
            ':cover'  => $s_cover,
            ':meaning'=> $s_meaning,
            ':surl'   => $s_spotify_url,
            ':purl'   => $s_preview_url ?? '',
            ':id'     => $songId,
        ]);
    } else {
        // Insert lagu baru
        if (($dbDriver ?? 'mysql') === 'pgsql') {
            $stmtInsertSong = $conn->prepare(
                "INSERT INTO songs (spotify_id, title, artist, cover_url, meaning, spotify_url, preview_url)
                 VALUES (:spotify_id, :title, :artist, :cover_url, :meaning, :spotify_url, :preview_url)
                 RETURNING id"
            );
            $stmtInsertSong->execute([
                ':spotify_id'  => $songKey,
                ':title'       => $s_title,
                ':artist'      => $s_artist,
                ':cover_url'   => $s_cover,
                ':meaning'     => $s_meaning,
                ':spotify_url' => $s_spotify_url,
                ':preview_url' => $s_preview_url,
            ]);
            $inserted = $stmtInsertSong->fetch();
            $songId = $inserted ? (int)$inserted['id'] : 0;
        } else {
            $stmtInsertSong = $conn->prepare(
                "INSERT INTO songs (spotify_id, title, artist, cover_url, meaning, spotify_url, preview_url)
                 VALUES (:spotify_id, :title, :artist, :cover_url, :meaning, :spotify_url, :preview_url)"
            );
            $stmtInsertSong->execute([
                ':spotify_id'  => $songKey,
                ':title'       => $s_title,
                ':artist'      => $s_artist,
                ':cover_url'   => $s_cover,
                ':meaning'     => $s_meaning,
                ':spotify_url' => $s_spotify_url,
                ':preview_url' => $s_preview_url,
            ]);
            $songId = (int)$conn->lastInsertId();
        }
    }

    if ($songId === 0) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal memproses lagu Spotify di database.']);
        exit;
    }

    // ─── Generate slug unik ───────────────────────────────────
    function generateSlug($name) {
        $base = preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($name)));
        $base = trim($base, '-');
        return $base . '-' . bin2hex(random_bytes(4));
    }

    $slug = '';
    $attempts = 0;
    do {
        $slug = generateSlug($receiver);
        $stmtSlug = $conn->prepare("SELECT id FROM messages WHERE slug = :slug LIMIT 1");
        $stmtSlug->execute([':slug' => $slug]);
        $exists = $stmtSlug->fetch() !== false;
        $attempts++;
    } while ($exists && $attempts < 5);

    $userId = 0;

    // ─── Proses Unggah Gambar (Base64) jika ada ────────────────
    // Simpan data URL di database, bukan filesystem serverless yang bersifat sementara.
    $imagesVal = '';
    if (!empty($images)) {
        if (!preg_match('#^data:image/(jpeg|png|webp);base64,([A-Za-z0-9+/=]+)$#', $images, $imageMatch)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Format foto harus JPG, PNG, atau WEBP.']);
            exit;
        }

        $decodedData = base64_decode($imageMatch[2], true);
        if ($decodedData === false || strlen($decodedData) > 2 * 1024 * 1024) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Ukuran foto setelah diproses maksimal 2 MB.']);
            exit;
        }

        $imagesVal = $images;
    }

    // ─── INSERT ke tabel messages ────────────────────────────
    if (($dbDriver ?? 'mysql') === 'pgsql') {
        $stmt = $conn->prepare(
            "INSERT INTO messages (user_id, song_id, recipient_name, sender_name, message, lyric_excerpt, lyric_section, clip_start, images, slug)
             VALUES (:user_id, :song_id, :recipient_name, :sender_name, :message, :lyric_excerpt, :lyric_section, :clip_start, :images, :slug)
             RETURNING id"
        );
        $stmt->execute([
            ':user_id'        => $userId,
            ':song_id'        => $songId,
            ':recipient_name' => $receiver,
            ':sender_name'    => $senderName,
            ':message'        => $message,
            ':lyric_excerpt'  => $lyricExcerpt,
            ':lyric_section'  => $lyricSection,
            ':clip_start'     => $clipStart,
            ':images'         => $imagesVal,
            ':slug'           => $slug,
        ]);
        $newRow = $stmt->fetch();
        $newId  = $newRow ? (int)$newRow['id'] : 0;
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO messages (user_id, song_id, recipient_name, sender_name, message, lyric_excerpt, lyric_section, clip_start, images, slug)
             VALUES (:user_id, :song_id, :recipient_name, :sender_name, :message, :lyric_excerpt, :lyric_section, :clip_start, :images, :slug)"
        );
        $stmt->execute([
            ':user_id'        => $userId,
            ':song_id'        => $songId,
            ':recipient_name' => $receiver,
            ':sender_name'    => $senderName,
            ':message'        => $message,
            ':lyric_excerpt'  => $lyricExcerpt,
            ':lyric_section'  => $lyricSection,
            ':clip_start'     => $clipStart,
            ':images'         => $imagesVal,
            ':slug'           => $slug,
        ]);
        $newId = (int)$conn->lastInsertId();
    }

    http_response_code(201);
    echo json_encode([
        'success'   => true,
        'message'   => 'Pesan berhasil dikirim!',
        'id'        => $newId,
        'slug'      => $slug,
        'images'    => $imagesVal,
        'timestamp' => time() * 1000
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menyimpan pesan ke database.',
        'error'   => $e->getMessage()
    ]);
}
?>
