# Rencana Implementasi Penyempurnaan Sistem Perkaderan Terpadu

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Mengimplementasikan perbaikan esensial dan penambahan fitur prioritas tinggi yang kurang dari sistem: perbaikan kueri progres RTL pada dashboard portal, fitur batalkan/cabut presensi (undo attendance) di scanner/sesi, dan fitur pengingat acara (event reminder) via WhatsApp untuk peserta Baitul Arqam.

**Architecture:** Memanfaatkan arsitektur service yang sudah ada (`AbsensiService`, `NilaiService`, `WhatsAppNotificationService`), memperluas endpoint RESTful di Laravel dengan otorisasi berbasis role/event (`event_access`), serta menyempurnakan interaktivitas antarmuka Blade + Alpine.js.

**Tech Stack:** Laravel 12, PHP 8.2+, Blade, Alpine.js, Tailwind CSS, PHPUnit.

**Spec:** Dokumen `docs/agentic/PRD.md` dan `docs/agentic/RULES.md`.

## Global Constraints
- PHP 8.2+ kompatibel cPanel.
- Strict authorization (Admin & Fasilitator berwenang).
- Transaksi database dan rekalkulasi nilai otomatis saat kehadiran berubah.
- Semua tes automated harus lulus 100%.

---

### Task 1: Perbaikan Edge Case Kueri Status RTL di Portal Dashboard

**Files:**
- Modify: `app/Http/Controllers/Portal/DashboardController.php:24-29`
- Test: `tests/Feature/PortalEvaluationTest.php`

**Interfaces:**
- Consumes: `App\Models\Rtl`, `App\Models\EventPeserta`
- Produces: Progres array dengan `'rtl' => true` jika status RTL peserta adalah `'terkirim'` atau `'disetujui'`.

- [ ] **Step 1: Tulis test regresi untuk RTL status disetujui di Portal Dashboard**
- [ ] **Step 2: Jalankan test untuk memverifikasi kegagalan (atau ekspektasi)**
- [ ] **Step 3: Implementasikan perbaikan di `DashboardController.php`**
- [ ] **Step 4: Jalankan test dan pastikan lulus**

---

### Task 2: Fitur Batalkan / Cabut Presensi (Void / Undo Attendance)

**Files:**
- Modify: `app/Http/Controllers/Admin/AbsensiController.php`
- Modify: `routes/web.php`
- Modify: `resources/views/admin/absensi/scan.blade.php`
- Test: `tests/Feature/AbsensiTest.php`

**Interfaces:**
- Route: `DELETE /admin/events/{event}/sesi/{sesi}/absensi/{absensi}` (`admin.events.absensi.destroy`)
- Consumes: `Event`, `EventSesi`, `Absensi`
- Produces: JSON response `{ status: 'success', message: 'Presensi berhasil dibatalkan.' }`, otomatis memicu `recalculateAttendance` dan `hitungUlangPeserta`.

- [ ] **Step 1: Tulis feature test pembatalan presensi di `tests/Feature/AbsensiTest.php`**
- [ ] **Step 2: Daftarkan rute delete di `routes/web.php`**
- [ ] **Step 3: Implementasikan method `destroy` di `AbsensiController.php`**
- [ ] **Step 4: Pasang tombol aksi batalkan presensi di `resources/views/admin/absensi/scan.blade.php`**
- [ ] **Step 5: Jalankan test untuk memastikan lulus 100%**

---

### Task 3: Fitur Pengingat Acara WhatsApp (Broadcast & Individual Reminder)

**Files:**
- Modify: `app/Http/Controllers/Admin/PesertaController.php`
- Modify: `routes/web.php`
- Modify: `resources/views/admin/events/show.blade.php`
- Create: `tests/Feature/PengingatWaEventTest.php`

**Interfaces:**
- Route: `POST /admin/events/{event}/peserta/{peserta}/remind-wa` (`admin.events.peserta.remind-wa`)
- Route: `POST /admin/events/{event}/peserta/remind-wa-batch` (`admin.events.peserta.remind-wa-batch`)
- Consumes: `WhatsAppNotificationService::pesanPengingatEvent`, `WhatsAppNotificationService::kirimOtomatis`, `WhatsAppNotificationService::makeUrl`
- Produces: Redirect dengan flash message status keberhasilan pengiriman / fallback click-to-chat URL.

- [ ] **Step 1: Tulis feature test di `tests/Feature/PengingatWaEventTest.php`**
- [ ] **Step 2: Daftarkan rute pengingat di `routes/web.php`**
- [ ] **Step 3: Tambahkan method pengingat WA di `PesertaController.php`**
- [ ] **Step 4: Tambahkan tombol aksi kirim pengingat di Tab 1 `admin/events/show.blade.php`**
- [ ] **Step 5: Jalankan test suite dan verifikasi kelulusan**
