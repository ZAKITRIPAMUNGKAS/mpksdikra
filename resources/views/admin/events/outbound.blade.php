@extends('layouts.app')

@section('title', 'Kelompok Outbound — ' . $event->nama_event)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-mpksdi-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke {{ $event->nama_event }}
    </a>
</div>

<div class="space-y-6">
    <!-- Header & Config Card -->
    <div class="bg-white rounded-xl border border-slate-200 p-6 sm:p-7 shadow-xs">
        <div class="flex items-start justify-between gap-4 flex-wrap pb-5 border-b border-slate-100">
            <div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-mpksdi-50 text-mpksdi-800 border border-mpksdi-200 mb-2">
                    Modul Outbound & Dinamika Kelompok
                </span>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Pengacak Kelompok Outbound</h1>
                <p class="text-xs text-slate-500 mt-1">
                    Sistem membagi {{ $totalAktif }} peserta aktif secara otomatis dan proporsional sesuai kriteria yang dipilih panitia.
                </p>
            </div>
            @if($kelompoks->isNotEmpty())
            <form method="POST" action="{{ route('admin.events.outbound.reset', $event) }}"
                onsubmit="return confirm('Apakah Anda yakin ingin mereset pembagian kelompok?')">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-red-200 text-red-600 hover:bg-red-50 text-xs font-bold rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span>Reset Kelompok</span>
                </button>
            </form>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.events.outbound.generate', $event) }}" class="mt-5">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Jumlah Kelompok:
                    </label>
                    <input type="number" name="jumlah_kelompok" min="1" max="{{ max(1, $totalAktif) }}"
                        value="{{ old('jumlah_kelompok', $kelompoks->count() > 0 ? $kelompoks->count() : min(4, max(1, $totalAktif))) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-lg border border-slate-300 focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 bg-white"
                        required>
                    <p class="text-[11px] text-slate-400 mt-1">Disesuaikan dengan jumlah pos outbound.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Kriteria Penyeimbangan:
                    </label>
                    <select name="kriteria" class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-lg border border-slate-300 focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 bg-white">
                        <option value="seimbang_gender" selected>Seimbang Gender (Putra & Putri Merata)</option>
                        <option value="seimbang_cabang">Seimbang Cabang (Asal PCM Tidak Menumpuk)</option>
                        <option value="acak_murni">Acak Murni (Random Tanpa Filter)</option>
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Algoritma otomatis mendistribusikan peserta.</p>
                </div>

                <div>
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-mpksdi-600 hover:bg-mpksdi-700 text-white text-xs font-bold rounded-lg shadow-xs transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>{{ $kelompoks->isEmpty() ? 'Acak Kelompok Sekarang' : 'Acak Ulang Kelompok' }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Unassigned Warning -->
    @if($unassignedPeserta->isNotEmpty() && $kelompoks->isNotEmpty())
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-900 flex items-center justify-between">
        <div>
            <span class="font-bold">Perhatian:</span> Terdapat {{ $unassignedPeserta->count() }} peserta aktif baru yang belum masuk ke kelompok manapun.
        </div>
        <form method="POST" action="{{ route('admin.events.outbound.generate', $event) }}">
            @csrf
            <input type="hidden" name="jumlah_kelompok" value="{{ $kelompoks->count() }}">
            <input type="hidden" name="kriteria" value="seimbang_gender">
            <button type="submit" class="underline font-bold text-amber-900 hover:text-amber-800">
                Acak Ulang untuk Memasukkan Semua
            </button>
        </form>
    </div>
    @endif

    <!-- Group Cards Grid -->
    @if($kelompoks->isNotEmpty())
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($kelompoks as $kelompok)
        @php
            $males = $kelompok->peserta->filter(fn($p) => ($p->anggota->jenis_kelamin ?? 'L') === 'L')->count();
            $females = $kelompok->peserta->filter(fn($p) => ($p->anggota->jenis_kelamin ?? 'P') === 'P')->count();
        @endphp
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden flex flex-col">
            <div class="bg-slate-50/80 px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">{{ $kelompok->nama_kelompok }}</h3>
                    <span class="text-[10px] text-slate-500">{{ $kelompok->peserta->count() }} Anggota</span>
                </div>
                <div class="flex items-center gap-1.5 text-[10px] font-bold">
                    <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">{{ $males }} L</span>
                    <span class="px-2 py-0.5 rounded bg-pink-50 text-pink-700 border border-pink-200">{{ $females }} P</span>
                </div>
            </div>
            <div class="divide-y divide-slate-100 flex-1 overflow-y-auto max-h-80">
                @forelse($kelompok->peserta as $p)
                <div class="p-3 hover:bg-slate-50/50 transition flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-slate-800 truncate">{{ $p->anggota->nama_lengkap }}</div>
                        <div class="text-[10px] text-slate-400 truncate">{{ $p->anggota->kecamatan ?? '-' }} • {{ $p->anggota->jabatan_aum ?? 'Kader' }}</div>
                    </div>
                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded {{ ($p->anggota->jenis_kelamin ?? 'L') === 'L' ? 'bg-blue-50 text-blue-700' : 'bg-pink-50 text-pink-700' }}">
                        {{ $p->anggota->jenis_kelamin ?? 'L' }}
                    </span>
                </div>
                @empty
                <div class="p-6 text-center text-xs text-slate-400">
                    Belum ada anggota di kelompok ini.
                </div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="bg-white rounded-xl border border-dashed border-slate-300 p-12 text-center">
        <div class="w-12 h-12 rounded-full bg-mpksdi-50 text-mpksdi-700 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <h3 class="text-sm font-bold text-slate-800 mb-1">Kelompok Belum Dibentuk</h3>
        <p class="text-xs text-slate-500 max-w-md mx-auto mb-4">
            Tentukan jumlah kelompok dan kriteria pembagian di atas, lalu klik tombol "Acak Kelompok Sekarang" untuk membagi peserta secara adil.
        </p>
    </div>
    @endif
</div>
@endsection
