# Deploy ke cPanel

Target: hosting cPanel, PHP 8.2+, MySQL, tanpa SSH root (SSH opsional bila tersedia).

## Prasyarat
- PHP 8.2+ dengan ekstensi: `pdo_mysql`, `gd`, `mbstring`, `openssl`, `zip`, `bcmath`, `intl`, `fileinfo`.
- MySQL/MariaDB database + user dengan hak penuh.
- Composer tersedia (via SSH) atau jalankan `composer install --no-dev --optimize-autoloader` lokal lalu upload `vendor/`.
- Node.js (via SSH) atau jalankan `npm run build` lokal lalu upload `public/build/`.

## Langkah deploy

### 1. Siapkan database
Di cPanel → MySQL Databases:
- Buat database (mis. `cpuser_arqam`).
- Buat user + password kuat.
- Tambahkan user ke database dengan `ALL PRIVILEGES`.

### 2. Upload kode
Upload seluruh proyek ke folder di luar `public_html` bila memungkinkan, mis. `~/arqam-app`,
lalu arahkan `public_html` ke isi folder `public/` aplikasi.

Bila hosting tidak memisahkan folder: upload seluruh proyek ke `public_html`, lalu pindahkan
isi `public/` ke `public_html` dan sesuaikan `public/index.php`:
```php
require __DIR__.'/../arqam-app/vendor/autoload.php';
$app = require_once __DIR__.'/../arqam-app/bootstrap/app.php';
```

### 3. Konfigurasi `.env` produksi
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com
APP_KEY=<hasil php artisan key:generate>

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cpuser_arqam
DB_USERNAME=cpuser_dbuser
DB_PASSWORD=<password-kuat>

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=local
MAIL_MAILER=log
```
Jangan commit `.env`. Isi `ADMIN_EMAIL` dan `ADMIN_PASSWORD` sebelum seed.

### 4. Jalankan migrasi + seed (via SSH)
```bash
cd ~/arqam-app
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Bila tidak ada SSH: jalankan migrasi lokal terhadap database produksi lewat tunnel, atau
gunakan tool migrasi cPanel. `storage:link` bisa diganti rute admin satu kali atau symlink manual.

### 5. Build aset (bila tidak build lokal)
```bash
npm ci
npm run build
```
Hasil ada di `public/build/`. Pastikan terupload.

### 6. Cron job (queue + scheduler)
Di cPanel → Cron Jobs, tambahkan:
```
* * * * * cd ~/arqam-app && php artisan schedule:run >> /dev/null 2>&1
```
Queue tanpa daemon: tambahkan pemanggilan berkala, atau jalankan lewat scheduler:
di `routes/console.php` gunakan `Schedule::command('queue:work --stop-when-empty')->everyMinute();`

### 7. Keamanan
- `APP_DEBUG=false` di produksi.
- Folder `storage/app` privat (foto, bukti RTL). Jangan expose ke web.
- Backup database + `storage/app` terjadwal lewat fasilitas hosting.
- Ganti password admin seed segera setelah login pertama.

## Verifikasi pasca-deploy
- Buka `/up` → "Application up".
- Login admin, buat event, enroll 1 anggota uji.
- Unduh ID Card PDF dan laporan PDF → pastikan DomPDF + GD berfungsi.
- Jalankan `ba:siapkan-materi {eventId}` untuk isi rubrik default.

## Catatan keterbatasan cPanel
- Tanpa imagick: QR PNG sudah pakai GD (aman).
- Tanpa SSH: build + composer harus dikerjakan lokal, lalu upload `vendor/` dan `public/build/`.
- `php.ini`/`.user.ini`: naikkan `upload_max_filesize` dan `post_max_size` ≥ 8M untuk foto/RTL.
- Batas eksekusi PDF massal: gunakan antrian (cron queue) bila peserta banyak.
