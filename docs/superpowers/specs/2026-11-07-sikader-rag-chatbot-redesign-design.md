# Redesain SiKader RAG Chatbot

**Status:** Draf untuk ditinjau  
**Tanggal:** 7 November 2026  
**Ruang lingkup:** widget SiKader pada halaman pendaftaran peserta kegiatan

## Tujuan

Membuat SiKader terasa tenang, jelas, dan tepercaya bagi admin kegiatan, terutama pada lebar 380–480 px dan mode layar penuh. Antarmuka harus membantu operator menemukan calon kader, memahami dasar jawaban RAG, lalu mendaftarkan peserta tanpa aksi tidak disengaja.

## Batasan dan keputusan

- Mengikuti Laravel Blade + Alpine + Tailwind yang sudah dipakai; tidak menambah pustaka besar atau mengubah penyedia RAG.
- Semua copy diletakkan pada satu konstanta i18n JavaScript. Nama dan NIK pada contoh/UI selalu tersamar.
- Endpoint RAG yang ada tetap dipakai. Efek jawaban bertahap di klien adalah adapter presentasi, bukan klaim bahwa penyedia RAG telah mendukung SSE token asli.
- Aksi mutasi tetap memakai endpoint server, kuota server, CSRF, dan transaksi yang ada.
- Riwayat percakapan disimpan per-kegiatan dalam `sessionStorage` browser agar tidak menambah penyimpanan data percakapan/peserta di server. Riwayat hilang ketika sesi browser berakhir.
- Pelaporan jawaban salah perlu disimpan secara server-side sesuai kebutuhan produk: query dan sumber yang telah disamarkan, ID kegiatan, ID admin, waktu, dan alasan opsional. Tidak menyimpan NIK utuh.
- Undo pendaftaran membutuhkan endpoint non-RAG baru dengan token aksi berumur 10 detik. Ini tidak mengubah logika RAG; endpoint hanya membatalkan pendaftaran yang dibuat oleh aksi SiKader tersebut.

## Pendekatan yang dipilih

1. **Adapter frontend di atas API yang ada.** Respons `match`, `recommend`, dan `ai-prompt` dipetakan ke kontrak pesan SiKader. Ini paling aman untuk memperbaiki UX tanpa mengganti backend RAG.
2. **Streaming presentasi.** Saat request berjalan, status berubah berurutan: “Mencari data kader…” → “Membaca dokumen…” → “Menyusun jawaban…”. Setelah respons ada, konten ditampilkan sedikit demi sedikit dengan `requestAnimationFrame`/timer yang menghormati reduced motion. Jika kelak backend mengirim SSE, adapter dapat menerima event yang sama tanpa mengubah komponen.
3. **Komponen hasil terstruktur.** Kartu dan tabel peserta dirender dari payload, bukan dirakit sebagai paragraf HTML. Hal ini menjaga tombol aksi, checkbox, dan masking NIK konsisten.
4. **Mock terisolasi.** Fixture hanya tersedia pada mode demo/test atau ketika respons API belum dapat menyediakan properti baru. Data mock tidak menjadi default produksi dan tidak memuat data pribadi nyata.

## Arsitektur antarmuka

```
SmartEnroll modal
└─ SiKaderShell
   ├─ ChatHeader (identitas, aktif, konteks, kuota/progres, kontrol)
   ├─ ChatTimeline (aria-live)
   │  ├─ MessageBubble
   │  ├─ RetrievalStatus / ResultSkeleton
   │  ├─ CitationChips + SourcesDisclosure
   │  └─ ResultRenderer
   │     ├─ MemberCards
   │     ├─ MemberTable
   │     ├─ UploadSummary
   │     └─ EnrollmentConfirmation
   ├─ CitationSidePanel
   ├─ QuickActionBar
   └─ SmartComposer (slash menu, attachment, send)
```

Alpine tetap menjadi pemegang state satu widget, tetapi markup dan renderer dipecah menjadi partial kecil. Modul adapter tidak mengetahui Blade/DOM; ia hanya mengubah respons endpoint menjadi kontrak pesan.

## Kontrak data klien

```js
// Semua properti tampilan dinormalisasi oleh adapter.
const message = {
  id: 'msg_...',
  role: 'assistant' | 'user' | 'system',
  content: '...',
  status: 'idle' | 'searching' | 'reading' | 'composing' | 'complete' | 'error',
  sources: [source],
  actions: [{ id, label, kind, payload }],
  component: { type, payload } | null,
  createdAt: 'ISO-8601',
};

const source = {
  id: 'source_...',
  title: 'Nama dokumen',
  page: 1,
  snippet: 'Potongan yang relevan…',
  url: null,
  score: 0.86,
};

// Adapter mendukung event masa depan dari backend.
// { type: 'token' | 'status' | 'sources' | 'component' | 'done' | 'error', payload }
```

Komponen hasil yang diizinkan: `member_cards`, `member_table`, `confirm`, dan `upload_summary`. NIK dibentuk hanya melalui helper mask, misalnya `331358****`.

## Desain visual dan responsif

- **Identitas:** navy/teal untuk struktur dan status; putih/netral untuk ruang baca; amber hanya untuk aksi primer dan sitasi. Tidak ada gradien berat atau bayangan tebal.
- **Tipografi:** Plus Jakarta Sans yang sudah tersedia, ukuran dasar 14 px, line-height lega. Pengaturan S/M/L mengubah token ukuran, bukan skala browser secara paksa.
- **Header:** tinggi ringkas namun tidak dipotong: identitas bot dan badge aktif; konteks kegiatan; kuota `20` dan terdaftar `4`, dengan progress bar berlabel. Pada 380 px, konteks turun ke baris kedua, bukan ellipsis.
- **Bubble:** pengguna solid teal/navy dengan teks putih; bot putih/netral ber-border lembut. Pesan masuk fade + geser 8 px selama 200 ms; motion dimatikan bila `prefers-reduced-motion`.
- **Aksesibilitas:** kontras minimal AA, target sentuh minimum 44 px, fokus terlihat, tooltip yang dapat fokus, serta label ARIA untuk seluruh tombol ikon. Navigasi `Tab`, `Shift+Tab`, `Enter`, dan `Escape` lengkap.
- **Tema:** token CSS untuk terang/gelap dengan tombol terlabel. Preferensi tema dan ukuran teks disimpan lokal per perangkat.

## Alur utama

### 1. Menempel daftar nama

1. Admin memilih `/tempel` atau menempel teks lalu kirim.
2. SiKader menampilkan status retrieval dan skeleton hasil.
3. Hasil menjadi tabel: cocok, mirip, atau tidak ditemukan; perbaikan nama dapat dilakukan inline.
4. Admin memilih peserta dan menekan “Daftarkan N terpilih”.
5. Kartu konfirmasi menyebut jumlah dan konteks kegiatan. Tombol default bukan aksi destruktif.
6. Setelah sukses, kuota diperbarui dari respons server dan toast menyediakan “Batalkan” selama 10 detik.

### 2. Unggah Excel/CSV

1. `/unggah` membuka file picker yang menerima format yang telah didukung server.
2. Tahap pra-proses menunjukkan ringkasan non-mutatif, contoh: `128 baris · 3 duplikat · 5 NIK tidak valid`.
3. Admin memilih “Lanjutkan pencocokan”; hasil lalu menggunakan tabel yang sama seperti alur tempel.

### 3. Pertanyaan RAG

1. Pesan melewati endpoint RAG yang ada.
2. Jika sumber cukup, jawaban memuat chip inline bernomor `[1]`, `[2]`; hover/focus membuka preview, klik membuka panel sumber berisi judul, halaman, dan cuplikan ter-highlight.
3. Disclosure “Berdasarkan N sumber” dapat dibuka dengan keyboard.
4. Jika skor retrieval di bawah ambang atau sumber tidak ada, tampilkan banner “Saya kurang yakin” dan jawaban hanya menyatakan keterbatasan/arah tindak lanjut—tanpa mengarang.

## Aksi dan kepercayaan

- Tombol salin, regenerasi, dan “Laporkan jawaban salah” tersedia pada jawaban bot yang relevan.
- Laporan memanggil endpoint baru dan menyimpan query + sumber yang sudah dimasking; UI memberi status tersimpan/gagal secara jujur.
- Semua mutasi pendaftaran selalu melalui konfirmasi. Undo hanya berlaku untuk batch yang baru dibuat, tidak pernah membatalkan pendaftaran sebelumnya, dan ditolak server setelah masa berlaku.
- Quick action awal mengikuti sisa kuota; setelah jawaban, hanya 2–3 tindakan lanjutan yang relevan ditampilkan.

## Perubahan berkas yang direncanakan

- `resources/views/admin/events/partials/smart-enroll-modal.blade.php` — shell minimal dan pemasangan Alpine.
- `resources/views/admin/events/partials/sikader/*.blade.php` — header, pesan, renderer hasil, sitasi, composer, dan panel sumber.
- `resources/js/sikader/constants.js` — seluruh teks Indonesia dan token label.
- `resources/js/sikader/adapter.js` — normalisasi endpoint/fixture dan streaming presentasi.
- `resources/js/sikader/state.js` — state Alpine, persistence session, keyboard, theme, font-size, dan aksi.
- `resources/css/app.css` atau stylesheet SiKader terisolasi — token tema, responsif, motion, focus, dan dark mode.
- `app/Http/Controllers/Admin/SmartEnrollController.php`, `app/Services/SmartEnrollService.php`, `routes/web.php` — hanya endpoint pra-proses/undo/feedback bila data yang dibutuhkan belum tersedia.
- migrasi/model laporan SiKader bila penyimpanan laporan belum ada.
- `tests/Feature/SmartEnrollFeatureTest.php` dan test baru yang relevan.

## Tahapan implementasi dan penerimaan

### Tahap 1 — Fondasi visual

Pecah partial, tetapkan i18n dan token, header/progress, tema terang-gelap, ukuran S/M/L, composer, dan kontrol keyboard. Verifikasi lint/Pint, view cache, serta tampilan 380 dan 480 px.

### Tahap 2 — Percakapan dan RAG

Tambahkan adapter status/streaming presentasi, skeleton, pesan, sitasi/panel sumber, fallback retrieval lemah, riwayat sesi, dan aksi salin/regenerasi/lapor. Verifikasi kontrak adapter dan aksesibilitas dasar.

### Tahap 3 — Hasil kader dan input cerdas

Tambahkan kartu/tabel kader, seleksi batch, parser daftar, command slash, pra-proses unggahan, quick action kontekstual, serta progres kuota. Verifikasi endpoint non-mutatif dan masking.

### Tahap 4 — Aksi aman

Tambahkan konfirmasi, undo 10 detik yang diverifikasi server, serta penyimpanan laporan jawaban salah bila belum ada. Verifikasi otorisasi, kadaluwarsa token, dan kuota.

### Tahap 5 — QA dan audit

Jalankan test, Pint, build, inspeksi visual di 380/480 px dan fullscreen, lalu uji manual tiga skenario wajib: tempel→cocok→konfirmasi, CSV duplikat/NIK invalid, dan pertanyaan tanpa basis data.

## Kriteria selesai

- Sembilan kelompok fitur pada brief tersedia memakai respons nyata atau fixture yang jelas bertanda demo.
- Tidak ada label penting terpotong pada 380 px maupun 480 px.
- Kontras AA, fokus keyboard, `aria-live`, dan reduced motion diverifikasi.
- Ketiga skenario manual selesai tanpa mengubah data secara tidak sengaja.
- Tidak ada perubahan pada logika retrieval/generasi RAG inti.

## Risiko terbuka

- Respons RAG saat ini mungkin belum mengirim skor/sumber lengkap. Adapter akan menampilkan fallback jujur; sitasi interaktif penuh memerlukan metadata sumber dari endpoint.
- Pra-proses XLSX yang akurat dan undo server tidak dapat benar-benar selesai hanya di klien. Keduanya memerlukan endpoint kecil di luar inti RAG.
- Penyimpanan laporan membutuhkan keputusan retensi data. Implementasi awal akan menyimpan payload minimum yang disamarkan.
