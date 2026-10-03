<?php
// api/index.php - Entry point untuk Vercel PHP runtime
$root = dirname(__DIR__);
chdir($root . '/Website-SFY');
set_include_path($root . '/Website-SFY');
require $root . '/Website-SFY/index.php';
