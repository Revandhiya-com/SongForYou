<?php
/*
 * submit_message.php
 * Menyimpan pesan baru ke database & menyinkronkan data lagu Spotify.
 *
 * Method  : POST
 * Body    : JSON {
 *               receiver      : string,   // recipient_name
 *               senderName    : string?,  // sender_name (boleh kosong/anonim)
 *               songKey       : string,   // spotify_id dari tabel songs
 *               message       : string,
 *               images        : string?   // path file gambar, opsional
 *               songDetails   : object    // detail lagu Spotify
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
$receiver    = isset($data['receiver'])    ? trim($data['receiver'])             : '';
$senderName  = isset($data['senderName'])  ? trim($data['senderName'])           : 'Anonim';
$songKey     = isset($data['songKey'])     ? trim($data['songKey'])              : '';
$message     = isset($data['message'])     ? trim($data['message'])              : '';
$images      = isset($data['images'])      ? trim($data['images'])               : '';
$songDetails = isset($data['songDetails']) ? $data['songDetails']                : null;

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

// ─── Synchronize / Upsert ke Tabel `songs` ────────────────
$s_title   = isset($songDetails['title']) ? trim($songDetails['title']) : 'Lagu Pilihan';
$s_artist  = isset($songDetails['artist']) ? trim($songDetails['artist']) : 'Spotify Artist';
$s_cover   = isset($songDetails['coverUrl']) ? trim($songDetails['coverUrl']) : '';
$s_meaning = (!empty($songDetails['meaning']) && strpos($songDetails['meaning'], 'mewakili perasaan mendalam') === false) 
    ? trim($songDetails['meaning']) 
    : getSongMeaning($s_title, $s_artist);
$s_spotify_url = isset($songDetails['spotifyUrl']) ? trim($songDetails['spotifyUrl']) : '';
$s_preview_url = isset($songDetails['previewUrl']) ? trim($songDetails['previewUrl']) : null;

// Cek apakah lagu sudah ada berdasarkan spotify_id
$stmtSong = mysqli_prepare($conn, "SELECT id FROM songs WHERE spotify_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmtSong, 's', $songKey);
mysqli_stmt_execute($stmtSong);
$resSong = mysqli_stmt_get_result($stmtSong);
$songRow = mysqli_fetch_assoc($resSong);
mysqli_stmt_close($stmtSong);

$songId = 0;

if ($songRow) {
    $songId = (int)$songRow['id'];
    
    // Update data lagu agar selalu fresh dengan cover & preview_url dari Spotify
    $stmtUpdate = mysqli_prepare($conn, 
        "UPDATE songs SET 
            title = ?, 
            artist = ?, 
            cover_url = IF(LENGTH(?) > 0, ?, cover_url), 
            spotify_url = IF(LENGTH(?) > 0, ?, spotify_url), 
            preview_url = IF(? IS NOT NULL AND LENGTH(?) > 0, ?, preview_url) 
         WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmtUpdate, 'sssssssssi', 
        $s_title, $s_artist, 
        $s_cover, $s_cover, 
        $s_spotify_url, $s_spotify_url, 
        $s_preview_url, $s_preview_url, $s_preview_url, 
        $songId
    );
    mysqli_stmt_execute($stmtUpdate);
    mysqli_stmt_close($stmtUpdate);
} else {
    // Insert lagu baru
    $stmtInsertSong = mysqli_prepare($conn,
        "INSERT INTO songs (spotify_id, title, artist, cover_url, meaning, spotify_url, preview_url)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmtInsertSong, 'sssssss',
        $songKey,
        $s_title,
        $s_artist,
        $s_cover,
        $s_meaning,
        $s_spotify_url,
        $s_preview_url
    );
    
    if (mysqli_stmt_execute($stmtInsertSong)) {
        $songId = mysqli_insert_id($conn);
    }
    mysqli_stmt_close($stmtInsertSong);
}

if ($songId === 0) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => "Gagal memproses lagu Spotify di database."]);
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
    $stmtSlug = mysqli_prepare($conn, "SELECT id FROM messages WHERE slug = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtSlug, 's', $slug);
    mysqli_stmt_execute($stmtSlug);
    mysqli_stmt_store_result($stmtSlug);
    $exists = mysqli_stmt_num_rows($stmtSlug) > 0;
    mysqli_stmt_close($stmtSlug);
    $attempts++;
} while ($exists && $attempts < 5);

$userId = 0;

// ─── Proses Unggah Gambar (Base64) jika ada ────────────────
$imagesVal = '';
if (!empty($images)) {
    if (strpos($images, 'data:image/') === 0) {
        $parts = explode(',', $images);
        if (count($parts) === 2) {
            $header = $parts[0];
            $dataBase64 = $parts[1];
            
            $ext = 'png';
            if (preg_match('/data:image\/([a-zA-Z0-9+]+);base64/', $header, $matches)) {
                $ext = $matches[1];
                if ($ext === 'jpeg') $ext = 'jpg';
            }
            
            $decodedData = base64_decode($dataBase64);
            if ($decodedData !== false) {
                // Folder uploads utama (relatif ke file ini)
                $uploadDir = dirname(__DIR__) . '/uploads';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $filename = uniqid('img_', true) . '.' . $ext;
                $filePath = $uploadDir . '/' . $filename;
                
                if (file_put_contents($filePath, $decodedData) !== false) {
                    $imagesVal = 'uploads/' . $filename;

                    // Mirror ke XAMPP htdocs agar foto tampil di kedua server
                    $mirrorDirs = [
                        'C:/xamppp/htdocs/Website-SFY/uploads',
                        'C:/xamppp/htdocs/SFY/uploads',
                    ];
                    foreach ($mirrorDirs as $mirror) {
                        if (!file_exists($mirror)) @mkdir($mirror, 0777, true);
                        @copy($filePath, $mirror . '/' . $filename);
                    }
                }
            }
        }
    } else {
        $imagesVal = $images;
    }
}

// ─── INSERT ke tabel messages ────────────────────────────
$stmt = mysqli_prepare($conn,
    "INSERT INTO messages (user_id, song_id, recipient_name, sender_name, message, images, slug)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
mysqli_stmt_bind_param($stmt, 'iisssss',
    $userId,
    $songId,
    $receiver,
    $senderName,
    $message,
    $imagesVal,
    $slug
);

if (mysqli_stmt_execute($stmt)) {
    $newId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    http_response_code(201);
    echo json_encode([
        'success'   => true,
        'message'   => 'Pesan berhasil dikirim!',
        'id'        => $newId,
        'slug'      => $slug,
        'images'    => $imagesVal,
        'timestamp' => time() * 1000
    ]);
} else {
    $error = mysqli_error($conn);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menyimpan pesan ke database.',
        'error'   => $error
    ]);
}
?>
