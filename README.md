<p align="center">
  <img src="public/aset/logo-mpksdi.webp" width="80" alt="Logo MPKSDI Karanganyar">
</p>

<h1 align="center">ARQAM — Sistem Perkaderan Terpadu Baitul Arqam</h1>

<p align="center">
  <strong>Majelis Pembinaan Kader dan Sumber Daya Insani (MPKSDI)<br>Pimpinan Daerah Muhammadiyah Karanganyar</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777bb3?logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/Laravel-12-red?logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/Tests-168%20passed-brightgreen" alt="Tests">
  <img src="https://img.shields.io/badge/license-MIT-blue.svg" alt="License">
</p>

---

## Tentang

Sistem operasional Baitul Arqam untuk pendaftaran, pelaksanaan, penilaian, dan pelaporan perkaderan di bawah MPKSDI PDM Karanganyar.

### Pengguna
- **Admin Daerah** — konfigurasi event, kelola data kader, akses, dan keluaran dokumen resmi
- **Admin Cabang (PCM)** — verifikasi dan pangkalan data kader di wilayah PCM-nya
- **Fasilitator** — menjalankan sesi, presensi, dan penilaian sesuai tugas event
- **Peserta** — pendaftaran, profil, ujian CBT, angket, RTL, unduh sertifikat & ID card
- **Executive** — dashboard analitik dan laporan

### Struktur
1. **Landing Page MPKSDI** — profil organisasi, program, peta 17 PCM, FAQ, kontak
2. **Portal Master Anggota** — pendaftaran mandiri kader Muhammadiyah Karanganyar dengan verifikasi admin; multi-organisasi & jabatan
3. **Portal BA** — event perkaderan; peserta di-enroll admin dari master anggota; akun sama

## Fitur Utama

- 🧾 Pendaftaran anggota dengan form bertahap 4 langkah dan pasfoto auto-fit 3:4
- 🏷️ Verifikasi & validasi kader oleh admin daerah / admin cabang
- 📅 Event Baitul Arqam: persiapan, berlangsung, selesai
- 🔐 Login kader: username = No WA, password = tanggal lahir (DDMMYYYY)
- 📸 Scanner presensi QR dengan fallback offline (localStorage queue)
- ✍️ Penilaian 4 aspek: Kognitif (pretest/posttest + N-Gain), Psikomotor, Afektif, Keaktifan
- 🧮 Bobot penilaian dinamis per event
- 🏆 Ranking nilai akhir dengan tiebreaker bertingkat
- 🏅 Grade A/B/C opsional per event (≥85 / ≥70 / <70)
- 📝 CBT ujian online dengan timer, navigasi grid soal, anti-resubmission
- 🎯 Kelompok outbound diacak dengan kriteria gender/PCM
- 📄 Output: ID Card QR batch A4, Sertifikat 2 sisi + QR verifikasi, Laporan PDF + Excel, Berita Acara Yudisium, Lembar Presensi
- 🖥️ Live slideshow presentasi yudisium 16:9
- 📊 Dashboard analitik dengan Chart.js (tren pendaftaran, distribusi status, lulus)
- 📱 Notifikasi WhatsApp click-to-chat untuk verifikasi, reset sandi, undangan
- 🗺️ Peta interaktif 17 PCM se-Karanganyar (Leaflet + OpenStreetMap)

## Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | PHP 8.2+, Laravel 12 |
| Database | MySQL / MariaDB |
| Frontend | Blade, Tailwind CSS 4, Alpine.js |
| Build | Vite |
| Chart | Chart.js |
| PDF | DomPDF, Simple QR Code (Bacon) |
| Excel | Laravel Excel (maatwebsite/excel) |
| Peta | Leaflet + OpenStreetMap |

## Quick Start (Lokal)

### Prasyarat
- PHP 8.2+
- Composer
- Node.js 18+
- MariaDB / MySQL (database: `arqam`)

### Instalasi

```bash
git clone https://github.com/ZAKITRIPAMUNGKAS/mpksdikra.git
cd mpksdikra

composer install
cp .env.example .env
# Edit .env: DB_DATABASE=arqam, DB_USERNAME=root, DB_PASSWORD=

php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

Login admin: `admin@mpksdi.test` / password dari env `ADMIN_PASSWORD` (default: `ubah-segera`).

### Perintah berguna

```bash
php artisan test                    # PHPUnit (168 tests)
vendor/bin/pint                     # Format code
npm run build                       # Vite production build
php artisan migrate:fresh --seed    # Reset DB + seed
php artisan ba:siapkan-materi {id}  # Isi rubrik/angket/RTL untuk event
```

## Deployment (cPanel)

1. Upload seluruh folder proyek ke `public_html/` atau subdomain
2. Set document root ke `public/`
3. Konfigurasi `.env`: `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
4. Jalankan via SSH (jika tersedia) atau buka `/migrate-seed` sekali untuk migrasi
5. Setup cron job untuk queue (opsional):
   ```
   * * * * * php /path/to/project/artisan schedule:run >> /dev/null 2>&1
   ```
6. Build frontend di lokal: `npm run build`, upload folder `public/build/`

Lihat `docs/deploy.md` untuk panduan lengkap.

## Testing

```bash
php artisan test
```

165+ test meliputi:
- Autentikasi & role
- Pendaftaran anggota (validasi, stepper, draf localStorage)
- Verifikasi & penolakan admin
- Admin cabang (scoping wilayah)
- Event CRUD, sesi, enroll peserta
- Absensi & presensi (QR, offline queue)
- Keaktifan, psikomotor, afektif, angket
- Ujian CBT (timer, resubmission, deadline)
- RTL (submit & review)
- Nilai akhir, ranking, grade
- Sertifikat & laporan PDF/Excel
- ID Card batch, Berita Acara Yudisium

## Keputusan Bisnis

Lihat `docs/agentic/PRD.md` untuk daftar keputusan yang sudah dan belum dibuat.

Beberapa hal masih menunggu keputusan:
- Rumus nilai akhir definitif (sudah ada bobot dinamis, tinggal final approval)
- Template final pamflet
- Integrasi WhatsApp API gateway (Fonnte/Wablas)
- Integrasi SIMPEKA
- Aturan penyeimbangan kelompok outbound yang definitif

## Lisensi

MIT License. Hak milik MPKSDI PDM Karanganyar.

---

<p align="center">
  <sub>Developed by <a href="https://instagram.com/gemala.dev">gemala.dev</a> • Bumi Intanpari, Karanganyar</sub>
</p>