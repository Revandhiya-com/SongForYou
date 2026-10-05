<?php
/*
 * /Website-SFY/index.php
 * SongForYou — Archive Your Feelings Through Music
 * Redesigned & Refined:
 * - Removed "Dari Siapa?" (Sender) field per user request
 * - Preserved Spotify & iTunes real-time search & official artwork
 * - Enhanced audio playback with automatic 30s preview fallback
 * - Structured Photo & Song Meaning display
 * - Jesper Landberg aesthetic & full mobile zoom lock
 */
header('Content-Type: text/html; charset=utf-8');

$requestUri = $_SERVER['REQUEST_URI'];
$parsedPath = parse_url($requestUri, PHP_URL_PATH);

if (substr($parsedPath, -1) !== '/' && !str_ends_with($parsedPath, '.php')) {
    $queryString = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '';
    header('Location: ' . $parsedPath . '/' . $queryString, true, 301);
    exit;
}

if (preg_match('/\/admin\/?$/i', $parsedPath)) {
    header('Location: /admin/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SongForYou — Bisikkan Perasaanmu Lewat Lagu</title>
    <meta name="description" content="Kirimkan pesan rahasia, ungkapan hati, dan lagu spesial untuk seseorang yang berarti dalam hidupmu.">

    <!-- Fonts: DM Serif Display, Syne, Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700&family=Syne:wght@500;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-dark: #08080a;
            --bg-card: #121216;
            --bg-card-hover: #181820;
            --bg-input: #1a1a22;
            --border-color: rgba(255, 255, 255, 0.08);
            --border-highlight: rgba(255, 255, 255, 0.18);
            --text-main: #f3f3f6;
            --text-muted: #8e8e9e;
            --accent-green: #1db954;
            --accent-glow: rgba(29, 185, 84, 0.15);
            --accent-warm: #e5c07b;
            --font-serif: 'DM Serif Display', Georgia, serif;
            --font-heading: 'Syne', sans-serif;
            --font-body: 'Inter', system-ui, sans-serif;
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-sm: 8px;
            --transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* RESET & BASE */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        html, body {
            width: 100%;
            height: 100%;
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: var(--font-body);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }

        /* SCROLLBAR */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: var(--bg-dark);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* CONTAINER & LAYOUT */
        .app-container {
            max-width: 1160px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        /* HEADER / NAVBAR */
        header.navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.75rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        .brand-logo {
            font-family: var(--font-heading);
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: -0.5px;
            color: var(--text-main);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .brand-logo i {
            color: var(--accent-green);
            font-size: 1.1rem;
        }
        .btn-nav {
            font-family: var(--font-heading);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 0.65rem 1.4rem;
            border-radius: 100px;
            border: 1px solid var(--border-color);
            background: rgba(255, 255, 255, 0.03);
            color: var(--text-main);
            text-decoration: none;
            transition: var(--transition);
        }
        .btn-nav:hover {
            border-color: var(--text-main);
            background: var(--text-main);
            color: var(--bg-dark);
        }

        /* HERO SECTION */
        .hero-section {
            padding: 4.5rem 0 3.5rem;
            text-align: center;
            position: relative;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 1.1rem;
            border-radius: 100px;
            background: var(--accent-glow);
            border: 1px solid rgba(29, 185, 84, 0.3);
            color: var(--accent-green);
            font-family: var(--font-heading);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }
        .hero-title {
            font-family: var(--font-serif);
            font-size: clamp(2.4rem, 5.5vw, 4.5rem);
            line-height: 1.12;
            font-weight: 400;
            color: var(--text-main);
            margin-bottom: 1.25rem;
        }
        .hero-title em {
            font-style: italic;
            color: var(--accent-green);
        }
        .hero-desc {
            max-width: 580px;
            margin: 0 auto 2.5rem;
            font-size: 1.05rem;
            line-height: 1.6;
            color: var(--text-muted);
        }
        .hero-cta-group {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .btn-primary {
            font-family: var(--font-heading);
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 1rem 2.2rem;
            border-radius: 100px;
            background: var(--text-main);
            color: var(--bg-dark);
            border: 1px solid var(--text-main);
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            box-shadow: 0 10px 30px rgba(255,255,255,0.1);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            background: #ffffff;
            box-shadow: 0 14px 40px rgba(255,255,255,0.2);
        }
        .btn-secondary {
            font-family: var(--font-heading);
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 1rem 2.2rem;
            border-radius: 100px;
            background: transparent;
            color: var(--text-main);
            border: 1px solid var(--border-color);
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition);
        }
        .btn-secondary:hover {
            border-color: var(--border-highlight);
            background: rgba(255, 255, 255, 0.05);
        }

        /* PILLARS GRID */
        .pillars-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.5rem;
            margin: 3rem 0 4rem;
        }
        .pillar-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1.75rem;
            transition: var(--transition);
        }
        .pillar-card:hover {
            border-color: var(--border-highlight);
            transform: translateY(-3px);
        }
        .pillar-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-green);
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }
        .pillar-title {
            font-family: var(--font-heading);
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 0.4rem;
        }
        .pillar-text {
            font-size: 0.88rem;
            color: var(--text-muted);
            line-height: 1.55;
        }

        /* SECTION HEADERS */
        .section-header {
            margin: 4rem 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .section-tag {
            font-family: var(--font-heading);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--accent-green);
            margin-bottom: 0.5rem;
        }
        .section-title {
            font-family: var(--font-serif);
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            font-weight: 400;
            line-height: 1.15;
        }
        .section-desc {
            color: var(--text-muted);
            font-size: 0.95rem;
            max-width: 500px;
            line-height: 1.5;
        }

        /* FEATURED CAROUSEL */
        .carousel-wrapper {
            position: relative;
            margin-bottom: 4rem;
        }
        .carousel-track-container {
            overflow-x: auto;
            scroll-behavior: smooth;
            scrollbar-width: none;
            -ms-overflow-style: none;
            padding: 0.5rem 0;
        }
        .carousel-track-container::-webkit-scrollbar {
            display: none;
        }
        .carousel-track {
            display: flex;
            gap: 1.5rem;
        }
        .carousel-card {
            flex: 0 0 320px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: var(--transition);
            cursor: pointer;
        }
        .carousel-card:hover {
            border-color: var(--border-highlight);
            transform: translateY(-4px);
        }
        .card-recipient-tag {
            font-family: var(--font-heading);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--accent-green);
        }
        .card-recipient-name {
            font-family: var(--font-serif);
            font-size: 1.4rem;
            margin: 0.3rem 0 0.8rem;
            color: var(--text-main);
        }
        .card-message-snippet {
            font-size: 0.9rem;
            line-height: 1.6;
            color: #b0b0c0;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-style: italic;
        }

        /* FORM SECTION */
        .form-section {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: clamp(1.75rem, 4vw, 3.5rem);
            margin: 4rem 0;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.75rem;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            position: relative;
        }
        .form-label {
            font-family: var(--font-heading);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .form-input, .form-textarea {
            width: 100%;
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 1rem 1.2rem;
            color: var(--text-main);
            font-family: var(--font-body);
            font-size: 0.95rem;
            outline: none;
            transition: var(--transition);
        }
        .form-input:focus, .form-textarea:focus {
            border-color: var(--accent-green);
            background: #1e1e28;
            box-shadow: 0 0 0 4px var(--accent-glow);
        }
        .form-textarea {
            resize: vertical;
            min-height: 130px;
        }

        /* SONG SEARCH DROPDOWN */
        .song-search-container {
            position: relative;
        }
        .song-dropdown {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            background: #161620;
            border: 1px solid var(--border-highlight);
            border-radius: var(--radius-md);
            max-height: 300px;
            overflow-y: auto;
            z-index: 100;
            box-shadow: 0 15px 40px rgba(0,0,0,0.8);
            display: none;
        }
        .song-option {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            padding: 0.85rem 1.1rem;
            cursor: pointer;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            transition: background 0.2s ease;
        }
        .song-option:last-child {
            border-bottom: none;
        }
        .song-option:hover {
            background: rgba(255,255,255,0.08);
        }
        .song-option img {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
        }
        .song-option-info {
            flex: 1;
            min-width: 0;
        }
        .song-option-info h5 {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .song-option-info p {
            font-size: 0.78rem;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .spotify-tag-badge {
            font-size: 0.75rem;
            color: var(--accent-green);
            margin-left: 0.5rem;
        }

        /* SELECTED SONG DISPLAY BOX */
        .selected-song-bubble {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(29, 185, 84, 0.08);
            border: 1px solid rgba(29, 185, 84, 0.3);
            border-radius: var(--radius-md);
            padding: 0.9rem 1.1rem;
            margin-top: 0.6rem;
            gap: 1rem;
        }
        .song-preview-meta {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            min-width: 0;
        }
        .song-preview-meta img {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
        }
        .song-preview-meta h4 {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .song-preview-meta p {
            font-size: 0.8rem;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .btn-remove-song {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1.1rem;
            padding: 0.4rem;
            transition: color 0.2s ease;
            flex-shrink: 0;
        }
        .btn-remove-song:hover {
            color: #ff5555;
        }

        /* FILE DROPZONE */
        .file-dropzone {
            border: 2px dashed var(--border-color);
            border-radius: var(--radius-md);
            padding: 1.6rem;
            text-align: center;
            background: var(--bg-input);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
        }
        .file-dropzone:hover {
            border-color: var(--accent-green);
            background: rgba(29, 185, 84, 0.04);
        }
        .file-dropzone input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        .file-preview-thumb {
            max-height: 160px;
            border-radius: 8px;
            margin-top: 0.8rem;
            object-fit: cover;
            display: none;
        }

        /* MESSAGES FEED GRID */
        .messages-search-bar {
            margin-bottom: 2rem;
        }
        .messages-search-input {
            width: 100%;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 100px;
            padding: 0.9rem 1.6rem;
            color: var(--text-main);
            font-size: 0.92rem;
            outline: none;
            transition: var(--transition);
        }
        .messages-search-input:focus {
            border-color: var(--border-highlight);
            background: var(--bg-card-hover);
        }

        .messages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 1.75rem;
        }
        @media (max-width: 480px) {
            .messages-grid {
                grid-template-columns: 1fr;
            }
        }

        /* MESSAGE CARD STYLING */
        .message-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1.6rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: var(--transition);
            position: relative;
        }
        .message-card:hover {
            border-color: var(--border-highlight);
            background: var(--bg-card-hover);
            transform: translateY(-3px);
        }
        .card-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.1rem;
        }
        .card-to {
            font-family: var(--font-serif);
            font-size: 1.35rem;
            color: var(--text-main);
            line-height: 1.2;
        }
        .card-date {
            font-size: 0.72rem;
            color: var(--text-muted);
            white-space: nowrap;
        }
        .card-image-wrap {
            width: 100%;
            height: 200px;
            border-radius: var(--radius-md);
            overflow: hidden;
            margin-bottom: 1.2rem;
            cursor: pointer;
            position: relative;
        }
        .card-image-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }
        .card-image-wrap:hover img {
            transform: scale(1.04);
        }
        .card-body-text {
            font-size: 0.95rem;
            line-height: 1.65;
            color: #d5d5e2;
            margin-bottom: 1.2rem;
            word-wrap: break-word;
        }

        /* SONG MEANING BOX */
        .song-meaning-box {
            background: rgba(255, 255, 255, 0.03);
            border-left: 3px solid var(--accent-green);
            border-radius: 6px;
            padding: 0.8rem 1rem;
            margin-bottom: 1.2rem;
        }
        .meaning-header {
            font-family: var(--font-heading);
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--accent-green);
            margin-bottom: 0.3rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .meaning-text {
            font-size: 0.84rem;
            color: var(--text-muted);
            line-height: 1.5;
            font-style: italic;
        }

        /* AUDIO PLAYER BAR */
        .player-bar {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: var(--radius-md);
            padding: 0.8rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.9rem;
        }
        .player-cover {
            width: 46px;
            height: 46px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
        }
        .player-meta {
            flex: 1;
            min-width: 0;
        }
        .player-title {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .player-artist {
            font-size: 0.76rem;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .player-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
        }
        .btn-play-icon {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--accent-green);
            color: #000;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.2s ease, background 0.2s ease;
        }
        .btn-play-icon:hover {
            transform: scale(1.08);
            background: #1ed760;
        }
        .spotify-link-btn {
            color: var(--text-muted);
            font-size: 1.1rem;
            transition: color 0.2s ease;
            text-decoration: none;
        }
        .spotify-link-btn:hover {
            color: var(--accent-green);
        }

        /* MODAL OVERLAY */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.85);
            backdrop-filter: blur(12px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .modal-card {
            background: var(--bg-card);
            border: 1px solid var(--border-highlight);
            border-radius: 24px;
            max-width: 620px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            padding: 2.2rem;
            position: relative;
        }
        .btn-close-modal {
            position: absolute;
            top: 1.2rem;
            right: 1.2rem;
            background: rgba(255,255,255,0.1);
            border: none;
            color: var(--text-main);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s ease;
        }
        .btn-close-modal:hover {
            background: rgba(255,255,255,0.25);
        }

        /* TOAST */
        .toast-container {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 2000;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .toast {
            background: #1e1e28;
            border: 1px solid var(--border-highlight);
            border-left: 4px solid var(--accent-green);
            color: var(--text-main);
            padding: 1rem 1.4rem;
            border-radius: var(--radius-md);
            font-size: 0.88rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            animation: slideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* FOOTER */
        footer {
            border-top: 1px solid var(--border-color);
            padding: 3rem 0;
            margin-top: 6rem;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        /* MOBILE RESPONSIVE */
        @media (max-width: 600px) {
            .app-container {
                padding: 0 1rem;
            }
            .hero-title {
                font-size: 2.2rem;
            }
            .hero-desc {
                font-size: 0.92rem;
            }
            .btn-primary, .btn-secondary {
                width: 100%;
                justify-content: center;
            }
            .form-section {
                padding: 1.5rem 1.2rem;
                border-radius: 16px;
            }
            .message-card {
                padding: 1.25rem;
            }
            .toast-container {
                left: 1rem;
                right: 1rem;
                bottom: 1rem;
            }
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <header class="navbar app-container">
        <a href="#" class="brand-logo">
            <i class="fa-solid fa-music"></i>
            <span>SongForYou</span>
        </a>
        <a href="#formSection" class="btn-nav">Kirim Pesan</a>
    </header>

    <!-- HERO SECTION -->
    <section class="hero-section app-container">
        <div class="hero-badge">
            <i class="fa-brands fa-spotify"></i>
            <span>Music & Emotion Archive</span>
        </div>
        <h1 class="hero-title">
            Bisikkan Perasaanmu<br>Lewat <em>Lagu Spesial</em>
        </h1>
        <p class="hero-desc">
            Sampaikan pesan rahasia, ungkapan hati, dan lagu kenangan untuk seseorang yang berharga dalam hidupmu.
        </p>
        <div class="hero-cta-group">
            <a href="#formSection" class="btn-primary">
                <i class="fa-solid fa-paper-plane"></i>
                Tulis Pesan Rahasia
            </a>
            <a href="#feedSection" class="btn-secondary">
                Jelajahi Pesan
            </a>
        </div>
    </section>

    <!-- PILLARS / FEATURES -->
    <div class="app-container">
        <div class="pillars-grid">
            <div class="pillar-card">
                <div class="pillar-icon"><i class="fa-solid fa-heart"></i></div>
                <h3 class="pillar-title">Pesan Untuk Sang Kekasih</h3>
                <p class="pillar-text">Tuliskan kata-kata manis atau curahan hati yang ingin kamu sampaikan khusus untuknya.</p>
            </div>
            <div class="pillar-card">
                <div class="pillar-icon"><i class="fa-brands fa-spotify"></i></div>
                <h3 class="pillar-title">Integrasi Musik Spotify</h3>
                <p class="pillar-text">Pilih lagu favorit lengkap dengan artwork resmi dan alunan melodinya.</p>
            </div>
            <div class="pillar-card">
                <div class="pillar-icon"><i class="fa-solid fa-compact-disc"></i></div>
                <h3 class="pillar-title">Makna Lagu & Foto</h3>
                <p class="pillar-text">Lengkapi pesanmu dengan foto kenangan indah dan penjelasan makna lagu yang menyentuh.</p>
            </div>
        </div>
    </div>

    <!-- FEATURED CAROUSEL SECTION -->
    <section class="app-container">
        <div class="section-header">
            <div>
                <div class="section-tag">Pesan Terpilih</div>
                <h2 class="section-title">Ungkapan Hati Terbaru</h2>
            </div>
            <p class="section-desc">Momen-momen bermakna yang dibagikan lewat musik dan bait cerita.</p>
        </div>
        
        <div class="carousel-wrapper">
            <div class="carousel-track-container" id="carouselTrackContainer">
                <div class="carousel-track" id="carouselTrack">
                    <!-- Loaded dynamically -->
                </div>
            </div>
        </div>
    </section>

    <!-- FORM SECTION -->
    <section id="formSection" class="app-container">
        <div class="form-section">
            <div class="section-header" style="margin-top:0;">
                <div>
                    <div class="section-tag">Kirim Pesan</div>
                    <h2 class="section-title">Bagikan Melodimu</h2>
                </div>
                <p class="section-desc">Isi formulir di bawah untuk mendedikasikan pesan dan lagu spesial.</p>
            </div>

            <form id="createMessageForm" onsubmit="handleFormSubmit(event)">
                <div class="form-grid">
                    <!-- RECIPIENT INPUT ONLY (NO "DARI SIAPA?") -->
                    <div class="form-group">
                        <label class="form-label" for="recipientInput"><i class="fa-solid fa-user"></i> Untuk (Nama / Inisial Penerima)</label>
                        <input type="text" id="recipientInput" name="receiver" class="form-input" placeholder="Tuliskan nama atau inisial seseorang (mis. Adinda)" required>
                    </div>

                    <!-- SONG SEARCH INPUT -->
                    <div class="form-group song-search-container">
                        <label class="form-label" for="songSearchInput"><i class="fa-brands fa-spotify" style="color:var(--accent-green);"></i> Cari Lagu Spesial</label>
                        <input type="text" id="songSearchInput" class="form-input" placeholder="Ketik judul lagu atau nama penyanyi..." autocomplete="off">
                        
                        <div class="song-dropdown" id="songDropdown"></div>
                        
                        <!-- SELECTED SONG DISPLAY -->
                        <div id="selectedSongBubble" class="selected-song-bubble" style="display:none;">
                            <div class="song-preview-meta">
                                <img id="selectedBubbleImg" src="" alt="Album Cover">
                                <div>
                                    <h4 id="selectedBubbleTitle">Judul Lagu</h4>
                                    <p id="selectedBubbleArtist">Penyanyi</p>
                                </div>
                            </div>
                            <button type="button" class="btn-remove-song" onclick="clearSelectedSong()"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    </div>

                    <!-- MESSAGE INPUT -->
                    <div class="form-group">
                        <label class="form-label" for="messageInput"><i class="fa-solid fa-pen-nib"></i> Isi Pesan Rahasia</label>
                        <textarea id="messageInput" name="message" class="form-textarea" placeholder="Tuliskan cerita, perasaan, atau pesan hangatmu di sini..." required></textarea>
                    </div>

                    <!-- PHOTO UPLOAD -->
                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-image"></i> Lampirkan Foto / Kenangan (Opsional)</label>
                        <div class="file-dropzone" onclick="document.getElementById('photoInput').click()">
                            <i class="fa-solid fa-cloud-arrow-up" style="font-size:1.8rem; color:var(--accent-green); margin-bottom:0.5rem;"></i>
                            <p style="font-size:0.88rem; color:var(--text-main);">Klik untuk memilih foto dari galeri HP / Komputer</p>
                            <p style="font-size:0.75rem; color:var(--text-muted); margin-top:0.2rem;">Format JPG, PNG, WEBP</p>
                            <input type="file" id="photoInput" accept="image/*" onchange="handleFileSelected(event)">
                            <img id="photoPreviewThumb" class="file-preview-thumb" alt="Preview Photo">
                        </div>
                    </div>

                    <button type="submit" id="btnSubmitForm" class="btn-primary" style="justify-content:center; width:100%; margin-top:0.5rem;">
                        <i class="fa-solid fa-paper-plane"></i>
                        Kirim Pesan Rahasia
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- MESSAGES FEED SECTION -->
    <section id="feedSection" class="app-container">
        <div class="section-header">
            <div>
                <div class="section-tag">Arsip Pesan</div>
                <h2 class="section-title">Semua Pesan & Melodi</h2>
            </div>
            <p class="section-desc">Cari pesan berdasarkan nama penerima atau judul lagu.</p>
        </div>

        <div class="messages-search-bar">
            <input type="text" id="messagesFilterInput" class="messages-search-input" placeholder="Cari nama penerima atau lagu..." oninput="filterMessages()">
        </div>

        <div class="messages-grid" id="messagesGrid">
            <!-- Loaded dynamically -->
        </div>
    </section>

    <!-- MODAL POPUP FOR EXPANDED MESSAGE / PHOTO -->
    <div class="modal-overlay" id="messageModal">
        <div class="modal-card">
            <button class="btn-close-modal" onclick="closeFullScreenMessage()"><i class="fa-solid fa-xmark"></i></button>
            <div id="modalContent"></div>
        </div>
    </div>

    <!-- TOAST NOTIFICATIONS -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- FOOTER -->
    <footer>
        <div class="app-container">
            <p>&copy; <?php echo date('Y'); ?> SongForYou. Dibuat dengan perasaan & melodi.</p>
        </div>
    </footer>

    <!-- SCRIPT LOGIC -->
    <script>
        function getApiUrl(path) {
            return `/backend/${path}`;
        }
        function getAppUrl(path) {
            if (!path) return '';
            if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('data:')) return path;
            return path.startsWith('/') ? path : '/' + path;
        }

        let allMessages = [];
        let currentAudio = null;
        let currentPlayBtn = null;
        let selectedSong = null;
        let base64Photo = '';

        // Image compression helper (Canvas-based)
        function compressImage(file, maxWidth = 1000, quality = 0.75) {
            return new Promise((resolve) => {
                if (!file || !file.type.startsWith('image/')) {
                    resolve('');
                    return;
                }
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = (e) => {
                    const img = new Image();
                    img.src = e.target.result;
                    img.onload = () => {
                        let width = img.width;
                        let height = img.height;
                        if (width > maxWidth) {
                            height = Math.round((height * maxWidth) / width);
                            width = maxWidth;
                        }
                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);
                        const compressedBase64 = canvas.toDataURL('image/jpeg', quality);
                        resolve(compressedBase64);
                    };
                    img.onerror = () => resolve('');
                };
                reader.onerror = () => resolve('');
            });
        }

        // Handle Photo Selection
        async function handleFileSelected(e) {
            const file = e.target.files[0];
            const thumb = document.getElementById('photoPreviewThumb');
            if (file) {
                base64Photo = await compressImage(file, 1000, 0.75);
                thumb.src = base64Photo;
                thumb.style.display = 'block';
            } else {
                base64Photo = '';
                thumb.style.display = 'none';
            }
        }

        // Song Search Logic
        const songInput = document.getElementById('songSearchInput');
        const songDropdown = document.getElementById('songDropdown');
        let searchTimer = null;

        if (songInput) {
            songInput.addEventListener('input', function() {
                clearTimeout(searchTimer);
                const query = this.value.trim();
                if (query.length < 2) {
                    songDropdown.style.display = 'none';
                    return;
                }
                searchTimer = setTimeout(() => searchSongs(query), 300);
            });
            songInput.addEventListener('focus', function() {
                if (this.value.trim().length >= 2) {
                    songDropdown.style.display = 'block';
                }
            });
        }

        async function searchSongs(query) {
            try {
                let results = [];
                // 1. Fetch Spotify Search Endpoint
                try {
                    const res = await fetch(getApiUrl(`spotify_search.php?q=${encodeURIComponent(query)}`));
                    if (res.ok) {
                        const data = await res.json();
                        if (data.tracks && data.tracks.length > 0) {
                            results = data.tracks.map(t => ({
                                spotifyId: t.spotifyId || t.id || '',
                                title: t.title || t.name || '',
                                artist: t.artist || '',
                                coverUrl: t.coverUrl || t.cover || '',
                                spotifyUrl: t.spotifyUrl || '',
                                previewUrl: t.previewUrl || '',
                                meaning: t.meaning || ''
                            }));
                        }
                    }
                } catch(e) {}

                // 2. iTunes Search API Fallback if needed
                if (results.length === 0) {
                    const itunesRes = await fetch(`https://itunes.apple.com/search?term=${encodeURIComponent(query)}&country=ID&media=music&entity=song&limit=10`);
                    if (itunesRes.ok) {
                        const itunesData = await itunesRes.json();
                        results = (itunesData.results || []).map(item => ({
                            spotifyId: item.trackId ? String(item.trackId) : '',
                            title: item.trackName || '',
                            artist: item.artistName || '',
                            coverUrl: item.artworkUrl100 ? item.artworkUrl100.replace('100x100bb', '300x300bb') : '',
                            spotifyUrl: item.trackViewUrl || '',
                            previewUrl: item.previewUrl || '',
                            meaning: `Lagu "${item.trackName}" karya ${item.artistName} menggambarkan nuansa perasaan serta cerita cinta yang mendalam.`
                        }));
                    }
                }

                renderSongDropdown(results);
            } catch (err) {
                console.error("Song search error:", err);
            }
        }

        function renderSongDropdown(tracks) {
            if (!tracks || tracks.length === 0) {
                songDropdown.innerHTML = '<div style="padding:1rem; color:var(--text-muted); font-size:0.85rem; text-align:center;">Lagu tidak ditemukan</div>';
                songDropdown.style.display = 'block';
                return;
            }

            songDropdown.innerHTML = tracks.map((t, idx) => `
                <div class="song-option" onclick="selectSong(${idx})">
                    <img src="${t.coverUrl || 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=100'}" alt="Cover">
                    <div class="song-option-info">
                        <h5>${escapeHtml(t.title)}</h5>
                        <p>${escapeHtml(t.artist)} <span class="spotify-tag-badge"><i class="fa-brands fa-spotify"></i></span></p>
                    </div>
                </div>
            `).join('');

            window._searchResults = tracks;
            songDropdown.style.display = 'block';
        }

        async function selectSong(index) {
            const track = window._searchResults[index];
            if (!track) return;

            selectedSong = track;

            // Dynamically ensure previewUrl is available via iTunes if empty
            if (!selectedSong.previewUrl && selectedSong.title && selectedSong.artist) {
                selectedSong.previewUrl = await getItunesPreview(selectedSong.title, selectedSong.artist);
            }

            document.getElementById('selectedBubbleImg').src = track.coverUrl || 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=100';
            document.getElementById('selectedBubbleTitle').innerText = track.title;
            document.getElementById('selectedBubbleArtist').innerText = track.artist;
            document.getElementById('selectedSongBubble').style.display = 'flex';

            songInput.value = '';
            songDropdown.style.display = 'none';
        }

        function clearSelectedSong() {
            selectedSong = null;
            document.getElementById('selectedSongBubble').style.display = 'none';
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (songDropdown && !songInput.contains(e.target) && !songDropdown.contains(e.target)) {
                songDropdown.style.display = 'none';
            }
        });

        // Dynamic iTunes Audio Preview Helper
        async function getItunesPreview(title, artist) {
            try {
                const cleanTitle = title.replace(/[\(\[\-].*$/, '').trim();
                const cleanArtist = artist.split(',')[0].split('&')[0].trim();
                const q = encodeURIComponent(`${cleanTitle} ${cleanArtist}`);
                const res = await fetch(`https://itunes.apple.com/search?term=${q}&country=ID&media=music&entity=song&limit=5`);
                if (res.ok) {
                    const data = await res.json();
                    if (data.results && data.results.length > 0) {
                        for (let tr of data.results) {
                            if (tr.previewUrl) return tr.previewUrl;
                        }
                    }
                }
            } catch(e) {}
            return '';
        }

        // Submit Form Handler (NO "DARI SIAPA?" FIELD)
        async function handleFormSubmit(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmitForm');
            const originalBtnText = btn.innerHTML;

            const receiver = document.getElementById('recipientInput').value.trim();
            const message = document.getElementById('messageInput').value.trim();

            if (!receiver) {
                showToast('Harap isi nama penerima pesan.', 'error');
                return;
            }
            if (!selectedSong) {
                showToast('Silakan cari dan pilih lagu terlebih dahulu.', 'error');
                return;
            }
            if (!message) {
                showToast('Harap isi pesan rahasiamu.', 'error');
                return;
            }

            try {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim Pesan...';

                const payload = {
                    receiver: receiver,
                    senderName: 'Anonim', // Default anonymous sender
                    songKey: selectedSong.spotifyId || selectedSong.trackId || '2IVsRhKrx8hlQBOWy4qebo',
                    message: message,
                    images: base64Photo,
                    songDetails: {
                        title: selectedSong.title,
                        artist: selectedSong.artist,
                        coverUrl: selectedSong.coverUrl,
                        spotifyUrl: selectedSong.spotifyUrl,
                        previewUrl: selectedSong.previewUrl,
                        meaning: selectedSong.meaning
                    }
                };

                const res = await fetch(getApiUrl('submit_message.php'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (data.success) {
                    showToast('Pesan rahasiamu berhasil dikirim!');
                    document.getElementById('createMessageForm').reset();
                    clearSelectedSong();
                    base64Photo = '';
                    document.getElementById('photoPreviewThumb').style.display = 'none';
                    fetchMessages();
                    document.getElementById('feedSection').scrollIntoView({ behavior: 'smooth' });
                } else {
                    showToast('Gagal mengirim: ' + (data.message || 'Terjadi kesalahan server'), 'error');
                }
            } catch (err) {
                console.error("Form submit error:", err);
                showToast('Terjadi gangguan jaringan. Silakan coba lagi.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalBtnText;
            }
        }

        // Global Single-Audio Player Controller
        async function toggleAudioPlayback(previewUrl, title, artist, btnEl) {
            let urlToPlay = previewUrl;

            if (!urlToPlay && title && artist) {
                urlToPlay = await getItunesPreview(title, artist);
            }

            if (!urlToPlay) {
                showToast('Audio preview tidak tersedia untuk lagu ini', 'error');
                return;
            }

            if (currentAudio && currentAudio.src === urlToPlay) {
                if (currentAudio.paused) {
                    currentAudio.play();
                    if (btnEl) btnEl.innerHTML = '<i class="fa-solid fa-pause"></i>';
                } else {
                    currentAudio.pause();
                    if (btnEl) btnEl.innerHTML = '<i class="fa-solid fa-play"></i>';
                }
                return;
            }

            if (currentAudio) {
                currentAudio.pause();
                if (currentPlayBtn) currentPlayBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
            }

            currentAudio = new Audio(urlToPlay);
            currentPlayBtn = btnEl;

            currentAudio.play().then(() => {
                if (btnEl) btnEl.innerHTML = '<i class="fa-solid fa-pause"></i>';
            }).catch(err => {
                showToast('Gagal memutar audio preview', 'error');
            });

            currentAudio.onended = function() {
                if (btnEl) btnEl.innerHTML = '<i class="fa-solid fa-play"></i>';
            };
        }

        // Fetch & Render Messages
        async function fetchMessages() {
            try {
                const res = await fetch(getApiUrl('get_messages.php'));
                if (res.ok) {
                    const data = await res.json();
                    allMessages = Array.isArray(data) ? data : (data.messages || []);
                    renderCarousel(allMessages);
                    renderMessages(allMessages);
                }
            } catch (err) {
                console.error("Error fetching messages:", err);
            }
        }

        function renderCarousel(messages) {
            const track = document.getElementById('carouselTrack');
            if (!track) return;

            const featured = messages.slice(0, 6);
            if (featured.length === 0) {
                track.innerHTML = '<div style="color:var(--text-muted); font-size:0.85rem;">Belum ada pesan terbaru.</div>';
                return;
            }

            track.innerHTML = featured.map(m => `
                <div class="carousel-card" onclick="openFullScreenMessage(${m.id})">
                    <div>
                        <div class="card-recipient-tag">Untuk:</div>
                        <h4 class="card-recipient-name">${escapeHtml(m.receiver || 'Seseorang')}</h4>
                        <p class="card-message-snippet">"${escapeHtml(m.message || '')}"</p>
                    </div>
                    <div style="font-size:0.78rem; color:var(--accent-green); font-family:var(--font-heading); font-weight:700; margin-top:1rem;">
                        <i class="fa-brands fa-spotify"></i> ${escapeHtml(m.songTitle || 'Lagu Pilihan')}
                    </div>
                </div>
            `).join('');
        }

        function renderMessages(messages) {
            const grid = document.getElementById('messagesGrid');
            if (!grid) return;

            if (messages.length === 0) {
                grid.innerHTML = '<div style="grid-column: 1/-1; text-align:center; padding:3rem; color:var(--text-muted);">Belum ada pesan yang tersimpan. Jadilah yang pertama mengirim pesan!</div>';
                return;
            }

            grid.innerHTML = messages.map(m => {
                const hasImage = m.images || m.photo_url || m.image_url;
                const imgSrc = getAppUrl(m.images || m.photo_url || m.image_url);
                const songTitle = m.songTitle || m.song_title || 'Lagu Pilihan';
                const songArtist = m.songArtist || m.song_artist || '';
                const songCover = m.songCover || m.song_cover || 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=100';
                const previewUrl = m.previewUrl || m.song_preview_url || '';
                const spotifyUrl = m.songSpotifyUrl || m.song_spotify_url || `https://open.spotify.com/search/${encodeURIComponent(songTitle + ' ' + songArtist)}`;
                const meaning = m.songMeaning || '';

                return `
                    <div class="message-card">
                        <div>
                            <div class="card-header-row">
                                <h3 class="card-to">Untuk ${escapeHtml(m.receiver || 'Seseorang')}</h3>
                                <span class="card-date">${formatDate(m.timestamp || m.created_at)}</span>
                            </div>

                            ${hasImage ? `
                                <div class="card-image-wrap" onclick="openFullScreenMessage(${m.id})">
                                    <img src="${imgSrc}" alt="Photo">
                                </div>
                            ` : ''}

                            <p class="card-body-text">${escapeHtml(m.message || '')}</p>

                            ${meaning ? `
                                <div class="song-meaning-box">
                                    <div class="meaning-header"><i class="fa-solid fa-quote-left"></i> Makna Lagu:</div>
                                    <p class="meaning-text">${escapeHtml(meaning)}</p>
                                </div>
                            ` : ''}
                        </div>

                        <!-- AUDIO PLAYER BAR -->
                        <div class="player-bar">
                            <img src="${songCover}" class="player-cover" alt="Cover">
                            <div class="player-meta">
                                <div class="player-title">${escapeHtml(songTitle)}</div>
                                <div class="player-artist">${escapeHtml(songArtist)}</div>
                            </div>
                            <div class="player-controls">
                                <button class="btn-play-icon" onclick="toggleAudioPlayback('${previewUrl}', '${escapeHtml(songTitle)}', '${escapeHtml(songArtist)}', this)">
                                    <i class="fa-solid fa-play"></i>
                                </button>
                                <a href="${spotifyUrl}" target="_blank" class="spotify-link-btn" title="Buka di Spotify"><i class="fa-brands fa-spotify"></i></a>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function filterMessages() {
            const query = document.getElementById('messagesFilterInput').value.toLowerCase();
            const filtered = allMessages.filter(m => {
                const rec = (m.receiver || '').toLowerCase();
                const msg = (m.message || '').toLowerCase();
                const sng = (m.songTitle || m.song_title || '').toLowerCase();
                const art = (m.songArtist || m.song_artist || '').toLowerCase();
                return rec.includes(query) || msg.includes(query) || sng.includes(query) || art.includes(query);
            });
            renderMessages(filtered);
        }

        function openFullScreenMessage(id) {
            const m = allMessages.find(item => item.id == id);
            if (!m) return;

            const modalContent = document.getElementById('modalContent');
            const hasImage = m.images || m.photo_url || m.image_url;
            const imgSrc = getAppUrl(m.images || m.photo_url || m.image_url);
            const songTitle = m.songTitle || m.song_title || 'Lagu Pilihan';
            const songArtist = m.songArtist || m.song_artist || '';
            const songCover = m.songCover || m.song_cover || 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=100';
            const previewUrl = m.previewUrl || m.song_preview_url || '';
            const spotifyUrl = m.songSpotifyUrl || m.song_spotify_url || `https://open.spotify.com/search/${encodeURIComponent(songTitle + ' ' + songArtist)}`;
            const meaning = m.songMeaning || '';

            modalContent.innerHTML = `
                <div style="margin-bottom:1.2rem;">
                    <span style="font-family:var(--font-heading); font-size:0.75rem; text-transform:uppercase; letter-spacing:1.5px; color:var(--accent-green);">Untuk:</span>
                    <h2 style="font-family:var(--font-serif); font-size:1.9rem; margin:0.2rem 0;">${escapeHtml(m.receiver || 'Seseorang')}</h2>
                    <p style="font-size:0.8rem; color:var(--text-muted);">${formatDate(m.timestamp || m.created_at)}</p>
                </div>

                ${hasImage ? `<img src="${imgSrc}" style="width:100%; max-height:360px; object-fit:contain; background:#000; border-radius:12px; margin-bottom:1.2rem;">` : ''}

                <div style="font-size:1rem; line-height:1.7; color:#e0e0ec; margin-bottom:1.5rem; white-space:pre-wrap;">${escapeHtml(m.message || '')}</div>

                ${meaning ? `
                    <div class="song-meaning-box" style="margin-bottom:1.5rem;">
                        <div class="meaning-header"><i class="fa-solid fa-quote-left"></i> Makna Lagu:</div>
                        <p class="meaning-text">${escapeHtml(meaning)}</p>
                    </div>
                ` : ''}

                <div class="player-bar">
                    <img src="${songCover}" class="player-cover" alt="Cover">
                    <div class="player-meta">
                        <div class="player-title">${escapeHtml(songTitle)}</div>
                        <div class="player-artist">${escapeHtml(songArtist)}</div>
                    </div>
                    <div class="player-controls">
                        <button class="btn-play-icon" onclick="toggleAudioPlayback('${previewUrl}', '${escapeHtml(songTitle)}', '${escapeHtml(songArtist)}', this)">
                            <i class="fa-solid fa-play"></i>
                        </button>
                        <a href="${spotifyUrl}" target="_blank" class="spotify-link-btn" title="Buka di Spotify"><i class="fa-brands fa-spotify"></i></a>
                    </div>
                </div>
            `;

            document.getElementById('messageModal').style.display = 'flex';
        }

        function closeFullScreenMessage() {
            document.getElementById('messageModal').style.display = 'none';
        }

        // Helpers
        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>"']/g, function(m) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
            });
        }

        function formatDate(dateInput) {
            if (!dateInput) return '';
            try {
                const d = new Date(dateInput);
                if (isNaN(d.getTime())) return String(dateInput);
                return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            } catch(e) {
                return String(dateInput);
            }
        }

        function showToast(msg, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = 'toast';
            if (type === 'error') {
                toast.style.borderColor = '#ff5555';
                toast.style.borderLeftColor = '#ff5555';
            }
            toast.innerText = msg;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        document.addEventListener('DOMContentLoaded', () => {
            fetchMessages();
        });
    </script>
</body>
</html>
