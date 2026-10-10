# ID Card Cutout V2 Design

## Goal

Improve ID Card photos by preferring a browser-generated person cutout, retaining a secure server fallback, and removing the template fade that damages clothing detail.

## Data and upload flow

`anggota` receives nullable `foto_cutout_path` and `foto_mode` (`cutout` or `original`, default `cutout`). The existing `foto` remains the original/cropped source and is never replaced by a mask result. Registration and profile forms submit `foto`, optional `foto_cutout`, and `foto_mode`. The server validates the source image and validates that `foto_cutout` is a PNG with alpha pixels before writing both files to the private `local` disk. Replacement and deletion remove both files.

## Browser cutout

Vite loads `@mediapipe/tasks-vision` lazily after a file is selected. The Image Segmenter model and WASM are application assets rather than an upload to a cloud API. The component converts the confidence mask into a thresholded alpha mask, retains the largest connected foreground component, applies a 2px alpha blur, and encodes a PNG. If MediaPipe, WASM, or WebGL is unavailable, the component leaves `foto_cutout` absent and keeps the existing crop/upload flow usable. The user may select either the cutout or original mode and sees the selected result in the existing ID Card composition.

## PDF source selection

For `cutout` mode, use a valid persisted client PNG first. Otherwise run `ImageCutoutService` v2 on `foto`; if it cannot produce a PNG, use `foto` unchanged. For `original` mode, use `foto` unchanged and mark the view data as original so the template applies a rounded frame. An absent source uses the existing MPKSDI default image. QR code payload, signatures, policies, and routes remain unchanged.

## GD fallback v2

The fallback preserves maximum 600x800 resizing and JPEG fallback. Its cache key includes `v2`. It uses YCbCr-style color distance with lower luminance weight, neighbour-relative region growing, and a Sobel edge threshold to follow smooth studio shadows while not crossing strong foreground edges. Mask cleanup retains the largest foreground component and removes small islands. It softens the alpha-mask with a 2px blur rather than assigning one fixed edge alpha. No lower-image fade is applied.

## PDF layout

Both templates remove the 12% lower fade assumption. The photo box is moved lower behind the existing overlay and remains above background but beneath overlay/text. Original-mode photos are clipped inside a rounded frame; transparent cutouts remain unframed. DomPDF is configured to 180 DPI.

## Verification

Feature tests cover upload validation, selection priority, cache versioning, gradient shadow removal, retained subject, and removal of isolated foreground pixels. Existing PDF tests remain. A single and a batch PDF are rendered to PNG for visual review with synthetic photographs only.
