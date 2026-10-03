<?php
/*
 * delete_message.php
 * Menghapus pesan berdasarkan ID atau slug.
 *
 * Method  : DELETE  (atau POST?_method=DELETE)
 * Params  : ?id=<int>  ATAU  ?slug=<string>
 * Response: JSON { success, message }
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST' && isset($_GET['_method']) && strtoupper($_GET['_method']) === 'DELETE') {
    $method = 'DELETE';
}

if ($method !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

require_once __DIR__ . '/koneksi.php';

$id   = isset($_GET['id'])   ? (int)$_GET['id']     : 0;
$slug = isset($_GET['slug']) ? trim($_GET['slug'])    : '';

if ($id <= 0 && empty($slug)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parameter "id" atau "slug" wajib diisi.']);
    exit;
}

try {
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM messages WHERE id = :id");
        $stmt->execute([':id' => $id]);
    } else {
        $stmt = $conn->prepare("DELETE FROM messages WHERE slug = :slug");
        $stmt->execute([':slug' => $slug]);
    }

    $affected = $stmt->rowCount();

    if ($affected > 0) {
        echo json_encode(['success' => true, 'message' => 'Pesan berhasil dihapus.']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Pesan tidak ditemukan.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus pesan.', 'error' => $e->getMessage()]);
}
?>
