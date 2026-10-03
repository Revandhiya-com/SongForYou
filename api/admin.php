<?php
// api/admin.php - Entry point untuk admin panel
$root = dirname(__DIR__);
chdir($root . '/Website-SFY/admin');
set_include_path($root . '/Website-SFY/admin' . PATH_SEPARATOR . $root . '/Website-SFY');
require $root . '/Website-SFY/admin/index.php';
