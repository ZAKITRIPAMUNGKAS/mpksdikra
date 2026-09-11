# Pengertian Proyek Arqam

## Apa ini
Sistem operasional Baitul Arqam (BA) untuk perkaderan Muhammadiyah: pendaftaran peserta, pelaksanaan kegiatan, penilaian, dan pelaporan dalam satu aplikasi web.

## Fungsi utama
- Pendaftaran peserta lewat form publik bertoken atau impor Excel.
- Manajemen event: jadwal, lokasi, kuota, sasaran, status `persiapan → berlangsung → selesai`.
- Sesi/materi per event dengan presensi QR Code atau input manual panitia.
- Penilaian kognitif: pretest dan posttest dengan auto-grading dan N-Gain.
- Penilaian psikomotor: rubrik kustom yang diisi fasilitator.
- Penilaian afektif dan angket evaluasi penyelenggaraan diisi peserta.
- RTL (Rencana Tindak Lanjut) berisi pertanyaan essay dan upload bukti.
- Keluaran: ID Card QR/barcode, sertifikat, laporan perkaderan, laporan evaluasi, ekspor data.

## Pengguna
- Admin utama: membuat dan mengatur event, data, akses, serta seluruh keluaran.
- Fasilitator: menjalankan sesi, presensi, dan penilaian pada event yang ditugaskan.
- Peserta: mendaftar, melengkapi profil, mengerjakan tes/angket/RTL, unduh dokumen.
- Executive: membaca dashboard dan laporan tanpa hak kelola.

## Teknologi
Laravel 12, PHP 8.2, Blade, Tailwind CSS 4, Alpine.js, Vite, MySQL, DomPDF, Laravel Excel, Simple QR Code.

## Status
Fondasi dokumen agent dan skill sudah ada. Source code aplikasi belum dibuat.
Pola teknis dibaca dari `D:\website\SKRIPSI\SISTEM` sebagai referensi read-only; jangan disalin mentah dan jangan diedit.

## Batas MVP
Wajib: event, peserta, form bertab, akun/role, QR ID Card, sesi, presensi, tes, psikomotor, angket, laporan, sertifikat.

Ditunda sampai spesifikasi siap: integrasi SIMPEKA, pengiriman WhatsApp API, pamflet otomatis, AHP-SAW, AI, dan ranking.

## Aturan nilai sementara
- Kehadiran adalah syarat kelulusan: 100% lulus, 75% lulus bersyarat.
- Kehadiran tidak diperlakukan sebagai komponen nilai akademik sampai pemilik proses menetapkan rumus resmi.
- Keaktifan bernilai 1 poin per aksi: tanya, jawab, kultum, imam, adzan, juara outbound.
- Afektif dikonversi ke skala 0-100.
- Kognitif memakai pretest, posttest, dan N-Gain.

## Keputusan bisnis yang belum ada
Lihat `PRD.md` bagian "Keputusan belum ada". Jangan menganggapnya aturan final. Tandai `TODO keputusan bisnis` atau tanyakan pengguna.

## Dokumen terkait
- `PRD.md`: kebutuhan dan batas scope.
- `RULES.md`: aturan kerja dan keamanan data.
- `MEMORY.md`: status, keputusan aktif, perintah verifikasi.
- `DEBUG-RULES.md`: urutan debug dan larangan.
