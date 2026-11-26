# Memori Proyek

## Status
- Aplikasi Laravel 12 sudah di-scaffold di root workspace (digabung dengan docs/).
- Phase 1 selesai: role/auth, master anggota (daftar mandiri + verifikasi admin), landing MPKSDI, portal profil anggota.
- Phase 2 selesai: event, enroll peserta, sesi, presensi QR, tes kognitif, psikomotor, afektif, angket, RTL, nilai akhir + ranking, laporan PDF, sertifikat PDF, ekspor Excel, ID Card PDF, fasilitator & executive.
- Database lokal: MariaDB `arqam` (root/272800 di .env local; jangan commit .env).
- Admin seed: `php artisan db:seed` → email dari `ADMIN_EMAIL` (default admin@mpksdi.test), password `ADMIN_PASSWORD` (default ubah-segera — wajib diganti).
- Command `php artisan ba:siapkan-materi {eventId}` — isi rubrik psikomotor, butir afektif, dan soal RTL default (adaptasi dari referensi).
- Referensi dibaca dari `D:\website\SKRIPSI\SISTEM`; jangan edit proyek referensi saat membangun Arqam.

## Referensi teknis
Sistem referensi memakai Laravel 12, PHP 8.2, Blade, Tailwind CSS 4, Alpine.js, Vite, DomPDF, Laravel Excel, dan Simple QR Code.

Pola berguna dari referensi:
- Event dengan status persiapan, berlangsung, selesai.
- Role admin, fasilitator, peserta, executive.
- Peserta sebagai master data; pendaftaran event sebagai relasi terpisah.
- Sesi event terpisah dari event.
- Presensi per sesi memakai QR terverifikasi.
- Skor penilaian tersimpan per event-peserta.
- PDF untuk ID Card, sertifikat, laporan; Excel untuk impor/ekspor.

## Keputusan aktif
- Struktur sistem: landing page MPKSDI Karanganyar + portal master anggota + portal BA.
- Master anggota hanya kader/pengurus (PDM, PCM, PRM, AUM, ORTOM); satu anggota boleh banyak organisasi/jabatan.
- Pendaftaran anggota mandiri; verifikasi admin dulu; status `menunggu_verifikasi → aktif → nonaktif`.
- Anggota aktif boleh ubah profil tanpa verifikasi ulang.
- Peserta BA di-enroll admin dari master anggota; tidak ada pendaftaran BA mandiri; akun sama dengan portal anggota.
- Daftar field form anggota dicatat di `FORM-ANGGOTA.md`.
- Mulai dari MVP pada `PRD.md`.
- Kehadiran diperlakukan sebagai syarat kelulusan, bukan komponen nilai akademik, sampai pemilik proses menyetujui rumus berbeda.
- Ranking hanya memakai urutan nilai akhir tertinggi ke terendah. AHP-SAW dan ranking berbasis algoritma keputusan keluar dari scope permanen.
- Materi, soal, rubrik psikomotor, butir angket, dan alur penilaian diambil dari Arqam Apps di `D:\website\SKRIPSI\SISTEM` lalu disesuaikan; jangan salin kode mentah dan jangan edit referensi.
- Target deploy: cPanel, PHP 8.2+, MySQL, queue/cron tanpa daemon, storage terbatas akses.
- WhatsApp API dan SIMPEKA hanya placeholder desain sampai kontrak integrasi tersedia.

## Perintah verifikasi
- `php artisan test` — PHPUnit feature tests (264 test hijau per 2026-11-13).
- `php vendor/bin/pint` — format PHP (Laravel style).
- `node node_modules/vite/bin/vite.js build` — Vite production build.
- `php artisan migrate:fresh --seed --force` — reset skema lokal + seed admin (jangan di produksi).

## Log keputusan
- 2026-11-26: Salin instrumen penilaian sikap (afektif) dari kegiatan lain (`admin.events.afektif.clone`, Admin Daerah). Sub-aspek dicocokkan per nama dan butir per teks (case-insensitive) sehingga salin ulang tidak menduplikasi. Ditegaskan: angket kepuasan / tab "Self Assessment" **tidak** masuk perhitungan nilai; afektif (diisi mandiri peserta di portal) **masuk** 25% nilai akhir.
- 2026-11-20: Input jawaban pretest/posttest dari lembar kertas (arahan PDM untuk peserta yang kesulitan teknologi):
  - Keputusan pemilik: **hanya Admin Daerah (`role:admin`)**, soal kertas = bank soal sistem, input berupa jawaban A/B/C/D per soal (bukan nilai langsung).
  - Halaman `admin.events.tes-manual.index` / `.store` (`/admin/events/{event}/tes-manual/{tipe}`), `Admin\TesManualController`. Admin mengetik satu huruf per soal berurutan (`-` = kosong, spasi diabaikan); pratinjau benar/nilai langsung via Alpine.
  - `TesService::simpanJawabanManual()` menyimpan ke `jawaban_peserta` lalu memakai `grade()` yang sama dengan tes online. Validasi semua baris dulu; satu baris salah → tidak ada yang disimpan.
  - `peserta_tes_mulai` punya kolom `sumber` (`online`/`manual`) dan `diinput_oleh` (audit). Input manual mengisi `waktu_selesai`, sehingga portal menolak tes online peserta itu dan kelulusan menganggap posttest selesai walau nilainya 0.
  - Nilai online yang sudah selesai tidak bisa ditimpa dari input manual. Ranking menandai nilai "kertas"; laporan PDF memberi superscript "K", Excel memberi latar kuning (`PesertaTesMulai::tesKertasMap()`).
  - 2026-11-21: pindai foto lembar jawaban (OMR tanpa AI, keputusan pemilik "opsi B"). `App\Support\LembarJawabanLayout` = satu sumber koordinat (pt) untuk PDF lembar jawaban dan pembaca `public/aset/js/omr-lembar.js`; ubah keduanya bersamaan. Lembar punya 4 kotak hitam pojok + 32 bit kode (24 bit anggota_id, 8 bit checksum event+tipe; anggota_id 0 = lembar kosong). Foto diproses di browser (tidak diunggah), hasil hanya mengisi kolom input; admin tetap memeriksa lalu menyimpan. Jawaban ganda jadi `?` (ditolak server sampai dikoreksi); lingkaran/centang/silang terbaca hurufnya tetapi ditandai "cek". Maks 90 soal per lembar.
  - 2026-11-21: hapus input kertas (`.destroy`, hanya baris `sumber=manual`) menghapus jawaban + `peserta_tes_mulai`, nilai kolom tes kembali 0, dicatat ke log aplikasi (id saja, tanpa nama) sebagai audit. Cetak PDF `.cetak` `{lembar}=soal|jawaban`: lembar soal tanpa kunci; lembar jawaban satu halaman per peserta aktif (nama, PCM, No. urut sama dengan tabel input) + 1 lembar kosong.
- 2026-11-20: Pembesaran Penuh (Full QR) pada ID Card Baitul Arqam:
  - **QR Code Full Ukuran Maksimal**: Mengubah kontainer QR menjadi `width: 82pt; height: 82pt;` dengan gambar QR `width: 77pt; height: 77pt;` yang mengisi penuh wadah kartu (meningkat lebih dari 2.5 kali luas area dibandingkan versi ringkas 48pt sebelumnya). Lebar dan posisi horizontal QR kini sejajar presisi 100% dengan bingkai pasfoto (`width: 82pt; left: 91.875pt;`).
  - **Penyatuan Metadata Kader**: Menghapus label teks berlebih di dalam kotak QR ("PRESENSI KEHADIRAN") dan memindahkan nomor urut peserta ke baris identitas kader (`NO. 00045`), sehingga area QR murni difokuskan untuk kecepatan pemindaian barcode/kamera smartphone dari jarak pandang fleksibel panitia.
  - **Verifikasi**: 282 automated tests lulus 100% (1.354 assertions), Laravel Pint lulus 0 violations, pratinjau rendering PDF/PNG diverifikasi tajam.
- 2026-11-20: Perapian Visual & Estetika ID Card (Baitul Arqam - Massal & Portal):
  - **Penyatuan Header & Artwork Latar Alami**: Menghapus blok persegi navy kaku (`.header-band`) yang memotong sudut batik dan meninggalkan celah pohon/bangunan. Header kini menyatu secara elegan dengan ilustrasi latar belakang (kubah Edutorium dan langit biru tua), memperlihatkan lengkungan ornamen batik di sudut atas.
  - **Badge Logo Melingkar Emas**: Mengganti pelat kotak putih (`.brand-plate`) yang menyerupai stiker tempelan dengan lencana melingkar berbingkai emas (`badge-muhammadiyah.png` dan `badge-mpksdi.png`) dengan latar transparan.
  - **Harmoni Tipografi & Penghapusan Glitch Glif**: Judul "Baitul Arqam" menggunakan DejaVu Serif tebal putih, subjudul event dibersihkan dari redundansi nama acara ganda serta menggunakan titik pemisah aman (tanpa karakter asing `??`), dan rentang tanggal menggunakan tanda hubung standar anti-glitch `???`.
  - **Kapsul Melayang (Footer Pill)**: Membungkus masa berlaku kartu dalam kontainer kapsul melayang putih bersih (`.footer-pill`) di atas lengkungan batik bawah sehingga teks tidak lagi bertubrukan dengan ornamen gelap.
  - **Verifikasi**: 282 automated tests lulus 100% (1.353 assertions), Laravel Pint lulus 0 violations, dan pratinjau rendering PDF/PNG diverifikasi tajam dan presisi.
- 2026-11-20: Redesain header ID Card agar tidak generik + anti-gepeng:
  - Header jadi pita navy penuh rata kiri (logo berwarna asli di pelat putih — versi putih MPKSDI hanya siluet   - Header jadi pita navy penuh rata kiri (logo putih `logo-*-putih.png`, label lembaga, aksen emas tunggal, logo hijau Muhammadiyah tenggelam di navy; `logo-mpksdi-warna.png` = versi 240px; garis emas di tepi bawah pita, judul DejaVu Serif, subjudul huruf biasa). Badge jadi "PESERTA" polos; footer hanya teks "Berlaku …" (rentang tanggal diringkas bila sebulan).
  - DomPDF mengabaikan `box-sizing: border-box` pada `.header-band`, jadi lebarnya ditulis content-box (229.75pt + padding 2×18pt).
  - Foto ID card di-crop tengah ke 3:4 dan diperkecil (maks 480px) oleh `ImageCutoutService::coverCropDataUri()` karena DomPDF tidak mendukung `object-fit`; foto default persegi sebelumnya tergencet.
  - `idcard-satu-sisi-bg.jpg` dibuat ulang dari PNG dengan crop ke rasio kartu (1024×1479) agar latar tidak teregang.
- 2026-11-20: Perapian tata letak ID Card (massal & portal):
  - Markup satu kartu dipindah ke partial `admin/events/partials/idcard-face.blade.php` (parameter `card`, `top`, `left`); mode tunggal dan grid A4 sama-sama memakainya.
  - Nama: bagian sebelum koma pertama jadi nama utama KAPITAL satu baris (font menyusut otomatis 13–7pt), sisanya tampil sebagai baris gelar kecil. Mencegah nama 2 baris menimpa QR.
  - Blok identitas & header bertinggi tetap (tabel `vertical-align: middle`) agar posisi foto/QR stabil.
  - Subjudul membuang awalan `Baitul Arqam/Arqom`.
  - Unduhan portal kini ikut menampilkan NBM.
- 2026-11-20: Otomasi Format Cetak ID Card Massal Menjadi Grid A4 Berjajar (2x2) Siap Cetak & Potong:
  - **Latar Belakang & Kebutuhan**:
    - Sebelumnya cetak massal ID card menghasilkan 1 kartu per halaman kustom (B3 ~9.4x13.5 cm), sehingga saat dicetak menggunakan printer kantor/A4 menjadi boros kertas (1 lembar A4 hanya terisi 1 kartu kecil) atau merepotkan pengaturan printer.
  - **Penyusunan Grid 2x2 pada A4 Portrait**:
    - Mengubah dokumen cetak massal panitia (`admin.events.idcards.batch`) menjadi standar kertas **A4 Portrait** (595.28pt &times; 841.89pt).
    - Membagi peserta ke dalam lembaran per 4 kartu (`$cardList->chunk(4)`), disusun berjajar rapi 2 kolom &times; 2 baris dengan koordinat dan margin presisi.
    - Dimensi fisik tiap kartu dipertahankan 100% presisi standar fisik B3 (265.75pt &times; 383.75pt atau ~9.3 &times; 13.5 cm), pas dimasukkan ke plastik mika gantungan peserta.
    - Dilengkapi garis putus-putus panduan potong halus (`.card-cut-guide`) di sekeliling kartu agar panitia mudah memotong rapi dengan gunting/cutter.
  - **Kompatibilitas Unduhan Mandiri Peserta**:
    - Unduhan mandiri ID Card perorangan di portal peserta (`portal.idcard.download` via `KehadiranController::idCard`) tetap mempertahankan format kartu tunggal B3 (`$isSingle = true`) agar nyaman dibuka dan disimpan di ponsel/laptop kader.
  - **Verifikasi**:
    - 278 automated tests suite (1.343 assertions) lulus 100%, Laravel Pint lulus bersih, dan Vite production build sukses.
- 2026-11-19: Presensi Manual Checklist Sesi Berdekatan & Akses Terpadu Poin Keaktifan Forum Peserta:
  - **Presensi Manual Checklist (Solusi Sesi Berdekatan Tanpa Scan Barcode)**:
    - Membangun antarmuka presensi manual tabel checklist (`/admin/events/{event}/sesi/{sesi}/presensi`) sehingga panitia tidak perlu meminta peserta memindai QR code dua kali berturut-turut pada sesi yang jadwalnya berdekatan di ruangan yang sama.
    - Menyediakan tombol aksi cepat:
      1. `⚡ Tandai Semua Hadir`: Menandai seluruh peserta aktif yang belum hadir menjadi hadir secara instan (1-klik).
      2. `⚡ Salin Kehadiran dari Sesi Sebelumnya`: Menyalin daftar presensi hadir dari sesi sebelumnya yang dipilih panitia ke sesi saat ini.
      3. Tombol toggle individual `Centang Hadir` dan `Batal Hadir` per peserta dengan rekalkulasi nilai otomatis.
      4. Pencarian instan nama peserta / NBM dan filter status (Semua / Hadir / Belum Hadir) berbasis Alpine.js.
      5. Menambahkan tombol akses cepat `[Checklist]` pada setiap baris sesi agenda di dashboard event dan tombol beralih cepat di halaman scanner QR (`scan.blade.php`).
  - **Akses Poin Keaktifan & Penilaian Psikomotor (Tanya, Jawab, Adzan, Kultum/Presentasi, Juara Outbound)**:
    - Menjawab kebingungan penempatan nilai keaktifan dengan menambahkan kartu rujukan `Poin Keaktifan & Partisipasi Forum` langsung di dalam **Tab "CBT & Asesmen" &rarr; Sub-tab "Psikomotor"** di samping menu utama di **Tab "Keaktifan & Outbound" &rarr; Sub-tab "Poin Keaktifan Forum"**.
    - Memperkaya halaman pencatatan keaktifan (`admin.events.keaktifan`):
      1. Dropdown pemilihan Sesi Agenda opsional.
      2. Pelabelan aksi yang jelas: *Tanya (Forum)*, *Jawab (Forum)*, *Kultum / Presentasi*, *Imam Shalat*, *Adzan*, dan *Juara Outbound / Games*.
      3. Tabel log riwayat 50 pencatatan keaktifan terbaru lengkap dengan tombol hapus (rollback) jika panitia salah input.
  - **Verifikasi**: Automated test `AbsensiTest` (9 tests passed), `OutboundPdfAndKeaktifanPortalTest` passed, Laravel Pint 0 violations, Vite production build sukses, dan dev server aktif di port 8000.
- 2026-11-13: Integrasi Modul Tab "Feedback & Evaluasi" pada Navigasi Horizontal Event Dashboard (`show.blade.php`):
  - **Penambahan Tab Feedback Tingkat 1 (Workspace Module Nav)**: Menempatkan tab `Feedback` di samping `RTL` sebelum `Laporan` pada bilah navigasi horizontal *sticky* (`admin.events.show`). Dilengkapi indikator badge jumlah responden yang telah mengisi evaluasi.
  - **Arsitektur 3 Sub-Navigasi (Level 2 Sub-Tabs)**:
    1. *Sub-Tab 1: Rekap & Statistik Skor Likert*: Menampilkan 4 kartu bento metrik (Tingkat Partisipasi Responden, Indeks Kepuasan Rata-rata Likert 1–5 ⭐ beserta predikat mutu, Total Butir Instrumen, dan Ulasan Tertulis). Dilengkapi rincian rata-rata skor per butir dan bar visual progres warna (emerald/sky/amber) per aspek/kategori.
    2. *Sub-Tab 2: Masukan & Saran Kader*: Menampilkan kutipan testimoni kualitatif, kritik, dan saran konstruktif peserta dengan avatar inisial, nama kader, asal cabang/ranting, dan waktu pengiriman.
    3. *Sub-Tab 3: Kelola Instrumen Angket*: Form inline penambahan butir evaluasi Likert 1–5 per kategori (A - Materi & Kurikulum, B - Narasumber & Fasilitator, C - Pelayanan & Sarana, D - Aspek Tambahan), daftar butir aktif, dan tombol hapus.
  - **Otomasi Templat Standar MPKSDI**: Menyediakan tombol *"Isi Butir Standar MPKSDI"* (`POST /admin/events/{event}/angket/seed-defaults`) dan mengintegrasikan seeder angket standar ke command `ba:siapkan-materi` dan `DemoDataSeeder.php`.
  - **Tautan Cepat Pratinjau Peserta**: Memudahkan admin membuka dan menguji form pengisian angket dari sudut pandang peserta (`portal.angket.index`).
  - **Verifikasi**: 266 tests PHPUnit (1.276 assertions) lulus 100%, Laravel Pint 0 violations, Vite build produksi sukses (4.23s).
- 2026-11-13: Perbaikan Proporsi Pasfoto Kader (Anti-Gepeng / Distorsi Rasio Wajah 3:4):
  - **Akar Masalah "Masih Gepeng" Teridentifikasi**:
    1. *Sumber File Gambar Seeder (`DemoDataSeeder.php`)*: Pemotongan kisi (*grid crop*) foto demo kader dari `foto1.png` dan `foto2.png` awalnya mengambil dimensi persegi panjang mentah sel (lebar ~298px, tinggi ~507px) lalu langsung di-resample ke kanvas 300x400 px (`imagecopyresampled`). Karena tinggi ditekan dari 507px ke 400px (~21.1% pemampatan vertikal) sementara lebar tetap di ~300px, seluruh foto wajah kader tersimpan dalam kondisi pipih/gepeng vertikal dari file sumbernya (`kader-01.jpg` s.d. `kader-20.jpg`).
    2. *Dimensi Container Thumbnail UI*: Pada `admin/events/show.blade.php`, container avatar berukuran `w-10 h-12` (40x48px = rasio 5:6 atau 0.833), bukan rasio resmi pasfoto 3:4 (0.75). Beberapa tampilan lain (`enroll.blade.php`, `anggota/index.blade.php`, `dashboard.blade.php`) juga menggunakan `w-9 h-11` atau `w-8 h-10` tanpa `aspect-[3/4]`.
  - **Solusi & Penanganan Komprehensif**:
    1. *Formulasi Crop 3:4 Presisi di `DemoDataSeeder.php`*: Menerapkan perhitungan matematis rasio 3:4 seragam: `$cropH = (int) round($cropW * 4 / 3)` dengan offset atas `$srcY = $rRange[0] + 8` yang mencakup ruang kepala/peci/hijab, leher, dan bahu tanpa peregangan maupun pemampatan non-uniform.
    2. *Regenerasi 20 Pasfoto Demo*: Meregenerasi dan memperbarui 20 file `kader-01.jpg` s.d. `kader-20.jpg` pada `database/seeders/assets/kader/` dan `storage/app/private/anggota/foto/`.
    3. *Penyeragaman Rasio Kontainer 3:4 di Seluruh UI*: Mengubah seluruh wadah pasfoto thumbnail kader di `admin/events/show.blade.php` (`w-10 h-[53px] aspect-[3/4]`), `admin/events/enroll.blade.php` (`w-9 h-12 aspect-[3/4]`), `admin/anggota/index.blade.php` (`w-9 h-12 aspect-[3/4]`), `admin/dashboard.blade.php` (`aspect-[3/4]`), dan `portal/dashboard.blade.php` dengan `object-cover object-top`.
  - **Verifikasi**: 264 tests PHPUnit (1.267 assertions) lulus 100%, seluruh pasfoto kader tampil tegak, proporsional, dan alami.
- 2026-11-13: Studio Pasfoto & Penyesuaian ID Card (Tab Workbench Reposisi & Centering Anti-Gepeng) untuk Admin:
  - **Studio Workbench dalam Satu Halaman (`admin.anggota.show`)**: Menggantikan kartu pratinjau pasfoto statis lama dengan studio workbench terpadu yang memadukan edit pasfoto, reposisi (drag/pan, zoom 0.2x-3.5x, rotasi 90°, auto-center), dan centering ID card.
  - **Sistem Tab Navigasi Interaktif**:
    - **Tab 1 (Edit & Reposisi Pasfoto 3:4)**: Menyediakan pilihan unggah foto baru dari komputer/HP atau gunakan foto tersimpan. Kanvas interaktif (viewport 240x320 px, output 600x800 px) dengan panduan rasio resmi 3:4 wajah (tinggi mata, puncak kepala/peci, garis bahu), slider zoom, auto-center, dan reset transform.
    - **Tab 2 (Centering & Pratinjau ID Card Anti-Gepeng)**: Menghadirkan mockup realistis kartu tanda peserta fisik Baitul Arqam (navy & gold, logo PDM & MPKSDI, frame foto 3:4, nama kader, NBM, barcode/QR). Dilengkapi jaminan rasio 3:4 terkunci (100% bebas gepeng/distorsi), tombol kontrol cepat centering vertikal (Pusat Tengah, Naikkan Foto 8px, Turunkan Foto 8px), dan pemilih mode ID Card (Original berbingkai vs Cutout transparan).
  - **Sinkronisasi Dua Arah Real-time**: Perubahan posisi, zoom, atau ketinggian wajah di Tab 1 maupun Tab 2 saling menyinkronkan kanvas secara instan tanpa perlu reload.
  - **Backend Endpoint `POST /admin/anggota/{anggota}/foto`**: Mendukung multipart upload maupun Data URI Base64 dari kanvas, validasi format gambar, penyimpanan JPEG 3:4 terstandar, integrasi cutout klien/server GD otomatis, pembersihan file lama, dan otorisasi Admin Daerah & Admin Cabang.
  - **Pencegahan Distorsi DomPDF**: Memperketat styling `.photo img` pada `admin/events/idcard-batch-pdf.blade.php` dan `.photo-img-original` pada `portal/idcard-pdf.blade.php` (`max-width: 100%; max-height: 100%; object-fit: cover;`) agar pencetakan ID Card PDF massal bebas dari efek gepeng/pipih.
  - **Verifikasi**: 264 tests PHPUnit (1.267 assertions) lulus 100%, Laravel Pint 0 violations, Vite build produksi sukses (3.31s).
- 2026-11-07: Restyle Visual Widget Chat SiKader Bergaya Prompt Composer 21st.dev (Mosaic Prompt Template):
  - **Prompt Composer Kaca Ringan**: Mengubah kolom chat bawah menjadi kotak composer besar membulat bergaya kaca frosted (`glass-composer`, `backdrop-blur-md`, border tipis semi-transparan, bayangan lembut, ring fokus warna aksen amber `--accent`). Textarea auto-grow hingga 5 baris (`max-h-36`).
  - **Toolbar Aksi Terpadu di Dalam Composer**: Memindahkan baris aksi (lampirkan file, tempel daftar nama, unggah spreadsheet Excel/CSV) ke sisi kiri bawah di dalam kotak composer, dan menempatkan tombol kirim pesawat kertas (*paper plane*) di pojok kanan bawah composer.
  - **Quick Action Pills Melayang di Atas Composer**: Aksi cepat lama dipindahkan menjadi deretan pills chip kaca horizontal di atas kotak composer (`overflow-x-auto` rapi tanpa scrollbar kasar), mudah diakses dalam sekali sentuh di lebar 380px–480px.
  - **Build Log Proses RAG Bertahap & Collapsible**: Menyajikan status proses RAG langkah-demi-langkah berurutan (`✓ Selesai`, `● Berjalan`, `○ Antre`) di dalam bubble respon maupun saat status pencarian berlangsung (`showRetrievalStatus`), yang otomatis menciut menjadi akordeon *"Lihat proses (3 langkah) ▾"* setelah selesai.
  - **Sliding Preview Panel Dokumen Sumber**: Menggantikan popover kecil dengan panel laci geser mulus dari kanan (*Slide-over Preview Drawer*) yang menampilkan nomor sitasi `[1]`, status *RAG Terverifikasi*, judul sumber tabel database, dan blok kutipan teks terverifikasi ter-highlight.
  - **Kartu Hasil & Konfirmasi Kaca (`glass-card`)**: Kartu kandidat rekomendasi dan kartu hasil pencocokan mengadopsi latar semi-transparan, border halus, sudut membulat, hover naik 2px (`hover:-translate-y-0.5`), menampilkan NIK tersamar (`331392****`), badge skor/riwayat, serta tombol *Daftarkan* individual dan tombol *Enroll Terpilih* massal, didahului kartu konfirmasi pra-pendaftaran.
  - **Variabel Warna `--accent` & Aksesibilitas WCAG**: Menetapkan variabel CSS `--accent` (amber `#f8bb00`) untuk aksi utama, fokus ring, dan sitasi aktif berpadu dengan teal MPKSDI (`#005c8a`). Menghormati `prefers-reduced-motion` (animasi langkah dialihkan ke fade instan), `aria-live="polite"`, dan kontras teks ≥ 4.5:1. Seluruh teks antarmuka Bahasa Indonesia terpusat di `resources/js/sikader/constants.js`.
  - **Verifikasi**: 252 tests PHPUnit (1.197 assertions) lulus 100%, Laravel Pint lulus bersih, Vite production build sukses.
- 2026-11-07: Perbaikan Visual Terarah Empty State & Tampilan Awal Widget Chat SiKader:
  - **Quick Action Cards Rapi & Tidak Terpotong**: Menghapus ikon chevron `>`, menerapkan `line-clamp-2 leading-snug`, tinggi kartu seragam (`min-h-[54px]`), dan tooltip `:title="chip.label"` dengan label resmi: *"Rekomendasi seimbang"*, *"Prioritaskan belum aktif"*, *"Prioritaskan kader AUM"*, *"Tempel daftar nama"*, *"Unggah Excel / CSV"*.
  - **Ikon Tombol Kirim Standar**: Mengganti ikon segitiga dengan ikon pesawat kertas (*paper plane* Lucide). Tombol dipudarkan saat teks kosong dan solid teal saat siap kirim, dilengkapi `aria-label="Kirim pesan"` dan `title="Kirim pesan"`.
  - **Header Lega & Dropdown Pengaturan**: Subjudul dipadatkan menjadi satu baris (*"Asisten Perkaderan MPKSDI"*). Tombol ukuran font, tema terang/gelap, dan reset percakapan dikelompokkan ke dalam menu dropdown `⋯` yang rapi dan aksesibel (`aria-label="Menu pengaturan"`). Bar progres kuota dan badge AKTIF tetap dipertahankan.
  - **Sapaan Ringkas Dinamis**: Pesan sapaan awal dipangkas menjadi 2 baris tanpa emoji (*"Assalamu'alaikum, Admin. Siap membantu kegiatan **{namaKegiatan}**. Mau mulai dari mana?"*) sehingga kartu aksi terlihat utuh tanpa scroll.
  - **Konsistensi Ikon Garis 20px**: Menghapus seluruh ikon emoji berwarna, digantikan ikon garis Lucide seragam (20px, stroke-width 2, aksen teal) di dalam kotak membulat tipis seragam.
  - **Kontras Teks Bantu Terjamin**: Ukuran teks hint dinaikkan ke 12px (`text-xs`), dan warna placeholder serta hint disesuaikan agar rasio kontras mencapai ≥ 4.5:1 di mode terang (`text-slate-600` / `placeholder:text-slate-500`) maupun mode gelap (`dark:text-slate-300` / `dark:placeholder:text-slate-400`).
  - **Verifikasi**: 252 tests PHPUnit (1.197 assertions) lulus 100%, Laravel Pint 0 violations, Vite production build sukses.
- 2026-11-07: Penerapan Foto Profil Default Resmi Berdasarkan Gender (Putra Berpeci & Putri Berhijab):
  - **Aset Default Resmi**: Menyimpan foto profil default resmi ke `public/aset/default-profile-pria.jpg` (kader putra berpeci hitam dan koko putih) dan `public/aset/default-profile-wanita.jpg` (kader putri berhijab rapi).
  - **Routing Terpusat di `FotoController`**: `FotoController::show` mendeteksi `$anggota->jenis_kelamin === 'P'` untuk menyajikan foto wanita dan default foto pria dalam format JPEG (`Content-Type: image/jpeg`), menggantikan kotak inisial SVG lama secara universal di seluruh sistem.
  - **Dukungan ID Card**: `ImageCutoutService::resolveOriginalForAnggota()` mengarahkan fallback foto ID card ke aset profil default gender tersebut.
  - **Integrasi Antarmuka**: Seluruh kartu enroll kader (`admin/events/enroll.blade.php`), topbar/sidebar avatar (`layouts/app.blade.php`), dan daftar kartu rekomendasi SiKader AI (`smart-enroll-modal.blade.php`) kini menampilkan foto profil default gender secara konsisten dengan mekanisme cache-busting `?v=2`.
  - **Verifikasi**: 249 tests PHPUnit (1.174 assertions) lulus 100%, Laravel Pint lulus bersih, Vite production build sukses.
- 2026-11-07: Penambahan Fitur Rekomendasi Kader Berdasarkan Riwayat BA Terbanyak & Profil Keagamaan:
  - **Penyebab Awal Jawaban "Tidak Bisa"**: Sebelumnya instruksi prompt berasumsi bahwa pangkalan data SIMKader tidak mencatat riwayat keikutsertaan BA secara terinci atau kebiasaan sholat di masjid. Padahal pangkalan data memiliki relasi `event_peserta` (bisa menghitung frekuensi keikutsertaan BA per kader) serta kolom formulir profil keagamaan mandiri seperti `aktivitas_sholat_masjid`, `hafalan_quran`, dan `keaktifan_takmir`.
  - **Dukungan Pencarian Partisipasi BA Terbanyak**: Menambahkan opsi `sort: paling_sering_ba` pada `recommendCandidates()` yang mengurutkan kader aktif berdasarkan `withCount('eventPeserta')->orderByDesc('event_peserta_count')`. Rekomendasi menyajikan kader senior/berpengalaman yang telah mengikuti BA (hingga 3x).
  - **Dukungan Pencarian Profil Keagamaan**: Mendukung pencarian kriteria ibadah riil dari formulir pendaftaran anggota (`aktivitas_sholat_masjid` untuk kader yang selalu/sering sholat 5 waktu di masjid, `hafalan_quran`, dan `keaktifan_takmir`).
  - **Badge Transparan di Kartu Rekomendasi**: Menambahkan informasi riil pada setiap baris calon peserta yang direkomendasikan di modal SiKader (menampilkan badge frekuensi `3x BA` dan catatan sholat masjid).
  - **Verifikasi**: 19 automated tests PHPUnit lulus 100%, Pint lulus bersih, Vite build berhasil.
- 2026-11-07: Penyempurnaan Bahasa Hangat (Welcome), Bolding Informasi Kunci, Grounding Data & Perapian Input Bar SiKader:
  - **Penghilangan Border Kotak Input Bar**: Membersihkan CSS textarea pada `resources/css/app.css` dan inline styling modal `smart-enroll-modal.blade.php` (`border: 0 !important`, `border-none`, `ring-0`, `shadow-none`) sehingga textarea berada mulus di dalam kapsul chat tanpa garis kotak persegi internal.
  - **Bahasa Welcome & Tidak Kaku**: Mengubah nada bahasa asisten AI menjadi ramah, santun, bersahabat, islami, dan komunikatif layaknya rekan sejawat perkaderan dengan format berbutir (*bullet points*) dan emoji relevan.
  - **Cetak Tebal (Bold) Informasi Kunci**: Menegakkan instruksi format markdown `**teks**` pada LLM dan tag `<strong>` pada sapaan awal modal untuk setiap nama asisten (**SiKader**), nama kegiatan (**BA DOSEN DEMO**), lokasi (**Rumah Revolusi WCS Mojogedang**), tanggal/waktu, kuota/kursi, nama peserta/kader, cabang PCM/AUM, dan 4 pilar evaluasi (**Kognitif**, **Psikomotor Praktik Ibadah sesuai HPT**, **Afektif**, **RTL**).
  - **Grounding & Batas Data Ibadah Pribadi**: Menghubungkan permintaan kriteria ibadah pribadi (seperti "sholat 5 waktu di masjid") secara bijak bahwa pangkalan data SIMKader Karanganyar mencatat profil administratif kader, dan mengaitkannya dengan 4 Pilar Evaluasi Baitul Arqam (Praktik Ibadah sesuai HPT 35%) serta menawarkan rekomendasi kader aktif siap bina tanpa memunculkan data halusinasi.
  - **Disambiguasi Kueri Peserta**: Memastikan kueri seperti *"list pesertanya coba"* atau *"siapa saja pesertanya"* menampilkan daftar nama peserta resmi terdaftar dan sisa kuota kursi yang kosong, tanpa pernah keliru menganggapnya sebagai nama orang.
  - **Verifikasi**: Automated tests PHPUnit lulus 100%, Laravel Pint lulus bersih, Vite build produksi sukses.
- 2026-11-07: Implementasi Desain Sitasi RAG & Sumber Terverifikasi (ala 21st.dev / Perplexity AI):
  - **Inline Citation Chips**: Teks balasan SiKader menyertakan lencana nomor sitasi interaktif `[1]`, `[2]`, `[3]`, `[4]` yang grounded pada entitas database riil.
  - **Floating Preview Tooltip**: Meng-hover atau mengetuk badge sitasi menampilkan cuplikan data asli dan identitas tabel sumber (`Database: events`, `event_sesi`, `SPM`, atau `master_anggota`).
  - **Collapsible Sources Drawer**: Di bawah balon chat, terdapat akordeon lipat *"Sumber Data RAG Terverifikasi"* yang menampilkan grid kartu rujukan lengkap dengan fitur cross-highlighting antara chip di teks dan kartu sumber di bawah.
  - **Verifikasi**: 245 automated tests (1.143 assertions) lulus 100%, Pint 0 violations, Vite build bersih.
- 2026-11-07: Perluasan Wawasan dan Kapabilitas Edukatif AI SiKader (Holistic RAG & Intent Disambiguation):
  - **Pencegahan False Positive Pencarian Nama**: Memperbaiki `classifyIntent()` agar pertanyaan umum atau perintah penjelasan seperti *"jelaskan soal ba ini"*, *"apa itu baitul arqam"*, *"bagaimana penilaiannya"*, dan *"apa saja materinya"* tidak pernah lagi keliru dideteksi sebagai pencarian nama anggota. Kata kunci penjelasan/edukasi secara tegas diarahkan ke kategori `info`.
  - **Pengetahuan RAG Mendalam**: Memperluas `retrieveEventKnowledge()` dengan data deskripsi kegiatan, kurikulum/daftar sesi materi dari relasi `sesi` (`EventSesi`), bobot komposisi 4 pilar kelulusan (Kognitif, Praktik Ibadah Psikomotorik, Sikap Afektif, dan RTL), profil fasilitator, serta pangkalan data PCM/AUM.
  - **Jawaban Edukatif & Holistik**: Menambahkan handler naratif mendalam untuk pertanyaan seputar Baitul Arqam Muhammadiyah, kurikulum perkaderan, sistem evaluasi kelulusan 4 pilar, serta panduan alur pendaftaran Smart Enroll.
  - **Optimasi Integrasi Reasoning Geraikita AI**: Menyesuaikan parameter pemanggilan model penalaran (`deepseek-v4-flash`) dengan menaikkan `max_tokens` ke 2048 dan `timeout` ke 18 detik sehingga kuota token tidak habis sebelum penalaran selesai. Mendukung pembersihan tag `<think>...</think>` dan pemformatan markdown (bold/italic/bullet) otomatis menjadi tag HTML terstruktur yang ramah antarmuka.
  - **Ketahanan Fallback RAG Lokal**: Jika AI eksternal sedang offline atau lambat, SiKader lokal deterministik merespon instan dengan kualitas jawaban RAG terstruktur yang sama lengkapnya.
  - **Verifikasi**: Seluruh 243 automated tests PHPUnit (1.126 assertions) lulus 100%, Laravel Pint lulus bersih.
- 2026-11-06: Penyempurnaan & Perapian UI Modal SiKader (`smart-enroll-modal.blade.php`):
  - Memperbaiki backdrop gelap (`bg-slate-950/80 backdrop-blur-sm`) sehingga elemen halaman di latar belakang tidak lagi tembus pandang atau bocor pada tepi bawah viewport.
  - Memperbaiki tata letak header: tombol close dan reset memiliki target klik lebih lega (`w-8 h-8 rounded-full`), margin aman, dan tidak terhalang ekstensi browser.
  - Mengintegrasikan strip status event di bawah header (`Konteks kegiatan`) dengan badge dot emerald dan metrik kuota live.
  - Merapikan balon chat dan avatar dengan lencana `Asisten`, padding proporsional, serta sapaan alami.
  - Menyatukan bilah masukan (dock footer) menjadi **kapsul chat modern** terpadu (`flex items-end gap-1.5 bg-slate-50 border rounded-2xl p-1.5 focus-within:ring-2`), menggabungkan tombol klip lampiran, textarea borderless, dan tombol kirim secara harmonis.
- 2026-11-06: Arsitektur RAG (Retrieval-Augmented Generation) & Dialog Alami SiKader:
  - Mengimplementasikan Retrieval Engine riil (`retrieveEventKnowledge`): mengambil data event (kuota, sisa kursi, terdaftar, jadwal, lokasi, tim fasilitator) serta statistik master kader MPKSDI (kader aktif, belum pernah BA, rasio gender, persebaran top PCM dan AUM).
  - Klasifikasi Intensi Cerdas (`classifyIntent`): membedakan pesan `chat` (sapaan santai), `info` (tanya kuota, cabang, jadwal, fasilitator), `search` (cek status satu kader), dan `recommendation` (permintaan kandidat).
  - Dialog Alami: Sapaan santai seperti "hai", "halo", "assalamu'alaikum" kini dibalas hangat, ramah, dan kontekstual dengan overview status kuota terkini tanpa memunculkan kartu 20 kandidat membabi-buta (`needs_candidate_card: false`). Kartu rekomendasi hanya dihadirkan saat admin benar-benar memintanya.
  - Injeksi RAG ke LLM & Local Fallback: Konteks RAG diinjeksikan langsung ke prompt Geraikita AI dengan timeout 6s, didukung fallback lokal deterministik yang menjawab pertanyaan kuota, cabang, dan fasilitator secara akurat.
- 2026-11-06: Asisten untuk enroll massal dinamai **SiKader** dengan identitas “Asisten Perkaderan MPKSDI”; modal membuka satu sapaan saja dan menyediakan tindakan cepat dalam grid dua kolom di layar ponsel.
- 2026-11-06: Tampilan SiKader dipadatkan untuk penggunaan ponsel: konteks kegiatan tampil satu baris, sapaan dipersingkat, tindakan cepat memakai grid dua kolom dengan AUM satu baris penuh, dan dock input tanpa teks bantuan tambahan.
- 2026-11-06: Panel SiKader memakai inset viewport aman (`100dvh - 1rem`) dan panel membulat, sehingga header serta dock input tidak terpotong pada layar kecil.
- 2026-11-06: Scroll SiKader diisolasi dengan `overscroll-contain`; saat modal terbuka, scroll dokumen dikunci dan dipulihkan saat modal ditutup agar roda mouse/geser tidak merembet ke halaman belakang.
- 2026-11-06: Penerapan Maskot & Logo Resmi SiKader (`public/aset/sikader.jpg`):
  - Memasang logo robot berbentuk 'S' biru maskot SiKader pada header modal chatbot, avatar balon obrolan asisten, balon status berpikir/mengetik (*thinking bubble*), balon hasil pencocokan nama, balon kandidat rekomendasi, serta Quick Action banner dan tombol pemicu di halaman enroll event (`enroll.blade.php`).
- 2026-11-06: Transformasi Smart Enroll Menjadi Asisten Chatbot AI Interaktif (`smart-enroll-modal.blade.php`):
  - Mengubah antarmuka modal Smart Enroll menjadi **Chatbot AI Interaktif** yang komunikatif layaknya asisten virtual pribadi:
    * **Antarmuka Chatbot Bento Modern**:
      - Modal floating chat drawer (`h-[100dvh] sm:h-[680px] sm:max-w-2xl`) dengan avatar cerulean-emas, indikator status online berdenyut hijau (*pulsing dot*), bubble obrolan dua arah, status berpikir AI (*thinking dots*), dan auto-scroll reaktif.
    * **Quick Action Chips Cepat**:
      - Sapaan hangat awal dilengkapi 5 pilihan aksi cepat: *Tempel Daftar Nama WA*, *Unggah File Excel/CSV*, *Rekomendasi Seimbang 50:50*, *Prioritaskan Belum Pernah BA*, dan *Prioritas Kader AUM*.
    * **Mesin Pemrosesan Cerdas (*Hybrid Parser*)**:
      - Deteksi otomatis jenis pesan: input daftar nama (multi-line atau berangka/bullet) langsung diarahkan ke *Bulk Matcher* in-memory berjenjang (Exact + Fuzzy >88%).
      - Pesan instruksi bebas diproses oleh Geraikita AI API (`https://ai.geraikita.com/v1`) dengan *graceful local fallback* (`extractLocalCriteria`) dan pencarian nama parsial tanpa pernah menimbulkan error 402/500 kepada pengguna.
    * **Kartu Hasil & Eksekusi Cepat di Dalam Obrolan**:
      - Kartu hasil analisis memuat ubin metrik 3 status (*Siap Enroll*, *Sudah Terdaftar*, *Belum Punya Akun*), daftar centang kandidat, tombol 1-klik *Salin Undangan WA*, dan tombol eksekusi massal *Enroll Terpilih* dengan proteksi `DB::transaction`.
    * **Restorasi Routing & Controller**:
      - Menghidupkan kembali rute dan endpoint `smart-enroll.recommend` dan `smart-enroll.ai-prompt` dengan validasi server-side dan penanganan error yang andal.
    * **Verifikasi Lengkap**:
      - Seluruh 236 feature & unit tests suite (1.062 assertions) lulus 100%.
      - Vite production build bersih (3.95 detik).
      - Laravel Pint 0 style violations.
- 2026-11-06: Penyederhanaan Modul: Fokus 100% pada Pencocokan Nama Massal (Bulk Matcher Excel / WhatsApp):
  - Menghapus tab/asisten kriteria sesuai permintaan pengguna agar antarmuka lebih simpel, cepat, dan fokus:
    * Modal di [`resources/views/admin/events/partials/smart-enroll-modal.blade.php`](file:///D:/website/04_Organisasi%20%26%20Komunitas/Portal-Muhammadiyah/MPKSDI%20KRA/resources/views/admin/events/partials/smart-enroll-modal.blade.php) disederhanakan tanpa tab switcher: langsung menyajikan 2 opsi input (Tempel Teks WhatsApp & Unggah File Excel/CSV).
    * Hasil langsung menampilkan 3 status: *Siap Enroll*, *Sudah Terdaftar*, dan *Belum Punya Akun* (+ Tombol Salin Pesan Undangan WhatsApp).
    * Membersihkan endpoint controller dan rute asisten kriteria yang tidak lagi digunakan.
    * 233 feature & unit tests (1.048 assertions) lulus 100%, Vite build bersih, Pint 0 violations.
- 2026-11-06: Implementasi Lengkap Smart Enroll & Bulk Name Matcher (Hybrid Engine + Geraikita AI):
  - Memecahkan kendala pendaftaran peserta massal (*enroll*) yang sebelumnya lambat saat panitia menerima puluhan/ratusan nama delegasi dari Excel/WhatsApp:
    * **Modul Pencocokan Nama Massal (*Bulk Matcher*)**:
      - Mendukung upload file spreadsheet (`.xlsx`, `.xls`, `.csv`) maupun tempel teks (*paste text*) dari grup WhatsApp/Word.
      - Normalisasi cerdas otomatis membersihkan nomor urut, karakter pemisah, serta gelar awalan/akhiran (*Drs., Dra., dr., Ir., H., Hj., Ust., S.Pd, M.Pd, S.T, M.T, S.Ag, M.Ag, dll.*).
      - Pencocokan in-memory berjenjang (Exact Match dan Fuzzy Similarity >88%).
      - Pengelompokan hasil ke 3 tab status: *Siap Enroll*, *Sudah Terdaftar di Event*, dan *Belum Punya Akun*.
      - Generator WhatsApp 1-klik untuk nama yang belum punya akun, memuat draf pesan ramah berisikan daftar nama bersangkutan dan tautan registrasi mandiri (`route('anggota.register')`).
    * **Asisten Kriteria Cerdas Lokal & Integrasi Geraikita AI**:
      - Engine kriteria lokal: filter target kuota, keseimbangan gender seimbang 50:50, cabang PCM, instansi AUM, dan prioritas kader yang belum pernah mengikuti Baitul Arqam.
      - Integrasi Geraikita AI (`https://ai.geraikita.com/v1/chat/completions`) untuk memproses instruksi prompt bahasa alami.
      - Mekanisme *Graceful Fallback*: jika kuota GeraiPoints habis (`402`) atau offline, sistem otomatis mengalihkan ke asisten lokal dengan aman tanpa crash.
    * **Modal Bento Interaktif (`partials/smart-enroll-modal.blade.php`)**:
      - Komponen modal berbasis Alpine.js dengan transisi halus, tab interaktif, ubin metrik hasil analisis, centang massal, dan eksekusi transaksi database aman (`DB::transaction`).
    * **Verifikasi Komprehensif**:
      - 13 test otomatis baru (`SmartEnrollServiceTest`, `SmartEnrollRecommendationTest`, `SmartEnrollFeatureTest`) lulus 100%.
      - Seluruh 238 feature & unit tests suite (1.063 assertions) lulus 100%, Vite asset build bersih, Pint 0 violations.
- 2026-11-06: ID Card Baitul Arqam diubah menjadi satu muka portrait untuk cetak dan unduhan peserta:
  - Kedua jalur memakai template PDF tunggal `admin/events/idcard-batch-pdf.blade.php`, dengan latar biru-emas resmi `aset/idcard/idcard-satu-sisi-bg.png`.
  - Isi kartu dipadatkan menjadi identitas institusi, judul Baitul Arqam, acara/angkatan, foto peserta, nama, PCM, nomor peserta, QR presensi, dan masa berlaku.
  - Foto kartu selalu memakai unggahan asli peserta (`resolveOriginalForAnggota`); penghapusan latar dan cache cutout tidak lagi dijalankan untuk ID card.
- 2026-11-06: Perbaikan & Kustomisasi Paginasi Bento Bahasa Indonesia (`vendor/pagination/tailwind.blade.php`, `lang/id/pagination.php`):
  - Memperbaiki bug tampilan teks mentah `pagination.previous` dan `pagination.next` yang muncul pada tombol gelap default framework:
    * **Penyediaan Lokalisasi ID**: Menambahkan berkas `lang/id/pagination.php`, `lang/id/auth.php`, dan `lang/id/passwords.php` sehingga pemanggilan `__('pagination.previous')` dan `__('pagination.next')` diterjemahkan secara sempurna menjadi *Sebelumnya* dan *Berikutnya*.
    * **Pembersihan Kelas Dark Mode Liar**: Menghapus kelas `dark:bg-gray-800` dan `dark:text-gray-300` pada template bawaan Laravel yang menyebabkan tombol berubah menjadi kotak hitam legam saat perangkat klien mengaktifkan prefers-color-scheme dark.
    * **Desain Paginasi Bento Modern**:
      - Mobile: Tampilan ringkas dengan tombol *Sebelumnya* (panah kiri), indikator halaman aktif tengah (*Hal X / Y*), dan tombol *Berikutnya* (panah kanan) dengan palet putih-slate bersih.
      - Tablet/Desktop: Baris ringkasan data ("Menampilkan X sampai Y dari Z data") dan grup pil nomor halaman dengan highlight biru Muhammadiyah (`#005c8a`) pada halaman aktif.
    * **Verifikasi Otomatis**: Menambahkan assertion `assertSee('Sebelumnya')`, `assertSee('Berikutnya')`, `assertDontSee('pagination.previous')`, dan `assertDontSee('pagination.next')` pada `AdminEventFlowTest`. Seluruh 12 test (37 assertions) lulus 100%, Pint 0 violations, Vite build bersih.
- 2026-11-06: Redesain Bento & Deep-Clean Halaman Enroll Peserta Kegiatan (`admin/events/enroll.blade.php`):
  - Mengatasi penumpukan vertikal ekstrem pada form pencarian & filter yang sebelumnya memakan >70% layar mobile:
    * **Bento Header & Micro-Metric Strip**: Judul dan informasi halaman diintegrasikan dengan strip 3 metrik mikro ringkas (`Kandidat`, `Sisa Kuota`, dan `Terpilih` secara live via Alpine.js).
    * **Konsol Filter Bento Terstruktur**: Mengubah dropdown yang sebelumnya berjejer tak beraturan menjadi Bento Grid responsif 2 kolom di mobile (`grid-cols-2`), 3 kolom di tablet (`sm:grid-cols-3`), dan 5 kolom di desktop (`lg:grid-cols-5`). Setiap select diformat ringkas dengan `truncate` dan state hover/focus elegan.
    * **Chips Filter Aktif (*Active Filter Pills*)**: Menampilkan badge pil interaktif untuk filter pencarian, pilar, AUM, ORTOM, PCM, atau urutan yang sedang aktif dengan tombol hapus silang individual (&times;) serta tombol *Reset Semua*.
    * **Grid Bento Kartu Kandidat**: Menata daftar kandidat dalam grid 2 kolom di tablet/desktop (`grid-cols-1 sm:grid-cols-2 gap-2.5`) sehingga memotong tinggi scroll hingga 50%, dengan avatar ringkas, badge AUM/ORTOM/PCM yang tertata rapi, dan feedback visual saat kandidat dipilih (`ring-1 ring-[#005c8a]/30 bg-sky-50/60`).
    * **Mobile Floating Action Dock**: Menyediakan dock bawah melayang (`fixed bottom-0 inset-x-0 z-30`) otomatis muncul saat ada kandidat yang dipilih, menampilkan ringkasan jumlah terpilih dan tombol aksi langsung `Enroll Terpilih` tanpa harus menggulir ke dasar halaman.
  - Seluruh 225 feature tests PHPUnit (1.003 assertions) lulus 100%, Vite asset build bersih, Pint 0 violations.
- 2026-11-06: Optimasi Bento Skalabel untuk Penugasan Banyak Fasilitator (`admin/events/show.blade.php`):
  - Mengantisipasi jumlah fasilitator/instruktur yang banyak (10-30+ orang) dengan arsitektur Bento Grid multi-kolom yang ringkas:
    * **Multi-Kolom Grid Responsif**: Mengubah susunan baris vertikal penuh menjadi grid ubin Bento 2 kolom di mobile (`grid-cols-2`), 3 kolom di tablet (`sm:grid-cols-3`), dan 4 kolom di desktop (`lg:grid-cols-4`).
    * **Wadah Scroll Terkendali (*Contained Height*)**: Membatasi tinggi maksimal grid (`max-h-[300px] sm:max-h-[360px] overflow-y-auto`) sehingga sebanyak apa pun jumlah fasilitator terdaftar di sistem, halaman tidak akan memanjang ke bawah tanpa batas.
    * **Pencarian Cepat & Aksi Massal (*Quick Search & Bulk Select*)**: Menambahkan kontrol pencarian live (`x-model="searchFasil"`) serta tombol cepat *Pilih Semua* dan *Kosongkan* dengan penghitung reaktif real-time.
    * **Desain Ubin Bento Mandiri**: Ubin kartu interaktif dengan inisial nama, indikator centang (*check indicator*), nama, email, dan status *Bertugas* (dot hijau) vs *Lepas Tugas* (dot abu-abu).
  - 12 feature test `AdminEventFlowTest` lulus 100%, Vite build bersih, Pint 0 violations.
- 2026-11-06: Redesain Bento Penugasan Fasilitator & Instruktur (`admin/events/show.blade.php`):
  - Mengubah seksi Penugasan Fasilitator dari 2 kartu polos memanjang dengan tombol terpisah menjadi **Bento Tile Widget terpadu**:
    * **Header Bento Integratif**: Dilengkapi ikon person, judul tebal, badge jumlah instruktur bertugas (`{{ count($assignedFasilitatorIds) }} Bertugas`), serta tombol CTA `Simpan Penugasan` yang terintegrasi rapi di sudut atas kartu.
    * **Grid Bento Fasilitator**: Disusun dalam grid seimbang (`grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-2.5`) dengan avatar inisial berwarna, tipografi nama yang tegas, email monospaced, dan checkbox interaktif.
    * **Interaktivitas Visual State**: Saat dicentang (`has-[:checked]`), avatar berubah warna menjadi biru sky cerah dengan teks putih, bingkai berubah menjadi biru sky (`border-sky-500`), dan latar belakang menjadi aksen lembut (`bg-sky-50/50`).
  - Menambahkan automated test `test_admin_can_update_event_fasilitator_assignment()` pada `AdminEventFlowTest`.
  - Seluruh 225 feature tests PHPUnit (1003 assertions) lulus 100%, Vite build bersih, Pint 0 violations.
- 2026-11-06: Redesain Bento Tab Peserta Event (`admin/events/show.blade.php`):
  - Mengubah tampilan Tab Peserta yang sebelumnya menumpuk vertikal menjadi arsitektur Bento Grid yang ringkas, modern, dan proporsional:
    1. **Bento Strip Ringkasan 3-Tile (Menggantikan 3 Box Vertikal)**:
       - Mengubah 3 kartu besar yang tadinya menumpuk ke bawah (*Total Kader*, *Komposisi Gender*, *Sebaran Cabang*) menjadi 3 Bento Tile sejajar dalam 1 baris (`grid grid-cols-3 gap-2 sm:gap-3`), memangkas tinggi vertikal hingga 70% di layar ponsel.
       - Tile 1 (*Total Kader*): Indikator kuota, rasio keterisian, dan mini progress bar warna sky.
       - Tile 2 (*Komposisi Gender*): Badge kontras pill L (biru) dan P (rose) beserta persentase.
       - Tile 3 (*Sebaran Cabang*): Jumlah PCM terwakili beserta nama pimpinan cabang domisili utama.
    2. **Bento Action Cluster Header**:
       - Mengatur tombol aksi (`+ Enroll Peserta`, `ID Card A4`, `Broadcast WA`) dalam grid 2-kolom mobile yang rapi dan seimbang tanpa patahan teks atau baris canggung.
    3. **Kartu Bento Kader Peserta**:
       - Menata ulang baris data peserta menjadi kartu Bento bersih dengan avatar berbingkai lembut, badge PCM berlatar sky, nomor NBM monospaced, serta kluster aksi tombol WhatsApp, Sertifikat, dan Keluarkan yang proporsional dan tidak mendesak nama kader.
  - Asset Vite dikompilasi ulang, Pint 0 style violations, pengujian `AdminEventFlowTest` lulus 100%.
- 2026-11-06: Redesain Bento & Optimasi Scroll Landing Page (`landing/index.blade.php`):
  - Mengeliminasi tata letak menumpuk vertikal ke bawah (*linear single-column stack*) dan memangkas tinggi scroll vertikal secara signifikan (pengurangan lebih dari 3.000px pada layar ponsel) menggunakan Bento Grid asimetris dan modern:
    1. **Bento Micro-Dock Kategori**: Deretan 8 kategori materi/keilmuan dikompresi menjadi *micro-dock strip* horizontal yang ramping (`mt-4 sm:mt-5 p-2 sm:p-3`, ikon `w-9 h-9 sm:w-11 sm:h-11`).
    2. **Bento Keunggulan Mutu & Tokoh Pimpinan (`#keunggulan`)**:
       * Kolom kiri (`lg:col-span-5`): Kartu Tokoh Ketua MPKSDI H. Zainudin Ahpandi dengan tampilan *horizontal compact strip* di mobile (thumbnail `w-16 h-20`, badge, quote) dan *full portrait* elegan di desktop.
       * Kolom kanan (`lg:col-span-7`): Grid Bento 2x2 yang memadukan 4 standar keunggulan (Kurikulum Baku PP, Penilaian 4 Format, Instruktur Bersertifikat, E-Sertifikat QR) secara berdampingan 2 kolom bahkan di layar smartphone.
    3. **Bento Course Cards & Mobile Snap Carousel (`#program`)**:
       * Mengonversi 4 kartu pelatihan besar menjadi *horizontal swipeable snap carousel* di mobile (`flex overflow-x-auto snap-x snap-mandatory gap-3 pb-2 -mx-4 px-4 sm:grid sm:grid-cols-2 lg:grid-cols-4 sm:overflow-visible`) dengan kartu berdimensi kompak (`w-[82vw] max-w-[290px]`, header `h-32 sm:h-36`), memangkas ~1.360px tinggi scroll mobile tanpa menghilangkan konten.
    4. **Bento Alur Peserta (`#tahapan`)**:
       * Mengubah alur vertikal 4 langkah menjadi grid 2x2 di mobile (`grid-cols-2 gap-2.5`) dan alur 4 kolom terhubung di desktop.
    5. **Bento Peta Spasial & Direktori 17 Cabang (`#sebaran`)**:
       * Menyesuaikan kanvas peta Leaflet menjadi responsif proporsional (`min-h-[260px] sm:min-h-[360px] lg:min-h-[440px]`), kontainer padding lebih rapat, serta mempertahankan jendela scroll 5 cabang aktif yang halus.
    6. **Bento Helpdesk & FAQ Accordion (`#faq`)**:
       * Memperketat *vertical padding* dan margin kartu helpdesk dan accordion, menghemat ~250px tinggi layar.
    7. **Multi-Column Themed Footer (`#kontak`)**:
       * Mempercantik dan memadatkan padding footer (`pt-8 sm:pt-12 pb-14 sm:pb-16 lg:pb-10`), menjaga navigasi bawah mengambang (*floating bottom dock*) tetap ergonomis.
  - 224 feature tests PHPUnit (992 assertions) lulus 100%, Vite build bersih, Pint 0 violations.
- 2026-11-06: Penyesuaian Rentang Tahun Selesai Dropdown Kepengurusan Organisasi (ORTOM) Sampai 2040:
  - Mengubah batas atas perulangan opsi `tahun_selesai` pada formulir pendaftaran (`register.blade.php`) dan halaman profil anggota (`profile.blade.php`) menjadi `max(2040, (int)date('Y') + 4)` sehingga dropdown mencakup tahun hingga 2040 secara menurun ke 1980.
  - Opsi 'Sekarang' (Masih Menjabat) tetap dipertahankan di posisi atas.
  - Menambahkan pengujian automated test pada `AnggotaRegistrationTest` dan `PortalProfileTest` untuk memverifikasi opsi `<option value="2040">` dan penyimpanan data periode paska-2030.
  - 224 feature tests PHPUnit (992 assertions) lulus 100%. Pint 0 violations.
- 2026-11-05: Eliminasi AI Slop Badge Kelulusan & Implementasi Fitur Edit/Upload TTD Pengesahan Sertifikat:
  1. **Tipografi Formal Kelulusan (Bebas AI Slop)**:
     - Mengganti bentuk rounded pill badge (`border-radius: 14px; background: #ecfdf5`) yang bergaya web AI chip dengan tipografi Serif klasik formal `L U L U S` (`letter-spacing: 5px; color: #064e3b; font-size: 15pt;`) diapit garis hairline emas horizontal (*gold hairline rules*). Desain menjadi sangat berwibawa layaknya ijazah/sertifikat resmi kenegaraan dan persyarikatan.
  2. **Pengesahan & Upload Tanda Tangan PNG**:
     - Menambahkan kolom database baru di tabel `events`:
       * `nama_penandatangan_kiri`, `jabatan_penandatangan_kiri`, `keterangan_penandatangan_kiri`, `ttd_kiri_path` (Penandatangan 1 / MoT / Instruktur).
       * `ttd_kanan_path` (Penandatangan 2 / Ketua MPKSDI / PDM).
     - Memperbarui form event (`form.blade.php`) dengan `enctype="multipart/form-data"` dan kartu 2 kolom pengesahan yang memungkinkan admin mengedit seluruh teks pejabat serta mengunggah file tanda tangan PNG transparan (maks 2 MB) lengkap dengan pratinjau dan opsi hapus.
     - Controller `EventController.php` memvalidasi file PNG, menyimpan ke disk publik (`storage/app/public/signatures/`), dan menghapus file fisik lama saat diganti atau dihapus.
     - Template PDF (`certificate-pdf.blade.php` & `certificates-batch-pdf.blade.php`) secara otomatis mendeteksi tanda tangan tersimpan atau fallback cerdas bila nama pejabat sesuai.
  3. **Verifikasi**:
     - Seluruh 222 automated feature tests PHPUnit (978 assertions) lulus 100%.
     - Laravel Pint lulus bersih (0 style violations).
- 2026-11-05: Redesain Sertifikat & Transkrip Baitul Arqam Berbasis Template Kubah Hijau-Emas (Sesuai Referensi Media 1 & 2):
  - **Aset & Background**:
    * Menggunakan background resolusi tinggi kubah hijau ornamen geometris Islam beraksen emas (`public/aset/bg-sertifikat-ba.jpg`), siluet Gedung Dakwah dan Surya Muhammadiyah.
    * Menggunakan teknik `@page { size: 297mm 210mm; margin: 0; }` dengan `.bg-fixed { position: fixed; top: 0; left: 0; width: 297mm; height: 210mm; z-index: -1000; }` sehingga latar belakang ter-render presisi di setiap halaman tanpa menambah bobot DOM atau memicu overflow.
    * Menambahkan aset grafis pendukung: tanda tangan resmi berlatar belakang transparan (`public/aset/signature-mot.png`, `public/aset/signature-ketua.png`), dan ikon pembatas berlian emas (`public/aset/gold-diamond.png`) untuk menghindari karakter `?` pada font standar PDF.
  - **Halaman 1 (Sertifikat Utama — Selaras Media 1)**:
    * Dua logo di tengah atas (Surya Muhammadiyah kiri, MPKSDI kanan) di bawah kubah, dipisahkan garis emas vertikal.
    * Teks formal institusi PDM & MPKSDI Karanganyar dengan pembatas berlian emas.
    * Judul `SERTIFIKAT` (serif klasik), nomor sertifikat dinamis per-peserta, nama peserta bergaris bawah tebal, NBM, dan asal PCM.
    * Pita nama acara bernuansa hijau botol (`#064e3b`) berbingkai emas (`#d4af37`), diiringi pill status kelulusan hijau emerald diapit ornamen berlian emas.
    * Pengesahan 3 kolom seimbang: Master of Training (MoT) di kiri, QR Code verifikasi online & hash pangkalan data di tengah, serta Ketua MPKSDI PDM Karanganyar di kanan.
  - **Halaman 2 (Transkrip Capaian Kompetensi — Selaras Media 2)**:
    * Logo Surya Muhammadiyah tunggal di tengah atas beserta identitas PDM MPKSDI dan alamat kantor Gedung Dakwah.
    * Judul `Transkrip Capaian Kompetensi` diiringi subjudul `PERKADERAN BAITUL ARQAM` dan badge pill lampiran nomor sertifikat & acara.
    * Biodata kader 2 kolom (Nama Lengkap, NBM, PCM, Nomor Peserta).
    * Tabel ganda 2 kolom proporsional:
      - Kolom kiri: Tabel Mata Pembinaan / Materi Sesi, Narasumber, dan Durasi Aktual (menit) beserta total durasi tercatat.
      - Kolom kanan: Tabel Evaluasi 3 Aspek Capaian (I. Kognitif, II. Psikomotorik, III. Afektif), Nilai Akhir Komprehensif, dan badge Lulus/Grade, diiringi kotak penjelasan Keterangan.
    * Pengesahan Ketua Majelis di sisi kanan bawah.
  - **Template Massal (`certificates-batch-pdf.blade.php`)**:
    * Mengadopsi struktur identik dengan perulangan per-peserta yang dipisahkan secara tepat menggunakan `.page-break`.
  - **Verifikasi**:
    * Seluruh 220 automated tests PHPUnit (953 assertions) lulus 100%.
    * Laravel Pint lulus bersih (0 style violations).
    * Hasil render PDF contoh (`sample_certificate.pdf`) dan pratinjau PNG visual halaman 1 & 2 terkonfirmasi rapi, tanpa overflow, dan tersimpan di direktori artifact.
- 2026-11-05: Penyesuaian Nomenklatur Spesifik Tingkat Kepengurusan Per ORTOM:
  - Menambahkan konfigurasi `tingkat_ortom_per_organisasi` di `config/arqam.php` untuk memetakan tingkat kepengurusan resmi tiap organisasi otonom secara terisolasi tanpa mencampur singkatan ORTOM lain.
  - Untuk Ikatan Mahasiswa Muhammadiyah (IMM): `Dewan Pimpinan Pusat (DPP IMM)`, `Dewan Pimpinan Daerah (DPD IMM)`, `Pimpinan Cabang (PC IMM)`, `Koordinator Komisariat (Korkom IMM)`, `Pimpinan Komisariat (PK IMM)`, dan `Lainnya`.
  - Untuk IPM: PP, PW, PD, PC, PR, PK (Sekolah/Madrasah).
  - Untuk HW: Kwarpus, Kwarwil, Kwarda, Kwarcab, Qobilah.
  - Untuk TS: PP, Pimwil, Pimda, Pimcab, Unit Latihan / Kolat.
  - Untuk Pemuda Muhammadiyah, NA, Aisyiyah: Sesuai struktur resmi persyarikatan.
  - Menjaga backward compatibility penuh: data tersimpan lama tetap dirender dan dipertahankan.
  - Seluruh 220 tests PHPUnit (953 assertions) lulus 100%, Pint 0 violations, Vite build bersih.
- 2026-11-05: Perbaikan Kepatuhan Dokumen Sertifikat:
  - Data pengesahan sertifikat kini per-event: tanggal terbit, jabatan, nama, dan NBM penandatangan. Template tidak lagi menggunakan nama/NBM pejabat atau tanggal cetak yang hardcode.
  - NIK dihilangkan dari sertifikat dan transkrip. Identitas dokumen menggunakan nomor sertifikat, QR verifikasi, NBM bila tersedia, serta asal PCM.
  - Transkrip mencetak durasi aktual sesi materi dalam menit dari jam mulai/selesai; tidak lagi mengasumsikan 2 JP per sesi atau total minimum 16 JP.
  - Redaksi `Lulus Bersyarat` dibedakan dari kelulusan reguler. Ambang predikat dan presensi tidak dicetak sebagai kebijakan baku sebelum keputusan bisnis disahkan.
- 2026-11-05: Standardisasi & Perapihan Desain Sistem Cetak Sertifikat Baitul Arqam (2 Halaman Landscape A4):
  1. **Halaman 1 (Sertifikat Utama) — `certificate-pdf.blade.php` & `certificates-batch-pdf.blade.php`**:
     - **Kop Surat Resmi Simetris**: Menambahkan logo resmi Muhammadiyah (kiri) dan logo resmi MPKSDI (kanan) berbasis Base64 data URI lokal agar kompatibel 100% di DomPDF tanpa masalah izin file/jaringan.
     - **Perbaikan Masalah Border Tabrakan**: Menghapus `position: absolute; bottom: 3mm` pada tabel tanda tangan yang sebelumnya menyebabkan nama penandatangan, NBM, dan QR code menabrak dan memotong garis border kuning inner (`.cert-inner`). Diganti dengan alur vertikal normal (`margin-top: 6mm`) sehingga seluruh elemen tanda tangan (MoT, QR verifikasi, dan Ketua PDM) berada rapi di dalam batas bingkai inner.
     - **Tipografi & Spacing**: Menyelaraskan ukuran font judul, nomor sertifikat, nama peserta (bold underline), deskripsi kegiatan, dan badge status kelulusan/grade.
  2. **Halaman 2 (Transkrip Capaian Kompetensi Perkaderan)**:
     - Mengadopsi bingkai double-border biru tua dan emas yang identik dengan Halaman 1 untuk konsistensi visual dokumen resmi.
     - Header kop transkrip dilengkapi logo resmi Muhammadiyah dan MPKSDI.
     - Rekap sesi materi (dengan beban JP) di sebelah kiri, dan rekap penilaian 3 aspek (Kognitif, Psikomotorik, Afektif) beserta nilai akhir dan predikat mutu kelulusan di sebelah kanan.
     - Pengesahan Ketua Majelis tertata proporsional di kanan bawah di dalam batas bingkai dokumen.
  3. **Verifikasi**:
     - Seluruh 218 automated tests PHPUnit (931 assertions) tetap lulus 100%.
     - Contoh sertifikat PDF dan hasil render PNG 150 DPI berhasil dibuat dan didokumentasikan di folder artifact.
- 2026-11-05: Perapihan Mobile UI / Mobile-First Redesign (Stepper Navigation & Footer Landing Page):
  1. **Tombol Navigasi Stepper Form Pendaftaran (`register.blade.php`)**:
     - Mengubah orientasi tombol navigasi bawah pada seluruh tab (Diri, Agama, Organisasi, Kemasyarakatan):
       * Mengganti susunan bertumpuk vertikal (`flex-col-reverse` yang menempatkan tombol Lanjut di atas dan tombol Kembali di bawah dengan tinggi masif) menjadi susunan **berdampingan horizontal (side-by-side)** di mobile (`flex items-center justify-between gap-2.5 sm:gap-4`).
       * Tombol "Kembali" dibuat kompak, ramping, dan bersahabat di sisi kiri (`px-4 sm:px-6 py-2.5 sm:py-3 min-h-[44px] border border-slate-300 rounded-xl`).
       * Tombol "Lanjut" / "Kirim Pendaftaran" berada di sisi kanan menguasai porsi fleksibel (`flex-1 sm:flex-initial py-2.5 sm:py-3 min-h-[44px] rounded-xl font-extrabold`).
       * Mengurangi konsumsi tinggi layar ponsel hingga lebih dari 60%, sehingga tidak lagi menutupi layar dan sangat ergonomis untuk jangkauan jempol pengguna.
  2. **Footer Mobile Landing Page (`landing/index.blade.php`)**:
     - **Batik Corner Overlay**: Sembunyikan batik sudut kanan atas dan kiri bawah pada layar mobile (`hidden sm:block`) agar motif batik tidak lagi menabrak/menimpa teks deskripsi MPKSDI PDM Karanganyar. Keterbacaan teks menjadi 100% jernih.
     - **Tautan Organisasi & Perkaderan**: Diubah menjadi grid 2 kolom berdampingan di mobile (`grid grid-cols-2 gap-4 sm:gap-6 md:col-span-2 lg:col-span-4 lg:grid-cols-2 lg:gap-8`) untuk menghemat ruang vertikal secara drastis daripada bertumpuk 1 kolom memanjang.
     - **Kontak & Padding Bawah**: Mengatur kartu WhatsApp dan Email menjadi grid responsif yang rapi serta menambahkan padding bawah ekstra (`pb-24 sm:pb-20 lg:pb-10`) agar kotak Sekretariat tidak terpotong oleh browser navigation bar pada perangkat ponsel.
  3. **Periode ORTOM Mobile-First (`register.blade.php`, `profile.blade.php`)**:
     - Mengubah grid dropdown Tahun Mulai dan Tahun Selesai menjadi `grid grid-cols-2 gap-2 sm:gap-3` sehingga selalu berdampingan secara alami di ponsel tanpa memanjang vertikal.
  4. **Verifikasi**:
     - Seluruh 218 automated tests PHPUnit (931 assertions) tetap lulus 100%.
     - Laravel Pint lulus bersih (0 style violations).
     - Kompilasi build aset Vite sukses.
- 2026-11-05: Implementasi Hierarki IMM, Kunci Input Numerik Anti-Huruf, & Perapihan Mobile UI / Footer:
  1. **Konfigurasi ORTOM (`config/arqam.php`)**:
     - Menambahkan `'Ikatan Mahasiswa Muhammadiyah'` ke `keaktifan_ortom` melengkapi 7 ortom resmi Muhammadiyah.
     - Memperbarui `tingkat_ortom` dengan nomenklatur spesifik IMM (`DPP IMM`, `DPD IMM (Wilayah)`, `PC IMM`, `Korkom IMM`, `PK IMM / Rayon`).
  2. **Penguncian Input Klien & Validasi Server Anti-Huruf**:
     - Frontend (`register.blade.php`, `profile.blade.php`, `cek-status.blade.php`, `admin/users/`, `admin/events/`):
       * Menambahkan `inputmode="numeric"`, `pattern="[0-9]*"`, pencegah tombol huruf `onkeypress`, dan pembersihan instan `oninput="this.value = this.value.replace(/\D/g, '')"` pada field `nik`, `no_wa`, `nbm`, `identifier`, dan `kuota`. Pengguna tidak bisa mengetik atau menempel huruf.
     - Backend (`AnggotaRegistrationRequest.php`, `ProfileController.php`):
       * `nik`: Wajib 16 digit angka murni (`digits:16`, `regex:/^[0-9]{16}$/`) dengan pesan error kustom.
       * `no_wa`: Wajib angka murni (`regex:/^[0-9]+$/`) dengan pesan error kustom.
       * `nbm`: Angka murni opsional (`regex:/^[0-9]*$/`) dengan pesan error kustom.
  3. **Perapihan Mobile UI & Footer (`register.blade.php`)**:
     - Header kartu AUM & ORTOM ditata ulang dengan layout fleksibel (`flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 sm:gap-3`) sehingga badge pill ("2 Dropdown (AUM & Jabatan)", "Boleh pilih lebih dari 1") tidak saling bertabrakan atau menumpuk judul pada layar ponsel sempit.
     - Kotak footer "Sudah memiliki akun kader?" dan navigasi bawah dioptimalkan untuk mobile dengan touch target ergonomis ($\ge 44\text{px}$) dan alignment seimbang.
  4. **Pengujian & Verifikasi**:
     - Ditambahkan 4 skenario automated test (penolakan NIK/WA/NBM berhuruf dan pendaftaran kader IMM Korkom/PK IMM).
     - 218 tests PHPUnit (931 assertions) lulus 100%.
     - Laravel Pint lulus bersih (0 violations) dan aset Vite terkompilasi sukses.
     - Pint styling lulus bersih.
- 2026-11-05: Penyesuaian Periode Kepengurusan ORTOM Menjadi 2 Dropdown (Tahun Mulai & Tahun Selesai):
  1. **Antarmuka Pendaftaran & Profil (`register.blade.php`, `profile.blade.php`)**:
     - Menggantikan droplist periode statis tunggal dengan 2 dropdown berdampingan dalam grid 2 kolom:
       * **2a. Tahun Mulai**: Opsi tahun dari `date('Y') + 4` (2030) sampai 1980 menurun (`@selected($oldTahunMulai == $yr)`).
       * **2b. Tahun Selesai**: Opsi 'Sekarang' (Masih Menjabat) dan opsi tahun dari `date('Y') + 4` (2030) sampai 1980 menurun (`@selected($oldTahunSelesai == $yr)`).
     - Menambahkan ekstraksi nilai lama otomatis untuk `$oldTahunMulai` dan `$oldTahunSelesai`, termasuk fallback parsing otomatis dari string periode lama (`YYYY - YYYY` atau `YYYY - Sekarang`).
  2. **Validasi & Backend Controller (`AnggotaRegistrationRequest`, `AnggotaRegistrationController`, `ProfileController`)**:
     - Aturan validasi mengizinkan `jabatan_ortom.*.tahun_mulai` dan `jabatan_ortom.*.tahun_selesai` (string nullable).
     - Controller mengekstrak `$tahunMulai` dan `$tahunSelesai` serta melakukan sintesis atribut `$periode = trim($tahunMulai . ($tahunSelesai ? ' - ' . $tahunSelesai : ''))`.
     - Struktur tersimpan pada kolom JSON `jabatan_ortom` berisi `tingkat`, `periode`, `tahun_mulai`, `tahun_selesai`, `jabatan`.
  3. **Model & Kompatibilitas (`Anggota::formatJabatanOrtom()`)**:
     - Memformat periode secara dinamis baik dari key `periode` maupun gabungan `tahun_mulai` & `tahun_selesai`.
     - Seluruh template cetak PDF biodata kader, ekspor Excel, dan tampilan detail admin tetap 100% kompatibel tanpa regresi.
  4. **Verifikasi**:
     - Automated tests bertambah menjadi 214 tests lulus 100% (907 assertions).
     - Laravel Pint lulus bersih.
     - Kompilasi aset Vite sukses.
- 2026-10-20: Pembersihan Keamanan LoginController (Hapus Hardcode Password & Auto-Provision):
  1. Menghapus seluruh password hardcode (`AdminPDM@25`, `cabangkra`, `cabangjra`) dan map auto-provision PCM dari `LoginController`.
  2. Menghapus bypass otentikasi backdoor `User::updateOrCreate` saat login gagal/tidak ditemukan.
  3. Login murni memverifikasi kredensial terhadap hash password pengguna di database (`Hash::check`), mendukung fleksibilitas input email/no_wa/slug PCM tanpa celah keamanan.
  4. Tes `AuthTest` diperbarui untuk memverifikasi autentikasi aman tanpa auto-provisioning.
- 2026-10-20: Peningkatan Input Organisasi Otonom (ORTOM) Menjadi 3 Dropdown Terstruktur (Tingkat, Periode, Jabatan):
  1. **Tingkat ORTOM (Dropdown 1)**: Opsi mencakup hirarki lengkap ortom: Pimpinan Pusat (PP), Pimpinan Wilayah (PW), Pimpinan Daerah (PD / DPD / Pimda TS / Kwarda HW), Pimpinan Cabang (PC / DPC / Pimcab TS / Kwarcab HW), Pimpinan Ranting (PR / DPR / Kolat TS / Qobilah HW), Pimpinan Komisariat (PK / Rayon), dan Lainnya (`config('arqam.opsi_form.tingkat_ortom')`).
  2. **Periode Kepengurusan (Dropdown 2)**: Opsi periode muktamar/musyda: 2022 - 2027, 2018 - 2022, 2015 - 2018, 2010 - 2015, dan Lainnya (`config('arqam.opsi_form.periode_ortom')`).
  3. **Jabatan / Amanah (Dropdown 3 + Teks Kustom)**: Opsi jabatan standar (Ketua Umum / Ketua, Wakil Ketua, Sekretaris, Bendahara, Koordinator Bidang, Anggota Bidang, Anggota Biasa, Lainnya) dengan input teks kustom otomatis bila memilih "Lainnya" (`config('arqam.opsi_form.jabatan_ortom_standar')`).
  4. **Backend & Validasi**:
     - `AnggotaRegistrationRequest` & `ProfileController`: Mengizinkan struktur array `jabatan_ortom.*` (`tingkat`, `periode`, `jabatan`, `jabatan_custom`) serta string legacy tanpa error validasi.
     - `AnggotaRegistrationController` & `ProfileController`: Sanitasi dan pembersihan data jabatan ORTOM agar tersimpan rapi sebagai array terstruktur di kolom JSON `jabatan_ortom`.
     - `Anggota::formatJabatanOrtom()`: Helper otomatis memformat data array menjadi string yang rapi (`Jabatan - Tingkat - Periode Tahun`) atau mengembalikan string legacy secara transparan.
  5. **Tampilan & Ekspor**:
     - `register.blade.php` dan `portal/profile.blade.php`: Antarmuka interaktif 3 dropdown per ortom yang dicentang.
     - `admin/anggota/show.blade.php`, `pdf/biodata-kader.blade.php`, dan `exports/anggota.blade.php`: Menggunakan `formatJabatanOrtom()` sehingga aman dari warning `Array to string conversion` dan tampil elegan di PDF serta Excel.
- 2026-10-20: Pemisahan Tegas 3 Pilar Organisasi (Pimpinan Muhammadiyah, AUM, ORTOM) & Filter / Sorting Multi-Kategori:
  1. **Pemisahan 3 Pilar**:
     - Pilar 1: Pimpinan Muhammadiyah (`keaktifan_muhammadiyah`, `jabatan_muhammadiyah`, `pcm`, `prm`).
     - Pilar 2: Amal Usaha Muhammadiyah (AUM) dengan 2 Dropdown terpisah:
       * Dropdown 1: Nama AUM (`nama_aum`) dengan opsi dari `config('arqam.opsi_form.nama_aum')`, plus opsi 'Lainnya' yang memunculkan input teks kustom (`nama_aum_custom`).
       * Dropdown 2: Jabatan / Amanah di AUM (`jabatan_aum`) dengan opsi dari `config('arqam.opsi_form.jabatan_aum')`, plus opsi 'Lainnya' yang memunculkan input teks kustom (`jabatan_aum_custom`).
     - Pilar 3: Organisasi Otonom (ORTOM) (`keaktifan_ortom`, `jabatan_ortom`).
  2. **Dukungan Form Pendaftaran & Profil**:
     - `resources/views/anggota/register.blade.php`: Tab 3 Data Organisasi ditata ulang menjadi 3 pilar yang jelas dan modern. State 2 dropdown AUM terintegrasi dengan pemulihan draf (localStorage).
     - `resources/views/portal/profile.blade.php`: Mendukung pembaruan nama AUM dan jabatan AUM dengan 2 dropdown dan input teks kustom.
     - `app/Models/FormPertanyaan.php`: Menambahkan `nama_aum` dan `jabatan_aum` ke `$skipOpsiValidation` agar kader dapat memilih dari dropdown maupun menginput nama AUM kustom tanpa terhalang strict `Rule::in`.
  3. **Filter & Sorting Multi-Kategori**:
     - `AnggotaController::index` & `resources/views/admin/anggota/index.blade.php`: Menambahkan filter pilar (Semua/AUM/ORTOM/Muhammadiyah), filter spesifik nama AUM, filter spesifik ORTOM, filter cabang PCM, dan sorting (nama A-Z/Z-A, nama AUM A-Z, cabang PCM A-Z). Tabel menampilkan kolom Kategori & Afiliasi lengkap dengan badge AUM, ORTOM, dan Cabang.
     - `PesertaController::search` & `resources/views/admin/events/enroll.blade.php`: Mendukung pencarian, filter pilar, filter spesifik AUM, filter ORTOM, filter cabang, dan sorting sehingga panitia Baitul Arqam AUM atau ORTOM dapat menyaring kader dari unit bersangkutan secara instan untuk di-enroll massal.
  4. **Ekspor & Cetak Biodata**:
     - `resources/views/exports/anggota.blade.php`: Kolom `Nama AUM` dan `Jabatan di AUM` diekspor secara terpisah ke Excel.
     - `resources/views/pdf/biodata-kader.blade.php` & `resources/views/admin/anggota/show.blade.php`: Menampilkan data AUM secara terstruktur dan jelas.
  5. **Verifikasi**:
     - 208 automated feature tests lulus 100% (854 assertions).
     - Laravel Pint lulus 100%.
     - Vite production build berhasil.
- 2026-10-10: Implementasi ID Card Cutout V2 & Multi-Tier Background Removal:
  1. **Model Segmentasi Terpilih (Browser-Side)**:
     - Model terpilih: `selfie_segmenter.tflite` (249 KB) vs `selfie_multiclass_256x256.tflite` (16.37 MB).
     - Alasan pemilihan:
       * Ukuran berkas sangat ringan (249 KB vs 16.37 MB, efisiensi bandwidth 65x lebih hemat).
       * Membaca kontur peci hitam dan jilbab/busana secara utuh sebagai foreground tunggal probabilitas kontinu tanpa artefak kuantisasi antar-kelas.
       * Waktu pemuatan awal instan (<50ms) dan konsumsi memori WebGL/WASM rendah di perangkat seluler peserta.
  2. **Lokasi Aset WASM & Model Self-Hosted**:
     - Direktori WASM: `public/vendor/mediapipe/wasm/` (`vision_wasm_internal.wasm`, `vision_wasm_nosimd_internal.wasm`, dll.).
     - Direktori Model: `public/models/selfie_segmenter.tflite`.
     - Bebas ketergantungan CDN runtime pihak ketiga untuk keamanan intranet/offline dan kepatuhan CSP.
  3. **Aturan Validasi Cutout Server-Side (`ValidCutoutPng`)**:
     - Magic bytes PNG wajib: `\x89PNG\r\n\x1a\n`.
     - Proporsi transparansi: piksel transparan (GD alpha >= 120) wajib berada di antara 5% dan 90%.
     - Dimensi dan rasio aspek identik dengan foto sumber asli (toleransi ±1px).
     - Server wajib me-reencode PNG melalui GD (`imagesavealpha` + `imagepng`) sebelum disimpan ke disk private; byte mentah dari klien tidak pernah disimpan langsung.
  4. **Hierarki Resolusi Sumber Foto ID Card (`ImageCutoutService::resolveForAnggota`)**:
     - Jika `foto_mode === 'original'`: gunakan foto sumber asli dengan bingkai rounded (fallback default photo).
     - Jika `foto_mode !== 'original'` (mode cutout aktif):
       1. Cutout valid dari klien browser (`foto_cutout_path` di disk private).
       2. Fallback server-side GD v2 dari foto sumber asli (`cutouts/v2_{hash}.png`).
       3. Foto asli sumber (mode `original`).
       4. Foto default pimpinan MPKSDI (`ketua-mpksdi-cutout.png`).
  5. **Algoritma Server Fallback GD v2 (`v2_` cache)**:
     - Cache key v2: `$hash = md5($binary.'|v2|'.$tolerance); $cachePath = "cutouts/v2_{$hash}.png";`.
     - Metrik jarak warna YCbCr dengan bobot luminance rendah: $\sqrt{0.25 \times \Delta Y^2 + 1.25 \times \Delta Cb^2 + 1.25 \times \Delta Cr^2}$.
     - Sobel edge barrier pada channel Y (threshold ~55) untuk mencegah flood-fill menembus batas subjek.
     - Pertumbuhan wilayah (region growing) relatif terhadap tetangga lokal.
     - Pembersihan komponen terhubung terbesar (largest connected component) untuk mengeliminasi pulau bintik noise di sudut/tepi latar.
     - Blur alpha 2px (5x5 separable box blur) untuk tepi anti-aliasing alami tanpa gradient fade bawah (transisi gelombang ditangani oleh overlay artwork).
  6. **Tampilan Admin & Kontrol Pasfoto**:
     - Thumbnail foto asli dan cutout ditampilkan berdampingan di detail anggota admin dengan indikator mode aktif.
     - Admin dapat mengubah mode menjadi `original` atau menghapus cutout (memicu fallback otomatis ke GD v2).

- 2026-10-10: Implementasi Transisi Halaman Mulus & Indikator Loading Lingkaran Global (*Smooth Circular Preloader*):
  1. **Latar Belakang & Masalah**:
     - Navigasi antar-halaman pada aplikasi MPA terasa kasar / patah (*stutter*) saat browser menunggu aset CSS/JS dan parsing DOM di jaringan/perangkat tertentu.
  2. **Solusi & Desain Arsitektur**:
     - Komponen Blade `<x-page-loader />` di `resources/views/components/page-loader.blade.php`:
       - Dual-ring spinner berputar elegan dengan palet identitas resmi MPKSDI: Lingkaran luar Cerulean `#005c8a` dan lingkaran dalam Saffron Gold `#f8bb00` (counter-rotating).
       - Badge emblem logo MPKSDI di pusat lingkaran dengan fallback aman.
       - Backdrop transparan berkabut (`bg-white/85 backdrop-blur-md`) dan tipografi institusional: *"Menyiapkan Halaman — Memuat data dan aset sistem..."*.
     - Mesin Transisi di `resources/js/app.js` (`initPageLoader()`):
       - Menangkap klik navigasi tautan internal `<a>` (same-origin, non-download, non-hash, non-external, non-modified click).
       - Pengecualian otomatis untuk unduhan berkas (ekstensi `.pdf`, `.xlsx`, `.zip`, route segmen `download`, `export`, `batch`, `cetak-lembar`, dll.).
       - Menangani form submissions non-dialog.
       - Kompatibilitas BFCache via event `pageshow` dan auto-fade saat `DOMContentLoaded`/`load`.
       - Safety fallback timeout 8 detik agar layar tidak pernah terkunci jika terjadi latensi ekstrem.
     - Dipasang di seluruh layout utama (`layouts/app.blade.php`, `layouts/guest.blade.php`, `landing/index.blade.php`, `errors/layout.blade.php`, `admin/absensi/scan.blade.php`, `admin/events/presentasi.blade.php`).
  3. **Verifikasi**:
     - `php artisan test`: 181 passed (738 assertions), durasi 76.16s.
     - `vendor/bin/pint`: 100% lulus.
     - `npm run build`: Kompilasi Vite berhasil dalam 4.11s.

- 2026-10-07: Penyempurnaan Algoritma Pembersihan Latar Belakang Pasfoto Peserta (Cutout ID Card) di `ImageCutoutService.php`:
  1. **Akar Masalah**:
     - Sebelumnya terdapat sisa busur/lingkaran elips abu-abu di atas kepala/peci peserta karena hard-coded face shield yang terlalu besar.
     - Terdapat kotak kaku di samping bahu peserta dan kebocoran flood-fill ke dalam baju batik/kemeja berwarna putih/krem.
     - Koordinat sampling perimeter berpotensi melebihi batas piksel gambar (menghasilkan ValueError/exception dan fallback ke JPEG mentah).
  2. **Penyempurnaan Algoritma**:
     - Menghapus pembatas elips kaku di luar kepala dan menggantinya dengan **Inner Core Face Protection** (hanya melindungi area hidung/mata tengah agar peci, jilbab, rambut, dan telinga terpotong presisi alami).
     - Menambahkan proteksi biometrik warna kulit manusia (`$isSkinTone`) dan peci/rambut hitam (`$bgBrightness > 95 && $r < 55 && $g < 55 && $b < 55`).
     - Membatasi flood-fill seed queue hanya dari tepi atas (`y = 0`) dan tepi samping atas (`y <= 0.45 * H`) agar kain bahu pada pasfoto portrait tidak dijadikan seed background.
     - Melindungi area dada tengah (`chestY = 0.58H, chestX = 0.28W..0.72W`) dan menambahkan 2-pass **Hole-Filling** pada area tubuh (`y >= 0.50H`) untuk mencegah motif batik/kemeja putih tembus pandang.
     - Menggunakan toleransi adaptif netral studio (35) dan despeckle pass untuk membersihkan bintik noise di sekitar leher dan bahu.
     - Menerapkan soft-edge feathering (alpha 42) dan bottom gradient fade pada 12% terbawah gambar.
  3. **Verifikasi**:
     - `Tests\Feature\IdCardBatchTest`: 4 passed (11 assertions).
     - `Tests\Feature\EndToEndSystemLifecycleTest`: 1 passed (50 assertions).
     - Verifikasi visual pada sampel foto riil kader (`kader-01.jpg` s.d. `kader-05.jpg`) menunjukkan kontur peci, jilbab, dan kemeja sangat bersih, rapi, dan alami.

- 2026-10-07: Redesign Global Navigasi Event Menjadi Workspace-Style Horizontal Navigation (`show.blade.php`):
  1. **Transformasi Arsitektur Navigasi**:
     - Menggantikan pola wizard/stepper bernomor lama (`1. Pendataan`, `2. Jadwal`, dst.) menjadi **Workspace-Style Horizontal Navigation** ala enterprise modern yang sticky di bagian atas halaman detail event.
     - Struktur bertingkat hierarkis (Level 1: 6 Modul Utama, Level 2: Sub-Navigasi per modul aktif) untuk memangkas cognitive load admin.
  2. **Level 1 (Global Horizontal Module Navigation)**:
     - Urutan wajib 6 modul utama:
       1. `Peserta` (daftar peserta, registrasi, kuota, detail)
       2. `Sesi & Presensi` (jadwal kegiatan, kehadiran, attendance display)
       3. `CBT & Asesmen` (ditempatkan sebelum Keaktifan & Outbound)
       4. `Keaktifan & Outbound` (ditempatkan sesudah CBT & Asesmen)
       5. `RTL` (Rencana Tindak Lanjut)
       6. `Laporan` (rekap, ekspor data, dokumen resmi)
     - Desain visual: Underline indicator 2–3px `#063A60`, font weight 600 saat aktif, neutral slate 450–500 saat inaktif, tanpa nomor urut, tanpa pill besar, tanpa rounded badge sembarangan, sticky `top-0 z-30` dengan overflow-x horizontal scrollable.
  3. **Level 2 (Secondary Sub-Navigation)**:
     - **CBT & Asesmen**: 5 Sub-tabs (`Pretest & Posttest`, `Psikomotor`, `Afektif`, `Self Assessment`, `AHP–SAW`).
     - **Keaktifan & Outbound**: 2 Sub-tabs (`Regu Outbound`, `Poin Keaktifan Forum`).
     - **RTL**: Header bersih tanpa sub-tab berlebih, subtitle penugasan paska pelatihan.
     - **Laporan**: 3 Sub-tabs (`Rekap`, `Export Data`, `Laporan & Dokumen Resmi`).
  4. **Integritas Sistem**:
     - 100% routes, permissions, data binding, modal Alpine.js, dan fungsionalitas backend dipertahankan tanpa regresi.
     - Verifikasi automated test PHPUnit (`AbsensiTest`, `AdminEventFlowTest`) dan build Vite lulus 100%.

- 2026-10-07: Redesign Menyeluruh Live Attendance Display (`scan.blade.php`) & Penataan Ulang Tab Stepper Asesmen & CBT (`show.blade.php`):
  1. **Redesign Halaman Presensi / Scanner (`resources/views/admin/absensi/scan.blade.php`)**:
     - Ditransformasikan dari tampilan dashboard admin 50:50 konfigurasional menjadi **Digital Attendance Display / Check-in Screen 16:9** yang dioptimalkan untuk proyektor/TV di depan ruangan Baitul Arqam (jarak pandang audiens beberapa meter).
     - **Elemen Tidak Relevan Dihapus**: Menghilangkan tombol cetak PDF, badge teknis (Scanner Aktif/Online), kotak preview QR gelap, instruksi USB barcode, card-card kecil admin, tombol submit "Proses Presensi", dan aksi delete/edit individu di layar publik.
     - **Alur Scanner Tanpa Klik**: Scanner barcode USB/Bluetooth bertindak sebagai keyboard input yang langsung mentrigger validasi saat mengirimkan `Enter`. Input field berukuran besar (56–64px, 18–20px font) dengan autofocus bawaan, auto-refocus saat blur atau klik layar, dan pembersihan otomatis tanpa tombol submit.
     - **Success State Sangat Menonjol**: Confirmation panel/overlay besar di tengah layar (durasi 3.5 detik) dengan nama peserta raksasa (36–48px), tanda checkmark hijau tenang `#0D7A5F`, judul sesi, dan waktu presensi. Otomatis kembali ke scanner input.
     - **Penanganan Duplicate Lembut**: Banner amber/orange subtle (*"PRESENSI SUDAH TERCATAT"*, waktu terakhir tercatat) tanpa error merah agresif.
     - **Invalid State Ringan**: Alert merah subtle (*"QR CODE TIDAK DIKENALI"*, auto-refocus 2.5s).
     - **Live Attendance List 3 Kolom**: Menampilkan waktu (`09:12`), nama peserta, dan status `Berhasil ✓`. Terurut terbaru di atas dengan highlight hijau lembut 1.5 detik pada peserta baru. Empty state berbobot tipis tanpa card raksasa.
     - **Fitur Proyektor**: Jam digital real-time, toggle layar penuh (Fullscreen F11 API), dan watermark tipis motif geometris Muhammadiyah (opacity 2.5%).
  2. **Penataan Stepper Kronologis & Tab Asesmen & CBT (`resources/views/admin/events/show.blade.php`)**:
     - **Urutan Kronologis Stepper**:
       1. Pendataan Peserta
       2. Jadwal & Presensi
       3. **Asesmen & CBT** (*dipindah sebelum Keaktifan & Outbound*)
       4. **Keaktifan & Outbound** (*dipindah sesudah Asesmen & CBT*)
       5. Tugas Akhir RTL
       6. Rekap & Unduh Data
     - **Redesign Tab Asesmen & CBT**:
       - Mengganti layout 4 card paralel menjadi **SATU container putih tenang (`bg-white rounded-xl border border-slate-200/90 shadow-2xs divide-y divide-slate-100`)**.
       - Menampilkan 4 alur tahapan bernomor vertikal (`01 Pretest Kognitif`, `02 Posttest Kognitif`, `03 Praktik Psikomotor`, `04 Afektif & Evaluasi`).
       - Header tab menyertakan indikator progress instrumen aktif/selesai dengan mini progress bar.
       - Progressive disclosure untuk Afektif & Evaluasi via menu dropdown terpadu (*"Penilaian Sikap Afektif"* & *"Angket Evaluasi Kegiatan"*).
       - Menghilangkan badge status berisik ("Tutup", "0 Rubrik", "Sikap") dan menggantinya dengan teks status tenang berbobot proporsional.
  3. **Verifikasi**:
     - `php artisan test tests/Feature/AbsensiTest.php tests/Feature/AdminEventFlowTest.php` lulus 100% (17 passed, 51 assertions).
     - `vendor/bin/pint --test` lulus tanpa pelanggaran style formatting.

- 2026-10-07: Penyempurnaan Tuntas Cutout Pasfoto ID Card (Eliminasi Wajah/Mata/Jilbab Bolong & Kotak Border 1px):
  1. **Akar Masalah**:
     - Pada foto kader berlatar belakang studio cokelat/beige muda dengan jilbab berwarna senada (misal Novi Kartikasari, `kader-18.jpg`), algoritma flood-fill sebelumnya dengan toleransi statis 38 menembus jilbab, pipi kanan, dan bola mata sehingga motif gedung/kuning Layer 1 tembus pandang ke wajah peserta.
     - Pembagian grid kolase foto (`foto1.png`, `foto2.png`) sebelumnya menyisakan 1px garis pembatas putih di sisi atas dan kiri, yang tidak terhapus dan tampak sebagai garis kotak bingkai persegi tipis di atas gedung ID Card.
     - Bagian bawah dada/baju terpotong flat di atas kurva putih foreground Layer 3.
  2. **Solusi & Rekayasa Presisi**:
     - `app/Services/ImageCutoutService.php`:
       * Menambahkan *Automatic Border Artifact Trimming*: mendeteksi kontras tepi 1-2px dari grid divider dan memangkasnya otomatis.
       * *Face & Inner Head Protection Shield*: perlindungan elips area wajah inti (`x=0.50w, y=0.42h, rx=0.24w, ry=0.25h`) dan proteksi tubuh tengah bawah (`y>=0.70h, x=0.18w..0.82w`) sehingga mata, hidung, pipi, bibir, dan dada TIDAK PERNAH bisa termakan transparansi.
       * *Weighted Color Separation*: pembobotan kanal Blue (1.5x) yang membedakan kertas studio bersuhu hangat (yellowish) dengan jilbab/busana bersuhu dingin (cool mauve/grey).
       * *Tolerance Calibration*: toleransi terkalibrasi aman ke 15-16, mencegah kebocoran warna.
       * *Soft Silhouette Anti-Aliasing*: transparansi tepi semi-transparan (alpha 36-40) sehingga kontur rambut/jilbab/bahu halus tanpa efek tangga bergerigi.
       * *Soft Bottom Gradient Fade*: gradien transparansi lembut pada 10% terbawah foto sehingga baju menyatu alami dengan kurva foreground kartu.
     - `database/seeders/DemoDataSeeder.php`: ekstraksi grid 20 pasfoto diperbarui dengan margin inset +3px sehingga seluruh foto profil kader bebas dari garis kisi.
     - Pembersihan cache `storage/app/private/cutouts/` dan regenerasi 20 pasfoto lokal.
  3. **Verifikasi**:
     - Visual QA terverifikasi pada PDF `public/sample-idcard-novi-fixed.pdf`: wajah, mata, pipi, dan jilbab 100% utuh solid, latar belakang transparan sempurna, gedung MPKSDI Layer 1 terlihat bersih tanpa kotak garis abu-abu.
     - Seluruh 180 feature tests lulus 100% (688 assertions). Pint clean.

- 2026-10-07: Halaman Error Kustom Profesional & Elegan (403 Akses Dibatasi/Sertifikat, 404, 419, 500):
  1. **Akar Masalah**:
     - Saat sistem memanggil `abort(403, 'Sertifikat tersedia setelah event selesai.')` atau pengecualian hak akses lainnya, Laravel sebelumnya merender halaman error default minimal tanpa styling, tanpa logo, dan tanpa navigasi kembali.
  2. **Implementasi Sistem Halaman Error Terpadu**:
     - `resources/views/errors/layout.blade.php`: Layout induk konsisten berstandar resmi MPKSDI PDM Karanganyar dengan dual logo Muhammadiyah & MPKSDI, styling latar belakang bersih gradien slate, font Plus Jakarta Sans, dan footer resmi.
     - `resources/views/errors/403.blade.php`: Halaman 403 adaptif cerdas:
       - Mendeteksi konteks pesan otomatis (misalnya penundaan unduh sertifikat, kelulusan, atau hak akses).
       - Menampilkan badge status amber/gold, ikon sertifikat/gembok elegan, judul yang bersahabat (*"Sertifikat Belum Dapat Diunduh"*), dan kotak penjelasan sistem yang jelas.
       - Menyediakan tombol aksi cepat: *"Kembali ke Halaman Sebelumnya"* (`window.history.back()`) dan *"Buka Dashboard"* sesuai role akun (Portal Peserta / Dashboard Admin).
     - Menyiapkan halaman `404.blade.php` (Halaman Tidak Ditemukan), `419.blade.php` (Sesi Kedaluwarsa), dan `500.blade.php` (Kendala Sistem).
  3. **Verifikasi**:
     - Ditambahkan pengujian automated `tests/Feature/ErrorPagesTest.php`. Lulus 100%.

- 2026-10-07: Otomatisasi Penghapusan Latar Belakang (Remove Background / Cutout) Pasfoto Cetak ID Card Massal:
  1. **Akar Masalah Foto Tidak Transparan pada PDF Massal**:
     - Prototipe web sebelumnya (`idcard-generator.html`) menerapkan chroma-key flood-fill berbasis JavaScript Canvas di sisi klien browser (`removeBackgroundCanvas`).
     - Saat panitia mencetak ID Card massal melalui backend Laravel (`/admin/events/{event}/id-cards-batch`) atau peserta mengunduh di portal, foto peserta diambil langsung dari penyimpanan (`Storage::disk('local')`) dan di-encode mentah sebagai data URI JPEG tanpa proses pembersihan background server-side.
     - DomPDF tidak menjalankan skrip JavaScript Canvas klien, sehingga foto pasfoto kader yang berlatar belakang polos (biru, merah, putih/abu) tampil sebagai kotak persegi masif yang menutupi gambar masjid/gedung MPKSDI di Layer 1 (`2.png`).
  2. **Implementasi Layanan `ImageCutoutService`** (`app/Services/ImageCutoutService.php`):
     - Dibuat engine pemrosesan gambar cerdas berbasis PHP GD dengan algoritma flood-fill multi-sample perimeter:
       - Mengambil sampel warna latar belakang di perimeter tepi atas dan sisi atas.
       - Melakukan penelusuran Breadth-First Search (BFS) menggunakan `SplQueue` untuk mendeteksi piksel background terhubung dari tepi berdasarkan batas toleransi warna Euclidean.
       - Mengubah piksel latar belakang menjadi kanal alfa transparan murni (`imagecolorallocatealpha` alfa 127).
       - Menjaga wajah, pakaian, dan badan peserta tetap 100% opak (alpha 0) tanpa terpengaruh oleh warna kulit atau baju.
       - Penskalaan proporsional otomatis (maksimal 600×800 px) untuk efisiensi memori, kecepatan komputasi kilat (~35–50 ms/foto), dan mencegah ukuran berkas PDF membengkak.
       - Menyimpan hasil cutout PNG secara permanen ke cache disk lokal (`cutouts/{hash}.png`) sehingga permintaan cetak berulang berlangsung instan (~0–9 ms).
       - Deteksi aman untuk foto yang sudah memiliki transparansi (PNG dengan alfa channel) dan penanganan graceful fallback ke berkas asli jika berkas bukan gambar valid.
  3. **Integrasi ke Generator PDF ID Card**:
     - `EventOutputController::idCardsBatch`: Menggunakan `ImageCutoutService` untuk setiap foto kader aktif yang diikutsertakan dalam batch PDF.
     - `KehadiranController::idCard`: Menerapkan `ImageCutoutService` pada unduh ID Card perorangan kader di portal.
     - Fallback default photo: Menyiapkan aset transparan `public/aset/ketua-mpksdi-cutout.png` jika kader belum memiliki pasfoto, sehingga foto pengganti pun menyatu alami di atas background kartu.
  4. **Verifikasi & Pengujian**:
     - Ditambahkan uji fitur pada `tests/Feature/IdCardBatchTest.php` mencakup pemotongan latar belakang menjadi transparan, keutuhan badan/wajah, penyimpanan cache, dan integrasi penuh batch cetak PDF DomPDF.
     - Seluruh 178 pengujian PHPUnit lulus hijau (680 assertions).
     - Format kode diverifikasi bersih dengan `vendor/bin/pint`.

- 2026-10-07: Penanganan Orphaned Vite Hot File & Graceful Handling Error 419 (Page Expired):
  1. **Aset CSS/JS Landing & Layout Tidak Memuat**:
     - File `public/hot` tertinggal dari sesi dev sebelumnya sehingga Laravel mengarahkan asset ke `http://[::1]:5173`.
     - Dilakukan penghapusan `public/hot`, rebuild aset produksi `npm run build`, dan penambahan pengaman ukuran inline pada logo MPKSDI navbar & footer di `resources/views/landing/index.blade.php`.
  2. **Penanganan Elegan HTTP 419 Page Expired** (`bootstrap/app.php`, `tests/Feature/AuthTest.php`):
     - Laravel 12 mengonversi `TokenMismatchException` menjadi `HttpException(419)` di `prepareException()` sebelum render callbacks dijalankan, sehingga handler lama tidak terpanggil dan menampilkan layar error hitam-putih mentah 419.
     - Handler diperbarui menangani `HttpException` dengan status code 419 dan `TokenMismatchException`: otomatis redirect back ke referer / `/login`, mempertahankan input form lama, dan menampilkan banner pesan "Pemberitahuan Sesi" yang ramah pengguna.
     - Ditambahkan automated test pada `AuthTest`.
  3. **Tampilan Pasfoto Profil & Avatar Kandidat Peserta** (`resources/views/admin/events/enroll.blade.php`, `resources/views/admin/dashboard.blade.php`):
     - Menggantikan kotak inisial huruf tunggal dengan pasfoto resmi kader 3:4 (`w-9 h-11`) terhubung ke `route('foto.anggota', $k)` pada halaman Enroll Peserta Kegiatan Baitul Arqam.
     - Dilengkapi efek ring highlight saat kartu kandidat dipilih (`isSelected`), lazy loading, dan fallback otomatis ke inisial jika foto belum diunggah atau gagal dimuat.
     - Diterapkan juga pada antrean verifikasi dan pendaftar aktif terbaru di Dashboard Admin.
  4. **Paginasi 20 Kandidat per Halaman & Cross-Page Selection** (`PesertaController::search`, `resources/views/admin/events/enroll.blade.php`, `AdminEventFlowTest`):
     - Mengganti pembatasan statis 50 record menjadi paginasi standar 20 data per halaman (`paginate(20)->withQueryString()`).
     - Menyediakan navigasi halaman `{{ $kandidat->links() }}` di bawah formulir kandidat.
     - Integrasi state Alpine.js dengan `sessionStorage` dan hidden input array dinamis sehingga pilihan kader tidak hilang saat panitia berpindah ke halaman berikutnya (halaman 2, 3, dst).
     - Ditambahkan automated test `test_admin_enroll_search_is_paginated_to_twenty_items_per_page`.
  5. **Filter Cabang (PCM) pada Enroll Peserta** (`PesertaController::search`, `resources/views/admin/events/enroll.blade.php`, `AdminEventFlowTest`):
     - Menambahkan dropdown pilihan cabang (`cabang`) yang memuat seluruh 17 PCM resmi se-Karanganyar (`config('arqam.opsi_form.cabang_pcm')`).
     - Mendukung filtering instan `onchange="this.form.submit()"`, kombinasi pencarian kata kunci + cabang, chip badge PCM pada tiap kartu kader (`PCM Colomadu`, dll), indikator wilayah di header, dan tombol reset.
     - Ditambahkan automated test `test_admin_enroll_search_filters_by_cabang`.

- 2026-10-05: Redesain Implementasi Generator ID Card Baitul Arqam Berbasis 3 Asset PNG (1063 × 1535 px B4 Canvas):
  1. **Source of Truth Desain Berbasis 3 Berkas PNG Resmi (`1.png`, `2.png`, `3.png`)**:
     - Menghentikan pembuatan ulang motif batik/curve/frame via CSS tiruan. Artwork PNG asli dijadikan acuan absolut (Source of Truth).
     - Berkas aset: `1.png` (Desain Belakang ID Card), `2.png` (Background Depan ID Card), dan `3.png` (Foreground/Overlay Depan ID Card dengan transparansi atas).
     - Rasio kanvas absolut 1063 × 1535 px dipertahankan di seluruh preview dan print engine tanpa stretch/squash.
  2. **Arsitektur Layer Sisi Depan (Front Card)**:
     - Layer 1 (`2.png`, z-index: 1): Background lanskap Gedung MPKSDI PDM Karanganyar & masjid biru dengan ornamen batik.
     - Layer 2 (`foto peserta`, z-index: 2): Portrait peserta (cutout natural tanpa bingkai kotak). Dikalibrasi presisi dengan pergeseran optimal ke sisi kanan:
       - Parameter wrapper: `left: 36%`, `top: 14.5%`, `width: 58%`, `height: 52%`, alignment: `right bottom` (`photo_x = 525-530px`, `photo_y = 226px`, ujung kanan `999px`).
       - Menggunakan *cutout* solid alami 100% tanpa crop atau feathering di sisi kanan/kiri (photo_x = 530px, photo_y = 226px), mempertahankan bentuk asli bahu/lengan peserta.
       - Wajah berada di horizontal canvas ~70% (`face center X ~760px`, `Y ~390-420px`), kepala/peci membingkai lingkaran kuning halo di latar belakang dan memiliki negative space di bawah batik atas.
       - Kompleks bangunan (gedung, masjid kubah biru) di sisi kiri tampak 100% utuh dan bebas halangan.
       - Badan bagian bawah masuk secara natural di balik kurva wave putih foreground `3.png` pada dada bawah/upper torso (`y ~940-950px`), menghasilkan efek 3D cutout alami tanpa menutupi leher/dagu atau memperlihatkan pinggang berlebih.
       - Teruji sempurna pada 3 varian foto: Pria pakai peci (Ahmad Fathoni, S.T.), Wanita berhijab (Siti Nurjanah, S.Pd.I.), dan Pria tanpa peci (Bambang Kusuma Wardana, M.M.).
     - Layer 3 (`3.png`, z-index: 3, pointer-events: none): Foreground kurva putih dengan 3 rounded box kuning resmi dan aksen batik sudut kanan bawah.
     - Layer 4 (`dynamic text`, z-index: 4): Dynamic value langsung di atas kotak artwork tanpa label/icon tiruan tambahan:
       - Posisi horizontal teks diselaraskan ke `left: 20.2%` (`x = 215px`, padding 38px dari garis pemisah icon `x = 177px`), melenyapkan ruang kosong canggung selebar 80px yang sebelumnya memisahkan teks dari ikon:
         - Kotak 1 (Status): Center Y = 1001 (optikal tengah kotak putih, CSS `top: 62.4%`), Montserrat 800 uppercase, warna `#063A60`.
         - Kotak 2 (Nama Peserta): Center Y = 1146 (optikal tengah kotak putih, CSS `top: 71.85%`), Montserrat 800, warna `#063A60`, dengan penskalaan responsif otomatis (<=18 kar: 38px, 19–28 kar: 30px, 29–36 kar: 26px, >36 kar: 22px).
         - Kotak 3 (NBM): Center Y = 1291 (optikal tengah kotak putih, CSS `top: 81.3%`), Montserrat 700, warna `#063A60`.
  3. **Arsitektur Sisi Belakang (Back Card)**:
     - Layer 1 (`1.png`): Background artwork penuh.
     - Layer 2 (Dynamic Content):
       - QR Code presensi scannable level H (warna `#063A60`, 230 × 230 px) diletakkan presisi di antara 4 corner brackets di atas label "QR ABSENSI" dengan quiet-zone putih murni.
       - Teks nilai peserta langsung diisi ke dalam 5 kotak putih yang telah tersedia di artwork: Nama Lengkap (`top: 49.9%`), NBM (`top: 57.07%`), Status (`top: 64.10%`), Kegiatan (`top: 71.07%`), Waktu & Tempat (`top: 78.11%`) dengan font Inter / Plus Jakarta Sans 700 `#063A60`.
  4. **Pembaruan Aplikasi Generator Web & Templating Laravel**:
     - `public/idcard-generator.html` & `scratch/idcard-b3-package/idcard-baitul-arqam-b3.html`: Generator interaktif lengkap dengan Mode Tunggal & Massal (Batch), preset visual QA 3 kader uji (Peci, Hijab, Non-Peci), direct Canvas 2D export 1063 × 1535 px (1x) dan 2126 × 3070 px (2x HQ), dan batch downloader.
     - `idcard-batch-pdf.blade.php`, `portal/idcard-pdf.blade.php`: Templating DomPDF diselaraskan ke koordinat terkalibrasi kanvas presisi 265.75 × 383.75 pt (1063 × 1535 px ratio).
  5. **Pengujian & Verifikasi**:
     - `IdCardBatchTest` lulus 100%, Laravel Pint passed 100%.
     - Visual QA front card terverifikasi pada `public/sample-idcard-front.png`, back card pada `public/sample-idcard-back.png`, dan DomPDF pada `public/sample-idcard.pdf`.
     - File zip portabel `public/baitul_arqam_idcard_b3_package.zip` (6.6 MB) siap pakai.

- 2026-10-04: Penyempurnaan Operasional Baitul Arqam (Fitur Cabut/Batal Presensi, Siaran Pengingat Acara WhatsApp, dan Toleransi Status RTL Disetujui):
  1. **Fitur Batalkan / Cabut Presensi (Void / Undo Attendance)** (`AbsensiController::destroy`, `scan.blade.php`, `web.php`):
     - Menambahkan endpoint `DELETE /admin/events/{event}/sesi/{sesi}/absensi/{absensi}` yang menghapus record presensi yang keliru di-scan atau salah input manual.
     - Otomatis memicu `AbsensiService::recalculateAttendance` dan `NilaiService::hitungUlangPeserta`, sehingga persentase kehadiran dan status kelulusan peserta ter-update secara instan.
     - Antarmuka riwayat kehadiran pada `scan.blade.php` dilengkapi tombol batalkan interaktif dengan konfirmasi nama kader dan pembaruan dinamis.
  2. **Pengingat Acara WhatsApp Gateway (Broadcast & Individual Reminder)** (`PesertaController::remind`, `PesertaController::remindBatch`, `WhatsAppNotificationService`, `show.blade.php`):
     - Memperbaiki pembacaan atribut `$event->nama_event` pada `WhatsAppNotificationService::pesanPengingatEvent`.
     - Menambahkan fitur broadcast pengingat acara massal (`POST /admin/events/{event}/peserta/remind-wa-batch`) pada Tab 1 (Pendataan Peserta) untuk menyiarkan pesan pengingat jadwal, tanggal, dan lokasi ke seluruh kader aktif sekaligus.
     - Menambahkan tombol aksi pengingat perorangan (`POST /admin/events/{event}/peserta/{peserta}/remind-wa`) di setiap baris peserta dengan fallback URL WhatsApp Web saat gateway belum aktif.
     - Penanganan alert `session('warning')` dan `session('wa_url')` ditambahkan pada `app.blade.php`.
  3. **Penyelarasan Status Progres RTL di Portal Kader** (`Portal\DashboardController.php`):
     - Memperbarui pengecekan progres RTL dari `where('status', 'terkirim')` menjadi `whereIn('status', ['terkirim', 'disetujui'])` agar kader yang tugasnya telah disetujui admin tetap melihat chip progres "RTL Terkirim" berwarna hijau.
  4. **Pengujian & Verifikasi**:
     - Ditambahkan 1 unit test di `PortalEvaluationTest`, 1 unit test di `AbsensiTest`, dan suite baru `PengingatWaEventTest` (3 feature tests).
     - Seluruh 173 automated feature tests lulus 100% (664 assertions), Laravel Pint passed 100%, Vite production build clean.

- 2026-10-04: Redesain Total ID Card / Name Tag B3 Baitul Arqam Muhammadiyah Karanganyar (85 × 110 mm, Standar Cetak 300 DPI, Mode Massal, dan Template PDF):
  1. **Redesain Komprehensif Generator Web (`scratch/idcard-b3-package/idcard-baitul-arqam-b3.html`, `public/idcard-generator.html`, `baitul_arqam_idcard_b3_package.zip`)**:
     - Format fisik insert name tag B3 vertikal presisi 85 × 110 mm (rasio 85:110).
     - Palet warna resmi: Deep Navy (`#063A60`), Primary Blue (`#0869A7`), Bright Blue (`#0B78B5`), Gold (`#F5C33B`), Soft Gold (`#FFD966`), White (`#FFFFFF`), Dark Text (`#102D45`).
     - Aksen Batik Nusantara resmi: Berbasis PNG transparan resolusi tinggi (Kawung/Surya) dengan opacity 16–18%, diletakkan di sudut kanan atas dan bingkai belakang. Mengeliminasi motif CSS murahan/artificial.
     - Foto Peserta: Cutout portrait dengan visual weight 35–40% di sisi kanan kartu, masking halus menyatu ke pembatas diagonal emas tanpa memotong kepala atau menabrak nama.
     - Hirarki Muka (Front): Level 1 Nama Peserta (Montserrat 900) dengan penskalaan responsif otomatis (<=18 kar, 19–26 kar, 27–35 kar, >35 kar), Level 2 Baitul Arqam 2026, Level 3 Status Pill Badge (Gold), Level 4 NBM, Level 5 Identitas MPKSDI PDM Karanganyar.
     - Hirarki Belakang (Back): Berfokus pada Presensi Digital + QR Code scannable (level H) dengan quiet-zone putih murni tanpa ornamen latar belakang, kode token, grid data peserta ringkas editorial (bukan tabel database), serta kutipan resmi "Fastabiqul khairat".
     - Fitur Lengkap: Mode Tunggal (Single) + Mode Massal (Batch) 5 kader demo, navigasi antar-peserta, pengujian cepat responsif nama panjang, ekspor PNG 300 DPI (1004 × 1299 px), dan print engine `@page { size: 85mm 110mm; margin: 0; }`.
  2. **Penyelarasan Template Cetak PDF Laravel (`idcard-batch-pdf.blade.php`, `portal/idcard-pdf.blade.php`, `EventOutputController.php`, `KehadiranController.php`)**:
     - Menghilangkan bug cetak kartu kecil di pojok A4; seluruh ekspor PDF disetel ke ukuran presisi `85mm × 110mm` (`[0, 0, 240.945, 311.811]` pt).
     - Cetak massal menghasilkan halaman individual 85 × 110 mm per kartu (Page 1 Depan P1, Page 2 Belakang P1, Page 3 Depan P2, Page 4 Belakang P2, dst) tanpa label teks di luar artwork.
  3. **Verifikasi**:
     - Seluruh 168 feature tests lulus 100% (647 assertions), Laravel Pint passed, Vite production build clean.

- 2026-10-04: Ekstraksi & Integrasi Pasfoto Profil Asli Peserta dari foto1.png dan foto2.png ke Seeder & Database:
  1. **Ekstraksi Grid Pasfoto Resmi 3:4 (`database/seeders/assets/kader/kader-01.jpg` s.d. `kader-20.jpg`)**:
     - Membaca 2 berkas kolase foto `foto1.png` dan `foto2.png` (masing-masing 1536×1024 px berisi grid 2 baris × 5 kolom = 10 wajah).
     - Mengidentifikasi koordinat garis pembatas (border divider) antarsel dan memotong 20 pasfoto portrait rasio standar 3:4 (300×400 px) beresolusi tinggi dengan proporsi kepala, peci/hijab, dan pundak yang presisi.
     - Komposisi gender pasfoto (11 Pria berpeci/batik/jas dan 9 Wanita berhijab/pin matahari) terbukti 100% selaras dengan 20 data kader aktif di `DemoDataSeeder`.
  2. **Integrasi Otomatis pada `DemoDataSeeder.php`**:
     - Menambahkan method `ensureKaderPhotosExist()` yang otomatis menyalin pasfoto dari asset folder `database/seeders/assets/kader/` ke storage lokal `storage/app/private/anggota/foto/` (dengan fallback pemotongan dinamis via GD jika asset belum ada).
     - Menugaskan foto spesifik (`anggota/foto/kader-XX.jpg`) yang relevan dengan profesi dan gender masing-masing peserta pada `seedAnggota()`.
  3. **Pembaruan Database Aktif**:
     - Seluruh 25 kader dalam database langsung dimutakhirkan dengan path foto yang valid, sehingga pasfoto langsung tampil pada Dashboard Portal, Profil, ID Card PDF, Lembar Biodata Kader, dan Daftar Peserta Admin.
  4. **Verifikasi**:
     - 168 tests passed 100% (647 assertions), Laravel Pint passed 100%.

- 2026-10-03: Peningkatan Vital Subsistem Baitul Arqam (Bobot Penilaian Dinamis, Berita Acara Yudisium PDF Resmi, dan Scanner Presensi Luar Jaringan):
  1. **Pengaturan Bobot Penilaian Dinamis per Event (`database/migrations/2026_10_03_000001_add_bobot_to_events_table.php`, `Event.php`, `NilaiService.php`, `EventController.php`)**:
     - Ditambahkan kolom `bobot_kognitif`, `bobot_psikomotor`, `bobot_afektif`, dan `bobot_keaktifan` pada tabel `events`.
     - Ditambahkan method `Event::getBobotKomposisi(): array` dengan fallback transparan ke `config('arqam.bobot')`.
     - `NilaiService::hitungNilaiAkhirDanRanking` kini dinamis menggunakan bobot per-event bila ditentukan, sehingga tiap jenis pelatihan (Baitul Arqam Pimpinan/Ideologi 45:25:30 vs Baitul Arqam AUM/Praktik Ibadah 30:45:25 vs Standar MPKSDI 40:35:25) dapat memiliki kurikulum pembobotan yang presisi.
     - Form event (`admin/events/form.blade.php`) dilengkapi accordion Alpine.js interaktif dengan 4 preset cepat (Standar MPKSDI, AUM/Ibadah, Ideologi/Kepemimpinan, Reset).
  2. **Dokumen Cetak PDF Resmi: Berita Acara Sidang Pleno Yudisium Kelulusan (`GET /admin/events/{event}/berita-acara`, `admin/events/berita-acara-pdf.blade.php`)**:
     - Dokumen legal format A4 resmi berkop surat Majelis Pembinaan Kader dan Sumber Daya Insani (MPKSDI) PDM Karanganyar.
     - Memuat identitas sidang pleno, rekapitulasi statistik kelulusan (lulus murni, bersyarat, tidak lulus), statistik capaian nilai (tertinggi, terendah, rerata), daftar Dewan Instruktur/Narasumber, daftar 5 Lulusan Terbaik (Kader Berprestasi), dan blok 3 penandatangan sah (Master of Training, Imam Training, dan Ketua MPKSDI H. Zainudin Ahpandi, S.E. NBM 883.693).
     - Terintegrasi langsung di Tab 6 Dashboard Event (`admin/events/show.blade.php`) dalam grid kartu unduhan dokumen resmi.
  3. **Ketahanan Luar Jaringan (Offline-Resilient / Local Queue) Scanner Presensi QR (`resources/views/admin/absensi/scan.blade.php`)**:
     - Mengatasi kendala koneksi seluler/Wi-Fi yang fluktuatif di lokasi pelosok atau ruang diklat dengan deteksi otomatis status jaringan (`navigator.onLine`, event `online`/`offline`).
     - Antrean presensi lokal otomatis tersimpan di `localStorage` (`arqam_attendance_queue_{sesi_id}`) saat perangkat offline atau request presensi gagal karena koneksi terputus.
     - Indikator visual real-time status jaringan (Hijau: Online, Kuning: Luar Jaringan / Offline) beserta banner counter antrean tertunda dan tombol sinkronisasi otomatis/manual begitu internet pulih.
  4. **Pengujian & Verifikasi**:
     - Ditambahkan 4 automated feature test di `tests/Feature/EventBobotAndBeritaAcaraTest.php`.
     - Seluruh 165 automated tests lulus 100% (643 assertions), Laravel Pint clean, Vite production build clean.

- 2026-09-30: Penyelarasan Penuh Data Wilayah Desa/Kelurahan se-Kabupaten Karanganyar & Penambahan Desa Kaling (`config/arqam.php`):
  1. **Akar Masalah**: Pendaftar asal Desa Kaling, Kecamatan Tasikmadu mendapati bahwa opsi "Kaling" tidak muncul pada dropdown formulir pendaftaran (`/daftar`). Pada konfigurasi `config/arqam.php`, Desa Kaling sebelumnya terselip secara keliru di Gondangrejo dan Kerjo, sedangkan Kecamatan Tasikmadu kehilangan opsi Kaling dan Pandeyan.
  2. **Solusi & Penyelarasan Resmi**: Seluruh daftar 17 kecamatan dan 177 desa/kelurahan disinkronkan 100% presisi sesuai data resmi Kementerian Dalam Negeri (Permendagri No. 137 Tahun 2017 & No. 72 Tahun 2019).
  3. **Kecamatan Tasikmadu**: Kini lengkap memuat 10 desa resmi (Buran, Gaum, Kalijirak, Kaling, Karangmojo, Ngijo, Pandeyan, Papahan, Suruh, Wonolopo) terurut alfabetis.
  4. **Pengujian**: Ditambahkan automated test `test_registration_supports_desa_kaling_in_tasikmadu` di `AnggotaRegistrationTest.php`. Seluruh 161 automated tests lulus 100% (630 assertions), Pint clean, Vite build clean.

- 2026-09-29: Fitur Live PPT / Slideshow Evaluasi & Yudisium (`admin/events/presentasi` & Tab 6 Rekap):
  1. **Live Presentation Mode (16:9)**: Dibuat controller khusus `App\Http\Controllers\Admin\PresentasiController` dan view `resources/views/admin/events/presentasi.blade.php` untuk menampilkan slide presentasi interaktif layar penuh berformat 16:9 saat penutupan dan yudisium perkaderan.
  2. **Navigasi & Kontrol Lengkap**: Mendukung remote clicker PPT / keyboard panah (kanan/kiri, spasi, enter), tombol fullscreen (`F`), auto-play timer (8 detik per slide), drawer daftar slide (`O`/`M`), serta ekspor slide ke PDF (`Ctrl+P`).
  3. **Cakupan 9 Slide**: Cover judul resmi MPKSDI, demografi & profil kader (gender & sebaran PCM), presensi per sesi, evaluasi kognitif CAT (pretest vs posttest & N-Gain), evaluasi psikomotorik (praktik ibadah) & afektif (akhlak), keputusan yudisium kelulusan, penganugerahan 5 kader teladan (top leaderboard), rencana tindak lanjut (RTL), dan penutup/pesan gerakan KH Ahmad Dahlan.
  4. **Integrasi Tab 6**: Menambahkan kartu emas "Live PPT / Slideshow" di grid dokumen unduhan Tab 6 Rekapitulasi dengan tombol langsung membuka presentasi di tab baru.

- 2026-09-29: Relokasi Sesi Berjalan & Leaderboard Top 5 ke Tab Kronologis (`admin/events/show.blade.php`):
  1. **Relokasi Kartu Sesi Berjalan**: Dipindahkan dari area dashboard atas ke dalam **Tab 2: Jadwal Acara, Berkas Modul & Presensi (Absensi)**, tepat di bawah judul tab sebagai spotlight interaktif dengan tombol cepat *Scanner QR* dan *Lembar Presensi PDF*.
  2. **Relokasi Leaderboard Teratas (Top 5)**: Dipindahkan dari area dashboard atas ke dalam **Tab 6: Rekapitulasi Akhir, Kelulusan & Unduh Dokumen Resmi**, diposisikan tepat di atas pratinjau tabel peringkat nilai lengkap.
  3. **Dampak UI/UX**: Area atas dashboard kini super ramping (hanya memuat hero card ringkas + 4 strip KPI) sehingga bar navigasi 6 tab langsung tampil tepat di viewport (*above-the-fold*) tanpa perlu scroll.
  4. **Penyempurnaan Seeder Presensi PKU (`DemoDataSeeder.php`)**: Presensi 12 peserta nakes disebar proporsional pada 5 sesi (100% lulus murni, 80% lulus bersyarat, 60% tidak lulus) sehingga status kelulusan di leaderboard dan tabel rekapitulasi mencerminkan variasi realistis.

- 2026-09-29: Kompaktifikasi Dashboard Pelatihan & Data Seeder Uji Coba Lengkap:
  1. **Kompaktifikasi Layout & Ergonomi Penggunaan (`admin/events/show.blade.php`)**:
     - Memangkas tinggi vertikal hero card, grid 4 KPI metrics, dan panel spotlight/leaderboard sebesar 30-40% sehingga navigasi tab dan konten utama langsung terlihat (above-the-fold) tanpa scroll berlebih.
     - Menjadikan Tab Navigation Bar **Sticky (`sticky top-2 z-20`)** dengan latar `bg-white/95 backdrop-blur-md` sehingga admin dapat berpindah antar-langkah pelaksanaan kapan saja saat menelusuri data tabel yang panjang.
     - Menambahkan fitur interaktif **Live Search Filter Peserta** (`x-model="searchPeserta"`) pada Tab 1 (Pendataan Peserta) untuk menyaring nama kader, NBM, atau cabang secara instan tanpa reload halaman.
     - Merampingkan kartu sesi jadwal, panel regu outbound, kartu ujian CBT, dan tabel rekapitulasi nilai akhir.
  2. **Penyempurnaan Seeder Data Uji Coba (`DemoDataSeeder.php`)**:
     - Mengisi data event "Baitul Arqam Tenaga Kesehatan RS PKU Muhammadiyah Karanganyar" dengan data lengkap siap uji coba: status `berlangsung`, 5 sesi agenda, 12 peserta nakes, 3 regu outbound (Ibnu Sina, Ar-Razi, Al-Zahrawi), sesi CBT pretest (tutup) & posttest (aktif), rekam absensi sesi 1-2, log keaktifan, penilaian rubrik ibadah & afektif, berkas tugas RTL, serta kalkulasi nilai akhir resmi `NilaiService::hitungUlang` yang menghasilkan leaderboard hidup.
  3. **Verifikasi**: 157 automated feature tests lulus 100% (613 assertions), Laravel Pint passed, Vite production build clean.
- 2026-09-29: Redesain Hierarki & Tab Navigasi Alur Pelaksanaan Baitul Arqam (`admin/events/show.blade.php`):
  1. **Struktur Hierarki Informasi Atas**:
     - Mengeliminasi 11 deretan tombol/pills tercecer di bawah kartu hero; memindahkan masing-masing aksi ke dalam tab kronologisnya yang relevan.
     - Kartu Hero bersih & institusional memuat status, tanggal, lokasi, kuota, nama event, subjudul resmi, dan tombol enroll/edit/hapus.
     - Grid 4 KPI Metrics: Kader Peserta, Jadwal Sesi, Tes Kognitif, Rerata Nilai & Kelulusan.
     - Spotlight Sesi Berlangsung (Countdown/waktu, narasumber, live bar absensi, tombol cepat Buka Scanner QR & Lembar Presensi PDF) + Leaderboard Top 5 Real-Time.
  2. **Tab Navigasi Alur Kronologis Pelaksanaan Perkaderan (6 Langkah)**:
     - **Tab 1 (1. Pendataan Peserta)**: Enroll peserta, cetak ID card massal A4 grid 8 kartu, komposisi gender & cabang, daftar peserta aktif, dan penugasan fasilitator/instruktur.
     - **Tab 2 (2. Jadwal & Presensi)**: Agenda sesi urut, modul materi & slide presentasi, live bar kehadiran per sesi, scanner QR, lembar daftar hadir PDF, dan modal tambah sesi.
     - **Tab 3 (3. Keaktifan & Outbound)**: Pengacak kelompok outbound berimbang gender/PCM, cetak PDF distribusi regu lapangan, dan pencatatan poin keaktifan forum.
     - **Tab 4 (4. Asesmen & CBT)**: 4 kartu asesmen terpadu (Pretest CBT, Posttest CBT & Kloning Soal, Input Nilai Praktik Psikomotor Lapangan, dan Instrumen Afektif & Angket).
     - **Tab 5 (5. Tugas Akhir RTL)**: Monitoring pengumpulan rencana aksi kader, butir pertanyaan RTL, antrean penyerahan tugas, dan review instruktur.
     - **Tab 6 (6. Rekap & Unduh Data)**: 4 kartu unduhan utama (Unduh Batch Sertifikat PDF 2 sisi, Unduh Laporan PDF berkop resmi, Ekspor Excel XLSX, dan Buka Ranking Lengkap) disertai pratinjau tabel peringkat Top 10 beserta status kelulusan terpadu.
  3. **Pengujian & Verifikasi**: 157 tests lulus 100% (613 assertions), Pint clean, Vite production build clean.
- 2026-09-28: Penguatan & Penyempurnaan Komprehensif Subsistem Baitul Arqam (BA):
  1. **Dashboard Khusus Pelatihan (Training Command Center / Mission Control)**:
     - Didesain ulang di `resources/views/admin/events/show.blade.php` dengan Quick Actions Bar (Aksi Cepat), Metrik Real-Time (Total Peserta, Rasio L/P, Sebaran PCM, Progres Tes Pre/Post, Kelengkapan Afektif/RTL/Psikomotor, Rerata Nilai), Spotlight Sesi Berlangsung (Countdown waktu, instruktur, tombol absensi instan), Leaderboard Top 5 Real-Time, serta Tab Navigasi Terpadu (Sesi & Jadwal, Peserta & ID Card, Evaluasi & Kelulusan, Dynamic Outbound).
  2. **Modul Berkas Materi Sesi Perkaderan**:
     - Ditambahkan kolom `file_materi`, `link_materi`, `deskripsi` pada tabel `event_sesi`.
     - Admin dapat mengunggah slide presentasi (PDF/PPTX) dan menyematkan tautan materi luar saat menjadwalkan sesi.
     - Peserta aktif dapat mengunduh slide materi dan membuka tautan langsung melalui portal kehadiran (`/portal/kegiatan/{event}/kehadiran`).
  3. **Cetak ID Card Panitia Massal (Batch Printing A4 Grid)**:
     - Fitur cetak kartu tanda peserta massal format A4 (8 kartu per lembar lengkap dengan garis potong potong presisi, QR code verifikasi kehadiran, foto, dan asal PCM) via `GET /admin/events/{event}/id-cards/batch`.
  4. **Kloning Bank Soal Antar Event**:
     - Memungkinkan panitia menduplikasi seluruh soal pretest/posttest dari pelatihan Baitul Arqam sebelumnya ke pelatihan baru dengan 1 klik via `POST /admin/events/{event}/soal/clone`.
  5. **Sertifikat Resmi 2 Sisi & Cetak Sertifikat Massal**:
     - Halaman Depan: Sertifikat resmi lengkap kop, nomor seri terverifikasi hash SHA-256, tanda tangan Ketua MPKSDI H. Zainudin Ahpandi, S.E. & Master of Training (MoT), serta QR validasi keaslian.
     - Halaman Belakang: Transkrip Materi Pelatihan dengan rincian Jam Pelajaran (JP) dan nilai capaian aspek (Kognitif, Afektif, Psikomotor, Keaktifan).
     - Fitur Cetak Massal: Seluruh sertifikat peserta yang dinyatakan lulus dapat diunduh sekaligus dalam 1 berkas PDF via `GET /admin/events/{event}/sertifikat/batch`.
  6. **Cetak PDF Lembar Rekap Kelompok Outbound**:
     - Fitur ekspor lembar distribusi kelompok outbound per regu (nama, gender, PCM) untuk pegangan instruktur lapangan via `GET /admin/events/{event}/outbound/pdf`.
  7. **Transparansi Rekam Keaktifan Peserta di Portal**:
     - Peserta dapat memantau perolehan poin keaktifan (tanya, jawab, kultum, imam, adzan, outbound) beserta rincian forum yang dicatat fasilitator secara transparan.
  8. **Integrasi Kelulusan Terpadu & Sertifikat Resmi Portal**:
     - `NilaiService::tentukanStatusKelulusan`: Mengintegrasikan 3 pilar syarat kelulusan perkaderan secara harmonis: Kehadiran (>= 100% mutlak, 75%-99% bersyarat, < 75% tidak lulus), Posttest Selesai (jika ada sesi posttest pada event), dan RTL Terkirim/Disetujui (jika event memiliki instrumen RTL).
     - Jika kehadiran 100% namun posttest belum diselesaikan atau RTL belum dikumpulkan, status kelulusan peserta otomatis berstatus 'Lulus Bersyarat' sampai tugas dilengkapi.
     - Alur reaktif: Pengumpulan RTL oleh peserta (`RtlController::submit`) dan tinjauan oleh admin (`RtlAdminController::review`) langsung memicu rekalkulasi status kelulusan peserta secara instan.
     - Sertifikat Portal: Endpoint `portal.sertifikat.download` dimutakhirkan menggunakan template lanskap 2 sisi resmi (Halaman 1: Sertifikat ber-QR verifikasi dinamis; Halaman 2: Transkrip Materi Pelatihan resmi & rincian JP).
  9. **Pengujian & Verifikasi**:
     - 157 tests lulus 100% (613 assertions). Pint clean, Vite production build clean.
- 2026-09-16: Perbaikan Tuntas Bug Validasi "validation.in" & Bahasa Indonesia Form Pendaftaran:
  1. Akar Masalah "validation.in":
     - `pendidikan_terakhir`: dropdown form mengirim kode jenjang (`SD`, `SMP`, `SMA`, `D3`, `S1`, dll), namun validasi dinamis sebelumnya memaksakan `Rule::in` ke display label panjang dari database (`S1 / D4 (Sarjana)`).
     - `pcm`: dropdown form menyediakan opsi `Lainnya` (Luar Karanganyar), namun `form_pertanyaan.opsi` hanya memuat 17 nama kecamatan Karanganyar.
     - Ketiadaan paket bahasa `lang/id/validation.php`: saat rule `in` gagal, Laravel mencetak string mentah `"validation.in"` alih-alih pesan bahasa Indonesia.
  2. Solusi & Perbaikan:
     - `FormPertanyaan::applyStandardValidationRules`: `pendidikan_terakhir` dan `jenis_kelamin` dikecualikan dari penimpaan opsi mentah (`skipOpsiValidation`), dan opsi `pcm` otomatis menoleransi `Lainnya`.
     - `AnggotaRegistrationRequest`: ditambahkan pesan ramah bahasa Indonesia untuk seluruh aturan `*.in`.
     - Bahasa Aplikasi: diterbitkan `lang/id/validation.php` dan disetel fallback locale `en` di `config/app.php`.
  3. Pengujian: 142 tests lulus 100% (576 assertions), Pint clean, Vite production build clean.
- 2026-09-16: Sinkronisasi Penuh Opsi Pilihan Form Pertanyaan (Checkbox / Dropdown) ke Formulir & Profil:
  1. Helper `FormPertanyaan::opsi($kode, $default)`: membaca daftar opsi hasil edit admin di `/admin/form-kader` langsung dari database `form_pertanyaan.opsi`, dengan fallback aman ke `config('arqam.opsi_form')`.
  2. Rendering dinamis di `register.blade.php` & `portal/profile.blade.php` untuk seluruh opsi: `keaktifan_ortom`, `keaktifan_muhammadiyah`, `kemampuan_baca_quran`, `hafalan_quran`, `aktivitas_sholat_masjid`, `aktivitas_kajian_agama`, `jumlah_buku_agama`, `langganan_suara_muhammadiyah`, `lembaga_zis`, `pcm`, `status_pernikahan`, dan `bahasa_dikuasai`.
  3. Validasi dinamis server-side (`applyStandardValidationRules`): `Rule::in` otomatis disesuaikan dengan opsi database terkini untuk select dan checkbox array sehingga item baru yang ditambahkan admin tidak ditolak validasi.
  4. Pengujian: 140 tests lulus 100% (568 assertions), Pint clean, Vite build clean.
- 2026-09-16: Implementasi Mekanisme Hapus Data Kader:
  1. Route `DELETE /admin/anggota/{anggota}` dengan controller action `AdminAnggotaController::destroy`.
  2. Proteksi integritas data perkaderan: jika kader sudah terdaftar di kegiatan Baitul Arqam (`eventPeserta`), penghapusan ditolak untuk melindungi riwayat nilai dan sertifikat (disarankan nonaktifkan).
  3. Transaksi database: melepaskan relasi organisasi pivot, menghapus berkas pasfoto fisik di storage, menghapus record anggota, dan menghapus akun login `users` terkait.
  4. Otorisasi bertingkat: Admin Daerah memiliki hak akses penuh, Admin Cabang hanya dapat menghapus kader di wilayah PCM-nya (`authorizeCabang`).
  5. UI: tombol hapus merah dengan dialog konfirmasi JavaScript ganda di tabel pangkalan data (`admin/anggota/index.blade.php`) dan halaman profil kader (`admin/anggota/show.blade.php`).
  6. Pengujian: 139 tests lulus 100% (563 assertions), Pint clean, Vite production build clean.
- 2026-09-16: Integrasi Sinkronisasi Editor Formulir Dinamis (`/admin/form-kader`) ke Formulir Pendaftaran (`/daftar`) & Profil Portal:
  1. Helper methods dinamis pada `FormPertanyaan`: `isWajib()`, `isAktif()`, `label()`, `bantuan()`, `placeholder()`, dan auto-cache clearing saat save/delete.
  2. Request validation dinamis (`FormPertanyaan::applyStandardValidationRules`): Field standar yang di-toggle `is_wajib = true` otomatis menjadi `required` di `AnggotaRegistrationRequest` dan `ProfileController`, dengan custom message bahasa Indonesia. Jika di-set `is_aktif = false`, field menjadi `nullable` dan disembunyikan dari antarmuka.
  3. UI `/daftar` & `/portal/profile`: Label, tanda bintang merah `*` wajib, teks `(Opsional)`, placeholder, dan bantuan teks kini membaca konfigurasi `FormPertanyaan` langsung secara reaktif. Client-side validation Alpine.js di Tab 1 ikut memeriksa field dinamis yang di-wajibkan.
  4. Pengujian: 135 tests lulus 100% (547 assertions), Pint clean, Vite production build clean.
- 2026-09-16: Changelog `/updates` diperbarui ke v2.3.1 untuk validasi server-side pertanyaan custom aktif (wajib, tipe, opsi). Field standar tetap dirender hardcode; pengaturan standar belum terhubung ke form publik/profil. Jangan mengklaim integrasi Google Forms penuh selesai. Filter `is_custom` dipertahankan untuk mencegah duplikasi field inti.
- 2026-09-16: Rombak Final Desain PDF CV/Biodata Kader → Corporate Executive Profile (1 Lembar Tepat):
  - Template `resources/views/pdf/biodata-kader.blade.php` ditulis ulang dari nol.
  - Konsep "corporate executive dossier": kop resmi (logo MPKSDI kiri, Muhammadiyah kanan), divider navy-gold, doc-bar judul dokumen dengan nomor registrasi.
  - **Pattern batik SVG**: diambil langsung dari `public/aset/batik-web-corner.svg` (identik komponen `batik-corner.blade.php`), ditempatkan di sudut kanan atas & kiri bawah.
  - **Watermark logo MPKSDI** ultra-subtle di tengah halaman (opacity 4.5%).
  - **Sidebar kiri (~166px)**: pasfoto, nama, NIK, badge status (aktif/nonaktif/pending berwarna), data pribadi (TTL, gender, nikah, ayah, ibu), kontak & domisili, pendidikan & bahasa, QR verifikasi.
  - **Panel kanan**: 4 seksi dengan header navy + aksen kuning — I. Amanah & Profesi, II. Keagamaan & Kemasjidan (split 2 kolom), III. Rekam Jejak BA & keterangan custom, IV. Lembar Pengesahan.
  - `PAGE_COUNT: 1` terverifikasi via DomPDF langsung.
  - 131 tests passed, Pint clean, npm build OK. Commit: `4dea35c`.

  1. Eliminasi "Database-Generated Slop":
     - Menghilangkan struktur tabel kotak kaku, garis zebra kuitansi, dan blok hitam pekat yang memberatkan visual.
     - Menghapus ornamen sudut yang sebelumnya menabrak teks kop surat, menggantikannya dengan watermark resmi logo MPKSDI ultra-subtle (transparansi ~3.5% baked alpha) yang bersih dan tidak mengganggu keterbacaan data.
  2. Arsitektur Executive 2-Column Resume:
     - **Sidebar Kiri (~33%)**: Wadah berlatar abu-abu halus (`#f8fafc`) memuat pasfoto 3×4 formal, nama, NIK, badge status kader, data pribadi/keluarga (TTL, gender, nikah, ayah, ibu), kontak & domisili, riwayat pendidikan & bahasa, serta QR Code validasi keaslian dokumen di bagian dasar.
     - **Kanvas Utama Kanan (~67%)**: Judul seksi bernuansa Deep Ocean Blue (`#002b40`) dengan aksen garis bawah tipis, memuat Amanah Persyarikatan & Profesi (PCM, PRM, AUM, Ortom), Profil Keagamaan & Kemasjidan (Qur'an, Sholat, Kajian, ZIS, Takmir, Pengabdian), Rekam Jejak BA & Catatan Tambahan, serta Lembar Pengesahan Resmi (Kader & Ketua MPKSDI H. Zainudin Ahpandi, S.E.).
  3. Strict 1-Page Precision:
     - Dikalibrasi presisi dengan margin 7mm/9mm dan font hierarchy terstruktur sehingga 100% pas dalam 1 halaman A4 tanpa tumpah ke halaman kedua (`PAGE_COUNT: 1`).
  4. Pengujian & Verifikasi:
     - 131 tests lulus 100% (528 assertions). Pint clean, Vite production build clean.

- 2026-09-16: Integrasi Fitur Formulir Kader Dinamis (Google Form Editor), Biodata Orang Tua, dan Pembersihan UI Admin:
  1. Biodata Orang Tua & Dynamic Custom Fields:
     - Migration menambahkan kolom `nama_ayah` (Nama Ayah Kandung), `nama_ibu` (Nama Ibu Kandung), dan `custom_fields` (JSON) ke tabel `anggota`.
     - Ditampilkan pada formulir pendaftaran mandiri (`register.blade.php`), portal profil kader (`portal/profile.blade.php`), detail anggota admin (`admin/anggota/show.blade.php`), dan ekspor data Excel (`exports/anggota.blade.php`).
  2. Modul Manajemen Formulir Dinamis Ala Google Form (`/admin/form-kader`):
     - Dibuat tabel `form_pertanyaan` dengan seksi (data_diri, keagamaan, kemuhammadiyahan, kemasyarakatan), tipe pertanyaan (text, textarea, number, select, checkbox), opsi dinamis, placeholder, toggle status aktif/sembunyi, toggle wajib/opsional, dan pengurutan nomor urut.
     - Dikelola melalui antarmuka interaktif khusus Admin Daerah di `resources/views/admin/form-pertanyaan/index.blade.php` dengan modal Google-Form builder dan tabs seksi.
     - Komponen rendering dinamis `<x-form-custom-fields>` dipasang pada form pendaftaran dan portal kader, menyimpan jawaban langsung ke JSON `custom_fields` secara fleksibel tanpa perlu migrasi berkala.
  3. Pembersihan & Perbaikan UI Detail Kader (`admin/anggota/show.blade.php`):
     - Mengeliminasi duplikasi banner & tombol pada status kader nonaktif (menghapus tombol ganda di header, menyatukan banner reaktivasi tunggal bernuansa gelap elegan, dan membatasi banner merah catatan perbaikan hanya saat status 'ditolak').
     - Memperbarui kartu WhatsApp kader dengan logo resmi SVG WhatsApp hijau (`#25D366`), bukan icon emoji.
  4. Verifikasi:
     - 126 feature/unit tests lulus 100% (517 assertions).
     - Laravel Pint clean (100% passed).
     - Vite production build (`npm run build`) sukses tanpa issue.

- 2026-09-15: Integrasi Data Admin Cabang (PCM) di Halaman Manajemen Akun Staf & Pengguna:
  1. Akar Masalah:
     - Query di `UserController::index` sebelumnya hanya memfilter `['admin', 'fasilitator', 'executive']`, sehingga 17 akun Admin Cabang yang sudah terdaftar di database tidak muncul.
  2. Solusi & Pembaruan:
     - Memperluas query `UserController::index` untuk menyertakan role `admin_cabang`, dengan urutan prioritas terstruktur (Admin Daerah -> Admin Cabang -> Fasilitator -> Eksekutif) dan pencarian mencakup nama, email/username, no WA, dan wilayah `pcm`.
     - Menambahkan tab filter `Admin Cabang (PCM)` di `resources/views/admin/users/index.blade.php` dengan badge wilayah PCM, username login, kontak WhatsApp, dan tindakan kelola status aktif/nonaktif.
     - Memperbarui `resources/views/admin/users/create.blade.php` dan `UserController::store` sehingga Admin Daerah dapat membuat akun Admin Cabang baru secara terstruktur dengan memilih wilayah PCM (17 kecamatan resmi se-Karanganyar).
  3. Verifikasi:
     - 120 tests lulus 100% (487 assertions), Pint passed, Vite production build clean.
- 2026-09-15: Pengembangan Sistem Penonaktifan & Reaktivasi Akun Kader:
  1. Modal & Kategori Penonaktifan:
     - Admin dapat menonaktifkan kader dengan memilih kategori alasan terstruktur (Pindah Domisili/Mutasi Luar Daerah, Wafat/Meninggal Dunia, Mengundurkan Diri dari Kepengurusan/AUM, Pelanggaran Etik/Disiplin Persyarikatan, atau Lainnya) serta catatan detail di `resources/views/admin/anggota/show.blade.php`.
     - Keterangan disimpan pada kolom `catatan_verifikasi` model `Anggota`.
  2. Notifikasi WhatsApp Otomatis:
     - Dibuatkan template pesan resmi `pesanPenonaktifan` dan `pesanReaktivasi` di `WhatsAppNotificationService`.
     - `AnggotaController::deactivate` otomatis mengirimkan pemberitahuan WA dan menyimpan fallback `wa_url`.
     - `AnggotaController::activate` ditambahkan (rute `POST /admin/anggota/{anggota}/activate`) untuk mengaktifkan kembali (reaktivasi) akun kader yang nonaktif dan mengirimkan pesan reaktivasi via WhatsApp.
  3. Integrasi Login & Cek Status:
     - `LoginController`: Menampilkan pesan gagal login informatif yang memuat alasan penonaktifan spesifik kader.
     - `auth/cek-status.blade.php`: Menampilkan kartu status pendaftaran "NONAKTIF" berdesain slate yang jelas, memuat alasan penonaktifan dan tombol pintas WhatsApp ke Helpdesk MPKSDI.
  4. Pengujian & Verifikasi:
     - 119 tests lulus 100% (477 assertions) mencakup deactivasi ber-alasan, aktivasi kembali, dan cek status nonaktif.
     - Pint clean, Vite production build sukses.
- 2026-09-15: Integrasi Pengiriman Pesan Otomatis WhatsApp Gateway (Fonnte API):
  1. Arsitektur & Konfigurasi:
     - Menambahkan konfigurasi gateway di `config/services.php` (`services.whatsapp`) dan `.env.example` (`WA_GATEWAY_PROVIDER`, `WA_GATEWAY_TOKEN`, `WA_GATEWAY_ENDPOINT`).
     - Mengimplementasikan method `WhatsAppNotificationService::kirimOtomatis(string $noWa, string $pesan)` berbasis `Illuminate\Support\Facades\Http` dengan authorization header Fonnte dan form payload (`target`, `message`, `countryCode: 62`).
     - Toleransi kegagalan (fault tolerance) dengan try-catch dan timeout 6 detik sehingga jika API timeout atau token belum diatur, alur transaksi admin tidak terputus.
  2. Integrasi Alur Kerja Verifikasi Admin:
     - `AnggotaController::verify`: Otomatis mengirim pesan verifikasi & kredensial login via WA ke nomor kader saat disetujui.
     - `AnggotaController::reject`: Otomatis mengirim pesan penolakan & catatan perbaikan via WA ke nomor kader saat berkas ditolak.
     - `AnggotaController::resetPassword`: Otomatis mengirim kredensial sandi baru via WA ke nomor kader saat admin me-reset sandi.
     - Sesi cadangan manual `wa_url` (click-to-chat) tetap dipertahankan penuh sebagai fallback aman.
  3. Indikator UI Admin:
     - `resources/views/admin/anggota/show.blade.php`: Menampilkan alert dinamis yang membedakan keberhasilan pengiriman otomatis via gateway dan opsi tombol kirim manual cadangan / kirim ulang.
  4. Verifikasi:
     - 117 tests lulus 100% (462 assertions) dengan `Http::fake()` pada pengujian suite.
     - Pint clean, Vite build sukses.
- 2026-09-15: Rilis Resmi Versi 2.3.0 & Perapian Tampilan Username Kader:
  1. Penjelasan & Penanganan Awalan 62 pada Username:
     - Nomor WhatsApp dinormalisasi ke format internasional E.164 (`628xxx`) di database untuk integrasi API WhatsApp Gateway.
     - Login fleksibel: `LoginController` mendukung input nomor HP baik dengan awalan `08`, `62`, maupun `+62` tanpa batasan.
     - UI kartu pendaftaran (`register.blade.php`) dan pesan notifikasi WA (`WhatsAppNotificationService`) kini menampilkan format lokal ramah `08xxxxxxxxxx` (serta alternatif `62xxxxxxxxxx`) dengan keterangan bahwa kedua awalan sama-sama valid untuk masuk portal.
  2. Pembaruan Halaman Pusat Informasi Sistem (`/updates`):
     - Memperbarui rilis stabil resmi ke `Versi 2.3.0 Stable` (15 September 2026).
     - Menambahkan catatan komprehensif v2.3.0: Panel 17 Admin Cabang (PCM), Ekspor Data Excel 47 Atribut, Dashboard Analitik Terpadu & Anti Bento Overload, Desain Form Aksesibilitas Lansia (40-70+ thn), dan Penanganan Sesi Kedaluwarsa.
     - Footer layout diperbarui ke `Pembaruan v2.3`.
  3. Verifikasi:
     - 116 unit & feature tests lolos 100% (456 assertions).
     - Laravel Pint clean, Vite production build sukses.






- 2026-09-14: Implementasi Formulir Riwayat Pendidikan Bertingkat (Pendidikan Terakhir -> Rincian Sekolah/PT):
  1. Skema Database:
     - Dibuatkan migration `2026_09_14_000002_add_pendidikan_lengkap_to_anggota_table.php` yang menambahkan kolom `pendidikan_terakhir`, `pendidikan_smp`, `pendidikan_sma`, dan `pendidikan_s1` (Perguruan Tinggi) ke tabel `anggota`.
  2. Alur Pengisian Interaktif (Alpine.js):
     - Kader terlebih dahulu memilih jenjang `Pendidikan Terakhir` (SD/MI, SMP/MTs, SMA/SMK/MA, D3, S1, S2, S3, atau Lainnya).
     - Kolom rincian nama sekolah/kampus (`pendidikan_sd`, `pendidikan_smp`, `pendidikan_sma`, `pendidikan_s1`) terbuka dinamis dan halus hanya sesuai jenjang yang ditempuh oleh kader.
     - Diterapkan secara seragam di formulir pendaftaran mandiri (`register.blade.php`) dan portal pembaruan profil kader (`profile.blade.php`).
  3. Integrasi Admin & Ekspor Excel:
     - Halaman detail anggota admin (`admin/anggota/show.blade.php`) menampilkan badge pendidikan terakhir dan kartu rincian institusi pendidikan lengkap.
     - Ekspor Excel (`exports/anggota.blade.php`) diperluas menjadi 47 kolom memuat seluruh kolom riwayat pendidikan.
  4. Pengujian:
     - 115 tests lolos 100% (443 assertions) mencakup registrasi, update profil, dan render ekspor Excel.

- 2026-09-14: Implementasi Penuh Role Admin Cabang, Ekspor Data Excel Cabang, dan Optimasi UI Mobile KPI:
  1. Perbaikan Bug Z-Index Drawer Mobile:
     - Mengubah z-index backdrop mobile drawer menjadi `z-40` dan `<aside>` menjadi `z-50` pada `resources/views/layouts/app.blade.php`, mengeliminasi bug overlay gelap/blur yang sebelumnya menutupi sidebar di layar smartphone.
  2. Diferensiasi Antarmuka & Otorisasi Admin Cabang vs Admin Daerah:
     - Sidebar brand & breadcrumb topbar menampilkan identitas resmi "PCM [Kecamatan] / Panel Admin Cabang".
     - Menu khusus Admin Cabang: Dashboard Cabang, Data Anggota Cabang (dengan live badge counter pending verifikasi PCM), dan Unduh Data (Excel). Menu Event BA dan Pengguna & Staf disembunyikan.
     - Akses rute `/admin/users*`, `/admin/events/create`, dan `/admin/anggota/import` dibatasi ketat khusus `role:admin` (Admin Daerah). Akses ditolak (403 Forbidden) jika diakses `admin_cabang`.
     - Scoping pangkalan data kader di `AnggotaController`: Admin Cabang hanya dapat melihat, menyetujui (verifikasi), menolak pendaftaran (dengan catatan perbaikan), dan mereset kata sandi anggota di wilayah PCM-nya saja. Upaya manipulasi kader di luar PCM otomatis di-abort (403).
  3. Fitur Ekspor Data Anggota ke Excel (.xlsx):
     - Dibuatkan class `App\Exports\AnggotaExport` dan view template `resources/views/exports/anggota.blade.php` dengan styling resmi navy khas MPKSDI PDM Karanganyar.
     - Memuat SELURUH 43 atribut data formulir pendaftaran anggota (Tab 1 Data Diri, Tab 2 Keagamaan, Tab 3 Kemuhammadiyahan/Organisasi, Tab 4 Kemasjidan & Harapan, plus status administrasi).
     - Dilengkapi grup header kategori bertingkat, format apostrof aman untuk NIK/NBM/No WA agar tidak berubah menjadi notasi ilmiah/kehilangan nol, perapian array string (bahasa, ZIS, sumber info), dan pemetaan jabatan organisasi & ortom terstruktur.
     - Tombol "Unduh Data Cabang" terintegrasi langsung di dashboard dan pangkalan data kader.
  4. Optimasi Responsif Kartu KPI Dashboard di Layar Mobile:
     - Mengatasi masalah teks bertumpuk/berdempetan ("PendaftarSemua", "50% Laki-50% Perempuan", "+4 Bulan Ini") di tampilan 2 kolom mobile.
     - Visualisasi komposisi gender dipercantik dengan dua-warna progress bar (biru `#005c8a` dan rose `rose-400`).
     - Label indikator dipangkas fleksibel dengan wrapping yang elegan dan bebas tabrakan kata di semua ukuran layar.
  5. Verifikasi Komprehensif:
     - 112 automated unit & feature tests lulus 100% (390 assertions).
     - Code style Pint (`vendor/bin/pint`) terformat 100%.
     - Vite production build (`npm run build`) berhasil (3.04s).

- 2026-09-14: Audit dan Debugging Komprehensif Seluruh Sistem:
  1. Perbaikan Hierarki DOM & Alpine.js:
     - Ditemukan dan diperbaiki 2 extra tag penutup `</div></div>` pada `resources/views/components/pasfoto-fitter.blade.php` yang sebelumnya memutus scope Alpine `registrationForm()` dan `pasfotoFitter()` sehingga memicu ReferenceError (`modalOpen`, `rawImageSrc`, `scale`, `tab`, `isSubmitting`).
     - Seluruh file Blade (100%) dipindai dan diverifikasi: keseimbangan tag `<div>`, `<form>`, dan `<template>` kini 100% simetris (termasuk penambahan closing div yang kurang pada `admin/anggota/index.blade.php` dan `landing/index.blade.php`).
  2. Dropdown Bertingkat & Hierarki Wilayah Karanganyar:
     - Dropdown bertingkat 17 Kecamatan dan Desa/Kelurahan se-Kabupaten Karanganyar diurutkan secara hierarki logis yang benar: Kabupaten (fixed Karanganyar) -> Kecamatan -> Desa/Kelurahan.
     - Judul halaman `/daftar` disesuaikan menjadi "Daftar Anggota Muhammadiyah".
     - Menghilangkan banner pemulihan draf yang redundant ("double info") sehingga status draf terpusat ringkas pada badge auto-save.
  3. Manajemen Akun Admin Cabang & Daerah:
     - Menambahkan role `admin_cabang` dan kolom `pcm` pada tabel `users`.
     - Dibuatkan 17 akun Admin Cabang per PCM dengan username murni nama kecamatan (misal: `tawangmangu`, `colomadu`, `jaten`) tanpa karakter `@` maupun domain, dengan password disamakan `cabangjra` (disertai toleransi typo `cabangkra`).
     - 1 akun Admin Daerah PDM Karanganyar dengan username `admin` dan password `AdminPDM@25`.
  4. Verifikasi Total:
     - 103 automated tests lulus 100% (361 assertions).
     - Code style Pint (`vendor/bin/pint --test`) passed 100%.
     - Vite production build (`npm run build`) sukses tanpa warning/error.
     - Log aplikasi bersih dari runtime error.

- 2026-09-14: Perombakan Komprehensif UI/UX Seluruh Formulir untuk Kader Senior / Lansia (Usia 40–70+):
  1. Standar Desain Aksesibilitas Lansia:
     - Seluruh input teks, dropdown, radio, dan tombol form ditingkatkan ke ukuran minimum ergonomis (`min-h-[50px]` hingga `min-h-[56px]`, `py-3.5` hingga `py-4`, `px-4`).
     - Ukuran font input distandarisasi ke `text-base` (16px) untuk mencegah auto-zoom di browser smartphone yang sering membingungkan pengguna usia lanjut.
     - Batas elemen menggunakan `border-2` berkontras tinggi (`border-slate-300` / `border-sky-600`) agar jelas terlihat pada layar HP dengan brightness rendah atau keterbatasan penglihatan (presbiopia/rabun dekat).
     - Seluruh label input diubah menjadi tebal berkontras tinggi (`font-black`/`font-bold text-slate-900`) tanpa kapitalisasi sempit (menghilangkan `uppercase tracking-wider` yang sulit dibaca mata tua).
     - Contoh isian konkret dihadirkan langsung pada microcopy dan placeholder.
  2. Formulir Pendaftaran Kader Mandiri (`register.blade.php`):
     - Stepper & bar progres navigasi diperbesar dengan indikator langkah aktif yang jelas di layar HP.
     - Tab 1–4 ditingkatkan penuh: kartu pilihan cepat (checkbox/preset) untuk bahasa, lembaga ZIS, organisasi Muhammadiyah, ORTOM, aktivitas masjid, takmir, dan peran kemasyarakatan diubah menjadi kartu sentuh lebar (`min-h-[50px]`+) dengan centang besar.
     - Komponen Pasfoto (`pasfoto-fitter.blade.php`) dilengkapi tombol aksi sentuh besar ("Pilih Foto dari Galeri / Kamera HP") dan panduan foto setengah badan otomatis.
     - Tombol "Kirim Pendaftaran" dibuat sangat menonjol (`min-h-[56px]`, `text-lg font-black`, warna emerald mencolok).
  3. Halaman Masuk / Login (`login.blade.php`):
     - Kolom Nomor WhatsApp dan Kata Sandi Tanggal Lahir (DDMMYYYY) berukuran besar dengan kartu petunjuk format tanggal lahir berlatar kuning lembut (`amber-50`).
     - Tombol toggle mata lihat/sembunyikan sandi diperbesar sentuhannya.
  4. Halaman Cek Status Berkas (`cek-status.blade.php`):
     - Input NIK/WhatsApp dan kartu hasil verifikasi (disetujui, ditolak, menunggu) diperbesar dengan tipografi tebal dan tombol aksi yang nyaman ditekan.
  5. Portal Profil Kader (`portal/profile.blade.php`):
     - Seluruh tab (Identitas, Keagamaan, Kemuhammadiyahan) diselaraskan ke ukuran kontrol ramah lansia, memudahkan pimpinan/kader senior memperbarui data dari smartphone.
  6. CBT Ujian Kognitif (`portal/tes/take.blade.php`):
     - Butir pertanyaan diperbesar (`text-base sm:text-lg font-bold`), kartu pilihan jawaban A–E diperlebar menjadi card besar (`min-h-[56px] p-4 sm:p-5 border-2`) yang nyaman disentuh dengan jempol.
  7. Angket Evaluasi (`portal/angket.blade.php`) & RTL (`portal/rtl.blade.php`):
     - Skala tombol nilai 1–5 diubah menjadi kotak sentuh besar (`min-h-[52px]` - `min-h-[56px] font-black text-lg`), dan textarea RTL diperluas dengan font 16px.
  8. Verifikasi: Seluruh 102 automated tests lulus 100%, Pint lolos, Vite production build sukses.
- 2026-09-14: Transformasi Kredensial Masuk Portal (Username = Nomor HP, Password = Tanggal & Tahun Lahir):
  1. Login Portal (`LoginController` & `login.blade.php`):
     - Username login kader menggunakan Nomor WhatsApp/HP aktif (`no_wa`), dinormalisasi otomatis (mendukung awalan 08, 628, +628, maupun spasi/tanda hubung). Admin/staf tetap dapat menggunakan email.
     - Kata sandi login menggunakan Tanggal & Tahun Lahir kader (format 8 digit: `DDMMYYYY`, misal 17 Agustus 1985 -> `17081985`). Sistem toleran terhadap variasi tanda pemisah (`17-08-1985` atau `17/08/1985`).
  2. Formulir Pendaftaran Kader (`register.blade.php` & `AnggotaRegistrationController`):
     - Kader senior tidak lagi dibebani membuat kata sandi baru secara manual; kolom password dan konfirmasi password dihilangkan dari form pendaftaran dan digantikan dengan kartu informasi pembuatan akun otomatis yang ramah lansia.
     - Sistem secara otomatis membuatkan kata sandi dari tanggal lahir kader (`Carbon::parse($tanggal_lahir)->format('dmY')`).
     - Ringkasan pendaftaran pasca-submit menampilkan Username (No WA) dan Kata Sandi (Tanggal Lahir DDMMYYYY).
  3. Notifikasi WhatsApp & Reset Kata Sandi:
     - Pesan WhatsApp verifikasi disetujui & pesan reset password admin (`WhatsAppNotificationService`) menginformasikan kredensial masuk: Username = Nomor HP/WA, Password = Tanggal Lahir (DDMMYYYY).
     - Admin reset password kader (`AnggotaController::resetPassword`) kini me-reset kata sandi langsung ke tanggal lahir kader.
  4. Impor Data Anggota (`AnggotaImport`):
     - Kata sandi default akun hasil impor diatur ke tanggal tahun lahir (`dmY`).
  5. Seluruh 102 automated tests lulus 100% (green), Pint lolos, Vite production build sukses.
- 2026-09-15: Penambahan Statistik Profil & Demografi Database Kader di Dashboard Admin:
  1. Fitur:
     - Menghadirkan seksi analitik demografi dan sosial-keagamaan pada Dashboard Admin (`resources/views/admin/dashboard.blade.php` & `DashboardController.php`).
     - **4 Mini KPI Badges**:
       * Persentase Sholat Berjamaah 5 Waktu di Masjid (% rutin masjid & jumlah kader).
       * Persentase Pengurus/Takmir Masjid (% takmir aktif & jumlah kader).
       * Persentase Kemampuan Membaca Al-Qur'an (% lancar & fasih).
       * Jenjang Pendidikan Terbanyak / Dominan.
     - **Visualisasi Sebaran Profesi / Pekerjaan**: Bar progres persentase per kategori pekerjaan (Guru AUM, Wiraswasta, PNS, Karyawan, dll) secara dinamis.
     - **Visualisasi Kemasjidan & Pendidikan**: Rincian aktivitas sholat berjamaah di masjid bertingkat dan pills badge distribusi jenjang pendidikan terakhir (SD, SMP, SMA, D3, S1, S2, S3).
     - **Isolasi Wilayah**: Otomatis teragregasi penuh untuk Admin Daerah PDM, dan terfilter khusus untuk Admin Cabang PCM (seperti PCM Colomadu).
  2. Verifikasi: 116 tests passed 100%, Pint passed, Vite build sukses, commit & push berhasil.
- 2026-09-14: Integrasi Pasfoto Kader pada Portal Dashboard & Navigasi:
  1. Fitur:
     - Menampilkan pasfoto formal kader pada kartu sambutan utama Dashboard Portal (`portal/dashboard.blade.php`) dengan bingkai rasio pasfoto 3:4 yang proporsional (`w-14 h-18 sm:w-16 sm:h-20 rounded-xl border-2 border-sky-400`).
     - Mengintegrasikan pasfoto kader pada avatar kanan atas Navbar Header (`layouts/app.blade.php`) dan avatar pojok kiri bawah Sidebar Footer.
     - Jika anggota belum mengunggah pasfoto, sistem otomatis menampilkan avatar inisial warna khas persyarikatan sebagai fallback.
  2. Otorisasi Keamanan:
     - Memperbarui `FotoController`: membedakan `isAdminDaerah()` (akses seluruh foto) dan `isAdminCabang()` (akses foto anggota cabang yang bersangkutan), serta pemilik foto.
  3. Verifikasi: 116 tests passed 100%, Pint passed, commit & push sukses.
- 2026-09-14: Penyempurnaan & Auto-Sync Login Admin Daerah (admin / AdminPDM@25) & 17 Admin Cabang:
  1. Akar Masalah:
     - Di server produksi, data seeder `AdminCabangSeeder` belum pernah dijalankan atau akun admin daerah masih menggunakan email lama (`admin@mpksdi-kra.id` / `admin@mpksdi.test`).
     - Query login sebelumnya hanya mencari exact match kolom `email`.
  2. Solusi:
     - Memperluas query pencarian user pada `LoginController`: jika input login adalah `admin`, sistem mencocokkan ke record ber-role `admin`, `email = 'admin'`, maupun varian domain resmi.
     - Menambahkan fitur **Auto-Provision & Auto-Sync**: jika akun admin atau cabang belum ada di database produksi saat login dengan password resmi (`admin` + `AdminPDM@25`, atau cabang + `cabangkra`/`cabangjra`), sistem otomatis membuat dan mengaktifkan akun tersebut secara aman dan instan tanpa perlu menjalankan seeder manual.
     - Mendaftarkan `AdminCabangSeeder::class` di `DatabaseSeeder.php`.
  3. Verifikasi: 116 tests passed 100%, Pint passed, commit & push sukses.
- 2026-09-14: Penyempurnaan Floating Mobile Bottom Navigation Dock pada Landing Page:
  1. Akar Masalah:
     - Pada tampilan smartphone, navigasi mengambang (floating bottom bar) memiliki efek semi-transparan `bg-white/92 backdrop-blur-xl`.
     - Karena rendering backdrop blur pada browser mobile sering kali tembus atau tidak mem-blur secara pekat saat hardware acceleration hemat daya, teks 4 kartu pilar perkaderan ("Ideologi", "Ke-Muhammadiyahan", "Kepemimpinan", "Fikih Ibadah") tembus ke dalam dock navigasi dan bertabrakan dengan teks tombol navigasi ("Beranda", "Program", "Peta PCM", "Masuk", "Daftar").
  2. Solusi & Implementasi:
     - Mengubah latar belakang navigasi melayang menjadi putih pekat solid 100% (`bg-white`), menghilangkan efek tembus pandang sama sekali.
     - Mempertegas bayangan elevasi (`shadow-[0_12px_28px_-4px_rgba(15,23,42,0.18)]`) dan border pembatas (`border-slate-200`) agar terpisah bersih dari konten di baliknya.
     - Menjadikan tombol aksi utama (Daftar / Dashboard) berwarna kontras (`bg-sky-600 text-white font-bold`) dengan ikon putih stroke tebal.
     - Menambahkan padding bawah mobile `pb-28 sm:pb-20` pada `<main>` agar footer tidak tertutup floating dock saat di-scroll ke paling bawah.
  3. Verifikasi: 115 tests passed 100%, Vite build clean, commit & push sukses.
- 2026-09-14: Penyempurnaan Formulir Pendaftaran Mandiri (/daftar), Profil Anggota, dan Penyesuaian Tokoh Resmi:
  1. Tab 1 Formulir Pendaftaran: Label `Kecamatan (Cabang)` disederhanakan menjadi `Kecamatan` saja.
  2. Tab 3 Kemuhammadiyahan:
     - Keaktifan di Pimpinan Muhammadiyah (PDM, PCM, PRM, AUM): saat dicentang, langsung memunculkan kolom isian nama jabatan/posisi (`jabatan_muhammadiyah[...]`).
     - Pimpinan Cabang & Ranting: ditempatkan terstruktur sebelum ORTOM:
       - Dropdown `Pimpinan Cabang Muhammadiyah (PCM)` (17 Cabang resmi se-Karanganyar + opsi lainnya).
       - Input teks `Pimpinan Ranting Muhammadiyah (PRM)` (isian mandiri bebas untuk mengakomodasi ranting berbasis masjid/dusun/desa).
     - Keaktifan di ORTOM (Pemuda Muhammadiyah, NA, Aisyiyah, IPM, HW, TS): saat dicentang, langsung memunculkan kolom isian nama jabatan/posisi (`jabatan_ortom[...]`).
     - Penggantian kolom `Amanah di AUM` menjadi `Pekerjaan / Profesi Utama` (`pekerjaan`).
  3. Aksesibilitas & Ramah Senior (Usia 40+):
     - Rute alias `/pendaftaran` otomatis redirect ke `route('anggota.register')`.
     - Header banner form dibuat lebih ringkas dan langsung fokus ke formulir pendaftaran.
     - Auto-scroll mulus ke `#form-pendaftaran` saat halaman diakses.
  4. Portal Profil Kader (`/portal/profile`):
     - Memperbarui `ProfileController` dan `portal/profile.blade.php` sehingga kader aktif dapat melihat & memperbarui data `PCM`, `PRM`, `pekerjaan`, `NBM`, `jabatan_muhammadiyah`, dan `jabatan_ortom`.
  5. Penyelarasan Ekspor & Seeder:
     - `DemoDataSeeder` dan `AnggotaImport` kini menyertakan field `pcm`, `prm`, `pekerjaan`, dan jabatan organisasi.
     - Ekspor Excel laporan kelulusan (`exports.laporan-event`) mengutamakan kolom `pcm` dan `prm`.
  6. Koreksi Nama Resmi Tokoh:
     - Nama Ketua MPKSDI PDM Karanganyar dikoreksi di seluruh sistem (Landing Page, PDF Laporan & Tanda Tangan) menjadi **H. Zainudin Ahpandi, S.E**.
  7. Seluruh 99 automated tests lulus 100% (green), Pint lolos, Vite production build sukses.
- 2026-09-10: Dokumen fondasi agent dibuat dari BA.DOC dan sistem referensi.
- 2026-09-10: AHP-SAW dihapus dari scope permanen. Ranking pakai urutan nilai akhir tertinggi ke terendah.
- 2026-09-10: Materi, soal, rubrik diambil dari Arqam Apps referensi dan disesuaikan.
- 2026-09-10: Target hosting cPanel, PHP 8.2+, MySQL, cron/queue.
- 2026-09-10: Phase 1 dibangun langsung (Laravel 12, MariaDB lokal `arqam`). Foto anggota disimpan disk `local` (privat), bukan `public`.
- 2026-09-10: Umur dihitung `Carbon::parse(tanggal_lahir)->age`, bukan diffInYears (Carbon 3 signed).
- 2026-09-10: Phase 2 selesai. 52 test hijau, Pint lolos, build sukses, HTTP smoke test 200.
- 2026-09-10: QR PNG untuk ID Card PDF via GD (tanpa imagick). Bacon QR encoder → imagecreate → PNG.
- 2026-09-10: Middleware `event_access` resolve event dari route param (model atau ID string).
- 2026-09-10: Role `executive` ditambahkan. Fasilitator ditugaskan per event via `event_fasilitator` pivot.
- 2026-09-10: Command `ba:siapkan-materi` adaptasi rubrik dari referensi (psikomotor, afektif, RTL). Idempotent.
- 2026-09-11: Rombak total UI/UX dari AI-slop generik (gradient hijau/teal, bubble cards mengambang) ke identitas visual resmi MPKSDI Karanganyar berbasis logo otentik: Deep Ocean Blue (`#005c8a`), Saffron Gold (`#f8bb00`), Midnight Navy (`#002b40`), dan Sky Cyan (`#01b0f5`). Desain berkarakter institusional persyarikatan, hairline borders (`border-slate-200`), tipografi berjarak tegas, mobile-first, dan form ramah usia kader. Seluruh 57 unit/feature tests lulus 100%.
- 2026-09-11: Audit mendalam & eksekusi perbaikan komprehensif selesai:
  1. Keamanan Ujian Kognitif: TesService & TesController memblokir resubmission jawaban yang sudah final dan menjaga durasi ujian.
  2. Pasfoto Anggota: Foto disajikan via FotoController terotentikasi & privat, ditampilkan di admin show anggota, dan di-embed base64 pada ID Card PDF.
  3. Dokumen Output: ID Card dan Sertifikat diperbarui ke entitas "MPKSDI PDM Karanganyar" dengan skema warna navy-gold. Ekspor Excel menambahkan rincian kolom Keaktifan.
  4. Pengacak Kelompok Outbound: Tabel outbound_kelompok, algoritma pemerataan gender/cabang/acak murni, dan UI visual admin untuk pembagian pos outbound.
  5. Penolakan Verifikasi: Status 'ditolak' dengan catatan perbaikan yang muncul informatif saat kader mencoba login.
  6. Manajemen Staf: CRUD akun internal (Admin, Fasilitator, Executive) di panel admin dengan fitur toggle aktif/nonaktif.
  7. Pencarian Kader: Fitur cari berdasarkan nama, NIK, nbm, dan kecamatan di index admin anggota.
- 2026-09-12: Migrasi Layout Dashboard dari horizontal navbar ke Collapsible Sidebar Navigation:
  1. Sidebar Terintegrasi: Header sidebar menyatukan Logo Muhammadiyah dan Logo MPKSDI dalam satu lockup terpadu, navigasi bertingkat dengan ikon & badge role.
  2. Fleksibilitas Desktop: Fitur collapse/expand lebar sidebar dengan penyimpanan state di localStorage.
  3. Mobile-First: Off-canvas drawer navigasi dengan backdrop blur dan transisi halus Alpine.js.
  4. Top Bar Ringkas: Breadcrumbs workstation, tanggal hari ini, avatar pengguna, dan tombol toggle.
- 2026-09-12: Eliminasi Elemen "AI Slop" & Transformasi ke Desain Institusional Autentik:
  1. Header Bar: Menghapus badge pills berlebih ('Admin Workstation', 'Pusat Operasional', 'Sistem Siap') dan sub-bar sekunder yang membuang ruang vertikal; menggantinya dengan breadcrumbs bersih ('MPKSDI PDM Karanganyar / Panel Administrasi') serta profil pengguna yang proporsional.
  2. Sidebar: Menghilangkan garis gradien pelangi, menghapus widget role badge kapital di tengah navigasi, menyeragamkan item navigasi dengan border radius 'rounded-lg' dan kontras teks yang tajam, serta menetapkan background Midnight Navy (#001f30) yang solid.
  3. Dashboard Admin: Mengganti banner gelembung raksasa ('rounded-3xl') menjadi header halaman administratif yang ringkas; mengganti 4 kartu metrik bergaya template SaaS dengan kartu metrik 'rounded-xl' yang tenang, terstruktur, dan memiliki hirarki tipografi tegas; menghapus efek 'card-lift-sm' dan 'card-sheen' agar tidak memantul-mantul saat disentuh kursor; menyajikan antrean verifikasi dan agenda perkaderan dengan tombol aksi cepat (Peringkat, Excel, PDF) yang fungsional.
  4. Fixed Sidebar Layout: Menetapkan sidebar desktop sebagai elemen fixed viewport (`fixed inset-y-0 left-0 h-screen z-40`) dengan area menu yang scrollable mandiri (`overflow-y-auto`), dipadukan dengan offset dinamis konten utama (`lg:pl-64` saat expand, `lg:pl-20` saat collapsed) sehingga sidebar tetap terkunci di posisinya saat halaman panjang digulir.
  5. Desain Linear Minimalist & Ergonomi Responsif:
     - Merombak sidebar menjadi gaya terang bersih (*soft light minimal* `bg-white border-r border-slate-200/90`) yang menyatu dengan kanvas konten utama tanpa kontras gelap-terang yang melelahkan mata.
     - Memperbaiki lockup logo Muhammadiyah & MPKSDI agar berpadu alami tanpa kotak putih artifisial.
     - Menghadirkan *Actionable Operational Ribbon* di dashboard atas jika terdapat pendaftar pending untuk alur kerja cepat admin.
     - Menyesuaikan responsivitas kartu agenda event di mobile (`sm:flex-row`, tombol aksi fleksibel tanpa *overflow/squishing*).
     - Menghapus sisa-sisa efek mengambang `card-lift-sm` dan container `rounded-2xl` pada pesan alert.
  6. Integrasi Pola Latar Belakang Subtle, Pusat Pembaruan & Panduan Sistem:
     - Menghadirkan utility class `.bg-grid-subtle` dan aksen watermark sinar surya Muhammadiyah untuk memberi tekstur estetis berkelas pada kanvas kerja.
     - Merilis halaman Pusat Informasi & Pembaruan Sistem (`/updates`) dengan riwayat rilis terstruktur (v1.0 s.d. v2.2) dan roadmap pengembangan.
     - Merilis halaman Panduan & Tutorial Sistem (`/panduan`) interaktif multi-peran (Admin, Kader, Fasilitator, Executive) dengan Accordion FAQ teknis.
     - Menambahkan rute dan grup menu sidebar terpadu "Bantuan & Panduan" serta tautan cepat di dashboard dan footer.
     - Seluruh 92 unit & feature tests lulus 100% (green), Vite build terkompilasi sukses.
  7. Penyeragaman Tipografi & Skala Visual Sidebar (Menu Utama, Bantuan & Panduan, Akses Publik):
     - Mengatasi inkonsistensi ukuran tulisan ("besar kecilnya beda") dengan membungkus seluruh navigasi dalam `<nav class="flex-1 overflow-y-auto px-3 py-4 space-y-4 text-xs font-semibold">`.
     - Menyeragamkan seluruh tautan anchor (`<a>`) di ketiga grup menu ke `text-xs font-semibold`, padding `px-3 py-2.5`, dan dimensi ikon SVG seragam `w-5 h-5 shrink-0`.
     - Mengeliminasi disparitas visual di mana tautan Bantuan & Panduan sebelumnya berada di luar `<nav>` tanpa deklarasi font, sehingga mewarisi ukuran browser reguler (16px), serta Akses Publik yang menggunakan font-normal tipis dan padding tidak seragam.
     - 92/92 tests tetap 100% lulus dan Vite production build berhasil.
  8. Optimalisasi Maksimal Antarmuka Sistem (Executive 2-Column Workstation & Ergonomi Form):
     - Merombak halaman Enroll Peserta Kegiatan (`admin.events.enroll`) dari kartu sempit `max-w-2xl` menjadi Executive 2-Column Workstation: kolom kiri untuk daftar kandidat kader (pencarian, select-all cerdas via Alpine.js, live counter terpilih, kartu kader ergonomis beravatar inisial) dan kolom kanan sticky untuk widget kapasitas kuota event terisi, peringatan overflow kuota, serta tombol aksi dinamis.
     - Menata ulang formulir Buat/Edit Event (`admin/events/form.blade.php`) dan Tambah Staf (`admin/users/create.blade.php`) menjadi layout 2-kolom dengan panel pendamping panduan (status event, tag sertifikat dinamis, dan matriks wewenang peran).
     - Menghapus batasan artifisial `max-w-3xl` dan `max-w-4xl` pada modul Afektif, Angket, Keaktifan, RTL, dan Profil Anggota agar memanfaatkan kanvas kerja secara penuh dan seimbang di layar desktop/widescreen.
     - Membersihkan seluruh sisa kelas warna non-standar `mpksdi-` di profil kader dan detail RTL ke standar resmi `#005c8a` dan `sky-500`.
     - Seluruh 92 automated tests lulus 100%, Pint lolos, dan Vite production build sukses.
  9. Perbaikan Alur Pendaftaran Anggota Mandiri & Validasi Multi-Step Cerdas (/daftar):
     - Menyelesaikan akar masalah "gak bisa kirim pendaftaran": tombol submit di Tab 4 sebelumnya digugurkan secara diam-diam oleh browser (*silent abort*) karena terdapat 14 kolom `required` di Tab 1 yang berstatus tersembunyi (`display: none`).
     - Menambahkan atribut `novalidate` pada form pendaftaran dan menggantikannya dengan sistem validasi per langkah (*step-by-step validation*) interaktif via Alpine.js `registrationForm()`.
     - Mencegah pengguna melangkah ke Tab 2/3/4 jika isian wajib di Tab 1 belum lengkap, serta otomatis mengarahkan kursor dan menggulir (*auto-focus & smooth scroll*) ke kolom pertama yang bermasalah.
     - Menambahkan status loading dan *anti double-submit* pada tombol kirim di Tab 4.
     - Menambahkan sanitasi otomatis spasi, tanda hubung (`-`), dan titik (`.`) pada nomor WhatsApp di `AnggotaRegistrationRequest::prepareForValidation()`.
     - Menambahkan aturan `unique:users,email` untuk mencegah kegagalan SQL (1062 Duplicate entry) saat alamat email yang sama dimasukkan.
     - Memperbaiki penempatan sintaks error `<select>` pada `jenis_kelamin` dan menggunakan `Js::from()` untuk sanitasi variabel string pada Tab 4.
     - Menambahkan fallback aman pada pasfoto fitter bila `DataTransfer` tidak didukung browser tertentu.
     - Rangkaian pengujian otomatis bertambah menjadi 94 tests (293 assertions) dan 100% lulus (green).
- 2026-09-11: Rombak Landing Page & Modernisasi Form sesuai identitas MPKSDI:
  1. Landing Page publik: Fokus awal pada profil resmi MPKSDI PDM Karanganyar, gedung dakwah, dan infografis 4 pilar Baitul Arqam.
  2. Display Sebaran Data: Pemetaan interaktif 17 PCM se-Kabupaten Karanganyar dengan filter zona (Pusat & Barat, Lereng Lawu & Utara, Wilayah Selatan) dan agregat kader riil.
  3. Diferensiasi Atmosfer Layout: Sub-bar kontekstual yang tegas membedakan Workstation Manajemen PDM (Admin/Fasilitator/Executive) vs Personal Portal Kader (Anggota).
  4. Form Registrasi Anggota: Multi-step stepper 4 langkah, live photo preview instan, input ergonomis, dan tombol navigasi bersih.
  5. Form Profil Kader: Tabbed interface (Identitas, Keagamaan, Kemuhammadiyahan), foto avatar live preview, dan badge status verifikasi.
  6. Form Ujian CBT: Antarmuka CBT modern dengan sticky countdown timer, progress bar, grid navigator nomor soal interaktif (hijau/terisi vs abu-abu/kosong), dan modal konfirmasi pengumpulan.
  Semua 63 unit/feature tests lulus 100%, Pint lolos, build Vite berhasil.
  Semua 63 unit/feature tests lulus 100%, Pint lolos, Vite build berhasil.
- 2026-09-11: Implementasi Desain Landing Page Modern berdasarkan Referensi Platform Pelatihan Modern (Upskill):
  1. Navbar pill-style yang bersih dengan tombol outline 'Masuk Portal' dan solid pill 'Daftar Kader'.
  2. Hero Banner lengkung (`rounded-3xl`) dengan kontainer abu-abu lembut / aksen sky tint, breadcrumb navigasi, headline berbobot kuat, dan Quick Metrics Card pangkalan data resmi.
  3. Baris 8 Pilar & Kategori Perkaderan (Ideologi, Tarjih, Kemuhammadiyahan, Kepemimpinan, dsb.) dengan ikon warna-warni kotak halus.
  4. Section Keunggulan Standar Mutu 2 kolom dengan checklist bulat biru dan visual aksen kartu miring (*skewed card backdrop*).
  5. Section Program Perkaderan Baitul Arqam dalam kontainer sky-blue tint dengan 4 kartu pelatihan (Pimpinan, Guru AUM, Nakes PKU, TOT Instruktur) lengkap dengan rating bintang, durasi jam, dan badge level.
  6. Alur 4 Langkah Peserta (Pendaftaran, Verifikasi, Pelaksanaan BA, Kelulusan & RTL) berurutan dengan ikon ilustrasi modern.
  7. Sebaran 17 Cabang PCM Interaktif terintegrasi filter zona (Pusat & Barat, Lereng Lawu, Selatan).
- 2026-09-11: Full-Page Continuous Scroll Animation Engine & Dynamic Progress Indicator:
  1. Animasi scroll diperberat (*heavyweight & cinematic curve* `cubic-bezier(0.16, 1, 0.3, 1)` dengan translasi 42-46px) agar pergerakan terasa substansial dan premium.
  2. Continuous Scroll Re-arm: IntersectionObserver tidak melepas listener (`unobserve`) melainkan melakukan re-arming otomatis ketika elemen keluar dari viewport (`rect.top > window.innerHeight * 0.92 || rect.bottom < -150`), sehingga elemen terus beranimasi dinamis setiap kali di-scroll naik atau turun.
  3. Cakupan penuh seluruh halaman (100% reveal coverage):
     - Navbar: Brand (`reveal-left`), Nav Links (`reveal-top`), CTA Buttons (`reveal-right`).
     - Hero Section: Wadah, Motif Batik Pojok (`reveal-fade`), Gedung Dakwah (`reveal-right`), Headline & Subtitle (`reveal` stagger), Tombol CTA, dan Floating Info Badges.
     - 8 Pilar Kategori: 8 kartu micro-interactions dengan `reveal-scale` dan stagger delay harmonis (100ms - 450ms).
     - Keunggulan Standar Mutu: Live blinking radar badge (`reveal-pop`), Heading & Subtitle (`reveal-left` stagger 150-200ms), 4 kartu fitur mutu (`reveal-pop` stagger 200-500ms) dengan spring reaction badge `.feature-badge-icon`, indikator nomor (01-04), dan Kartu Tokoh KH. Zainudin Alfandi (`reveal-right`).
     - Program Perkaderan: Batik pojok (`reveal-fade`), Judul (`reveal-top`), 4 Course Cards tematik (`reveal-scale` stagger 100-400ms).
     - Alur Tahapan Peserta: Judul (`reveal-top`), Garis penghubung dashed (`reveal-fade`), 4 kartu tahapan langkah (`reveal-pop` stagger 100-400ms).
     - Sebaran 17 Cabang: Batik pojok (`reveal-fade`), Header judul & subtitle (`reveal-left`), Quick aggregate chips (`reveal-right`), Tab filter zona (`reveal-left`), Search input (`reveal-right`), Wadah Peta Leaflet (`reveal-left`), Panel Direktori & Selected Cabang Card (`reveal-right`).
     - FAQ: Batik pojok (`reveal-fade`), Header & Helpdesk Card (`reveal-left`), 5 kartu akordeon interaktif (`reveal-right` stagger 100-420ms).
     - Footer: Wadah, Batik pojok (`reveal-fade`), 4 kolom informasi (`reveal-left`, `reveal-top`, `reveal-right`), serta Sub-footer kiri & kanan (`reveal-left` & `reveal-right`).
     - Batik Corner Component: Observer re-arm otomatis di `batik-corner.blade.php` agar ornamen batik bersinar dan muncul kembali setiap kali di-scroll bolak-balik.
  4. Global Scroll Progress Indicator: Bar gradient fixed top (`#scroll-progress-bar`) yang mengisi proporsional seiring pergerakan scroll pengguna.
  5. Resolusi konflik CSS: Selektor gabungan `.reveal.card-lift`, `.reveal-scale.card-lift`, dll. untuk menjaga durasi animasi masuk 1.15s tetap utuh sebelum transisi hover 0.35s aktif saat `:hover`.
  Seluruh 63 tests lulus (100% green), Vite build sukses.
- 2026-09-11: Interactive Card Animations & Motion Design:
  1. Card Lift & Sheen: Utilitas `.card-lift`, `.card-lift-sm`, dan `.card-sheen` menghadirkan elevasi hover yang halus (`translateY(-8px)` / `translateY(-4px)`), bayangan lembut, dan pantulan kilau diagonal (*diagonal light sweep*).
  2. Floating Badges: Keyframe `@keyframes card-float-gentle` pada badge Gedung Dakwah dan Pangkalan Data (`animate-card-float` & `animate-card-float-delayed`).
  3. Responsive Micro-interactions: Ikon berotasi dan membesar dinamis saat kartu disentuh/dihilangkan hover-nya di seluruh kartu (8 Pilar, 4 Program Perkaderan, 4 Alur Tahapan, Peta/Direktori Cabang, FAQ, dan Footer).
  Seluruh 63 tests lulus (100% green), Pint passed, Vite build sukses.
- 2026-09-11: Integrasi Visual Gedung Dakwah PDM Karanganyar pada Hero Section:
  1. Menghadirkan visual representatif Gedung Dakwah PDM Karanganyar (kubah biru dan masjid) di sisi kanan hero banner melengkung.
  2. Menerapkan efek siluet gradasi memudar (*feathered gradient fade*) dari kanan ke kiri (`from-[#f3f5f8] via-[#f3f5f8]/80 to-transparent`), sehingga foto gedung di sisi kanan menyatu mulus (*seamless blend*) dengan latar teks di sisi kiri.
  3. Menyematkan floating glass-morphic badges di atas visual gedung: 'Gedung Dakwah PDM Karanganyar - Pusat Dakwah & Perkaderan' dan 'Pangkalan Data Terintegrasi - Total Kader di 17 Cabang'.
  Semua 63 test lolos, Pint passed, Vite build sukses.
- 2026-09-11: Penempatan Foto Tokoh KH Zainudin Alfandi pada Kartu Keunggulan (Second Card):
  1. Menghadirkan portrait resmi KH Zainudin Alfandi (Ketua MPKSDI Karanganyar) dalam gaya vektor digital berwibawa yang satu tema dengan visual gedung dakwah.
  2. Diletakkan di sisi kanan Section Keunggulan Standar Mutu dengan backdrop miring (*skewed light blue card backdrop* `#dbeafe`) dan cincin elips dekoratif sesuai referensi desain upskill.
  3. Dilengkapi role tag 'Pimpinan Majelis', nama beliau 'KH. Zainudin Alfandi', jabatan 'Ketua MPKSDI PDM Karanganyar', dan kutipan amanat perkaderan.
  Seluruh 63 tests lulus (100%), Pint passed, Vite build sukses.
- 2026-09-11: Implementasi Favicon Resmi Logo MPKSDI:
  1. Konversi `public/aset/logo-mpksdi.webp` menjadi `public/favicon.png` (128x128 RGBA transparan tajam) dan `public/favicon.ico`.
  2. Penambahan tag `<link rel="icon">` dan `<link rel="apple-touch-icon">` pada `resources/views/landing/index.blade.php`, `resources/views/layouts/app.blade.php` (portal kader & admin), dan `resources/views/layouts/guest.blade.php` (halaman auth).
  3. Seluruh 63 unit/feature tests lulus 100%, tampilan tab browser konsisten menggunakan lambang resmi MPKSDI Karanganyar.
- 2026-09-11: Implementasi Peta Spasial Interaktif Sebaran 17 Cabang & Pembersihan Total Landing Page:
  1. Integrasi library Leaflet.js yang dibundel mandiri via npm (`import L from 'leaflet'`) dan `@import 'leaflet/dist/leaflet.css'` pada app.css/app.js.
  2. Penambahan koordinat GPS riil (lat, lng), alamat sentra dakwah, dan jumlah estimasi PRM untuk ke-17 PCM se-Kabupaten Karanganyar pada LandingController.
  3. Peta interaktif Leaflet dengan ubin CartoDB Positron, 17 pin dinamis bertema MPKSDI (warna per zona + cincin emas `#f8bb00` + angka kader), tooltip & pop-up informatif, tombol 'Pusatkan Peta', dan sinkronisasi real-time dengan kartu detail dan daftar pencarian cabang Alpine.js.
  4. Pembersihan estetika: Hero badge resmi dengan animasi pulse, micro-cards 2x2 keunggulan standar mutu, pembenahan margin foto tokoh KH Zainudin Alfandi, baris 8 pilar perkaderan, dan kartu program BA.
  5. Seluruh 63 feature tests lulus 100%, Pint passed, Vite build sukses.
- 2026-09-11: Penyesuaian Ikon 8 Pilar Perkaderan:
  1. Seluruh 8 ikon pilar diganti dari ikon generik menjadi visual otentik persyarikatan:
     - Ideologi: Kitab Suci Al-Qur'an / Rehal Terbuka.
     - Ke-Muhammadiyahan: Matahari Bersinar 12 Sinar (Lambang Resmi Muhammadiyah).
     - Kepemimpinan: Kompas Arah Kemajuan & Bintang Panduan.
     - Fikih Ibadah: Kubah Masjid & Mihrab Ibadah Shalat HPT.
     - Ujian CBT: Layar Komputer Evaluasi dengan Checklist Soal.
     - Psikomotor: Target Ketangkasan & Keterampilan Praktik Lapangan.
     - Keteladanan: Perisai Integritas & Bintang Akhlaqul Karimah.
     - 17 Cabang: Peta Spasial Berjejaring Titik PCM se-Karanganyar.
  2. Seluruh 63 unit/feature tests tetap hijau 100%, Vite build sukses.
- 2026-09-11: Migrasi Tile Peta ke OpenStreetMap Resmi (Bebas API Key):
  1. Penyedia ubin peta CartoDB Positron menyisipkan watermark "API KEY REQUIRED" pada gambar petanya bila diakses tanpa registrasi API key komersial.
  2. Mengganti tile layer Leaflet ke OpenStreetMap resmi (`https://tile.openstreetmap.org/{z}/{x}/{y}.png`) yang 100% terbuka, tanpa API key, tanpa watermark, dan menampilkan detail wilayah/jalan/geografis Karanganyar secara akurat.
  3. Seluruh 63 feature test suite lulus 100%, Pint passed, Vite build sukses.
- 2026-09-11: Perapian Kartu Tokoh KH. Zainudin Alfandi:
  1. Menghapus cincin outline biru mengambang (`rounded-full absolute -bottom-3`) yang sebelumnya menumpuk dan menutupi teks kutipan amanat.
  2. Mengganti latar belakang miring yang tidak simetris dengan bayangan kedalaman berlapis yang presisi (`bg-sky-200/50 rounded-3xl translate-x-2.5 translate-y-2.5 -z-10 blur-[1px]`).
  3. Menata ulang tipografi nama, gelar jabatan, kotak kutipan kutip resmi, dan penanda periode kepengurusan PDM Karanganyar secara elegan.
  4. Seluruh 63 feature tests tetap 100% hijau.
- 2026-09-11: Desain Visual Tematik 4 Event Program Perkaderan (#program):
  1. Merombak 4 kartu kursus/pelatihan dari header polos menjadi karya ilustrasi vektor tematik yang kaya (rich multi-layered SVG), responsif, dan tajam di setiap resolusi:
     - Event 1 (Baitul Arqam Pimpinan): Ilustrasi musyawarah syura pimpinan berpeci di meja konferensi, blueprint strategi cabang, grafik batang mini, dan matahari 12 sinar emas berlatar navy-sky (`#003d5c` ke `#01b0f5`).
     - Event 2 (Baitul Arqam Guru AUM): Ilustrasi kitab suci Al-Qur'an terbuka di atas rehal kayu bersilang emas, topi toga wisuda akademik, serta bintang-bintang inspirasi pendidik berlatar hangat amber-gold (`#853704` ke `#f59e0b`).
     - Event 3 (Baitul Arqam Nakes PKU): Ilustrasi Bulan Sabit Hijau-Emas PKU Muhammadiyah, stetoskop melingkar lembut, perisai palang medis & tanggap MDMC, serta garis detak jantung EKG berlatar emerald-teal (`#064e3b` ke `#10b981`).
     - Event 4 (TOT Instruktur Perkaderan): Ilustrasi podium fasilitator dengan mikrofon ganda gooseneck, sorot lampu panggung, gelombang suara andragogi, medali emas master trainer bermahkota bintang, dan flipchart rubrik penilaian berlatar velvet-indigo (`#2e1065` ke `#7c3aed`).
  2. Dilengkapi kartu bergradasi kedalaman, chip kompetensi khusus per program (Manhaj Tarjih, Nilai ISMUBA, Fikih Pasien, Andragogi Modern), micro-interaction hover, dan tombol aksi interaktif dengan icon chevron.
- 2026-09-11: Optimasi Auto-Fit Peta Sebaran 17 Cabang:
  1. Mengganti hardcoded `setView([-7.596, 110.97], 11)` dengan fungsi dinamis `fitAllCabang()` menggunakan `window.L.latLngBounds()` dan `map.fitBounds(bounds, { padding: [35, 35], maxZoom: 12 })`.
  2. Seluruh 17 titik marker PCM se-Kabupaten Karanganyar (dari Colomadu di barat hingga Jenawi di timur, dan Jatipuro di selatan) kini otomatis pas (fit) dan tidak terpotong di tepi kanvas.
  3. Mengatur popup marker dengan `autoPan: false` agar tidak menggeser batas peta yang sudah pas, menambahkan `invalidateSize()` saat kanvas selesai dimuat, serta memperbarui tombol aksi menjadi 'Fit Seluruh Peta'.
  4. Seluruh 63 unit/feature tests lulus 100%, Vite build sukses.
- 2026-09-11: Redesain Tematik Section FAQ (#faq):
  1. Menyelaraskan tata letak FAQ dengan wadah kontainer biru lembut (`bg-[#ebf4fa] rounded-3xl p-6 sm:p-10 lg:p-12 border border-sky-100`) serupa section Program Perkaderan.
  2. Format split-view 2 kolom modern:
     - Kolom Kiri: Badge status ('Pusat Bantuan & Informasi'), heading berbobot, serta kartu Helpdesk WhatsApp resmi MPKSDI PDM Karanganyar.
     - Kolom Kanan: 5 kartu akordeon interaktif dengan nomor urut '01'-'05', efek fokus aktif (`border-sky-400 ring-2 ring-sky-100 shadow-md`), indikator panah berputar, dan tipografi rapi.
  3. Seluruh 63 feature test suite lulus 100%, Pint passed, Vite build sukses.
- 2026-09-11: Penyelarasan Tinggi Kanvas Peta & Panel Direktori (#sebaran):
  1. Mengubah row container dari `items-start` menjadi `items-stretch` dengan wrapper `h-full flex flex-col`.
  2. Kartu Daftar 17 Cabang di kolom kanan kini menggunakan `flex-1 flex flex-col min-h-[220px]` dengan daftar cabang `flex-1 min-h-0 overflow-y-auto`.
  3. Garis batas bawah kolom kanan dan kanvas peta di kolom kiri kini 100% sejajar, presisi rata bawah (*flush bottom*), dan tidak lagi terlihat gantung/timpang.
  4. Seluruh 63 unit/feature tests lulus 100%, Vite build sukses.
- 2026-09-11: Penyempurnaan Bottom Bar & Navigasi Mobile Landing Page:
  1. Map Bottom Bar: Mengganti bubble legenda mengambang dan box atribusi mentah Leaflet dengan bar terintegrasi (*integrated GIS bottom bar*) di sepanjang tepi bawah kanvas peta (`bottom-0 inset-x-0`). Menampilkan legenda warna zona (Pusat & Barat, Lereng Lawu, Selatan) dengan chip modern, serta atribusi GIS resmi (`© OpenStreetMap`).
  2. Footer Sub-Footer Bottom Bar: Memperbarui bar sub-footer dengan identitas brand MPKSDI, penanda sistem ('Sistem Perkaderan Terpadu Baitul Arqam • Bumi Intanpari'), status indikator aktif, navigasi kebijakan/data cabang, dan tombol interaktif 'Kembali ke Atas' dengan smooth-scroll.
  3. Mobile Floating Bottom Bar: Menambahkan bar navigasi melayang (*floating bottom bar*) khusus tampilan layar seluler (`lg:hidden`) dengan tautan cepat Beranda, Program, Peta PCM, dan Masuk/Daftar/Dashboard untuk pengalaman akses layaknya aplikasi native.
  4. Seluruh 63 unit/feature tests lulus 100%, Vite build sukses.
- 2026-09-11: Integrasi Branding Pengembang "gemala.dev":
  1. Menambahkan logo otentik gemala.dev (`public/aset/logo-gemaladev.png`) dengan format PNG transparan.
  2. Menyematkan chip badge "Developed by gemala.dev" dengan tautan aktif ke Instagram `@gemala.dev` (`https://instagram.com/gemala.dev`) pada sub-footer landing page (`resources/views/landing/index.blade.php`) dan layout portal utama (`resources/views/layouts/app.blade.php`).
  3. Desain badge responsif, dilengkapi ikon Instagram, efek hover interaktif, dan terintegrasi selaras dengan tema modern MPKSDI.
  4. Seluruh 63 test feature suite tetap 100% hijau, Vite build sukses.
- 2026-09-11: Kompaksi Tinggi Peta & Pembatasan 5 Cabang dengan Scroll Down:
  1. Mengurangi ketinggian vertikal kanvas peta dari yang sebelumnya terlalu panjang (550px+) menjadi tinggi proporsional dan kompak (`min-h-[380px] sm:min-h-[440px]`).
  2. Panel daftar cabang di kolom kanan dibatasi jendela pandangnya agar hanya memuat tepat 5 cabang aktif (`h-[235px] max-h-[235px]`).
  3. Dilengkapi *custom sleek scrollbar* (`.cabang-scrollbar`) dan bilah footer indikator dengan animasi panah ("Menampilkan 5 dari 17 cabang • Scroll ke bawah ↓").
  4. Padding `fitBounds` diperhalus (`paddingBottomRight: [25, 45]`) agar pin wilayah selatan tidak tertutup bar dasbor GIS bawah.
  5. Seluruh 63 unit/feature tests lulus 100%, Vite build sukses.
- 2026-09-11: Redesain Total Footer & Penyederhanaan Badge Pengembang:
  1. Merombak kartu kontak WhatsApp dan Email dari lingkaran kuning kasar menjadi kartu putih elegan berkontur halus dengan ikon SVG resmi berlatar emerald (`bg-emerald-500`) dan navy (`bg-[#005c8a]`).
  2. Menata ulang grid 12 kolom seimbang (5 kolom identitas & kontak terpadu, 2 kolom Organisasi beraksen sky `#01b0f5`, 2 kolom Perkaderan beraksen amber `#f59e0b`, 3 kolom Sekretariat dengan kartu tersemat, pin SVG lokasi Gedung Dakwah, dan tombol aksi 'Daftar Kader Sekarang').
  3. Memperbaiki sub-footer / baris bawah menjadi sangat bersih:
     - Sisi kiri: Hak cipta resmi `© 2026 MPKSDI PDM Karanganyar`, indikator pulse hijau aktif, dan teks penanda `Sistem Perkaderan Terpadu Baitul Arqam • Bumi Intanpari` dengan `whitespace-nowrap`.
     - Sisi kanan: Tautan regulasi, badge minimalis 'Developed by gemala.dev' (menghilangkan teks/ikon Instagram yang terlihat, namun tetap aktif sebagai tautan `href="https://instagram.com/gemala.dev"`), serta tombol 'Ke Atas ↑'.
  4. Latar footer diselaraskan ke `bg-slate-50 border-t border-slate-200/80` sehingga senada (*se-tema*), bersih, dan tidak terasa hampa.
  5. Seluruh 63 feature test suite lulus 100% (200 assertions), Vite build sukses.
- 2026-09-11: Perbaikan Stacking Context Z-Index Navbar & Bilah Peta:
  1. Masalah: Bilah legenda zona & atribusi peta (`z-[400]`) menembus/menimpa di atas sticky header navbar (`z-50`) saat halaman di-scroll ke bawah.
  2. Solusi:
     - Menaikkan z-index `<header>` dan bilah navigasi mobile menjadi `z-[100]`.
     - Mengisolasi konteks tumpukan (*stacking context*) pada kontainer peta Leaflet dengan kelas CSS `isolate z-10`.
     - Dengan `isolation: isolate`, semua z-index internal Leaflet maupun bar peta terkunci di dalam konteks lokal kontainer peta, sehingga saat di-scroll ke bawah peta secara konsisten melintas di bawah navbar tanpa ada kebocoran visual.
  3. Seluruh 63 unit/feature tests lulus 100%, Vite build sukses.
- 2026-09-11: Desain & Integrasi Ornamen Sudut SVG Batik Solo Surya (Kearifan Surakarta & Pencerahan Muhammadiyah):
  1. Karya Vektor Orisinil (`public/aset/batik-surya-solo.svg` & komponen Blade `resources/views/components/batik-corner.blade.php`):
     - Memadukan motif klasik Batik Solo (4 Kelopak Agung Batik Kawung Picis dengan isen-isen 4 cecek, kembang Bintang Truntum Surakarta pemandu ilmu, serta sulur ukel canting yang mengalir).
     - Menyatukannya dengan identitas persyarikatan & kecerdasan (Surya Muhammadiyah 12 Sinar Utama runcing & 12 lidah api sekunder = 24 sinar ritmis, pusat bintang kecerdasan 8 sudut Rub el Hizb, serta lintasan orbit lingkaran konsentris astrolabe keilmuan berkemajuan).
     - Berbasis `currentColor` dinamis dengan variasi posisi (`top-left`, `top-right`, `bottom-left`, `bottom-right`) menggunakan transformasi SVG/CSS.
  2. Implementasi Sudut Section:
     - Ditempatkan secara presisi dan elegan di sudut-sudut kontainer utama: Hero Banner (`#beranda`), Kartu Tokoh KH Zainudin Alfandi (`#keunggulan`), Program Perkaderan (`#program`), Peta Sebaran 17 Cabang (`#sebaran`), Akordeon FAQ (`#faq`), dan Footer (`#kontak`).
     - Tampilan berbobot, transparan lembut (`opacity-[0.04]` hingga `opacity-[0.08]`), tidak mengganggu keterbacaan teks, dan memperkuat karakter kultural berkemajuan MPKSDI PDM Karanganyar.
  3. Seluruh 63 unit/feature tests lulus 100% (200 assertions), Vite build sukses.
- 2026-09-11: Penyempurnaan Ornamen SVG Batik Kawung Ceplok Solo (High-Fidelity V2):
   1. Analisis detail mikro visual referensi (`media_1789111874646.jpg`):
      - Geometri sejati: Grid lingkaran tangensial dengan 4 kelopak vesica piscis per lingkaran.
      - Pola bersilang (checkerboard): Diagonal 1 berisi kelopak terisi (primary) dengan cecek putih; diagonal 2 berisi kelopak kontur/tint halus (secondary) dengan cecek gelap.
      - Isen-isen cecek otentik: Tepat 6 titik per kelopak (4 titik sumbu diagonal + 2 titik sayap/flank).
      - Pusat lingkaran: Bunga melati 5 kelopak (bukan 6) dengan inti lubang putih bersih.
   2. Seamless Tiling 2x2: Unit pengulangan 160×160 px (R=40) menyatu tanpa patahan secara horizontal dan vertikal.
   3. Komponen Blade (`resources/views/components/batik-corner.blade.php`):
      - Berbasis `currentColor` dinamis yang harmonis mengikuti tema Tailwind (`text-[#005c8a]`, `text-[#f8bb00]`, dll).
      - Ditambahkan fitur **Vignette Gradient Fade Mask** (`fade="true"` default): ornamen di sudut memudar lembut ke arah dalam kontainer tanpa potongan kotak kaku, menghasilkan efek watermark mewah institusional.
   4. Aset mandiri: `public/aset/batik-kawung-solo.svg` diperbarui dengan warna referensi otentik.
   5. Seluruh 63 unit/feature tests lulus 100% (200 assertions), npm run build sukses.
- 2026-09-11: Pembaruan Ilustrasi Vektor Tokoh KH. Zainudin Alfandi:
   1. Pengguna menyediakan foto asli beliau (`media_1789118293817.png`) berjas resmi, kemeja putih, dan dasi hitam.
   2. Foto diproses menjadi karya ilustrasi vektor digital / kartun eksekutif beresolusi tinggi (`public/aset/ketua-mpksdi-vector.jpg`):
      - Menjaga ciri khas wajah otentik beliau (belahan rambut samping, bentuk telinga, senyum ramah, mata berbinar dengan catchlight).
      - Menghilangkan noise/grain kamera dengan segmentasi mean-shift dan cel-shading halus.
      - Garis inking vektor bersih pada kontur wajah, jas formal, dan dasi.
      - Latar belakang gradasi biru langit lembut studio (`#dbeafe` ke `#eff6ff`) yang menyatu dengan kartu profil Section Keunggulan.
   3. Seluruh 63 unit/feature tests lulus 100% (200 assertions), npm run build sukses.
- 2026-09-11: Penebalan Opacity Ornamen Batik Sudut Landing Page:
   1. Opacity kontainer ornamen sudut `<x-batik-corner>` pada `landing/index.blade.php` dinaikkan sekitar 3x lipat (dari semula 3%-8% menjadi 16%-24%) agar motif terlihat jelas, tegas, dan berbobot.
   2. Nilai default props komponen `batik-corner.blade.php` dinaikkan ke `opacity-[0.22]`.
   3. Vignette mask diperluas jangkauan transparansinya (stop 70% opacity 0.95, stop 100% opacity 0.2) sehingga pola batik tampak utuh dan tetap memudar halus di ujung dalamnya.
   4. Seluruh 63 unit/feature tests lulus 100% (200 assertions), npm run build sukses.
- 2026-09-11: Animasi Reveal Interaktif Ornamen Batik Sudut & Standar Generasi Foto Tokoh:
   1. Komponen `<x-batik-corner>` (`resources/views/components/batik-corner.blade.php`) dilengkapi animasi reveal saat di-scroll (Intersection Observer + Alpine.js):
      - Mendukung prop `reveal="true"` (aktif secara default).
      - Efek kemunculan: transisi halus (`transition-all duration-1000 ease-out`), melayang masuk dari sudut luar (`translate-x-5 translate-y-5`) dengan pembesaran lembut (`scale-90` ke `scale-100`) dan pemudaran (`opacity-0` ke `opacity-100`).
   2. Penegasan Generasi Foto Tokoh KH. Zainudin Alfandi:
      - Pengguna menegaskan untuk TIDAK memakai filter Python lokal dalam pembuatan kartun/vektor potret.
      - Generasi foto kartun akan dijalankan langsung menggunakan model AI (Gemini image generation) setelah jendela kuota API terbuka kembali (~18:00 WIB / 11:00 UTC). Foto asli disimpan aman di `public/aset/ketua-mpksdi.png`.
   3. Seluruh 63 unit/feature tests lulus 100% (200 assertions), Vite build sukses tanpa error.
- 2026-09-11: Pembaruan Gradasi Motif Batik Menjadi Ultra-Soft (Radial Vignette Falloff):
   1. Gradasi pemudaran ornamen sudut (`resources/views/components/batik-corner.blade.php`) diubah dari linear gradient tajam menjadi **Radial Gradient** dengan kurva pemudaran bertingkat (`offset 0% -> 1.0`, `25% -> 0.82`, `48% -> 0.52`, `70% -> 0.22`, `88% -> 0.05`, `100% -> 0.0`).
   2. Efek pemudaran kini melengkung lembut (radial arc) memancar dari sudut ke arah dalam, tanpa garis potong kaku (hard edge/cutoff) sama sekali, menghasilkan efek watermark mewah yang menyatu mulus (*seamless feathering*).
   3. Foto asli terbaru KH. Zainudin Alfandi disimpan di `public/aset/ketua-mpksdi.png`.
   4. Seluruh 63 unit/feature tests lulus 100% (200 assertions), Vite build sukses.
- 2026-09-11: Pembobotan Animasi Scroll Reveal Motif Batik (Heavy & Cinematic Inertia):
   1. Animasi scroll reveal pada `resources/views/components/batik-corner.blade.php` diperberat secara fisika gerak:
      - Durasi transisi diperpanjang ke `duration-[1500ms]` dengan kurva inersia berat `ease-[cubic-bezier(0.16,1,0.3,1)]`.
      - Jarak perpindahan (*initial shift*) diperjauh dari 20px menjadi 40px–56px (`translate-x-10 translate-y-10 sm:translate-x-14 sm:translate-y-14`).
      - Skala awal diperdalam ke `scale-[0.72]` sehingga kedalaman 3D dan bobot mendaratnya terasa mantap (*weighted arrival*).
- 2026-09-11: Penerapan Animasi Scroll Reveal Berat ke Seluruh Elemen Landing Page:
   1. Pembobotan Fisika Gerak Global (`resources/css/app.css` & `resources/js/app.js`):
      - Dibuat sistem utilitas reveal berbasis IntersectionObserver mandiri: `.reveal` (slide-up 36px), `.reveal-left` (slide-in kiri -36px), `.reveal-right` (slide-in kanan +36px), dan `.reveal-scale` (scale 0.88 ke 1.0).
      - Menggunakan kurva inersia berat (*deep inertia deceleration curve*) `cubic-bezier(0.16, 1, 0.3, 1)` berdurasi panjang 1.3 detik (`duration-1300`), memberikan efek gerakan mantap, berbobot, dan tidak terburu-buru.
      - Dilengkapi stagger delay classes (`.reveal-delay-100` hingga `.reveal-delay-700`) dan fallback aksesibilitas `@media (prefers-reduced-motion: reduce)`.
   2. Penerapan Menyeluruh di `resources/views/landing/index.blade.php`:
      - Section 2 (Hero Banner): Kontainer hero, floating badges, headline, paragraf, tombol CTA, dan visual Gedung Dakwah.
      - Section 3 (8 Kategori Perkaderan): Grid wrapper dan seluruh 8 chip pilar perkaderan bertahap (`reveal-scale` + stagger).
      - Section 4 (Keunggulan Standar Mutu): Heading, deskripsi, 4 kartu checklist mutu, dan kartu tokoh KH. Zainudin Alfandi (`reveal-right`).
      - Section 5 (Program Perkaderan): Heading program dan ke-4 kartu jenjang perkaderan (Pimpinan, Guru AUM, Nakes PKU, TOT Instruktur).
      - Section 6 (Alur Peserta): Heading dan ke-4 kartu tahapan pendaftaran hingga kelulusan.
      - Section 7 (Sebaran 17 Cabang PCM): Header, tombol filter zona, kanvas peta interaktif Leaflet (`reveal-left`), dan daftar direktori cabang (`reveal-right`).
      - Section 8 (FAQ): Header, kontak helpdesk (`reveal-left`), dan seluruh 5 accordion pertanyaan (`reveal-delay-*`).
      - Section 9 (Footer): Grid utama 12-kolom (Brand, Organisasi, Perkaderan, Sekretariat) dan sub-footer copyright / branding gemala.dev.
   3. Generasi AI Kartun Resmi Tokoh KH. Zainudin Alfandi via Gemini:
      - Tepat pukul 18:00:44 WIB (setelah reset kuota API 11:00 UTC), sistem mengeksekusi generasi gambar kartun potret resmi langsung menggunakan model AI Google Gemini (`generate_image`) tanpa filter Python.
      - Hasil generasi merefleksikan kemiripan wajah asli beliau: belahan rambut rapi, senyum berwibawa dan ramah, jas formal hitam, kemeja putih berkerah rapi, dasi, cel-shading lembut, dan latar biru studio minimalis.
      - File disimpan ke `public/aset/ketua-mpksdi-vector.jpg` dan tampil otomatis pada kartu tokoh Section Keunggulan Standar Mutu.
   4. Penyempurnaan Pola Ornamen Batik Sudut (Rapi, Simetris & Jelas Kelihatan):
      - Kerapian & Simetri Geometris: Komponen `batik-corner.blade.php` menerapkan coordinate mirror pada SVG (`-scale-x-100`, `-scale-y-100`) sehingga motif origin (0,0) di ke-4 sudut (top-left, top-right, bottom-right, bottom-left) selalu berpusat simetris dan rapi dari sudut luar ke dalam tanpa perpotongan canggung (*no awkward cutoffs*).
      - Ketegasan Vektor (*Crisp Lines*): Ketebalan garis ditingkatkan dari `stroke-width="0.6"` menjadi `stroke-width="1.0"`, titik cecek batik diperbesar ke `r="1.35"` (`fill-opacity="0.95"`), dan kelopak sekunder diberi bayang `fill-opacity="0.14"`.
      - Kejelasan & Kelembutan Gradasi: Kurva radial mask diperlebar zona terangnya (`0% -> 1.0`, `35% -> 0.95`, `60% -> 0.75`, `80% -> 0.40`, `92% -> 0.12`, `100% -> 0.0`) dan opasitas kontainer di seluruh section landing page ditingkatkan ke rentang proporsional `0.26` - `0.36` sehingga motif tampak nyata (*kelihatan jelas*), mewah, dan tetap memudar halus ke latar belakang.
   5. Seluruh 63 unit/feature tests lulus 100% (200 assertions), Vite build sukses tanpa kendala.
- 2026-09-11: Push Perdana Kode Proyek ke GitHub:
   - Inisialisasi Git repository lokal, verifikasi `.gitignore` (file sensitif `.env`, `storage/*.key`, dan `vendor/` aman terisolasi).
   - Commit perdana: `Initial release: Sistem Perkaderan Terpadu Baitul Arqam MPKSDI PDM Karanganyar`.
   - Push berhasil ke remote repository: `https://github.com/ZAKITRIPAMUNGKAS/mpksdikra.git` pada branch `main`.
- 2026-09-11: Perbaikan Posisi & Keselarasan Pola Batik Sudut Bawah (Bottom Corner Flush & Aligned):
   - Investigasi & Identifikasi Root Cause: Pada elemen berposisi sudut bawah (`bottom-left` dan `bottom-right`), parent container yang memiliki utilitas Tailwind `space-y-*` (seperti Section 5 `space-y-8`) menambahkan `margin-bottom: 2rem` (32px) pada anak non-terakhir. Hal ini menyebabkan komponen yang memiliki `position: absolute; bottom: 0;` terdorong melayang 32px ke atas dan terpotong garis lurus horizontal.
   - Mengapa Pola Sempat Hilang: Penghapusan `$svgFlip` pada percobaan sebelumnya menyebabkan kuadran motif primer (bunga tebal dengan cecek canting putih di 0..80, 0..80) bergeser ke sudut berlawanan dan terpotong oleh radial mask menjadi 0% opacity. Ditambah utilitas `sm:w-60` yang tidak terdaftar di Tailwind scale default.
   - Solusi Presisi:
     1. Mengembalikan SVG mirror transform (`$svgFlip`) dan radial gradient mask (`cx="0%" cy="0%"`) sehingga kelopak bunga tebal dan cecek canting selalu terpancar sempurna langsung dari titik sudut kartu.
     2. Menjaga isolasi margin mutlak (`!m-0 !mt-0 !mb-0 !ml-0 !mr-0` dan `style="margin: 0 !important;"`) sehingga tidak ada dorongan 32px dari `space-y-*`.
     3. Mengembalikan kelas ukuran Tailwind resmi (`w-40 h-40 sm:w-56 sm:h-56` dsb.).
     4. Menyetel IntersectionObserver reveal ke `threshold: 0.01` dan `rootMargin: 60px 0px 60px 0px` agar langsung terpicu dan tampil saat di-scroll.
   - Seluruh 63 feature tests lulus 100% (200 assertions), build Vite sukses, dan perubahan telah di-push ke GitHub repository `main`.
- 2026-09-11: Standarisasi Total Seluruh Tampilan Aplikasi Selaras Identitas Desain Landing Page:
    - Dilakukan refaktorisasi dan restyling komprehensif pada 32 file view di seluruh sistem (Master Layouts, Autentikasi, Registrasi Mandiri, Portal Kader, dan Seluruh Modul Manajemen Admin/Fasilitator/Executive) agar konsisten 100% dengan bahasa visual landing page MPKSDI:
      1. Master Layouts (`layouts/app.blade.php`, `layouts/guest.blade.php`): Penambahan top scroll progress bar gradient emas-biru, brand logo `MPKSDI<span class="text-sky-500">.</span>`, tab navigasi pill, badge status radar berkedip (*pulsing live dot*), alert toast `rounded-2xl` dengan card-lift, serta institutional footer dengan lencana pengembang `gemala.dev` dan tombol scroll "Ke Atas ↑".
      2. Autentikasi & Registrasi (`auth/login.blade.php`, `anggota/register.blade.php`): Form login berornamen batik sudut, badge live status, kartu split elegan `rounded-3xl`, stepper pill dengan progress bar amber pada form pendaftaran anggota 4 langkah.
      3. Personal Portal Kader: Antarmuka terpadu pada `portal/dashboard`, `portal/profile`, `portal/kehadiran` (digital pass badge emas & unduh ID Card), `portal/tes` (CBT exam runner dengan nomor grid interaktif), `portal/afektif`, `portal/angket`, dan `portal/rtl` dengan hero banner berornamen batik, kartu putih murni `rounded-3xl card-lift-sm`, radio options dengan highlight sky tint, dan tombol aksi rounded-full.
      4. Workstation Admin, Fasilitator & Executive: Penyelarasan seluruh 16 modul operasional (`admin/dashboard`, `admin/executive`, `admin/events/*` [index, show command center, form, enroll, soal CBT, keaktifan, ranking, psikomotor, afektif, angket, outbound], `admin/anggota/*`, `admin/users/*`, dan `admin/absensi/scan` dengan target viewfinder laser scan bercahaya amber-cyan).
      5. Token Desain Terstandarisasi:
         - Background canvas: `bg-[#f8fafc]` (soft slate).
         - Header Hero: `bg-[#f3f5f8] rounded-3xl border border-sky-100/80 p-6 sm:p-8 shadow-xs relative overflow-hidden card-lift-sm` + `<x-batik-corner position="top-right" />`.
         - Kartu Konten: `bg-white rounded-3xl border border-slate-200/80 shadow-xs card-lift-sm card-sheen hover:border-sky-400 p-6`.
         - Tombol Utama: `px-6 py-2.5 rounded-full bg-sky-500 hover:bg-sky-600 font-bold text-white shadow-xs hover:scale-[1.01]`.
         - Tombol Aksen Emas: `rounded-full bg-[#f8bb00] hover:bg-amber-400 text-slate-950 font-black shadow-md`.
         - Kontrol Input: `rounded-xl border border-slate-200/90 bg-slate-50/60 focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20`.
         - Tabel & Data Feed: `rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden bg-white card-lift-sm`.
    - Seluruh 63 unit & feature tests lulus 100% (200 assertions), build Vite clean tanpa warning.
- 2026-09-11: Penyesuaian Formulir Pendaftaran Anggota & Profil Kader:
    - Menghapus pertanyaan Harapan PCM dan Harapan BA dari formulir.
    - Menambahkan 3 pertanyaan baru pada Tab 4 (Kemasyarakatan & Kemasjidan):
      1. Aktifitas Jamaah di Masjid (`aktifitas_jamaah_masjid` - textarea).
      2. Keaktifan sebagai Takmir Masjid (`keaktifan_takmir` [Tidak / Ya] & `jabatan_takmir` [input teks amanah/jabatan]).
      3. Keaktifan di Kemasyarakatan (`keaktifan_kemasyarakatan` [RT/RW/BPD/Karang Taruna dll.] - textarea).
    - Menambahkan migrasi database `2026_09_11_000001_add_kemasyarakatan_fields_to_anggota_table.php`.
- 2026-09-11: Optimasi UX Form Kemasjidan & Kemasyarakatan Ramah Usia 40+:
    - Merombak formulir pertanyaan aktivitas masjid, takmir, dan kemasyarakatan agar ramah kader senior (usia 40 tahun ke atas) yang sering kesulitan mengetik panjang di layar ponsel:
      1. Preset Chips Sekali Sentuh (*One-Tap Chips*): Pilihan cepat rutinitas masjid (*Sholat Berjamaah 5 Waktu, Subuh/Isya, Pengajian Rutin Subuh/Malam, Pengajian Ahad Pagi, Tadarus, Panitia PHBI*) dan peran kemasyarakatan (*Pengurus RT, RW, BPD Desa, Paguyuban Warga, Kader Posyandu, Linmas, Kelompok Tani, Rukun Kematian, Karang Taruna, Warga Masyarakat*).
      2. Kartu Pilihan Visual 2-State: Kartu besar dengan kontras tegas untuk status Takmir (*Bukan Takmir* vs *Ya, Pengurus Takmir*) beserta tombol cepat jabatan takmir (*Ketua Takmir, Sekretaris, Bendahara, Sie Ibadah/Dakwah, Sarpras, Sie Remaja/TPA, Imam/Marbot*).
      3. Sinkronisasi Otomatis 2 Arah: Sentuhan chip langsung mengisi textarea / input secara otomatis, namun kader tetap leluasa menambahkan rincian khusus secara manual (misal: nama masjid atau dusun).
      4. Banner konfirmasi yang melegakan & tombol aksi ukuran sentuh besar (*touch-friendly*).
      5. Standardisasi Tombol Stepper: Penyelarasan tinggi tombol navigasi (tinggi seragam `h-11`), penyingkatan label (`Lanjut: Kemasyarakatan`), dan penambahan `whitespace-nowrap` sehingga tidak ada lagi tombol yang membengkak atau patah teks menjadi 3 baris di layar ponsel.
    - Diterapkan secara identik pada formulir pendaftaran mandiri (`anggota/register.blade.php`) dan portal edit profil kader (`portal/profile.blade.php`).
    - Seluruh 64 feature tests lulus 100% (208 assertions), Vite build sukses, committed & pushed ke GitHub `main`.
- 2026-09-12: Implementasi 6 Fitur Penyempurnaan Sistem Operasional Baitul Arqam (Non-Berita):
    - Menolak penambahan modul berita/artikel CMS sesuai instruksi tegas pengguna ("berita gak perlu, kerjakan selain ini").
    - Membangun 6 pilar fitur operasional lapangan dan administrasi perkaderan:
      1. **Aksi Cepat WhatsApp Otomatis (`WhatsAppNotificationService`)**:
         - Generator URL WhatsApp click-to-chat dengan nomor ternormalisasi (standar 628xxx).
         - Template pesan resmi persyarikatan: Verifikasi Akun Sukses, Catatan Revisi Pendaftaran Ditolak, Undangan Jadwal Event BA, Reset Sandi Baru, dan Chat Bantuan Helpdesk.
         - Tombol aksi cepat WA di detail kader (`admin/anggota/show`), kolom tabel data kader (`admin/anggota/index`), dan modal login.
      2. **Reset Kata Sandi Ramah Kader Usia 40+**:
         - Menghindari alur token email yang sering gagal/masuk spam pada kader senior.
         - Method `resetPassword()` di `AdminAnggotaController` untuk me-reset sandi ke 6 digit terakhir NIK kader secara instan.
         - Alert sukses dilengkapi tombol satu klik kirim sandi baru via WhatsApp ke nomor kader.
         - Modal bantuan "Lupa Kata Sandi?" di halaman login dengan akses langsung Helpdesk WhatsApp MPKSDI dan tombol Cek Status.
      3. **Impor Massal Kader dari Spreadsheet Excel (`maatwebsite/excel`)**:
         - Class `AnggotaTemplateExport`: Format spreadsheet standar MPKSDI dengan header styling rapi dan baris contoh.
         - Class `AnggotaImport`: Validasi NIK 16 digit, pencegahan duplikat otomatis, pembuatan akun user aktif langsung, pembuatan password bawaan (6 digit NIK), dan kalkulasi umur otomatis.
         - Controller `AnggotaImportController` & View `resources/views/admin/anggota/import.blade.php` lengkap dengan kartu rekap hasil (berhasil, dilewati, error).
      4. **Standar Penomoran Sertifikat Resmi Persyarikatan**:
         - Migrasi database `2026_09_12_000001_add_format_sertifikat_to_events_table.php` (kolom `format_nomor_sertifikat` default: `{nomor}/BA-MPKSDI/{bulan_romawi}/{tahun}`).
         - Helper method `Event::generateNomorSertifikat($nomorUrut)` yang menerjemahkan bulan ke angka Romawi (I-XII) dan nomor urut 3 digit (misal: `001/BA-MPKSDI/IX/2026`).
         - Input konfigurasi format di form event admin (`admin/events/form.blade.php`) dan terintegrasi pada PDF sertifikat admin & portal kader.
      5. **Lembar Presensi Fisik Manual Siap Cetak (PDF A4)**:
         - Method `AbsensiController::cetakLembar(Event $event, EventSesi $sesi)`.
         - View `resources/views/pdf/lembar-presensi.blade.php`: Format A4 resmi dengan Kop Surat MPKSDI PDM Karanganyar, rincian sesi materi, tabel tanda tangan presensi selang-seling rapi, dan kolom tanda tangan Master of Training / Petugas Presensi sebagai cadangan darurat di lokasi acara.
         - Tombol aksi "Cetak Lembar Fisik (PDF)" di halaman scanner presensi (`admin/absensi/scan.blade.php`) dan kartu sesi pada detail event (`admin/events/show.blade.php`).
      6. **Cek Status Pendaftaran Mandiri Publik (`/cek-status`)**:
         - Controller `CekStatusController` & View `resources/views/auth/cek-status.blade.php`.
         - Calon peserta cukup memasukkan NIK atau No WA untuk mengetahui status terkini (Aktif, Menunggu Verifikasi, Ditolak dengan catatan verifikator, atau Belum Terdaftar) tanpa perlu login.
    - Seluruh 79 feature & unit tests lulus 100% (241 assertions), Laravel Pint lolos, Vite production build sukses.
- 2026-09-12: Implementasi Sistem Penyesuai Posisi Pasfoto Wajah Proporsional (Pasfoto Face Fitter 3x4):
    - Masalah: Foto unggahan pendaftar sering tidak proporsional (wajah terlalu jauh, terlalu dekat, miring, atau kepala terpotong) sehingga merusak kerapian ID Card fisik dan dokumen sertifikat resmi Baitul Arqam.
    - Solusi: Komponen Blade interaktif `<x-pasfoto-fitter>` (`resources/views/components/pasfoto-fitter.blade.php`) berbasis Alpine.js dan HTML5 Canvas:
      1. **Rasio Standar Pasfoto 3:4**: Viewport framing rasio 3:4 dengan kanvas pemotong beresolusi tinggi (600 × 800 pixel JPEG).
      2. **Overlay Sketsa Panduan Wajah (Silhouette Face Guide)**:
         - Garis oval kepala & peci/jilbab warna cyan berkilau lembut (`stroke="#01b0f5"`).
         - Garis horizontal tinggi mata warna emas (`stroke="#f8bb00"`) berlabel `TINGGI MATA`.
         - Garis batas puncak kepala / peci.
         - Garis kurva bahu kiri & kanan berlabel `GARIS BAHU`.
         - Titik silang tengah (crosshair).
      3. **Kontrol Interaktif Ramah Kader Senior (Usia 40+)**:
         - Sentuh & Geser (*Pointer Events Drag*): Foto bisa digeser langsung dengan mouse di desktop atau sentuhan jari di layar ponsel tanpa lag via `setPointerCapture`.
         - Slider & Tombol Zoom: Pengatur ukuran wajah (`&minus;` dan `&#43;` serta slider halus) agar wajah pas di dalam oval.
         - Putar 90° (*Rotate*): Mengatasi foto kamera HP yang terbalik atau miring.
         - Reset Posisi: Mengembalikan foto ke titik tengah.
      4. **Injeksi File Otomatis**:
         - Hasil potongan di-render pada HTML5 Canvas dan dikonversi ke Blob JPEG beresolusi 600×800 px.
         - Berkas JPEG baru disuntikkan langsung ke elemen `<input type="file" name="foto">` melalui API standar `DataTransfer()`, sehingga controller Laravel menerima berkas gambar 3:4 siap cetak tanpa perlu perubahan skema backend.
         - Dilengkapi badge hijau *"✓ Wajah Pas & Proporsional (3:4)"* dan tombol *"Atur Posisi"* untuk penyesuaian ulang sewaktu-waktu.
      5. **Cakupan**: Diterapkan secara seragam pada formulir pendaftaran mandiri kader (`anggota/register.blade.php`) dan form pembaruan profil kader (`portal/profile.blade.php`).
    - Seluruh 79 unit & feature tests tetap 100% hijau, Vite build sukses.
- 2026-09-12: Implementasi Smart Auto-Match Pasfoto 3:4 (Tanpa Wajib Manual + Indikator Loading):
    - Permintaan: Pengguna meminta UI lebih responsif dan proses pencocokan foto dibuat otomatis ("buat otomatis match aja jadi loading dulu gitu gak usah manual").
    - Solusi & Alur Baru:
      1. **Otomatis Tanpa Modal Paksa**: Calon kader cukup memilih foto dari galeri/kamera HP. Sistem TIDAK memunculkan popup modal yang mengganggu alur pengisian form.
      2. **Indikator Loading & Scanning Elegan**: Saat foto dipilih, kotak pratinjau langsung menampilkan status pemindaian (*Laser Scan Effect*, cincin putar amber-cyan, dan label *"Auto Match: Menyelaraskan 3:4..."*) selama ~650ms untuk memberikan umpan balik visual yang meyakinkan.
      3. **Algoritma Cerdas Auto-Framing 3:4 dengan Close-Up Zoom Wajah**:
         - Masalah Sebelumnya: Foto tegak (3:4 portrait) dari kejauhan (setengah badan / memegang barang) tidak di-zoom sehingga wajah kader tetap kecil di kejauhan.
         - Solusi: Ditambahkan `detectFaceAndHead()` berbasis analisis warna kulit YCbCr (`Cb [77..127], Cr [133..173]`) yang sangat cepat (< 5ms di Canvas) untuk mendeteksi centroid dan bounding box wajah/kepala.
         - Close-Up Zoom Pasfoto: Sistem otomatis memperbesar (zoom in) frame 3:4 agar kepala dan jilbab/peci memenuhi ~50-55% tinggi pasfoto resmi, memotong badan/tangan/pagar yang tidak perlu, dan menempatkan mata di 38% dari puncak foto.
         - Sinkronisasi Transformasi Manual: Jika pengguna membuka modal "Atur Manual (Opsional)", editor langsung berada pada posisi zoom dan sentral wajah yang sama dengan siluet panduan resmi.
         - Render otomatis ke HTML5 Canvas HD 600 × 800 pixel dan langsung disuntikkan ke berkas input formulir via `DataTransfer`.
      4. **Badge Keberhasilan & Opsi Fleksibel**:
         - Menampilkan badge hijau *"✓ Pasfoto 3:4 Otomatis Pas"*.
         - Menyediakan tombol sekunder ringkas *"Atur Manual (Opsional)"* jika kader ingin memperbesar/menggeser secara khusus, serta tombol *"Foto Asli"*.
      5. **Modal Manual Ultra-Responsif**: Jika dibuka, modal menggunakan kanvas kompak 195 × 260 pixel dengan `shrink-0` header dan footer serta `flex-1 min-h-0` body, dijamin 100% pas dan tidak pernah meluap di semua ukuran layar smartphone.
    - Seluruh 79 feature tests lulus 100% (241 assertions), Pint passed, Vite build sukses.
- 2026-09-12: Penyempurnaan Auto-Crop Pasfoto Formal Setengah Badan (Half-Body 3:4) & Anti-Noise Pagar Bambu:
    - Masalah: Pada foto pengguna yang diambil di luar ruangan (misal di depan pagar bambu/gedek dengan latar coklat kayu dan orang di samping), rumus warna kulit YCbCr lama menganggap pagar bambu sebagai "kulit" raksasa sehingga dimensi wajah membengkak (mencapai 60%+ lebar foto). Akibatnya, sistem menghitung `desiredH` melampaui tinggi foto dan membatalkan zoom (menampilkan 100% foto utuh dari kepala sampai tangan memegang kotak makanan, serta orang di samping).
    - Solusi & Algoritma Baru:
      1. **Filter Kulit Ketat & Anti-Bambu**: Menambahkan syarat saturasi hemoglobin `(r - b) >= 24` dan `(r - g) >= 12` yang membedakan kulit manusia dengan warna tan/kuning kayu pagar bambu.
      2. **Multi-Scale Candidate Face Window Scanning**: Pemindaian kandidat oval wajah berukuran realistis (12% s/d 30% lebar kanvas) dengan pembobotan kepadatan kulit, kontras fitur mata (area mata lebih gelap dari pipi), kontras siluet luar (perbedaan tajam antara kulit wajah dengan jilbab/busana hijau tua di sekelilingnya), dan pembobotan letak sentral subjek.
      3. **Framing Standar Pasfoto Setengah Badan (Half-Body Bust Framing)**:
         - Menetapkan rasio standar pasfoto setengah badan: kepala & jilbab menempati ~38% - 42% tinggi frame, ruang atas kepala (headroom) ~10%, dan sisa 50% di bawah adalah bahu & dada (setengah badan).
         - `cropH = Math.min(origH * 0.58, Math.max(origH * 0.28, faceHeight * 3.7))`.
         - Menjamin tangan memegang barang di bawah, orang di samping, serta pagar bambu di tepi kiri/kanan otomatis terpotong (*cropped out*).
         - Fallback cerdas jika wajah tidak terisolasi: otomatis mengambil 48% bagian atas terpusat (setengah badan), tidak pernah menyisakan 100% full-body.
      4. **Pembaruan Label UI**: Badge *"✓ Pasfoto 3:4 Setengah Badan Pas"* dan panduan teks yang menegaskan pasfoto setengah badan seimbang untuk ID Card & Sertifikat.
    - Seluruh 79 feature tests lulus 100% (241 assertions), Pint lolos, Vite build berhasil.
- 2026-09-12: Redesain Ergonomis Navigasi Halaman Registrasi Anggota (`anggota/register.blade.php`):
    - Masalah: Terdapat tombol oval putih raksasa bertuliskan "← Kembali ke Beranda" yang membentang 100% lebar layar pada tampilan mobile, bertumpuk di atas teks "Sudah memiliki akun? Masuk Portal". Hal ini membuat bagian atas halaman terasa penuh, canggung (*clumsy*), dan mendesak formulir utama ke bawah.
    - Solusi & Perbaikan:
      1. **Sleek Single-Line Mini Navbar**: Mengganti tombol blok raksasa dengan satu baris navigasi minimalis (`flex items-center justify-between`) yang tidak pernah membengkak/menumpuk pada layar smartphone.
      2. **Tautan Ringkas & Responsif**: Sisi kiri berupa tautan panah halus `← Beranda` dan sisi kanan berupa akses cepat `Masuk Portal →`.
      3. **Bottom Helper Navigation**: Menambahkan penunjuk navigasi cadangan di bagian bawah formulir (setelah tombol submit) untuk kader yang sudah selesai membaca/mengisi form.
      4. Diselaraskan juga pada tautan kembali di `resources/views/auth/cek-status.blade.php`.
    - Seluruh 79 feature tests lulus 100% (241 assertions), Pint lolos, Vite production build sukses.
- 2026-09-12: Optimasi Presisi Mobile-First Footer & Navigasi Formulir Registrasi (`anggota/register.blade.php`):
    - Masalah: Tampilan footer pada layar ponsel (handphone 360-390px) sempat terlalu tinggi, bertumpuk memanjang (kartu emoji kunci besar diikuti footer teks panjang berlipat-lipat), sehingga terlihat kurang rapi dan memakan ruang gulir.
    - Solusi & Perbaikan:
      1. **Unified Compact Bottom Card**: Menggabungkan bantuan akun dan identitas footer ke dalam satu wadah kartu putih ramping terpadu (`rounded-2xl bg-white border border-slate-200/80 shadow-2xs p-4`).
      2. **Baris 1 (Aksi Akun)**: Menampilkan prompt *"Sudah memiliki akun kader?"* di kiri dan tombol pill kompak *"Masuk Portal →"* di kanan.
      3. **Baris 2 (Navigasi & Identitas)**: Dipisahkan garis tipis `border-t border-slate-100 pt-3`, memuat tautan *"← Beranda Utama"* di kiri dan status *"● MPKSDI Karanganyar"* di kanan dalam 1 baris pas tanpa ada teks yang terpotong atau membengkak.
      4. **Top Nav Mobile**: Penyelarasan bar atas menjadi tombol kapsul lembut *"Masuk Portal →"* yang seimbang di samping tautan *"← Beranda"*.
    - Seluruh 79 feature tests lulus 100% (241 assertions), Pint lolos, Vite production build sukses.
- 2026-09-12: Peningkatan Aksesibilitas (Screen Reader & Keyboard) & Menghapus Rating Palsu di Landing, Halaman Form, dan Portal yang Diautentikasi:
    - Masalah: Situs sangat berat grafis (banyak emoji sebagai ikon, elemen `cursor-pointer` tanpa aksi, animasi reveal yang berulang) dan minim aksesibilitas (tanpa focus-visible, modal tanpa role/Esc, rating bintang palsu di kartu program meski tidak ada fitur rating, teks 8–10px terlalu kecil untuk audiens usia 40+).
    - Solusi & Perbaikan:
      1. **Focus Ring Global**: Blok `:where(...):focus-visible` ber-garis outline `#0284c7` di `resources/css/app.css`.
      2. **Hapus Affordance Palsu**: Menghapus `cursor-pointer`/`card-sheen` dari elemen non-aksi di `landing/index.blade.php` (badge hero, tile kategori, kartu ketua, stepper). Tile "17 Cabang" diubah menjadi `<a href="#sebaran">`.
      3. **Rating Palsu → Stat Asli**: Empat kartu program menukar garis bintang + "4.9/5.0" dengan ikon users SVG + jumlah asli dari LandingController (`number_format($totalKader/Aktif/Event/Lulus)`), pill durasi JP dipertahankan.
      4. **Modal Dialog A11y**: Modal lupa sandi (`auth/login.blade.php`) dan modal atur manual pasfoto (`components/pasfoto-fitter.blade.php`) memperoleh `role="dialog"`, `aria-modal`, `aria-labelledby`, dan `@keydown.escape.window` untuk menutup.
      5. **Semantik & Menandai**: `aria-current="page"` di link nav aktif (desktop+mobile) `layouts/app.blade.php`; hamburger `aria-expanded`/`aria-label` (app + landing); link skip-to-content + `id="main-content"` di `layouts/app.blade.php` dan landing; hapus `focus:outline-none` dari hamburger app.
      6. **Emoji → aria-hidden**: Emoji dekoratif di modal login (🔑💬🔍), catatan landing (💡), register (🕌👤), dan tip pasfoto (👆) dibungkus/dihapus agar tidak dibaca screen reader.
      7. **SVG Dekoratif aria-hidden**: Watermark & ilustrasi header empat kartu program landing.
      8. **Sweep Teks Mikro**: `text-[8/9/9.5/10px]` → `text-xs` di landing, login, register, app, cek-status, pasfoto-fitter. Counter dekoratif "01–04" dibiarkan.
      9. **Error Per-Field + Autocomplete**: 18 field register yang tervalidasi server memperoleh border merah/`aria-invalid`/pesan bawah (pola `@error` yang sama di tab 2/3); autocomplete `name/tel/email/bday/sex/street-address/address-level1-3/new-password`, `nik` → `off`.
      10. **Performa Gambar**: Hero gedung `fetchpriority="high" decoding="async"` (kandidat LCP); portrait ketua `loading="lazy" decoding="async"`.
      11. **Scroll-Spy + Reveal Satu-Kali**: `resources/js/app.js` scroll-spy nav desktop (IntersectionObserver `-30%/-60%`) dan satu-kali `revealObserver.unobserve` (tidak re-arm).
    - Verifikasi: `npm run build` sukses; `php artisan view:cache` semua Blade ter-compile bersih.
    - Catatan: Field nullable tab 2/3 (pendidikan, baca-quran) tanpa border per-field — ringkasan error atas menanganinya. Emoji di `admin/anggota/show.blade.php` dan `portal/profile.blade.php` di luar scope; hero jpg 582KB layak dikompres ke WebP terpisah.
- 2026-09-12: Redesain Dashboard Admin MPKSDI Menampilkan Visual Grafik di Awal:
    - Masalah: Dashboard admin sebelumnya langsung menyajikan kotak metrik angka statis ("Total Kader 0", "Menunggu Verifikasi 0") tanpa representasi analitik visual atau tren pertumbuhan perkaderan.
    - Solusi & Arsitektur Baru:
      1. **Controller Dedicated (`App\Http\Controllers\Admin\DashboardController`)**:
         - Menghitung metrik tren pendaftaran kader per bulan (6 bulan terakhir) menggunakan data `created_at` Anggota.
         - Menghitung proporsi status akun kader (aktif, menunggu verifikasi, ditolak, nonaktif).
         - Mengambil data kegiatan Baitul Arqam terkini dan antrean verifikasi pendaftar baru.
      2. **Integrasi Chart.js Engine**:
         - Menginstal `chart.js` via npm dan mengeksposnya ke `window.Chart` pada `resources/js/app.js`.
         - Dijalankan secara reaktif melalui Alpine.js (`growthChartData` dan `statusChartData`) dengan deteksi canvas context.
      3. **Visualisasi Grafik di Atas (First-Fold Visual Analytics)**:
         - **Grafik Tren Pendaftaran Kader Baru**: Smooth Area Line Chart bergradasi biru MPKSDI (`#005c8a`) dengan kurva halus (`tension: 0.38`), titik hover interaktif, tooltip gelap bernilai kader riil, dan kartu ringkasan di bawahnya.
         - **Grafik Distribusi Status Pendaftaran**: Donut Chart 72% cutout yang menampilkan komposisi akun terverifikasi (sky), pending (amber), ditolak (rose), dan nonaktif (slate). Dilengkapi fallback visual bila data masih kosong.
      4. **Tata Letak Dashboard Modern & Komprehensif**:
         - Header Pusat Kendali MPKSDI dengan tombol cepat *"Buat Event BA Baru"*.
         - First Section: Visual Charts Grid (Line Chart 8 kolom + Donut Chart 4 kolom).
         - Second Section: 4 KPI Cards (Total Kader, Menunggu Verifikasi, Event Aktif, Total Terlaksana).
         - Third Section: 2 Panel Operasional (Kegiatan Baitul Arqam Terkini + Kader Menunggu Verifikasi dengan tombol langsung *"Periksa"*).
         - Fourth Section: Quick Action Cards (Verifikasi & Pangkalan Data Kader + Manajemen Baitul Arqam).
    - Seluruh 79 feature tests lulus 100% (241 assertions), Pint lolos, Vite production build sukses.

- 2026-09-12: Perbaikan Flow Sistem (Notifikasi WA, Keamanan Tes, Status Kelulusan, Sertifikat, Rate Limit) & Refactor Otorisasi EventPolicy:
    - Masalah: Audit flow menemukan 5 celah fungsional + 1 utang arsitektur: (a) `pesanVerifikasiSukses`/`pesanPerbaikanDitolak` di `WhatsAppNotificationService` mati — tidak pernah dipanggil, admin chat manual setelah verify/reject; (b) `TesController::submit()` hanya cek `hasSubmitted`, tanpa cek `sesi_tes.status` maupun deadline server (`waktu_mulai + durasi_menit`) — timer client-side bisa dibypass; (c) `AbsensiService::recalculateAttendance` hanya update `nilai_kehadiran`, `status_kelulusan` cuma dihitung di `NilaiService::hitungNilaiAkhirDanRanking` — scan absensi tanpa buka halaman ranking → status basi; (d) sertifikat admin (`EventOutputController::certificate`) tanpa guard lulus/selesai padahal versi portal punya; (e) `/cek-status` publik tanpa throttle → enumerasi NIK/WA; (f) nol Policy/Gate, `ensureParticipant` diduplikasi byte-identical di 6 portal controller dan error 500 kalau user tanpa relasi anggota.
    - Solusi & Perbaikan:
      1. **Notifikasi WA verify/reject**: `Admin\AnggotaController::verify/reject` flash key `wa_url` via helper private `waUrl()` + `WhatsAppNotificationService::makeUrl()` (pola click-to-chat sama dengan `resetPassword`, tanpa HTTP client — cPanel tanpa queue/daemon). Blok `@if(session('wa_url'))` baru di `admin/anggota/show.blade.php` dengan tombol "Kirim Notifikasi ke WhatsApp Kader"; blok `wa_reset_url` lama tidak disentuh.
      2. **Hardening tes submit**: `Portal\TesController::submit()` cek `SesiTes.status === 'aktif'` (403) + deadline server `PesertaTesMulai.waktu_mulai->addMinutes(durasi_menit)->isPast()` (403). Tes yang belum pernah dibuka (`$mulai` null) tetap boleh submit.
      3. **Fix status_kelulusan basi**: satu baris di `AbsensiService::recalculateAttendance` — `updateOrCreate` kini ikut menulis `'status_kelulusan' => $this->statusKelulusan($persen)`. Tidak ada rekursi (`statusKelulusan` fungsi murni); jalur `hitungNilaiAkhirDanRanking` L156 idempoten.
      4. **Guard sertifikat admin**: `Admin\EventOutputController::certificate()` mirror guard portal — `abort_unless($event->status === 'selesai')` + `abort_unless($nilai->status_kelulusan !== 'Tidak Lulus')`, keduanya 403.
      5. **Rate limit cek-status**: `routes/web.php` POST `/cek-status` diberi `throttle:5,1` (5 request/menit/IP, middleware bawaan, tanpa provider baru).
      6. **Refactor otorisasi**: `App\Policies\EventPolicy` baru (auto-discovered Laravel 12) dengan method `participate(User, Event): bool` — null-safe (`$user->anggota !== null`), 403 bukan 500. `App\Http\Controllers\Controller` dapat trait `AuthorizesRequests`. 4 controller portal (Afektif, Angket, Rtl, Tes) ganti `ensureParticipant` → `$this->authorize('participate', $event)`, method private dihapus; Kehadiran + Sertifikat (varian `firstOrFail`) dapat `authorize` di depan query agar 403 konsisten. `use EventPeserta` dibuang dari 3 file (TesController masih pakai di `index()`).
      7. **Tests baru**: `TesSecurityTest` +2 (sesi inaktif → 403; deadline lewat → 403, status enum sesi_tes = `belum_buka|aktif|tutup`); `AdminAnggotaTest` +2 (verify/reject flash `wa_url`); `AbsensiTest` +1 (scan penuh → `status_kelulusan` = 'Lulus' tanpa buka ranking) + assertion status di test 75%; `CekStatusTest` +1 (request ke-6 → 429, `Cache::flush()` dulu); `AdminEventFlowTest` +2 (certificate 403 bila event belum selesai / peserta Tidak Lulus). `PortalEvaluationTest` cert test disesuaikan: `PenilaianAkhir` butuh `nilai_kehadiran => 100` (karena `report` panggil `hitungUlang` yang recompute status dari kehadiran) + event di-update ke `selesai` sebelum unduh sertifikat.
    - Verifikasi: 87 feature tests lulus 100% (265 assertions), Pint lolos, `view:cache` compile bersih lalu `view:clear`.
    - Catatan: Notifikasi WA tetap manual-klik (click-to-chat) karena deploy cPanel tanpa queue/daemon — jalur upgrade: tambah method `send()` di `WhatsAppNotificationService` bila ada API gateway WA (Fonnte/Wablas). Policy admin (`manage`) sengaja dilewati — middleware `role:admin` + `event_access` sudah cukup dan teruji.
    - 2026-09-12: Isolasi Database Pengujian PHPUnit (`phpunit.xml`): Mengaktifkan koneksi `DB_CONNECTION=sqlite` dengan `DB_DATABASE=:memory:` pada `phpunit.xml`. Hal ini mencegah eksekusi test runner (yang menggunakan trait `RefreshDatabase`) menyentuh, mereset, atau mengosongkan tabel `users` pada database MySQL/MariaDB lokal pengembang. Akun admin dipastikan selalu persisten di database MySQL.
- 2026-09-12: Redesain Navbar Minimalis Landing Page dengan Dropdown Menu (Opsi 1):
    - Menyederhanakan 8 deretan link menu menjadi 4 item ringkas pada desktop navbar:
      1. `Beranda` (link langsung).
      2. `Perkaderan ▾` (dropdown popover Alpine.js: Program BA, Keunggulan Mutu, Alur Peserta).
      3. `Peta 17 Cabang` (link langsung dengan dot badge).
      4. `Pusat Informasi ▾` (dropdown popover Alpine.js: Cek Status Pendaftaran, Tanya Jawab FAQ, Kontak & Sekretariat).
    - Desain dropdown kartu putih melayang (`rounded-2xl border border-slate-100 shadow-xl p-2`) dengan micro-interactions hover, ikon tematik bergradasi warna lembut, subteks deskriptif, dan transisi halus.
    - Mobile menu diselaraskan menjadi hierarki berkategori dengan penanda visual (`border-l-2 border-sky-100`) agar ramah layar sentuh ponsel.
    - Mobile Bottom Floating Nav Bar dirapikan: kontainer berbentuk rounded-full kapsul ramping dengan glassmorphism (`bg-white/92 backdrop-blur-xl`), tombol Dashboard/Daftar diselaraskan menjadi soft pill accent (`bg-sky-100/70 text-sky-700`) menggantikan blok gradien biru tebal yang timpang, indikator aktif diperhalus (`bg-sky-50 text-sky-600 rounded-full`), dan tipografi diperkecil proporsional (`text-[10px]`) agar simetris di layar HP.
    - Hero Section Landing Page dioptimasi untuk Handphone: teks badge atas dicegah wrap ("Pangkalan Data Resmi MPKSDI" di layar kecil), judul dan tombol CTA responsif (`items-stretch sm:items-center`), dua kartu floating info box besar disembunyikan di mobile (`hidden lg:flex`) dan digantikan oleh sebaris micro-chips ringkas di bawah tombol CTA, sehingga hero banner tidak lagi memanjang berlebih dan tidak bertabrakan dengan floating bottom bar.
    - Seluruh 87 feature & unit tests lulus 100% (265 assertions), Pint lolos, Vite production build sukses.
- 2026-09-12: Perbaikan Bug Tombol Hamburger Mobile Menu Landing Page:
    - Masalah: Tombol burger menu pada navbar mobile (`<header>`) tidak merespons klik/ketukan pengguna di smartphone.
    - Penyebab: (1) SVG path menggunakan `x-show` dan `x-cloak` secara langsung pada elemen `<path>` SVG (`http://www.w3.org/2000/svg`). Di lingkungan peramban mobile touch (WebKit/Blink), manipulasi display/style pada path SVG saat touchstart/pointer event membatalkan event click sintetis dan memicu anomali rendering SVG; (2) Ikon SVG tanpa `pointer-events-none` menelan sentuhan/tap sebelum mencapai tombol induk; (3) Belum adanya event stopping (`@click.stop`) dan penutupan otomatis saat klik di luar area (`@click.outside`) atau tombol escape.
    - Solusi:
      1. Tombol hamburger menggunakan binding atribut path dinamis tunggal `:d="mobileOpen ? 'M6 18L18 6M6 6l12 12' : 'M4 6h16M4 12h16M4 18h16'"` dengan `pointer-events-none` pada SVG. Menghilangkan ketergantungan `x-show`/`x-cloak` pada elemen SVG internal sehingga transisi ikon hamburger ke tanda 'X' bersifat instan, native, dan bebas bug.
      2. Menambahkan `@click.stop="mobileOpen = !mobileOpen"` serta penataan kelas interaksi sentuh: `relative z-10 p-2.5 cursor-pointer select-none active:bg-slate-200`.
      3. Menambahkan `@click.outside="mobileOpen = false"` dan `@keydown.escape.window="mobileOpen = false"` pada elemen `<header>`.
      4. Mobile dropdown menu dilengkapi transisi eksplisit (`opacity-0 -translate-y-2` ke `opacity-100 translate-y-0`), batas tinggi aman (`max-h-[calc(100vh-4.5rem)] overflow-y-auto`), dan padding bawah yang ergonomis (`pb-6`) agar seluruh menu tidak tertutup floating bottom bar pada layar ponsel pendek.
    - Verifikasi: 87 feature & unit tests lulus 100% (265 assertions), Pint lolos, Vite production build sukses.
- 2026-09-12: Peningkatan Menyeluruh Dashboard, Seeder Data Realistis, Audit Konfigurasi, dan Output Laporan Pelatihan PDF & Excel:
    - **1. Perbaikan Dashboard Secara Menyeluruh (Admin & Executive)**:
      - Admin Dashboard (`admin/dashboard.blade.php` & `DashboardController.php`):
        - Metrik kelulusan perkaderan: Menambahkan metrik `$totalLulus`, `$totalPesertaAll`, dan `$persenKelulusan%` pada kartu KPI ke-4, menggantikan placeholder.
        - Kartu Kegiatan Baitul Arqam Terkini: Dilengkapi tombol aksi 1-klik langsung (`Peringkat`, `Excel`, dan `PDF`) pada setiap kegiatan.
      - Executive Dashboard (`admin/executive.blade.php` & `ExecutiveController.php`):
        - Peningkatan grid metrik dari 3 kartu menjadi 4 kartu KPI komprehensif: Total Event BA, Total Peserta Terdaftar, Tingkat Kelulusan Kader (`$totalLulus` & `{{ $persenKelulusan }}%`), dan Rata-rata Nilai Akhir Perkaderan.
        - Tombol unduh laporan Excel langsung disematkan pada baris tabel di samping PDF dan Peringkat.
    - **2. Output Laporan Pelatihan Resmi (PDF & Excel)**:
      - Laporan Resmi PDF (`resources/views/admin/events/report-pdf.blade.php`):
        - Redesain total menjadi Dokumen Berkop Surat Resmi MPKSDI PDM Karanganyar dengan Logo MPKSDI dan Muhammadiyah.
        - Dilengkapi Card Metadata Kegiatan, Ringkasan Eksekutif KPI (Total Peserta, Lulus, Lulus Bersyarat, Tidak Lulus, Rerata NA, Nilai Tertinggi, Nilai Terendah), Tabel Rekapitulasi Lengkap 14 Kolom (Ranking, NIK, NBM, Nama, Cabang/Ranting, Pretest, Posttest, N-Gain, Afektif, Psikomotor, Keaktifan, Kehadiran %, NA, Status Kelulusan), Catatan Rumus Baku Standar, serta Lembar Pengesahan Tanda Tangan Ketua MPKSDI dan Master of Training.
      - Laporan Rekapitulasi Excel (`resources/views/exports/laporan-event.blade.php` & `app/Exports/LaporanEventExport.php`):
        - Format spreadsheet standar MPKSDI dengan header styling rapi (`#002b40`), metadata event, format teks NIK/NBM (`mso-number-format`), dan struktur DOM yang 100% kompatibel dengan PhpSpreadsheet HTML parser.
      - Hak Akses Rute: Rute `admin.events.exportExcel` disetarakan dengan laporan PDF di middleware `['role:admin,fasilitator,executive', 'event_access']`.
      - Tombol Akses Cepat: Tersedia di halaman Peringkat Nilai (`admin.events.ranking`), Dashboard Eksekutif (`admin.executive`), dan Dashboard Admin (`admin.dashboard`).
    - **3. Seeder Data Demo Realistis (`database/seeders/DemoDataSeeder.php`)**:
      - Terdaftar di `DatabaseSeeder.php` dan bersifat idempoten (`updateOrCreate`).
      - Pengguna terdaftar untuk semua peran: Admin (`admin@mpksdi.test`), Fasilitator 1 (`fasilitator@mpksdi.test` - Drs. H. Sriyanto, M.Pd.), Fasilitator 2 (`instruktur@mpksdi.test` - Ust. Ahmad Dahlan Al-Hafidz), dan Executive (`executive@mpksdi.test` - Dr. Muhammad Ihsan, M.Ag.).
      - 24 Kader Muhammadiyah Karanganyar yang tersebar di berbagai PCM (Colomadu, Tasikmadu, Jaten, Karanganyar, Tawangmangu, Kebakkramat, Gondangrejo, Matesih, Jumapolo, dll.): 20 aktif terverifikasi, 3 menunggu verifikasi (tampil di widget dashboard pending), 1 ditolak dengan catatan perbaikan KTP luar daerah.
      - 11 Entitas Organisasi (PDM, PCM, PRM, AUM RS PKU, AUM SMA Muh 1, Ortom Pemuda & Nasyiah).
      - 3 Event Perkaderan Nyata:
        1. Event Selesai: "Baitul Arqam Pimpinan PCM & PRM se-Kabupaten Karanganyar 2026" lengkap dengan 6 sesi, 3 kelompok outbound, rubrik afektif/psikomotor/RTL, soal CBT pretest & posttest, 14 peserta terdaftar, absensi lengkap, poin keaktifan, dan penilaian akhir matematis akurat (10 lulus, 2 lulus bersyarat, 2 tidak lulus) dengan ranking dan verification hash.
        2. Event Berlangsung: "Baitul Arqam Guru & Karyawan AUM Karanganyar Angkatan IV" (6 peserta, sesi materi aktif).
        3. Event Persiapan: "Baitul Arqam Tenaga Kesehatan RS PKU Muhammadiyah Karanganyar".
    - **4. Audit & Penyelarasan Konfigurasi**:
      - `config/app.php`: Set default timezone ke `Asia/Jakarta` (`APP_TIMEZONE`), default locale `id`, fallback locale `id`, faker locale `id_ID`.
      - `.env.example`: Ditambahkan `APP_TIMEZONE=Asia/Jakarta`, `ADMIN_EMAIL=admin@mpksdi.test`, dan `ADMIN_PASSWORD=ubah-segera`.
      - `.env`: Ditambahkan `APP_TIMEZONE=Asia/Jakarta`.
    - **5. Hasil Pengujian & Kualitas**:
      - Seluruh 88 unit & feature tests lulus 100% (270 assertions), Laravel Pint lolos rapi, dan Vite production build sukses.
- 2026-09-12: Redesain Total Dashboard Admin (Kerapian Visual & Keselarasan Warna MPKSDI):
    - **Masalah**: Screenshot pengguna menunjukkan ketidakharmonisan warna dan hierarki tampilan: header flat gray `#f3f5f8` tebal dan hampa, tombol biru neon terisolasi, 4 kartu KPI utama tenggelam di bawah grafik 300px+, warna donut chart acak (pink/magenta keras), subbar abu-abu bertabrakan dengan canvas, dan data sebaran kader per kecamatan belum ditampilkan.
    - **Solusi & Implementasi**:
      1. **Penyelarasan Warna (Color Harmony)**: Menghapus warna abu-abu kusam `#f3f5f8`. Mengadopsi palet otentik MPKSDI Karanganyar: Deep Ocean Blue (`#005c8a`), Midnight Navy (`#002b40`), Saffron Gold (`#f8bb00`), dan Emerald (`#059669`). Donut chart diselaraskan ke `#005c8a` (Aktif), `#f8bb00` (Menunggu), `#e11d48` (Ditolak), dan `#94a3b8` (Nonaktif). Subbar atas diselaraskan ke `bg-slate-50/90`.
      2. **Hierarki Logis & Rapi**:
         - **Header Banner**: Format kartu putih lengkung modern beraksen hairline, watermark batik halus (`opacity-[0.14]`), badge institusional, serta aksi ganda yang presisi (*Impor Excel* dan *Buat Event Baru* beraksen gold plus).
         - **Row 1 (KPI Metrics First)**: 4 kartu metrik utama (Total Kader, Menunggu Verifikasi, Event Aktif, Kelulusan Perkaderan) dipindahkan langsung ke baris teratas di bawah banner agar administrator mendapat gambaran operasional seketika (*instant situational awareness*).
         - **Row 2 (Visual Analytics Grid 8+4)**: Line chart pertumbuhan 6 bulan dengan gradient fill lembut (`#005c8a`), grid garis putus halus, serta Donut Chart dengan persentase dan legend bersih.
         - **Row 3 (Operational Command Center 7+5)**: Rekap 6 agenda perkaderan terkini dengan 3 tombol cetak output 1-klik (`Peringkat`, `Excel`, `PDF`) bersanding dengan antrean kader menunggu verifikasi.
         - **Row 4 (Sebaran Wilayah & Pusat Akses)**: Menampilkan sebaran kader per cabang (*Top PCM Karanganyar*) dengan bar progres proporsional serta jalan pintas modul data dan event.
      3. **Verifikasi**: 88 feature tests lulus 100% (270 assertions), Pint passed, build Vite bersih (3.29s).
- 2026-09-12: Integrasi Pola Latar Belakang Subtle, Halaman Pembaruan Sistem & Panduan Interaktif:
    - Menghadirkan utility class `.bg-grid-subtle` pada kanvas kerja dashboard untuk memberi tekstur geometris institusional yang tenang dan tidak sepi/hampa.
    - Menambahkan rute dan halaman `/updates` (`SystemHelpController::updates`) yang merinci changelog versi v1.0 s.d. v2.2, kartu sorotan rilis, serta roadmap fitur masa depan.
    - Menambahkan rute dan halaman `/panduan` (`SystemHelpController::panduan`) berbasis tab interaktif Alpine.js 4 peran (Administrator, Kader/Peserta, Fasilitator, Executive) dilengkapi panduan tahapan teknis dan akordeon FAQ operasional.
    - Menambahkan grup menu "Bantuan & Panduan" pada sidebar dan footer aplikasi.
    - Suite test baru: `tests/Feature/SystemHelpPagesTest.php` (4 test baru, 92 tests lulus total).
- 2026-09-12: Standarisasi Total Tipografi, Geometri Kontainer & Eliminasi Elemen "AI Slop" di Seluruh Sistem:
    - **Masalah**: Inkonsistensi hierarki ukuran tulisan ("BESAR KECIL TULISANNYA TIDAK KONSISTEN"), penggunaan font mikro yang sulit dibaca (`text-[10px]`, `text-[11px]`), font weight ekstrem (`font-black` 900 vs `font-extrabold`), kontainer gelembung (`rounded-3xl`), tombol lonjong pil (`rounded-full`), serta animasi memantul template SaaS (`card-lift-sm`, `card-sheen`, `hover:scale-[1.01]`).
    - **Solusi & Standarisasi Desain Sistem**:
      1. **Standar Tipografi Baku**:
         - Judul Halaman Utama: `text-xl sm:text-2xl font-bold tracking-tight text-slate-900`
         - Sub-judul / Section Header: `text-base font-bold text-slate-900`
         - Judul Kartu / Item: `text-sm font-bold text-slate-900`
         - Teks Konten / Body: `text-sm text-slate-600`
         - Metadata / Label / Badge: `text-xs font-semibold text-slate-700` atau `text-xs font-medium`
         - Menghapus tuntas kelas mikro acak `text-[10px]` dan `text-[11px]` serta bobot berlebih `font-black` di seluruh form operasional.
      2. **Standar Kontainer & Radius Geometris**:
         - Kartu & Section Box: `rounded-xl border border-slate-200/90 shadow-2xs bg-white`
         - Tombol Aksi Utama: `rounded-lg font-semibold text-xs px-3.5 py-2` (atau `px-4 py-2 text-sm` pada form submit panjang) dengan warna solid institusional (`bg-sky-600 hover:bg-sky-700 text-white`).
         - Kontrol Input: `rounded-lg border border-slate-200/90 text-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20`.
         - Menghapus semua efek hover memantul `card-lift-sm`, `card-sheen`, dan `hover:scale-[1.01]`.
      3. **Penyelarasan Komprehensif di 14+ File View**:
         - `admin/events/outbound.blade.php`: Header, card pos kelompok, tombol aksi, dan badge.
         - `admin/events/psikomotor.blade.php`: Header, rubric manager, scoring matrix table header, dan tombol simpan.
         - `portal/dashboard.blade.php`: Welcome hero card, event cards, tombol detail, dan metadata event.
         - `portal/kehadiran.blade.php`: Header, kartu ID pass digital, barcode container, timeline presensi.
         - `portal/angket.blade.php`: Aspek evaluasi cards, rating 1-5 tiles, textarea box, dan sticky bottom bar.
         - `portal/afektif.blade.php`: Sub-aspek cards, Likert option tiles, dan submit button.
         - `portal/rtl.blade.php`: Question list cards, form textarea, file attachment inputs.
         - `portal/tes/index.blade.php`: Kartu daftar tes pretest/posttest, badge status durasi, dan tombol mulai.
         - `portal/tes/take.blade.php`: Sticky timer bar, soal CBT cards, radio option tiles, drawer navigator, submit modal.
         - `portal/tes/closed.blade.php`: Kartu pemberitahuan ujian ditutup, tombol kembali ke portal.
         - `portal/profile.blade.php`: Header identitas, live avatar box, field groups, dan bottom submit bar.
         - `auth/login.blade.php`: Kotak card login, form inputs, modal bantuan lupa password.
         - `auth/cek-status.blade.php`: Input pencarian NIK/WA, hasil penelusuran cards, dan alert catatan penolakan.
         - `anggota/register.blade.php`: Stepper header, wrapper form 4-langkah, tombol submit, dan bottom helper navigation.
         - `system/updates.blade.php`: Header, badge versi rilis resmi `Versi 2.2.0 Stable`, timeline cards, roadmap cards.
         - `system/panduan.blade.php`: Role tabs, workflow steps, dan accordion FAQ.
    - **Hasil Verifikasi**:
      - 92 feature & unit tests lulus 100% (286 assertions).
      - Vite production build sukses tanpa error (67 modules transformed).
      - View cache compile bersih (`php artisan view:clear`).
- 2026-09-12: Perapian Total Dashboard & Integrasi Pola Tradisional Batik Solo Kawung:
    - **Identitas Kultural Batik Terpadu**:
      - Mengganti latar belakang kanvas teknis `.bg-grid-subtle` dengan motif vektor autentik `.bg-batik-subtle` (Batik Kawung Solo 140×140 px seamless tile di `#005c8a` opasitas 4.5% dan aksen titik emas `#f8bb00`) pada master layout `layouts/app.blade.php`.
      - Membungkus header dashboard menjadi **Header Hero Card** terstruktur (`rounded-xl border border-slate-200/90 shadow-2xs`) dilengkapi ornamen sudut `<x-batik-corner position="top-right">` resmi MPKSDI.
      - Menambahkan ornamen batik corner `<x-batik-corner>` pada header modul admin lainnya (`admin/executive`, `admin/events/index`, `admin/anggota/index`, `admin/users/index`, `system/updates`, `system/panduan`).
    - **Perapian Antarmuka Dashboard Administrasi**:
      - **Kesimetrisan KPI Cards**: Menstandarisasi seluruh 4 kartu metrik ke bingkai putih border slate seragam (menghilangkan border kuning asimetris pada kartu verifikasi), dengan format nilai `[angka besar] [unit kecil]` dan footer metadata yang serasi.
      - **Penyederhanaan Chart**: Menghapus 3 sub-kotak duplikat di bawah grafik garis dan menggantinya dengan analitik pertumbuhan 6 bulan, bulan berjalan, dan rerata kelulusan.
      - **Kerapian Agenda Perkaderan**: Judul kegiatan tidak lagi terpotong dots karena tombol aksi dibuat cerdas/kondisional (event `persiapan` menampilkan tombol `Kelola Event`, sedangkan event `berlangsung/selesai` menampilkan `Peringkat/Excel/PDF`).
    - **Verifikasi**: 92/92 feature & unit tests lulus 100% (286 assertions), Vite production build sukses.
- 2026-09-12: Implementasi Form Persistence on Refresh (Penyimpanan Draf Otomatis /daftar):
    - **Solusi Masalah Kehilangan Data Pengisian Form**:
      - Pengguna yang mengisi formulir pendaftaran anggota mandiri (`/daftar`) tidak akan kehilangan data isian jika browser ter-refresh (F5), tab tertutup, atau jaringan terputus sebelum formulir terkirim.
      - Data formulir disimpan secara instan dan debounced (400ms) ke `localStorage` dengan key `mpksdi_register_draft_v1` saat pengguna mengetik, memilih dropdown, mencentang checkbox, memilih radio, atau berpindah tab.
    - **Keamanan Kredensial Pengguna**:
      - Kolom sensitif kata sandi (`password` dan `password_confirmation`) serta CSRF token `_token` dikecualikan secara ketat (100% tidak pernah disimpan ke browser storage).
    - **Dukungan Pasfoto & Pemulihan Reaktif**:
      - Menyimpan preview pasfoto (Base64 JPEG 3:4) dan memulihkannya kembali via `DataTransfer` ke `<input type="file" name="foto">` dengan mekanisme fallback yang aman dari batas kuota storage.
      - Menghubungkan custom event `@tab4-restore.window` dan `@tab4-clear.window` untuk menyinkronkan seluruh pilihan preset kemasjidan & kemasyarakatan di Tab 4.
    - **Pembersihan Draf Otomatis & Kontrol Manual**:
      - Begitu pendaftaran berhasil terkirim (`session('success')`), draf otomatis dibersihkan secara hening agar pendaftaran anggota berikutnya dimulai dari form bersih.
      - Disediakan tombol *"Hapus Draf"* yang dilengkapi modal konfirmasi jika pengguna ingin mengosongkan seluruh formulir.
      - Menambahkan banner informatif pemulihan draf dan indikator status draf tersimpan di sebelah stepper langkah.
      - Menambahkan aturan CSS global `[x-cloak] { display: none !important; }` di `resources/css/app.css` untuk mencegah layout flicker pada inisialisasi Alpine.js.
    - **Hasil Verifikasi**:
      - 94 feature & unit tests lulus 100% (293 assertions).
      - Laravel Pint lolos (0 issues).
      - Vite production build sukses (assets terkompilasi dalam 2.77s).
- 2026-11-07: Perbaikan UI Widget Chat SiKader (Dark Mode, Hint Layout, & Quick Actions):
    - **Penyelesaian Bug Hint Melayang**: Memperbaiki tag duplikat pada composer form dan memindahkan hint keyboard ("Enter kirim • Shift+Enter baris baru") ke dalam kontainer `.glass-composer` di bawah baris input/aksi; menambahkan `overflow-hidden` pada root modal dan `.sikader-shell`.
    - **Mode Gelap Terbaca & Konsisten (WCAG AA)**: Bubble bot menggunakan `rgba(255,255,255,0.07)` + `backdrop-blur-md` + border `rgba(255,255,255,0.10)` dengan teks terang `#E6EDF3` (kontras > 4.5:1). Bubble user solid `#005c8a` teks putih. Composer bergaya kaca gelap dengan fokus ring `--accent` (#f8bb00).
    - **Scrollbar Khusus**: Area percakapan menggunakan scrollbar tipis 6px dengan track transparan dan thumb semi-transparan (`.sikader-chat-scroll`), sedangkan baris chip menggunakan mask fade tepi kanan-kiri (`.sikader-chips-scroll`).
    - **Pola Quick Action Tunggal (Unified Pattern)**: Saat percakapan kosong (`!hasUserMessage`), kartu aksi ringkas 48px ditampilkan pada badan chat (1 kolom di <400px, 2 kolom di ≥400px) tanpa baris chip. Setelah pesan pertama dikirim (`hasUserMessage`), kartu awal disembunyikan dan baris chip di atas composer dimunculkan.
    - **Pembersihan Override & Penyegaran Sesi**: Menghapus selektor CSS agresif berklausul `!important` yang sebelumnya merusak kontras tema, mengisolasi storage history ke `sikader-chat-v2` untuk membuang draf sapaan 3-paragraf usang dan kartu ganda, merampingkan kartu aksi menjadi `h-[44px]` dengan ikon 16px, dan menyempurnakan baris konteks kegiatan agar rasio keterisian `4/20 Terdaftar` terbaca jelas pada mode terang maupun gelap.
    - **Optimalisasi Vertikal & Tombol Kirim**: Sapaan dipadatkan maksimal 2 baris tanpa emoji, baris konteks kegiatan menampilkan judul penuh dengan ringkasan status terdaftar "4/20 Terdaftar" dan progress bar amber. Tombol kirim konsisten memakai ikon paper plane dengan status disabled opasitas 40% saat kosong dan solid aksen emas saat ada teks.
    - **Verifikasi**: 252 tests PHPUnit lulus 100% (1.197 assertions), Laravel Pint lulus, Vite build sukses.

- 2026-11-07: SiKader memakai light mode saat halaman dimuat, tanpa mengikuti tema OS atau preferensi sikader-theme lama. Toggle tema tetap tersedia selama sesi halaman. Varian Tailwind dark dikendalikan kelas .dark agar pilihan light konsisten. Verifikasi: 23 SmartEnrollFeatureTest lulus (169 assertions), runtime init/toggle Node lulus, Vite build lulus melalui node node_modules/vite/bin/vite.js build (npm shim gagal pada path proyek dengan ampersand).

- 2026-11-07: Perbaikan Tampilan Box Peringatan / Notice SiKader & Penyesuaian Respons Sapaan (Chat Intent):
  - **Penyebab Teks Duplikat Vertikal**: Di `resources/views/admin/events/partials/smart-enroll-modal.blade.php`, template notice sebelumnya menampilkan dua elemen `<span>` bersebelahan (`msg.data.notice` dan `UI.previewPanel.uncertainNotice`) dalam kontainer flex, di mana keduanya berisi teks peringatan panjang yang sama dan elemen pertama menggunakan `shrink-0`. Hal ini menyebabkan teks kedua terhimpit ke sisi kanan dan memecah kata per baris secara vertikal.
  - **Penyempurnaan Struktur Box Notice**: Mengganti template notice menjadi satu kesatuan elemen `<div class="flex-1 leading-relaxed">` dengan icon SVG segitiga peringatan amber, label `Perlu Konfirmasi:`, dan teks peringatan tunggal yang mengalir rapi.
  - **Peniadaan Notice pada Sapaan Santai (Chat/Greeting)**: Menyesuaikan `adapter.js` dan normalisasi frontend agar respons sapaan atau percakapan ramah (`intent === 'chat'`) tidak otomatis diberi flag `confidence: 'low'` atau memunculkan box peringatan luar basis data.
  - **Penyertaan RAG Sources pada Balasan Sapaan Lokal**: Menghubungkan sumber RAG kegiatan pada balasan sapaan ramah di `SmartEnrollService.php` sehingga sitasi yang disebut dapat langsung diverifikasi.
  - **Verifikasi**: Vitest 10 tests lulus, PHPUnit 252 tests (1.197 assertions) lulus 100%, Laravel Pint lulus bersih, Vite production build sukses.

- 2026-11-08: Penyelesaian Celah Tembus Pandang / Garis Putih Bawah Modal SiKader (Backdrop Margin Leak):
  - **Penyebab**: Modal `smart-enroll-modal.blade.php` sebelumnya di-include di dalam kontainer `<div class="space-y-5 pb-20 lg:pb-6">` pada `resources/views/admin/events/enroll.blade.php`. Di Tailwind CSS 4, aturan `:where(.space-y-5 > :not(:last-child))` memberikan `margin-block-end: 20px` pada seluruh anak langsung kecuali yang terakhir. Karena di dalam file modal terdapat tag `<script>` setelah `<div>` modal, elemen modal menjadi `:not(:last-child)` sehingga terdorong margin bawah 20px. Hal ini menyebabkan backdrop `fixed inset-0` terpotong 20px dari tepi bawah layar, membocorkan baris kartu kandidat di latar belakang ("-, Kec. Karanganyar • NIK: 331327****").
  - **Solusi**:
    1. Memindahkan `@include('admin.events.partials.smart-enroll-modal')` keluar dari kontainer `.space-y-5` di `enroll.blade.php` sehingga modal berada di level root section.
    2. Menambahkan kelas utilitas `!m-0 m-0` pada elemen backdrop modal di `smart-enroll-modal.blade.php` untuk memastikan margin selalu bernilai 0 mutlak terlepas dari konteks parent.
  - **Verifikasi**: Vite production build sukses, 252 pengujian PHPUnit (1.197 assertions) lulus 100%, Laravel Pint lulus bersih (0 issues).

- 2026-11-08: Optimalisasi Performa & Kelancaran Interaksi SiKader Chatbot (60 FPS & Eliminasi Lag/Stutter):
  - **Identifikasi Penyebab Lag & Rasa Berat**:
    1. *Smooth Scroll Animation Contention*: Pada `smart-enroll-modal.blade.php`, observer `$watch('messages')` memanggil `el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' })` setiap 14ms pada tiap token teks yang di-stream. Pemanggilan animasi scroll berulang 50-70x per detik memicu thrashing pada thread animasi browser (ticker contention) dan penurunan drastis FPS.
    2. *Synchronous I/O Storage Thrashing*: Fungsi `persistHistory()` melakukan serialisasi `JSON.stringify` seluruh riwayat pesan dan menulis ke `sessionStorage` secara sinkron pada setiap kata saat streaming teks.
    3. *Artificial Delay / Latensi Buatan*: Pemanggilan `setTimeout` 600ms buatan saat modal dibuka, 500ms saat reset, dan 350ms saat klik chip starter memperlambat persepsi responsivitas UI.
    4. *Ketiadaan GPU Compositing*: Kontainer scroll belum memiliki instruksi isolasi layout (`contain`) dan akselerasi GPU.
  - **Solusi & Perbaikan**:
    1. *Pemisahan Mode Scroll*: Menghapus `$watch('messages')` looping. Menggunakan direct assignment instan `el.scrollTop = el.scrollHeight` selama proses streaming teks (tanpa animasi easing berulang), dan mencadangkan animasi smooth hanya saat kartu pesan baru ditambahkan.
    2. *Streaming Cadence & Debounce Storage*: Menyesuaikan streaming menjadi 2 kata per chunk (interval 22ms) dan menerapkan debounce 250ms pada penyimpanan `sessionStorage` yang hanya dieksekusi final setelah streaming selesai.
    3. *Instant Response*: Menghilangkan artificial delay pada sapaan awal dan starter chips sehingga respon chatbot instan.
    4. *CSS GPU Acceleration*: Menambahkan `contain: content; will-change: scroll-position;` pada `.sikader-chat-scroll` dan `transform: translateZ(0); backface-visibility: hidden;` pada `.sikader-shell`.
  - **Verifikasi**: Vitest 10 tests lulus, PHPUnit 252 tests (1.197 assertions) lulus 100%, Vite asset build sukses (3.14s).

- 2026-11-08: Peningkatan Kemampuan Pencarian Cerdas & Pelacakan Riwayat Perkaderan Kader (Smart Cadre History & Profile Lookup):
  - **Identifikasi Masalah**: Pertanyaan pengguna mengenai riwayat perkaderan perorangan (seperti "tolong carikan riwayat perkaderan husain faqihudin") sebelumnya menghasilkan respon "tidak ditemukan". Hal ini disebabkan oleh:
    1. *Intent Misclassification*: Prompt bahasa alami tidak terdeteksi sebagai pencarian kader melainkan jatuh ke rekomendasi umum.
    2. *Kekakuan Pencarian Teks*: Pencarian nama sebelumnya hanya menggunakan klausa LIKE persis (`%searchQuery%`) sehingga variasi ejaan (misal: "husain faqihudin" dengan 1 huruf d vs nama resmi "Husain Faqihuddin Asy Sarif, S.Pd" dengan 2 huruf d dan gelar) gagal cocok.
    3. *Ketiadaan Injeksi Riwayat BA pada Prompt LLM*: LLM eksternal tidak memiliki akses basis data langsung dan hanya menerima statistik agregat, sehingga berhalusinasi menyatakan kader tidak ditemukan.
  - **Solusi & Implementasi**:
    1. *Pencarian Cerdas Multi-Tier (Fuzzy, Metaphone, Substring, Levenshtein)*: Membuat method `searchCadres()` dan `extractCadreSearchQuery()` di `SmartEnrollService.php` yang membersihkan stopwords pencarian, menormalisasi gelar, dan mencocokkan token nama dengan toleransi ejaan/metaphone.
    2. *Ekstraksi Riwayat Perkaderan Terpadu*: Menambahkan method `getCadreHistory()` yang menggabungkan riwayat BA dari arsip SIMKader (`custom_fields['riwayat_ba']`) dan relasi `eventPeserta`, lengkap dengan nama event, tanggal, lokasi, dan status kelulusan.
    3. *Grounding Data Riil & Anti-Halusinasi*: Menginjeksikan hasil pencarian kader riil langsung ke system prompt LLM (`Geraikita`), serta menambahkan proteksi fallback ke RAG lokal terstruktur jika LLM salah menyimpulkan kader tidak ada.
    4. *Kartu Pendaftaran Interaktif*: Menyediakan data kandidat terformat beserta opsi kartu centang (`needs_candidate_card: true`) jika kader belum terdaftar di kegiatan yang sedang dibuka.
  - **Verifikasi**:
    - PHPUnit: Seluruh 253 tests (1.206 assertions) lulus 100%, termasuk unit dan feature test baru untuk pencarian riwayat perkaderan dengan ejaan tidak persis.
    - Vitest: 10 tests lulus 100%.
    - Laravel Pint: Lulus bersih.

- 2026-11-08: Transformasi SiKader Menjadi All-in-One Perkaderan Copilot (Natural Language Action Execution, WhatsApp Blast, Evaluasi 4 Pilar & Quick Export):
  - **Latar Belakang & Kebutuhan**: Mengembangkan seluruh rekomendasi kapabilitas agar SiKader tidak hanya berfungsi sebagai asisten rekomendasi dan tanya-jawab statis, melainkan menjadi All-in-One Copilot cerdas yang dapat mengeksekusi aksi pendaftaran, membuat draf komunikasi, menganalisis evaluasi peserta, dan menyediakan shortcut berkas.
  - **Fitur yang Diimplementasikan**:
    1. *Command-to-Action (Natural Language Auto-Enroll)*:
       - Memproses perintah langsung seperti *"Daftarkan 5 kader dari PCM Colomadu yang belum BA"*.
       - Otomatis mengekstrak kriteria wilayah, kuota, dan prioritas, menyaring kandidat terbaik, dan menyajikan kartu pendaftaran dengan pesan afirmasi instruksi serta tombol bulk enroll langsung.
    2. *WhatsApp Blast & Invitation Generator*:
       - Menghasilkan draf undangan perkaderan resmi berformat WhatsApp (emoji, jadwal, lokasi, kuota, dan tautan pendaftaran mandiri).
       - Menyediakan tombol aksi langsung `Kirim via WhatsApp` yang membuka URL `https://api.whatsapp.com/send?text=...` untuk kemudahan broadcast pimpinan cabang/ranting/AUM.
    3. *Copilot Analitik Penilaian & Deteksi Risiko 4 Pilar*:
       - Menghitung statistik riil: rata-rata pre-test, post-test, delta peningkatan, dan nilai akhir (skala 100).
       - Menjalankan simulasi yudisium: Lulus Murni, Lulus Bersyarat, Tidak Lulus.
       - *Deteksi Dini Risiko*: Mengidentifikasi peserta dengan nilai akhir di bawah Passing Grade/KKM (< 70) dan memberikan saran remedial pilar Kognitif & RTL sebelum penandatanganan Berita Acara.
       - *Deteksi Presensi*: Memperingatkan peserta dengan kehadiran sesi di bawah 75% sesuai standar SPM MPKSDI.
    4. *Quick Export & Document Links (One-Click Actions)*:
       - Menyediakan tombol pintas langsung di gelembung obrolan untuk mengunduh Rekap Excel (`admin.events.exportExcel`), Lembar Ranking (`admin.events.ranking`), dan Berita Acara Yudisium (`admin.events.berita-acara`).
    5. *Interactive Slash Commands (`/`) di Composer UI*:
       - Menampilkan floating quick-command bar saat pengguna mengetik `/` (`/kuota`, `/rekap`, `/undangan`, `/ranking`, `/export`).
       - Memungkinkan eksekusi instan deterministik berlatensi 0 ms tanpa jeda jaringan LLM eksternal.
  - **Verifikasi & Kualitas**:
    - PHPUnit: Seluruh 257 pengujian (1.230 assertions) lulus 100% tanpa ada kegagalan maupun regresi.
    - Vitest: 10 tests lulus 100%.
    - Laravel Pint: Lulus bersih (0 code style issues).
    - Frontend Assets: Vite production build sukses dan terkompilasi optimal.