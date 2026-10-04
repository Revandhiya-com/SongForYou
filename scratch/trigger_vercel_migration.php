<?php
echo "Triggering all 10 migration batches on Vercel via /backend/run_migration.php...\n";

for ($b = 0; $b < 10; $b++) {
    $url = "https://songforyou-sigma.vercel.app/backend/run_migration.php?batch={$b}";
    $res = @file_get_contents($url);
    echo "Batch {$b} response: " . trim($res) . "\n";
}
echo "Finished migration trigger on Vercel!\n";
?>
