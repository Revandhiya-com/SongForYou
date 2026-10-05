<?php
/*
 * backend/song_meaning_helper.php
 * Generator dan Kamus Makna Lagu Otomatis & Presisi untuk SongForYou
 */

function fetchLyricsForMeaning($title, $artist) {
    static $cache = [];
    $artist = trim(explode(',', $artist)[0]);
    $cacheKey = strtolower($artist . '|' . $title);
    if (array_key_exists($cacheKey, $cache)) return $cache[$cacheKey];

    $url = 'https://api.lyrics.ovh/v1/' . rawurlencode($artist) . '/' . rawurlencode($title);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 2,
        CURLOPT_TIMEOUT => 4,
        CURLOPT_USERAGENT => 'SongForYou/1.0'
    ]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $payload = $response ? json_decode($response, true) : null;
    return $cache[$cacheKey] = ($status === 200 && !empty($payload['lyrics'])) ? $payload['lyrics'] : '';
}

function buildMeaningFromLyrics($title, $artist, $lyrics) {
    $text = mb_strtolower($lyrics, 'UTF-8');
    $themes = [
        'perpisahan dan upaya merelakan' => '/\b(pergi|berpisah|pamit|lepas|kehilangan|lupa|goodbye|leave|gone|lost|break)\b/ui',
        'kerinduan pada sosok yang tidak hadir' => '/\b(rindu|merindu|kenangan|bayang|jauh|jarak|miss|memory|remember|home)\b/ui',
        'kesetiaan dan keinginan untuk bertahan' => '/\b(setia|selamanya|tetap|bersama|janji|takkan|forever|always|stay|promise)\b/ui',
        'pemulihan dan keberanian untuk melangkah' => '/\b(sembuh|bangkit|kuat|melangkah|harapan|cahaya|heal|strong|hope|rise)\b/ui',
        'cinta yang rapuh dan penuh keraguan' => '/\b(takut|ragu|salah|luka|kecewa|cry|afraid|hurt|sorry|pain)\b/ui'
    ];
    $found = [];
    foreach ($themes as $theme => $pattern) {
        if (preg_match($pattern, $text)) $found[] = $theme;
    }
    $primary = $found[0] ?? 'perjalanan emosi personal';
    $secondary = $found[1] ?? null;
    $meaning = '"' . $title . '" oleh ' . $artist . ' berpusat pada ' . $primary;
    if ($secondary) $meaning .= ', dengan lapisan ' . $secondary;
    return $meaning . '. Makna ini disusun dari tema yang muncul dalam lirik lagu, bukan dari template judul.';
}

function getSongMeaning($title, $artist, $lookUpLyrics = true) {
    $titleClean = strtolower(trim($title));
    $artistClean = strtolower(trim($artist));
    $fullClean = $titleClean . ' ' . $artistClean;
    $titleKey = trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9]+/i', ' ', $titleClean)));

    // ─── 1. Kamus Makna Lagu Spesifik (Akurat & Puitis) ───────────
    $dictionary = [
        // Indie & Viral Indonesia
        'there is a light that never goes out' => 'Lagu melancholic legendaris tentang keputusasaan dan kerinduan mendalam untuk berada di samping orang tersayang, bahkan jika harus menghadapi bahaya bersama di dalam kegelapan malam.',
        'akhir tak bahagia' => 'Kisah perpisahan yang pahit dan penuh penyesalan ketika sebuah hubungan yang telah diperjuangkan dengan keras harus berakhir dengan kesedihan dan luka.',
        'usai di sini' => 'Pernyataan kedewasaan untuk mengakhiri hubungan yang tak lagi sejalan dan merelakan pasangan pergi tanpa rasa benci.',
        'melepasmu' => 'Keberanian dan keikhlasan hati untuk merelakan sosok kekasih pergi ketika menyadari jiwanya tak lagi ada untuk kita.',
        'perayaan mati rasa' => 'Proses penerimaan rasa sakit hati setelah kekecewaan besar, hingga mencapai titik di mana perasaan luka berubah menjadi mati rasa yang sunyi.',
        'simpan saja' => 'Keputusan untuk memendam perasaan cinta secara rahasia demi menjaga hubungan dan menghindari kekecewaan.',
        'tunjuk satu bintang' => 'Harapan dan petunjuk cinta di tengah kegelapan, bagaikan menunjuk satu bintang terang pemandu arah.',
        'about you' => 'Perasaan nostalgia mendalam di mana setiap sudut kehidupan dan bayangan kenangan selalu mengingatkan pada satu sosok yang tak pernah benar-benar pergi dari ingatan.',
        'garis terdepan' => 'Kisah pengagum rahasia yang rela berdiri di barisan paling depan untuk menjaga dan melindungi orang yang dicintainya, meski sadar hatinya milik orang lain.',
        'dewi' => 'Kekaguman dan pemujaan tinggi kepada seorang wanita bagaikan dewi khayangan yang memikat seluruh jiwa.',
        'bila' => 'Pertanyaan dan imajinasi tentang apa yang akan terjadi jika perasaan cinta yang terpendam akhirnya diungkapkan.',
        'teh hijau' => 'Kehangatan sederhana dan ketenangan jiwa yang dihadirkan oleh sosok yang dicintai di tengah riuhnya dunia.',
        'i knew it, i knew you' => 'Perasaan firasat dan kepastian mendalam saat mengenali karakter asli serta kenangan seseorang yang tak terduga.',
        'my everything' => 'Pengakuan tulus bahwa pasangan adalah segalanya, pusat dari dunia dan sumber kebahagiaan utama.',
        'you are my everything' => 'Ungkapan penuh kehangatan bahwa seseorang merupakan sosok terpenting yang melengkapi seluruh ruang di dalam hidup.',
        'mr. loverman' => 'Lagu ini menggambarkan rasa takut akan kehilangan orang tersayang dan kerapuhan dalam mengungkapkan perasaan cinta mendalam di saat hati merasa kesepian.',
        'blinding lights' => 'Lagu tentang rasa kesepian dan kerinduan mendalam pada seseorang yang mampu meredakan kegelapan dan kekosongan hidup di tengah gemerlap kota.',
        'perfect' => 'Lagu romantisme sejati tentang menemukan pasangan hidup sejak masa muda dan membangun harapan serta impian masa depan bersama.',
        'sorai' => 'Lagu tentang merelakan dan merayakan perpisahan dengan rasa terima kasih atas setiap kenangan indah yang pernah terukir bersama.',
        'soulmate' => 'Menggambarkan keyakinan pada takdir cinta sejati dan kerinduan mendalam untuk menyatu kembali dengan sang belahan jiwa.',
        'somebody\'s pleasure' => 'Kisah tentang seseorang yang merasa lelah menjadi pelampiasan sementara dan merindukan kebahagiaan serta ketenangan cinta yang sejati.',
        'someone like you' => 'Perjuangan merelakan mantan kekasih yang telah bahagia dengan pasangan barunya sambil tetap mendoakan yang terbaik dari kejauhan.',
        'somewhere only we know' => 'Nostalgia dan kerinduan akan tempat serta kenangan rahasia yang hanya dipahami dan dirasakan oleh dua insan yang saling mencintai.',
        'someone you loved' => 'Luka mendalam saat kehilangan sosok tempat bersandar dan merasa hampa di saat-saat paling membutuhkan kehangatan.',
        'something just like this' => 'Bentuk cinta sederhana yang tidak menuntut keajaiban atau kekuatan pahlawan super, melainkan kehadiran dan kasih sayang nyata.',

        // Tulus
        'monokrom' => 'Ungkapan rasa terima kasih dan penghormatan tulus kepada sosok-sosok penyayang yang mewarnai lembaran kisah hidup dari masa lalu.',
        'hati-hati di jalan' => 'Kisah perpisahan dua insan yang semula saling mencintai dan sejalan, namun akhirnya harus berpisah arah dengan rasa rela.',
        'diri' => 'Pesan pengingat lembut untuk mencintai dan memeluk diri sendiri, berdamai dengan luka, serta memberi waktu untuk pemulihan jiwa.',
        'interaksi' => 'Dilema emosional saat mencoba membatasi perasaan agar tidak terlalu dalam jatuh cinta pada orang yang belum tentu ditakdirkan bersama.',
        'labirin' => 'Perjalanan mencari cara untuk menembus dan memahami isi hati seseorang yang misterius bagaikan susunan labirin.',
        'tujuh belas' => 'Nostalgia akan masa kepolosan usia 17 tahun dan kerinduan untuk menjaga jiwa muda yang penuh semangat dan kejujuran.',
        'jatuh suka' => 'Sensasi hangat dan manis saat pertama kali merasakan kekaguman dan getaran cinta yang perlahan tumbuh.',
        'ruang sendiri' => 'Pentingnya jarak dan ruang pribadi dalam sebuah hubungan agar benih kerinduan dan apresiasi dapat tumbuh lebih sehat.',
        'pamit' => 'Perpisahan yang dilakukan secara dewasa dan penuh ketenangan tanpa dendam saat dua jiwa tak lagi bisa bersama.',
        'gajah' => 'Transformasi dari ejekan masa kecil menjadi kekuatan karakter dan rasa percaya diri yang kokoh.',
        'sepatu' => 'Kiasan dua insan yang selalu bersama dan saling melengkapi, namun tak akan pernah bisa menyatu karena perbedaan.',
        'teman hidup' => 'Janji dan komitmen tulus untuk berjalan berdampingan melewati suka duka kehidupan sebagai pasangan sejati.',
        'sewindu' => 'Kesetiaan menunggu cinta seseorang selama bertahun-tahun yang akhirnya harus berujung pada keikhlasan.',

        // Bernadya
        'kata mereka ini berlebihan' => 'Perjuangan menguras emosi seseorang yang merela mengubah seluruh kepribadiannya demi menyenangkan kekasih, meski dianggap berlebihan oleh orang lain.',
        'satu bulan' => 'Kehampaan dan kebingungan di bulan pertama setelah perpisahan ketika melihat mantan kekasih tampak begitu cepat melanjutkan hidup.',
        'untungnya' => 'Rasa syukur atas proses kedewasaan pasca patah hati, menyadari bahwa perpisahan justru menyelamatkan diri dari luka yang lebih besar.',
        'hidup harus tetap berjalan' => 'Pemberian keteguhan hati bahwa meski dunia terasa runtuh akibat kekecewaan, roda kehidupan dan harapan baru terus berputar.',
        'kini mereka tahu' => 'Pengakuan jujur saat topeng kesempurnaan hubungan akhirnya terkuak, dan kenyataan pahit diketahui oleh orang-orang sekitar.',
        'apa mungkin' => 'Pertanyaan bimbang dan rasa penasaran mendalam akan alasan di balik perubahan sikap pasangan yang tiba-tiba menjauh.',
        'sialan' => 'Kekesalan dan kerapuhan emosi saat ingatan tentang masa lalu tiba-tiba muncul kembali hanya karena hal-hal kecil.',

        // Nadhif Basalamah & Mahalini
        'penjaga hati' => 'Keinginan kuat dan tekad tulus untuk menjadi sosok pelindung dan tempat pulang paling aman bagi kekasih tercinta.',
        'kota ini tak sama tanpamu' => 'Perasaan hampa dan sunyi yang meliputi sudut-sudut kota setelah kepergian seseorang yang membawa kehangatan.',
        'sial' => 'Rasa sesal dan amarah pada diri sendiri karena telah terlalu mudah mempercayai dan menyerahkan seluruh hati kepada orang yang salah.',
        'sisa rasa' => 'Ketabahan menyimpan sisa-sisa kenangan dan rasa cinta yang tertinggal setelah sosok terpenting dalam hidup berpulang.',
        'melawan restu' => 'Perjuangan cinta yang gigih namun harus terbentang oleh benteng restu dan kenyataan hidup.',
        'kisah sempurna' => 'Ungkapan rasa bersyukur saat hadir seseorang yang mampu menyembuhkan trauma masa lalu dan merajut kembali kisah cinta yang utuh.',

        // Fiersa Besari & Hindia
        'celengan rindu' => 'Kerinduan yang menumpuk bagaikan celengan yang siap ditumpahkan saat momen pertemuan dengan sang kekasih tiba.',
        'april' => 'Merelakan seseorang yang dicintai bahagia bersama pilihan hatinya, meski diri sendiri harus menanggung rasa sepi.',
        'pelukku untuk pelukmu' => 'Kehangatan pelukan dan dukungan moral saat dua pasangan saling menopang di tengah kepungan ujian hidup.',
        'waktu yang salah' => 'Perjumpaan dua jiwa yang saling menyukai namun berada di timing waktu dan kondisi emosional yang belum siap.',
        'evaluasi' => 'Refleksi mendalam untuk mengistirahatkan pikiran dari kelelahan hidup, mengakui kegagalan tanpa meredupkan harapan.',
        'secukupnya' => 'Pengingat untuk tidak meratapi duka secara berlebihan dan merayakan kebahagiaan secara bijak dan secukupnya.',
        'rumah ke rumah' => 'Perjalanan pencarian tempat bermuara yang sesungguhnya dari satu hubungan ke hubungan lain hingga menemukan kedamaian.',
        'cincin' => 'Simbol komitmen sederhana yang mengikat janji saling menerima kekurangan dan mempertahankan hubungan.',

        // Pamungkas & Misellia & Last Child
        'to the bone' => 'Perasaan cinta dan hasrat yang sangat mendalam hingga ke merasuk ke dalam sumsum tulang dan jiwa.',
        'one only' => 'Penetapan hati bahwa seseorang adalah satu-satunya sosok yang paling diinginkan untuk mendampingi masa depan.',
        'i love you but i\'m letting go' => 'Keputusan pahit melepaskan seseorang yang sangat dicintai demi kebaikan dan kebahagiaan masa depannya.',
        'diam-diam' => 'Kisah penyimpanan perasaan cinta secara rahasia yang disimpan rapi di balik senyuman dan interaksi biasa.',
        'duka' => 'Kedukaan mendalam saat harus berpisah dengan sosok yang menjadi alasan utama untuk tersenyum.',
        'bernafas tanpamu' => 'Sensasi kehampaan dan kesuksesan menjalani hari-hari saat belahan jiwa tak lagi berada di sisi.',

        // Sheila on 7 & Glenn Fredly & Raisa & Afgan
        'dan' => 'Permohonan maaf yang tulus dan keikhlasan dilupakan demi menghapus penderitaan kekasih akibat kesalahan diri.',
        'sephia' => 'Pesan perpisahan kepada kekasih rahasia untuk kembali ke kehidupan nyata dan mengejar kebahagiaan sejati.',
        'anugerah terindah yang pernah ku miliki' => 'Rasa syukur setinggi-tingginya kepada Tuhan atas kehadiran sosok kekasih yang melengkapi hidup.',
        'januari' => 'Perpisahan emosional di bulan Januari yang menandai berakhirnya kisah cinta yang telah lama terajut.',
        'sekali ini saja' => 'Permohonan terakhir untuk diberi kesempatan memeluk dan menggenggam erat orang tersayang sebelum berpisah.',
        'kali kedua' => 'Keindahan kesempatan kedua dalam meraih dan membangun kembali cinta yang sempat terputus.',
        'terima kasih cinta' => 'Ungkapan terima kasih atas semua pelajaran, kehangatan, dan cinta yang pernah diberikan meski harus berpisah.',

        // Western Popular Songs
        'yellow' => 'Pernyataan cinta murni dan pengorbanan tanpa batas di mana segala keindahan alam semesta dipersembahkan untuk sang kekasih.',
        'fix you' => 'Janji kesetiaan untuk memberikan penghiburan, bimbingan, dan kehangatan saat seseorang berada di titik terendah.',
        'the scientist' => 'Keinginan kuat untuk memutar balik waktu kembali ke awal kisah demi memperbaiki kesalahan yang merusak hubungan.',
        'until i found you' => 'Kepastian bahwa pencarian cinta sejati telah berakhir setelah menemukan sosok yang memberikan kedamaian abadi.',
        'golden hour' => 'Momen magis dan keindahan berkilau saat berada di dekat orang yang dicintai, bagaikan kehangatan sinar matahari senja.',
        'glimpse of us' => 'Kerapuhan jiwa yang selalu melihat bayangan mantan kekasih saat bersama pasangan yang baru.',
        'seasons' => 'Perubahan musim dan perjalanan waktu yang tidak melunturkan perasaan cinta hangat kepada pasangan.',
        'i wanna be yours' => 'Pengabdian total di mana seseorang rela menjadi benda atau hal apapun demi selalu dekat dengan kekasihnya.',
        'creep' => 'Rasa tidak percaya diri dan perasaan terasing saat mengagumi seseorang yang dianggap terlalu sempurna.',
        'lover' => 'Perayaan romantisme abadi dan keinginan untuk menghabiskan seluruh sisa musim dan hidup bersama.',
        'all too well' => 'Nostalgia dan detail ingatan yang sangat tajam tentang kisah cinta masa lalu yang indah namun membekaskan luka.',
        'cruel summer' => 'Dinamika cinta musim panas yang intens, rahasia, penuh tekanan emosi namun tak tertahankan.',
        'die for you' => 'Komitmen dan pengorbanan cinta tanpa batas di mana keselamatan dan kebahagiaan pasangan berada di atas segalanya.',
        'bimbang' => 'Bimbang menggambarkan pergulatan batin ketika cinta membuat seseorang rindu sekaligus tersiksa. Ia berada di persimpangan: ingin mempertahankan rasa yang indah, tetapi juga lelah menghadapi ketidakpastian dan kehilangan yang terasa seperti separuh diri.',
        'panasea' => 'Lagu ini memaknai cinta sebagai panasea—obat yang menguatkan. Janji untuk tidak berubah, melintasi ruang dan waktu, hingga tekad bahwa rintangan tidak membuatnya menyerah menggambarkan kesetiaan yang tetap bergerak maju meski terpisah.'
    ];

    // Cek judul secara utuh agar satu kata pendek tidak mengambil makna lagu lain.
    uksort($dictionary, fn($a, $b) => strlen($b) <=> strlen($a));
    foreach ($dictionary as $key => $meaning) {
        $keyClean = trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9]+/i', ' ', $key)));
        if ($titleKey === $keyClean) {
            return $meaning;
        }
    }

    if ($lookUpLyrics) {
        $lyrics = fetchLyricsForMeaning($title, $artist);
        if ($lyrics !== '') return buildMeaningFromLyrics($title, $artist, $lyrics);
    }

    return 'Makna "' . $title . '" oleh ' . $artist . ' sedang disiapkan dari sumber lirik lagu.';

    // ─── 2. Deteksi Kata Kunci Kesedihan / Perpisahan ───────────────
    if (preg_match('/(tak bahagia|bukan|usai|lepas|mati rasa|simpan|sedih|sad|cry|tears|pergi|hilang|leave|lonely|sepi|luka|break|sorry|maaf|ditinggal|patah|kecewa|ending|akhir|gagal|hampa|berpisah|lupa|forget|hurt|die|ghost|pain|alone|goodbye|pamit)/i', $fullClean)) {
        return 'Lagu "' . $title . '" karya ' . $artist . ' mengekspresikan kepedihan hati, rasa kehilangan, serta proses merelakan seseorang yang pernah menjadi bagian terpenting dalam hidup.';
    }

    // ─── 3. Deteksi Kata Kunci Kerinduan / Kenangan ─────────────────
    if (preg_match('/(rindu|miss|remember|memory|kenangan|bayang|kembali|home|night|malam|senja|about you|light|bintang|star|shadow|dream|impian|pulang|jauh|jarak|distance)/i', $fullClean)) {
        return 'Lagu "' . $title . '" karya ' . $artist . ' membawa nuansa kerinduan hangat dan nostalgia akan kenangan indah bersama seseorang yang selalu bernaung di dalam pikiran.';
    }

    // ─── 4. Deteksi Kata Kunci Percintaan & Kasih Sayang ─────────────
    if (preg_match('/(love|cinta|sayang|heart|soul|kasih|jodoh|takdir|lover|sweet|honey|everything|milik|milikku|sempurna|perfect|beautiful|cantik|indah|always|forever|bersama|janji|promise)/i', $fullClean)) {
        return 'Lagu "' . $title . '" karya ' . $artist . ' mengisahkan tentang ketulusan cinta mendalam, rasa syukur atas kehadiran pasangan, dan kehangatan yang mengikat dua jiwa.';
    }

    // ─── 5. Deteksi Kata Kunci Kebahagiaan & Harapan ────────────────
    if (preg_match('/(smile|senyum|happy|bahagia|tawa|fun|dance|sky|sun|terang|bunga|flower|fly|free|bebas|semangat|cahaya|shine|bright|pagi|morning)/i', $fullClean)) {
        return 'Lagu "' . $title . '" karya ' . $artist . ' menyebarkan energi positif, keceriaan, dan rasa bahagia yang hadir saat seseorang mewarnai hari-hari dengan kehangatan.';
    }

    // ─── 6. Generator Puitis Kontekstual Berdasarkan Judul & Penyanyi ─
    return 'Makna spesifik untuk "' . $title . '" oleh ' . $artist . ' belum tersedia di katalog terverifikasi. Sistem tidak akan menggantinya dengan makna umum yang berisiko keliru.';
}
