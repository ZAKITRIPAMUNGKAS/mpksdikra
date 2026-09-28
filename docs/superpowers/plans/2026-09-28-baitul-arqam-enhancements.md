# Peningkatan Menyeluruh Sistem Baitul Arqam & Dashboard Khusus Pelatihan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Mengimplementasikan penyempurnaan menyeluruh pada sistem Baitul Arqam (BA) MPKSDI PDM Karanganyar, meliputi Dashboard Khusus Pelatihan (Mission Control), Modul Materi Sesi, Cetak ID Card Massal A4, Kloning Bank Soal, Penyempurnaan Sertifikat Dua Sisi (Depan & Transkrip Belakang), Validasi Kelulusan Terpadu, Cetak Kelompok Outbound, dan Transparansi Keaktifan di Portal Peserta.

**Architecture:** Memperkaya ekosistem Laravel 12 yang sudah ada dengan pendekatan modular dan minimum-diff. Menambahkan field materi pada `event_sesi`, menyempurnakan `NilaiService` dan `EventOutputController`, menyediakan view Dashboard Pelatihan komprehensif, template cetak PDF DomPDF presisi A4, serta endpoint kloning soal dan rekapitulasi.

**Tech Stack:** Laravel 12, PHP 8.2+, Blade, Tailwind CSS 4, Alpine.js, DomPDF, Simple QrCode.

**Spec:** `docs/agentic/PRD.md`, `docs/agentic/RULES.md`, `docs/agentic/MEMORY.md`

## Global Constraints
- Tetap menjaga 142 tests yang ada agar 100% passed tanpa regresi.
- Sesuai standar kode Laravel Pint (`vendor/bin/pint`).
- Jangan mengedit folder referensi `D:\website\SKRIPSI\SISTEM` (Read-Only).
- Semua operasi multi-tabel wajib dibungkus dalam `DB::transaction`.
- Format cetak PDF wajib строго proporsional dan tidak overflow.

---

### Task 1: Modul Materi Sesi Perkaderan (Database & Upload/Unduh)
**Files:**
- Create: `database/migrations/2026_09_28_230000_add_materi_to_event_sesi_table.php`
- Modify: `app/Models/EventSesi.php`
- Modify: `app/Http/Controllers/Admin/SesiController.php`
- Modify: `resources/views/admin/events/show.blade.php`
- Modify: `resources/views/portal/kehadiran.blade.php`
- Test: `tests/Feature/MateriSesiTest.php`

- [x] **Step 1: Write migration & model cast**
- [x] **Step 2: Update SesiController store & update to handle file upload and link**
- [x] **Step 3: Update admin view event show (modal sesi with materi inputs)**
- [x] **Step 4: Update portal kehadiran view with material download badges/buttons**
- [x] **Step 5: Write feature test, run test, format code, and commit**

---

### Task 2: Dashboard Khusus Pelatihan (Event Mission Control)
**Files:**
- Modify: `app/Http/Controllers/Admin/EventController.php`
- Modify: `resources/views/admin/events/show.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/EventDashboardTest.php`

- [x] **Step 1: Enhance `EventController::show` to compute real-time operational stats (active session, live scanner status, test sessions state, evaluation completion rates, leaderboard preview)**
- [x] **Step 2: Redesign `resources/views/admin/events/show.blade.php` into an executive Training Command Center with Live Session card, Quick Action Bar, Metric Cards, and Tabbed management**
- [x] **Step 3: Write tests for event dashboard data and authorization**
- [x] **Step 4: Format code and commit**

---

### Task 3: Cetak ID Card Massal Panitia (Batch Printing A4 Grid)
**Files:**
- Modify: `app/Http/Controllers/Admin/EventOutputController.php`
- Create: `resources/views/admin/events/idcard-batch-pdf.blade.php`
- Modify: `routes/web.php`
- Modify: `resources/views/admin/events/show.blade.php`
- Test: `tests/Feature/IdCardBatchTest.php`

- [x] **Step 1: Implement `EventOutputController::idCardsBatch`**
- [x] **Step 2: Create `idcard-batch-pdf.blade.php` with 8 ID cards per A4 page, neat cut lines, QR codes, and participant photos**
- [x] **Step 3: Add route and quick button in event dashboard**
- [x] **Step 4: Write test, verify DomPDF rendering, format code, and commit**

---

### Task 4: Kloning Bank Soal Pretest & Posttest Antar Event
**Files:**
- Modify: `app/Http/Controllers/Admin/SoalController.php`
- Modify: `resources/views/admin/events/soal.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/SoalCloneTest.php`

- [x] **Step 1: Add `cloneFromEvent` method in `SoalController`**
- [x] **Step 2: Add clone button and modal in `admin/events/soal.blade.php`**
- [x] **Step 3: Add route in `routes/web.php`**
- [x] **Step 4: Write feature test, verify, format code, and commit**

---

### Task 5: Penyempurnaan Sertifikat Resmi (Dua Sisi: Depan & Transkrip Belakang) + Cetak Massal
**Files:**
- Modify: `app/Http/Controllers/Admin/EventOutputController.php`
- Modify: `resources/views/admin/events/certificate-pdf.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/CertificateEnhancementTest.php`

- [x] **Step 1: Upgrade `certificate-pdf.blade.php` to 2-page landscape: Page 1 Official Certificate with authentic Muhammadiyah styling, Ketua MPKSDI & MoT signatures; Page 2 Official Transcript with Sessions, JP, Aspects & Grades**
- [x] **Step 2: Implement batch certificate download `certificatesBatch` for all eligible participants**
- [x] **Step 3: Update event view with batch certificate action**
- [x] **Step 4: Write test, format code, and commit**

---

### Task 6: Integrasi Kelulusan Terpadu (Kehadiran + Posttest + RTL)
**Files:**
- Modify: `app/Services/NilaiService.php`
- Modify: `resources/views/admin/events/ranking.blade.php`
- Modify: `resources/views/admin/events/report-pdf.blade.php`
- Test: `tests/Feature/NilaiAkhirTest.php`

- [x] **Step 1: Update `NilaiService::hitungNilaiAkhirDanRanking` to check posttest completion and RTL submission**
- [x] **Step 2: Update status display and legend in ranking and PDF report**
- [x] **Step 3: Ensure certificate authorization checks unified graduation criteria**
- [x] **Step 4: Run existing and updated tests, format code, and commit**

---

### Task 7: Cetak PDF Kelompok Outbound & Transparansi Keaktifan di Portal
**Files:**
- Modify: `app/Http/Controllers/Admin/OutboundKelompokController.php`
- Create: `resources/views/admin/events/outbound-pdf.blade.php`
- Modify: `app/Http/Controllers/Portal/KehadiranController.php`
- Modify: `resources/views/portal/kehadiran.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/OutboundPdfAndKeaktifanPortalTest.php`

- [x] **Step 1: Implement `OutboundKelompokController::exportPdf`**
- [x] **Step 2: Create `outbound-pdf.blade.php`**
- [x] **Step 3: Pass keaktifan records to `portal/kehadiran` view**
- [x] **Step 4: Write tests, format code, and commit**
