@extends('layouts.app')

@section('title', 'Keaktifan & Partisipasi — ' . $event->nama_event)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between gap-3 flex-wrap">
        <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 transition shadow-2xs">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke {{ $event->nama_event }}</span>
        </a>
    </div>

    {{-- Form Pencatatan Keaktifan --}}
    <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs relative overflow-hidden">
        <x-batik-corner position="top-right" class="w-36 h-36 text-[#005c8a] opacity-[0.22]" />
        <div class="relative z-10">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-medium bg-sky-50 text-sky-800 border border-sky-200 mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
                Observasi &amp; Poin Keaktifan Peserta
            </span>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mb-1">Catat Keaktifan Peserta</h1>
            <p class="text-xs text-slate-500 mb-5 font-normal max-w-2xl">
                Catat setiap kontribusi aktif peserta di forum maupun lapangan (tanya, jawab, kultum/presentasi kelompok, imam shalat, adzan, dan juara games outbound). Tiap aksi bernilai 1 poin bonus (maksimal {{ config('arqam.keaktifan.bonus_maks') }} poin) dan otomatis mengkalkulasi skor akhir kader.
            </p>

            <form method="POST" action="{{ route('admin.events.keaktifan.store', $event) }}" class="flex gap-3 flex-wrap items-end">
                @csrf
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Peserta</label>
                    <select name="anggota_id" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition">
                        <option value="">Pilih peserta...</option>
                        @foreach($peserta as $p)
                        <option value="{{ $p->anggota_id }}">{{ $p->anggota->nama_lengkap }} ({{ $p->anggota->cabang ?? 'Peserta' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[170px]">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Sesi Agenda</label>
                    <select name="sesi_id" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition">
                        <option value="">-- Umum / Semua Sesi --</option>
                        @foreach($sesiList as $s)
                        <option value="{{ $s->id }}">Sesi {{ $s->urutan }}: {{ Str::limit($s->nama_sesi, 24) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[160px]">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Jenis Aksi</label>
                    <select name="jenis" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 cursor-pointer transition">
                        <option value="tanya">Tanya (Forum)</option>
                        <option value="jawab">Jawab (Forum)</option>
                        <option value="kultum">Kultum / Presentasi</option>
                        <option value="imam">Imam Shalat</option>
                        <option value="adzan">Adzan</option>
                        <option value="juara_outbound">Juara Outbound / Games</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Keterangan</label>
                    <input name="keterangan" placeholder="Contoh: Pertanyaan materi PHIWM, Presentasi kelompok 2" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition">
                </div>
                <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white text-xs font-bold rounded-lg shadow-2xs transition cursor-pointer">
                    + Catat Poin
                </button>
            </form>
        </div>
    </div>

    {{-- Grid: Total Poin & Riwayat Log --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Total Poin per Peserta (1 Kolom) --}}
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-2xs h-fit">
            <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                <h2 class="font-bold text-slate-900 text-sm tracking-tight">Total Poin Peserta</h2>
                <span class="text-[11px] text-slate-500 font-medium">{{ $peserta->count() }} Kader</span>
            </div>
            <div class="overflow-y-auto max-h-[500px] scrollbar-thin">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider text-left text-[11px]">
                            <th class="py-2.5 px-3">Nama</th>
                            <th class="py-2.5 px-3 text-center">Poin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($peserta as $p)
                        @php
                            $poin = $counts->get($p->anggota_id, 0);
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-2.5 px-3 font-semibold text-slate-900">
                                <div>{{ $p->anggota->nama_lengkap }}</div>
                                <div class="text-[10px] text-slate-400 font-normal">{{ $p->anggota->cabang ?? '-' }}</div>
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $poin > 0 ? 'bg-sky-100 text-sky-800 border border-sky-200' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $poin }} Poin
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="py-6 text-center text-slate-400">Belum ada peserta.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Log Riwayat Aksi (2 Kolom) --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200/90 p-5 shadow-2xs">
            <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm tracking-tight">Riwayat Catatan Keaktifan</h2>
                    <p class="text-[11px] text-slate-500">Log 50 pencatatan keaktifan terbaru. Klik ikon hapus jika salah mencatat.</p>
                </div>
                <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-md">
                    {{ $riwayatKeaktifan->count() }} Catatan
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider text-left text-[11px]">
                            <th class="py-2.5 px-3">Waktu</th>
                            <th class="py-2.5 px-3">Peserta</th>
                            <th class="py-2.5 px-3">Jenis Aksi</th>
                            <th class="py-2.5 px-3">Sesi / Keterangan</th>
                            <th class="py-2.5 px-3 text-center w-12">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($riwayatKeaktifan as $rk)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-2.5 px-3 font-mono text-[11px] text-slate-400 whitespace-nowrap">
                                {{ $rk->created_at->format('H:i') }}
                            </td>
                            <td class="py-2.5 px-3 font-semibold text-slate-900 whitespace-nowrap">
                                {{ $rk->anggota->nama_lengkap ?? '-' }}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold
                                    {{ in_array($rk->jenis, ['tanya', 'jawab']) ? 'bg-sky-100 text-sky-800' :
                                       ($rk->jenis === 'juara_outbound' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                    {{ ucfirst(str_replace('_', ' ', $rk->jenis)) }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-slate-600">
                                @if($rk->sesi)
                                <span class="font-medium text-slate-800 block text-[11px]">Sesi {{ $rk->sesi->urutan }}: {{ $rk->sesi->nama_sesi }}</span>
                                @endif
                                <span class="text-[11px] text-slate-500">{{ $rk->keterangan ?: '-' }}</span>
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <form method="POST" action="{{ route('admin.events.keaktifan.destroy', [$event, $rk]) }}" onsubmit="return confirm('Hapus poin keaktifan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-rose-600 transition p-1" title="Hapus catatan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">
                                Belum ada poin keaktifan yang dicatat untuk event ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
