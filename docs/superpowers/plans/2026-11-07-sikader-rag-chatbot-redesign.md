# SiKader RAG Chatbot Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Memodernkan widget SiKader menjadi asisten pendaftaran kader yang dapat diaudit, aman untuk aksi pendaftaran, dan nyaman dipakai pada lebar 380–480 px.

**Architecture:** UI Blade/Alpine dipecah menjadi shell dan partial komponen, dengan state tunggal yang mengonsumsi adapter respons endpoint Smart Enroll. Adapter menormalisasi respons RAG/matching yang sudah ada menjadi kontrak pesan dan memberi streaming presentasi di klien; endpoint Laravel baru hanya menangani preview non-mutatif, undo pendaftaran yang terbatas waktu, serta laporan jawaban salah.

**Tech Stack:** Laravel 12, PHP 8.2, Blade, Alpine.js 3, Tailwind CSS 4, Vite, PHPUnit/Pest runner Laravel.

**Spec:** `docs/superpowers/specs/2026-11-07-sikader-rag-chatbot-redesign-design.md`

## Global Constraints

- Ikuti Laravel Blade + Alpine + Tailwind yang sudah dipakai; jangan tambah dependensi besar atau mengubah penyedia/logika retrieval-generasi RAG.
- Seluruh teks UI Bahasa Indonesia disimpan di satu konstanta JavaScript; NIK selalu melalui helper masker.
- Streaming tanpa SSE harus diberi perlakuan sebagai presentasi UI, bukan stream token penyedia RAG.
- Konfirmasi wajib mendahului mutasi pendaftaran; kuota dan transaksi tetap divalidasi server-side.
- Riwayat disimpan per kegiatan di `sessionStorage`; laporan server menyamarkan query/sumber dan tidak menyimpan NIK utuh.
- Kontras minimal WCAG AA, target sentuh minimum 44 px, keyboard penuh, `aria-live`, fokus terlihat, serta `prefers-reduced-motion`.
- Workspace sedang memiliki perubahan pengguna yang belum dikomit. Stage/commit hanya berkas yang disebut di tiap tugas setelah verifikasi; jangan menyentuh berkas tak terkait.

## Review Focus

- File XLSX/CSV kosong, duplikat, atau NIK invalid harus menghasilkan ringkasan pra-proses yang jujur tanpa mendaftarkan siapa pun (Task 3).
- Batch undo tidak boleh menghapus peserta yang sudah ada sebelum aksi SiKader, milik event lain, atau dilakukan setelah 10 detik (Task 4).
- Respons RAG tanpa sumber atau dengan skor rendah harus menyajikan fallback “Saya kurang yakin”, bukan sitasi fiktif (Task 2).
- Pergantian ukuran font/tema serta tampilan 380 px tidak boleh memotong label, menghilangkan fokus, atau menutup composer (Task 1 dan 5).
- Aksi pendaftaran tidak boleh terpicu dari kartu hasil atau keyboard tanpa tahap konfirmasi eksplisit (Task 4).

---

### Task 1: Fondasi shell SiKader, token, dan aksesibilitas

**Files:**
- Create: `resources/js/sikader/constants.js`
- Create: `resources/js/sikader/state.js`
- Create: `resources/views/admin/events/partials/sikader/header.blade.php`
- Create: `resources/views/admin/events/partials/sikader/composer.blade.php`
- Create: `resources/views/admin/events/partials/sikader/timeline.blade.php`
- Modify: `resources/views/admin/events/partials/smart-enroll-modal.blade.php`
- Modify: `resources/css/app.css`
- Modify: `resources/js/app.js`
- Test: `tests/Feature/SmartEnrollFeatureTest.php`

**Interfaces:**
- Produces `window.smartEnrollChatbot(config)` with `openModal()`, `close()`, `sendComposer()`, `setTheme(theme)`, and `setFontSize(size)`.
- Produces `SIKADER_TEXT`, `SIKADER_THEME`, and `SIKADER_FONT_SIZES` from `resources/js/sikader/constants.js`.
- Consumes existing `eventTitle`, route URLs, `kuota`, and `pesertaAktif` passed by Blade.

- [ ] **Step 1: Write failing view tests for the shell contract**

Add assertions that the enrol page contains the labelled SiKader dialog, quota progress semantics, font/theme controls, `aria-live` timeline, 44px composer controls, containment classes, and no obsolete fixed-height/ellipsis-only context layout.

- [ ] **Step 2: Run the focused feature test to verify it fails**

Run: `php artisan test tests/Feature/SmartEnrollFeatureTest.php --filter=modal`

Expected: FAIL because the new accessible shell contract is not yet present.

- [ ] **Step 3: Implement the shell and modular state contract**

Replace only the modal composition with header/timeline/composer partials. Register the ES module from `resources/js/app.js`; implement body-scroll locking with restoration, Escape close, visible focus, session-persisted theme/font size, and responsive header wrapping.

- [ ] **Step 4: Run focused tests and formatting**

Run: `php artisan test tests/Feature/SmartEnrollFeatureTest.php --filter=modal` and `php vendor/bin/pint --dirty`

Expected: PASS; Pint exits 0.

- [ ] **Step 5: Record the stage checkpoint**

Inspect `git diff --check` for only Task 1 paths, then create a scoped commit only if the selected index contains no pre-existing user change. Otherwise record the verified stage in the final report without committing unrelated files.

### Task 2: Pesan, adapter RAG, sitasi, dan kepercayaan

**Files:**
- Create: `resources/js/sikader/adapter.js`
- Create: `resources/js/sikader/fixtures.js`
- Create: `resources/views/admin/events/partials/sikader/message.blade.php`
- Create: `resources/views/admin/events/partials/sikader/citations.blade.php`
- Modify: `resources/js/sikader/state.js`
- Modify: `resources/views/admin/events/partials/sikader/timeline.blade.php`
- Modify: `tests/Feature/SmartEnrollFeatureTest.php`

**Interfaces:**
- Produces `normalizeSmartEnrollResponse(response, context): SikaderMessage` and `createPresentationStream(message, options): AsyncIterable<SikaderEvent>`.
- `SikaderEvent.type` is one of `token`, `status`, `sources`, `component`, `done`, `error`.
- Produces `renderMessageWithCitations(content, sources)` that only converts citation IDs that exist in normalized sources.

- [ ] **Step 1: Write failing adapter and view-contract tests**

Add focused Vitest tests for source normalization, missing/low-score fallback, and ordered status events. Add Laravel view assertions for citation disclosure, source panel controls, report/copy/regenerate labels, and reduced-motion hooks.

- [ ] **Step 2: Run the new tests to verify they fail**

Run: `npx vitest run resources/js/sikader --runInBand` (or the repository’s equivalent single-run Vitest command) and `php artisan test tests/Feature/SmartEnrollFeatureTest.php --filter=sikader`

Expected: FAIL because adapter/component contracts do not yet exist.

- [ ] **Step 3: Implement the normalized message pipeline**

Map current `ai-prompt`, `match`, and `recommend` JSON fields into the agreed message/source/component shapes. Implement sequential retrieval statuses, skeleton cards, word/token presentation after response, source preview on focus/hover, source side panel on click, `sessionStorage` history per event, and fixture gating for demo/test only.

- [ ] **Step 4: Implement honest retrieval fallback and trust actions**

Show “Saya kurang yakin” whenever normalized sources are absent or below the defined confidence threshold; do not inject citation tokens. Add copy/regenerate/report affordances with ARIA status feedback; report transport is wired in Task 4.

- [ ] **Step 5: Run focused tests, style, and build**

Run: `npx vitest run`, `php artisan test tests/Feature/SmartEnrollFeatureTest.php`, `php vendor/bin/pint --dirty`, and `npm run build`

Expected: all commands exit 0.

- [ ] **Step 6: Record the stage checkpoint**

Run `git diff --check` for Task 2 paths and commit only the selected, verified paths when safe in the shared dirty workspace.

### Task 3: Hasil kader terstruktur dan input cerdas

**Files:**
- Create: `resources/views/admin/events/partials/sikader/member-cards.blade.php`
- Create: `resources/views/admin/events/partials/sikader/member-table.blade.php`
- Create: `resources/views/admin/events/partials/sikader/upload-summary.blade.php`
- Modify: `app/Services/SmartEnrollService.php`
- Modify: `app/Http/Controllers/Admin/SmartEnrollController.php`
- Modify: `routes/web.php`
- Modify: `resources/js/sikader/adapter.js`
- Modify: `resources/js/sikader/state.js`
- Modify: `tests/Feature/SmartEnrollFeatureTest.php`
- Test: `tests/Unit/SmartEnrollServiceTest.php`

**Interfaces:**
- Produces `POST /admin/events/{event}/smart-enroll/preview` returning `{success, total_rows, unique_rows, duplicate_rows, invalid_nik_rows, rows}` without creating `EventPeserta`.
- Produces normalized components `member_cards`, `member_table`, `upload_summary`.
- Produces client helpers `maskNik(value)`, `parseSlashCommand(input)`, and `buildFollowUpActions(message, quota)`.

- [ ] **Step 1: Write failing tests for preview and presentation data**

Feature-test an admin preview of duplicate input and invalid NIK fields without database mutation; test non-admin denial. Unit-test summary counters/masking. Add view-contract tests for select-all, bulk label, status labels `cocok/mirip/tidak ditemukan`, and paper-plane send semantics.

- [ ] **Step 2: Run focused tests to verify they fail**

Run: `php artisan test tests/Unit/SmartEnrollServiceTest.php tests/Feature/SmartEnrollFeatureTest.php --filter="preview|nik|bulk"`

Expected: FAIL because preview route/summary/component contract is absent.

- [ ] **Step 3: Implement non-mutating preview and result renderers**

Extend the existing parser result with duplicate and NIK-validity metadata without changing matching/enrollment behavior. Add validated preview route/controller method. Render candidate cards/table with masked NIK, score/status, checkbox selection, detail, contextual quick actions, quota progress update, and inline correction path.

- [ ] **Step 4: Implement composer commands and upload flow**

Provide `/tempel`, `/unggah`, and `/rekomendasi` menu navigation. Require an upload summary acknowledgement before calling match; preserve Enter-to-send, Shift+Enter newline, and an unambiguous paper-plane icon/button.

- [ ] **Step 5: Run feature/unit tests and build**

Run: `php artisan test tests/Unit/SmartEnrollServiceTest.php tests/Feature/SmartEnrollFeatureTest.php`, `php vendor/bin/pint --dirty`, and `npm run build`

Expected: all commands exit 0.

- [ ] **Step 6: Record the stage checkpoint**

Run `git diff --check` for Task 3 paths and commit only selected verified paths if no unowned modifications are included.

### Task 4: Konfirmasi, undo aman, dan laporan jawaban salah

**Files:**
- Create: `app/Models/SikaderFeedback.php`
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_create_sikader_feedback_table.php`
- Modify: `app/Http/Controllers/Admin/SmartEnrollController.php`
- Modify: `routes/web.php`
- Modify: `resources/js/sikader/state.js`
- Modify: `resources/js/sikader/adapter.js`
- Modify: `resources/views/admin/events/partials/sikader/message.blade.php`
- Modify: `tests/Feature/SmartEnrollFeatureTest.php`

**Interfaces:**
- `execute()` returns `undo_token`, `undo_expires_at`, `quota`, and `registered_count` in addition to its existing success message.
- Produces `POST /admin/events/{event}/smart-enroll/undo` accepting `undo_token`, which only deactivates records created by that execute request before expiry.
- Produces `POST /admin/events/{event}/smart-enroll/feedback` accepting a masked `query`, normalized `sources`, and optional `reason`.

- [ ] **Step 1: Write failing security and behavior tests**

Feature-test explicit confirmation payload handling, undo within 10 seconds, expired/replayed token rejection, preservation of pre-existing participants, quota return values, feedback persistence with masked NIK, and non-admin denial.

- [ ] **Step 2: Run focused tests to verify they fail**

Run: `php artisan test tests/Feature/SmartEnrollFeatureTest.php --filter="undo|feedback|confirmation"`

Expected: FAIL because the safe-action API does not yet exist.

- [ ] **Step 3: Implement mutation records and undo**

Have `execute()` collect IDs it actually creates, issue an opaque server-stored token with event/admin binding and 10-second TTL, then return it with fresh quota counts. `undo()` must use a transaction, consume the token once, and affect only recorded rows. Keep original quota validation intact.

- [ ] **Step 4: Implement feedback persistence and UI confirmation**

Create the minimal feedback table/model. Sanitize/mask source snippets and query before persistence. Render a non-auto-submitting confirmation component; show undo countdown/toast after success and send report action through the new endpoint.

- [ ] **Step 5: Run focused tests, migration, format, and build**

Run: `php artisan test tests/Feature/SmartEnrollFeatureTest.php`, `php artisan migrate --pretend`, `php vendor/bin/pint --dirty`, and `npm run build`

Expected: all commands exit 0.

- [ ] **Step 6: Record the stage checkpoint**

Run `git diff --check` for Task 4 paths and commit only selected verified paths if safe.

### Task 5: Responsiveness, accessibility, and acceptance scenarios

**Files:**
- Modify: `tests/Feature/SmartEnrollFeatureTest.php`
- Modify: `docs/agentic/MEMORY.md`
- Modify: relevant Task 1–4 files only if a verified defect is found

**Interfaces:**
- Consumes the UI/API contracts produced by Tasks 1–4.
- Produces an updated durable project memory entry for SiKader’s message/undo/privacy contracts.

- [ ] **Step 1: Add failing regression checks for the three acceptance scenarios**

Add focused test coverage for: paste→match→confirmation payload; CSV preview containing duplicate/invalid values; and RAG response lacking sources producing the honest fallback. Assert key 380/480 responsive class contracts and keyboard/ARIA labels in the rendered page.

- [ ] **Step 2: Run these tests to verify the gaps**

Run: `php artisan test tests/Feature/SmartEnrollFeatureTest.php --filter="sikader|preview|undo|fallback"`

Expected: any uncovered behavior fails before its minimal correction.

- [ ] **Step 3: Correct verified defects only**

Apply the smallest correction necessary in the task-owning module; do not redesign unrelated admin UI.

- [ ] **Step 4: Perform manual browser acceptance checks**

At 380 px, 480 px, and fullscreen, run: (a) tempel daftar→cocok→konfirmasi→undo; (b) unggah CSV duplikat/NIK invalid→ringkasan→pencocokan; (c) pertanyaan tanpa sumber→banner kurang yakin. Verify tab order, Escape close, visible focus, no outer-page scroll chaining, and reduced-motion behavior.

- [ ] **Step 5: Run the complete verification gate**

Run: `php artisan test`, `php vendor/bin/pint --dirty`, `php artisan view:cache`, `npm run build`, and `git diff --check`.

Expected: all commands exit 0; report any pre-existing failures separately if present.

- [ ] **Step 6: Update memory and record final checkpoint**

Update only permanent architecture decisions in `docs/agentic/MEMORY.md`. Commit only selected verified SiKader files if the dirty workspace allows an isolated index; otherwise provide a precise changed-file list and verification evidence.
