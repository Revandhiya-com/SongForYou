<?php
copy('api/run_migration.php', 'Website-SFY/backend/run_migration.php');
copy('api/migration_common.php', 'Website-SFY/backend/migration_common.php');
for ($i = 0; $i < 10; $i++) {
    copy("api/migration_batch_{$i}.php", "Website-SFY/backend/migration_batch_{$i}.php");
}
echo "Copied migration files to Website-SFY/backend/\n";
?>
