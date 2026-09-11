# Aturan Debug

## Urutan
1. Reproduksi masalah dengan input minimum dan tanpa data peserta nyata.
2. Pastikan role, event, status event, sesi, dan data relasi benar.
3. Baca log aplikasi dan error asli sebelum mengubah kode.
4. Temukan akar masalah. Jangan tambahkan fallback yang menyembunyikan error.
5. Buat perbaikan terkecil.
6. Tambahkan atau perbarui test regresi untuk logika non-trivial.
7. Jalankan verifikasi proyek penuh yang tersedia.

## Larangan
- Jangan debug dengan menonaktifkan validasi, CSRF, autentikasi, otorisasi, atau verifikasi QR.
- Jangan menampilkan NIK, nomor HP, token, password, isi kesehatan, atau data pribadi pada log/debug response.
- Jangan menjalankan reset, truncate, migrate:fresh, delete massal, atau perubahan schema destruktif tanpa persetujuan pengguna.
- Jangan mengubah data produksi untuk membuktikan hipotesis.

## Fokus domain
- Presensi: cek event peserta, sesi, status aktif, duplikasi, dan signature QR.
- Nilai: cek skala, pembulatan, bobot, kondisi kelulusan, dan sumber nilai sebelum menghitung ulang.
- Sertifikat: cek syarat kelulusan, template, hash verifikasi, serta otorisasi unduh.
- WhatsApp: cek consent, nomor normalisasi, template, idempotensi, status provider, dan jangan kirim ulang otomatis.

## Laporan bug
Catat: gejala, langkah reproduksi, akar masalah, file berubah, test dijalankan, dan risiko data.
