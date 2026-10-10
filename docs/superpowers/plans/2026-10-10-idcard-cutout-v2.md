# ID Card Cutout V2 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Store and prefer high-quality client-side person cutouts while upgrading the local GD fallback and ID Card rendering.

**Architecture:** A nullable cutout path and explicit display mode travel from the existing forms through private storage to a single photo-resolution service used by both PDF controllers. Browser MediaPipe processing is progressive enhancement; PHP GD v2 remains the reliable fallback. Templates receive image source and a boolean indicating original-mode framing.

**Tech Stack:** Laravel 12, PHP 8.2+, GD, Blade, Alpine.js, Vite, DomPDF, `@mediapipe/tasks-vision` (Apache-2.0).

**Spec:** `docs/superpowers/specs/2026-10-10-idcard-cutout-v2-design.md`

## Global Constraints

- Do not change `QrService`, QR payload, signature verification, routes, policies, or access middleware.
- Store source and cutout photos only on private `local` storage.
- Keep the 600x800 GD input limit and original-JPEG fallback.
- No paid/cloud image processing and no AGPL dependency.
- Preserve user-authored changes outside this feature branch.

---

### Task 1: Persist and validate selected photo mode (K4 & K5)

**Files:**
- Create: `database/migrations/2026_10_10_000001_add_idcard_photo_fields_to_anggota_table.php`
- Create: `app/Rules/ValidCutoutPng.php` (replaces basic TransparentPng with K4 validation)
- Modify: `app/Models/Anggota.php`, `app/Http/Requests/AnggotaRegistrationRequest.php`, `app/Http/Controllers/AnggotaRegistrationController.php`, `app/Http/Controllers/Portal/ProfileController.php`, `app/Http/Controllers/Admin/AnggotaController.php`
- Modify: `resources/views/admin/anggota/show.blade.php`, `resources/views/admin/anggota/edit.blade.php`
- Test: `tests/Feature/AnggotaRegistrationTest.php`, `tests/Feature/PortalProfileTest.php`, `tests/Feature/AdminAnggotaCutoutTest.php`

**Interfaces:** Produces `Anggota::$foto_cutout_path`, `Anggota::$foto_mode` (enum: 'cutout', 'original', default: 'cutout'), server re-encodes PNG via GD, validates magic bytes, 5%-90% alpha transparency (alpha >= 120), aspect ratio match with source (+-1px). Admin can view side-by-side thumbnails and change mode or delete cutout.

- [ ] Write failing registration/profile tests for valid cutout PNG, invalid mime, out-of-range transparency (<5% or >90%), aspect ratio mismatch, and GD re-encoding.
- [ ] Run focused tests and confirm expected failures.
- [ ] Implement migration for `foto_cutout_path` and `foto_mode` on `anggota`.
- [ ] Implement `ValidCutoutPng` rule with magic bytes check, transparency ratio 5%-90%, aspect ratio verification against source photo, and GD re-encoding helper.
- [ ] Update `AnggotaRegistrationController`, `ProfileController`, and `AnggotaController` with storage, re-encoding, replacement, and deletion logic.
- [ ] Add side-by-side thumbnail display and mode switch/cutout deletion in admin anggota views.
- [ ] Run focused tests, Pint, full PHPUnit, view:cache, and commit `feat: persist validated id card cutouts`.
- [ ] Push to `origin/feat/idcard-cutout-v2`.

### Task 2a: ImageCutoutService GD v2 Fallback (K6)

**Files:**
- Modify: `app/Services/ImageCutoutService.php`
- Test: `tests/Feature/ImageCutoutServiceV2Test.php`

**Interfaces:** `ImageCutoutService::removeBackground()` upgrades to v2 algorithm: YCbCr with lower luminance weight, neighbour-relative region growing, Sobel edge barriers, largest connected component cleanup, 2px alpha blur, no lower fade, cache key `v2`.

- [ ] Write failing tests for synthetic images: gradient background with shadow -> alpha 127; central subject -> alpha 0; no small isolated foreground islands; cache key v2 separation from v1.
- [ ] Implement GD v2 algorithm in `ImageCutoutService`.
- [ ] Run focused tests and verify GD v2 behavior.
- [ ] Run Pint, full PHPUnit suite, view:cache, and commit `fix: add id card cutout v2 fallback`.
- [ ] Push to `origin/feat/idcard-cutout-v2`.

### Task 2b: Resolve PDF Photo Sources in Controllers (K6)

**Files:**
- Modify: `app/Services/ImageCutoutService.php`, `app/Http/Controllers/Portal/KehadiranController.php`, `app/Http/Controllers/Admin/EventOutputController.php`
- Test: `tests/Feature/IdCardBatchTest.php`, `tests/Feature/PortalEvaluationTest.php`

**Interfaces:** `ImageCutoutService::resolveForAnggota(Anggota $anggota): array{dataUri:?string,mode:string}` returns client cutout, GD v2 cutout, or original source in that order according to `foto_mode`.

- [ ] Write failing tests verifying resolution priority (client cutout -> GD v2 -> original when mode=cutout; directly original when mode=original).
- [ ] Implement `resolveForAnggota` in `ImageCutoutService` and integrate into both `KehadiranController` and `EventOutputController`.
- [ ] Run Pint, full PHPUnit suite, view:cache, and commit `fix: resolve id card photo sources`.
- [ ] Push to `origin/feat/idcard-cutout-v2`.

### Task 3: Progressive Browser Segmentation & Source Choice (K1, K2, K3, K7)

**Files:**
- Modify: `package.json`, `package-lock.json`, `resources/js/app.js`, `resources/views/components/pasfoto-fitter.blade.php`, `resources/views/anggota/register.blade.php`, `resources/views/portal/profile.blade.php`
- Create: `resources/js/idcard-mask.js` (pure functions: mask threshold, largest connected component, 2px feathering, transparency ratio calculation)
- Create: `tests/unit/idcard-mask.test.js` (Vitest unit tests)
- Create: `resources/js/idcard-segmenter.js` (MediaPipe loader and wrapper)
- Asset: `public/models/selfie_segmenter.tflite` or chosen vision model asset
- Test: Vitest test suite (`npx vitest run`)

**Interfaces:** `idcard-mask.js` exports pure helper functions. Fitter triggers segmentation on cropped canvas output (`K3`), updates hidden fields `foto_cutout` and `foto_mode`, provides cutout preview/toggle. Fallback is graceful and non-blocking if WASM/WebGL fails.

- [ ] Install Vitest and write unit tests for `idcard-mask.js`.
- [ ] Implement pure mask helpers in `resources/js/idcard-mask.js` and verify passing `npx vitest run`.
- [ ] Add `@mediapipe/tasks-vision`, download model asset to `public/models/`, and implement `idcard-segmenter.js`.
- [ ] Integrate into `pasfoto-fitter.blade.php` to run segmentation on cropped output, maintaining identical dimensions.
- [ ] Add UI controls for cutout/original toggle with live preview.
- [ ] Run `npx vitest run`, `npm run build`, Pint, full PHPUnit suite, view:cache, and commit `feat: segment id card photos in browser`.
- [ ] Push to `origin/feat/idcard-cutout-v2`.

### Task 4: Render Source Modes Cleanly in Both PDFs & Visual Verification

**Files:**
- Modify: `resources/views/portal/idcard-pdf.blade.php`, `resources/views/admin/events/idcard-batch-pdf.blade.php`, both output controllers
- Test: `tests/Feature/IdCardBatchTest.php`, `tests/Feature/PortalEvaluationTest.php`

**Interfaces:** PDF templates support `photo-img-cutout` and `photo-img-original` (with rounded border/frame). Photo box positioned behind overlay coverage so lower edge is clean. DomPDF DPI set to 180.

- [ ] Update PDF templates for photo mode styling and positioning.
- [ ] Generate single and batch test PDFs with synthetic photos for client cutout, GD v2, and original modes.
- [ ] Convert rendered PDFs to PNGs in an ignored temporary directory and verify visually against all visual criteria.
- [ ] Run Pint, full PHPUnit suite, view:cache, and commit `fix: render id card photo modes cleanly`.
- [ ] Push to `origin/feat/idcard-cutout-v2`.

### Task 5: Regression Verification, Memory Docs, and Final Report

**Files:**
- Modify: `docs/agentic/MEMORY.md`

- [ ] Run all verification commands: `vendor/bin/pint --test`, `php artisan test`, `npm run build`, `npx vitest run`, `php artisan view:cache`.
- [ ] Update `docs/agentic/MEMORY.md` with permanent decisions.
- [ ] Commit `test: verify id card cutout v2 flow`.
- [ ] Push to `origin/feat/idcard-cutout-v2`.
- [ ] Present comprehensive final report.
