<?php
// api/index.php - Entry point untuk Vercel PHP runtime v2
header('Content-Type: text/html; charset=utf-8');

$root = dirname(__DIR__);
chdir($root . '/Website-SFY');
set_include_path($root . '/Website-SFY');
require $root . '/Website-SFY/index.php';
?>
