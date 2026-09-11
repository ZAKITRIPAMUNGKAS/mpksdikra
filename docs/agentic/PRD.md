# Arqam Website

## Tujuan
Sistem operasional Baitul Arqam untuk pendaftaran, pelaksanaan, penilaian, dan pelaporan perkaderan.

## Pengguna
- Admin utama: konfigurasi event, data, akses, dan keluaran.
- Fasilitator: menjalankan sesi, presensi, dan penilaian sesuai tugas event.
- Peserta: pendaftaran, profil, tes, angket, RTL, serta unduh dokumen.
- Executive: baca dashboard dan laporan.

## Struktur sistem
1. **Landing Page MPKSDI Karanganyar** — profil organisasi, program, berita, kontak.
2. **Portal Master Anggota** — kader/pengurus Muhammadiyah Karanganyar; pendaftaran mandiri dengan verifikasi admin; multi-organisasi/jabatan.
3. **Portal BA** — event perkaderan; peserta di-enroll admin dari master anggota; akun sama.

## Alur inti
1. Admin membuat event: nama, sasaran, jadwal, lokasi, kuota, status.
2. Admin enroll anggota aktif dari master sebagai peserta BA.
3. Sistem menyimpan biodata lengkap dan menghasilkan akun serta ID Card QR/barcode.
4. Panitia membuka sesi dan mencatat kehadiran QR atau input manual.
5. Peserta mengerjakan pretest, posttest, angket, afektif, dan RTL jika diwajibkan.
6. Fasilitator mencatat keaktifan dan penilaian psikomotor.
7. Sistem menghitung kelulusan, laporan, sertifikat, dan dokumen keluaran.

## Kebutuhan BA
- Form bertab: data diri, organisasi, aktivitas Muhammadiyah.
- Huruf form besar, jelas, dan mobile-first.
- Kehadiran: 1 poin setiap sesi termasuk salat dan outbound; kelulusan presensi 100% atau bersyarat 75%; presensi tidak menjadi nilai akademik kecuali aturan resmi berubah.
- Keaktifan: 1 poin per aksi tanya, jawab, kultum, imam, adzan, atau juara outbound.
- Psikomotor dapat dikustom admin, termasuk pemisahan AUM, percabangan, putra/putri.
- Kelompok outbound diacak sistem dengan kriteria admin.
- Afektif berasal dari angket dan dikonversi skala 0-100.
- Kognitif memakai pretest, posttest, dan N-Gain.
- Grade A/B/C opsional, tergantung keputusan pemilik proses.
- Output: laporan perkaderan, laporan evaluasi, ID Card, pamflet, sertifikat, ekspor data.
- Ranking peserta memakai urutan nilai akhir tertinggi ke terendah. Tidak memakai AHP, SAW, atau algoritma keputusan lain. Aturan nilai sama menunggu keputusan rumus nilai akhir.
- Pengingat WhatsApp untuk form dan presensi. Integrasi API belum diputuskan.

## Sumber materi
Materi, soal tes, rubrik psikomotor, butir angket, dan alur penilaian diambil dari Arqam Apps referensi di `D:\website\SKRIPSI\SISTEM`.
Referensi bersifat read-only. Ambil dan sesuaikan isi yang relevan dengan BA ini; jangan menyalin kode mentah dan jangan mengedit folder referensi.

## Batas MVP
Wajib: event, peserta, form bertab, akun/role, QR ID Card, sesi, presensi, tes, psikomotor, angket, ranking nilai akhir, laporan, sertifikat.
Ditunda sampai spesifikasi siap: integrasi SIMPEKA, pengiriman WhatsApp API, pamflet otomatis.
Keluar dari scope: AHP-SAW, AI, dan ranking berbasis algoritma keputusan.

## Deployment
Target hosting cPanel.
- PHP 8.2 atau lebih baru dan MySQL sesuai dukungan hosting.
- Aplikasi harus dapat berjalan tanpa akses shell root; siapkan langkah deploy tanpa SSH bila hosting membatasi.
- Cron job untuk penjadwalan bila dibutuhkan; queue berjalan lewat cron bila tidak ada worker daemon.
- Upload file, foto, dan dokumen berada di storage dengan akses terbatas.
- Backup database dan file dijadwalkan lewat fasilitas hosting.

## Keputusan belum ada
- Kepanjangan BA dan nama resmi sistem.
- Rumus nilai akhir; dokumen menyebut kehadiran bukan nilai tapi juga ada rumus NA.
- Bobot, batas lulus, aturan grade, dan syarat sertifikat.
- Otorisasi pencatatan aksi keaktifan.
- Template final ID Card, pamflet, sertifikat, dan laporan.
- Penyedia WhatsApp, biaya, template pesan, consent, status kirim.
- Kontrak SIMPEKA dan metode sinkronisasi.
- Aturan penyeimbangan kelompok outbound.

Jangan menganggap keputusan terbuka sebagai aturan. Tanyakan atau tandai sebagai `TODO keputusan bisnis`.
