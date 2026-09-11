@extends('layouts.app')

@section('title', $event->nama_event)

@section('content')
<div class="space-y-6">
    {{-- Breadcrumb & Event Hero Header --}}
    <div>
        <a href="{{ route('admin.events.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-sky-600 transition mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar Event
        </a>

        <div class="bg-[#f3f5f8] rounded-3xl border border-sky-100/80 p-6 sm:p-8 shadow-xs relative overflow-hidden card-lift-sm">
            <x-batik-corner position="top-right" class="w-48 h-48 text-[#005c8a] opacity-[0.22]" />
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div>
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-2xs
                            {{ $event->status === 'persiapan' ? 'bg-amber-50 text-amber-800 border border-amber-300' : '' }}
                            {{ $event->status === 'berlangsung' ? 'bg-sky-50 text-sky-800 border border-sky-200' : '' }}
                            {{ $event->status === 'selesai' ? 'bg-slate-100 text-slate-600 border border-slate-200' : '' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $event->status === 'berlangsung' ? 'bg-sky-500 animate-ping' : ($event->status === 'persiapan' ? 'bg-amber-500' : 'bg-slate-400') }}"></span>
                            Status: {{ $event->status }}
                        </span>
                        @if($event->lokasi)
                        <span class="text-xs text-slate-600 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ $event->lokasi }}
                        </span>
                        @endif
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $event->nama_event }}</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 font-medium">
                        Jadwal: <span class="font-bold text-slate-800">{{ $event->tanggal_mulai->format('d M Y') }} &mdash; {{ $event->tanggal_selesai->format('d M Y') }}</span>
                    </p>
                </div>

                @if($canManage)
                <div class="flex items-center gap-2 flex-wrap">
                    <form method="POST" action="{{ route('admin.events.status', $event) }}">
                        @csrf
                        <select name="status" onchange="this.form.submit()" class="px-3.5 py-2 bg-white border border-slate-200/90 rounded-full text-xs font-bold text-slate-700 outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 cursor-pointer transition shadow-2xs">
                            @foreach(['persiapan','berlangsung','selesai'] as $s)
                            <option value="{{ $s }}" @selected($event->status === $s)>Ubah: {{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </form>
                    <a href="{{ route('admin.events.edit', $event) }}" class="px-4 py-2 rounded-full border border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 transition shadow-2xs">Edit Info</a>
                    <form method="POST" action="{{ route('admin.events.destroy', $event) }}" onsubmit="return confirm('Hapus kegiatan ini secara permanen?')">
                        @csrf @method('DELETE')
                        <button class="px-4 py-2 rounded-full border border-red-200 text-red-700 bg-white hover:bg-red-50 text-xs font-bold transition shadow-2xs">Hapus</button>
                    </form>
                </div>
                @endif
            </div>

            {{-- Operational Sub-Navigation Pills --}}
            <div class="mt-6 pt-6 border-t border-slate-200/60 flex gap-2 flex-wrap text-xs">
                @if($canManage)
                <a href="{{ route('admin.events.enroll', $event) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white font-black shadow-xs transition hover:scale-[1.01]">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <span>+ Enroll Peserta</span>
                </a>
                @endif
                <a href="{{ route('admin.events.soal.index', [$event, 'pretest']) }}" class="px-3.5 py-2 rounded-full border border-slate-200/90 bg-white hover:border-sky-400 hover:bg-sky-50 hover:text-sky-900 font-bold text-slate-700 transition shadow-2xs">Soal Pretest</a>
                <a href="{{ route('admin.events.soal.index', [$event, 'posttest']) }}" class="px-3.5 py-2 rounded-full border border-slate-200/90 bg-white hover:border-sky-400 hover:bg-sky-50 hover:text-sky-900 font-bold text-slate-700 transition shadow-2xs">Soal Posttest</a>
                <a href="{{ route('admin.events.psikomotor', $event) }}" class="px-3.5 py-2 rounded-full border border-slate-200/90 bg-white hover:border-sky-400 hover:bg-sky-50 hover:text-sky-900 font-bold text-slate-700 transition shadow-2xs">Rubrik Psikomotor</a>
                @if($canOperate)
                <a href="{{ route('admin.events.keaktifan', $event) }}" class="px-3.5 py-2 rounded-full border border-slate-200/90 bg-white hover:border-sky-400 hover:bg-sky-50 hover:text-sky-900 font-bold text-slate-700 transition shadow-2xs">Poin Keaktifan</a>
                <a href="{{ route('admin.events.outbound.index', $event) }}" class="px-3.5 py-2 rounded-full border border-slate-200/90 bg-white hover:border-sky-400 hover:bg-sky-50 hover:text-sky-900 font-bold text-slate-700 transition shadow-2xs">Kelompok Outbound</a>
                @endif
                @if($canManage)
                <a href="{{ route('admin.events.afektif', $event) }}" class="px-3.5 py-2 rounded-full border border-slate-200/90 bg-white hover:border-sky-400 hover:bg-sky-50 hover:text-sky-900 font-bold text-slate-700 transition shadow-2xs">Butir Afektif</a>
                <a href="{{ route('admin.events.angket', $event) }}" class="px-3.5 py-2 rounded-full border border-slate-200/90 bg-white hover:border-sky-400 hover:bg-sky-50 hover:text-sky-900 font-bold text-slate-700 transition shadow-2xs">Butir Angket</a>
                <a href="{{ route('admin.events.rtl.index', $event) }}" class="px-3.5 py-2 rounded-full border border-slate-200/90 bg-white hover:border-sky-400 hover:bg-sky-50 hover:text-sky-900 font-bold text-slate-700 transition shadow-2xs">Tinjau RTL</a>
                @endif
                <a href="{{ route('admin.events.ranking', $event) }}" class="px-3.5 py-2 rounded-full border border-slate-200/90 bg-white hover:border-sky-400 hover:bg-sky-50 hover:text-sky-900 font-bold text-slate-700 transition shadow-2xs">Rekap &amp; Ranking</a>
                <a href="{{ route('admin.events.report', $event) }}" class="px-3.5 py-2 rounded-full border border-slate-200/90 bg-white hover:border-sky-400 hover:bg-sky-50 hover:text-sky-900 font-bold text-slate-700 transition shadow-2xs">Laporan PDF</a>
                @if($canManage)
                <a href="{{ route('admin.events.exportExcel', $event) }}" class="px-3.5 py-2 rounded-full border border-slate-200/90 bg-white hover:border-sky-400 hover:bg-sky-50 hover:text-sky-900 font-bold text-slate-700 transition shadow-2xs">Ekspor Excel</a>
                @endif
            </div>
        </div>
    </div>

    {{-- Two Column Panels: Peserta & Sesi --}}
    <div class="grid lg:grid-cols-2 gap-6 items-start">
        {{-- Peserta Card --}}
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-7 card-lift-sm card-sheen hover:border-sky-400 transition">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <div>
                    <h2 class="font-black text-slate-900 text-base tracking-tight">Peserta Terdaftar</h2>
                    <p class="text-xs text-slate-500">Total kader aktif dalam event ini</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-800 border border-sky-200">
                    {{ $peserta->count() }} Orang
                </span>
            </div>

            <div class="space-y-2.5 max-h-96 overflow-y-auto pr-1">
                @forelse($peserta as $p)
                <div class="flex items-center justify-between gap-3 p-3.5 rounded-2xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-sky-200 transition">
                    <div class="min-w-0">
                        <p class="font-bold text-sm text-slate-900 truncate">{{ $p->anggota->nama_lengkap }}</p>
                        <p class="text-[11px] text-slate-400 font-medium">{{ $p->anggota->desa_kelurahan }}, {{ $p->anggota->kecamatan }}</p>
                    </div>
                    @if($canManage)
                    <div class="flex gap-1.5 shrink-0">
                        <a href="{{ route('admin.events.certificate', [$event, $p]) }}" class="px-3 py-1.5 rounded-full border border-slate-200 hover:bg-sky-50 hover:text-sky-900 hover:border-sky-300 text-[11px] font-bold text-slate-600 transition shadow-2xs">
                            Sertifikat
                        </a>
                        <form method="POST" action="{{ route('admin.events.peserta.remove', [$event, $p]) }}" onsubmit="return confirm('Nonaktifkan peserta dari event?')">
                            @csrf
                            <button class="px-3 py-1.5 rounded-full border border-red-200 text-red-700 hover:bg-red-50 text-[11px] font-bold transition shadow-2xs">
                                Hapus
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
                @empty
                <div class="text-center py-10 text-slate-400">
                    <p class="text-sm">Belum ada peserta yang di-enroll.</p>
                    @if($canManage)
                    <a href="{{ route('admin.events.enroll', $event) }}" class="inline-block mt-2 text-xs font-bold text-sky-600 hover:underline">
                        + Enroll Peserta Sekarang
                    </a>
                    @endif
                </div>
                @endforelse
            </div>
        </div>

        {{-- Sesi Card --}}
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-7 card-lift-sm card-sheen hover:border-sky-400 transition">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <div>
                    <h2 class="font-black text-slate-900 text-base tracking-tight">Jadwal Sesi Kegiatan</h2>
                    <p class="text-xs text-slate-500">Materi, ibadah sholat, dan outbound</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-800 border border-sky-200">
                    {{ $totalSesi }} Sesi
                </span>
            </div>

            @if($canManage)
            <form method="POST" action="{{ route('admin.events.sesi.store', $event) }}" class="flex gap-2 mb-4 flex-wrap">
                @csrf
                <input name="nama_sesi" placeholder="Nama sesi (contoh: Sholat Subuh, AIK)" required
                    class="flex-1 min-w-[150px] px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 focus:bg-white transition">
                <select name="jenis" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-sky-500/20 cursor-pointer transition">
                    <option value="materi">Materi</option>
                    <option value="sholat">Sholat</option>
                    <option value="outbound">Outbound</option>
                </select>
                <button class="px-4 py-2 bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white text-xs font-bold rounded-full shadow-xs transition hover:scale-[1.01]">
                    + Tambah
                </button>
            </form>
            @endif

            <div class="space-y-2.5 max-h-96 overflow-y-auto pr-1">
                @forelse($sesi as $s)
                <div class="flex items-center justify-between gap-2 p-3.5 rounded-2xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-sky-200 transition">
                    <div class="min-w-0">
                        <p class="font-bold text-xs sm:text-sm text-slate-900 truncate">
                            {{ $s->urutan }}. {{ $s->nama_sesi }}
                            <span class="text-[10px] text-slate-400 font-normal uppercase tracking-wider ml-1">({{ $s->jenis }})</span>
                        </p>
                        <p class="text-[11px] text-slate-500 mt-0.5 font-medium">Kehadiran: <b class="text-slate-700">{{ $s->absensi_count }}</b> / {{ $peserta->count() }} Kader</p>
                    </div>
                    <div class="flex gap-1.5 shrink-0">
                        @if($canOperate)
                        <a href="{{ route('admin.events.absensi.cetak', [$event, $s]) }}" title="Cetak Lembar Presensi Fisik" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-slate-50 hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 border border-slate-200 hover:border-emerald-200 text-xs font-bold transition shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            <span>Lembar PDF</span>
                        </a>
                        <a href="{{ route('admin.events.absensi.scan', [$event, $s]) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-sky-50 text-sky-900 hover:bg-sky-100 border border-sky-200 text-xs font-bold transition shadow-2xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                            <span>Scan QR</span>
                        </a>
                        @endif
                        @if($canManage)
                        <form method="POST" action="{{ route('admin.events.sesi.destroy', [$event, $s]) }}" onsubmit="return confirm('Hapus sesi ini?')">
                            @csrf @method('DELETE')
                            <button class="px-3 py-1.5 rounded-full border border-red-200 text-red-700 hover:bg-red-50 text-xs font-bold transition shadow-2xs">Hapus</button>
                        </form>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center py-10 text-slate-400">
                    <p class="text-sm">Belum ada sesi dijadwalkan.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Facilitators Assignment Card --}}
    @if($canManage)
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-7 card-lift-sm card-sheen hover:border-sky-400 transition">
        <div class="mb-5">
            <h2 class="font-black text-slate-900 text-base tracking-tight">Penugasan Fasilitator Event</h2>
            <p class="text-xs text-slate-500 mt-0.5">Fasilitator yang dicentang dapat melakukan presensi dan mengisi penilaian psikomotor event ini.</p>
        </div>

        <form method="POST" action="{{ route('admin.events.fasilitator.update', $event) }}" class="space-y-4">
            @csrf
            <div class="grid sm:grid-cols-2 gap-3">
                @forelse($fasilitatorOptions as $f)
                <label class="flex items-center gap-3 p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-sky-400 has-[:checked]:border-sky-500 has-[:checked]:bg-sky-50/40 cursor-pointer transition">
                    <input type="checkbox" name="fasilitator_ids[]" value="{{ $f->id }}"
                        @checked(in_array($f->id, $assignedFasilitatorIds))
                        class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                    <div>
                        <span class="text-xs sm:text-sm font-bold text-slate-900 block leading-tight">{{ $f->name }}</span>
                        <span class="text-[11px] text-slate-400 font-medium">{{ $f->email }}</span>
                    </div>
                </label>
                @empty
                <div class="sm:col-span-2 py-6 text-center text-slate-400 text-xs">
                    Belum ada akun dengan role fasilitator yang terdaftar dalam sistem.
                </div>
                @endforelse
            </div>

            @if($fasilitatorOptions->count())
            <div class="pt-3 flex justify-end">
                <button class="px-6 py-2.5 bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white text-xs font-bold rounded-full shadow-xs transition hover:scale-[1.01]">
                    Simpan Penugasan Fasilitator
                </button>
            </div>
            @endif
        </form>
    </div>
    @endif
</div>
@endsection
