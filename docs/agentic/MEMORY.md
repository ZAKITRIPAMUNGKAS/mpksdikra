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
- `php artisan test` — PHPUnit feature tests (102 test hijau per 2026-09-14).
- `vendor\bin\pint` — format PHP (Laravel style).
- `npm run build` — Vite production build.
- `php artisan migrate:fresh --seed --force` — reset skema lokal + seed admin (jangan di produksi).

## Log keputusan
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

