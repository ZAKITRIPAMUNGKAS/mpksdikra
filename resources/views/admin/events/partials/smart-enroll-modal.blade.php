{{-- ============================================================
     Smart Enroll — Chatbot AI Perkaderan (SiKader)
     Prompt Composer & Glass Visuals (21st.dev inspired)
     ============================================================ --}}
<div x-data="smartEnrollChatbot({
        eventId: {{ (int) $event->id }},
        eventTitle: {{ json_encode($event->nama_event) }},
        previewUrl: {{ json_encode(route('admin.events.smart-enroll.preview', $event)) }},
        matchUrl: {{ json_encode(route('admin.events.smart-enroll.match', $event)) }},
        recommendUrl: {{ json_encode(route('admin.events.smart-enroll.recommend', $event)) }},
        aiPromptUrl: {{ json_encode(route('admin.events.smart-enroll.ai-prompt', $event)) }},
        executeUrl: {{ json_encode(route('admin.events.smart-enroll.execute', $event)) }},
        undoUrl: {{ json_encode(route('admin.events.smart-enroll.undo', $event)) }},
        feedbackUrl: {{ json_encode(route('admin.events.smart-enroll.feedback', $event)) }},
        eventUpdateConfirmUrl: {{ json_encode(route('admin.events.smart-enroll.event-update.confirm', $event)) }},
        quota: {{ (int) ($event->kuota ?? 0) }},
        registeredCount: {{ (int) ($event->peserta_aktif_count ?? $event->pesertaAktif()->count()) }},
     })"
     @open-smart-enroll.window="openModal()"
     x-show="open"
     x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @keydown.escape.window="if (previewDrawerOpen) { previewDrawerOpen = false; } else { close(); }"
     class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm p-2 sm:p-4 overflow-hidden !m-0 m-0">

    {{-- Chat Window Shell --}}
    <div class="sikader-shell relative flex flex-col bg-slate-50 dark:bg-[#0b1620] w-full max-w-[calc(100vw-1rem)] h-[calc(100dvh-1rem)] max-h-[calc(100dvh-1rem)] rounded-2xl sm:max-w-2xl shadow-2xl border border-slate-200/90 dark:border-slate-800 overflow-hidden overscroll-contain sm:h-[680px] sm:max-h-[92vh]"
         role="dialog"
         aria-modal="true"
         aria-label="Percakapan SiKader"
         :data-theme="theme"
         :class="theme === 'dark' ? 'dark text-slate-100' : 'text-slate-800'"
         :data-font-size="fontSize"
         x-show="open"
         x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
         @click.outside="close()"
         x-ref="chatWindow">

        {{-- ── Header ── --}}
        <div class="flex items-center justify-between gap-3 px-4 sm:px-5 py-3.5 bg-gradient-to-r from-[#00476d] via-[#005c8a] to-[#00476d] text-white shrink-0 border-b border-white/10 shadow-xs z-20">
            <div class="flex items-center gap-3 min-w-0">
                {{-- Avatar with pulse indicator --}}
                <div class="relative shrink-0">
                    <div class="w-10 h-10 rounded-full bg-white p-0.5 flex items-center justify-center ring-2 ring-white/40 shadow-sm overflow-hidden">
                        <img src="{{ asset('aset/sikader.jpg') }}" alt="Logo SiKader" class="w-full h-full object-contain rounded-full">
                    </div>
                    <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-emerald-400 ring-2 ring-[#00476d] animate-pulse" title="Online"></span>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="font-black text-sm sm:text-base leading-tight text-white tracking-tight" x-text="UI?.identity?.name || 'SiKader'">SiKader</h3>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-amber-400 text-[#002b42] tracking-wider uppercase shadow-2xs" x-text="UI?.identity?.badge || 'Aktif'">Aktif</span>
                    </div>
                    <p class="text-[11px] text-sky-100/90 font-medium leading-snug truncate" x-text="UI?.identity?.subtitle || 'Asisten Perkaderan MPKSDI'">Asisten Perkaderan MPKSDI</p>
                </div>
            </div>

            {{-- Action Controls (Settings Menu & Close) --}}
            <div class="flex items-center gap-1.5 shrink-0 relative" @keydown.escape.stop="menuOpen = false">
                {{-- Dropdown Trigger (⋯) --}}
                <div class="relative">
                    <button type="button"
                            @click="menuOpen = !menuOpen"
                            aria-label="Menu pengaturan"
                            title="Pengaturan SiKader"
                            :aria-expanded="menuOpen"
                            class="min-h-11 min-w-11 rounded-full bg-white/10 hover:bg-white/20 active:bg-white/30 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white flex items-center justify-center transition text-white cursor-pointer active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="12" r="1"/>
                            <circle cx="19" cy="12" r="1"/>
                            <circle cx="5" cy="12" r="1"/>
                        </svg>
                    </button>

                    {{-- Dropdown Panel --}}
                    <div x-show="menuOpen"
                         x-cloak
                         @click.outside="menuOpen = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                         class="absolute right-0 top-full mt-2 w-56 rounded-xl bg-white dark:bg-[#172635] text-slate-800 dark:text-slate-100 shadow-xl border border-slate-200 dark:border-slate-700 py-1.5 z-50 text-xs divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                        
                        {{-- Opsi 1: Ukuran Teks --}}
                        <div class="p-1.5">
                            <button type="button" @click="cycleFontSize()" aria-label="Pilih ukuran teks" title="Pilih ukuran teks (Kecil, Sedang, Besar)"
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-left transition cursor-pointer">
                                <span class="flex items-center gap-2.5">
                                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M4 7V4h16v3"/><path d="M9 20h6"/><path d="M12 4v16"/>
                                    </svg>
                                    <span>Ukuran teks</span>
                                </span>
                                <span class="px-2 py-0.5 rounded bg-sky-50 dark:bg-sky-950 text-[#005c8a] dark:text-sky-300 font-bold uppercase text-[10px]" x-text="fontSize === 's' ? 'Kecil' : (fontSize === 'l' ? 'Besar' : 'Sedang')"></span>
                            </button>
                        </div>

                        {{-- Opsi 2: Tema Gelap / Terang --}}
                        <div class="p-1.5">
                            <button type="button" @click="toggleTheme()" aria-label="Ganti tema tampilan" :title="theme === 'dark' ? 'Gunakan tema terang' : 'Gunakan tema gelap'"
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-left transition cursor-pointer">
                                <span class="flex items-center gap-2.5">
                                    <template x-if="theme === 'dark'">
                                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                            <circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>
                                        </svg>
                                    </template>
                                    <template x-if="theme !== 'dark'">
                                        <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
                                        </svg>
                                    </template>
                                    <span>Tema tampilan</span>
                                </span>
                                <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400" x-text="theme === 'dark' ? 'Gelap' : 'Terang'"></span>
                            </button>
                        </div>

                        {{-- Opsi 3: Mulai Ulang Percakapan --}}
                        <div class="p-1.5">
                            <button type="button" @click="menuOpen = false; reset()" aria-label="Mulai ulang percakapan" title="Mulai ulang percakapan"
                                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 text-slate-700 dark:text-slate-300 hover:text-rose-600 dark:hover:text-rose-400 text-left transition cursor-pointer">
                                <svg class="w-4 h-4 text-slate-400 dark:text-slate-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/>
                                    <path d="M21 3v5h-5"/>
                                    <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/>
                                    <path d="M3 21v-5h5"/>
                                </svg>
                                <span>Mulai ulang chat</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Close button --}}
                <button type="button" @click="close()" aria-label="Tutup SiKader" title="Tutup Jendela"
                        class="min-h-11 min-w-11 rounded-full bg-white/10 hover:bg-white/20 active:bg-white/30 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white flex items-center justify-center transition text-white/90 hover:text-white cursor-pointer active:scale-95">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- ── Pinned Event Context Strip ── --}}
        <div class="px-4 py-2.5 bg-slate-50/90 dark:bg-[#08111a] border-b border-slate-200/80 dark:border-slate-800/80 text-xs shrink-0 z-10 transition-colors">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2 min-w-0 flex-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0 animate-pulse"></span>
                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider shrink-0">Konteks kegiatan:</span>
                    <span class="font-bold text-[#005c8a] dark:text-sky-300 text-xs leading-snug truncate" title="{{ $event->nama_event }}">{{ $event->nama_event }}</span>
                </div>
                <div class="flex items-baseline gap-1.5 shrink-0 text-right">
                    <span class="text-xs font-black text-slate-800 dark:text-slate-100 tracking-tight">
                        <span class="text-[#005c8a] dark:text-amber-400 font-bold" x-text="registeredCount">{{ $event->peserta_aktif_count ?? $event->pesertaAktif()->count() }}</span>/<span class="font-semibold text-slate-500 dark:text-slate-400" x-text="quota || '∞'">{{ $event->kuota ?: '∞' }}</span>
                    </span>
                    <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Terdaftar</span>
                </div>
            </div>
            <div class="mt-1.5 h-1 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800" role="progressbar" aria-label="Kapasitas kegiatan" :aria-valuenow="registeredCount" :aria-valuemin="0" :aria-valuemax="quota || registeredCount || 1">
                <div class="h-full rounded-full bg-gradient-to-r from-[#005c8a] to-sky-400 dark:from-sky-500 dark:to-amber-400 transition-[width] duration-300" :style="{ width: quota ? Math.min(100, Math.round((registeredCount / quota) * 100)) + '%' : '100%' }"></div>
            </div>
        </div>

        {{-- ── Chat Body (scrollable) ── --}}
        <div class="flex-1 overflow-y-auto overscroll-contain px-3.5 sm:px-5 py-3.5 space-y-3.5 bg-slate-100/40 dark:bg-[#0b141f] sikader-chat-scroll" x-ref="chatBody" role="log" aria-live="polite" aria-relevant="additions text">

            <template x-for="msg in messages" :key="msg.id">
                <div>
                    {{-- ── ASSISTANT: text bubble with build log & citations (21st.dev style) ── --}}
                    <template x-if="msg.role === 'assistant' && msg.type === 'text'">
                        <div class="flex items-start gap-2.5 max-w-[95%] sm:max-w-[88%]" x-data="{ activeSource: null, showSources: false, feedbackOpen: false, feedbackRating: null, feedbackReason: '' }">
                            <div class="w-8 h-8 rounded-full bg-white dark:bg-slate-800 border border-sky-200/90 dark:border-slate-700 p-0.5 flex items-center justify-center shrink-0 mt-0.5 shadow-2xs overflow-hidden">
                                <img src="{{ asset('aset/sikader.jpg') }}" alt="SiKader" class="w-full h-full object-contain rounded-full">
                            </div>
                            <div class="space-y-1.5 flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 pl-1">
                                    <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300">SiKader</span>
                                    <span class="text-[9px] font-semibold text-sky-600 dark:text-sky-400">&bull; Asisten</span>
                                    <template x-if="msg.data && msg.data.sources && msg.data.sources.length > 0">
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-[9px] font-semibold border border-emerald-200/80 dark:border-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>RAG Grounded</span>
                                        </span>
                                    </template>
                                </div>

                                <div class="glass-card rounded-2xl rounded-tl-xs px-3.5 py-2.5 text-xs text-slate-800 dark:text-[#E6EDF3] leading-relaxed shadow-xs relative">
                                    {{-- Build Log (Status Proses RAG) ala 21st.dev --}}
                                    <template x-if="msg.data?.buildLog && msg.data.buildLog.length > 0">
                                        <div class="mb-3 rounded-xl border border-slate-200/90 dark:border-slate-700/80 bg-slate-50/80 dark:bg-slate-900/60 p-2.5 text-xs font-mono">
                                            <div class="flex items-center justify-between gap-2 mb-1">
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                                    <span class="w-2 h-2 rounded-full" :class="msg.data.logCompleted ? 'bg-emerald-500' : 'bg-[var(--accent)] animate-pulse'"></span>
                                                    <span x-text="UI.buildLog.title">Proses RAG</span>
                                                </span>
                                                <template x-if="msg.data.logCompleted">
                                                    <button type="button"
                                                            @click="msg.data.logExpanded = !msg.data.logExpanded"
                                                            :aria-label="msg.data.logExpanded ? UI.buildLog.toggleHide(msg.data.buildLog.length) : UI.buildLog.toggleShow(msg.data.buildLog.length)"
                                                            class="text-[10px] font-semibold text-[#005c8a] dark:text-sky-300 hover:underline cursor-pointer flex items-center gap-1">
                                                        <span x-text="msg.data.logExpanded ? UI.buildLog.toggleHide(msg.data.buildLog.length) : UI.buildLog.toggleShow(msg.data.buildLog.length)"></span>
                                                        <svg :class="msg.data.logExpanded ? 'rotate-180' : ''" class="w-3 h-3 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                    </button>
                                                </template>
                                            </div>
                                            <div x-show="!msg.data.logCompleted || msg.data.logExpanded" x-collapse>
                                                <ul class="space-y-1.5 pt-1 text-[11px]" role="status" :aria-label="UI.buildLog.statusLiveAria">
                                                    <template x-for="(step, sIdx) in msg.data.buildLog" :key="sIdx">
                                                        <li class="flex items-center gap-2 build-log-item"
                                                            :class="step.status === 'done' ? 'text-emerald-700 dark:text-emerald-400' : (step.status === 'active' ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'text-slate-400 dark:text-slate-500')">
                                                            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] shrink-0"
                                                                  :class="step.status === 'done' ? 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 font-bold' : (step.status === 'active' ? 'bg-amber-100 dark:bg-amber-950/80 text-amber-600' : 'bg-slate-100 dark:bg-slate-800')">
                                                                <template x-if="step.status === 'done'"><span>✓</span></template>
                                                                <template x-if="step.status === 'active'"><span class="animate-pulse">●</span></template>
                                                                <template x-if="step.status === 'pending'"><span>○</span></template>
                                                            </span>
                                                            <span x-text="step.text"></span>
                                                        </li>
                                                    </template>
                                                </ul>
                                            </div>
                                        </div>
                                    </template>

                                                                        {{-- Low Confidence Notice --}}
                                    <template x-if="msg.data?.notice">
                                        <div class="mb-2 flex items-start gap-2 rounded-lg border border-amber-300/80 bg-amber-50 dark:bg-amber-950/40 px-3 py-2 text-[11px] text-amber-900 dark:text-amber-200">
                                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                            <div class="flex-1 leading-relaxed">
                                                <span class="font-bold mr-1">Perlu Konfirmasi:</span>
                                                <span x-text="msg.data.notice"></span>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- Teks bubble dengan inline citation chips --}}
                                    <div class="whitespace-pre-line leading-relaxed"
                                         @click="if ($event.target.closest('[data-cite-id]')) { const cid = parseInt($event.target.closest('[data-cite-id]').dataset.citeId); openPreviewFromId(cid, msg.data?.sources); }"
                                         x-html="renderMessageWithCitations(msg.content, msg.data?.sources, activePreviewSource?.id)"></div>

                                    <template x-if="msg.data?.event_update">
                                        <div class="mt-3 rounded-xl border border-amber-300 bg-amber-50 p-3 text-slate-800 dark:border-amber-700 dark:bg-amber-950/30 dark:text-slate-100">
                                            <p class="text-xs font-bold">Pratinjau perubahan kegiatan</p>
                                            <dl class="mt-2 space-y-1.5 text-xs">
                                                <template x-for="change in msg.data.event_update.changes" :key="change.field">
                                                    <div class="flex flex-wrap gap-1"><dt class="font-semibold" x-text="change.label + ':'"></dt><dd x-text="change.value"></dd></div>
                                                </template>
                                            </dl>
                                            <div class="mt-3 flex gap-2">
                                                <button type="button" @click="confirmEventUpdate(msg.data.event_update.token, $event.currentTarget)" class="min-h-10 rounded-lg bg-amber-500 px-3 text-xs font-bold text-slate-950 hover:bg-amber-400">Konfirmasi perubahan</button>
                                                <button type="button" @click="$el.closest('.rounded-xl').remove()" class="min-h-10 rounded-lg border border-slate-300 px-3 text-xs font-semibold dark:border-slate-600">Batal</button>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- Action Buttons (WhatsApp Blast, Export Excel, Berita Acara, Ranking) --}}
                                    <template x-if="msg.data && msg.data.action_buttons && msg.data.action_buttons.length > 0">
                                        <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex flex-wrap gap-2">
                                            <template x-for="btn in msg.data.action_buttons" :key="btn.label">
                                                <a :href="btn.url" :target="btn.target || (btn.icon === 'whatsapp' ? '_blank' : '_self')"
                                                   rel="noopener noreferrer"
                                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all duration-150 active:scale-95 shadow-xs cursor-pointer select-none"
                                                   :class="btn.style === 'success' || btn.icon === 'whatsapp' ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-900/20' : (btn.style === 'excel' || btn.icon === 'excel' ? 'bg-emerald-700 hover:bg-emerald-800 text-white shadow-emerald-950/20' : 'bg-[#005c8a] hover:bg-[#00486d] text-white shadow-sky-900/20')">
                                                    <template x-if="btn.icon === 'whatsapp'">
                                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2zm5.79 14.15c-.24.68-1.39 1.3-1.92 1.38-.5.08-1.14.11-3.69-.93-3.26-1.34-5.36-4.66-5.52-4.88-.16-.22-1.32-1.75-1.32-3.34 0-1.59.84-2.37 1.14-2.69.3-.32.65-.4.87-.4.22 0 .43.01.62.02.2.01.47-.08.73.55.27.65.92 2.25 1 2.41.08.16.13.36.03.57-.1.22-.16.35-.32.54-.16.19-.34.42-.48.56-.16.16-.33.33-.14.65.19.32.84 1.38 1.8 2.24 1.24 1.1 2.28 1.44 2.61 1.6.32.16.51.14.7-.08.19-.22.81-.95 1.03-1.27.22-.32.43-.27.73-.16.3.11 1.9.9 2.22 1.06.32.16.54.24.62.38.08.13.08.78-.16 1.46z"/>
                                                        </svg>
                                                    </template>
                                                    <template x-if="btn.icon === 'excel'">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/>
                                                            <path d="M14 2v4a2 2 0 0 0 2 2h4"/>
                                                            <path d="M8 13h2"/>
                                                            <path d="M14 13h2"/>
                                                            <path d="M8 17h2"/>
                                                            <path d="M14 17h2"/>
                                                        </svg>
                                                    </template>
                                                    <template x-if="btn.icon === 'ranking'">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/>
                                                            <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/>
                                                            <path d="M4 22h16"/>
                                                            <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/>
                                                            <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/>
                                                            <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>
                                                        </svg>
                                                    </template>
                                                    <template x-if="btn.icon === 'document'">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                            <polyline points="14 2 14 8 20 8"/>
                                                            <line x1="16" y1="13" x2="8" y2="13"/>
                                                            <line x1="16" y1="17" x2="8" y2="17"/>
                                                            <polyline points="10 9 9 9 8 9"/>
                                                        </svg>
                                                    </template>
                                                    <span x-text="btn.label"></span>
                                                </a>
                                            </template>
                                        </div>
                                    </template>

                                    <div class="mt-3 border-t border-slate-100 pt-2.5 dark:border-slate-800">
                                        <button type="button" @click="feedbackOpen = !feedbackOpen"
                                                class="min-h-10 rounded-lg px-2 text-[11px] font-semibold text-slate-500 hover:bg-slate-50 hover:text-[#005c8a] dark:text-slate-400 dark:hover:bg-white/10 dark:hover:text-amber-400">
                                            Beri penilaian jawaban
                                        </button>
                                        <div x-show="feedbackOpen" x-cloak class="mt-2 rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">Seberapa membantu jawaban ini?</p>
                                            <div class="mt-2 flex items-center gap-1" role="radiogroup" aria-label="Penilaian jawaban">
                                                <template x-for="score in [1, 2, 3, 4, 5]" :key="score">
                                                    <button type="button" @click="feedbackRating = score" :aria-checked="feedbackRating === score"
                                                            role="radio" :title="score + ' dari 5'"
                                                            :class="feedbackRating >= score ? 'text-amber-400' : 'text-slate-300 dark:text-slate-600'"
                                                            class="min-h-10 min-w-10 text-xl transition">★</button>
                                                </template>
                                            </div>
                                            <textarea x-model="feedbackReason" rows="2" maxlength="500"
                                                      placeholder="Ceritakan apa yang perlu diperbaiki (opsional)"
                                                      class="mt-2 w-full resize-none rounded-lg border border-slate-200 bg-transparent px-2.5 py-2 text-xs text-slate-800 placeholder:text-slate-500 dark:border-slate-700 dark:text-slate-100 dark:placeholder:text-slate-400"></textarea>
                                            <button type="button" :disabled="!feedbackRating || feedbackSending"
                                                    @click="sendFeedback(msg, feedbackRating, feedbackReason); feedbackOpen = false"
                                                    class="mt-2 min-h-10 rounded-lg bg-[var(--accent)] px-3 text-xs font-bold text-[#003855] disabled:cursor-not-allowed disabled:opacity-40">Kirim penilaian</button>
                                        </div>
                                    </div>

                                    {{-- Collapsible Sources Drawer (Sources Tray ala 21st.dev) --}}
                                    <template x-if="msg.data && msg.data.sources && msg.data.sources.length > 0">
                                        <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800">
                                            <button type="button"
                                                    @click="showSources = !showSources"
                                                    class="w-full flex items-center justify-between text-[11px] font-bold text-slate-600 dark:text-slate-300 hover:text-[#005c8a] dark:hover:text-sky-300 transition py-1 px-1 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer select-none">
                                                <div class="flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-sky-600 dark:text-sky-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    <span>Sumber Data RAG Terverifikasi (<span x-text="msg.data.sources.length"></span>)</span>
                                                </div>
                                                <div class="flex items-center gap-1 text-[10px] text-slate-400 font-semibold">
                                                    <span x-text="showSources ? 'Tutup' : 'Lihat Rujukan'"></span>
                                                    <svg :class="showSources ? 'rotate-180' : ''" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </div>
                                            </button>

                                            <div x-show="showSources" x-collapse class="mt-2.5 grid grid-cols-1 sm:grid-cols-2 gap-2" style="display: none;">
                                                <template x-for="src in msg.data.sources" :key="src.id">
                                                    <div @click="openPreviewSource(src, msg.data.sources)"
                                                         class="p-2.5 rounded-xl border text-left transition-all duration-150 cursor-pointer group glass-card"
                                                         :class="activePreviewSource?.id === src.id ? 'border-[var(--accent)] ring-1 ring-[var(--accent)] shadow-xs' : 'border-slate-200/80 dark:border-slate-700/80 hover:border-[#005c8a]'">
                                                        <div class="flex items-center justify-between gap-1 mb-1">
                                                            <div class="flex items-center gap-1.5 min-w-0">
                                                                <span class="w-4 h-4 rounded-full text-[9px] font-black flex items-center justify-center shrink-0 transition-colors bg-[#005c8a] text-white"
                                                                      x-text="src.id"></span>
                                                                <span class="text-[11px] font-bold text-slate-800 dark:text-slate-100 group-hover:text-[#005c8a] dark:group-hover:text-sky-300 truncate" x-text="src.title"></span>
                                                            </div>
                                                        </div>
                                                        <span class="inline-block px-1.5 py-0.2 rounded bg-slate-200/70 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-[9px] font-mono font-semibold mb-1" x-text="src.badge"></span>
                                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed" x-text="src.snippet"></p>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- ── ASSISTANT: thinking / progressive build log ── --}}
                    <template x-if="msg.role === 'assistant' && msg.type === 'thinking'">
                        <div class="flex items-start gap-2.5 max-w-[95%] sm:max-w-[88%]">
                            <div class="w-8 h-8 rounded-full bg-white dark:bg-slate-800 border border-sky-300 dark:border-slate-700 p-0.5 flex items-center justify-center shrink-0 mt-0.5 shadow-2xs overflow-hidden animate-pulse">
                                <img src="{{ asset('aset/sikader.jpg') }}" alt="SiKader" class="w-full h-full object-contain rounded-full">
                            </div>
                            <div class="space-y-1.5 flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 pl-1">
                                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">SiKader</span>
                                    <span class="text-[9px] text-sky-600 dark:text-sky-400 font-semibold animate-pulse">&bull; Sedang memproses...</span>
                                </div>
                                <div class="glass-composer rounded-2xl rounded-tl-xs p-3.5 shadow-xs transition-all duration-300">
                                    <div class="flex items-center gap-2.5 mb-2">
                                        <div class="flex items-center gap-1 shrink-0">
                                            <span class="w-2 h-2 rounded-full bg-[#005c8a] animate-bounce" style="animation-delay: 0ms"></span>
                                            <span class="w-2 h-2 rounded-full bg-cyan-500 animate-bounce" style="animation-delay: 150ms"></span>
                                            <span class="w-2 h-2 rounded-full bg-[var(--accent)] animate-bounce" style="animation-delay: 300ms"></span>
                                        </div>
                                        <span class="text-xs text-slate-700 dark:text-slate-200 font-medium truncate" x-text="msg.content || 'Bentar ya, saya putar dulu otak digitalnya... 🧠'"></span>
                                    </div>
                                    {{-- Progressive Steps --}}
                                    <template x-if="msg.data?.steps && msg.data.steps.length > 0">
                                        <div class="pt-2 border-t border-slate-200/80 dark:border-slate-700/80 space-y-1.5 font-mono text-[11px]" role="status" :aria-label="UI.buildLog.statusLiveAria">
                                            <template x-for="(st, idx) in msg.data.steps" :key="idx">
                                                <div class="flex items-center gap-2"
                                                     :class="idx < msg.data.currentStep ? 'text-emerald-700 dark:text-emerald-400 font-medium' : (idx === msg.data.currentStep ? 'text-amber-600 dark:text-amber-400 font-bold' : 'text-slate-400 dark:text-slate-600')">
                                                    <span class="w-3.5 h-3.5 rounded-full flex items-center justify-center text-[9px] shrink-0"
                                                          :class="idx < msg.data.currentStep ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700' : (idx === msg.data.currentStep ? 'bg-amber-100 dark:bg-amber-950 text-amber-600 animate-pulse' : 'bg-slate-100 dark:bg-slate-800')">
                                                        <span x-text="idx < msg.data.currentStep ? '✓' : (idx === msg.data.currentStep ? '●' : '○')"></span>
                                                    </span>
                                                    <span x-text="st"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>



                    {{-- ── ASSISTANT: match results bubble (glass cards) ── --}}
                    <template x-if="msg.role === 'assistant' && msg.type === 'match-result'">
                        <div class="flex items-start gap-2.5 max-w-full" x-data="{ selectedIds: msg.data.matched.map(m => m.anggota.id) }">
                            <div class="w-8 h-8 rounded-full bg-white dark:bg-slate-800 border border-sky-200/90 dark:border-slate-700 p-0.5 flex items-center justify-center shrink-0 mt-0.5 shadow-2xs overflow-hidden">
                                <img src="{{ asset('aset/sikader.jpg') }}" alt="SiKader" class="w-full h-full object-contain rounded-full">
                            </div>
                            <div class="flex-1 min-w-0 space-y-2.5">
                                {{-- Status tiles --}}
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="glass-card rounded-xl p-2.5 text-center border-emerald-200/80 bg-emerald-50/70 dark:bg-emerald-950/40">
                                        <span class="text-[9px] font-bold text-emerald-700 dark:text-emerald-300 uppercase tracking-wide block">Siap Enroll</span>
                                        <span class="text-base font-black text-emerald-800 dark:text-emerald-200" x-text="msg.data.matched.length">0</span>
                                    </div>
                                    <div class="glass-card rounded-xl p-2.5 text-center border-sky-200/80 bg-sky-50/70 dark:bg-sky-950/40">
                                        <span class="text-[9px] font-bold text-[#005c8a] dark:text-sky-300 uppercase tracking-wide block">Sudah Daftar</span>
                                        <span class="text-base font-black text-[#005c8a] dark:text-sky-200" x-text="msg.data.already_enrolled.length">0</span>
                                    </div>
                                    <div class="glass-card rounded-xl p-2.5 text-center border-amber-200/80 bg-amber-50/70 dark:bg-amber-950/40">
                                        <span class="text-[9px] font-bold text-amber-700 dark:text-amber-300 uppercase tracking-wide block">Tanpa Akun</span>
                                        <span class="text-base font-black text-amber-800 dark:text-amber-200" x-text="msg.data.unmatched.length">0</span>
                                    </div>
                                </div>

                                {{-- Matched candidate list --}}
                                <template x-if="msg.data.matched.length > 0">
                                    <div class="glass-card rounded-2xl overflow-hidden shadow-xs border border-slate-200/90 dark:border-slate-800">
                                        <div class="px-3.5 py-2.5 bg-emerald-50/90 dark:bg-emerald-950/50 border-b border-emerald-100 dark:border-emerald-900 flex items-center justify-between">
                                            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                                <input type="checkbox"
                                                    :checked="selectedIds.length === msg.data.matched.length"
                                                    @change="selectedIds = (selectedIds.length === msg.data.matched.length ? [] : msg.data.matched.map(m => m.anggota.id))"
                                                    class="w-4 h-4 rounded border-slate-300 text-[#005c8a] focus:ring-[var(--accent)]">
                                                <span class="text-[11px] font-bold text-emerald-800 dark:text-emerald-200">✅ Siap Enroll</span>
                                            </label>
                                            <span class="text-[10px] text-emerald-700 dark:text-emerald-300 font-semibold">
                                                <span x-text="selectedIds.length"></span> dipilih
                                            </span>
                                        </div>
                                        <div class="max-h-56 overflow-y-auto overscroll-contain divide-y divide-slate-100 dark:divide-slate-800/80 p-1">
                                            <template x-for="item in msg.data.matched" :key="item.anggota.id">
                                                <div class="glass-card my-1 rounded-xl p-2 flex items-center justify-between gap-2">
                                                    <label class="flex items-center gap-2.5 min-w-0 flex-1 cursor-pointer select-none">
                                                        <input type="checkbox" :value="item.anggota.id" x-model.number="selectedIds"
                                                            class="w-4 h-4 rounded border-slate-300 text-[#005c8a] focus:ring-[var(--accent)] shrink-0">
                                                        <div class="w-8 h-10 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 shrink-0 bg-slate-100 shadow-2xs">
                                                            <img :src="'/foto/anggota/' + item.anggota.id + '?v=2'" :alt="item.anggota.nama_lengkap" class="w-full h-full object-cover">
                                                        </div>
                                                        <div class="min-w-0 flex-1">
                                                            <p class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="item.anggota.nama_lengkap"></p>
                                                            <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate">
                                                                NIK: <span class="font-mono" x-text="maskNik(item.anggota.nik)"></span> &bull; PCM <span x-text="item.pcm"></span>
                                                            </p>
                                                        </div>
                                                    </label>
                                                    <button type="button"
                                                            @click="requestEnrollmentConfirmation([item.anggota.id], item.anggota.nama_lengkap)"
                                                            title="Daftarkan kader ini"
                                                            class="shrink-0 px-2.5 py-1.5 rounded-lg text-[10px] font-bold bg-sky-50 dark:bg-sky-950/60 text-[#005c8a] dark:text-sky-300 hover:bg-[#005c8a] hover:text-white transition cursor-pointer">
                                                        Daftarkan
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                {{-- Unmatched list + WA invite --}}
                                <template x-if="msg.data.unmatched.length > 0">
                                    <div class="glass-card rounded-2xl overflow-hidden shadow-xs border-amber-200/90 dark:border-amber-900/60 bg-amber-50/60 dark:bg-amber-950/30">
                                        <div class="px-3.5 py-2 border-b border-amber-100 dark:border-amber-900/60 flex items-center justify-between gap-2">
                                            <span class="text-[11px] font-bold text-amber-900 dark:text-amber-200">⚠️ Belum Punya Akun (<span x-text="msg.data.unmatched.length"></span>)</span>
                                            <button type="button"
                                                    @click="copyWaInvite(msg.data.whatsapp_invite)"
                                                    class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold transition flex items-center gap-1 shrink-0 cursor-pointer shadow-2xs">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                </svg>
                                                <span x-text="copiedWa ? 'Tersalin!' : 'Salin Undangan WA'"></span>
                                            </button>
                                        </div>
                                        <div class="max-h-32 overflow-y-auto overscroll-contain px-3 py-2">
                                            <ul class="text-[11px] text-slate-700 dark:text-slate-300 space-y-0.5 list-disc pl-3.5">
                                                <template x-for="item in msg.data.unmatched" :key="item.raw_name">
                                                    <li x-text="item.raw_name" class="font-medium"></li>
                                                </template>
                                            </ul>
                                        </div>
                                    </div>
                                </template>

                                {{-- Bulk Enroll CTA --}}
                                <template x-if="msg.data.matched.length > 0">
                                    <button type="button"
                                            @click="requestEnrollmentConfirmation(selectedIds)"
                                            :disabled="executing || selectedIds.length === 0"
                                            class="w-full py-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs cursor-pointer"
                                            :class="selectedIds.length === 0 ? 'bg-slate-200 dark:bg-slate-800 text-slate-400 dark:text-slate-600 cursor-not-allowed' : 'bg-[#005c8a] hover:bg-[#00486d] text-white'">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                        </svg>
                                        <span>Enroll <span x-text="selectedIds.length"></span> Kader Terpilih</span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- ── ASSISTANT: candidate cards (padanan preview ala 21st.dev) ── --}}
                    <template x-if="msg.role === 'assistant' && msg.type === 'candidates'">
                        <div class="flex items-start gap-2.5 max-w-full" x-data="{ selectedIds: msg.data.candidates.map(c => c.id) }">
                            <div class="w-8 h-8 rounded-full bg-white dark:bg-slate-800 border border-sky-200/90 dark:border-slate-700 p-0.5 flex items-center justify-center shrink-0 mt-0.5 shadow-2xs overflow-hidden">
                                <img src="{{ asset('aset/sikader.jpg') }}" alt="SiKader" class="w-full h-full object-contain rounded-full">
                            </div>
                            <div class="flex-1 min-w-0 space-y-2.5">
                                <div class="glass-card rounded-2xl overflow-hidden shadow-xs border border-slate-200/90 dark:border-slate-800">
                                    <div class="px-3.5 py-2.5 bg-sky-50/90 dark:bg-sky-950/50 border-b border-sky-100 dark:border-sky-900 flex items-center justify-between">
                                        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                            <input type="checkbox"
                                                :checked="selectedIds.length === msg.data.candidates.length"
                                                @change="selectedIds = (selectedIds.length === msg.data.candidates.length ? [] : msg.data.candidates.map(c => c.id))"
                                                class="w-4 h-4 rounded border-slate-300 text-[#005c8a] focus:ring-[var(--accent)]">
                                            <span class="text-[11px] font-bold text-[#005c8a] dark:text-sky-300">Pilih Semua Rekomendasi</span>
                                        </label>
                                        <span class="text-[10px] text-sky-700 dark:text-sky-400 font-semibold">
                                            <span x-text="selectedIds.length"></span> dipilih
                                        </span>
                                    </div>
                                    <div class="max-h-60 overflow-y-auto overscroll-contain divide-y divide-slate-100 dark:divide-slate-800/80 p-1">
                                        <template x-for="c in msg.data.candidates" :key="c.id">
                                            <div class="glass-card my-1 rounded-xl p-2.5 flex items-center justify-between gap-2.5">
                                                <label class="flex items-center gap-2.5 min-w-0 flex-1 cursor-pointer select-none">
                                                    <input type="checkbox" :value="c.id" x-model.number="selectedIds"
                                                        class="w-4 h-4 rounded border-slate-300 text-[#005c8a] focus:ring-[var(--accent)] shrink-0">
                                                    <div class="w-8 h-10 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 shrink-0 bg-slate-100 shadow-2xs">
                                                        <img :src="'/foto/anggota/' + c.id + '?v=2'" :alt="c.nama_lengkap" class="w-full h-full object-cover">
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <div class="flex items-center gap-1.5 flex-wrap">
                                                            <p class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="c.nama_lengkap"></p>
                                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold bg-amber-100 text-amber-900 dark:bg-amber-950/80 dark:text-amber-300"
                                                                  x-text="c.match_score || (c.event_peserta_count > 0 ? c.event_peserta_count + 'x BA' : 'Kandidat')"></span>
                                                        </div>
                                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                                            NIK: <span class="font-mono" x-text="maskNik(c.nik)"></span> &bull; PCM <span x-text="c.pcm || c.kecamatan || '-'"></span>
                                                            <template x-if="c.nama_aum"> &bull; <span x-text="c.nama_aum"></span></template>
                                                        </p>
                                                    </div>
                                                </label>
                                                <button type="button"
                                                        @click="requestEnrollmentConfirmation([c.id], c.nama_lengkap)"
                                                        title="Daftarkan kader ini sekarang"
                                                        class="shrink-0 px-3 py-1.5 rounded-lg text-[10px] font-bold bg-[#005c8a] text-white hover:bg-[#00486d] active:scale-95 transition cursor-pointer shadow-2xs">
                                                    Daftarkan
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <button type="button"
                                        @click="requestEnrollmentConfirmation(selectedIds)"
                                        :disabled="executing || selectedIds.length === 0"
                                        class="w-full py-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs cursor-pointer"
                                        :class="selectedIds.length === 0 ? 'bg-slate-200 dark:bg-slate-800 text-slate-400 dark:text-slate-600 cursor-not-allowed' : 'bg-[#005c8a] hover:bg-[#00486d] text-white'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                    </svg>
                                    <span>Enroll <span x-text="selectedIds.length"></span> Kader Terpilih</span>
                                </button>
                            </div>
                        </div>
                    </template>

                    {{-- ── ASSISTANT: confirmation card ── --}}
                    <template x-if="msg.role === 'assistant' && msg.type === 'confirm'">
                        <div class="flex items-start gap-2.5 max-w-[95%] sm:max-w-[88%]">
                            <div class="w-8 h-8 rounded-full bg-white dark:bg-slate-800 border border-amber-300 p-0.5 flex items-center justify-center shrink-0 mt-0.5 overflow-hidden">
                                <img src="{{ asset('aset/sikader.jpg') }}" alt="SiKader" class="w-full h-full object-contain rounded-full">
                            </div>
                            <div class="flex-1 rounded-2xl rounded-tl-xs border border-amber-300/80 bg-amber-50/90 dark:bg-amber-950/40 p-3.5 text-xs text-slate-800 dark:text-slate-100 shadow-sm glass-card">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <span class="w-2 h-2 rounded-full bg-[var(--accent)] animate-ping"></span>
                                    <p class="font-bold text-slate-900 dark:text-white" x-html="msg.data.promptHtml || UI.confirmation.bulkEnrollPrompt(msg.data.ids.length)"></p>
                                </div>
                                <p class="text-[11px] text-slate-600 dark:text-slate-400 mb-3">Peserta akan terdaftar resmi pada kegiatan ini.</p>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="messages = messages.filter(item => item.id !== msg.id)"
                                            class="min-h-10 px-3.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 cursor-pointer">
                                        <span x-text="UI.confirmation.cancelBtn">Batal</span>
                                    </button>
                                    <button type="button" @click="executeEnroll(msg.data.ids); messages = messages.filter(item => item.id !== msg.id)"
                                            class="min-h-10 px-4 py-1.5 rounded-lg bg-[var(--accent)] hover:bg-[#e0a700] text-[#003855] font-black shadow-xs cursor-pointer active:scale-95 transition">
                                        <span x-text="UI.confirmation.confirmBtn">Konfirmasi Daftarkan</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- ── ASSISTANT: upload summary ── --}}
                    <template x-if="msg.role === 'assistant' && msg.type === 'upload-summary'">
                        <div class="flex items-start gap-2.5 max-w-[95%] sm:max-w-[88%]">
                            <div class="w-8 h-8 rounded-full bg-white dark:bg-slate-800 border border-sky-200 p-0.5 flex items-center justify-center shrink-0 mt-0.5 overflow-hidden">
                                <img src="{{ asset('aset/sikader.jpg') }}" alt="SiKader" class="w-full h-full object-contain rounded-full">
                            </div>
                            <div class="flex-1 rounded-2xl rounded-tl-xs border border-slate-200 dark:border-slate-700 p-3.5 text-xs text-slate-800 dark:text-slate-100 glass-card">
                                <p class="font-bold text-slate-900 dark:text-white">Ringkasan pra-proses spreadsheet</p>
                                <p class="mt-1 text-slate-600 dark:text-slate-300"><span x-text="msg.data.total_rows"></span> baris · <span x-text="msg.data.duplicate_rows"></span> duplikat · <span x-text="msg.data.invalid_nik_rows"></span> NIK tidak valid</p>
                                <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">Data belum dicocokkan atau didaftarkan.</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <button type="button" @click="continueUploadMatch()" class="min-h-10 rounded-lg bg-[var(--accent)] hover:bg-[#e0a700] px-3.5 py-1.5 text-xs font-bold text-[#003d5c] cursor-pointer">Lanjutkan pencocokan</button>
                                    <button type="button" @click="cancelUploadPreview()" class="min-h-10 rounded-lg border border-slate-300 dark:border-slate-700 px-3.5 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 cursor-pointer">Batalkan</button>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- ── ASSISTANT: success confirmation ── --}}
                    <template x-if="msg.role === 'assistant' && msg.type === 'success'">
                        <div class="flex items-start gap-2.5 max-w-[88%]">
                            <div class="relative shrink-0 mt-0.5">
                                <div class="w-8 h-8 rounded-full bg-white border border-emerald-300 p-0.5 flex items-center justify-center shadow-2xs overflow-hidden">
                                    <img src="{{ asset('aset/sikader.jpg') }}" alt="SiKader" class="w-full h-full object-contain rounded-full">
                                </div>
                                <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[8px] font-black ring-1 ring-white">✓</span>
                            </div>
                            <div class="bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 rounded-2xl rounded-tl-sm px-4 py-3 text-xs space-y-2.5 shadow-xs glass-card">
                                <p class="font-bold text-emerald-900 dark:text-emerald-200" x-text="msg.content"></p>
                                <template x-if="msg.data?.undo_token">
                                    <button type="button" @click="undoEnrollment(msg.data.undo_token)" class="min-h-10 w-full rounded-xl border border-emerald-300 bg-white dark:bg-slate-800 px-3 text-xs font-bold text-emerald-800 dark:text-emerald-200 hover:bg-emerald-100 cursor-pointer">
                                        Batalkan pendaftaran (10 detik)
                                    </button>
                                </template>
                                <button type="button" @click="close(); setTimeout(() => window.location.reload(), 300)"
                                        class="w-full py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition cursor-pointer shadow-2xs">
                                    ✓ Tutup & Perbarui Halaman
                                </button>
                            </div>
                        </div>
                    </template>

                    {{-- ── USER: text bubble ── --}}
                    <template x-if="msg.role === 'user'">
                        <div class="flex justify-end pl-10">
                            <div class="bg-[#005c8a] text-white rounded-2xl rounded-tr-sm px-4 py-2.5 text-xs max-w-[85%] leading-relaxed shadow-xs" x-html="msg.content"></div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- ── Quick Action Cards (Empty State / Percakapan Kosong) ── --}}
            <div x-show="!hasUserMessage" class="sm:pl-10.5 pl-0 pt-0.5 space-y-1.5">
                <div class="grid grid-cols-2 gap-2.5 max-[399px]:grid-cols-1">
                    @php
                        $initialActionCards = [
                            ['action' => 'paste', 'label' => 'Tempel daftar nama', 'tooltip' => 'Salin tempel daftar nama kader dari grup WhatsApp atau dokumen'],
                            ['action' => 'file', 'label' => 'Unggah Excel / CSV', 'tooltip' => 'Unggah spreadsheet Excel atau CSV untuk pencocokan massal'],
                            ['action' => 'recommend_balanced', 'label' => 'Rekomendasi seimbang', 'tooltip' => 'Rekomendasi kader komposisi seimbang 50% putra dan 50% putri'],
                            ['action' => 'recommend_ba', 'label' => 'Prioritaskan belum aktif', 'tooltip' => 'Prioritaskan kader yang belum pernah mengikuti perkaderan Baitul Arqam'],
                            ['action' => 'recommend_aum', 'label' => 'Prioritaskan kader AUM', 'tooltip' => 'Rekomendasi kader dari unit Amal Usaha Muhammadiyah (RS PKU, Sekolah, dll)'],
                            ['action' => 'recommend_pcm', 'label' => 'Rekomendasi per Cabang', 'tooltip' => 'Rekomendasi kader perwakilan Cabang (PCM) se-Karanganyar'],
                        ];
                    @endphp
                    @foreach ($initialActionCards as $card)
                        <button type="button"
                                @click="handleChip('{{ $card['action'] }}')"
                                title="{{ $card['tooltip'] }}"
                                aria-label="{{ $card['label'] }}"
                                class="glass-card flex items-center gap-2.5 px-3.5 py-2 h-[46px] rounded-xl text-slate-800 dark:text-[#E6EDF3] text-xs font-semibold cursor-pointer group text-left select-none active:scale-[0.98] transition">
                            <span class="w-7 h-7 rounded-lg bg-sky-500/10 border border-sky-500/20 text-[#005c8a] dark:text-sky-300 group-hover:bg-[#005c8a] group-hover:text-white dark:group-hover:bg-sky-500 dark:group-hover:text-white flex items-center justify-center shrink-0 transition-all shadow-2xs">
                                @if ($card['action'] === 'paste')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <rect width="8" height="4" x="8" y="2" rx="1" ry="1"/>
                                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                                        <path d="M12 11h4"/>
                                        <path d="M12 16h4"/>
                                        <path d="M8 11h.01"/>
                                        <path d="M8 16h.01"/>
                                    </svg>
                                @elseif ($card['action'] === 'file')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/>
                                        <path d="M14 2v4a2 2 0 0 0 2 2h4"/>
                                        <path d="M8 13h2"/>
                                        <path d="M14 13h2"/>
                                        <path d="M8 17h2"/>
                                        <path d="M14 17h2"/>
                                    </svg>
                                @elseif ($card['action'] === 'recommend_balanced')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/>
                                        <path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/>
                                        <path d="M7 21h10"/>
                                        <path d="M12 3v18"/>
                                        <path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"/>
                                    </svg>
                                @elseif ($card['action'] === 'recommend_ba')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="12" cy="12" r="10"/>
                                        <circle cx="12" cy="12" r="6"/>
                                        <circle cx="12" cy="12" r="2"/>
                                    </svg>
                                @elseif ($card['action'] === 'recommend_aum')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/>
                                        <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/>
                                        <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/>
                                        <path d="M10 6h4"/>
                                        <path d="M10 10h4"/>
                                        <path d="M10 14h4"/>
                                        <path d="M10 18h4"/>
                                    </svg>
                                @elseif ($card['action'] === 'recommend_pcm')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                @endif
                            </span>
                            <span class="flex-1 line-clamp-2 leading-tight text-xs font-semibold group-hover:text-[#005c8a] dark:group-hover:text-sky-300" title="{{ $card['label'] }}">{{ $card['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── Dock Footer Area: Quick Action Chips + Glass Prompt Composer ── --}}
        <div class="border-t border-slate-200/90 dark:border-slate-800 bg-white/95 dark:bg-[#0b141f]/95 px-3 sm:px-4 pt-2.5 pb-2.5 shrink-0 shadow-lg z-20 backdrop-blur-md">
            
            @php
                $quickActionPills = [
                    ['action' => 'paste', 'label' => 'Tempel daftar nama', 'tooltip' => 'Salin tempel daftar nama kader'],
                    ['action' => 'file', 'label' => 'Unggah Excel / CSV', 'tooltip' => 'Unggah file spreadsheet'],
                    ['action' => 'recommend_balanced', 'label' => 'Rekomendasi seimbang', 'tooltip' => 'Rekomendasi kader 50% putra dan 50% putri'],
                    ['action' => 'recommend_ba', 'label' => 'Prioritaskan belum aktif', 'tooltip' => 'Prioritaskan kader belum pernah Baitul Arqam'],
                    ['action' => 'recommend_aum', 'label' => 'Prioritaskan kader AUM', 'tooltip' => 'Rekomendasi kader dari unit AUM'],
                    ['action' => 'recommend_pcm', 'label' => 'Rekomendasi per Cabang', 'tooltip' => 'Rekomendasi kader perwakilan PCM'],
                ];
            @endphp

            {{-- Quick action pills moved above composer: ONLY visible AFTER first user message (pola satu quick action) --}}
            <div x-show="hasUserMessage" x-cloak class="flex items-center gap-1.5 overflow-x-auto pb-2 sikader-chips-scroll" role="toolbar" aria-label="Aksi cepat SiKader">
                @foreach ($quickActionPills as $chip)
                    <button type="button"
                            @click="handleChip('{{ $chip['action'] }}')"
                            title="{{ $chip['tooltip'] }}"
                            aria-label="{{ $chip['label'] }}"
                            class="glass-card shrink-0 flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold text-slate-700 dark:text-[#E6EDF3] hover:text-[#005c8a] dark:hover:text-amber-400 cursor-pointer select-none border border-slate-200/90 dark:border-white/10 active:scale-95 transition">
                        <span class="w-1.5 h-1.5 rounded-full bg-[var(--accent)]"></span>
                        <span>{{ $chip['label'] }}</span>
                    </button>
                @endforeach
            </div>

            {{-- Pending file indicator --}}
            <template x-if="pendingFileName">
                <div class="flex items-center gap-2 mb-2 px-1">
                    <div class="flex items-center gap-1.5 flex-1 min-w-0 bg-sky-50 dark:bg-sky-950/60 border border-sky-200 dark:border-sky-800 rounded-lg px-2.5 py-1">
                        <svg class="w-3.5 h-3.5 text-[#005c8a] dark:text-sky-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                        <span class="text-[11px] font-semibold text-[#005c8a] dark:text-sky-300 truncate" x-text="pendingFileName"></span>
                    </div>
                    <button type="button" @click="pendingFile=null; pendingFileName=''; fileInputKey++"
                            class="text-slate-400 hover:text-rose-500 transition shrink-0 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>

            {{-- ── 21st.dev Glass Prompt Composer ── --}}
            <div class="glass-composer rounded-2xl p-2 transition-all">
                {{-- Textarea (auto-grow up to 5 lines) --}}
                <textarea
                    x-ref="chatInput"
                    x-model="inputText"
                    @keydown="handleKeydown($event)"
                    :disabled="isLoading"
                    rows="1"
                    :placeholder="UI?.composer?.placeholder || 'Tulis instruksi atau tempel daftar nama...'"
                    class="chat-seamless-input w-full resize-none border-0 border-none ring-0 ring-offset-0 focus:ring-0 focus:ring-offset-0 focus:border-0 focus:border-transparent focus:outline-none shadow-none focus:shadow-none bg-transparent px-2.5 py-1 text-xs text-slate-800 dark:text-[#E6EDF3] outline-none transition placeholder:text-slate-400 dark:placeholder:text-slate-500 max-h-32 overflow-y-auto overscroll-contain leading-snug"
                    style="border: 0 !important; border-width: 0 !important; outline: none !important; box-shadow: none !important; background: transparent !important; min-height: 2rem; height: auto;"
                    @input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 120) + 'px'">
                </textarea>

                {{-- Action Row Inside Composer --}}
                <div class="flex items-center justify-between pt-1 border-t border-slate-200/50 dark:border-white/10 mt-0.5">
                    {{-- Left action buttons: File, Paste, Excel --}}
                    <div class="flex items-center gap-1 text-slate-500 dark:text-slate-400">
                        {{-- Hidden File Input --}}
                        <input type="file" :key="fileInputKey" @change="handleFileSelect" accept=".xlsx,.xls,.csv,.txt"
                               class="hidden" x-ref="fileInput">

                        {{-- Button 1: Lampirkan Berkas --}}
                        <button type="button"
                                @click="$refs.fileInput.click()"
                                :aria-label="UI?.composer?.attachFileAria || 'Unggah berkas Excel atau CSV'"
                                :title="UI?.composer?.attachFileTitle || 'Unggah berkas (.xlsx, .xls, .csv, .txt)'"
                                class="p-1.5 rounded-lg hover:bg-slate-200/60 dark:hover:bg-white/10 hover:text-[#005c8a] dark:hover:text-amber-400 transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                            </svg>
                        </button>

                        {{-- Button 2: Tempel Daftar Nama --}}
                        <button type="button"
                                @click="handleChip('paste')"
                                :aria-label="UI?.composer?.pasteActionAria || 'Tempel daftar nama kader'"
                                :title="UI?.composer?.pasteActionTitle || 'Tempel daftar nama (WhatsApp/Teks)'"
                                class="p-1.5 rounded-lg hover:bg-slate-200/60 dark:hover:bg-white/10 hover:text-[#005c8a] dark:hover:text-amber-400 transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                <rect width="8" height="4" x="8" y="2" rx="1" ry="1"/>
                                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                                <path d="M12 11h4"/>
                                <path d="M12 16h4"/>
                                <path d="M8 11h.01"/>
                                <path d="M8 16h.01"/>
                            </svg>
                        </button>

                        {{-- Button 3: Unggah Excel / CSV --}}
                        <button type="button"
                                @click="$refs.fileInput.click()"
                                :aria-label="UI?.composer?.uploadActionAria || 'Unggah Excel atau CSV'"
                                :title="UI?.composer?.uploadActionTitle || 'Unggah spreadsheet Excel / CSV'"
                                class="p-1.5 rounded-lg hover:bg-slate-200/60 dark:hover:bg-white/10 hover:text-[#005c8a] dark:hover:text-amber-400 transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/>
                                <path d="M14 2v4a2 2 0 0 0 2 2h4"/>
                                <path d="M8 13h2"/>
                                <path d="M14 13h2"/>
                                <path d="M8 17h2"/>
                                <path d="M14 17h2"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Right: Send Button inside Composer --}}
                    <button type="button"
                            @click="sendComposer()"
                            aria-label="Kirim pesan"
                            title="Kirim pesan"
                            :disabled="isLoading || (!inputText.trim() && !pendingFile)"
                            class="min-h-11 min-w-11 px-3 py-1.5 rounded-xl transition-all duration-150 flex items-center justify-center shadow-2xs active:scale-95 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--accent)]"
                            :class="(isLoading || (!inputText.trim() && !pendingFile))
                                ? 'bg-slate-100 dark:bg-white/[0.05] text-slate-400 dark:text-slate-600 opacity-50 cursor-not-allowed'
                                : 'bg-gradient-to-r from-[#005c8a] to-[#0284c7] hover:from-[#004e75] hover:to-[#0369a1] text-white font-bold cursor-pointer shadow-md shadow-sky-900/20'">
                        <template x-if="isLoading">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </template>
                        <template x-if="!isLoading">
                            <svg class="w-4 h-4 translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m22 2-7 20-4-9-9-4Z"/>
                                <path d="M22 2 11 13"/>
                            </svg>
                        </template>
                    </button>
                </div>

                {{-- Keyboard hint inside composer container --}}
                <p class="text-center text-[11px] text-slate-500 dark:text-slate-400 mt-1.5 font-medium tracking-tight select-none" x-text="UI?.composer?.keyboardHint || 'Enter kirim • Shift+Enter baris baru'">
                    Enter kirim • Shift+Enter baris baru
                </p>
            </div>
        </div>

        {{-- ── Sliding Preview Panel (Padanan Preview Sumber 21st.dev) ── --}}
        <div x-show="previewDrawerOpen"
             x-cloak
             x-transition:enter="transition transform ease-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition transform ease-in duration-200"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="absolute inset-y-0 right-0 w-full sm:w-80 md:w-96 bg-white/95 dark:bg-[#12202d]/95 backdrop-blur-md border-l border-slate-200 dark:border-slate-700 shadow-2xl z-50 flex flex-col p-4 overflow-y-auto">
            
            {{-- Panel Header --}}
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-700 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[var(--accent)]"></span>
                    <h4 class="font-bold text-sm text-slate-800 dark:text-white" x-text="UI.previewPanel.title">Rujukan Dokumen Sumber</h4>
                </div>
                <button type="button"
                        @click="previewDrawerOpen = false"
                        :aria-label="UI.previewPanel.closeAria"
                        title="Tutup panel"
                        class="p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Source Content --}}
            <div class="flex-1 py-4 space-y-4">
                <template x-if="activePreviewSource">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-[#005c8a] text-white">
                                Sitasi [<span x-text="activePreviewSource.id"></span>]
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800"
                                  x-text="activePreviewSource.score >= 0.55 ? UI.previewPanel.confidenceGrounded : UI.previewPanel.confidenceLow"></span>
                        </div>

                        <div>
                            <h5 class="font-black text-sm text-slate-900 dark:text-white" x-text="activePreviewSource.title"></h5>
                            <span class="inline-block mt-1 px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] font-mono font-semibold"
                                  x-text="activePreviewSource.badge"></span>
                        </div>

                        <div class="p-3.5 rounded-xl bg-amber-50/50 dark:bg-slate-900/60 border border-amber-200/80 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-200 leading-relaxed font-sans">
                            <p class="font-semibold text-slate-500 dark:text-slate-400 text-[10px] uppercase tracking-wider mb-1.5">Cuplikan Teks Terverifikasi:</p>
                            <div class="bg-amber-100/60 dark:bg-amber-950/40 p-2 rounded-lg border-l-2 border-[var(--accent)] font-medium text-slate-900 dark:text-amber-100"
                                 x-text="activePreviewSource.snippet"></div>
                        </div>

                        {{-- Other sources in this message --}}
                        <template x-if="activePreviewList && activePreviewList.length > 1">
                            <div class="pt-2">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Sitasi Lainnya:</p>
                                <div class="flex flex-wrap gap-1.5">
                                    <template x-for="s in activePreviewList" :key="s.id">
                                        <button type="button"
                                                @click="activePreviewSource = s"
                                                class="px-2.5 py-1 rounded-lg text-xs font-bold border transition cursor-pointer"
                                                :class="activePreviewSource.id === s.id ? 'bg-[#005c8a] text-white border-[#005c8a]' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700'">
                                            [<span x-text="s.id"></span>] <span class="truncate max-w-[120px]" x-text="s.title"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="!activePreviewSource">
                    <div class="text-center py-8 text-slate-400 text-xs">
                        <p x-text="UI.previewPanel.emptyNotice"></p>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
window.smartEnrollChatbot = function(config) {
    const UI = window.SiKaderUI || {
        identity: { name: 'SiKader', badge: 'Aktif', subtitle: 'Asisten Perkaderan MPKSDI', ariaChatDialog: 'Percakapan SiKader' },
        greeting: { intro: (title) => `Assalamu'alaikum, Admin. Siap membantu kegiatan <strong>${title}</strong>.\nMau mulai dari mana?` },
        composer: { placeholder: 'Tulis instruksi atau tempel daftar nama...', keyboardHint: 'Enter kirim • Shift+Enter baris baru', sendAria: 'Kirim pesan', sendTitle: 'Kirim pesan', attachFileAria: 'Unggah berkas Excel atau CSV', attachFileTitle: 'Unggah berkas (.xlsx, .xls, .csv, .txt)', pasteActionAria: 'Tempel daftar nama kader', pasteActionTitle: 'Tempel daftar nama (WhatsApp/Teks)', uploadActionAria: 'Unggah Excel atau CSV', uploadActionTitle: 'Unggah spreadsheet Excel / CSV' },
        quickActions: [
            { action: 'paste', label: 'Tempel daftar nama', tooltip: 'Salin tempel daftar nama kader' },
            { action: 'file', label: 'Unggah Excel / CSV', tooltip: 'Unggah file spreadsheet' },
            { action: 'recommend_balanced', label: 'Rekomendasi seimbang', tooltip: 'Rekomendasi kader 50% putra dan 50% putri' },
            { action: 'recommend_ba', label: 'Prioritaskan belum aktif', tooltip: 'Prioritaskan kader belum pernah Baitul Arqam' },
            { action: 'recommend_aum', label: 'Prioritaskan kader AUM', tooltip: 'Rekomendasi kader dari unit AUM' }
        ],
        buildLog: {
            title: 'Proses RAG',
            stepsDefault: ['Membaca dokumen dan kurikulum perkaderan', 'Mencocokkan dengan database kader aktif', 'Menyusun rekomendasi berbasis data riil'],
            stepsPaste: ['Membaca dan membersihkan daftar nama masukan', 'Mencocokkan nama dengan basis data master anggota', 'Memvalidasi status pendaftaran kegiatan'],
            stepsFile: ['Membaca berkas spreadsheet Excel/CSV', 'Mengekstrak baris data dan nomor identitas', 'Memverifikasi kecocokan data peserta'],
            stepsRecommend: ['Menyaring pangkalan data kader aktif', 'Menganalisis riwayat keikutsertaan BA & profil', 'Menyusun peringkat rekomendasi terbaik'],
            toggleShow: (count) => `Lihat proses (${count} langkah)`,
            toggleHide: (count) => `Sembunyikan proses (${count} langkah)`,
            statusLiveAria: 'Status pemrosesan SiKader'
        },
        previewPanel: { title: 'Rujukan Dokumen Sumber', closeAria: 'Tutup panel rujukan', confidenceGrounded: 'RAG Terverifikasi', confidenceLow: 'Perlu Konfirmasi', uncertainNotice: 'Saya kurang yakin dengan informasi ini karena berada di luar basis data resmi MPKSDI.', emptyNotice: 'Pilih nomor sitasi untuk melihat rujukan.' },
        confirmation: { singleEnrollPrompt: (n) => `Daftarkan <strong>${n}</strong> ke kegiatan ini?`, bulkEnrollPrompt: (c) => `Anda akan mendaftarkan <strong>${c} kader</strong>. Lanjutkan?`, confirmBtn: 'Konfirmasi Daftarkan', cancelBtn: 'Batal' },
        settings: { menuAria: 'Menu pengaturan', menuTitle: 'Pengaturan SiKader', textSize: 'Ukuran teks', themeToggle: 'Tema tampilan', resetChat: 'Mulai ulang chat', closeModal: 'Tutup SiKader' }
    };

    return {
        UI,
        open: false,
        menuOpen: false,
        previewDrawerOpen: false,
        activePreviewSource: null,
        activePreviewList: [],
        messages: [],
        inputText: '',
        pendingFile: null,
        pendingFileName: '',
        isLoading: false,
        executing: false,
        feedbackSending: false,
        copiedWa: false,
        fileInputKey: 0,
        pageScrollLock: null,
        thinkingIntervals: {},
        theme: 'light',
        fontSize: 'm',
        quota: Number(config.quota || 0),
        registeredCount: Number(config.registeredCount || 0),
        historyKey: `sikader-chat-v2:${config.eventId}`,

        get hasUserMessage() {
            return this.messages.some(m => m.role === 'user');
        },

        init() {
            this.theme = 'light';
            this.fontSize = localStorage.getItem('sikader-font-size') || 'm';
            this.restoreHistory();
            this.$watch('fontSize', (size) => localStorage.setItem('sikader-font-size', size));
        },

        toggleTheme() {
            this.theme = this.theme === 'dark' ? 'light' : 'dark';
        },

        cycleFontSize() {
            const sizes = ['s', 'm', 'l'];
            this.fontSize = sizes[(sizes.indexOf(this.fontSize) + 1) % sizes.length];
        },

        restoreHistory() {
            try {
                sessionStorage.removeItem(`sikader-history:${config.eventId}`);
                const history = JSON.parse(sessionStorage.getItem(this.historyKey) || '[]');
                if (Array.isArray(history) && history.some(m => m.role === 'user')) {
                    this.messages = history.filter(m => m.type !== 'chips');
                } else {
                    sessionStorage.removeItem(this.historyKey);
                    this.messages = [];
                }
            } catch (_) {
                sessionStorage.removeItem(this.historyKey);
                this.messages = [];
            }
        },

        persistHistory() {
            clearTimeout(this._persistTimer);
            this._persistTimer = setTimeout(() => {
                const history = this.messages.filter((message) => message.type !== 'thinking');
                sessionStorage.setItem(this.historyKey, JSON.stringify(history));
            }, 250);
        },

        openModal() {
            this.lockPageScroll();
            this.open = true;
            if (this.messages.length === 0) {
                this.greet();
            }
        },

        close() {
            this.menuOpen = false;
            this.previewDrawerOpen = false;
            this.open = false;
            this.unlockPageScroll();
        },

        lockPageScroll() {
            if (this.pageScrollLock) return;
            this.pageScrollLock = {
                bodyOverflow: document.body.style.overflow,
                rootOverflow: document.documentElement.style.overflow,
            };
            document.body.style.overflow = 'hidden';
            document.documentElement.style.overflow = 'hidden';
        },

        unlockPageScroll() {
            if (!this.pageScrollLock) return;
            document.body.style.overflow = this.pageScrollLock.bodyOverflow;
            document.documentElement.style.overflow = this.pageScrollLock.rootOverflow;
            this.pageScrollLock = null;
        },

        reset() {
            this.menuOpen = false;
            this.previewDrawerOpen = false;
            this.messages = [];
            sessionStorage.removeItem(this.historyKey);
            this.inputText = '';
            this.pendingFile = null;
            this.pendingFileName = '';
            this.isLoading = false;
            this.executing = false;
            this.copiedWa = false;
            this.fileInputKey++;

            this.greet();
        },

        scrollToBottom(smooth = false) {
            const el = this.$refs.chatBody;
            if (!el) return;
            if (smooth) {
                el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' });
            } else {
                el.scrollTop = el.scrollHeight;
            }
        },

        pushMsg(role, type, content, data = null) {
            this.messages.push({ role, type, content, data, id: Date.now() + Math.random() });
            this.$nextTick(() => {
                this.scrollToBottom(true);
                this.persistHistory();
            });
        },

        maskNik(nik) {
            if (!nik) return '331392****';
            const s = String(nik).trim();
            if (s.length <= 6) return s + '****';
            return s.substring(0, 6) + '****';
        },

        openPreviewFromId(citeId, sources) {
            if (!sources || sources.length === 0) return;
            const src = sources.find(s => s.id === citeId || Number(s.id) === Number(citeId));
            if (src) {
                this.openPreviewSource(src, sources);
            }
        },

        openPreviewSource(src, allSources = []) {
            this.activePreviewSource = src;
            this.activePreviewList = allSources.length > 0 ? allSources : [src];
            this.previewDrawerOpen = true;
        },

        showRetrievalStatus(promptText = '') {
            const id = 'retrieve-' + Date.now() + Math.random();
            const steps = UI.buildLog.stepsDefault;
            const entry = {
                role: 'assistant',
                type: 'thinking',
                content: steps[0],
                data: {
                    steps: steps,
                    currentStep: 0,
                },
                id
            };
            this.messages.push(entry);

            let current = 0;
            const interval = setInterval(() => {
                current++;
                if (current < steps.length) {
                    const message = this.messages.find((item) => item.id === id);
                    if (message && message.data) {
                        message.data.currentStep = current;
                        message.content = steps[current];
                    }
                }
            }, 800);

            this.thinkingIntervals[id] = interval;
            return id;
        },

        streamAssistantMessage(message) {
            const reducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
            const stream = window.SiKaderAdapter?.createPresentationEvents(message, { reducedMotion }) || [];
            const token = stream.find((event) => event.type === 'token')?.payload || message.content;
            const messageId = Date.now() + Math.random();
            const entry = {
                role: 'assistant',
                type: 'text',
                content: '',
                data: {
                    ...message,
                    action_buttons: message.action_buttons || message.actions || [],
                    buildLog: message.buildLog || [
                        { text: UI.buildLog.stepsDefault[0], status: 'done' },
                        { text: UI.buildLog.stepsDefault[1], status: 'done' },
                        { text: UI.buildLog.stepsDefault[2], status: 'done' },
                    ],
                    logCompleted: true,
                    logExpanded: false,
                },
                id: messageId
            };
            this.messages.push(entry);

            if (reducedMotion) {
                entry.content = token;
                return;
            }

            const words = String(token).match(/\S+\s*/g) || [];
            let index = 0;
            const chunkSize = 2;
            const reveal = () => {
                for (let c = 0; c < chunkSize && index < words.length; c++) {
                    entry.content += words[index++];
                }
                const el = this.$refs.chatBody;
                if (el) {
                    el.scrollTop = el.scrollHeight;
                }
                if (index < words.length) {
                    window.setTimeout(reveal, 22);
                } else {
                    this.persistHistory();
                    this.$nextTick(() => this.scrollToBottom(false));
                }
            };
            reveal();
        },

        sendComposer() {
            this.sendMessage();
        },

        runSlashCommand(cmd) {
            this.inputText = '';
            if (this.$refs.chatInput) {
                this.$refs.chatInput.style.height = 'auto';
            }
            this.pushMsg('user', 'text', cmd);
            this.submitAiPrompt(cmd);
        },

        greet() {
            this.pushMsg('assistant', 'text',
                UI.greeting.intro(config.eventTitle)
            );
        },

        handleChip(action) {
            if (action === 'paste') {
                this.pushMsg('user', 'text', 'Tempel daftar nama');
                this.pushMsg('assistant', 'text', 'Silakan ketik atau tempel daftar nama kader di kolom composer di bawah, lalu tekan tombol kirim. Satu nama per baris ya. Gelar akademik, keagamaan, dan nomor urut akan dibersihkan otomatis.');
                this.$nextTick(() => this.$refs.chatInput?.focus());
            } else if (action === 'file') {
                this.pushMsg('user', 'text', 'Unggah Excel / CSV');
                this.pushMsg('assistant', 'text', 'Silakan pilih berkas spreadsheet Anda (.xlsx, .xls, .csv) dengan menekan tombol klip berkas di composer.');
                this.$nextTick(() => this.$refs.fileInput?.click());
            } else if (action === 'recommend_balanced') {
                this.pushMsg('user', 'text', 'Rekomendasi seimbang');
                this.submitRecommend({ limit: 20, gender: 'seimbang' });
            } else if (action === 'recommend_ba') {
                this.pushMsg('user', 'text', 'Prioritaskan belum aktif');
                this.submitRecommend({ limit: 20, prioritas_belum_ba: true });
            } else if (action === 'recommend_aum') {
                this.pushMsg('user', 'text', 'Prioritaskan kader AUM');
                this.pushMsg('assistant', 'text', 'Unit AUM mana yang ingin diprioritaskan? Ketik misalnya <em>"Kader dari RS PKU"</em> atau <em>"Guru dari sekolah"</em> di kolom obrolan di bawah.');
                this.$nextTick(() => this.$refs.chatInput?.focus());
            } else if (action === 'recommend_pcm') {
                this.pushMsg('user', 'text', 'Rekomendasi per Cabang');
                this.pushMsg('assistant', 'text', 'Cabang (PCM) mana yang ingin diprioritaskan? Ketik misalnya <em>"Kader PCM Colomadu"</em> atau <em>"Kader PCM Karanganyar"</em> di kolom obrolan di bawah.');
                this.$nextTick(() => this.$refs.chatInput?.focus());
            } else if (action === 'analytics') {
                this.runSlashCommand('/rekap');
            } else if (action === 'undangan_wa') {
                this.runSlashCommand('/undangan');
            }
        },

        handleFileSelect(e) {
            const files = e.target.files;
            if (files && files.length > 0) {
                this.pendingFile = files[0];
                this.pendingFileName = files[0].name;
                this.previewUpload();
            }
        },

        async previewUpload() {
            if (!this.pendingFile) return;
            this.isLoading = true;
            const formData = new FormData();
            formData.append('file', this.pendingFile);

            try {
                const response = await fetch(config.previewUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Berkas tidak dapat dipra-proses.');
                this.pushMsg('assistant', 'upload-summary', '', data);
            } catch (error) {
                this.pushMsg('assistant', 'text', 'Berkas belum dapat diperiksa: <strong>' + error.message + '</strong>');
            } finally {
                this.isLoading = false;
            }
        },

        continueUploadMatch() {
            if (this.pendingFile) this.submitMatch(null, this.pendingFile);
        },

        cancelUploadPreview() {
            this.pendingFile = null;
            this.pendingFileName = '';
            this.fileInputKey++;
        },

        sendMessage() {
            const text = this.inputText.trim();
            if (!text && !this.pendingFile) return;
            if (this.isLoading) return;

            if (this.pendingFile) {
                this.pushMsg('assistant', 'text', 'Periksa ringkasan pra-proses, lalu pilih <strong>Lanjutkan pencocokan</strong>.');
                return;
            }

            if (text) {
                this.pushMsg('user', 'text', text.replace(/</g, '&lt;').replace(/>/g, '&gt;'));
                this.inputText = '';
                if (this.$refs.chatInput) {
                    this.$refs.chatInput.style.height = 'auto';
                }

                const lines = text.split('\n').map(l => l.trim()).filter(l => l.length > 0);
                const isNumberedList = /^\s*(\d+[\.\)]|\-|\*|•|[a-zA-Z][\.\)])\s+/m.test(text);

                if (lines.length >= 2 || (lines.length === 1 && isNumberedList)) {
                    this.submitMatch(text, null);
                } else {
                    this.submitAiPrompt(text);
                }
            }
        },

        async submitMatch(textContent, fileObj) {
            this.isLoading = true;
            const thinkingId = this.showThinkingWithSteps(UI.buildLog.stepsPaste);

            const formData = new FormData();
            if (fileObj) formData.append('file', fileObj);
            if (textContent) formData.append('names_text', textContent);

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            try {
                const res = await fetch(config.matchUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
                const data = await res.json();
                this.removeThinking(thinkingId);
                if (!res.ok) throw new Error(data.message || 'Gagal memproses pencocokan.');

                this.pendingFile = null;
                this.pendingFileName = '';
                this.fileInputKey++;

                const summary = `Selesai! Dari <strong>${data.total_input}</strong> nama yang dianalisis:\n\n` +
                    `✅ <strong>${data.matched.length}</strong> kader siap di-enroll\n` +
                    `📌 <strong>${data.already_enrolled.length}</strong> sudah terdaftar di kegiatan ini\n` +
                    `⚠️ <strong>${data.unmatched.length}</strong> belum punya akun portal`;

                this.pushMsg('assistant', 'text', summary, {
                    buildLog: UI.buildLog.stepsPaste.map(st => ({ text: st, status: 'done' })),
                    logCompleted: true,
                    logExpanded: false,
                });
                this.pushMsg('assistant', 'match-result', '', data);
            } catch (err) {
                this.removeThinking(thinkingId);
                this.pushMsg('assistant', 'text', '❌ Maaf, terjadi kesalahan: <strong>' + err.message + '</strong>');
            } finally {
                this.isLoading = false;
            }
        },

        async submitAiPrompt(prompt) {
            this.isLoading = true;
            const thinkingId = this.showRetrievalStatus(prompt);
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            try {
                const res = await fetch(config.aiPromptUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ prompt }),
                });
                const data = await res.json();
                this.removeThinking(thinkingId);
                if (!res.ok) throw new Error(data.message || 'Gagal memproses pesan.');

                if (data.event_update) {
                    this.pushMsg('assistant', 'text', data.message, data);
                    return;
                }

                const isChat = data.intent === 'chat' || data.is_chat === true;
                const normalized = window.SiKaderAdapter?.normalizeRagResponse(data, { eventTitle: config.eventTitle }) || {
                    content: data.message || data.reply || 'Berikut respons saya.',
                    sources: data.sources || [],
                    notice: isChat ? null : (!data.sources?.length ? (data.notice || UI.previewPanel.uncertainNotice) : null),
                    buildLog: UI.buildLog.stepsDefault.map(s => ({ text: s, status: 'done' })),
                    logCompleted: true,
                    logExpanded: false
                };
                this.streamAssistantMessage(normalized);

                if (data.needs_candidate_card && data.candidates && data.candidates.length > 0) {
                    this.pushMsg('assistant', 'candidates', '', { candidates: data.candidates });
                }
            } catch (err) {
                this.removeThinking(thinkingId);
                this.pushMsg('assistant', 'text', '❌ Maaf, asisten tidak dapat memproses permintaan ini: <strong>' + err.message + '</strong>');
            } finally {
                this.isLoading = false;
            }
        },

        async confirmEventUpdate(token, button) {
            button.disabled = true;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            try {
                const response = await fetch(config.eventUpdateConfirmUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ token }),
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Perubahan gagal disimpan.');
                this.pushMsg('assistant', 'text', data.message);
                this.quota = Number(data.event.kuota || 0);
            } catch (error) {
                button.disabled = false;
                this.pushMsg('assistant', 'text', 'Perubahan belum tersimpan: ' + error.message);
            }
        },

        async submitRecommend(params) {
            this.isLoading = true;
            const thinkingId = this.showThinkingWithSteps(UI.buildLog.stepsRecommend);
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            try {
                const res = await fetch(config.recommendUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(params),
                });
                const data = await res.json();
                this.removeThinking(thinkingId);
                if (!res.ok) throw new Error(data.message || 'Gagal mendapat rekomendasi.');

                if (data.candidates && data.candidates.length > 0) {
                    this.pushMsg('assistant', 'text', `Ini rekomendasi <strong>${data.candidates.length}</strong> kader terbaik yang saya temukan [4]. Centang nama atau tekan <strong>Daftarkan</strong> pada tiap kartu:`, {
                        sources: [
                            { id: 4, title: 'Pangkalan Data Master Anggota', badge: 'Database: master_anggota', snippet: 'Kader aktif yang diprioritaskan belum pernah mengikuti Baitul Arqam.' },
                            { id: 1, title: 'Kapasitas Kuota Kegiatan', badge: 'Database: events', snippet: 'Pengecekan ketersediaan kursi kegiatan saat ini.' }
                        ],
                        buildLog: UI.buildLog.stepsRecommend.map(st => ({ text: st, status: 'done' })),
                        logCompleted: true,
                        logExpanded: false,
                    });
                    this.pushMsg('assistant', 'candidates', '', { candidates: data.candidates });
                } else {
                    this.pushMsg('assistant', 'text', 'Maaf, tidak ada kandidat yang memenuhi kriteria ini saat ini.');
                }
            } catch (err) {
                this.removeThinking(thinkingId);
                this.pushMsg('assistant', 'text', '❌ Kesalahan: <strong>' + err.message + '</strong>');
            } finally {
                this.isLoading = false;
            }
        },

        requestEnrollmentConfirmation(ids, singleName = null) {
            const uniqueIds = [...new Set(ids.map(Number))];
            if (uniqueIds.length === 0) return;
            const promptHtml = singleName ? UI.confirmation.singleEnrollPrompt(singleName) : UI.confirmation.bulkEnrollPrompt(uniqueIds.length);
            this.pushMsg('assistant', 'confirm', '', { ids: uniqueIds, promptHtml });
        },

        async executeEnroll(ids) {
            if (!ids || ids.length === 0) {
                this.pushMsg('assistant', 'text', '⚠️ Belum ada kader yang dipilih. Centang minimal satu nama terlebih dahulu.');
                return;
            }
            this.executing = true;
            this.pushMsg('user', 'text', `🚀 Enroll ${ids.length} kader terpilih`);
            const thinkingId = this.showThinking('Mendaftarkan kader ke kegiatan...');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            try {
                const res = await fetch(config.executeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ anggota_ids: ids }),
                });
                const data = await res.json();
                this.removeThinking(thinkingId);
                if (!res.ok) throw new Error(data.message || 'Gagal mendaftarkan peserta.');

                this.registeredCount = Number(data.registered_count ?? this.registeredCount + data.enrolled_count);
                this.pushMsg('assistant', 'success', data.message || `${ids.length} kader berhasil didaftarkan!`, data);
            } catch (err) {
                this.removeThinking(thinkingId);
                this.pushMsg('assistant', 'text', '❌ Gagal enroll: <strong>' + err.message + '</strong>');
            } finally {
                this.executing = false;
            }
        },

        async undoEnrollment(undoToken) {
            try {
                const response = await fetch(config.undoUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ undo_token: undoToken }),
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Pendaftaran tidak dapat dibatalkan.');
                this.registeredCount = Number(data.registered_count ?? this.registeredCount);
                this.pushMsg('assistant', 'text', data.message || 'Pendaftaran dibatalkan.');
            } catch (error) {
                this.pushMsg('assistant', 'text', 'Pembatalan tidak berhasil: <strong>' + error.message + '</strong>');
            }
        },

        async sendFeedback(message, rating, reason) {
            if (!rating || rating < 1 || rating > 5) return;

            this.feedbackSending = true;
            try {
                const response = await fetch(config.feedbackUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        query: message.content,
                        sources: message.data?.sources || [],
                        rating,
                        reason: reason || null,
                    }),
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Penilaian tidak dapat disimpan.');
                this.pushMsg('assistant', 'text', 'Terima kasih. Penilaian Anda sudah kami catat.');
            } catch (error) {
                this.pushMsg('assistant', 'text', 'Penilaian belum tersimpan: <strong>' + error.message + '</strong>');
            } finally {
                this.feedbackSending = false;
            }
        },

        copyWaInvite(text) {
            navigator.clipboard.writeText(text).then(() => {
                this.copiedWa = true;
                setTimeout(() => { this.copiedWa = false; }, 3000);
            });
        },

        showThinkingWithSteps(steps) {
            const id = 'think-' + Date.now() + Math.random();
            const entry = {
                role: 'assistant',
                type: 'thinking',
                content: steps[0],
                data: {
                    steps: steps,
                    currentStep: 0,
                },
                id
            };
            this.messages.push(entry);

            let current = 0;
            const interval = setInterval(() => {
                current++;
                if (current < steps.length) {
                    const message = this.messages.find((item) => item.id === id);
                    if (message && message.data) {
                        message.data.currentStep = current;
                        message.content = steps[current];
                    }
                }
            }, 900);

            this.thinkingIntervals[id] = interval;
            return id;
        },

        showThinking(defaultText = 'SiKader sedang berpikir...') {
            const id = 'think-' + Date.now() + Math.random();
            this.messages.push({ role: 'assistant', type: 'thinking', content: defaultText, data: null, id });
            return id;
        },

        removeThinking(id) {
            if (this.thinkingIntervals && this.thinkingIntervals[id]) {
                clearInterval(this.thinkingIntervals[id]);
                delete this.thinkingIntervals[id];
            }
            this.messages = this.messages.filter(m => m.id !== id);
        },

        handleKeydown(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        },

        renderMessageWithCitations(content, sources, activeSourceId) {
            if (!content) return '';
            if (!sources || sources.length === 0) return content;

            return content.replace(/\[(\d+)\]/g, (match, idStr) => {
                const id = parseInt(idStr);
                const found = sources.find(s => s.id === id || Number(s.id) === id);
                if (!found) return match;

                const isActive = (activeSourceId === id);
                const chipClasses = isActive
                    ? 'bg-[var(--accent)] text-[#003855] border-[var(--accent)] font-black scale-105 shadow-xs'
                    : 'bg-sky-100/90 text-sky-800 dark:bg-sky-950 dark:text-sky-300 hover:bg-[#005c8a] hover:text-white border-sky-300 dark:border-sky-700';

                return `<span class="inline-block align-baseline mx-0.5"><button type="button" data-cite-id="${id}" class="inline-flex items-center justify-center px-1.5 py-0.2 text-[10px] font-bold rounded-md border transition-all duration-150 cursor-pointer shadow-2xs ${chipClasses}" title="${found.title} (${found.badge})">${id}</button></span>`;
            });
        },
    };
};
</script>
