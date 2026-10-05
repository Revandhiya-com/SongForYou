<?php
/*
 * /Website-SFY/index.php
 * SongForYou — Archive Your Feelings Through Music
 * Handcrafted Aesthetic: Human, fluid, organic dark theme with soft typography & zero AI-slop vibe.
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
    <meta name="description" content="Kirimkan pesan rahasia, ungkapan hati, dan lagu kenangan untuk seseorang yang berarti dalam hidupmu.">

    <!-- Fonts: clean interface type + handwritten notes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;500;600;700&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-main: #070707;
            --bg-surface: #101010;
            --bg-surface-hover: #191919;
            --bg-input: #0d0d0d;
            --border-subtle: rgba(255, 255, 255, 0.07);
            --border-glow: rgba(255, 255, 255, 0.16);
            --text-heading: #f5f5f5;
            --text-body: #a3a3a3;
            --text-subtle: #737373;
            --accent-soft: #e5e5e5;
            --accent-sage: #e5e5e5;
            --accent-dim: rgba(255, 255, 255, 0.10);
            --font-serif: 'Cormorant Garamond', Georgia, serif;
            --font-message: 'Caveat', cursive;
            --font-sans: 'Plus Jakarta Sans', -apple-system, sans-serif;
            --radius-xl: 20px;
            --radius-lg: 14px;
            --radius-md: 10px;
            --transition: all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
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
            background:
                radial-gradient(circle at 12% 8%, rgba(255,255,255,0.08), transparent 22rem),
                radial-gradient(circle at 90% 36%, rgba(255,255,255,0.05), transparent 28rem),
                var(--bg-main);
            color: var(--text-heading);
            font-family: var(--font-sans);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
            line-height: 1.6;
        }

        /* SCROLLBAR */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        ::-webkit-scrollbar-track {
            background: var(--bg-main);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 4px;
        }

        /* CONTAINER */
        .app-container {
            max-width: 1120px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        body::before,
        body::after {
            content: '';
            position: fixed;
            z-index: -1;
            border-radius: 50%;
            filter: blur(2px);
            pointer-events: none;
            animation: ambient-float 14s ease-in-out infinite alternate;
        }
        body::before {
            width: 18rem;
            height: 18rem;
            top: 42%;
            left: -10rem;
            background: rgba(255,255,255,0.04);
        }
        body::after {
            width: 20rem;
            height: 20rem;
            top: 10%;
            right: -12rem;
            background: rgba(255,255,255,0.03);
            animation-delay: -7s;
        }
        @keyframes ambient-float {
            to { transform: translate3d(2rem, -1.5rem, 0) scale(1.12); }
        }

        /* NAVBAR */
        header.navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2rem 0 1.5rem;
            border-bottom: 1px solid var(--border-subtle);
        }
        .brand-logo {
            font-family: var(--font-sans);
            font-weight: 700;
            font-size: 1.15rem;
            letter-spacing: -0.3px;
            color: var(--text-heading);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .brand-logo-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--accent-dim);
            color: var(--accent-sage);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }
        .btn-nav-action {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.6rem 1.4rem;
            border-radius: 100px;
            border: 1px solid var(--border-subtle);
            background: rgba(255, 255, 255, 0.02);
            color: var(--text-heading);
            text-decoration: none;
            transition: var(--transition);
        }
        .btn-nav-action:hover {
            border-color: var(--border-glow);
            background: rgba(255, 255, 255, 0.06);
        }

        /* HERO SECTION */
        .hero-section {
            padding: 5rem 0 3.5rem;
            text-align: center;
            position: relative;
            isolation: isolate;
        }
        .hero-section::before {
            content: '';
            position: absolute;
            z-index: -1;
            width: min(62rem, 100%);
            height: 23rem;
            left: 50%;
            top: 1.7rem;
            transform: translateX(-50%);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 48% 52% 42% 58% / 50% 45% 55% 50%;
            background: linear-gradient(120deg, rgba(255,255,255,0.055), rgba(255,255,255,0.005));
            box-shadow: inset 0 0 70px rgba(255,255,255,0.025);
        }
        .hero-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 1rem;
            border-radius: 100px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-subtle);
            color: var(--text-body);
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 1.75rem;
        }
        .hero-pill i {
            color: var(--accent-sage);
        }
        .hero-title {
            font-family: var(--font-serif);
            font-size: clamp(2.6rem, 6vw, 4.8rem);
            line-height: 1.08;
            font-weight: 400;
            color: var(--text-heading);
            margin-bottom: 1.25rem;
            letter-spacing: -0.5px;
        }
        .hero-title em {
            font-style: italic;
            color: #ffffff;
            border-bottom: 1px solid rgba(255,255,255,0.55);
        }
        .hero-subtitle {
            max-width: 560px;
            margin: 0 auto 2.5rem;
            font-size: 1.02rem;
            color: var(--text-body);
            font-weight: 400;
        }
        .hero-actions {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .btn-main {
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.95rem 2.2rem;
            border-radius: 100px;
            background: #ffffff;
            color: #0b0b0e;
            border: 1px solid #ffffff;
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            box-shadow: 0 10px 25px rgba(255,255,255,0.08);
        }
        .btn-main:hover {
            transform: translateY(-2px);
            background: #e5e5e5;
            box-shadow: 0 14px 35px rgba(255,255,255,0.15);
        }
        .btn-subtle {
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.95rem 2.2rem;
            border-radius: 100px;
            background: transparent;
            color: var(--text-heading);
            border: 1px solid var(--border-subtle);
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition);
        }
        .btn-subtle:hover {
            border-color: var(--border-glow);
            background: rgba(255, 255, 255, 0.04);
        }

        /* SECTION HEADER */
        .section-header {
            margin: 4.5rem 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .section-tagline {
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #d4d4d4;
            margin-bottom: 0.4rem;
        }
        .section-title {
            font-family: var(--font-serif);
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 400;
            line-height: 1.15;
            color: var(--text-heading);
        }
        .section-desc {
            color: var(--text-body);
            font-size: 0.92rem;
            max-width: 480px;
        }

        /* CAROUSEL TRACK */
        .carousel-container {
            overflow-x: auto;
            scroll-behavior: smooth;
            padding: 0.5rem 0 1rem;
            scrollbar-width: none;
        }
        .carousel-container::-webkit-scrollbar {
            display: none;
        }
        .carousel-flex {
            display: flex;
            gap: 1.5rem;
            padding: 0.25rem 0.15rem 1.25rem;
        }
        .carousel-item {
            flex: 0 0 min(74vw, 680px);
            min-height: 390px;
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            display: flex;
            flex-direction: column;
            transition: var(--transition);
            cursor: pointer;
            overflow: hidden;
            position: relative;
            isolation: isolate;
        }
        .carousel-item::after {
            content: '';
            position: absolute;
            inset: 0;
            z-index: -1;
            background: linear-gradient(120deg, transparent 30%, rgba(255,255,255,0.12), transparent 68%);
            transform: translateX(-130%);
            transition: transform 0.7s cubic-bezier(.2,.8,.2,1);
        }
        .carousel-item:hover {
            border-color: rgba(255,255,255,0.42);
            background: var(--bg-surface-hover);
            transform: translateY(-8px) rotate(-0.35deg);
            box-shadow: 0 24px 50px rgba(0,0,0,0.42);
        }
        .carousel-item:hover::after { transform: translateX(130%); }
        .item-media { width: 100%; aspect-ratio: 16 / 8; overflow: hidden; background: #1b1b1b; border-bottom: 1px solid var(--border-subtle); }
        .item-media img { width: 100%; height: 100%; object-fit: cover; filter: grayscale(1) contrast(1.05); transition: transform 0.7s cubic-bezier(.2,.8,.2,1), filter 0.5s ease; }
        .carousel-item:hover .item-media img { transform: scale(1.05); filter: grayscale(1) contrast(1.15); }
        .item-body { padding: 1.35rem 1.5rem 1.45rem; display: flex; flex: 1; flex-direction: column; justify-content: space-between; }
        .item-to {
            font-family: var(--font-serif);
            font-size: 1.4rem;
            color: var(--text-heading);
            margin-bottom: 0.6rem;
        }
        .item-text {
            font-family: var(--font-message);
            font-size: 1.16rem;
            color: #e5e5e5;
            line-height: 1.45;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-style: italic;
        }
        .item-song-pill {
            font-size: 0.78rem;
            color: var(--accent-sage);
            margin-top: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-weight: 500;
        }

        /* FORM SECTION */
        .form-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-xl);
            padding: clamp(1.75rem, 4vw, 3.2rem);
            margin: 4rem 0;
            box-shadow: 0 20px 50px rgba(0,0,0,0.4);
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        .form-field {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            position: relative;
        }
        .form-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-body);
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .form-input, .form-textarea {
            width: 100%;
            background: var(--bg-input);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            padding: 0.9rem 1.1rem;
            color: var(--text-heading);
            font-family: var(--font-sans);
            font-size: 0.92rem;
            outline: none;
            transition: var(--transition);
        }
        .form-input:focus, .form-textarea:focus {
            border-color: var(--accent-sage);
            background: #181818;
            box-shadow: 0 0 0 3px var(--accent-dim);
        }
        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }

        /* SEARCH DROPDOWN */
        .search-dropdown {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            background: #181818;
            border: 1px solid var(--border-glow);
            border-radius: var(--radius-md);
            max-height: 280px;
            overflow-y: auto;
            z-index: 100;
            box-shadow: 0 15px 35px rgba(0,0,0,0.7);
            display: none;
        }
        .search-option {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.8rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid rgba(255,255,255,0.03);
            transition: background 0.2s ease;
        }
        .search-option:last-child {
            border-bottom: none;
        }
        .search-option:hover {
            background: rgba(255,255,255,0.06);
        }
        .search-option img {
            width: 40px;
            height: 40px;
            border-radius: 6px;
            object-fit: cover;
        }
        .search-option-info {
            flex: 1;
            min-width: 0;
        }
        .search-option-info h5 {
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--text-heading);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .search-option-info p {
            font-size: 0.78rem;
            color: var(--text-body);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* SELECTED SONG DISPLAY */
        .selected-song-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.055);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: var(--radius-md);
            padding: 0.85rem 1rem;
            margin-top: 0.5rem;
        }
        .selected-song-meta {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            min-width: 0;
        }
        .selected-song-meta img {
            width: 44px;
            height: 44px;
            border-radius: 6px;
            object-fit: cover;
        }
        .selected-song-meta h4 {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-heading);
        }
        .selected-song-meta p {
            font-size: 0.78rem;
            color: var(--text-body);
        }
        .btn-clear-song {
            background: none;
            border: none;
            color: var(--text-subtle);
            cursor: pointer;
            font-size: 1.1rem;
            padding: 0.3rem;
            transition: color 0.2s ease;
        }
        .btn-clear-song:hover {
            color: #f5f5f5;
        }

        /* DROPZONE */
        .upload-dropzone {
            border: 2px dashed var(--border-subtle);
            border-radius: var(--radius-md);
            min-height: 180px;
            padding: 1.5rem;
            text-align: center;
            background: linear-gradient(135deg, rgba(255,255,255,0.07), rgba(255,255,255,0.015)), var(--bg-input);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
        }
        .upload-dropzone:hover {
            border-color: var(--accent-sage);
            background: rgba(255,255,255,0.06);
        }
        .upload-dropzone input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        .photo-preview {
            width: 126px;
            height: 126px;
            border-radius: 14px;
            margin-top: 0.9rem;
            object-fit: cover;
            display: none;
            border: 2px solid rgba(255,255,255,0.18);
            box-shadow: 0 12px 25px rgba(0,0,0,0.28);
        }

        /* FEED GRID */
        .filter-input {
            width: 100%;
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: 100px;
            padding: 0.85rem 1.5rem;
            color: var(--text-heading);
            font-size: 0.9rem;
            outline: none;
            margin-bottom: 2rem;
            transition: var(--transition);
        }
        .filter-input:focus {
            border-color: var(--border-glow);
            background: var(--bg-surface-hover);
        }

        .feed-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
            gap: 1.5rem;
        }
        @media (max-width: 480px) {
            .feed-grid {
                grid-template-columns: 1fr;
            }
        }

        /* MESSAGE CARD */
        .feed-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: var(--transition);
        }
        .feed-card:hover {
            border-color: var(--border-glow);
            background: var(--bg-surface-hover);
            transform: translateY(-3px);
        }
        .card-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .card-recipient {
            font-family: var(--font-serif);
            font-size: 1.35rem;
            color: var(--text-heading);
        }
        .card-date {
            font-size: 0.72rem;
            color: var(--text-subtle);
        }
        .card-img-wrap {
            width: 100%;
            height: 190px;
            border-radius: var(--radius-md);
            overflow: hidden;
            margin-bottom: 1.1rem;
            cursor: pointer;
        }
        .card-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }
        .card-img-wrap:hover img {
            transform: scale(1.04);
        }
        .card-message {
            font-family: var(--font-message);
            font-size: 1.15rem;
            font-style: italic;
            color: #e5e7eb;
            line-height: 1.6;
            margin-bottom: 1.1rem;
            word-wrap: break-word;
        }
        .card-message::before { content: '\201C'; color: #d4d4d4; font-size: 1.35em; margin-right: 0.08em; }
        .card-message::after { content: '\201D'; color: #d4d4d4; font-size: 1.35em; margin-left: 0.08em; }
        .modal-message {
            font-family: var(--font-message);
            font-size: clamp(1.3rem, 2.4vw, 1.72rem);
            font-weight: 500;
            font-style: italic;
            line-height: 1.7;
            letter-spacing: -0.025em;
            color: #e5e7eb;
            margin-bottom: 1.5rem;
            white-space: pre-wrap;
        }

        /* SONG MEANING BOX */
        .meaning-box {
            background: rgba(255, 255, 255, 0.025);
            border-left: 3px solid #d4d4d4;
            border-radius: 4px;
            padding: 0.75rem 0.95rem;
            margin-bottom: 1.1rem;
        }
        .meaning-lbl {
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--accent-sage);
            margin-bottom: 0.2rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .meaning-txt {
            font-size: 0.82rem;
            color: var(--text-body);
            line-height: 1.5;
            font-style: italic;
        }

        /* AUDIO PLAYER BAR */
        .audio-bar {
            background: rgba(255,255,255,0.025);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: var(--radius-md);
            padding: 0.75rem 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }
        .audio-cover {
            width: 42px;
            height: 42px;
            border-radius: 6px;
            object-fit: cover;
            flex-shrink: 0;
        }
        .audio-info {
            flex: 1;
            min-width: 0;
        }
        .audio-title {
            font-size: 0.86rem;
            font-weight: 600;
            color: var(--text-heading);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .audio-artist {
            font-size: 0.75rem;
            color: var(--text-body);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .audio-actions {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-shrink: 0;
        }
        .btn-play-audio {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #f5f5f5;
            color: #0b0b0e;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.2s ease, background 0.2s ease;
        }
        .btn-play-audio:hover {
            transform: scale(1.06);
            background: #ffffff;
        }
        .spotify-btn {
            color: var(--text-subtle);
            font-size: 1.1rem;
            transition: color 0.2s ease;
            text-decoration: none;
        }
        .spotify-btn:hover {
            color: var(--accent-sage);
        }

        /* MODAL */
        .modal-bg {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.85);
            backdrop-filter: blur(10px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .modal-box {
            background: var(--bg-surface);
            border: 1px solid var(--border-glow);
            border-radius: var(--radius-xl);
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            padding: 2rem;
            position: relative;
        }
        .btn-close {
            position: absolute;
            top: 1.2rem;
            right: 1.2rem;
            background: rgba(255,255,255,0.08);
            border: none;
            color: var(--text-heading);
            width: 34px;
            height: 34px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* TOAST */
        .toast-wrap {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 2000;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .toast-item {
            background: #181818;
            border: 1px solid var(--border-glow);
            border-left: 4px solid var(--accent-sage);
            color: var(--text-heading);
            padding: 0.9rem 1.3rem;
            border-radius: var(--radius-md);
            font-size: 0.88rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        /* FOOTER */
        footer {
            border-top: 1px solid var(--border-subtle);
            padding: 3rem 0;
            margin-top: 5rem;
            text-align: center;
            color: var(--text-subtle);
            font-size: 0.85rem;
        }

        /* MONOCHROME INTERACTION SYSTEM */
        html { scroll-behavior: smooth; }
        body {
            --cursor-x: 50%;
            --cursor-y: 0%;
            background-color: #090909;
            background-image:
                linear-gradient(rgba(255,255,255,0.028) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.028) 1px, transparent 1px),
                radial-gradient(ellipse 70% 45% at var(--cursor-x) var(--cursor-y), rgba(255,255,255,0.075), transparent 58%);
            background-size: 42px 42px, 42px 42px, auto;
            background-attachment: fixed;
        }
        body::before, body::after { display: none; }
        .app-container { max-width: 1180px; }
        header.navbar {
            margin-top: 1rem;
            padding: 1rem 0;
            border-bottom-color: rgba(255,255,255,0.11);
        }
        .brand-logo { letter-spacing: -0.65px; }
        .brand-logo-icon { background: #f5f5f5; color: #0b0b0b; border-radius: 9px; }
        .btn-nav-action, .btn-subtle {
            border-color: rgba(255,255,255,0.18);
            background: rgba(255,255,255,0.03);
        }
        .btn-nav-action:hover, .btn-subtle:hover { background: #f5f5f5; color: #111; border-color: #f5f5f5; transform: translateY(-2px); }
        .hero-section { padding: clamp(4.5rem, 10vw, 8.5rem) 0 4.5rem; text-align: left; }
        .hero-section::before { display: none; }
        .hero-title {
            max-width: 850px;
            font-family: var(--font-sans);
            font-size: clamp(3.2rem, 8.8vw, 7.8rem);
            font-weight: 600;
            line-height: 0.91;
            letter-spacing: -0.085em;
            margin-bottom: 2.2rem;
        }
        .hero-title em { font-family: var(--font-serif); font-weight: 400; letter-spacing: -0.045em; border: 0; }
        .hero-actions { justify-content: flex-start; }
        .btn-main { border-radius: 10px; box-shadow: none; padding: 0.95rem 1.25rem; }
        .btn-main:hover { transform: translateY(-3px); box-shadow: 0 10px 0 rgba(255,255,255,0.14); }
        .section-header { margin: 2.25rem 0 1.25rem; align-items: baseline; }
        .section-tagline { color: #a3a3a3; font-size: 0.68rem; letter-spacing: 0.14em; }
        .section-title { font-family: var(--font-sans); font-size: clamp(1.65rem, 3vw, 2.45rem); font-weight: 500; letter-spacing: -0.065em; }
        .carousel-container { overflow: visible; }
        .carousel-flex { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; padding: 0; }
        .carousel-item {
            min-height: 300px;
            width: 100%;
            background: rgba(16,16,16,0.9);
            border-radius: 16px;
            border-color: rgba(255,255,255,0.13);
            transform: none;
        }
        .carousel-item:hover { transform: translateY(-6px); border-color: rgba(255,255,255,0.48); box-shadow: 0 18px 35px rgba(0,0,0,0.36); }
        .carousel-item.no-photo { min-height: 250px; }
        .carousel-item.no-photo .item-body { padding-top: 1.75rem; }
        .item-media { aspect-ratio: 16 / 9; border-bottom-color: rgba(255,255,255,0.1); }
        .item-media img { filter: grayscale(1) contrast(1.1); }
        .item-body { padding: 1.2rem 1.3rem 1.3rem; }
        .item-to { font-family: var(--font-sans); font-size: 1rem; font-weight: 600; letter-spacing: -0.025em; margin-bottom: 0.7rem; }
        .item-text, .card-message, .modal-message {
            font-family: var(--font-message);
            font-weight: 500;
            font-style: normal;
            letter-spacing: 0.01em;
        }
        .item-text { font-size: clamp(1.55rem, 2.3vw, 2.15rem); line-height: 1.05; color: #f5f5f5; -webkit-line-clamp: 3; }
        .item-song-pill { color: #a3a3a3; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 0.85rem; margin-top: 1rem; }
        .form-card { border-radius: 18px; border-color: rgba(255,255,255,0.14); background: rgba(16,16,16,0.92); box-shadow: none; margin: 6rem 0 2rem; }
        .form-input, .form-textarea { border-radius: 8px; background: #0b0b0b; }
        .form-input:focus, .form-textarea:focus { border-color: #e5e5e5; background: #101010; box-shadow: 0 0 0 3px rgba(255,255,255,0.08); }
        .upload-dropzone { border-radius: 10px; background: #0b0b0b; }
        .upload-dropzone:hover { background: #151515; transform: translateY(-2px); }
        .modal-bg { background: rgba(0,0,0,0.9); }
        .modal-box { max-width: 720px; border-radius: 18px; background: #111; }
        .modal-message { font-size: clamp(1.9rem, 4vw, 3rem); line-height: 1.16; color: #f5f5f5; }

        @media (max-width: 600px) {
            .app-container { padding: 0 1.1rem; }
            header.navbar { padding: 1.15rem 0 1rem; }
            .brand-logo { font-size: 1rem; }
            .btn-nav-action { padding: 0.52rem 0.8rem; font-size: 0.72rem; }
            .hero-section { padding: 2.6rem 0 2.5rem; }
            .hero-section::before { height: 19rem; top: 0.5rem; }
            .hero-title { font-size: 2.35rem; }
            .hero-actions .btn-main { width: auto; min-width: 0; justify-content: center; padding: 0.82rem 1.05rem; }
            .btn-subtle { width: auto; justify-content: center; }
            .form-card { padding: 1.4rem 1.1rem; border-radius: 16px; }
            .feed-card { padding: 1.2rem; }
            .section-header { margin: 2.2rem 0 1rem; }
            .section-desc { font-size: 0.86rem; }
            .carousel-container { overflow: visible; }
            .carousel-flex { display: grid; grid-template-columns: 1fr; gap: 0.8rem; padding: 0; }
            .carousel-item { min-height: 0; width: 100%; }
            .carousel-item.no-photo { min-height: 210px; }
            .item-body { padding: 1rem 1.1rem 1.05rem; }
            .item-text { font-size: 1.55rem; -webkit-line-clamp: 2; }
            .item-media { aspect-ratio: 16 / 8; }
            .item-song-pill { padding-top: 0.65rem; margin-top: 0.75rem; }
            .upload-dropzone { min-height: 165px; padding: 1.2rem; }
            .card-img-wrap { height: 220px; }
            .card-message { font-size: 1.06rem; }
            .modal-message { font-size: 1.22rem; }
            .audio-bar { gap: 0.65rem; padding: 0.7rem; }
            .audio-title, .audio-artist { max-width: 135px; }
            .toast-wrap { left: 1rem; right: 1rem; bottom: 1rem; }
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <header class="navbar app-container">
        <a href="#" class="brand-logo">
            <div class="brand-logo-icon"><i class="fa-solid fa-music"></i></div>
            <span>SongForYou</span>
        </a>
        <a href="#formSection" class="btn-nav-action">Kirim Pesan</a>
    </header>

    <!-- HERO SECTION -->
    <section class="hero-section app-container">
        <h1 class="hero-title">
            Kirim rasa lewat<br><em>sebuah lagu.</em>
        </h1>
        <div class="hero-actions">
            <a href="#formSection" class="btn-main">
                <i class="fa-solid fa-paper-plane"></i>
                Tulis pesan
            </a>
        </div>
    </section>

    <!-- FEATURED CAROUSEL SECTION -->
    <section class="app-container">
        <div class="section-header">
            <div>
                <div class="section-tagline">Ungkapan terpilih</div>
                <h2 class="section-title">Untuk didengar.</h2>
            </div>
        </div>
        
        <div class="carousel-container">
            <div class="carousel-flex" id="carouselTrack">
                <!-- Loaded dynamically -->
            </div>
        </div>
    </section>

    <!-- FORM SECTION -->
    <section id="formSection" class="app-container">
        <div class="form-card">
            <div class="section-header" style="margin-top:0;">
                <div>
                    <div class="section-tagline">Dedikasikan Lagu</div>
                    <h2 class="section-title">Kirim Pesan Rahasia</h2>
                </div>
            </div>

            <form id="createMessageForm" onsubmit="handleFormSubmit(event)">
                <div class="form-grid">
                    <!-- RECIPIENT FIELD ONLY (NO SENDER FIELD) -->
                    <div class="form-field">
                        <label class="form-label" for="recipientInput"><i class="fa-regular fa-user"></i> Untuk (Nama / Inisial Penerima)</label>
                        <input type="text" id="recipientInput" class="form-input" placeholder="Tulis nama penerima (mis. Adinda)" required>
                    </div>

                    <!-- SONG SEARCH FIELD -->
                    <div class="form-field">
                        <label class="form-label" for="songSearchInput"><i class="fa-brands fa-spotify"></i> Cari Lagu</label>
                        <input type="text" id="songSearchInput" class="form-input" placeholder="Ketik judul lagu atau nama penyanyi..." autocomplete="off">
                        
                        <div class="search-dropdown" id="songDropdown"></div>
                        
                        <!-- SELECTED SONG DISPLAY -->
                        <div id="selectedSongBubble" class="selected-song-card" style="display:none;">
                            <div class="selected-song-meta">
                                <img id="selectedBubbleImg" src="" alt="Album Cover">
                                <div>
                                    <h4 id="selectedBubbleTitle">Judul Lagu</h4>
                                    <p id="selectedBubbleArtist">Penyanyi</p>
                                </div>
                            </div>
                            <button type="button" class="btn-clear-song" onclick="clearSelectedSong()"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    </div>

                    <!-- MESSAGE TEXTAREA -->
                    <div class="form-field">
                        <label class="form-label" for="messageInput"><i class="fa-regular fa-comment"></i> Isi Pesan Rahasia</label>
                        <textarea id="messageInput" class="form-textarea" placeholder="Tuliskan cerita, perasaan, atau harapan hangatmu..." required></textarea>
                    </div>

                    <!-- PHOTO UPLOAD -->
                    <div class="form-field">
                        <label class="form-label"><i class="fa-regular fa-image"></i> Lampirkan Foto / Kenangan (Opsional)</label>
                        <div class="upload-dropzone" onclick="document.getElementById('photoInput').click()">
                            <i class="fa-solid fa-cloud-arrow-up" style="font-size:1.6rem; color:#e5e5e5; margin-bottom:0.4rem;"></i>
                            <p style="font-size:0.86rem; color:var(--text-heading);">Klik untuk memilih foto dari perangkatmu</p>
                            <p style="font-size:0.75rem; color:var(--text-body); margin-top:0.2rem;">Format JPG, PNG, WEBP</p>
                            <input type="file" id="photoInput" accept="image/*" onchange="handleFileSelected(event)">
                            <img id="photoPreviewThumb" class="photo-preview" alt="Preview Photo">
                        </div>
                    </div>

                    <button type="submit" id="btnSubmitForm" class="btn-main" style="justify-content:center; width:100%; margin-top:0.5rem;">
                        <i class="fa-solid fa-paper-plane"></i>
                        Kirim Pesan Rahasia
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- MODAL -->
    <div class="modal-bg" id="messageModal" onclick="if (event.target === this) closeFullScreenMessage()">
        <div class="modal-box">
            <button class="btn-close" onclick="closeFullScreenMessage()"><i class="fa-solid fa-xmark"></i></button>
            <div id="modalContent"></div>
        </div>
    </div>

    <!-- TOAST CONTAINER -->
    <div class="toast-wrap" id="toastContainer"></div>

    <!-- FOOTER -->
    <footer>
        <div class="app-container">
            <p>&copy; <?php echo date('Y'); ?> SongForYou</p>
        </div>
    </footer>

    <!-- SCRIPT ENGINE -->
    <script>
        function getApiUrl(path) { return `/backend/${path}`; }
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

        // Canvas Image Compression
        function compressImage(file, maxWidth = 1000, quality = 0.75) {
            return new Promise((resolve) => {
                if (!file || !file.type.startsWith('image/')) { resolve(''); return; }
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
                        resolve(canvas.toDataURL('image/jpeg', quality));
                    };
                    img.onerror = () => resolve('');
                };
                reader.onerror = () => resolve('');
            });
        }

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

        // Song Search
        const songInput = document.getElementById('songSearchInput');
        const songDropdown = document.getElementById('songDropdown');
        let searchTimer = null;

        if (songInput) {
            songInput.addEventListener('input', function() {
                clearTimeout(searchTimer);
                const q = this.value.trim();
                if (q.length < 2) { songDropdown.style.display = 'none'; return; }
                searchTimer = setTimeout(() => searchSongs(q), 300);
            });
            songInput.addEventListener('focus', function() {
                if (this.value.trim().length >= 2) songDropdown.style.display = 'block';
            });
        }

        async function searchSongs(query) {
            try {
                let results = [];
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
                            meaning: `Lagu "${item.trackName}" oleh ${item.artistName} mengalunkan perasaan mendalam dan makna cerita yang menyentuh.`
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
                songDropdown.innerHTML = '<div style="padding:0.9rem; color:var(--text-body); font-size:0.85rem; text-align:center;">Lagu tidak ditemukan</div>';
                songDropdown.style.display = 'block';
                return;
            }

            songDropdown.innerHTML = tracks.map((t, idx) => `
                <div class="search-option" onclick="selectSong(${idx})">
                    <img src="${t.coverUrl || 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=100'}" alt="Cover">
                    <div class="search-option-info">
                        <h5>${escapeHtml(t.title)}</h5>
                        <p>${escapeHtml(t.artist)}</p>
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

        document.addEventListener('click', function(e) {
            if (songDropdown && !songInput.contains(e.target) && !songDropdown.contains(e.target)) {
                songDropdown.style.display = 'none';
            }
        });

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

        // Form Submit Handler
        async function handleFormSubmit(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmitForm');
            const originalBtnText = btn.innerHTML;

            const receiver = document.getElementById('recipientInput').value.trim();
            const message = document.getElementById('messageInput').value.trim();

            if (!receiver) { showToast('Harap isi nama penerima pesan.', 'error'); return; }
            if (!selectedSong) { showToast('Silakan cari dan pilih lagu terlebih dahulu.', 'error'); return; }
            if (!message) { showToast('Harap isi pesan rahasiamu.', 'error'); return; }

            try {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim Pesan...';

                const payload = {
                    receiver: receiver,
                    senderName: 'Anonim',
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
                    document.getElementById('carouselTrack').scrollIntoView({ behavior: 'smooth', block: 'center' });
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

        // Audio Player Controller
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
                track.innerHTML = '<div style="color:var(--text-body); font-size:0.85rem;">Belum ada pesan terbaru.</div>';
                return;
            }

            track.innerHTML = featured.map(m => {
                const hasImage = m.images || m.photo_url || m.image_url;
                return `
                <div class="carousel-item ${hasImage ? 'has-photo' : 'no-photo'}" onclick="openFullScreenMessage(${m.id})" role="button" tabindex="0" onkeydown="if(event.key === 'Enter' || event.key === ' ') openFullScreenMessage(${m.id})">
                    ${hasImage ? `<div class="item-media"><img src="${getAppUrl(m.images || m.photo_url || m.image_url)}" alt="Kenangan untuk ${escapeHtml(m.receiver || 'Seseorang')}"></div>` : ''}
                    <div class="item-body">
                      <div>
                        <div style="font-size:0.75rem; color:var(--text-body); font-weight:600; text-transform:uppercase; letter-spacing:1px;">Untuk:</div>
                        <h4 class="item-to">${escapeHtml(m.receiver || 'Seseorang')}</h4>
                        <p class="item-text">"${escapeHtml(m.message || '')}"</p>
                      </div>
                    <div class="item-song-pill">
                        <i class="fa-brands fa-spotify"></i> ${escapeHtml(m.songTitle || 'Lagu Pilihan')}
                    </div>
                    </div>
                </div>
            `;
            }).join('');
        }

        function renderMessages(messages) {
            const grid = document.getElementById('messagesGrid');
            if (!grid) return;

            if (messages.length === 0) {
                grid.innerHTML = '<div style="grid-column: 1/-1; text-align:center; padding:3rem; color:var(--text-body);">Belum ada pesan tersimpan. Jadilah yang pertama membuat kenangan!</div>';
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
                    <div class="feed-card">
                        <div>
                            <div class="card-top">
                                <h3 class="card-recipient">Untuk ${escapeHtml(m.receiver || 'Seseorang')}</h3>
                                <span class="card-date">${formatDate(m.timestamp || m.created_at)}</span>
                            </div>

                            ${hasImage ? `
                                <div class="card-img-wrap" onclick="openFullScreenMessage(${m.id})">
                                    <img src="${imgSrc}" alt="Photo Attachment">
                                </div>
                            ` : ''}

                            <p class="card-message">${escapeHtml(m.message || '')}</p>

                            ${meaning ? `
                                <div class="meaning-box">
                                    <div class="meaning-lbl"><i class="fa-solid fa-quote-left"></i> Makna Lagu:</div>
                                    <p class="meaning-txt">${escapeHtml(meaning)}</p>
                                </div>
                            ` : ''}
                        </div>

                        <div class="audio-bar">
                            <img src="${songCover}" class="audio-cover" alt="Cover">
                            <div class="audio-info">
                                <div class="audio-title">${escapeHtml(songTitle)}</div>
                                <div class="audio-artist">${escapeHtml(songArtist)}</div>
                            </div>
                            <div class="audio-actions">
                                <button class="btn-play-audio" onclick="toggleAudioPlayback('${previewUrl}', '${escapeHtml(songTitle)}', '${escapeHtml(songArtist)}', this)">
                                    <i class="fa-solid fa-play"></i>
                                </button>
                                <a href="${spotifyUrl}" target="_blank" class="spotify-btn" title="Buka di Spotify"><i class="fa-brands fa-spotify"></i></a>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function filterMessages() {
            const q = document.getElementById('messagesFilterInput').value.toLowerCase();
            const filtered = allMessages.filter(m => {
                const rec = (m.receiver || '').toLowerCase();
                const msg = (m.message || '').toLowerCase();
                const sng = (m.songTitle || m.song_title || '').toLowerCase();
                const art = (m.songArtist || m.song_artist || '').toLowerCase();
                return rec.includes(q) || msg.includes(q) || sng.includes(q) || art.includes(q);
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
                    <div style="font-size:0.75rem; color:var(--text-body); font-weight:600; text-transform:uppercase; letter-spacing:1px;">Untuk:</div>
                    <h2 style="font-family:var(--font-serif); font-size:1.8rem; margin:0.2rem 0; color:var(--text-heading);">${escapeHtml(m.receiver || 'Seseorang')}</h2>
                    <p style="font-size:0.78rem; color:var(--text-subtle);">${formatDate(m.timestamp || m.created_at)}</p>
                </div>

                ${hasImage ? `<img src="${imgSrc}" style="width:100%; max-height:350px; object-fit:contain; background:#000; border-radius:12px; margin-bottom:1.2rem;">` : ''}

                <div class="modal-message">${escapeHtml(m.message || '')}</div>

                ${meaning ? `
                    <div class="meaning-box" style="margin-bottom:1.5rem;">
                        <div class="meaning-lbl"><i class="fa-solid fa-quote-left"></i> Makna Lagu:</div>
                        <p class="meaning-txt">${escapeHtml(meaning)}</p>
                    </div>
                ` : ''}

                <div class="audio-bar">
                    <img src="${songCover}" class="audio-cover" alt="Cover">
                    <div class="audio-info">
                        <div class="audio-title">${escapeHtml(songTitle)}</div>
                        <div class="audio-artist">${escapeHtml(songArtist)}</div>
                    </div>
                    <div class="audio-actions">
                        <button class="btn-play-audio" onclick="toggleAudioPlayback('${previewUrl}', '${escapeHtml(songTitle)}', '${escapeHtml(songArtist)}', this)">
                            <i class="fa-solid fa-play"></i>
                        </button>
                        <a href="${spotifyUrl}" target="_blank" class="spotify-btn" title="Buka di Spotify"><i class="fa-brands fa-spotify"></i></a>
                    </div>
                </div>
            `;

            document.getElementById('messageModal').style.display = 'flex';
        }

        function closeFullScreenMessage() {
            document.getElementById('messageModal').style.display = 'none';
            if (currentAudio) {
                currentAudio.pause();
                currentAudio.currentTime = 0;
            }
            if (currentPlayBtn) currentPlayBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
            currentPlayBtn = null;
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('messageModal').style.display === 'flex') {
                closeFullScreenMessage();
            }
        });

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
            } catch(e) { return String(dateInput); }
        }

        function showToast(msg, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = 'toast-item';
            if (type === 'error') {
                toast.style.borderColor = '#a3a3a3';
                toast.style.borderLeftColor = '#a3a3a3';
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
            window.addEventListener('pointermove', (event) => {
                document.body.style.setProperty('--cursor-x', `${(event.clientX / window.innerWidth) * 100}%`);
                document.body.style.setProperty('--cursor-y', `${(event.clientY / window.innerHeight) * 100}%`);
            }, { passive: true });
        });
    </script>
</body>
</html>
