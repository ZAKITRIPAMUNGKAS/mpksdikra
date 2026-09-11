# Form Master Anggota

Sumber: daftar pertanyaan yang diberikan pengguna. Berlaku untuk pendaftaran mandiri kader/pengurus dan input admin.

## Tab Data Diri
| Field | Catatan |
|---|---|
| Nama Lengkap (beserta gelar) | Wajib |
| Alamat Rumah | Wajib |
| Jenis Kelamin | Wajib, L/P |
| NIK | Wajib, angka, unik kandidat |
| NO NBM | Opsional |
| Jabatan di AUM | Opsional teks |
| Tempat Tanggal Lahir | Tempat + tanggal |
| Umur | Hitung otomatis dari tanggal lahir |
| Status Pernikahan | Wajib |
| Desa/Kelurahan | Wajib |
| Kecamatan | Wajib |
| Kabupaten | Wajib |
| Nomor WA Aktif | Wajib, normalisasi ke format internasional |
| Email Aktif | Opsional bila WA ada |
| Foto | Wajib, pasfoto; dipakai untuk ID Card BA |

## Tab Keagamaan
| Field | Catatan |
|---|---|
| Pendidikan SD (nama SD) | Teks |
| Bahasa yang dikuasai | Multi-pilih |
| Kemampuan membaca Al-Qur'an | Skala pilihan |
| Hafalan Al-Qur'an | Surat pendek / juz |
| Aktivitas sholat di masjid | Skala frekuensi |
| Aktivitas kajian ilmu agama | Skala frekuensi |
| Jumlah buku agama yang dimiliki | Skala rentang |
| Sumber informasi Muhammadiyah | Multi-pilih |
| Langganan Suara Muhammadiyah/Aisyiyah | Ya/Tidak |
| Lembaga ZIS yang diikuti | Multi-pilih |
| Tokoh yang paling berpengaruh | Teks |
| Alasan memilih tokoh tersebut | Textarea |

## Tab Organisasi
| Field | Catatan |
|---|---|
| Keaktifan di Muhammadiyah | Multi-pilih |
| Keaktifan di ORTOM Muhammadiyah | Multi-pilih |
| Organisasi selain Muhammadiyah | Teks |
| Struktur/organisasi anggota | Multi-relasi: PDM, PCM, PRM, AUM, ORTOM; satu anggota boleh banyak jabatan |

## Tab Harapan
| Field | Catatan |
|---|---|
| Harapan tentang Pimpinan Cabang Muhammadiyah | Textarea |
| Harapan yang didapat selama mengikuti BA | Textarea |

## Status akun
`menunggu_verifikasi → aktif → nonaktif`. Hanya admin mengubah status verifikasi. Anggota aktif boleh memperbarui profil tanpa verifikasi ulang.

## Aturan form
- Mobile-first, label dan input besar, satu grup per tab, progres tersimpan per langkah.
- NIK dipakai untuk mencegah duplikat; admin memutuskan merge bila bentrok.
- Nomor WA dipakai untuk pengingat; consent dicatat saat pendaftaran.
- Data kesehatan, ukuran kaos, dan kebutuhan konsumsi belum ada dalam daftar; perlu keputusan apakah ditambahkan untuk pelaksanaan BA.
