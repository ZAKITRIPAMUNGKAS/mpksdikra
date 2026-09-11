@extends('layouts.app')

@section('title', 'Presensi — ' . $sesi->nama_sesi)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Header Banner with Batik Corner --}}
    <div class="bg-[#f3f5f8] rounded-3xl border border-sky-100/80 p-6 sm:p-8 shadow-xs relative overflow-hidden card-lift-sm">
        <x-batik-corner position="top-right" class="w-40 h-40 text-[#005c8a] opacity-[0.20]" />
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                    <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-600 transition shadow-2xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>{{ $event->nama_event }}</span>
                    </a>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-800 border border-sky-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-ping"></span>
                        Scanner Aktif
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Presensi: {{ $sesi->nama_sesi }}</h1>
                <p class="text-xs text-slate-500 mt-1">Sesi #{{ $sesi->urutan }} &bull; Kategori: <span class="capitalize font-bold text-slate-700">{{ $sesi->jenis }}</span></p>
            </div>
            <div>
                <a href="{{ route('admin.events.absensi.cetak', [$event, $sesi]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-white hover:bg-slate-50 border border-slate-200 text-xs font-bold text-slate-700 shadow-2xs transition hover:scale-[1.01]">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Cetak Lembar Fisik (PDF)</span>
                </a>
            </div>
        </div>
    </div>

    <div class="grid md:grid-cols-5 gap-6 items-start" x-data="scanApp()">
        {{-- Left: Scanner Console --}}
        <div class="md:col-span-3 bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-7 space-y-5 card-lift-sm">
            {{-- Target Viewfinder Frame --}}
            <div class="relative bg-slate-950 rounded-2xl p-8 text-center text-white overflow-hidden shadow-inner border border-slate-800">
                <div class="w-36 h-36 mx-auto border-2 border-dashed border-[#f8bb00] rounded-2xl flex flex-col items-center justify-center relative group bg-slate-900/60 shadow-lg shadow-amber-500/10">
                    <div class="absolute inset-x-2 top-1/2 h-0.5 bg-sky-400 shadow-sm shadow-sky-400 animate-pulse"></div>
                    <svg class="w-10 h-10 text-[#f8bb00] mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    <span class="text-[10px] text-amber-300 font-mono uppercase tracking-wider font-bold">Arahkan QR Code</span>
                </div>
                <p class="text-xs text-slate-400 mt-4">Gunakan barcode scanner USB / Bluetooth atau tempel token QR di bawah</p>
            </div>

            {{-- Input and Trigger --}}
            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Input Kode QR / Token Peserta</label>
                <div class="flex flex-col sm:flex-row gap-2">
                    <input x-model="qr" @keyup.enter="scan" autofocus placeholder="Tempel / ketik kode QR di sini..."
                        class="flex-1 px-4 py-3 bg-slate-50/70 border border-slate-200 rounded-xl text-sm font-mono focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition">
                    <button @click="scan" :disabled="!qr.trim()"
                        class="px-6 py-3 bg-sky-500 hover:bg-sky-600 active:bg-sky-700 disabled:opacity-50 text-white font-bold text-xs rounded-full shadow-xs hover:scale-[1.01] transition duration-150 shrink-0">
                        Proses Presensi
                    </button>
                </div>
            </div>

            {{-- Live Result Notification --}}
            <div x-show="message" x-transition class="p-4 rounded-2xl text-sm flex items-start gap-3 border"
                :class="ok ? 'bg-sky-50 border-sky-200 text-sky-900' : 'bg-red-50 border-red-200 text-red-800'" style="display: none;">
                <div class="shrink-0 mt-0.5">
                    <template x-if="ok">
                        <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                    <template x-if="!ok">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                </div>
                <div class="font-bold" x-text="message"></div>
            </div>
        </div>

        {{-- Right: Recent Scans Activity Feed --}}
        <div class="md:col-span-2 bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4 card-lift-sm">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h2 class="font-black text-slate-900 text-sm">Riwayat Presensi Terbaru</h2>
                <button @click="load" title="Segarkan data" class="text-slate-400 hover:text-sky-600 transition p-1 rounded-full hover:bg-slate-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>
            </div>

            <div id="recent" class="space-y-2 max-h-96 overflow-y-auto pr-1 text-xs text-slate-500">
                Memuat data...
            </div>
        </div>
    </div>
</div>

<script>
function scanApp() {
    return {
        qr: '', message: '', ok: false,
        async scan() {
            if (!this.qr.trim()) return;
            try {
                const res = await fetch('{{ route('admin.events.absensi.process', $event) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ qr_code: this.qr, sesi_id: {{ $sesi->id }} })
                });
                const data = await res.json();
                this.ok = res.ok && data.status !== 'error';
                this.message = data.message || (data.status === 'success' ? `Presensi ${data.nama} berhasil dicatat.` : `Presensi ${data.nama} sudah pernah tercatat.`);
                this.qr = '';
                this.load();
            } catch (e) {
                this.ok = false;
                this.message = 'Terjadi kesalahan koneksi saat memproses presensi.';
            }
        },
        async load() {
            try {
                const res = await fetch('{{ route('admin.events.absensi.recent', [$event, $sesi]) }}');
                const data = await res.json();
                const container = document.getElementById('recent');
                if (!data.records || data.records.length === 0) {
                    container.innerHTML = '<div class="py-8 text-center text-slate-400">Belum ada peserta yang hadir.</div>';
                    return;
                }
                container.innerHTML = data.records.map(x => `
                    <div class="p-3.5 rounded-2xl bg-slate-50/70 border border-slate-100 flex items-center justify-between gap-2">
                        <div>
                            <span class="font-bold text-slate-900 block text-xs">${x.nama}</span>
                            <span class="text-[10px] text-slate-400">${x.waktu} &bull; Metode: ${x.metode}</span>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-800 border border-sky-200">Hadir</span>
                    </div>
                `).join('');
            } catch (e) {}
        },
        init() { this.load(); }
    }
}
</script>
@endsection
