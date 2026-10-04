<?php
/*
 * /Website-SFY/index.php
 * SongForYou — Archive Your Feelings Through Music
 */
header('Content-Type: text/html; charset=utf-8');

$requestUri = $_SERVER['REQUEST_URI'];
$parsedPath = parse_url($requestUri, PHP_URL_PATH);

// Pastikan jika diakses sebagai folder tanpa trailing slash, redirect ke trailing slash
if (substr($parsedPath, -1) !== '/' && !str_ends_with($parsedPath, '.php')) {
    $queryString = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '';
    header('Location: ' . $parsedPath . '/' . $queryString, true, 301);
    exit;
}

// Redirect /admin ke admin/index.php jika diperlukan
if (preg_match('/\/admin\/?$/i', $parsedPath)) {
    header('Location: /admin/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SONGFORYOU.PROJECT — Digital Sound Archive</title>
    <meta name="description" content="Archive your feelings through music. Ungkapkan perasaanmu secara rahasia dan lampirkan lagu yang mewakilinya.">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@1,400;1,600;1,700&family=Syne:wght@700;800&family=Space+Mono&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-body: #070709;
            --bg-card: #111114;
            --bg-card-hover: #16161a;
            --border-color: rgba(255, 255, 255, 0.08);
            --border-highlight: rgba(255, 255, 255, 0.18);
            --text-main: #f4f4f6;
            --text-muted: #71717a;
            --text-sub: #a1a1aa;
            --spotify-green: #1db954;
            --radius-card: 16px;
            --radius-btn: 30px;
            --font-mono: 'Space Mono', monospace;
            --font-serif: 'Playfair Display', serif;
            --font-sans: 'Inter', sans-serif;
            --font-heading: 'Syne', sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-sans);
            background-color: var(--bg-body);
            color: var(--text-main);
            line-height: 1.5;
            min-height: 100vh;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        /* Top Announcement Bar */
        .top-bar {
            text-align: center;
            background: #000000;
            border-bottom: 1px solid var(--border-color);
            padding: 8px 16px;
            font-family: var(--font-mono);
            font-size: 0.72rem;
            letter-spacing: 1.5px;
            color: var(--text-sub);
            text-transform: uppercase;
        }

        /* Navbar */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(7, 7, 9, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-color);
            padding: 18px 40px;
        }

        .nav-container {
            max-width: 1300px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand-logo {
            font-family: var(--font-heading);
            font-weight: 800;
            font-size: 1.15rem;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .brand-logo span {
            color: var(--text-muted);
            font-weight: 400;
        }

        .nav-buttons {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .btn-nav-dashboard {
            padding: 10px 22px;
            border-radius: var(--radius-btn);
            border: 1px solid var(--border-highlight);
            background: transparent;
            color: var(--text-main);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-nav-dashboard:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--text-main);
        }

        .btn-nav-create {
            padding: 10px 24px;
            border-radius: var(--radius-btn);
            border: none;
            background: #ffffff;
            color: #000000;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            transition: all 0.2s ease;
            cursor: pointer;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.2);
        }

        .btn-nav-create:hover {
            background: #e2e2e8;
            transform: translateY(-1px);
        }

        /* Container */
        .main-wrapper {
            max-width: 1300px;
            margin: 0 auto;
            padding: 60px 20px;
        }

        /* Hero Section */
        .hero-section {
            text-align: center;
            padding: 60px 20px 80px;
        }

        .hero-tag {
            font-family: var(--font-mono);
            font-size: 0.75rem;
            letter-spacing: 3px;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 24px;
            display: block;
        }

        .hero-title {
            font-family: var(--font-heading);
            font-size: 4rem;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -1.5px;
            margin-bottom: 20px;
        }

        .hero-title i {
            font-family: var(--font-serif);
            font-weight: 400;
            font-style: italic;
            color: #e4e4e7;
        }

        .hero-sub {
            font-size: 1.05rem;
            color: var(--text-sub);
            max-width: 600px;
            margin: 0 auto 40px;
            font-weight: 400;
        }

        /* Search Bar */
        .search-container {
            max-width: 540px;
            margin: 0 auto 70px;
            position: relative;
        }

        .search-container i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .search-input {
            width: 100%;
            padding: 16px 20px 16px 50px;
            background: #0d0d10;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            color: var(--text-main);
            font-size: 0.85rem;
            font-family: var(--font-mono);
            letter-spacing: 1px;
            transition: all 0.2s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--border-highlight);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.05);
        }

        .search-input::placeholder {
            color: #4a4a52;
            text-transform: uppercase;
        }

        /* Features Columns */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            border-top: 1px solid var(--border-color);
            padding-top: 50px;
            margin-bottom: 80px;
            text-align: left;
        }

        .feature-item {
            padding-right: 20px;
        }

        .feature-num {
            font-family: var(--font-mono);
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 12px;
            display: block;
        }

        .feature-title {
            font-family: var(--font-heading);
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .feature-desc {
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.6;
        }

        /* Message Creation Section / Modal Component */
        .form-section {
            background: #0d0d10;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-card);
            padding: 40px;
            max-width: 680px;
            margin: 0 auto 90px;
            scroll-margin-top: 120px;
        }

        .form-tech-tag {
            font-family: var(--font-mono);
            font-size: 0.72rem;
            color: var(--text-muted);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 8px;
            display: block;
        }

        .form-title {
            font-family: var(--font-heading);
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .form-subtitle {
            font-size: 0.95rem;
            color: var(--text-sub);
            margin-bottom: 32px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 24px;
        }

        .form-group {
            margin-bottom: 28px;
        }

        .form-label {
            display: block;
            font-family: var(--font-mono);
            font-size: 0.75rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--text-sub);
            margin-bottom: 10px;
        }

        .form-control {
            width: 100%;
            padding: 14px 18px;
            background: #141418;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-main);
            font-size: 0.95rem;
            font-family: var(--font-sans);
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--border-highlight);
            background: #18181d;
        }

        .form-helper {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 8px;
            display: block;
        }

        textarea.form-control {
            min-height: 140px;
            resize: vertical;
        }

        /* Custom File Upload */
        .file-upload-box {
            border: 1px dashed var(--border-highlight);
            background: #141418;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .file-upload-box:hover {
            border-color: var(--text-main);
            background: #18181d;
        }

        .file-upload-box i {
            color: var(--text-muted);
            margin-right: 8px;
        }

        .file-upload-box span {
            font-size: 0.85rem;
            color: var(--text-sub);
        }

        /* Song Selector Options */
        .song-select-custom {
            position: relative;
        }

        .song-results-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #16161b;
            border: 1px solid var(--border-highlight);
            border-radius: 8px;
            margin-top: 6px;
            max-height: 280px;
            overflow-y: auto;
            z-index: 50;
            display: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.8);
        }

        .song-result-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .song-result-item:hover {
            background: rgba(255, 255, 255, 0.08);
        }

        .song-result-item img {
            width: 42px;
            height: 42px;
            border-radius: 6px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .song-result-item .song-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #ffffff;
        }

        .song-result-item .song-artist {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .btn-submit-form {
            width: 100%;
            padding: 16px;
            border-radius: 8px;
            border: none;
            background: #ffffff;
            color: #000000;
            font-family: var(--font-heading);
            font-size: 0.9rem;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 10px;
        }

        .btn-submit-form:hover {
            background: #e2e2e8;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
        }

        /* Message Cards Grid */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 80px;
        }

        .msg-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-card);
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 280px;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .msg-card:hover {
            background: var(--bg-card-hover);
            border-color: var(--border-highlight);
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.6);
        }

        .msg-card-tag {
            font-family: var(--font-mono);
            font-size: 0.72rem;
            letter-spacing: 1px;
            color: var(--text-sub);
            background: rgba(255, 255, 255, 0.05);
            padding: 4px 10px;
            border-radius: 4px;
            display: inline-block;
            align-self: flex-start;
            margin-bottom: 18px;
            text-transform: uppercase;
        }

        .msg-card-quote {
            font-family: var(--font-serif);
            font-style: italic;
            font-size: 1.35rem;
            color: #ffffff;
            margin-bottom: 20px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .msg-card-image {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 16px;
        }

        .msg-card-audiobar {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 10px 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: auto;
        }

        .msg-card-audiobar img {
            width: 38px;
            height: 38px;
            border-radius: 6px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .msg-card-audiobar .song-meta {
            flex: 1;
            overflow: hidden;
        }

        .msg-card-audiobar .song-meta .title {
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .msg-card-audiobar .song-meta .artist {
            font-size: 0.75rem;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .msg-card-audiobar i.fa-spotify {
            color: var(--spotify-green);
            font-size: 1.1rem;
        }

        /* Empty State */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 80px 20px;
            border: 1px dashed var(--border-color);
            border-radius: var(--radius-card);
            background: rgba(17, 17, 20, 0.5);
        }

        .empty-state i {
            font-size: 2.5rem;
            color: var(--text-muted);
            margin-bottom: 16px;
        }

        .empty-state h3 {
            font-family: var(--font-mono);
            font-size: 0.9rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--text-sub);
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: 0.9rem;
            color: var(--text-muted);
            max-width: 450px;
            margin: 0 auto 24px;
        }

        /* FULL SCREEN MESSAGE VIEW PAGE */
        .fullscreen-message-page {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: #070709;
            z-index: 999;
            overflow-y: auto;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.4s cubic-bezier(0.16, 1, 0.3, 1), transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            transform: scale(0.97);
            display: flex;
            flex-direction: column;
            padding: 50px 20px;
        }

        .fullscreen-message-page.active {
            opacity: 1;
            pointer-events: auto;
            transform: scale(1);
        }

        .fullscreen-content-container {
            max-width: 650px;
            width: 100%;
            margin: 0 auto;
            position: relative;
        }

        .btn-fullscreen-back {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 10px 22px;
            border-radius: 30px;
            font-family: var(--font-mono);
            font-size: 0.75rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s ease;
            margin-bottom: 40px;
        }

        .btn-fullscreen-back:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--text-main);
            transform: translateX(-2px);
        }

        .modal-tech-tag {
            font-family: var(--font-mono);
            font-size: 0.75rem;
            color: var(--text-muted);
            letter-spacing: 2.5px;
            text-transform: uppercase;
            text-align: center;
            display: block;
            margin-bottom: 14px;
        }

        .modal-header-title {
            font-size: 2.8rem;
            font-weight: 400;
            text-align: center;
            margin-bottom: 10px;
            line-height: 1.2;
        }

        .modal-header-title i {
            font-family: var(--font-serif);
            font-style: italic;
            color: #ffffff;
        }

        .modal-header-sub {
            font-size: 0.95rem;
            color: var(--text-muted);
            text-align: center;
            max-width: 500px;
            margin: 0 auto 35px;
            line-height: 1.6;
        }

        /* SINGLE SLEEK SPOTIFY CARD BUBBLE WITH ROTATING VINYL DISC */
        .spotify-single-bubble {
            background: #121216;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            padding: 22px 24px;
            display: flex;
            align-items: center;
            gap: 22px;
            margin: 0 auto 35px;
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.85);
            position: relative;
        }

        /* Vinyl Disc Rotation Container */
        .vinyl-disc-container {
            position: relative;
            width: 94px;
            height: 94px;
            flex-shrink: 0;
        }

        .vinyl-disc-container img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #1a1a20;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.7);
            animation: spinVinyl 8s linear infinite;
            animation-play-state: paused;
            transition: animation-play-state 0.3s ease;
        }

        /* Center Hole on Vinyl */
        .vinyl-disc-container::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: 20px;
            background: #070709;
            border: 3px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            pointer-events: none;
            box-shadow: inset 0 0 4px rgba(0,0,0,0.8);
        }

        /* Playing state for Vinyl Disc */
        .vinyl-disc-container.is-playing img {
            animation-play-state: running;
        }

        @keyframes spinVinyl {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .spotify-player-info {
            flex: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .spotify-player-info h4 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .spotify-player-info p {
            font-size: 0.9rem;
            color: var(--text-sub);
            margin-bottom: 8px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .btn-spotify-save {
            background: none;
            border: none;
            color: var(--text-sub);
            font-size: 0.78rem;
            font-family: var(--font-sans);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .spotify-audio-controls {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 8px;
            width: 100%;
        }

        .progress-bar-fake {
            flex: 1;
            height: 4px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 2px;
            position: relative;
            cursor: pointer;
        }

        .progress-bar-fake-fill {
            width: 0%;
            height: 100%;
            background: var(--spotify-green);
            border-radius: 2px;
            transition: width 0.1s linear;
        }

        .spotify-play-btn {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #ffffff;
            color: #000000;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.2);
            flex-shrink: 0;
            padding: 0;
            line-height: 1;
        }

        .spotify-play-btn:hover {
            transform: scale(1.08);
            background: var(--spotify-green);
            color: #000000;
        }

        .modal-divider {
            font-family: var(--font-mono);
            font-size: 0.72rem;
            color: var(--text-muted);
            letter-spacing: 2.5px;
            text-transform: uppercase;
            text-align: center;
            margin: 40px 0 20px;
        }

        .modal-message-text {
            font-family: var(--font-serif);
            font-style: italic;
            font-size: 1.8rem;
            text-align: center;
            line-height: 1.5;
            color: #ffffff;
            margin-bottom: 40px;
            white-space: pre-line;
            padding: 0 10px;
        }

        .modal-meaning-box {
            background: rgba(29, 185, 84, 0.08);
            border-left: 3px solid var(--spotify-green);
            padding: 16px 20px;
            border-radius: 8px;
            font-size: 0.9rem;
            color: var(--text-sub);
            margin-top: 30px;
        }

        .modal-meaning-box strong {
            font-family: var(--font-mono);
            font-size: 0.78rem;
            color: var(--spotify-green);
            display: block;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Toast */
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #18181c;
            border: 1px solid var(--border-highlight);
            color: var(--text-main);
            padding: 14px 24px;
            border-radius: 8px;
            font-family: var(--font-mono);
            font-size: 0.8rem;
            z-index: 1200;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .features-grid { grid-template-columns: 1fr; gap: 30px; }
            .hero-title { font-size: 2.8rem; }
            .navbar { padding: 16px 20px; }
            .modal-header-title { font-size: 2rem; }
            .modal-message-text { font-size: 1.4rem; }
        }
    </style>
</head>
<body>

    <!-- Top Announcement Bar -->
    <div class="top-bar">
        SONGFORYOU — ARCHIVE YOUR FEELINGS THROUGH MUSIC. 🎵
    </div>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="#" class="brand-logo">SONGFORYOU<span>.PROJECT</span></a>
            <div class="nav-buttons">
                <button onclick="scrollToForm()" class="btn-nav-create">CREATE MESSAGE</button>
            </div>
        </div>
    </nav>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        
        <!-- Hero Section -->
        <section class="hero-section">
            <span class="hero-tag">DIGITAL SOUND ARCHIVE</span>
            <h1 class="hero-title">Curating the <i>intangible.</i></h1>
            <p class="hero-sub">Cari nama seseorang untuk melihat apakah ada lagu & pesan rahasia yang dikirimkan untuknya.</p>
            
            <!-- Search Bar -->
            <div class="search-container">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="searchInput" class="search-input" placeholder="SEARCH A NAME (E.G. ZAHRA, LOUIS)..." autocomplete="off">
            </div>

            <!-- 3 Features Columns -->
            <div class="features-grid">
                <div class="feature-item">
                    <span class="feature-num">01 /</span>
                    <h3 class="feature-title">MONOLITHIC AUDIO</h3>
                    <p class="feature-desc">Pilih lagu yang paling mewakili perasaanmu dan abadikan momen tersebut selamanya.</p>
                </div>
                <div class="feature-item">
                    <span class="feature-num">02 /</span>
                    <h3 class="feature-title">STATIC TIME CAPSULES</h3>
                    <p class="feature-desc">Kunci kenangan dengan lagu. Hanya sang penerima yang bisa menemukan dan membukanya.</p>
                </div>
                <div class="feature-item">
                    <span class="feature-num">03 /</span>
                    <h3 class="feature-title">ANONYMOUS VAULT</h3>
                    <p class="feature-desc">Sepenuhnya anonim. Kirim perasaanmu tanpa perlu mengungkapkan siapa dirimu.</p>
                </div>
            </div>
        </section>

        <!-- Archived Messages Grid Section -->
        <section id="archiveSection">
            <div id="messagesGrid" class="cards-grid">
                <!-- Cards render dynamically via JS -->
            </div>
        </section>

        <!-- Form Section: SELECT SOUNDSCAPE -->
        <section id="createFormSection" class="form-section">
            <span class="form-tech-tag">CORE_INTERFACE // V2.020</span>
            <h2 class="form-title">SELECT SOUNDSCAPE</h2>
            <p class="form-subtitle">Ungkapkan perasaanmu secara rahasia dan lampirkan lagu yang mewakilinya.</p>

            <form id="createMessageForm">
                <div class="form-group">
                    <label class="form-label" for="receiver">TO: (NAMA PENERIMA)</label>
                    <input type="text" id="receiver" class="form-control" placeholder="Contoh: zahra, louis" required maxlength="100">
                    <span class="form-helper">Ketikkan nama panggilan yang sering digunakan agar mudah dicari.</span>
                </div>

                <div class="form-group song-select-custom">
                    <label class="form-label" for="songSearchInput">PILIH LAGU (LIVE SPOTIFY SEARCH)</label>
                    <input type="text" id="songSearchInput" class="form-control" placeholder="Ketik judul lagu atau nama penyanyi di Spotify..." autocomplete="off" required>
                    
                    <div id="songDropdown" class="song-results-dropdown"></div>
                    <input type="hidden" id="selectedSongKey" value="">
                </div>

                <div class="form-group">
                    <label class="form-label">LAMPIRKAN FOTO KENANGAN (OPSIONAL)</label>
                    <div class="file-upload-box" onclick="document.getElementById('fileInput').click()">
                        <i class="fa-solid fa-camera"></i>
                        <span id="fileNameDisplay">Pilih Foto (Klik atau seret)</span>
                    </div>
                    <input type="file" id="fileInput" accept="image/*" style="display: none;" onchange="handleFileSelected(this)">
                </div>

                <div class="form-group">
                    <label class="form-label" for="messageText">PESAN / SURAT RAHASIA</label>
                    <textarea id="messageText" class="form-control" placeholder="Tuliskan isi hatimu disini..." required maxlength="5000"></textarea>
                </div>

                <button type="submit" id="btnSubmitForm" class="btn-submit-form">KIRIM PESAN RAHASIA</button>
            </form>
        </section>

    </div>

    <!-- FULL SCREEN MESSAGE VIEW PAGE -->
    <div id="fullScreenMessagePage" class="fullscreen-message-page">
        <div class="fullscreen-content-container">
            <button class="btn-fullscreen-back" onclick="closeFullScreenMessage()"><i class="fa-solid fa-arrow-left"></i> KEMBALI KE ARCHIVE</button>
            
            <span class="modal-tech-tag">NOW ENCODING // FROM SECRETS</span>
            <h2 class="modal-header-title">Hello, <i id="fullReceiverName">fiwa</i></h2>
            <p class="modal-header-sub">There's someone sending you a song, they want you to hear this song that maybe you'll like :)</p>

            <!-- SINGLE SLEEK SPOTIFY CARD BUBBLE WITH ROTATING VINYL DISC -->
            <div class="spotify-single-bubble">
                <div class="vinyl-disc-container" id="fullVinylContainer">
                    <img id="fullCover" src="https://i.scdn.co/image/ab67616d0000b27341ad37380f2d8e05a81a7b44" alt="Album Cover">
                </div>
                <div class="spotify-player-info">
                    <h4 id="fullTitle">Perfect</h4>
                    <p id="fullArtist">Ed Sheeran</p>
                    <span class="btn-spotify-save"><i class="fa-brands fa-spotify" style="color: var(--spotify-green);"></i> SongForYou In-Browser Audio Player</span>
                    
                    <div class="spotify-audio-controls">
                        <button class="spotify-play-btn" id="btnFullPlayAudio" onclick="toggleAudioPlayback()"><i class="fa-solid fa-play" style="margin-left: 2px;"></i></button>
                        <div class="progress-bar-fake" onclick="seekAudio(event)">
                            <div id="fullProgressBarFill" class="progress-bar-fake-fill"></div>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);" id="fullAudioTime">00:00</span>
                    </div>
                </div>
            </div>

            <div class="modal-divider">— ALSO, HERE'S A MESSAGE FROM THE SENDER:</div>
            <div class="modal-message-text" id="fullMessageText">"test"</div>

            <div id="fullAttachmentBox" style="display: none; margin-bottom: 30px; text-align: center;">
                <img id="fullAttachmentImg" src="" alt="Attachment" style="max-width: 100%; border-radius: 12px; max-height: 350px; box-shadow: 0 10px 30px rgba(0,0,0,0.8);">
            </div>

            <div id="fullMeaningBox" class="modal-meaning-box" style="display: none;">
                <strong>♫ MAKNA LAGU</strong>
                <span id="fullMeaningText">-</span>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="toast">Pesan berhasil dikirim!</div>

    <script>
        let allMessages = [];
        let selectedSong = null;
        
        // In-Browser Audio Player
        const audioInstance = new Audio();
        let isAudioPlaying = false;
        let currentMessageObj = null;
        let currentCardHasPlayed = false;

        document.addEventListener('DOMContentLoaded', () => {
            fetchMessages();
            setupSongSearch();

            document.getElementById('searchInput').addEventListener('input', (e) => {
                const q = e.target.value.trim().toLowerCase();
                renderMessages(allMessages.filter(m => 
                    (m.receiver && m.receiver.toLowerCase().includes(q)) || 
                    (m.songTitle && m.songTitle.toLowerCase().includes(q)) || 
                    (m.songArtist && m.songArtist.toLowerCase().includes(q))
                ));
            });

            document.getElementById('createMessageForm').addEventListener('submit', handleFormSubmit);

            // Handle direct URL query param
            const urlParams = new URLSearchParams(window.location.search);
            const slugParam = urlParams.get('slug') || urlParams.get('msg');
            if (slugParam) {
                setTimeout(() => openFullScreenMessage(slugParam), 500);
            }
        });

        // Audio progress events
        audioInstance.addEventListener('timeupdate', () => {
            if (audioInstance.duration) {
                const pct = (audioInstance.currentTime / audioInstance.duration) * 100;
                document.getElementById('fullProgressBarFill').style.width = pct + '%';
                
                const curMins = Math.floor(audioInstance.currentTime / 60);
                const curSecs = Math.floor(audioInstance.currentTime % 60).toString().padStart(2, '0');
                const durMins = Math.floor(audioInstance.duration / 60);
                const durSecs = Math.floor(audioInstance.duration % 60).toString().padStart(2, '0');
                
                document.getElementById('fullAudioTime').textContent = `${curMins}:${curSecs} / ${durMins}:${durSecs}`;
            }
        });

        audioInstance.addEventListener('ended', () => {
            stopPlaybackState();
        });

        function scrollToForm() {
            document.getElementById('createFormSection').scrollIntoView({ behavior: 'smooth' });
        }

        const getApiUrl = (endpoint) => {
            // On Vercel: /backend/xxx -> routes to /api/backend.php?endpoint=xxx
            // On local: ./backend/xxx -> serves file directly
            const isVercel = window.location.hostname.includes('vercel.app') || 
                             window.location.hostname.includes('vercel.com') ||
                             (!window.location.hostname.includes('localhost') && !window.location.hostname.includes('127.0.0.1'));
            if (isVercel) {
                return '/backend/' + endpoint;
            }
            let base = window.location.pathname;
            if (!base.endsWith('/')) {
                if (!base.endsWith('.php')) {
                    base += '/';
                } else {
                    base = base.substring(0, base.lastIndexOf('/') + 1);
                }
            }
            return base + 'backend/' + endpoint;
        };

        // Helper: resolve path relatif (mis: uploads/foto.jpg) ke URL absolut app
        const getAppUrl = (path) => {
            if (!path) return '';
            if (path.startsWith('http') || path.startsWith('blob:') || path.startsWith('data:')) return path;
            let base = window.location.pathname;
            if (!base.endsWith('/')) {
                base = base.substring(0, base.lastIndexOf('/') + 1);
            }
            return base + path;
        };

        // Helper: Ambil preview URL dari iTunes Search API (country=ID) & Deezer API Fallback
        const getItunesPreview = async (title, artist) => {
            const cleanTitle = (title || '').replace(/\(feat\.[^)]+\)/gi, '').replace(/\([^)]+\)/g, '').replace(/\[[^\]]+\]/g, '').replace(/-\s*.*$/, '').trim();
            const cleanArtist = (artist || '').split(',')[0].split('&')[0].trim();
            const q = encodeURIComponent(`${cleanTitle} ${cleanArtist}`);

            // 1. Tembak iTunes API dengan region ID (country=ID)
            try {
                const res = await fetch(`https://itunes.apple.com/search?term=${q}&country=ID&media=music&entity=song&limit=10`, { signal: AbortSignal.timeout(3500) });
                const data = await res.json();

                if (data.results && data.results.length > 0) {
                    const targetTitle = cleanTitle.toLowerCase();
                    const targetArtist = cleanArtist.toLowerCase();

                    // Priority 1: Match judul dan penyanyi secara akurat
                    for (const track of data.results) {
                        if (!track.previewUrl) continue;
                        const trTitle = (track.trackName || '').toLowerCase();
                        const trArtist = (track.artistName || '').toLowerCase();
                        if ((trTitle.includes(targetTitle) || targetTitle.includes(trTitle)) &&
                            (trArtist.includes(targetArtist) || targetArtist.includes(trArtist))) {
                            return track.previewUrl;
                        }
                    }

                    // Priority 2: Match judul jika penyanyi mirip
                    for (const track of data.results) {
                        if (!track.previewUrl) continue;
                        const trTitle = (track.trackName || '').toLowerCase();
                        if (trTitle.includes(targetTitle) || targetTitle.includes(trTitle)) {
                            return track.previewUrl;
                        }
                    }

                    for (const track of data.results) {
                        if (track.previewUrl) return track.previewUrl;
                    }
                }
            } catch(e) {}

            // 2. Fallback: Deezer Search API (Gratis, fast, no-auth)
            try {
                const res2 = await fetch(`https://api.deezer.com/search?q=${q}&limit=5`, { signal: AbortSignal.timeout(3500) });
                const data2 = await res2.json();

                if (data2.data && data2.data.length > 0) {
                    const targetTitle = cleanTitle.toLowerCase();
                    const targetArtist = cleanArtist.toLowerCase();

                    for (const track of data2.data) {
                        if (!track.preview) continue;
                        const trTitle = (track.title || '').toLowerCase();
                        const trArtist = (track.artist && track.artist.name ? track.artist.name : '').toLowerCase();

                        if ((trTitle.includes(targetTitle) || targetTitle.includes(trTitle)) &&
                            (trArtist.includes(targetArtist) || targetArtist.includes(trArtist))) {
                            return track.preview;
                        }
                    }

                    for (const track of data2.data) {
                        if (track.preview) return track.preview;
                    }
                }
            } catch(e) {}

            return null;
        };


        // Fetch Messages from DB
        async function fetchMessages() {
            try {
                const res = await fetch(getApiUrl('get_messages.php'));
                const data = await res.json();
                if (data.success) {
                    allMessages = data.messages;
                    renderMessages(allMessages);
                }
            } catch (err) {
                console.error('Fetch messages error:', err);
                renderMessages([]);
            }
        }

        // Render Cards Grid
        function renderMessages(messages) {
            const grid = document.getElementById('messagesGrid');
            if (!messages || messages.length === 0) {
                grid.innerHTML = `
                    <div class="empty-state">
                        <i class="fa-regular fa-folder-open"></i>
                        <h3>BELUM ADA PESAN RAHASIA</h3>
                        <p>Jadilah yang pertama untuk membuat dan mengabadikan pesan rahasia lewat lagu pilihanmu.</p>
                        <button onclick="scrollToForm()" class="btn-nav-create">CREATE MESSAGE</button>
                    </div>
                `;
                return;
            }

            grid.innerHTML = messages.map(msg => {
                const cover = msg.songCover || 'https://via.placeholder.com/60';
                const title = escapeHtml(msg.songTitle || 'Lagu Pilihan');
                const artist = escapeHtml(msg.songArtist || 'Artist');
                const receiver = escapeHtml(msg.receiver.toUpperCase());
                const quote = escapeHtml(msg.message);

                return `
                    <div class="msg-card" onclick="openFullScreenMessage('${msg.slug}')">
                        <div>
                            <span class="msg-card-tag">TO: ${receiver}</span>
                            <div class="msg-card-quote">${quote}</div>
                            ${msg.images ? `<img src="${getAppUrl(msg.images)}" class="msg-card-image" alt="Attachment">` : ''}
                        </div>
                        <div class="msg-card-audiobar">
                            <img src="${escapeHtml(cover)}" alt="Cover">
                            <div class="song-meta">
                                <div class="title">${title}</div>
                                <div class="artist">${artist}</div>
                            </div>
                            <i class="fa-brands fa-spotify"></i>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Setup Real-time Live Spotify Search Selector
        function setupSongSearch() {
            const input = document.getElementById('songSearchInput');
            const dropdown = document.getElementById('songDropdown');
            let debounceTimer;

            const performSearch = async (query) => {
                dropdown.innerHTML = `<div style="padding: 12px; text-align: center; color: #71717a; font-size: 0.8rem; font-family: var(--font-mono);"><i class="fa-solid fa-spinner fa-spin"></i> SEARCHING...</div>`;
                dropdown.style.display = 'block';

                try {
                    const res = await fetch(getApiUrl(`spotify_search.php?q=${encodeURIComponent(query)}`));
                    const data = await res.json();
                    if (data.success && data.tracks && data.tracks.length > 0) {
                        renderSongDropdown(data.tracks);
                    } else {
                        dropdown.innerHTML = `<div style="padding: 12px; text-align: center; color: #71717a; font-size: 0.8rem; font-family: var(--font-mono);">LAGU TIDAK DITEMUKAN</div>`;
                    }
                } catch (err) {
                    console.error('Spotify Search Error:', err);
                    dropdown.style.display = 'none';
                }
            };

            const triggerSearch = () => {
                const q = input.value.trim();
                performSearch(q || 'viral indonesia');
            };

            input.addEventListener('focus', triggerSearch);
            input.addEventListener('click', triggerSearch);

            input.addEventListener('input', (e) => {
                clearTimeout(debounceTimer);
                const q = e.target.value.trim();
                debounceTimer = setTimeout(() => {
                    performSearch(q || 'viral indonesia');
                }, 80);
            });

            document.addEventListener('click', (e) => {
                if (!e.target.closest('.song-select-custom')) {
                    dropdown.style.display = 'none';
                }
            });
        }

        let _dropdownTracks = [];
        function renderSongDropdown(tracks) {
            const dropdown = document.getElementById('songDropdown');
            if (!tracks || tracks.length === 0) {
                dropdown.style.display = 'none';
                return;
            }
            _dropdownTracks = tracks;

            dropdown.innerHTML = tracks.map((t, i) => `
                <div class="song-result-item" data-track-idx="${i}">
                    <img src="${escapeHtml(t.coverUrl || 'https://via.placeholder.com/42')}" alt="Cover">
                    <div>
                        <div class="song-title">${escapeHtml(t.title)}</div>
                        <div class="song-artist">${escapeHtml(t.artist)}</div>
                    </div>
                </div>
            `).join('');
            dropdown.style.display = 'block';

            dropdown.querySelectorAll('.song-result-item').forEach(el => {
                el.addEventListener('click', () => {
                    const idx = parseInt(el.getAttribute('data-track-idx'));
                    selectSong(_dropdownTracks[idx]);
                });
            });
        }

        async function selectSong(t) {
            selectedSong = t;
            document.getElementById('selectedSongKey').value = t.spotifyId;
            document.getElementById('songSearchInput').value = t.title + ' — ' + t.artist;
            document.getElementById('songDropdown').style.display = 'none';

            // Jika previewUrl kosong, ambil dari iTunes secara langsung (cepat)
            if (!selectedSong.previewUrl) {
                const preview = await getItunesPreview(t.title, t.artist);
                if (preview) selectedSong.previewUrl = preview;
            }
        }

        function handleFileSelected(input) {
            const display = document.getElementById('fileNameDisplay');
            if (input.files && input.files[0]) {
                display.textContent = 'Foto dipilih: ' + input.files[0].name;
            } else {
                display.textContent = 'Pilih Foto (Klik atau seret)';
            }
        }

        // Form Submit Handler
        async function handleFormSubmit(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmitForm');
            const receiver = document.getElementById('receiver').value.trim();
            const message = document.getElementById('messageText').value.trim();
            const songKey = document.getElementById('selectedSongKey').value;
            const fileInput = document.getElementById('fileInput');

            if (!selectedSong) {
                alert('Silakan pilih lagu dari hasil pencarian Spotify terlebih dahulu!');
                return;
            }

            btn.disabled = true;
            btn.textContent = 'MEMPROSES PESAN...';

            let base64Img = '';
            if (fileInput.files && fileInput.files[0]) {
                base64Img = await fileToBase64(fileInput.files[0]);
            }

            const payload = {
                receiver: receiver,
                senderName: 'Anonim',
                songKey: selectedSong.spotifyId || songKey,
                message: message,
                images: base64Img,
                songDetails: {
                    title: selectedSong.title,
                    artist: selectedSong.artist,
                    coverUrl: selectedSong.coverUrl,
                    meaning: selectedSong.meaning || 'Lagu ini melambangkan perasaan mendalam.',
                    spotifyUrl: selectedSong.spotifyUrl || '',
                    previewUrl: selectedSong.previewUrl || null
                }
            };

            try {
                const res = await fetch(getApiUrl('submit_message.php'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                if (data.success) {
                    showToast('PESAN RAHASIA BERHASIL DISIMPAN!');
                    document.getElementById('createMessageForm').reset();
                    document.getElementById('fileNameDisplay').textContent = 'Pilih Foto (Klik atau seret)';
                    selectedSong = null;
                    fetchMessages();
                    
                    if (data.slug) {
                        setTimeout(() => openFullScreenMessage(data.slug), 400);
                    } else {
                        document.getElementById('archiveSection').scrollIntoView({ behavior: 'smooth' });
                    }
                } else {
                    alert('Gagal mengirim pesan: ' + (data.message || 'Error'));
                }
            } catch (err) {
                console.error('Submit error:', err);
                alert('Terjadi kesalahan koneksi.');
            } finally {
                btn.disabled = false;
                btn.textContent = 'KIRIM PESAN RAHASIA';
            }
        }

        // OPEN FULL SCREEN MESSAGE VIEW WITH EXACT SPOTIFY AUDIO
        async function openFullScreenMessage(slug) {
            const msg = allMessages.find(m => m.slug === slug);
            if (!msg) return;

            currentMessageObj = msg;
            currentCardHasPlayed = false;

            document.getElementById('fullReceiverName').textContent = msg.receiver;
            document.getElementById('fullCover').src = msg.songCover || 'https://via.placeholder.com/100';
            document.getElementById('fullTitle').textContent = msg.songTitle || 'Lagu Pilihan';
            document.getElementById('fullArtist').textContent = msg.songArtist || 'Artist';
            document.getElementById('fullMessageText').textContent = msg.message;

            const meaningBox = document.getElementById('fullMeaningBox');
            if (msg.songMeaning) {
                document.getElementById('fullMeaningText').textContent = msg.songMeaning;
                meaningBox.style.display = 'block';
            } else {
                meaningBox.style.display = 'none';
            }

            // Tampilkan gambar dengan path yang benar menggunakan getAppUrl helper
            const imgBox = document.getElementById('fullAttachmentBox');
            if (msg.images) {
                document.getElementById('fullAttachmentImg').src = getAppUrl(msg.images);
                imgBox.style.display = 'block';
            } else {
                imgBox.style.display = 'none';
            }

            // Reset Audio State & Vinyl Disc Rotation
            stopPlaybackState();

            // Selalu verifikasi dan dapatkan preview audio 100% akurat dari iTunes (country=ID) / Deezer
            let audioUrl = null;
            if (msg.songTitle && msg.songArtist) {
                audioUrl = await getItunesPreview(msg.songTitle, msg.songArtist);
            }
            if (!audioUrl) {
                audioUrl = msg.previewUrl;
            }

            if (audioUrl) {
                try {
                    audioInstance.pause();
                    audioInstance.removeAttribute('src');
                    audioInstance.load();
                } catch(e) {}

                audioInstance.src = audioUrl;
                audioInstance.load();
                
                audioInstance.onloadedmetadata = () => {
                    if (audioInstance.duration && !isNaN(audioInstance.duration)) {
                        const durMins = Math.floor(audioInstance.duration / 60);
                        const durSecs = Math.floor(audioInstance.duration % 60).toString().padStart(2, '0');
                        
                        // Reff/Korus untuk sampel audio 30 detik biasanya dimulai di detik 12 - 15 (45% durasi)
                        const targetReff = Math.min(14, audioInstance.duration * 0.45);
                        try {
                            audioInstance.currentTime = targetReff;
                        } catch(e) {}

                        const curMins = Math.floor(targetReff / 60);
                        const curSecs = Math.floor(targetReff % 60).toString().padStart(2, '0');
                        document.getElementById('fullAudioTime').textContent = `${curMins}:${curSecs} / ${durMins}:${durSecs}`;
                    }
                };
            } else {
                audioInstance.removeAttribute('src');
            }

            // Lock body scroll dan tampilkan full screen page
            document.body.style.overflow = 'hidden';
            document.getElementById('fullScreenMessagePage').classList.add('active');
        }

        // TOGGLE IN-BROWSER AUDIO PLAYBACK FOR THE EXACT SPOTIFY SONG (MENUNGGU KLIK USER)
        function toggleAudioPlayback() {
            const playBtn = document.getElementById('btnFullPlayAudio');
            const vinyl = document.getElementById('fullVinylContainer');

            if (!audioInstance.src || audioInstance.src === '' || audioInstance.src === window.location.href) {
                showToast('Audio preview sedang dimuat atau tidak tersedia untuk lagu ini...');
                return;
            }

            if (isAudioPlaying) {
                audioInstance.pause();
                isAudioPlaying = false;
                playBtn.innerHTML = '<i class="fa-solid fa-play" style="margin-left: 2px;"></i>';
                vinyl.classList.remove('is-playing');
            } else {
                const isReStart = !currentCardHasPlayed || audioInstance.ended || (audioInstance.duration && audioInstance.currentTime >= audioInstance.duration - 0.3);

                audioInstance.play().then(() => {
                    isAudioPlaying = true;
                    playBtn.innerHTML = '<i class="fa-solid fa-pause"></i>';
                    vinyl.classList.add('is-playing');

                    // Setel timestamp ke Reff/Korus setelah audio diputar agar tidak ter-reset browser
                    if (isReStart) {
                        try {
                            const targetReffTime = Math.min(14, (audioInstance.duration || 30) * 0.45);
                            audioInstance.currentTime = targetReffTime;
                        } catch(e) {
                            console.error('Reff seek error:', e);
                        }
                        currentCardHasPlayed = true;
                    }
                }).catch(err => {
                    console.error('Audio play error:', err);
                    showToast('Klik sekali lagi untuk memutar lagu.');
                });
            }
        }

        function stopPlaybackState() {
            try {
                audioInstance.pause();
                audioInstance.currentTime = 0;
            } catch(e) {}
            
            isAudioPlaying = false;
            currentCardHasPlayed = false;
            const playBtn = document.getElementById('btnFullPlayAudio');
            const vinyl = document.getElementById('fullVinylContainer');
            if (playBtn) playBtn.innerHTML = '<i class="fa-solid fa-play" style="margin-left: 2px;"></i>';
            if (vinyl) vinyl.classList.remove('is-playing');
            document.getElementById('fullProgressBarFill').style.width = '0%';
            document.getElementById('fullAudioTime').textContent = '00:00';
        }

        function seekAudio(e) {
            if (!audioInstance.duration || !audioInstance.src) return;
            const bar = e.currentTarget;
            const rect = bar.getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const pct = clickX / rect.width;
            audioInstance.currentTime = pct * audioInstance.duration;
        }

        function closeFullScreenMessage() {
            stopPlaybackState();
            document.body.style.overflow = 'auto';
            document.getElementById('fullScreenMessagePage').classList.remove('active');
        }

        function fileToBase64(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = () => resolve(reader.result);
                reader.onerror = error => reject(error);
            });
        }

        function showToast(text) {
            const toast = document.getElementById('toast');
            toast.textContent = text;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3500);
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    </script>
</body>
</html>
