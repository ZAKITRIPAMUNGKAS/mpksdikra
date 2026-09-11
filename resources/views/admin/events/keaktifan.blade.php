@extends('layouts.app')

@section('title', 'Keaktifan — ' . $event->nama_event)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-600 transition shadow-2xs">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span>Kembali ke {{ $event->nama_event }}</span>
    </a>
</div>

<div class="bg-[#f3f5f8] rounded-3xl border border-sky-100/80 p-6 sm:p-8 shadow-xs mb-6 max-w-3xl relative overflow-hidden card-lift-sm">
    <x-batik-corner position="top-right" class="w-36 h-36 text-[#005c8a] opacity-[0.22]" />
    <div class="relative z-10">
        <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-800 border border-sky-200 mb-2">
            <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-ping"></span>
            Observasi Keaktifan
        </span>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight mb-1">Catat Keaktifan Peserta</h1>
        <p class="text-xs text-slate-600 mb-5 font-medium">Tiap aksi bernilai 1 poin: tanya, jawab, kultum, imam, adzan, juara outbound. Maksimal {{ config('arqam.keaktifan.bonus_maks') }} poin.</p>

        <form method="POST" action="{{ route('admin.events.keaktifan.store', $event) }}" class="flex gap-3 flex-wrap items-end">
            @csrf
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Peserta</label>
                <select name="anggota_id" required class="w-full px-3.5 py-2.5 bg-white border border-slate-200/90 rounded-xl text-xs outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition">
                    <option value="">Pilih peserta...</option>
                    @foreach($peserta as $p)
                    <option value="{{ $p->anggota_id }}">{{ $p->anggota->nama_lengkap }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Jenis Aksi</label>
                <select name="jenis" required class="px-3.5 py-2.5 bg-white border border-slate-200/90 rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 cursor-pointer transition">
                    <option value="tanya">Tanya</option>
                    <option value="jawab">Jawab</option>
                    <option value="kultum">Kultum</option>
                    <option value="imam">Imam</option>
                    <option value="adzan">Adzan</option>
                    <option value="juara_outbound">Juara Outbound</option>
                </select>
            </div>
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Keterangan</label>
                <input name="keterangan" placeholder="Opsional (catatan fasilitator)" class="w-full px-3.5 py-2.5 bg-white border border-slate-200/90 rounded-xl text-xs outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition">
            </div>
            <button class="px-6 py-2.5 bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white text-xs font-bold rounded-full shadow-xs transition hover:scale-[1.01]">Catat Poin</button>
        </form>
    </div>
</div>

<div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs max-w-3xl card-lift-sm card-sheen hover:border-sky-400 transition">
    <h2 class="font-black text-slate-900 text-base mb-4 tracking-tight">Total Keaktifan per Peserta</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-left">
                    <th class="py-3.5 px-4">Nama Peserta</th>
                    <th class="py-3.5 px-4 text-center">Poin Terkumpul</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($peserta as $p)
                <tr class="hover:bg-slate-50/60 transition">
                    <td class="py-3.5 px-4 font-bold text-slate-900">{{ $p->anggota->nama_lengkap }}</td>
                    <td class="py-3.5 px-4 text-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-800 border border-sky-200 shadow-2xs">
                            {{ $counts->get($p->anggota_id, 0) }} Poin
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="2" class="py-8 text-center text-slate-400">Belum ada peserta terdaftar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
