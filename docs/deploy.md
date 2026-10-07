# Panduan Deploy ke cPanel

## Prasyarat Hosting

- **PHP 8.2 atau lebih baru**
- **MySQL 5.7+ / MariaDB 10.3+**
- **cPanel** dengan akses File Manager, phpMyAdmin, dan Cron Jobs
- SSH opsional (jika ada, lebih mudah; jika tidak, panduan ini tetap jalan)

## 1. Upload File

### Cara A: Via SSH (jika tersedia)

```bash
cd ~/public_html
git clone https://github.com/ZAKITRIPAMUNGKAS/mpksdikra.git .
composer install --no-dev --optimize-autoloader
```

### Cara B: Via File Manager cPanel (tanpa SSH)

1. **Build frontend di lokal**:
   ```bash
   npm run build
   ```

2. **Upload seluruh folder proyek** ke `public_html/` melalui File Manager → Upload (upload ZIP lalu extract).

3. **Upload vendor**: karena cPanel biasanya tidak bisa jalankan `composer install`, upload folder `vendor/` dari lokal setelah menjalankan:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
   Kompres folder `vendor/` ke ZIP lalu upload + extract. **Pastikan ZIP tidak corrupt**: gunakan format ZIP (bukan RAR).

## 2. Konfigurasi Document Root

cPanel default mengarah ke `public_html/`. Aplikasi Laravel perlu document root ke `public_html/public/`.

### Opsi A: Ubah Document Root via cPanel

1. Buka **Domains** → **Addon Domains** atau **Subdomains**
2. Edit domain, ubah **Document Root** dari `public_html` menjadi `public_html/public`

### Opsi B: .htaccess redirect (fallback)

Buat file `public_html/.htaccess`:

```apache
RewriteEngine On
RewriteRule ^(.*)$ public/$1 [L]
```

## 3. File .env

1. Salin `.env.example` ke `.env`
2. Isi konfigurasi database:

```ini
APP_NAME="ARQAM MPKSDI Karanganyar"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mpksdi-karanganyar.or.id
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
APP_FALLBACK_LOCALE=id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=namadatabase_arqam
DB_USERNAME=namauser_database
DB_PASSWORD=passworddatabase

ADMIN_EMAIL=admin@mpksdi-karanganyar.or.id
ADMIN_PASSWORD=PasswordPanjangAman123
```

3. Generate app key (SSH):
   ```bash
   php artisan key:generate
   ```
   Tanpa SSH: generate di lokal `php artisan key:generate`, copy nilai `APP_KEY` dari `.env` lokal ke `.env` produksi.

## 4. Database

### Buat database di cPanel

1. **MySQL Databases** → buat database baru (contoh: `namauser_arqam`)
2. Buat user database, assign ke database dengan **ALL PRIVILEGES**
3. Catat nama database, username, password → masukkan ke `.env`

### Jalankan migrasi

#### Via SSH

```bash
php artisan migrate --force --seed
```

#### Tanpa SSH: Buka URL sekali

1. Tambahkan route darurat di `routes/web.php` (sementara saja):

```php
Route::get('/migrate-seed', function () {
    if (app()->environment('production')) {
        return abort(404);
    }
    Artisan::call('migrate', ['--force' => true, '--seed' => true]);
    return 'Migration done. DELETE THIS ROUTE.';
});
```

> **PENTING**: Hapus route ini setelah migrasi berhasil! Jangan biarkan terbuka di production.

2. Buka `https://domainanda.com/migrate-seed`
3. Hapus route dari `web.php` dan re-upload file.

## 5. Storage & Symlink

Laravel butuh symlink `public/storage` → `storage/app/public`.

### Via SSH

```bash
php artisan storage:link
```

### Tanpa SSH

Buat file `public_html/public/storage.php` (workaround):

```php
<?php
// Jalankan sekali: https://domainanda.com/storage.php
symlink('../storage/app/public', 'storage');
echo 'Symlink created. DELETE THIS FILE.';
```

Buka URL, lalu **hapus file `storage.php`**!

## 6. Permission Folder

Pastikan folder berikut writable oleh PHP:

```bash
# Via SSH
chmod -R 775 storage bootstrap/cache
```

Via File Manager cPanel: klik kanan folder → **Change Permissions** → set 775.

## 7. Cron Job (Queue & Scheduler)

Laravel butuh scheduler untuk queue, notifikasi, dan pembersihan.

### Setup di cPanel

1. **Cron Jobs** → Add New
2. Interval: `Once per minute (* * * * *)`
3. Command:

```bash
/usr/local/bin/php /home/username/public_html/artisan schedule:run >> /dev/null 2>&1
```

> Ganti `/home/username` dengan path home direktori cPanel (lihat di panel kiri File Manager).

## 8. Build Frontend (Vite)

Frontend harus di-build di lokal karena cPanel tidak bisa menjalankan Node.js.

1. Di lokal:
   ```bash
   npm run build
   ```

2. Upload folder `public/build/` ke server (folder yang sama di `public/build/`).

3. Pastikan `public/build/manifest.json` ada.

## 9. Verifikasi

1. Buka `https://domainanda.com` → landing page tampil
2. Buka `https://domainanda.com/login` → halaman login tampil
3. Login sebagai admin: email dari `ADMIN_EMAIL`, password dari `ADMIN_PASSWORD`
4. Cek `https://domainanda.com/admin/dashboard` → dashboard tampil dengan data
5. Coba fitur utama: buat event, enroll peserta, scan QR

## 10. Troubleshooting

| Masalah | Solusi |
|---------|--------|
| 500 Internal Server Error | Cek `.env` `APP_DEBUG=true` sementara, lihat error log di `storage/logs/laravel.log` |
| 404 semua halaman | Pastikan `.htaccess` di `public/` ada, atau cek mod_rewrite aktif |
| Database connection error | Cek DB_HOST (biasanya `127.0.0.1` atau `localhost`), cek kredensial di phpMyAdmin |
| CSS/JS tidak tampil | Upload `public/build/` dari `npm run build` lokal; cek `APP_URL` di `.env` |
| File upload gagal | Permission `storage/` harus 775; cek `upload_max_filesize` di PHP settings |
| Queue tidak jalan | Cek cron job aktif; cek log di `storage/logs/` |
| Certifikat/PDF bercorak aneh | Install ekstensi PHP GD (`gd`), cek di PHP Selector |

## Backup

### Database

Buat cron job harian (via cPanel → Cron Jobs):

```bash
0 2 * * * /usr/local/bin/php /home/username/public_html/artisan db:backup
```

Atau gunakan fitur **Backup** bawaan cPanel untuk full backup mingguan.

### File

Download berkala folder `storage/app/` (foto anggota, file materi) dan folder `public/aset/`.

---

## Dukungan

Jika kendala deploy, hubungi developer:

- **gemala.dev** — [Instagram @gemala.dev](https://instagram.com/gemala.dev)
- **GitHub Issues** — [mpksdikra/issues](https://github.com/ZAKITRIPAMUNGKAS/mpksdikra/issues)