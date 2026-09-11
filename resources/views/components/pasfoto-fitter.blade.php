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
    class="p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200/90 shadow-2xs space-y-3">

    {{-- Label & Status --}}
    <div class="flex items-center justify-between flex-wrap gap-2">
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
        <template x-if="isCropped">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                <span>Wajah Pas &amp; Proporsional (3:4)</span>
            </span>
        </template>
    </div>

    {{-- Preview & Upload Trigger Row --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
        {{-- 3:4 Box Preview --}}
        <div class="relative w-24 h-32 border-2 rounded-2xl overflow-hidden bg-white flex items-center justify-center shrink-0 shadow-xs self-center sm:self-auto transition duration-200"
             :class="isCropped ? 'border-emerald-400 ring-2 ring-emerald-100' : (photoPreview ? 'border-sky-400 ring-2 ring-sky-100' : 'border-dashed border-slate-300')">
            <template x-if="photoPreview">
                <img :src="photoPreview" alt="Preview Pasfoto" class="w-full h-full object-cover select-none">
            </template>
            <template x-if="!photoPreview">
                <div class="text-center p-2 text-slate-400">
                    <svg class="w-8 h-8 mx-auto mb-1 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-[10px] font-bold block leading-none">Rasio 3x4</span>
                </div>
            </template>

            {{-- Re-edit quick overlay button if photo exists --}}
            <template x-if="photoPreview && rawImageSrc">
                <button type="button" @click="openModal()"
                    title="Sesuaikan Ulang Posisi Wajah"
                    class="absolute inset-0 bg-slate-950/40 hover:bg-slate-950/60 text-white flex flex-col items-center justify-center opacity-0 hover:opacity-100 transition duration-150 backdrop-blur-[1px]">
                    <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    <span class="text-[9px] font-bold uppercase tracking-wider">Atur Posisi</span>
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

                {{-- Button to manually open crop modal if image is loaded --}}
                <template x-if="rawImageSrc">
                    <button type="button" @click="openModal()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-sky-200 bg-sky-50/80 hover:bg-sky-100 text-sky-800 text-xs font-bold transition shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                        </svg>
                        <span>Sesuaikan Posisi Wajah</span>
                    </button>
                </template>
            </div>

            <p class="text-[11px] text-slate-400 leading-relaxed font-medium">
                Maksimal 2 MB. Format JPG, PNG, atau WEBP. Sistem menyediakan panduan otomatis agar posisi kepala, mata, dan bahu proporsional untuk ID Card resmi.
            </p>
        </div>
    </div>

    {{-- MODAL PENGATUR POSISI WAJAH & CROPPER 3:4 --}}
    <div x-show="modalOpen" x-cloak
         class="fixed inset-0 z-[200] overflow-y-auto bg-slate-950/85 backdrop-blur-sm p-3 sm:p-4 flex flex-col justify-start sm:justify-center items-center"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl w-full max-w-sm sm:max-w-md my-auto flex flex-col overflow-hidden relative"
             @click.outside="closeModal()"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="scale-95 opacity-0"
             x-transition:enter-end="scale-100 opacity-100">

            {{-- Modal Header --}}
            <div class="px-4 py-3 sm:px-5 sm:py-3.5 border-b border-slate-100 bg-[#f8fafc] flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2 sm:gap-2.5">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold shrink-0">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xs sm:text-sm font-extrabold text-slate-900 leading-tight">Sesuaikan Posisi Pasfoto (3x4)</h3>
                        <p class="text-[10px] sm:text-[11px] text-slate-500 font-medium leading-none mt-0.5">Posisikan wajah di garis panduan resmi</p>
                    </div>
                </div>
                <button type="button" @click="closeModal()"
                    class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-slate-200/70 hover:bg-slate-300 text-slate-600 flex items-center justify-center transition shrink-0">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Modal Body: Interactive Viewport with Face Overlay --}}
            <div class="p-3 sm:p-5 flex flex-col items-center bg-slate-900 select-none overflow-y-auto">
                {{-- 3:4 Viewport Canvas (210px x 280px) --}}
                <div class="relative overflow-hidden rounded-2xl shadow-2xl border-2 border-slate-700 bg-slate-950 cursor-grab active:cursor-grabbing touch-none shrink-0"
                     style="width: 210px; height: 280px;"
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

                    {{-- Face Silhouette Alignment Guide Overlay (SVG 210x280) --}}
                    <svg class="absolute inset-0 w-full h-full pointer-events-none" viewBox="0 0 210 280">
                        {{-- Border guideline --}}
                        <rect x="1" y="1" width="208" height="278" rx="14" fill="none" stroke="#f8bb00" stroke-width="2" stroke-dasharray="6,4" opacity="0.85" />

                        {{-- Head Oval (Kepala & Peci) --}}
                        <ellipse cx="105" cy="110" rx="50" ry="67" fill="rgba(1, 176, 245, 0.06)" stroke="#01b0f5" stroke-width="1.8" stroke-dasharray="5,4" opacity="0.9" />

                        {{-- Eye Line Guide (Tinggi Mata) --}}
                        <line x1="65" y1="106" x2="145" y2="106" stroke="#f8bb00" stroke-width="1.6" stroke-dasharray="3,3" opacity="0.95" />
                        <rect x="73" y="96" width="64" height="12" rx="3" fill="rgba(0,0,0,0.65)" />
                        <text x="105" y="105" text-anchor="middle" font-size="7.5" fill="#f8bb00" font-weight="bold" letter-spacing="0.4">TINGGI MATA</text>

                        {{-- Top of Head Guideline --}}
                        <line x1="80" y1="43" x2="130" y2="43" stroke="#01b0f5" stroke-width="1.5" stroke-dasharray="3,3" opacity="0.85" />
                        <rect x="58" y="33" width="94" height="12" rx="3" fill="rgba(0,0,0,0.65)" />
                        <text x="105" y="42" text-anchor="middle" font-size="7" fill="#01b0f5" font-weight="bold">PUNCAK KEPALA</text>

                        {{-- Shoulder Guideline Curve --}}
                        <path d="M 15 280 C 35 220, 65 202, 105 202 C 145 202, 175 220, 195 280"
                              fill="none" stroke="#f8bb00" stroke-width="2" stroke-dasharray="5,4" opacity="0.9" />
                        <rect x="75" y="208" width="60" height="12" rx="3" fill="rgba(0,0,0,0.65)" />
                        <text x="105" y="217" text-anchor="middle" font-size="7.5" fill="#f8bb00" font-weight="bold" letter-spacing="0.4">GARIS BAHU</text>

                        {{-- Center crosshair --}}
                        <circle cx="105" cy="140" r="2.5" fill="#ffffff" opacity="0.5" />
                    </svg>

                    {{-- Interactive Floating Drag Indicator Tip --}}
                    <div class="absolute bottom-2 inset-x-2 text-center pointer-events-none">
                        <span class="inline-block px-2.5 py-0.5 rounded-full bg-slate-950/85 backdrop-blur-sm text-[9.5px] font-bold text-slate-200 border border-slate-700/80 shadow-md">
                            👆 Geser foto untuk memposisikan
                        </span>
                    </div>
                </div>

                {{-- Toolbar Controls: Zoom, Rotate, Reset --}}
                <div class="w-full max-w-[260px] sm:max-w-xs mt-3 space-y-2 shrink-0">
                    {{-- Zoom Slider + Buttons --}}
                    <div class="flex items-center gap-2 bg-slate-800/90 rounded-xl px-2.5 py-1 border border-slate-700">
                        <button type="button" @click="zoomOut()" title="Perkecil Ukuran"
                            class="w-7 h-7 rounded-lg bg-slate-700 hover:bg-slate-600 text-white font-black text-xs flex items-center justify-center transition shrink-0 active:scale-95">
                            &minus;
                        </button>
                        <div class="flex-1 px-1">
                            <input type="range"
                                :min="minScale"
                                :max="maxScale"
                                step="0.02"
                                x-model.number="scale"
                                class="w-full h-1.5 bg-slate-600 rounded-lg appearance-none cursor-pointer accent-[#f8bb00]">
                        </div>
                        <button type="button" @click="zoomIn()" title="Perbesar Ukuran"
                            class="w-7 h-7 rounded-lg bg-slate-700 hover:bg-slate-600 text-white font-black text-xs flex items-center justify-center transition shrink-0 active:scale-95">
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
            <div class="px-4 py-3 sm:px-5 sm:py-3.5 bg-slate-50 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:items-center justify-between gap-2 shrink-0">
                <button type="button" @click="useOriginal()"
                    class="px-3.5 py-2 rounded-xl border border-slate-300 hover:bg-white text-slate-600 text-[11px] sm:text-xs font-bold transition text-center">
                    Gunakan Foto Asli
                </button>

                <button type="button" @click="applyCrop()"
                    class="px-4 py-2 sm:px-5 sm:py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-extrabold text-xs shadow-xs transition duration-150 flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        isCropped: false,

        // Image & Dimensions
        img: null,
        imgWidth: 0,
        imgHeight: 0,

        // Viewport 3:4 aspect ratio (compact 210x280 for mobile, exported as HD 600x800)
        viewportW: 210,
        viewportH: 280,
        outputW: 600,
        outputH: 800,

        // Position & Transformation State
        centerX: 105,
        centerY: 140,
        scale: 1,
        minScale: 0.3,
        maxScale: 3.5,
        rotation: 0,

        // Pointer Drag Tracking
        isDragging: false,
        startX: 0,
        startY: 0,
        initialCenterX: 105,
        initialCenterY: 140,

        init() {
            if (config.currentPhoto) {
                this.photoPreview = config.currentPhoto;
            }
        },

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                this.rawImageSrc = e.target.result;
                this.loadImage(this.rawImageSrc);
            };
            reader.readAsDataURL(file);
        },

        loadImage(src) {
            const image = new Image();
            image.onload = () => {
                this.img = image;
                this.imgWidth = image.naturalWidth || image.width;
                this.imgHeight = image.naturalHeight || image.height;
                this.resetTransform();
                this.modalOpen = true;
            };
            image.src = src;
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

            // Fit scale so image covers the 3:4 box completely
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

        applyCrop() {
            if (!this.img) return;

            const canvas = document.createElement('canvas');
            canvas.width = this.outputW;
            canvas.height = this.outputH;
            const ctx = canvas.getContext('2d');

            // White background fallback
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, this.outputW, this.outputH);

            const ratio = this.outputW / this.viewportW; // 600 / 270

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

            // High resolution JPEG DataURL for preview
            const croppedDataUrl = canvas.toDataURL('image/jpeg', 0.92);
            this.photoPreview = croppedDataUrl;
            this.isCropped = true;
            this.modalOpen = false;

            // Generate File object and assign to the file input via DataTransfer
            canvas.toBlob((blob) => {
                if (!blob) return;
                const fileInput = document.getElementById(this.inputId);
                if (fileInput) {
                    const croppedFile = new File([blob], 'pasfoto-3x4.jpg', {
                        type: 'image/jpeg',
                        lastModified: Date.now()
                    });
                    const dt = new DataTransfer();
                    dt.items.add(croppedFile);
                    fileInput.files = dt.files;
                }
            }, 'image/jpeg', 0.92);
        },

        useOriginal() {
            this.photoPreview = this.rawImageSrc;
            this.isCropped = false;
            this.modalOpen = false;
        }
    }));
});
</script>
@endonce
