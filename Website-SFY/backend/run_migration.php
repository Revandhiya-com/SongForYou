<?php
set_time_limit(25);
header("Content-Type: application/json; charset=utf-8");
$batch = isset($_GET["batch"]) ? (int)$_GET["batch"] : 0;
$batchFile = __DIR__ . "/migration_batch_{$batch}.php";
if (!file_exists($batchFile)) {
    echo json_encode(["error" => "Batch {$batch} file not found. Max batch: 9"]);
    exit;
}
require $batchFile;
?>