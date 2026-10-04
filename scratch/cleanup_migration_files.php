<?php
$filesToDelete = [
    'api/run_migration.php',
    'api/migration_common.php',
    'Website-SFY/backend/run_migration.php',
    'Website-SFY/backend/migration_common.php',
];

for ($i = 0; $i < 10; $i++) {
    $filesToDelete[] = "api/migration_batch_{$i}.php";
    $filesToDelete[] = "Website-SFY/backend/migration_batch_{$i}.php";
}

foreach ($filesToDelete as $f) {
    if (file_exists($f)) {
        unlink($f);
        echo "Deleted: $f\n";
    }
}
echo "Cleanup completed successfully!\n";
?>
