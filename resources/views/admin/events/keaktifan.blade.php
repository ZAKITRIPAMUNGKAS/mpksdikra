@extends('layouts.app')

@section('title', 'Keaktifan — ' . $event->nama_event)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-mpksdi-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke {{ $event->nama_event }}
    </a>
</div>

<div class="bg-white rounded-xl border border-slate-200 p-6 sm:p-7 shadow-xs mb-6 max-w-3xl">
    <h1 class="text-xl font-bold text-slate-900 tracking-tight mb-1">Catat Keaktifan Peserta</h1>
    <p class="text-xs text-slate-500 mb-5">Tiap aksi bernilai 1 poin: tanya, jawab, kultum, imam, adzan, juara outbound. Maksimal {{ config('arqam.keaktifan.bonus_maks') }} poin.</p>

    <form method="POST" action="{{ route('admin.events.keaktifan.store', $event) }}" class="flex gap-3 flex-wrap items-end">
        @csrf
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Peserta</label>
            <select name="anggota_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs outline-none focus:bg-white focus:ring-2 focus:ring-mpksdi-500 transition">
                <option value="">Pilih peserta...</option>
                @foreach($peserta as $p)
                <option value="{{ $p->anggota_id }}">{{ $p->anggota->nama_lengkap }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Jenis Aksi</label>
            <select name="jenis" required class="px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-semibold outline-none focus:ring-2 focus:ring-mpksdi-500 cursor-pointer transition">
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
            <input name="keterangan" placeholder="Opsional (catatan fasilitator)" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs outline-none focus:bg-white focus:ring-2 focus:ring-mpksdi-500 transition">
        </div>
        <button class="px-5 py-2.5 bg-mpksdi-600 hover:bg-mpksdi-700 text-white text-xs font-bold rounded-lg shadow-xs transition">Catat Poin</button>
    </form>
</div>

<div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs max-w-3xl">
    <h2 class="font-bold text-slate-900 text-sm mb-4">Total Keaktifan per Peserta</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-left">
                    <th class="py-3 px-4">Nama Peserta</th>
                    <th class="py-3 px-4 text-center">Poin Terkumpul</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($peserta as $p)
                <tr class="hover:bg-slate-50/60 transition">
                    <td class="py-3 px-4 font-semibold text-slate-900">{{ $p->anggota->nama_lengkap }}</td>
                    <td class="py-3 px-4 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-mpksdi-50 text-mpksdi-700 border border-mpksdi-200">
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
