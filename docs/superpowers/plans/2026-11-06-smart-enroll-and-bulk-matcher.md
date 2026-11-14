# Smart Enroll & Bulk Name Matcher Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Membangun subsistem Smart Enroll dan Bulk Name Matcher di portal administrasi MPKSDI untuk mempermudah pendaftaran massal peserta Baitul Arqam melalui pencocokan nama (Excel/CSV/teks WhatsApp) dan rekomendasi cerdas (kriteria lokal + Geraikita AI).

**Architecture:** Layanan modular `SmartEnrollService` yang menangani parsing nama, sanitasi gelar akademik/keagamaan, pencocokan ke database kader aktif (`Anggota`), generator draf pesan WhatsApp bagi nama yang belum punya akun, serta engine rekomendasi kandidat (berbasis aturan lokal dan AI Geraikita). Di sisi antarmuka, disediakan modal Bento interaktif dengan Alpine.js pada halaman `admin/events/enroll.blade.php`.

**Tech Stack:** Laravel 12, PHP 8.2, Blade, Tailwind CSS 4, Alpine.js, Guzzle/Http Client, Geraikita OpenAI-Compatible API (`https://ai.geraikita.com/v1`).

**Spec:** `docs/superpowers/specs/2026-11-06-smart-enroll-and-bulk-matcher-design.md`

## Global Constraints
- Target deploy PHP 8.2+ cPanel compatible, tanpa dependensi eksternal yang memerlukan daemon.
- Proteksi transaksi database (`DB::transaction`) saat eksekusi pendaftaran massal.
- Proteksi kuota event (`kuota`) tetap ditegakkan secara server-side.
- Geraikita API terintegrasi secara graceful: jika kuota GeraiPoints habis (402) atau offline, sistem otomatis beralih ke rekomendasi kriteria lokal tanpa error.
- Seluruh tes otomatis harus lulus 100%, Pint 0 violations, Vite build bersih.

---

### Task 1: Konfigurasi & Service Inti (Name Parsing, Normalization, & Matching)

**Files:**
- Create: `app/Services/SmartEnrollService.php`
- Modify: `config/services.php`
- Modify: `.env`
- Test: `tests/Unit/SmartEnrollServiceTest.php`

**Interfaces:**
- Produces:
  - `SmartEnrollService::normalizeName(string $name): string`
  - `SmartEnrollService::parseNamesFromText(string $text): array`
  - `SmartEnrollService::parseNamesFromFile($file): array`
  - `SmartEnrollService::matchNames(array $rawNames, Event $event): array`
  - `SmartEnrollService::generateWhatsAppInvite(array $unmatchedNames, Event $event): string`

- [x] **Step 1: Write the failing unit tests for SmartEnrollService**
- [x] **Step 2: Add Geraikita config in `config/services.php` & `.env`**
- [x] **Step 3: Implement `SmartEnrollService` name parsing, normalization, matching, and WhatsApp generator**
- [x] **Step 4: Run unit tests and confirm all pass**
- [x] **Step 5: Code style check with Pint**

---

### Task 2: Service Engine Rekomendasi Kriteria Cerdas & Geraikita AI Client

**Files:**
- Modify: `app/Services/SmartEnrollService.php`
- Test: `tests/Unit/SmartEnrollRecommendationTest.php`

**Interfaces:**
- Produces:
  - `SmartEnrollService::recommendCandidates(Event $event, array $criteria): Collection`
  - `SmartEnrollService::consultAi(string $prompt, Event $event): array`

- [x] **Step 1: Write the failing tests for recommendation engine & AI fallback**
- [x] **Step 2: Implement `recommendCandidates()` with quota, PCM, AUM, gender balance 50:50, and prioritas belum pernah BA**
- [x] **Step 3: Implement `consultAi()` with Geraikita API client & graceful fallback on 402/timeout**
- [x] **Step 4: Run tests and confirm all pass**

---

### Task 3: Controller AJAX Endpoints & Routing

**Files:**
- Create: `app/Http/Controllers/Admin/SmartEnrollController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/SmartEnrollFeatureTest.php`

**Interfaces:**
- Routes:
  - `POST /admin/events/{event}/smart-enroll/match` -> `SmartEnrollController@match`
  - `POST /admin/events/{event}/smart-enroll/recommend` -> `SmartEnrollController@recommend`
  - `POST /admin/events/{event}/smart-enroll/ai-prompt` -> `SmartEnrollController@aiPrompt`
  - `POST /admin/events/{event}/smart-enroll/execute` -> `SmartEnrollController@execute`

- [x] **Step 1: Write failing feature tests for all 4 endpoints**
- [x] **Step 2: Register routes in `routes/web.php` with admin middleware**
- [x] **Step 3: Implement `SmartEnrollController` with request validation, transaction safety, and JSON responses**
- [x] **Step 4: Run feature tests and confirm all pass**

---

### Task 4: Frontend Bento Modal Component & Alpine.js Integration

**Files:**
- Create: `resources/views/admin/events/partials/smart-enroll-modal.blade.php`
- Modify: `resources/views/admin/events/enroll.blade.php`

- [x] **Step 1: Build the Bento modal markup with 2 interactive tabs: "Pencocokan Nama Massal" & "Asisten Rekomendasi & AI"**
- [x] **Step 2: Implement Alpine.js state controller (`smartEnrollModal`) for file upload, paste text, AI prompt, candidate selection, WhatsApp copy, and AJAX execution**
- [x] **Step 3: Add trigger button `[⚡ Smart Matcher & Asisten Rekomendasi]` in `enroll.blade.php`**
- [x] **Step 4: Compile assets with Vite build**

---

### Task 5: End-to-End Verification & Dokumentasi

**Files:**
- Modify: `docs/agentic/MEMORY.md`

- [x] **Step 1: Run full automated test suite `php artisan test`**
- [x] **Step 2: Run Pint code style check**
- [x] **Step 3: Verify local dev server responsiveness**
- [x] **Step 4: Update `docs/agentic/MEMORY.md`**
