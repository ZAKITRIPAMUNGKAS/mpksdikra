# Aturan Kerja Agent

Baca `AGENTS.md`, seluruh `docs/agentic/*.md`, dan kebutuhan sumber sebelum mengubah kode.

## Prinsip
- Gunakan keputusan bisnis di `PRD.md` sebagai sumber utama.
- Jangan menambah fitur, dependensi, tabel, endpoint, atau konfigurasi tanpa kebutuhan jelas.
- Pakai Laravel, Blade, Tailwind, Alpine, PHP standard library, dan dependency terpasang bila proyek memakai stack referensi.
- Jangan menyalin sistem referensi mentah. Ambil pola yang relevan saja.
- Semua aturan nilai, kelulusan, sertifikat, dan WhatsApp harus dapat ditelusuri ke keputusan bisnis.

## Kode
- Diff kecil. Satu tanggung jawab per perubahan.
- Validasi semua input dari pengguna.
- Otorisasi server-side untuk tiap aksi sensitif.
- Gunakan transaksi database untuk proses multi-tabel atau data penting.
- Hindari hardcode token, password, API key, URL internal, angka bobot, dan role.
- Jangan menulis komentar kecuali logika sulit membutuhkan satu komentar singkat.
- Jangan menghapus data tanpa konfirmasi eksplisit dan mekanisme audit.

## Data sensitif
Biodata BA dapat memuat NIK, tanggal lahir, nomor HP, foto, kesehatan, dan informasi organisasi.
- Minimalkan data yang dikumpulkan dan tampilkan.
- Jangan masukkan data peserta nyata ke seed, test fixture, log, atau respons error.
- Batasi ekspor dan unduh dokumen ke role berwenang.
- File upload harus divalidasi tipe, ukuran, nama, dan aksesnya.

## Sebelum selesai
- Baca file sekitar sebelum edit.
- Jalankan formatter, lint, typecheck, dan test yang tersedia.
- Jika tidak ada test command, catat kekosongan di `MEMORY.md`.
- Perbarui `MEMORY.md` bila keputusan, struktur, atau perintah proyek berubah.
