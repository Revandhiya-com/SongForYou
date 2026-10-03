<?php
// api/index.php - Entry point untuk Vercel PHP runtime
// Forward semua request ke Website-SFY/index.php

$root = dirname(__DIR__);
chdir($root . '/Website-SFY');

// Set include path
set_include_path($root . '/Website-SFY');

require $root . '/Website-SFY/index.php';
