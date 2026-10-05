// --- SCRIPT BLOCK 1 ---

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

        // Helper: Ambil preview URL dari iTunes Search API (country=ID)
        const getItunesPreview = async (title, artist) => {
            const cleanTitle = (title || '').replace(/\(feat\.[^)]+\)/gi, '').replace(/\([^)]+\)/g, '').replace(/\[[^\]]+\]/g, '').replace(/-\s*.*$/, '').trim();
            const cleanArtist = (artist || '').split(',')[0].split('&')[0].trim();
            const q = encodeURIComponent(`${cleanTitle} ${cleanArtist}`);

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

                    // Priority 2: Substring match untuk penyanyi & judul
                    for (const track of data.results) {
                        if (!track.previewUrl) continue;
                        const trTitle = (track.trackName || '').toLowerCase();
                        const trArtist = (track.artistName || '').toLowerCase();
                        if ((trTitle.includes(targetTitle) || targetTitle.includes(trTitle)) &&
                            (trArtist.includes(targetArtist) || targetArtist.includes(trArtist))) {
                            return track.previewUrl;
                        }
                    }
                }
            } catch(e) {}

            return null;
        };


        let carouselAutoScrollTimer = null;
        let isCarouselHovered = false;

        function startCarouselAutoScroll() {
            if (carouselAutoScrollTimer) clearInterval(carouselAutoScrollTimer);
            
            carouselAutoScrollTimer = setInterval(() => {
                const wrapper = document.getElementById('carouselWrapper');
                if (!wrapper || isCarouselHovered) return;

                if (wrapper.scrollLeft >= (wrapper.scrollWidth - wrapper.clientWidth - 4)) {
                    wrapper.scrollLeft = 0;
                } else {
                    wrapper.scrollLeft += 1.2;
                }
            }, 30);
        }

        function scrollCarousel(dir) {
            const wrapper = document.getElementById('carouselWrapper');
            if (!wrapper) return;
            const amount = 300 * dir;
            wrapper.scrollBy({ left: amount, behavior: 'smooth' });
        }

        // Fetch Messages from DB
        async function fetchMessages() {
            try {
                const res = await fetch(getApiUrl('get_messages.php'));
                const data = await res.json();
                if (data.success) {
                    allMessages = data.messages;
                    renderMessages(allMessages);
                    startCarouselAutoScroll();
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

            if (!receiver) {
                alert('Silakan isi nama penerima terlebih dahulu!');
                document.getElementById('receiver').focus();
                return;
            }

            if (!selectedSong) {
                alert('Silakan pilih lagu dari hasil pencarian Spotify terlebih dahulu!');
                document.getElementById('songSearchInput').focus();
                return;
            }

            if (!message) {
                alert('Silakan tulis pesan rahasiamu terlebih dahulu!');
                document.getElementById('messageText').focus();
                return;
            }

            btn.disabled = true;
            btn.textContent = 'MEMPROSES PESAN...';

            let base64Img = '';
            if (fileInput.files && fileInput.files[0]) {
                try {
                    base64Img = await fileToBase64(fileInput.files[0]);
                } catch(e) {
                    console.warn('Image process error:', e);
                }
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

                if (res.ok && data.success) {
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
                    alert('Gagal mengirim pesan: ' + (data.message || data.error || 'Terjadi kesalahan server'));
                }
            } catch (err) {
                console.error('Submit error:', err);
                alert('Gagal mengirim pesan: Terjadi kesalahan jaringan. Coba periksa koneksi atau ukuran foto.');
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

            // Gunakan previewUrl dari DB terlebih dahulu (100% verified & accurate). Jika kosong baru cari via getItunesPreview
            let audioUrl = msg.previewUrl;
            if (!audioUrl && msg.songTitle && msg.songArtist) {
                audioUrl = await getItunesPreview(msg.songTitle, msg.songArtist);
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
                        document.getElementById('fullAudioTime').textContent = `00:00 / ${durMins}:${durSecs}`;
                        document.getElementById('fullProgressBarFill').style.width = '0%';
                        try {
                            audioInstance.currentTime = 0;
                        } catch(e) {}
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

                if (isReStart) {
                    try {
                        audioInstance.currentTime = 0;
                    } catch(e) {}
                    currentCardHasPlayed = true;
                }

                audioInstance.play().then(() => {
                    isAudioPlaying = true;
                    playBtn.innerHTML = '<i class="fa-solid fa-pause"></i>';
                    vinyl.classList.add('is-playing');
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
            return new Promise((resolve) => {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = (e) => {
                    const img = new Image();
                    img.src = e.target.result;
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        let width = img.width;
                        let height = img.height;
                        const maxDim = 1400;

                        if (width > maxDim || height > maxDim) {
                            if (width > height) {
                                height = Math.round((height * maxDim) / width);
                                width = maxDim;
                            } else {
                                width = Math.round((width * maxDim) / height);
                                height = maxDim;
                            }
                        }

                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        // Kompresi foto berkualitas tinggi HD (~250KB - 400KB) agar tetap tajam dan jauh di bawah batas 4.5MB Vercel
                        const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
                        resolve(dataUrl);
                    };
                    img.onerror = () => resolve(e.target.result);
                };
                reader.onerror = () => resolve('');
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

        // Close fullscreen view with ESC key on laptop
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeFullScreenMessage();
        });

        // Setup Carousel Mouse & Touch Listeners & Prevent Zoom Out
        window.addEventListener('DOMContentLoaded', () => {
            document.addEventListener('gesturestart', function (e) { e.preventDefault(); });
            document.addEventListener('touchmove', function (event) {
                if (event.scale !== undefined && event.scale !== 1) {
                    event.preventDefault();
                }
            }, { passive: false });

            const wrapper = document.getElementById('carouselWrapper');
            if (wrapper) {
                wrapper.addEventListener('mouseenter', () => { isCarouselHovered = true; });
                wrapper.addEventListener('mouseleave', () => { isCarouselHovered = false; });
                wrapper.addEventListener('touchstart', () => { isCarouselHovered = true; }, { passive: true });
                wrapper.addEventListener('touchend', () => {
                    setTimeout(() => { isCarouselHovered = false; }, 3000);
                }, { passive: true });
            }
        });
    

