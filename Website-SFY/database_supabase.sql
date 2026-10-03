-- ========================================================
-- Database Schema for SongForYou (SFY) - PostgreSQL / Supabase
-- ========================================================

-- Drop tables if exist (urutan penting karena ada foreign key)
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS songs;
DROP TABLE IF EXISTS users;

-- --------------------------------------------------------
-- 1. Table `songs`
-- --------------------------------------------------------
CREATE TABLE songs (
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

-- --------------------------------------------------------
-- 2. Table `users` (Opsional untuk sistem user masa depan)
-- --------------------------------------------------------
CREATE TABLE users (
    id         SERIAL PRIMARY KEY,
    username   VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- 3. Table `messages`
-- --------------------------------------------------------
CREATE TABLE messages (
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

-- --------------------------------------------------------
-- Seed Data / Data Awal
-- --------------------------------------------------------
INSERT INTO songs (spotify_id, title, artist, cover_url, meaning, spotify_url, preview_url) VALUES
('3n3Ppam7vgaVa1iaRUc9Lp', 'Mr. Loverman', 'Ricky Montgomery', 'https://i.scdn.co/image/ab67616d0000b27341ad37380f2d8e05a81a7b44', 'Lagu ini menggambarkan rasa takut akan kehilangan orang tersayang dan kerapuhan dalam mengungkapkan perasaan cinta mendalam di saat hati merasa kesepian.', 'https://open.spotify.com/track/3n3Ppam7vgaVa1iaRUc9Lp', NULL),
('0VjIjW4GlUZAMYd2vXMi3b', 'Blinding Lights', 'The Weeknd', 'https://i.scdn.co/image/ab67616d0000b2738863bc11d2aa12b54f5a86d7', 'Lagu tentang rasa kesepian dan kerinduan mendalam pada seseorang yang mampu meredakan kegelapan dan kekosongan hidup di tengah gemerlap kota.', 'https://open.spotify.com/track/0VjIjW4GlUZAMYd2vXMi3b', NULL);

INSERT INTO messages (user_id, song_id, recipient_name, sender_name, message, images, slug) VALUES
(0, 1, 'Zahra', 'Pengagum Rahasia', 'Terima kasih sudah selalu ada dan mencerahkan hari-hariku!', NULL, 'zahra-a3f9c12b');
