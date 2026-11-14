# Spesifikasi Desain: Smart Enroll & Bulk Name Matcher (dengan Integrasi Geraikita AI)

**Tanggal:** 2026-11-06  
**Status:** Draf Tinjauan  
**Dokumen Terkait:** `docs/agentic/PRD.md`, `docs/agentic/RULES.md`, `docs/agentic/MEMORY.md`

---

## 1. Latar Belakang & Masalah
Dalam operasional Baitul Arqam (BA), panitia sering menerima daftar nama peserta secara massal dari pimpinan cabang (PCM), pimpinan ranting (PRM), amal usaha (AUM), atau panitia lokal (misalnya daftar 30–100 kader dalam format Excel, lembar Google Sheet, atau daftar teks di grup WhatsApp).

Proses pendaftaran peserta (*enroll*) manual yang ada saat ini mengharuskan admin mencari nama satu per satu di kolom pencarian, mencentang kotak, dan berpindah-pindah halaman paginasi. Hal ini memakan waktu lama (*time-consuming*) dan rentan melewatkan kader.

Selain itu, panitia sering membutuhkan bantuan untuk:
1. Menyeleksi peserta secara proporsional (misalnya kuota 30 peserta, 50% putra dan 50% putri, memprioritaskan kader aktif yang belum pernah mengikuti Baitul Arqam sebelumnya).
2. Mengetahui secara instan siapa saja nama delegasi yang **belum memiliki akun di sistem Arqam**, serta menyediakan pesan ajakan WhatsApp instan agar calon peserta segera mendaftar mandiri.

---

## 2. Tujuan & Sasaran
1. **Pencocokan Nama Massal (Bulk Matcher)**:
   - Menerima input daftar nama melalui upload berkas Excel (`.xlsx`, `.xls`), CSV, maupun tempel teks (*paste text*) langsung dari WhatsApp/Word.
   - Mencocokkan nama secara cerdas dengan normalisasi gelar (*S.Pd, M.Pd, S.T, H., Hj., Drs., Dr., dll.*).
   - Membagi hasil dalam 3 kategori jelas:
     - **Siap Enroll**: Ditemukan sebagai kader aktif dan belum terdaftar di kegiatan ini.
     - **Sudah Terdaftar**: Sudah masuk sebagai peserta di kegiatan ini.
     - **Belum Punya Akun**: Belum terdaftar di database MPKSDI.
2. **Tindak Lanjut Kader Belum Terdaftar**:
   - Menghasilkan draf undangan WhatsApp 1-klik yang dipersonalisasi dengan tautan registrasi mandiri (`/daftar`).
3. **Smart Assistant & AI Recommendation**:
   - **Lokal (Rule-Based)**: Wizard kriteria instan tanpa biaya token (kuota target, asal PCM, asal AUM/ORTOM, keseimbangan gender 50:50, prioritas kader yang belum pernah ikut BA).
   - **Generative AI (Geraikita API)**: Integrasi OpenAI-compatible endpoint `https://ai.geraikita.com/v1/chat/completions` menggunakan API key Geraikita (`gk-...`) untuk memproses instruksi bahasa alami.
   - **Graceful Fallback**: Jika kuota GeraiPoints habis (`402 Payment Required`) atau offline, sistem otomatis beralih ke Asisten Kriteria Cerdas Lokal tanpa gangguan.

---

## 3. Arsitektur Komponen

### 3.1 Layanan Inti (`App\Services\SmartEnrollService`)
Bertanggung jawab atas seluruh logika pemrosesan data, pembersihan nama, pencocokan, dan komunikasi AI:
- `normalizeName(string $name): string`:
  - Mengubah ke huruf kecil, menghapus tanda baca, dan menghapus gelar akademik/keagamaan umum di Indonesia.
- `matchNames(array $rawNames, Event $event): array`:
  - Melakukan pencarian exact & fuzzy match terhadap tabel `anggota` yang memiliki `user.status = 'aktif'`.
  - Mengembalikan struktur: `['matched' => [...], 'already_enrolled' => [...], 'unmatched' => [...]]`.
- `parseUploadedFile($file): array`:
  - Mengekstrak nama dari file Excel (`Maatwebsite\Excel` atau `SimpleXLSX`/CSV parser bawaan).
- `parseRawText(string $text): array`:
  - Memecah teks baris demi baris, membersihkan nomor urut (*1., 2., a., -*), dan membuang baris kosong.
- `recommendCandidates(Event $event, array $criteria): Collection`:
  - Menjalankan kueri cerdas berbasis kriteria filter dan bobot prioritas belum pernah ikut BA.
- `consultAi(string $prompt, Event $event): array`:
  - Menghubungi endpoint Geraikita (`POST https://ai.geraikita.com/v1/chat/completions`).
  - Menangani error 402/timeout dan mengembalikan struktur data kriteria yang dipahami sistem.

### 3.2 Controller (`App\Http\Controllers\Admin\SmartEnrollController`)
Endpoint AJAX/JSON untuk melayani interaksi modal Bento:
- `POST /admin/events/{event}/smart-enroll/match`
  - Validasi file (xlsx, xls, csv) atau string `names_text`.
  - Memanggil `SmartEnrollService::matchNames()`.
  - Mengembalikan respons JSON dengan detail kandidat dan daftar nama belum punya akun.
- `POST /admin/events/{event}/smart-enroll/recommend`
  - Menerima payload kriteria kustom.
  - Mengembalikan daftar rekomendasi peserta.
- `POST /admin/events/{event}/smart-enroll/ai-prompt`
  - Mengirim prompt admin ke Geraikita AI untuk ekstraksi kriteria, lalu menjalankan rekomendasi.
- `POST /admin/events/{event}/smart-enroll/execute`
  - Menerima array `anggota_ids`.
  - Melakukan pendaftaran massal dalam transaksi database aman (`DB::transaction`).

### 3.3 Konfigurasi Environment (`.env`)
```ini
GERAIKITA_API_KEY=gk-gfS4ZP7neCPHC1LTLR1vi4VDlNJC4soSdhk1yWHM
GERAIKITA_BASE_URL=https://ai.geraikita.com/v1
GERAIKITA_MODEL=deepseek-v4-flash
```

---

## 4. Rincian Antarmuka (Bento Modal Design)

### Tombol Pemicu di Halaman Enroll
Di bagian atas konsol pencarian [`resources/views/admin/events/enroll.blade.php`](file:///D:/website/04_Organisasi%20%26%20Komunitas/Portal-Muhammadiyah/MPKSDI%20KRA/resources/views/admin/events/enroll.blade.php):
- Tombol: `[⚡ Smart Enroll & Bulk Matcher]`
- Aksen warna: Biru tua Muhammadiyah (`#005c8a`) dengan badge kilat (*sparkle*).

### Modal Dialog Bento (`x-data="smartEnrollModal"`)
1. **Header Modal**:
   - Judul: **Asisten Smart Enroll & Pencocokan Nama**
   - Subtitle: *Cocokkan nama dari Excel/WA atau gunakan asisten cerdas untuk memilih kandidat perkaderan.*
2. **Navigasi 2 Tab**:
   - **Tab 1: 📋 Pencocokan Nama Massal (Excel / Teks)**
     - Opsi A: Dropzone Upload Berkas (`.xlsx`, `.xls`, `.csv`).
     - Opsi B: Textarea Tempel Daftar Nama (*"Tempel daftar nama dari pesan WhatsApp panitia..."*).
     - Tombol: `Analisis & Cocokkan Nama`.
   - **Tab 2: 🤖 Asisten Kriteria & Rekomendasi AI**
     - Pengaturan Kriteria Cerdas:
       - Jumlah Target Kuota.
       - Keseimbangan Gender (*Seimbang 50:50, Khusus Putra, Khusus Putri, Semua*).
       - Filter Cabang PCM & AUM.
       - Sakelar Prioritas: *Utamakan kader yang belum pernah mengikuti Baitul Arqam*.
     - Kotak Prompt Bahasa Alami (Geraikita AI):
       - Input teks fleksibel (Contoh: *"Pilihkan 20 kader dari PCM Colomadu dan Jaten dengan komposisi seimbang putra/putri"*).
     - Tombol: `Dapatkan Rekomendasi`.
3. **Panel Hasil Analisis / Rekomendasi**:
   - **Ubin Metrik Ringkas**:
     - 🟢 Siap Di-enroll: `X kader`
     - 🔵 Sudah Terdaftar: `Y kader`
     - 🟠 Belum Punya Akun: `Z nama`
   - **Daftar Kandidat Terpilih**:
     - Checkbox per kandidat + tombol `Pilih Semua`.
     - Tampilan kartu mini (Nama, NIK, PCM, AUM, Gender, Status BA).
   - **Daftar Belum Terdaftar & Fitur WhatsApp**:
     - Accordion daftar nama yang tidak ditemukan di sistem.
     - Tombol **`Salin Format Pesan Undangan WhatsApp`**:
       Otomatis menghasilkan format teks sopan berisi daftar nama bersangkutan dan instruksi klik pendaftaran mandiri di URL sistem.
   - **Tombol Eksekusi Bawah**:
     - `Batal` | `Enroll (X) Peserta Terpilih Langsung`

---

## 5. Rencana Pengujian Otomatis (Automated Testing)
1. **`test_smart_enroll_matches_exact_and_normalized_names`**:
   - Memastikan nama dengan gelar (*"Drs. Ahmad Dahlan, M.Pd"*) berhasil dicocokkan ke database anggota yang tersimpan sebagai *"Ahmad Dahlan"*.
2. **`test_smart_enroll_categorizes_unmatched_names`**:
   - Memastikan nama yang tidak ada di database masuk ke daftar `unmatched` dan tidak menyebabkan error.
3. **`test_smart_enroll_recommends_with_gender_balance_and_ba_history`**:
   - Memverifikasi filter kriteria lokal menghasilkan rasio gender seimbang dan memprioritaskan kader tanpa riwayat event.
4. **`test_smart_enroll_handles_geraikita_insufficient_quota_gracefully`**:
   - Memverifikasi saat Geraikita API mengembalikan `402 Payment Required`, sistem tetap memberikan rekomendasi berbasis kriteria lokal tanpa kegagalan sistem.
5. **`test_admin_can_bulk_enroll_from_matched_results`**:
   - Memastikan pengiriman form eksekusi berhasil mendaftarkan seluruh ID terpilih ke tabel `event_peserta`.

---

## 6. Pertimbangan Keamanan & Performa
- **Validasi File**: Membatasi ukuran upload maksimal 5MB dan tipe berkas hanya MIME Excel/CSV yang valid.
- **Batasan Transaksi**: Membungkus eksekusi pendaftaran massal dalam transaksi database (`DB::transaction`) untuk mencegah pendaftaran parsial jika kuota penuh.
- **Perlindungan Kuota**: Tetap mematuhi aturan batas `kuota` event yang telah ditetapkan.
