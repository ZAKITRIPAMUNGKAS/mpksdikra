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

### Task 1: Persist and validate selected photo mode

**Files:**
- Create: `database/migrations/2026_10_10_000001_add_idcard_photo_fields_to_anggota_table.php`
- Create: `app/Rules/TransparentPng.php`
- Modify: `app/Models/Anggota.php`, `app/Http/Requests/AnggotaRegistrationRequest.php`, `app/Http/Controllers/AnggotaRegistrationController.php`, `app/Http/Controllers/Portal/ProfileController.php`, `app/Http/Controllers/Admin/AnggotaController.php`
- Test: `tests/Feature/AnggotaRegistrationTest.php`, `tests/Feature/PortalProfileTest.php`

**Interfaces:** Produces `Anggota::$foto_cutout_path`, `Anggota::$foto_mode`, and `TransparentPng` that accepts a real PNG containing at least one alpha value above zero.

- [ ] Write a failing registration test for a transparent PNG accepted with `foto_mode=cutout`, and tests for JPEG/non-alpha PNG rejection.
- [ ] Run the focused tests and confirm the missing fields/rule cause the expected failure.
- [ ] Add the nullable columns and fillable properties; add `TransparentPng`; validate `foto_cutout` as optional PNG up to 2 MB and dimensions 100–2000px; store it privately only for cutout mode.
- [ ] Extend profile replacement and admin deletion to delete the paired cutout path.
- [ ] Run focused tests and commit `feat: persist validated id card cutouts`.

### Task 2: Resolve PDF photo sources and improve GD fallback

**Files:**
- Modify: `app/Services/ImageCutoutService.php`, `app/Http/Controllers/Portal/KehadiranController.php`, `app/Http/Controllers/Admin/EventOutputController.php`
- Test: `tests/Feature/IdCardBatchTest.php`

**Interfaces:** `ImageCutoutService::resolveForAnggota(Anggota $anggota): array{dataUri:?string,mode:string}` returns client cutout, GD v2 cutout, or original source in that order according to mode.

- [ ] Write failing image tests for a gradient/shadow background, no small foreground islands, v2 cache key, client-cutout priority, and original-mode fallback.
- [ ] Run the focused test class; confirm failures reflect absent v2 resolution/mask behavior.
- [ ] Replace fixed-alpha/fade logic with YCbCr local region-growing, Sobel barriers, largest-component cleanup, and 2px alpha blur; retain safety fallback and scaling.
- [ ] Make both controllers consume `resolveForAnggota` without changing authorization or QR calls.
- [ ] Run focused tests and commit `fix: add id card cutout v2 fallback`.

### Task 3: Add progressive browser segmentation and source choice

**Files:**
- Modify: `package.json`, `package-lock.json`, `resources/js/app.js`, `resources/views/components/pasfoto-fitter.blade.php`, `resources/views/anggota/register.blade.php`, `resources/views/portal/profile.blade.php`
- Create: `resources/js/idcard-segmenter.js`
- Create: `public/models/selfie_segmenter.tflite`

**Interfaces:** The fitter maintains hidden form fields `foto_mode` and `foto_cutout`; it never prevents the source photo upload when `segmentPhoto()` rejects.

- [ ] Write a DOM-level test or extract pure mask helpers so largest-component and feathering assertions can fail independently.
- [ ] Run the frontend test/build path and confirm the helper is missing before implementation.
- [ ] Add the pinned Apache-2.0 MediaPipe dependency and lazy module; generate transparent PNG from mask; update hidden file input through `DataTransfer`; expose cutout/original controls and mockup preview.
- [ ] Ensure failure leaves original existing crop intact and shows non-blocking fallback copy.
- [ ] Run `npm run build` and commit `feat: segment id card photos in browser`.

### Task 4: Render source modes cleanly in both PDFs

**Files:**
- Modify: `resources/views/portal/idcard-pdf.blade.php`, `resources/views/admin/events/idcard-batch-pdf.blade.php`, both output controllers
- Test: `tests/Feature/IdCardBatchTest.php`, `tests/Feature/PortalEvaluationTest.php`

**Interfaces:** Templates receive `fotoDataUri`/card `foto` and `fotoMode`; original mode applies `photo-img-original`, cutout mode applies `photo-img-cutout`.

- [ ] Write failing response/PDF tests asserting source mode is passed to each view.
- [ ] Remove lower fade dependency, move the photo box behind overlay coverage, add rounded original photo frame, and set DomPDF DPI to 180.
- [ ] Generate single and batch fixture PDFs, render the first page to PNG, and inspect placement against status/name/NBM.
- [ ] Run focused tests and commit `fix: render id card photo modes cleanly`.

### Task 5: Regression verification and project memory

**Files:**
- Modify: `docs/agentic/MEMORY.md`
- Test: full PHPUnit suite and visual artifacts under ignored temporary directory

- [ ] Run `vendor/bin/pint --test`, `php artisan test`, `npm run build`, and `php artisan view:cache`.
- [ ] Record only permanent photo-source/caching decisions in project memory.
- [ ] Confirm no private photo, token, NIK, or user upload is included in tests or artifacts.
- [ ] Commit `test: verify id card cutout v2 flow`.
