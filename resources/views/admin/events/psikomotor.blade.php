@extends('layouts.app')

@section('title', 'Psikomotor — ' . $event->nama_event)

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-mpksdi-700 transition mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke {{ $event->nama_event }}
        </a>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Rubrik &amp; Penilaian Psikomotor</h1>
        <p class="text-xs text-slate-500 mt-0.5">Penilaian observasi lapangan oleh fasilitator (Ibadah &amp; Outbound/Aktivitas)</p>
    </div>

    {{-- Aspect Template Manager Card --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6 sm:p-7 shadow-xs">
        <h2 class="font-bold text-slate-900 text-sm mb-3">Tambah Aspek Penilaian Baru</h2>
        <form method="POST" action="{{ route('admin.events.psikomotor.template', $event) }}" class="flex gap-3 flex-wrap items-end mb-6">
            @csrf
            <div class="flex-1 min-w-[220px]">
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Aspek / Perilaku</label>
                <input name="nama_aspek" required placeholder="Contoh: Ketepatan Gerakan Sholat, Kerjasama Regu"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs outline-none focus:bg-white focus:ring-2 focus:ring-mpksdi-500 transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Kategori</label>
                <select name="jenis" class="px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-semibold outline-none focus:ring-2 focus:ring-mpksdi-500 cursor-pointer transition">
                    <option value="ibadah">Ibadah</option>
                    <option value="outbound">Outbound</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Skor Maks</label>
                <input type="number" name="skor_maks" value="4" min="2" max="10" required
                    class="w-24 px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-bold text-center outline-none focus:bg-white focus:ring-2 focus:ring-mpksdi-500 transition">
            </div>
            <button class="px-5 py-2.5 bg-mpksdi-600 hover:bg-mpksdi-700 active:bg-mpksdi-800 text-white text-xs font-bold rounded-lg shadow-xs transition">
                + Tambah Aspek
            </button>
        </form>

        {{-- Existing Aspects Chips --}}
        <div>
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-2">Aspek Aktif:</span>
            <div class="flex flex-wrap gap-2">
                @forelse($templates as $t)
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50/70 text-xs shadow-2xs">
                    <span class="px-1.5 py-0.5 rounded bg-white font-bold text-[10px] text-slate-500 uppercase border border-slate-200">{{ $t->jenis }}</span>
                    <span class="font-bold text-slate-800">{{ $t->nama_aspek }}</span>
                    <span class="text-slate-400 font-mono">(maks {{ $t->skor_maks }})</span>
                    <form method="POST" action="{{ route('admin.events.psikomotor.template.destroy', [$event, $t]) }}" onsubmit="return confirm('Hapus aspek beserta seluruh nilai peserta?')">
                        @csrf @method('DELETE')
                        <button class="text-slate-400 hover:text-red-600 font-bold ml-1 transition" title="Hapus Aspek">&times;</button>
                    </form>
                </div>
                @empty
                <p class="text-xs text-slate-400">Belum ada rubrik aspek yang ditambahkan.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Scoring Data Grid Card --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 sm:p-7 space-y-4" x-data="psikomotorApp()">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h2 class="font-bold text-slate-900 text-base">Matriks Penilaian Peserta</h2>
                <p class="text-xs text-slate-500">Masukkan nilai 1 s.d. Skor Maks, lalu klik Simpan pada tiap baris</p>
            </div>
            <div x-show="toast" x-transition class="px-3 py-1.5 rounded-lg bg-mpksdi-50 text-mpksdi-800 border border-mpksdi-200 text-xs font-semibold" style="display: none;">
                ✓ Nilai tersimpan
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-left">
                        <th class="py-3 px-4 sticky left-0 bg-slate-50">Nama Kader</th>
                        <template x-for="t in templates" :key="t.id">
                            <th class="py-3 px-3 text-center whitespace-nowrap">
                                <span x-text="t.nama_aspek"></span>
                                <span class="block text-[10px] text-slate-400 font-normal">Maks <span x-text="t.skor_maks"></span></span>
                            </th>
                        </template>
                        <th class="py-3 px-3 text-center">Persentase</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="row in rows" :key="row.anggota_id">
                        <tr class="hover:bg-slate-50/60 transition duration-150">
                            <td class="py-3 px-4 font-semibold text-slate-800 sticky left-0 bg-white whitespace-nowrap text-xs sm:text-sm" x-text="row.nama"></td>
                            <template x-for="t in templates" :key="t.id">
                                <td class="py-2.5 px-3 text-center">
                                    <input type="number" min="1" :max="t.skor_maks"
                                        :value="row.scores[t.id] ?? ''"
                                        @change="row.scores[t.id] = $event.target.value ? parseInt($event.target.value) : null"
                                        class="w-16 px-2 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-bold text-center outline-none focus:bg-white focus:ring-2 focus:ring-mpksdi-500 transition">
                                </td>
                            </template>
                            <td class="py-3 px-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-mpksdi-50 text-mpksdi-700 border border-mpksdi-200" x-text="rowTotal(row) + '%'"></span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <button @click="save(row)" class="px-3 py-1.5 rounded-lg bg-mpksdi-600 hover:bg-mpksdi-700 text-white text-xs font-bold shadow-2xs transition">
                                    Simpan
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <p x-show="rows.length === 0" class="text-xs text-slate-400 text-center py-10">Belum ada data peserta atau aspek yang disiapkan.</p>
    </div>
</div>

<script>
function psikomotorApp() {
    return {
        templates: @json($templates->map(fn($t) => ['id' => $t->id, 'nama_aspek' => $t->nama_aspek, 'skor_maks' => $t->skor_maks])),
        rows: [],
        toast: false,
        async init() {
            const res = await fetch('{{ route('admin.events.psikomotor.data', $event) }}', { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            this.templates = data.templates.map(t => ({ id: t.id, nama_aspek: t.nama_aspek, skor_maks: t.skor_maks }));
            this.rows = data.rows;
        },
        rowTotal(row) {
            const maks = this.templates.reduce((s, t) => s + t.skor_maks, 0);
            if (maks === 0) return 0;
            const total = this.templates.reduce((s, t) => s + (parseInt(row.scores[t.id]) || 0), 0);
            return Math.round((total / maks) * 100);
        },
        async save(row) {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const scores = this.templates.map(t => ({ template_id: t.id, skor: parseInt(row.scores[t.id]) || 1 }));
            const res = await fetch('{{ route('admin.events.psikomotor.save', $event) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ anggota_id: row.anggota_id, scores }),
            });
            if (res.ok) {
                this.toast = true;
                setTimeout(() => { this.toast = false; }, 2500);
            }
        },
    }
}
</script>
@endsection
