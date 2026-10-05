-- Jalankan sekali pada database PostgreSQL/Supabase yang sudah ada.
ALTER TABLE messages ADD COLUMN IF NOT EXISTS lyric_excerpt VARCHAR(280) DEFAULT NULL;
ALTER TABLE messages ADD COLUMN IF NOT EXISTS lyric_section VARCHAR(30) DEFAULT NULL;
