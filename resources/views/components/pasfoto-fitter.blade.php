@props([
    'name' => 'foto',
    'required' => false,
    'currentPhoto' => null,
    'label' => 'Pasfoto Formal (Wajib untuk ID Card BA)',
    'inputId' => 'pasfoto-file-input',
])

<div x-data="pasfotoFitter({
        inputId: '{{ $inputId }}',
        currentPhoto: '{{ $currentPhoto ? addslashes($currentPhoto) : '' }}'
    })"
    @pasfoto-restore.window="if (!$event.detail || !$event.detail.inputId || $event.detail.inputId === inputId) setFromDataUrl($event.detail.dataUrl)"
    @pasfoto-clear.window="if (!$event.detail || !$event.detail.inputId || $event.detail.inputId === inputId) clearPhoto()"
    class="p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200/90 shadow-2xs space-y-3">

    {{-- Label & Status --}}
    <div class="flex items-center justify-between flex-wrap gap-2">
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>

        {{-- Success Auto-Match Badge --}}
        <template x-if="isCropped && !isProcessing">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                <span>Pasfoto 3:4 Setengah Badan Pas</span>
            </span>
        </template>
    </div>

    {{-- Preview & Upload Trigger Row --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
        {{-- 3:4 Box Preview --}}
        <div class="relative w-24 h-32 border-2 rounded-2xl overflow-hidden bg-slate-900 flex items-center justify-center shrink-0 shadow-xs self-center sm:self-auto transition duration-200"
             :class="isProcessing ? 'border-sky-400 ring-4 ring-sky-100' : (isCropped ? 'border-emerald-400 ring-2 ring-emerald-100' : (photoPreview ? 'border-sky-400' : 'border-dashed border-slate-300 bg-white'))">

            {{-- 1. Loading / Auto-Scanning State --}}
            <template x-if="isProcessing">
                <div class="absolute inset-0 bg-slate-950 flex flex-col items-center justify-center p-2 text-center select-none z-30">
                    {{-- Pulse Loader --}}
                    <div class="relative w-9 h-9 mb-1.5">
                        <div class="w-9 h-9 rounded-full border-2 border-slate-700 border-t-[#f8bb00] animate-spin"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-2 h-2 rounded-full bg-sky-400 animate-ping"></div>
                        </div>
                    </div>
                    <span class="text-[9.5px] font-extrabold text-sky-300 block leading-none">Auto Match</span>
                    <span class="text-[8px] text-slate-400 block mt-1 leading-tight">Menyelaraskan 3:4 setengah badan...</span>
                </div>
            </template>

            {{-- 2. Cropped / Loaded Photo Preview --}}
            <template x-if="photoPreview && !isProcessing">
                <img :src="photoPreview" alt="Preview Pasfoto" class="w-full h-full object-cover select-none">
            </template>

            {{-- 3. Empty Placeholder --}}
            <template x-if="!photoPreview && !isProcessing">
                <div class="text-center p-2 text-slate-400">
                    <svg class="w-8 h-8 mx-auto mb-1 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-[10px] font-bold block leading-none">Rasio 3x4</span>
                </div>
            </template>

            {{-- Quick Hover / Tap to adjust if photo exists --}}
            <template x-if="photoPreview && rawImageSrc && !isProcessing">
                <button type="button" @click="openModal()"
                    title="Sesuaikan Ulang Posisi Wajah"
                    class="absolute inset-0 bg-slate-950/40 hover:bg-slate-950/60 text-white flex flex-col items-center justify-center opacity-0 hover:opacity-100 transition duration-150 backdrop-blur-[1px]">
                    <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    <span class="text-[8.5px] font-bold uppercase tracking-wider">Atur Posisi</span>
                </button>
            </template>
        </div>

        {{-- Controls and Input --}}
        <div class="flex-1 w-full space-y-2">
            <div class="flex flex-wrap items-center gap-2">
                <input type="file"
                    id="{{ $inputId }}"
                    name="{{ $name }}"
                    accept="image/jpeg,image/png,image/webp,image/jpg"
                    @if($required && !$currentPhoto) required @endif
                    @change="handleFileSelect($event)"
                    class="block w-full sm:w-auto text-xs text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-800 hover:file:bg-sky-100 cursor-pointer transition">

                {{-- Action options once photo is ready --}}
                <template x-if="rawImageSrc && !isProcessing">
                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="button" @click="openModal()"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-sky-200 bg-sky-50/80 hover:bg-sky-100 text-sky-800 text-xs font-bold transition shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                            </svg>
                            <span>Atur Manual (Opsional)</span>
                        </button>

                        <button type="button" @click="useOriginal()"
                            class="text-xs text-slate-500 hover:text-slate-700 font-medium underline px-1 py-1">
                            Foto Asli
                        </button>
                    </div>
                </template>
            </div>

            {{-- Dynamic guidance or processing status --}}
            <template x-if="isProcessing">
                <div class="flex items-center gap-2 text-xs font-bold text-sky-700 bg-sky-50 px-3 py-1.5 rounded-xl border border-sky-200/80">
                    <svg class="w-4 h-4 animate-spin text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Sedang memproses &amp; menyelaraskan pasfoto setengah badan (3:4)...</span>
                </div>
            </template>

            <template x-if="!isProcessing">
                <p class="text-[11px] text-slate-400 leading-relaxed font-medium">
                    Sistem otomatis memindai dan menyesuaikan foto Anda menjadi pasfoto setengah badan standar 3:4 resmi (posisi kepala &amp; dada seimbang) untuk ID Card &amp; Sertifikat Baitul Arqam.
                </p>
            </template>
        </div>
    </div>

    {{-- MODAL ATUR MANUAL (HANYA MUNCUL JIKA USER KLIK "ATUR MANUAL") --}}
    <div x-show="modalOpen" x-cloak
         class="fixed inset-0 z-[200] bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-3"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div role="dialog" aria-modal="true" aria-labelledby="pasfoto-title"
             class="bg-white rounded-3xl border border-slate-200 shadow-2xl w-full max-w-xs sm:max-w-sm max-h-[92vh] flex flex-col overflow-hidden relative"
             @click.outside="closeModal()" @keydown.escape.window="closeModal()"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="scale-95 opacity-0"
             x-transition:enter-end="scale-100 opacity-100">

            {{-- Modal Header --}}
            <div class="px-4 py-3 border-b border-slate-100 bg-[#f8fafc] flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 id="pasfoto-title" class="text-xs sm:text-sm font-extrabold text-slate-900 leading-tight">Sesuaikan Posisi Pasfoto</h3>
                        <p class="text-[10px] text-slate-500 font-medium">Garis panduan rasio resmi 3:4</p>
                    </div>
                </div>
                <button type="button" @click="closeModal()"
                    class="w-7 h-7 rounded-full bg-slate-200/70 hover:bg-slate-300 text-slate-600 flex items-center justify-center transition shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Modal Body: Interactive Viewport with Face Overlay --}}
            <div class="p-3 flex flex-col items-center justify-center bg-slate-900 select-none overflow-y-auto flex-1 min-h-0">
                {{-- 3:4 Viewport Canvas (195px x 260px) --}}
                <div class="relative overflow-hidden rounded-2xl shadow-2xl border-2 border-slate-700 bg-slate-950 cursor-grab active:cursor-grabbing touch-none shrink-0"
                     style="width: 195px; height: 260px;"
                     @pointerdown="startDrag($event)"
                     @pointermove="onDrag($event)"
                     @pointerup="endDrag($event)"
                     @pointercancel="endDrag($event)">

                    {{-- Scalable / Movable User Image --}}
                    <template x-if="rawImageSrc">
                        <img :src="rawImageSrc"
                             alt="Edit Pasfoto"
                             :style="`
                                 position: absolute;
                                 left: ${centerX}px;
                                 top: ${centerY}px;
                                 width: ${imgWidth}px;
                                 height: ${imgHeight}px;
                                 transform: translate(-50%, -50%) rotate(${rotation}deg) scale(${scale});
                                 transform-origin: center center;
                                 max-width: none;
                                 user-select: none;
                                 pointer-events: none;
                             `">
                    </template>

                    {{-- Face Silhouette Alignment Guide Overlay (SVG 195x260) --}}
                    <svg class="absolute inset-0 w-full h-full pointer-events-none" viewBox="0 0 195 260">
                        {{-- Border guideline --}}
                        <rect x="1" y="1" width="193" height="258" rx="12" fill="none" stroke="#f8bb00" stroke-width="1.8" stroke-dasharray="5,4" opacity="0.85" />

                        {{-- Head Oval (Kepala & Peci) --}}
                        <ellipse cx="97.5" cy="102" rx="46" ry="62" fill="rgba(1, 176, 245, 0.06)" stroke="#01b0f5" stroke-width="1.8" stroke-dasharray="5,4" opacity="0.9" />

                        {{-- Eye Line Guide (Tinggi Mata) --}}
                        <line x1="60" y1="98" x2="135" y2="98" stroke="#f8bb00" stroke-width="1.5" stroke-dasharray="3,3" opacity="0.95" />
                        <rect x="67" y="89" width="61" height="11" rx="3" fill="rgba(0,0,0,0.65)" />
                        <text x="97.5" y="97.5" text-anchor="middle" font-size="7" fill="#f8bb00" font-weight="bold" letter-spacing="0.3">TINGGI MATA</text>

                        {{-- Top of Head Guideline --}}
                        <line x1="74" y1="40" x2="121" y2="40" stroke="#01b0f5" stroke-width="1.4" stroke-dasharray="3,3" opacity="0.85" />
                        <rect x="54" y="31" width="87" height="11" rx="3" fill="rgba(0,0,0,0.65)" />
                        <text x="97.5" y="39.5" text-anchor="middle" font-size="6.5" fill="#01b0f5" font-weight="bold">PUNCAK KEPALA</text>

                        {{-- Shoulder Guideline Curve --}}
                        <path d="M 14 260 C 32 205, 60 188, 97.5 188 C 135 188, 163 205, 181 260"
                              fill="none" stroke="#f8bb00" stroke-width="1.8" stroke-dasharray="5,4" opacity="0.9" />
                        <rect x="70" y="194" width="55" height="11" rx="3" fill="rgba(0,0,0,0.65)" />
                        <text x="97.5" y="202.5" text-anchor="middle" font-size="7" fill="#f8bb00" font-weight="bold" letter-spacing="0.3">GARIS BAHU</text>

                        {{-- Center crosshair --}}
                        <circle cx="97.5" cy="130" r="2" fill="#ffffff" opacity="0.5" />
                    </svg>

                    {{-- Floating Drag Tip --}}
                    <div class="absolute bottom-1.5 inset-x-2 text-center pointer-events-none">
                        <span class="inline-block px-2 py-0.5 rounded-full bg-slate-950/85 text-[9px] font-bold text-slate-200 border border-slate-700/80">
                            <span aria-hidden="true">👆</span> Geser foto untuk memposisikan
                        </span>
                    </div>
                </div>

                {{-- Toolbar Controls: Zoom, Rotate, Reset --}}
                <div class="w-full max-w-[240px] mt-2.5 space-y-2 shrink-0">
                    {{-- Zoom Slider + Buttons --}}
                    <div class="flex items-center gap-2 bg-slate-800/90 rounded-xl px-2 py-1 border border-slate-700">
                        <button type="button" @click="zoomOut()" title="Perkecil Ukuran"
                            class="w-6 h-6 rounded-lg bg-slate-700 hover:bg-slate-600 text-white font-black text-xs flex items-center justify-center transition shrink-0 active:scale-95">
                            &minus;
                        </button>
                        <div class="flex-1 px-1">
                            <input type="range"
                                :min="minScale"
                                :max="maxScale"
                                step="0.02"
                                x-model.number="scale"
                                class="w-full h-1 bg-slate-600 rounded-lg appearance-none cursor-pointer accent-[#f8bb00]">
                        </div>
                        <button type="button" @click="zoomIn()" title="Perbesar Ukuran"
                            class="w-6 h-6 rounded-lg bg-slate-700 hover:bg-slate-600 text-white font-black text-xs flex items-center justify-center transition shrink-0 active:scale-95">
                            &#43;
                        </button>
                    </div>

                    {{-- Rotate & Reset Buttons --}}
                    <div class="flex items-center justify-center gap-2">
                        <button type="button" @click="rotate()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-bold border border-slate-700 transition active:scale-95">
                            <svg class="w-3 h-3 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Putar 90&deg;</span>
                        </button>

                        <button type="button" @click="resetTransform()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-bold border border-slate-700 transition active:scale-95">
                            <svg class="w-3 h-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Reset</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Modal Footer: Save & Cancel Buttons --}}
            <div class="px-4 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-2 shrink-0">
                <button type="button" @click="useOriginal()"
                    class="px-3 py-2 rounded-xl border border-slate-300 hover:bg-white text-slate-600 text-[11px] font-bold transition text-center">
                    Foto Asli
                </button>

                <button type="button" @click="applyCrop()"
                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-extrabold text-xs shadow-xs transition duration-150 flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Terapkan (3:4)</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Global Pasfoto Fitter Alpine Handler --}}
@once
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('pasfotoFitter', (config) => ({
        inputId: config.inputId,
        modalOpen: false,
        rawImageSrc: null,
        photoPreview: config.currentPhoto || null,
        isCropped: !!config.currentPhoto,
        isProcessing: false,

        // Image & Dimensions
        img: null,
        imgWidth: 0,
        imgHeight: 0,

        // Viewport 3:4 aspect ratio for manual modal (195x260, output 600x800)
        viewportW: 195,
        viewportH: 260,
        outputW: 600,
        outputH: 800,

        // Position & Transformation State
        centerX: 97.5,
        centerY: 130,
        scale: 1,
        minScale: 0.3,
        maxScale: 3.5,
        rotation: 0,

        // Pointer Drag Tracking
        isDragging: false,
        startX: 0,
        startY: 0,
        initialCenterX: 97.5,
        initialCenterY: 130,

        init() {
            if (config.currentPhoto) {
                this.photoPreview = config.currentPhoto;
                this.isCropped = true;
            }
        },

        // Triggered upon selecting a file
        handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Start auto-processing loading indicator
            this.isProcessing = true;

            const reader = new FileReader();
            reader.onload = (e) => {
                this.rawImageSrc = e.target.result;
                const image = new Image();
                image.onload = async () => {
                    this.img = image;
                    this.imgWidth = image.naturalWidth || image.width;
                    this.imgHeight = image.naturalHeight || image.height;
                    this.resetTransform();

                    // Deliberate brief loading time (650ms) for smooth visual feedback
                    setTimeout(async () => {
                        await this.executeAutoMatch(image);
                        this.isProcessing = false;
                    }, 650);
                };
                image.src = this.rawImageSrc;
            };
            reader.readAsDataURL(file);
        },

        // Robust Multi-Scale Face & Head Localizer (Skin-Cluster, Eye Contrast & Silhouette Analysis)
        detectFaceAndHead(image) {
            const origW = image.naturalWidth || image.width;
            const origH = image.naturalHeight || image.height;

            // Downsample to max 160px for instant analysis (< 8ms)
            const maxDim = 160;
            const scale = Math.min(1, maxDim / Math.max(origW, origH));
            const sw = Math.max(20, Math.round(origW * scale));
            const sh = Math.max(20, Math.round(origH * scale));

            const canvas = document.createElement('canvas');
            canvas.width = sw;
            canvas.height = sh;
            const ctx = canvas.getContext('2d', { willReadFrequently: true });
            ctx.drawImage(image, 0, 0, sw, sh);

            let imgData;
            try {
                imgData = ctx.getImageData(0, 0, sw, sh);
            } catch (err) {
                return null;
            }

            const data = imgData.data;

            // Helper to get pixel RGB and luminance at (x, y)
            const getPixel = (x, y) => {
                if (x < 0 || x >= sw || y < 0 || y >= sh) return null;
                const idx = (y * sw + x) * 4;
                const r = data[idx];
                const g = data[idx + 1];
                const b = data[idx + 2];
                const lum = 0.299 * r + 0.587 * g + 0.114 * b;
                return { r, g, b, lum };
            };

            // Strict skin test that firmly rejects wood, bamboo, and foliage
            const isSkinPixel = (r, g, b) => {
                // Human skin characteristics (melanin + hemoglobin)
                if (r <= g || g <= b) return false;
                if ((r - g) < 12 || (r - b) < 24) return false;
                if (r < 65 || r > 245 || b < 20 || b > 190) return false;

                // Normalized RGB
                const sum = r + g + b;
                const rn = r / sum;
                const gn = g / sum;
                const bn = b / sum;
                if (rn < 0.36 || rn > 0.58 || gn < 0.27 || gn > 0.38 || bn > 0.28) return false;

                // YCbCr skin range
                const cb = 128 - 0.168736 * r - 0.331264 * g + 0.5 * b;
                const cr = 128 + 0.5 * r - 0.418688 * g - 0.081312 * b;
                return (cb >= 80 && cb <= 126 && cr >= 134 && cr <= 172);
            };

            // Pre-compute skin map for fast scanning
            const skinMap = new Uint8Array(sw * sh);
            for (let y = 0; y < sh; y++) {
                for (let x = 0; x < sw; x++) {
                    const p = getPixel(x, y);
                    if (p && isSkinPixel(p.r, p.g, p.b)) {
                        skinMap[y * sw + x] = 1;
                    }
                }
            }

            // Candidate Face Window Search (Multi-scale template scan)
            // Realistic face width on portrait photos: 10% to 32% of width
            const candidateWidths = [
                Math.round(sw * 0.12),
                Math.round(sw * 0.16),
                Math.round(sw * 0.20),
                Math.round(sw * 0.25),
                Math.round(sw * 0.30)
            ];

            let bestBox = null;
            let bestScore = -1;

            const stepX = Math.max(2, Math.round(sw * 0.035));
            const stepY = Math.max(2, Math.round(sh * 0.035));

            const minY = Math.round(sh * 0.06);
            const maxY = Math.round(sh * 0.52); // Heads are in upper half

            for (const bw of candidateWidths) {
                const bh = Math.round(bw * 1.30); // Face oval aspect ratio
                const rx = bw * 0.38;
                const ry = bh * 0.44;

                for (let by = minY; by + bh <= maxY + Math.round(sh * 0.15); by += stepY) {
                    for (let bx = Math.round(sw * 0.08); bx + bw <= Math.round(sw * 0.92); bx += stepX) {
                        const cx = bx + (bw / 2);
                        const cy = by + (bh / 2);

                        // 1. Skin density inside face oval
                        let totalOval = 0;
                        let skinInOval = 0;
                        let eyeLumSum = 0, eyeCount = 0;
                        let cheekLumSum = 0, cheekCount = 0;

                        const sampleStep = 2;
                        for (let dy = -ry; dy <= ry; dy += sampleStep) {
                            for (let dx = -rx; dx <= rx; dx += sampleStep) {
                                if ((dx * dx) / (rx * rx) + (dy * dy) / (ry * ry) <= 1.0) {
                                    totalOval++;
                                    const px = Math.round(cx + dx);
                                    const py = Math.round(cy + dy);
                                    if (px >= 0 && px < sw && py >= 0 && py < sh) {
                                        if (skinMap[py * sw + px] === 1) {
                                            skinInOval++;
                                        }

                                        const p = getPixel(px, py);
                                        if (p) {
                                            // Eye band (-0.35 to 0.0 of cy)
                                            if (dy >= -ry * 0.40 && dy <= -ry * 0.05) {
                                                eyeLumSum += p.lum;
                                                eyeCount++;
                                            }
                                            // Cheek band (0.05 to 0.45 of cy)
                                            else if (dy >= ry * 0.10 && dy <= ry * 0.55) {
                                                cheekLumSum += p.lum;
                                                cheekCount++;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if (totalOval === 0) continue;
                        const skinDensity = skinInOval / totalOval;
                        if (skinDensity < 0.30) continue; // Not a face oval

                        // 2. Eye/Feature contrast score
                        let eyeContrast = 0;
                        if (eyeCount > 0 && cheekCount > 0) {
                            const avgEye = eyeLumSum / eyeCount;
                            const avgCheek = cheekLumSum / cheekCount;
                            // Eyes/eyebrows are darker than cheeks
                            if (avgEye < avgCheek) {
                                eyeContrast = Math.min(25, avgCheek - avgEye);
                            }
                        }

                        // 3. Border contrast (Contrast between face skin and surrounding jilbab/hair/clothes)
                        let borderDiff = 0;
                        let borderSamples = 0;
                        const outerRx = bw * 0.58;
                        const outerRy = bh * 0.58;
                        for (let angle = 0; angle < Math.PI * 2; angle += 0.4) {
                            const ox = Math.round(cx + outerRx * Math.cos(angle));
                            const oy = Math.round(cy + outerRy * Math.sin(angle));
                            const pOut = getPixel(ox, oy);
                            if (pOut) {
                                borderSamples++;
                                // Border is distinct from face skin (e.g. dark green jilbab or dark hair)
                                if (!isSkinPixel(pOut.r, pOut.g, pOut.b)) {
                                    borderDiff += 1;
                                }
                            }
                        }
                        const borderScore = borderSamples > 0 ? (borderDiff / borderSamples) : 0;

                        // 4. Centrality weighting (prefer centered person over edges)
                        const distFromCenter = Math.abs(cx - (sw / 2)) / (sw / 2);
                        const centerWeight = Math.max(0.4, 1.0 - (distFromCenter * 0.65));

                        // 5. Composite candidate score
                        const score = (skinDensity * 3.5) + (borderScore * 2.0) + (eyeContrast * 0.12) + (centerWeight * 1.8);

                        if (score > bestScore) {
                            bestScore = score;
                            bestBox = { cx, cy, bw, bh };
                        }
                    }
                }
            }

            if (bestBox && bestScore >= 2.5) {
                return {
                    found: true,
                    centerX: bestBox.cx / scale,
                    centerY: bestBox.cy / scale,
                    width: bestBox.bw / scale,
                    height: bestBox.bh / scale
                };
            }

            return null;
        },

        // Intelligent Auto-Matcher Algorithm with Half-Body (Setengah Badan) 3:4 Framing
        async executeAutoMatch(image) {
            const origW = image.naturalWidth || image.width;
            const origH = image.naturalHeight || image.height;
            const targetRatio = 3 / 4; // 0.75

            let faceCenterX = origW / 2;
            let faceCenterY = origH * 0.35;
            let faceWidth = origW * 0.20;
            let faceHeight = origH * 0.22;
            let faceDetected = false;

            // 1. Try Native Browser FaceDetector (Chromium / Chrome Android if available)
            if ('FaceDetector' in window) {
                try {
                    const detector = new window.FaceDetector({ maxDetectedFaces: 1, fastMode: true });
                    const faces = await detector.detect(image);
                    if (faces && faces.length > 0) {
                        const box = faces[0].boundingBox;
                        faceCenterX = box.x + (box.width / 2);
                        faceCenterY = box.y + (box.height / 2);
                        faceWidth = box.width;
                        faceHeight = box.height;
                        faceDetected = true;
                    }
                } catch (err) {}
            }

            // 2. Fallback: Run Robust Multi-Scale Face & Silhouette Analysis
            if (!faceDetected) {
                const detected = this.detectFaceAndHead(image);
                if (detected && detected.found) {
                    faceCenterX = detected.centerX;
                    faceCenterY = detected.centerY;
                    faceWidth = detected.width;
                    faceHeight = detected.height;
                    faceDetected = true;
                }
            }

            // 3. Compute 3:4 Crop Dimensions Specifically for "SETENGAH BADAN" (Half-Body Bust Portrait)
            let cropH, cropW;

            if (faceDetected) {
                // In standard formal pasfoto SETENGAH BADAN:
                // - Head (puncak kepala/jilbab sampai dagu) occupies ~38% - 42% of frame height.
                // - Dagu to dada/perut atas occupies ~50% of the lower frame.
                // - Headroom above head occupies ~10%.
                // - Therefore ideal crop height = faceHeight * 3.7 (or faceWidth * 4.4).
                const desiredH = Math.max(faceHeight * 3.7, (faceWidth * 3.3) / targetRatio);

                // Ensure it does NOT keep full-body (never exceed 60% of original height if photo was taken from afar)
                // Minimum zoom: 28% of height (prevents extreme macro)
                const maxAllowedH = Math.min(origH, Math.max(origH * 0.58, desiredH));
                cropH = Math.min(maxAllowedH, Math.max(origH * 0.28, desiredH));
                cropW = cropH * targetRatio;

                if (cropW > origW) {
                    cropW = origW;
                    cropH = cropW / targetRatio;
                }
            } else {
                // Default Smart Zoom for Setengah Badan when face is not clearly isolated:
                // Frame the upper 48% of the image (bust/half body) centered
                cropH = Math.min(origH, origH * 0.48);
                cropW = cropH * targetRatio;
                if (cropW > origW) {
                    cropW = origW;
                    cropH = cropW / targetRatio;
                }
                faceCenterX = origW / 2;
                faceCenterY = origH * 0.32;
            }

            // 4. Center crop on face/body horizontally; vertically position eyes/face at ~36% from top
            let cropX = faceCenterX - (cropW / 2);
            let cropY = faceCenterY - (cropH * 0.36);

            // Clamp coordinates strictly within image bounds
            cropX = Math.max(0, Math.min(origW - cropW, cropX));
            cropY = Math.max(0, Math.min(origH - cropH, cropY));

            // 5. Sync manual modal transformation so if user opens "Atur Manual",
            // it starts from this zoomed-in, aligned position!
            this.syncManualTransform(origW, origH, cropX, cropY, cropW, cropH);

            // 6. Render to high-definition 600 x 800 Canvas
            const canvas = document.createElement('canvas');
            canvas.width = this.outputW;
            canvas.height = this.outputH;
            const ctx = canvas.getContext('2d');

            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, this.outputW, this.outputH);

            ctx.drawImage(
                image,
                cropX, cropY, cropW, cropH,
                0, 0, this.outputW, this.outputH
            );

            this.commitCanvasToFile(canvas);
        },

        // Synchronize manual modal transforms to match the auto-matched crop
        syncManualTransform(origW, origH, cropX, cropY, cropW, cropH) {
            this.rotation = 0;
            const zoomRatio = this.viewportW / cropW;
            this.scale = Number(zoomRatio.toFixed(3));

            const cropCenterX = cropX + (cropW / 2);
            const cropCenterY = cropY + (cropH / 2);

            this.centerX = (this.viewportW / 2) + ((origW / 2) - cropCenterX) * zoomRatio;
            this.centerY = (this.viewportH / 2) + ((origH / 2) - cropCenterY) * zoomRatio;

            const coverScale = Math.max(this.viewportW / origW, this.viewportH / origH);
            this.minScale = Number((coverScale * 0.6).toFixed(3));
            this.maxScale = Number((this.scale * 3.0).toFixed(3));
        },

        // Commit Canvas output to File Input and Preview
        commitCanvasToFile(canvas) {
            const croppedDataUrl = canvas.toDataURL('image/jpeg', 0.92);
            this.photoPreview = croppedDataUrl;
            this.isCropped = true;
            this.modalOpen = false;

            canvas.toBlob((blob) => {
                if (!blob) return;
                const fileInput = document.getElementById(this.inputId);
                if (fileInput) {
                    try {
                        const croppedFile = new File([blob], 'pasfoto-3x4.jpg', {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        });
                        const dt = new DataTransfer();
                        dt.items.add(croppedFile);
                        fileInput.files = dt.files;
                    } catch (err) {
                        console.warn('DataTransfer unsupported or restricted; preserving original file.', err);
                    }
                    fileInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }, 'image/jpeg', 0.92);
        },

        openModal() {
            if (this.rawImageSrc) {
                this.modalOpen = true;
            } else {
                document.getElementById(this.inputId)?.click();
            }
        },

        closeModal() {
            this.modalOpen = false;
        },

        resetTransform() {
            if (!this.imgWidth || !this.imgHeight) return;

            this.rotation = 0;
            this.centerX = this.viewportW / 2;
            this.centerY = this.viewportH / 2;

            const scaleW = this.viewportW / this.imgWidth;
            const scaleH = this.viewportH / this.imgHeight;
            const coverScale = Math.max(scaleW, scaleH);

            this.minScale = Number((coverScale * 0.6).toFixed(3));
            this.maxScale = Number((coverScale * 4.0).toFixed(3));
            this.scale = Number((coverScale * 1.05).toFixed(3));
        },

        rotate() {
            this.rotation = (this.rotation + 90) % 360;
        },

        zoomIn() {
            this.scale = Number(Math.min(this.scale * 1.15, this.maxScale).toFixed(3));
        },

        zoomOut() {
            this.scale = Number(Math.max(this.scale * 0.85, this.minScale).toFixed(3));
        },

        startDrag(e) {
            this.isDragging = true;
            this.startX = e.clientX;
            this.startY = e.clientY;
            this.initialCenterX = this.centerX;
            this.initialCenterY = this.centerY;
            try {
                e.target.setPointerCapture(e.pointerId);
            } catch (err) {}
        },

        onDrag(e) {
            if (!this.isDragging) return;
            const dx = e.clientX - this.startX;
            const dy = e.clientY - this.startY;
            this.centerX = this.initialCenterX + dx;
            this.centerY = this.initialCenterY + dy;
        },

        endDrag(e) {
            this.isDragging = false;
            try {
                e.target.releasePointerCapture(e.pointerId);
            } catch (err) {}
        },

        // Manual apply crop from interactive modal
        applyCrop() {
            if (!this.img) return;

            const canvas = document.createElement('canvas');
            canvas.width = this.outputW;
            canvas.height = this.outputH;
            const ctx = canvas.getContext('2d');

            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, this.outputW, this.outputH);

            const ratio = this.outputW / this.viewportW; // 600 / 195

            ctx.save();
            ctx.scale(ratio, ratio);
            ctx.translate(this.centerX, this.centerY);
            ctx.rotate((this.rotation * Math.PI) / 180);
            ctx.scale(this.scale, this.scale);
            ctx.drawImage(
                this.img,
                -this.imgWidth / 2,
                -this.imgHeight / 2,
                this.imgWidth,
                this.imgHeight
            );
            ctx.restore();

            this.commitCanvasToFile(canvas);
        },

        useOriginal() {
            this.photoPreview = this.rawImageSrc;
            this.isCropped = false;
            this.modalOpen = false;
        },

        setFromDataUrl(dataUrl) {
            if (!dataUrl) return;
            this.photoPreview = dataUrl;
            this.rawImageSrc = dataUrl;
            this.isCropped = true;

            try {
                const arr = dataUrl.split(',');
                const mime = arr[0].match(/:(.*?);/)[1] || 'image/jpeg';
                const bstr = atob(arr[1]);
                let n = bstr.length;
                const u8arr = new Uint8Array(n);
                while (n--) {
                    u8arr[n] = bstr.charCodeAt(n);
                }
                const restoredFile = new File([u8arr], 'pasfoto-3x4.jpg', {
                    type: mime,
                    lastModified: Date.now()
                });

                const fileInput = document.getElementById(this.inputId);
                if (fileInput && window.DataTransfer) {
                    const dt = new DataTransfer();
                    dt.items.add(restoredFile);
                    fileInput.files = dt.files;
                    fileInput.dispatchEvent(new Event('input', { bubbles: true }));
                    fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            } catch (err) {
                console.warn('DataTransfer or File reconstruction from dataUrl failed:', err);
            }
        },

        clearPhoto() {
            this.photoPreview = null;
            this.rawImageSrc = null;
            this.isCropped = false;
            const fileInput = document.getElementById(this.inputId);
            if (fileInput) {
                fileInput.value = '';
                fileInput.dispatchEvent(new Event('input', { bubbles: true }));
                fileInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    }));
});
</script>
@endonce
