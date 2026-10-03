<?php
/*
 * backend/koneksi.php
 * Dual-Mode Database Connection (MySQL untuk lokal XAMPP, PostgreSQL untuk Supabase / Vercel)
 */

function getDbEnv($key, $default = '') {
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
    $val = getenv($key);
    if ($val !== false && $val !== '') return $val;
    return $default;
}

$host = getDbEnv('DB_HOST', '');
$user = getDbEnv('DB_USER', '');
$pass = getDbEnv('DB_PASS', '');
$db   = getDbEnv('DB_NAME', '');
$port = getDbEnv('DB_PORT', '');

$conn = null;
$dbDriver = 'mysql';

// 1. Coba koneksi ke PostgreSQL Supabase jika DB_HOST diset (bukan localhost biasa)
if (!empty($host) && $host !== 'localhost' && $host !== '127.0.0.1') {
    $dbName = !empty($db) ? $db : 'postgres';

    // Deteksi apakah ini Supabase pooler host (IPv4, aman untuk Vercel serverless)
    // vs direct host (db.xxx.supabase.co yang bisa IPv6 dan tidak bisa di Vercel)
    $isPoolerHost = (strpos($host, 'pooler.supabase.com') !== false);
    $isDirectHost = (strpos($host, 'supabase.co') !== false && !$isPoolerHost);

    // Username untuk pooler: postgres.{project-ref}
    // Jika DB_USER sudah berformat "postgres.xxx" atau DB_POOLER_USER diset, pakai itu
    $poolerUser = getDbEnv('DB_POOLER_USER', '');
    if (empty($poolerUser)) {
        // Auto-extract project-ref dari db.xxxxx.supabase.co jika DB_HOST adalah direct host
        if ($isDirectHost && preg_match('/^db\.([a-z0-9]+)\.supabase\.co$/', $host, $m)) {
            $projectRef = $m[1];
            $poolerUser = 'postgres.' . $projectRef;
        } elseif (!empty($user) && strpos($user, '.') !== false) {
            // DB_USER sudah berformat postgres.xxxxx
            $poolerUser = $user;
        } else {
            $poolerUser = !empty($user) ? $user : 'postgres';
        }
    }
    $directUser = !empty($user) ? $user : 'postgres';
    $configuredPort = !empty($port) ? $port : '5432';

    // Supabase di Vercel WAJIB pakai pooler (IPv4).
    // Urutan: 6543 pooler → 5432 pooler → 5432 direct (fallback XAMPP/local)
    $dsnAttempts = [
        // Supabase Transaction Pooler (port 6543) — paling stabil di Vercel
        ["pgsql:host={$host};port=6543;dbname={$dbName};sslmode=require", $poolerUser],
        ["pgsql:host={$host};port=6543;dbname={$dbName};sslmode=prefer",  $poolerUser],
        ["pgsql:host={$host};port=6543;dbname={$dbName}",                 $poolerUser],
        // Supabase Session Pooler / Direct (port 5432)
        ["pgsql:host={$host};port=5432;dbname={$dbName};sslmode=require", $poolerUser],
        ["pgsql:host={$host};port=5432;dbname={$dbName};sslmode=require", $directUser],
        ["pgsql:host={$host};port=5432;dbname={$dbName};sslmode=prefer",  $directUser],
        ["pgsql:host={$host};port={$configuredPort};dbname={$dbName}",    $directUser],
    ];

    foreach ($dsnAttempts as [$dsn, $dbUser]) {
        try {
            $conn = new PDO($dsn, $dbUser, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 5,
            ]);
            if ($conn) {
                $dbDriver = 'pgsql';
                break;
            }
        } catch (PDOException $e) {
            // Lanjut coba DSN berikutnya
        }
    }
}


// 2. Jika PostgreSQL belum tersambung, coba MySQL lokal (XAMPP / WAMP)
if (!$conn) {
    $myHost = !empty($host) ? $host : '127.0.0.1';
    $myUser = !empty($user) ? $user : 'root';
    $myPass = $pass;
    $myDb   = !empty($db) ? $db : 'songforyou';
    $myPort = !empty($port) ? $port : '3306';

    try {
        $dsn = "mysql:host={$myHost};port={$myPort};dbname={$myDb};charset=utf8mb4";
        $conn = new PDO($dsn, $myUser, $myPass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        $dbDriver = 'mysql';
    } catch (PDOException $e) {
        // Coba koneksi MySQL ke localhost XAMPP default
        try {
            $dsn = "mysql:host=localhost;dbname=songforyou;charset=utf8mb4";
            $conn = new PDO($dsn, 'root', '', [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            $dbDriver = 'mysql';
        } catch (PDOException $e2) {
            // Coba PostgreSQL di localhost
            try {
                $dsn = "pgsql:host=localhost;port=5432;dbname=postgres";
                $conn = new PDO($dsn, 'postgres', '', [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
                $dbDriver = 'pgsql';
            } catch (PDOException $e3) {
                $conn = null;
            }
        }
    }
}

if (!$conn) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "Koneksi database gagal. Pastikan MySQL di XAMPP dinyalakan untuk lokal, atau isi DB_HOST & DB_PASS untuk Supabase di Vercel."
    ]);
    exit;
}

// Auto-setup tabel jika belum ada di database yang tersambung
try {
    if ($dbDriver === 'pgsql') {
        $conn->exec("
            CREATE TABLE IF NOT EXISTS songs (
                id          SERIAL PRIMARY KEY,
                spotify_id  VARCHAR(255) NOT NULL UNIQUE,
                title       VARCHAR(255) NOT NULL,
                artist      VARCHAR(255) NOT NULL,
                cover_url   TEXT NOT NULL,
                meaning     TEXT DEFAULT NULL,
                spotify_url TEXT NOT NULL,
                preview_url TEXT DEFAULT NULL,
                created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS users (
                id         SERIAL PRIMARY KEY,
                username   VARCHAR(100) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS messages (
                id             SERIAL PRIMARY KEY,
                user_id        INT DEFAULT 0,
                song_id        INT NOT NULL,
                recipient_name VARCHAR(100) NOT NULL,
                sender_name    VARCHAR(100) DEFAULT NULL,
                message        TEXT NOT NULL,
                images         TEXT DEFAULT NULL,
                slug           VARCHAR(255) NOT NULL UNIQUE,
                created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_messages_songs FOREIGN KEY (song_id) REFERENCES songs (id) ON DELETE CASCADE
            );
        ");
    } else {
        $conn->exec("
            CREATE TABLE IF NOT EXISTS songs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                spotify_id VARCHAR(255) NOT NULL UNIQUE,
                title VARCHAR(255) NOT NULL,
                artist VARCHAR(255) NOT NULL,
                cover_url TEXT NOT NULL,
                meaning TEXT DEFAULT NULL,
                spotify_url TEXT NOT NULL,
                preview_url TEXT DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(100) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT DEFAULT 0,
                song_id INT NOT NULL,
                recipient_name VARCHAR(100) NOT NULL,
                sender_name VARCHAR(100) DEFAULT NULL,
                message TEXT NOT NULL,
                images TEXT DEFAULT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (song_id) REFERENCES songs (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    $checkSongs = $conn->query("SELECT COUNT(*) AS cnt FROM songs")->fetch();
    if (isset($checkSongs['cnt']) && (int)$checkSongs['cnt'] === 0) {
        $conn->exec("
            INSERT INTO songs (spotify_id, title, artist, cover_url, meaning, spotify_url, preview_url) VALUES
            ('3n3Ppam7vgaVa1iaRUc9Lp', 'Mr. Loverman', 'Ricky Montgomery', 'https://i.scdn.co/image/ab67616d0000b27341ad37380f2d8e05a81a7b44', 'Lagu ini menggambarkan rasa takut akan kehilangan orang tersayang dan kerapuhan dalam mengungkapkan perasaan cinta mendalam di saat hati merasa kesepian.', 'https://open.spotify.com/track/3n3Ppam7vgaVa1iaRUc9Lp', NULL),
            ('0VjIjW4GlUZAMYd2vXMi3b', 'Blinding Lights', 'The Weeknd', 'https://i.scdn.co/image/ab67616d0000b2738863bc11d2aa12b54f5a86d7', 'Lagu tentang rasa kesepian dan kerinduan mendalam pada seseorang yang mampu meredakan kegelapan dan kekosongan hidup di tengah gemerlap kota.', 'https://open.spotify.com/track/0VjIjW4GlUZAMYd2vXMi3b', NULL);

            INSERT INTO messages (user_id, song_id, recipient_name, sender_name, message, images, slug) VALUES
            (0, 1, 'Zahra', 'Pengagum Rahasia', 'Terima kasih sudah selalu ada dan mencerahkan hari-hariku!', NULL, 'zahra-a3f9c12b');
        ");
    }
} catch (Exception $e) {
    // Ignore setup error if tables already populated
}
?>
