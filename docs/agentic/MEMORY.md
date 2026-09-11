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
- `php artisan test` — PHPUnit feature tests (63 test hijau per 2026-09-11).
- `vendor\bin\pint` — format PHP (Laravel style).
- `npm run build` — Vite production build.
- `php artisan migrate:fresh --seed --force` — reset skema lokal + seed admin (jangan di produksi).

## Log keputusan
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
- 2026-09-11: Rombak Landing Page & Modernisasi Form sesuai identitas MPKSDI:
  1. Landing Page publik: Fokus awal pada profil resmi MPKSDI PDM Karanganyar, gedung dakwah, dan infografis 4 pilar Baitul Arqam.
  2. Display Sebaran Data: Pemetaan interaktif 17 PCM se-Kabupaten Karanganyar dengan filter zona (Pusat & Barat, Lereng Lawu & Utara, Wilayah Selatan) dan agregat kader riil.
  3. Diferensiasi Atmosfer Layout: Sub-bar kontekstual yang tegas membedakan Workstation Manajemen PDM (Admin/Fasilitator/Executive) vs Personal Portal Kader (Anggota).
  4. Form Registrasi Anggota: Multi-step stepper 4 langkah, live photo preview instan, input ergonomis, dan tombol navigasi bersih.
  5. Form Profil Kader: Tabbed interface (Identitas, Keagamaan, Kemuhammadiyahan), foto avatar live preview, dan badge status verifikasi.
  6. Form Ujian CBT: Antarmuka CBT modern dengan sticky countdown timer, progress bar, grid navigator nomor soal interaktif (hijau/terisi vs abu-abu/kosong), dan modal konfirmasi pengumpulan.
  Semua 63 unit/feature tests lulus 100%, Pint lolos, build Vite berhasil.
- 2026-09-11: Implementasi Desain Landing Page Modern berdasarkan Referensi Platform Pelatihan Modern (Upskill):
  1. Navbar pill-style yang bersih dengan tombol outline 'Masuk Portal' dan solid pill 'Daftar Kader'.
  2. Hero Banner lengkung (`rounded-3xl`) dengan kontainer abu-abu lembut / aksen sky tint, breadcrumb navigasi, headline berbobot kuat, dan Quick Metrics Card pangkalan data resmi.
  3. Baris 8 Pilar & Kategori Perkaderan (Ideologi, Tarjih, Kemuhammadiyahan, Kepemimpinan, dsb.) dengan ikon warna-warni kotak halus.
  4. Section Keunggulan Standar Mutu 2 kolom dengan checklist bulat biru dan visual aksen kartu miring (*skewed card backdrop*).
  5. Section Program Perkaderan Baitul Arqam dalam kontainer sky-blue tint dengan 4 kartu pelatihan (Pimpinan, Guru AUM, Nakes PKU, TOT Instruktur) lengkap dengan rating bintang, durasi jam, dan badge level.
  6. Alur 4 Langkah Peserta (Pendaftaran, Verifikasi, Pelaksanaan BA, Kelulusan & RTL) berurutan dengan ikon ilustrasi modern.
  7. Sebaran 17 Cabang PCM Interaktif terintegrasi filter zona (Pusat & Barat, Lereng Lawu, Selatan).
  8. FAQ Accordion interaktif (Alpine.js) dan Footer multi-kolom modern.
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
