@extends('layouts.app')

@section('title', 'Presensi — ' . $sesi->nama_sesi)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Breadcrumb & Event Title --}}
    <div>
        <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-mpksdi-700 transition mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke {{ $event->nama_event }}
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Presensi: {{ $sesi->nama_sesi }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">Sesi #{{ $sesi->urutan }} &bull; Kategori: <span class="capitalize font-semibold text-slate-700">{{ $sesi->jenis }}</span></p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-mpksdi-50 text-mpksdi-700 border border-mpksdi-200">
                    <span class="w-2 h-2 rounded-full bg-mpksdi-500 animate-ping"></span>
                    Scanner Siap
                </span>
            </div>
        </div>
    </div>

    <div class="grid md:grid-cols-5 gap-6 items-start" x-data="scanApp()">
        {{-- Left: Scanner Console --}}
        <div class="md:col-span-3 bg-white rounded-xl border border-slate-200 shadow-xs p-6 sm:p-7 space-y-5">
            {{-- Target Viewfinder Frame --}}
            <div class="relative bg-mpksdi-950 rounded-xl p-8 text-center text-white overflow-hidden shadow-inner border border-mpksdi-900">
                <div class="w-36 h-36 mx-auto border-2 border-dashed border-gold-400/80 rounded-xl flex flex-col items-center justify-center relative group bg-mpksdi-900/50">
                    <div class="absolute inset-x-2 top-1/2 h-0.5 bg-cyan-mpksdi shadow-sm shadow-cyan-mpksdi"></div>
                    <svg class="w-10 h-10 text-gold-400/80 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    <span class="text-[10px] text-gold-300 font-mono uppercase tracking-wider">Arahkan QR Code</span>
                </div>
                <p class="text-xs text-slate-400 mt-4">Gunakan barcode scanner USB / Bluetooth atau tempel token QR di bawah</p>
            </div>

            {{-- Input and Trigger --}}
            <div class="space-y-2">
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Input Kode QR / Token Peserta</label>
                <div class="flex gap-2">
                    <input x-model="qr" @keyup.enter="scan" autofocus placeholder="Tempel / ketik kode QR di sini..."
                        class="flex-1 px-4 py-3 bg-slate-50 border border-slate-300 rounded-lg text-sm font-mono focus:bg-white focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none transition">
                    <button @click="scan" :disabled="!qr.trim()"
                        class="px-5 py-3 bg-mpksdi-600 hover:bg-mpksdi-700 active:bg-mpksdi-800 disabled:opacity-50 text-white font-bold text-sm rounded-lg shadow-xs transition duration-150">
                        Proses Presensi
                    </button>
                </div>
            </div>

            {{-- Live Result Notification --}}
            <div x-show="message" x-transition class="p-4 rounded-lg text-sm flex items-start gap-3 border"
                :class="ok ? 'bg-mpksdi-50 border-mpksdi-200 text-mpksdi-900' : 'bg-red-50 border-red-200 text-red-800'" style="display: none;">
                <div class="shrink-0 mt-0.5">
                    <template x-if="ok">
                        <svg class="w-5 h-5 text-mpksdi-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                    <template x-if="!ok">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                </div>
                <div class="font-medium" x-text="message"></div>
            </div>
        </div>

        {{-- Right: Recent Scans Activity Feed --}}
        <div class="md:col-span-2 bg-white rounded-xl border border-slate-200 shadow-xs p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h2 class="font-bold text-slate-900 text-sm">Riwayat Presensi Terbaru</h2>
                <button @click="load" title="Segarkan data" class="text-slate-400 hover:text-mpksdi-700 transition">
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
                    <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-100 flex items-center justify-between gap-2">
                        <div>
                            <span class="font-bold text-slate-800 block text-xs">${x.nama}</span>
                            <span class="text-[10px] text-slate-400">${x.waktu} &bull; Metode: ${x.metode}</span>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-mpksdi-50 text-mpksdi-700 border border-mpksdi-200">Hadir</span>
                    </div>
                `).join('');
            } catch (e) {}
        },
        init() { this.load(); }
    }
}
</script>
@endsection
