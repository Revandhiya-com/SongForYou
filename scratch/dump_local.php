<?php
// scratch/dump_local.php - Dump local MySQL database content
try {
    $conn = new PDO("mysql:host=localhost;dbname=songforyou;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    $songs = $conn->query("SELECT * FROM songs")->fetchAll();
    $messages = $conn->query("SELECT * FROM messages")->fetchAll();
    
    echo json_encode([
        'status' => 'SUCCESS',
        'songs_count' => count($songs),
        'messages_count' => count($messages),
        'songs' => $songs,
        'messages' => $messages
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'ERROR',
        'message' => $e->getMessage()
    ]);
}
?>
