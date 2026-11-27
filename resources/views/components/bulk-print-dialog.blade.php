@props([
    'id',
    'title',
    'subtitle' => null,
    'items' => [],
    'actions' => [],
    'quickBadge' => null,
    'quickBadgeLabel' => null,
    'excludeBadge' => null,
    'excludeBadgeLabel' => null,
    'defaultBadges' => null,
    'sendAllIds' => false,
])

@php
    $itemList = collect($items)->values()->all();
    // Centangan awal: semua item, atau hanya item ber-badge tertentu (mis. Lulus) bila defaultBadges diisi.
    $awalTerpilih = $defaultBadges === null
        ? array_column($itemList, 'id')
        : array_values(array_column(array_filter($itemList, fn ($i) => in_array($i['badge'] ?? null, $defaultBadges, true)), 'id'));
@endphp

<dialog id="{{ $id }}"
    closedby="any"
    aria-labelledby="{{ $id }}-title"
    x-data="{
        items: @js($itemList),
        selected: @js($awalTerpilih),
        q: '',
        activeAction: { url: '', target: '_self', download: false },
        init() {
            const dlg = this.$el;
            if (!('closedBy' in HTMLDialogElement.prototype)) {
                const outside = (e) => {
                    const r = dlg.getBoundingClientRect();
                    return e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom;
                };
                let pressedOutside = false;
                dlg.addEventListener('pointerdown', (e) => { pressedOutside = outside(e); });
                dlg.addEventListener('pointerup', (e) => { if (pressedOutside && outside(e)) dlg.close(); });
            }
        },
        get filtered() {
            const term = this.q.trim().toLowerCase();
            if (!term) return this.items;
            return this.items.filter(i =>
                (i.nama + ' ' + (i.meta || '') + ' ' + (i.badge || '')).toLowerCase().includes(term)
            );
        },
        isChecked(id) {
            return this.selected.includes(id);
        },
        toggle(id) {
            if (this.isChecked(id)) {
                this.selected = this.selected.filter(x => x !== id);
            } else {
                this.selected = [...this.selected, id];
            }
        },
        selectAll() {
            this.selected = this.items.map(i => i.id);
        },
        selectFiltered() {
            const set = new Set(this.selected);
            for (const i of this.filtered) set.add(i.id);
            this.selected = Array.from(set);
        },
        clearAll() {
            this.selected = [];
        },
        selectByBadge(badge) {
            this.selected = this.items.filter(i => i.badge === badge).map(i => i.id);
        },
        selectExcludingBadge(badge) {
            this.selected = this.items.filter(i => i.badge !== badge).map(i => i.id);
        },
        submitAction(act) {
            if (!this.selected.length) return;
            this.activeAction = act;
            this.$nextTick(() => {
                this.$refs.printForm.submit();
                this.$el.close();
            });
        }
    }"
    class="bulk-print-dialog m-auto w-[calc(100%-1.5rem)] max-w-xl rounded-2xl border border-slate-200 bg-white p-0 text-slate-900 shadow-2xl" style="width: calc(100% - 1.5rem); max-width: 36rem; padding: 0; border-radius: 1rem;" data-bulk-print-dialog>

    <div class="flex flex-col max-h-[85vh]" style="display: flex; flex-direction: column; max-height: 85vh;">
        {{-- Header --}}
        <div class="flex items-start justify-between gap-3 px-4 py-3.5 sm:px-5 border-b border-slate-100 shrink-0">
            <div class="min-w-0">
                <h1 id="{{ $id }}-title" class="text-sm sm:text-base font-bold text-slate-900 leading-snug">{{ $title }}</h1>
                @if($subtitle)
                    <p class="text-xs text-slate-500 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            <button type="button" @click="$el.closest('dialog').close()" aria-label="Tutup"
                class="w-8 h-8 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-500 hover:text-slate-700 flex items-center justify-center shrink-0 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Search & Quick Actions --}}
        <div class="px-4 py-3 sm:px-5 bg-slate-50/70 border-b border-slate-100 space-y-2.5 shrink-0">
            <div class="relative">
                <input type="search" x-model="q" autofocus placeholder="Cari nama peserta, cabang, atau status..."
                    class="w-full pl-8 pr-3 py-2 text-xs bg-white border border-slate-200 rounded-xl outline-none focus:border-[#005c8a]">
                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 text-[11px]">
                <div class="flex flex-wrap items-center gap-1.5">
                    <button type="button" @click="selectAll()" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 font-semibold text-slate-700 cursor-pointer">Pilih Semua</button>
                    <button type="button" x-show="q.trim().length > 0" @click="selectFiltered()" class="px-2.5 py-1 rounded-lg bg-sky-50 border border-sky-200 hover:bg-sky-100 font-semibold text-sky-800 cursor-pointer">Pilih Hasil Cari</button>
                    @if($quickBadge)
                        <button type="button" @click="selectByBadge(@js($quickBadge))" class="px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 font-semibold text-emerald-800 cursor-pointer">{{ $quickBadgeLabel ?: $quickBadge }}</button>
                    @endif
                    @if($excludeBadge)
                        <button type="button" @click="selectExcludingBadge(@js($excludeBadge))" class="px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-200 hover:bg-amber-100 font-semibold text-amber-800 cursor-pointer">{{ $excludeBadgeLabel ?: 'Selain '.$excludeBadge }}</button>
                    @endif
                    <button type="button" @click="clearAll()" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 font-semibold text-slate-600 cursor-pointer">Kosongkan</button>
                </div>

                <span class="font-bold text-slate-700">
                    Terpilih: <span class="text-[#005c8a]" x-text="selected.length"></span> / <span x-text="items.length"></span>
                </span>
            </div>
        </div>

        {{-- Participant Checkbox List --}}
        <div class="flex-1 min-h-32 overflow-y-auto px-4 py-2.5 sm:px-5 divide-y divide-slate-100" style="min-height: 8rem;">
            <template x-if="items.length === 0">
                <p class="py-8 text-center text-xs text-slate-400">Belum ada peserta yang memenuhi syarat untuk dicetak.</p>
            </template>
            <template x-if="items.length > 0 && filtered.length === 0">
                <p class="py-6 text-center text-xs text-slate-400">Tidak ada peserta yang cocok dengan pencarian.</p>
            </template>
            <template x-for="item in filtered" :key="item.id">
                <label class="flex items-center justify-between gap-3 py-2.5 min-h-11 cursor-pointer hover:bg-slate-50/70 px-2 -mx-2 rounded-lg transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <input type="checkbox"
                            :checked="isChecked(item.id)"
                            @change="toggle(item.id)"
                            class="w-4 h-4 rounded border-slate-300 text-[#005c8a] focus:ring-[#005c8a] shrink-0 cursor-pointer">
                        <div class="min-w-0">
                            <span class="block text-xs sm:text-sm font-semibold text-slate-900 truncate" x-text="item.nama"></span>
                            <span class="block text-[11px] text-slate-500 truncate" x-show="item.meta" x-text="item.meta"></span>
                        </div>
                    </div>
                    <template x-if="item.badge">
                        <span class="px-2 py-0.5 rounded-md border text-[10px] font-bold shrink-0" :class="item.badgeClass || 'bg-slate-100 text-slate-700 border-slate-200'" x-text="item.badge"></span>
                    </template>
                </label>
            </template>
        </div>

        @if(trim($slot) !== '')
            <div class="px-4 py-3 sm:px-5 border-t border-slate-100 shrink-0 overflow-y-auto" style="max-height: 30vh;">{{ $slot }}</div>
        @endif

        {{-- Footer Form & Actions --}}
        <div class="px-4 py-3 sm:px-5 border-t border-slate-100 bg-slate-50/60 flex flex-wrap items-center justify-between gap-2 shrink-0">
            <form x-ref="printForm" id="{{ $id }}-form" method="GET" :action="activeAction.url" :target="activeAction.target || '_self'" class="hidden">
                <template x-if="activeAction.download">
                    <input type="hidden" name="download" value="1">
                </template>
                <template x-if="selected.length > 0 && ({{ $sendAllIds ? 'true' : 'false' }} || selected.length < items.length)">
                    <input type="hidden" name="peserta_ids" :value="selected.join(',')">
                </template>
            </form>

            <span class="text-[11px] text-rose-600 font-semibold" x-show="selected.length === 0">Pilih minimal 1 peserta.</span>
            <span class="text-[11px] text-slate-500" x-show="selected.length > 0">
                <span x-show="selected.length === items.length">Semua peserta aktif terpilih.</span>
                <span x-show="selected.length < items.length" x-text="'Mencetak ' + selected.length + ' peserta terpilih.'"></span>
            </span>

            <div class="grid grid-cols-2 w-full sm:flex sm:w-auto sm:flex-wrap items-center gap-2 sm:ml-auto [&>button]:whitespace-nowrap">
                <button type="button" @click="$el.closest('dialog').close()" class="px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-xs font-semibold text-slate-600 cursor-pointer">
                    Batal
                </button>
                @foreach($actions as $act)
                    <button type="button"
                        :disabled="selected.length === 0"
                        @click="submitAction(@js($act))"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-2xs disabled:opacity-45 disabled:cursor-not-allowed cursor-pointer {{ $act['class'] ?? 'bg-[#005c8a] hover:bg-[#004d73] text-white' }}">
                        {{ $act['label'] }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</dialog>
