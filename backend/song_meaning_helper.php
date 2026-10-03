<?php
/*
 * backend/song_meaning_helper.php
 * Generator dan Kamus Makna Lagu Otomatis & Presisi untuk SongForYou
 */

function getSongMeaning($title, $artist) {
    $titleClean = strtolower(trim($title));
    $artistClean = strtolower(trim($artist));
    $fullClean = $titleClean . ' ' . $artistClean;

    // ─── 1. Kamus Lagu Spesifik (Persisi & Sangat Akurat) ───────────
    $dictionary = [
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
        'my heart' => 'Ungkapan ketulusan hati yang menyerahkan seluruh cinta dan kehangatan kepada pasangan tanpa syarat.',
        'my love' => 'Kerinduan membara yang tak tertahankan untuk kembali ke pelukan orang yang paling dicintai di tanah kelahiran.',
        'my love mine all mine' => 'Bentuk kepasrahan dan rasa syukur atas cinta yang dirasakan di dalam dada sebagai satu-satunya aset paling berharga dalam hidup.',
        'my tears ricochet' => 'Duka dan kekecewaan atas pengkhianatan dari seseorang yang dahulu merupakan sosok terdekat dan paling dipercaya.',
        'my way' => 'Refleksi perjalanan hidup yang dijalani dengan keteguhan hati, keberanian, dan tanpa penyesalan atas setiap pilihan.',
        'mystery of love' => 'Keindahan sekaligus kepedihan dari cinta pertama yang penuh dengan keajaiban, kerinduan, dan rasa penasaran.',
        'mirrors' => 'Cinta sejati di mana pasangan hidup adalah cerminan dari belahan jiwa yang melengkapi seluruh kekurangan diri.',
        'one last time' => 'Permohonan maaf dan harapan untuk bisa menghabiskan satu momen terakhir bersama sebelum merelakan kepergiannya.',
        'almost is never enough' => 'Rasa sesal akan kisah cinta yang nyaris sempurna namun terhalang oleh waktu, jarak, dan keadaan.',
        'love me harder' => 'Keinginan untuk dicintai dengan komitmen, keberanian, dan kesungguhan tanpa ada keraguan sedikit pun.',
        'romansa ke masa depan' => 'Pengharapan cinta yang abadi dan optimisme menatap perjalanan hidup bersama ke depan dengan penuh kehangatan.',
        'break free' => 'Keberanian untuk lepas dari hubungan yang mengekang dan menemukan kembali kebebasan serta jati diri yang sejati.',
        'eleven' => 'Perasaan jatuh cinta yang membuat dunia terasa memikat, penuh warna, dan magis seperti dalam impian.',
        'gelora cintaku' => 'Perasaan cinta yang membara dan bergelora menghiasi relung hati dengan kebahagiaan.',
        'mesra-mesraannya kecil-kecilan dulu' => 'Kehangatan cinta sederhana yang tumbuh pelan-pelan melalui momen-momen kecil yang sangat berarti.',
        'tunjukkan' => 'Keberanian untuk mengungkapkan rasa cinta secara jujur dan terbuka tanpa ada keraguan.',
        'bunga abadi' => 'Simbol cinta abadi yang tidak akan pernah layu dan selalu mekar melintasi batas waktu.',
        'bunga terakhir' => 'Ungkapan perpisahan terakhir dan penghormatan setinggi-tingginya kepada kekasih jiwa yang tak tergantikan.',
        'bukti' => 'Rasa syukur dan bukti nyata kasih sayang atas anugerah sosok pasangan yang setia mendampingi hidup.',
        'bukan dia tapi aku' => 'Jeritan hati seseorang yang merasa lebih layak mencintai dan memperjuangkan pasangan ketimbang orang lain.',
        'buat aku tersenyum' => 'Harapan sederhana agar sang kekasih selalu hadir membawa tawa, kedamaian, dan ketenangan di kala duka.',
        'kita buat menyenangkan' => 'Ajakan untuk menikmati setiap detik hubungan dengan kegembiraan, ketulusan, dan tanpa beban.',
        'kamulah takdirku' => 'Keyakinan mendalam bahwa sang pasangan adalah takdir terindah yang dikirimkan Tuhan dalam hidup.',
        'itu aku' => 'Janji setia bahwa seseorang akan selalu hadir menjadi pelindung dan penopang di setiap langkah kekasihnya.',
        'andai aku bisa' => 'Kerapuhan hati ketika menyadari cinta tak bisa dipaksakan dan harus merelakan pasangan pergi.',
        'ditinggal bang dika' => 'Ungkapan kesedihan dan rasa kehilangan mendalam saat sosok terdekat pergi meninggalkan kenangan.',
        'bukan orangnya' => 'Kesadaran bahwa seseorang yang dicintai mungkin bukan jodoh yang ditakdirkan untuk mendampingi hidup.',
        'buat aku ragu' => 'Perasaan bimbang dan ragu saat ketulusan cinta mulai dipertanyakan oleh sikap pasangan.'
    ];

    // Cek match langsung dari kamus
    foreach ($dictionary as $key => $meaning) {
        if (strpos($fullClean, $key) !== false || strpos($key, $titleClean) !== false) {
            return $meaning;
        }
    }

    // ─── 2. Deteksi Kata Kunci Kesedihan / Perpisahan (PENTING: Didahulukan) ───
    if (preg_match('/(tak bahagia|bukan|usai|lepas|mati rasa|simpan|sedih|sad|cry|tears|pergi|hilang|leave|lonely|sepi|luka|break|sorry|maaf|ditinggal|patah|kecewa|ending|akhir)/i', $title)) {
        return 'Lagu "' . $title . '" karya ' . $artist . ' mengekspresikan kepedihan hati, rasa kehilangan, serta proses merelakan seseorang yang pernah menjadi bagian terpenting dalam hidup.';
    }

    // ─── 3. Deteksi Kata Kunci Kerinduan / Kenangan ─────────────────
    if (preg_match('/(rindu|miss|remember|memory|kenangan|bayang|kembali|home|night|malam|senja|about you|light)/i', $title)) {
        return 'Lagu "' . $title . '" karya ' . $artist . ' membawa nuansa kerinduan hangat dan nostalgia akan kenangan indah bersama seseorang yang selalu bernaung di dalam pikiran.';
    }

    // ─── 4. Deteksi Kata Kunci Percintaan & Kasih Sayang ─────────────
    if (preg_match('/(love|cinta|sayang|heart|soul|kasih|jodoh|takdir|lover|sweet|honey|everything|milik|milikku|sempurna)/i', $title)) {
        return 'Lagu "' . $title . '" karya ' . $artist . ' mengisahkan tentang ketulusan cinta mendalam, rasa syukur atas kehadiran pasangan, dan kehangatan yang mengikat dua jiwa.';
    }

    // ─── 5. Deteksi Kata Kunci Kebahagiaan ───────────────────────────
    if (preg_match('/(smile|senyum|happy|bahagia|tawa|fun|dance|sky|sun|terang|bunga)/i', $title)) {
        return 'Lagu "' . $title . '" karya ' . $artist . ' menyebarkan energi positif, keceriaan, dan rasa bahagia yang hadir saat seseorang mewarnai hari-hari dengan kehangatan.';
    }

    // ─── 6. Fallback Puitis Berkualitas ─────────────────────────────
    return 'Lagu "' . $title . '" karya ' . $artist . ' mengabadikan perasaan emosional yang mendalam, menyampaikan pesan rahasia yang sulit diucapkan dengan kata-kata biasa.';
}
