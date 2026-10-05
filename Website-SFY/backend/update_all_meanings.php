<?php
/*
 * backend/update_all_meanings.php
 *
 * Memperbarui makna lama secara bertahap. Setiap lagu hanya disimpan bila
 * kamus yang cocok atau lirik dengan judul dan artis yang sama ditemukan.
 * Dengan begitu, endpoint ini tidak mengganti data dengan template umum.
 */

header('Content-Type: application/json; charset=utf-8');
set_time_limit(25);

require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/song_meaning_helper.php';

$afterId = max(0, (int)($_GET['after_id'] ?? 0));
$limit = max(1, min(6, (int)($_GET['limit'] ?? 3)));

// Penanda yang dipakai oleh generator lama. Makna yang sudah dikurasi tidak
// masuk antrean agar tidak tertimpa oleh hasil otomatis.
$legacyConditions = [
    'melukiskan perasaan mendalam tentang bisikan emosi',
    'sedang disiapkan dari sumber lirik lagu',
    'makna tematiknya belum dapat diverifikasi',
    'makna spesifik untuk'
];

$whereParts = ["meaning IS NULL", "TRIM(meaning) = ''", "meaning LIKE 'Karya %'"];
$params = [':after_id' => $afterId];
foreach ($legacyConditions as $index => $marker) {
    $param = ':legacy_' . $index;
    $whereParts[] = "LOWER(meaning) LIKE {$param}";
    $params[$param] = '%' . strtolower($marker) . '%';
}
$legacyWhere = '(' . implode(' OR ', $whereParts) . ')';

try {
    $totalStmt = $conn->prepare("SELECT COUNT(*) FROM songs WHERE {$legacyWhere}");
    $totalStmt->execute(array_diff_key($params, [':after_id' => true]));
    $remainingBefore = (int)$totalStmt->fetchColumn();

    $stmt = $conn->prepare(
        "SELECT id, spotify_id, title, artist, meaning
         FROM songs
         WHERE id > :after_id AND {$legacyWhere}
         ORDER BY id ASC
         LIMIT {$limit}"
    );
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $updatedCount = 0;
    $unverifiedCount = 0;
    $lastScannedId = $afterId;
    $updateStmt = $conn->prepare('UPDATE songs SET meaning = :meaning WHERE id = :id');

    foreach ($rows as $row) {
        $lastScannedId = (int)$row['id'];
        $title = trim((string)$row['title']);
        $artist = trim((string)$row['artist']);
        $storedMeaning = trim((string)($row['meaning'] ?? ''));

        // Cek kamus terlebih dahulu; bila tidak ada, analisis hanya lirik
        // yang cocok persis dengan judul dan artis tersebut.
        $meaning = getSongMeaning($title, $artist, false, $row['spotify_id'] ?? '');
        if ($meaning === '') {
            $meaning = getSongMeaning($title, $artist, true, $row['spotify_id'] ?? '');
        }

        if (!hasUsableSongMeaning($meaning)) {
            $unverifiedCount++;
            continue;
        }

        if ($meaning !== $storedMeaning) {
            $updateStmt->execute([
                ':meaning' => $meaning,
                ':id' => (int)$row['id']
            ]);
            $updatedCount++;
        }
    }

    $remainingStmt = $conn->prepare("SELECT COUNT(*) FROM songs WHERE {$legacyWhere}");
    $remainingStmt->execute(array_diff_key($params, [':after_id' => true]));
    $remainingAfter = (int)$remainingStmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'processed' => count($rows),
        'updated' => $updatedCount,
        'unverified' => $unverifiedCount,
        'remainingBefore' => $remainingBefore,
        'remainingAfter' => $remainingAfter,
        'nextAfterId' => $lastScannedId,
        'done' => count($rows) === 0
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Pembaruan makna gagal diproses.',
        'error' => $e->getMessage()
    ]);
}
?>
