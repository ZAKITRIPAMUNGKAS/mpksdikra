@extends('layouts.app')

@section('title', $event->nama_event)

@section('content')
<div class="space-y-6">
    {{-- Breadcrumb & Event Hero Header --}}
    <div>
        <a href="{{ route('admin.events.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-mpksdi-700 transition mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar Event
        </a>

        <div class="bg-white rounded-xl border border-slate-200 p-6 sm:p-7 shadow-xs">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div>
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider
                            {{ $event->status === 'persiapan' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
                            {{ $event->status === 'berlangsung' ? 'bg-mpksdi-50 text-mpksdi-700 border border-mpksdi-200' : '' }}
                            {{ $event->status === 'selesai' ? 'bg-slate-100 text-slate-600 border border-slate-200' : '' }}">
                            Status: {{ $event->status }}
                        </span>
                        @if($event->lokasi)
                        <span class="text-xs text-slate-500 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ $event->lokasi }}
                        </span>
                        @endif
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">{{ $event->nama_event }}</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Jadwal: <span class="font-medium text-slate-700">{{ $event->tanggal_mulai->format('d M Y') }} &mdash; {{ $event->tanggal_selesai->format('d M Y') }}</span>
                    </p>
                </div>

                @if($canManage)
                <div class="flex items-center gap-2 flex-wrap">
                    <form method="POST" action="{{ route('admin.events.status', $event) }}">
                        @csrf
                        <select name="status" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 outline-none focus:ring-2 focus:ring-mpksdi-500 focus:bg-white cursor-pointer transition">
                            @foreach(['persiapan','berlangsung','selesai'] as $s)
                            <option value="{{ $s }}" @selected($event->status === $s)>Ubah: {{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </form>
                    <a href="{{ route('admin.events.edit', $event) }}" class="px-3 py-2 rounded-lg border border-slate-200 hover:border-slate-300 hover:bg-slate-50 text-xs font-semibold text-slate-700 transition">Edit Info</a>
                    <form method="POST" action="{{ route('admin.events.destroy', $event) }}" onsubmit="return confirm('Hapus kegiatan ini secara permanen?')">
                        @csrf @method('DELETE')
                        <button class="px-3 py-2 rounded-lg border border-red-200 text-red-700 hover:bg-red-50 text-xs font-semibold transition">Hapus</button>
                    </form>
                </div>
                @endif
            </div>

            {{-- Operational Sub-Navigation Pills --}}
            <div class="mt-6 pt-6 border-t border-slate-100 flex gap-2 flex-wrap text-xs">
                @if($canManage)
                <a href="{{ route('admin.events.enroll', $event) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-mpksdi-600 hover:bg-mpksdi-700 text-white font-bold shadow-xs transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <span>+ Enroll Peserta</span>
                </a>
                @endif
                <a href="{{ route('admin.events.soal.index', [$event, 'pretest']) }}" class="px-3 py-2 rounded-lg border border-slate-200 hover:border-mpksdi-400 hover:bg-mpksdi-50 hover:text-mpksdi-900 font-semibold text-slate-700 transition">Soal Pretest</a>
                <a href="{{ route('admin.events.soal.index', [$event, 'posttest']) }}" class="px-3 py-2 rounded-lg border border-slate-200 hover:border-mpksdi-400 hover:bg-mpksdi-50 hover:text-mpksdi-900 font-semibold text-slate-700 transition">Soal Posttest</a>
                <a href="{{ route('admin.events.psikomotor', $event) }}" class="px-3 py-2 rounded-lg border border-slate-200 hover:border-mpksdi-400 hover:bg-mpksdi-50 hover:text-mpksdi-900 font-semibold text-slate-700 transition">Rubrik Psikomotor</a>
                @if($canOperate)
                <a href="{{ route('admin.events.keaktifan', $event) }}" class="px-3 py-2 rounded-lg border border-slate-200 hover:border-mpksdi-400 hover:bg-mpksdi-50 hover:text-mpksdi-900 font-semibold text-slate-700 transition">Poin Keaktifan</a>
                <a href="{{ route('admin.events.outbound.index', $event) }}" class="px-3 py-2 rounded-lg border border-slate-200 hover:border-mpksdi-400 hover:bg-mpksdi-50 hover:text-mpksdi-900 font-semibold text-slate-700 transition">Kelompok Outbound</a>
                @endif
                @if($canManage)
                <a href="{{ route('admin.events.afektif', $event) }}" class="px-3 py-2 rounded-lg border border-slate-200 hover:border-mpksdi-400 hover:bg-mpksdi-50 hover:text-mpksdi-900 font-semibold text-slate-700 transition">Butir Afektif</a>
                <a href="{{ route('admin.events.angket', $event) }}" class="px-3 py-2 rounded-lg border border-slate-200 hover:border-mpksdi-400 hover:bg-mpksdi-50 hover:text-mpksdi-900 font-semibold text-slate-700 transition">Butir Angket</a>
                <a href="{{ route('admin.events.rtl.index', $event) }}" class="px-3 py-2 rounded-lg border border-slate-200 hover:border-mpksdi-400 hover:bg-mpksdi-50 hover:text-mpksdi-900 font-semibold text-slate-700 transition">Tinjau RTL</a>
                @endif
                <a href="{{ route('admin.events.ranking', $event) }}" class="px-3 py-2 rounded-lg border border-slate-200 hover:border-mpksdi-400 hover:bg-mpksdi-50 hover:text-mpksdi-900 font-semibold text-slate-700 transition">Rekap &amp; Ranking</a>
                <a href="{{ route('admin.events.report', $event) }}" class="px-3 py-2 rounded-lg border border-slate-200 hover:border-mpksdi-400 hover:bg-mpksdi-50 hover:text-mpksdi-900 font-semibold text-slate-700 transition">Laporan PDF</a>
                @if($canManage)
                <a href="{{ route('admin.events.exportExcel', $event) }}" class="px-3 py-2 rounded-lg border border-slate-200 hover:border-mpksdi-400 hover:bg-mpksdi-50 hover:text-mpksdi-900 font-semibold text-slate-700 transition">Ekspor Excel</a>
                @endif
            </div>
        </div>
    </div>

    {{-- Two Column Panels: Peserta & Sesi --}}
    <div class="grid lg:grid-cols-2 gap-6 items-start">
        {{-- Peserta Card --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <div>
                    <h2 class="font-bold text-slate-900 text-base">Peserta Terdaftar</h2>
                    <p class="text-xs text-slate-500">Total kader aktif dalam event ini</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                    {{ $peserta->count() }} Orang
                </span>
            </div>

            <div class="space-y-2.5 max-h-96 overflow-y-auto pr-1">
                @forelse($peserta as $p)
                <div class="flex items-center justify-between gap-3 p-3 rounded-lg border border-slate-100 bg-slate-50/40 hover:bg-white hover:border-slate-200 transition">
                    <div class="min-w-0">
                        <p class="font-bold text-sm text-slate-900 truncate">{{ $p->anggota->nama_lengkap }}</p>
                        <p class="text-[11px] text-slate-400">{{ $p->anggota->desa_kelurahan }}, {{ $p->anggota->kecamatan }}</p>
                    </div>
                    @if($canManage)
                    <div class="flex gap-1.5 shrink-0">
                        <a href="{{ route('admin.events.certificate', [$event, $p]) }}" class="px-2.5 py-1.5 rounded-lg border border-slate-200 hover:bg-mpksdi-50 hover:text-mpksdi-900 hover:border-mpksdi-300 text-[11px] font-semibold text-slate-600 transition">
                            Sertifikat
                        </a>
                        <form method="POST" action="{{ route('admin.events.peserta.remove', [$event, $p]) }}" onsubmit="return confirm('Nonaktifkan peserta dari event?')">
                            @csrf
                            <button class="px-2.5 py-1.5 rounded-lg border border-red-200 text-red-700 hover:bg-red-50 text-[11px] font-semibold transition">
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
                    <a href="{{ route('admin.events.enroll', $event) }}" class="inline-block mt-2 text-xs font-bold text-mpksdi-700 hover:underline">
                        + Enroll Peserta Sekarang
                    </a>
                    @endif
                </div>
                @endforelse
            </div>
        </div>

        {{-- Sesi Card --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <div>
                    <h2 class="font-bold text-slate-900 text-base">Jadwal Sesi Kegiatan</h2>
                    <p class="text-xs text-slate-500">Materi, ibadah sholat, dan outbound</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                    {{ $totalSesi }} Sesi
                </span>
            </div>

            @if($canManage)
            <form method="POST" action="{{ route('admin.events.sesi.store', $event) }}" class="flex gap-2 mb-4 flex-wrap">
                @csrf
                <input name="nama_sesi" placeholder="Nama sesi (contoh: Sholat Subuh, AIK)" required
                    class="flex-1 min-w-[150px] px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs outline-none focus:ring-2 focus:ring-mpksdi-500 focus:bg-white transition">
                <select name="jenis" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-semibold outline-none focus:ring-2 focus:ring-mpksdi-500 cursor-pointer transition">
                    <option value="materi">Materi</option>
                    <option value="sholat">Sholat</option>
                    <option value="outbound">Outbound</option>
                </select>
                <button class="px-4 py-2 bg-mpksdi-600 hover:bg-mpksdi-700 active:bg-mpksdi-800 text-white text-xs font-bold rounded-lg shadow-xs transition">
                    + Tambah
                </button>
            </form>
            @endif

            <div class="space-y-2.5 max-h-96 overflow-y-auto pr-1">
                @forelse($sesi as $s)
                <div class="flex items-center justify-between gap-2 p-3 rounded-lg border border-slate-100 bg-slate-50/40 hover:bg-white hover:border-slate-200 transition">
                    <div class="min-w-0">
                        <p class="font-bold text-xs sm:text-sm text-slate-900 truncate">
                            {{ $s->urutan }}. {{ $s->nama_sesi }}
                            <span class="text-[10px] text-slate-400 font-normal uppercase tracking-wider ml-1">({{ $s->jenis }})</span>
                        </p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Kehadiran: <b class="text-slate-700">{{ $s->absensi_count }}</b> / {{ $peserta->count() }} Kader</p>
                    </div>
                    <div class="flex gap-1.5 shrink-0">
                        @if($canOperate)
                        <a href="{{ route('admin.events.absensi.scan', [$event, $s]) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-mpksdi-50 text-mpksdi-900 hover:bg-mpksdi-100 border border-mpksdi-200 text-xs font-bold transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                            <span>Scan QR</span>
                        </a>
                        @endif
                        @if($canManage)
                        <form method="POST" action="{{ route('admin.events.sesi.destroy', [$event, $s]) }}" onsubmit="return confirm('Hapus sesi ini?')">
                            @csrf @method('DELETE')
                            <button class="px-2.5 py-1.5 rounded-lg border border-red-200 text-red-700 hover:bg-red-50 text-xs font-semibold transition">Hapus</button>
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
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 sm:p-7">
        <div class="mb-4">
            <h2 class="font-bold text-slate-900 text-base">Penugasan Fasilitator Event</h2>
            <p class="text-xs text-slate-500 mt-0.5">Fasilitator yang dicentang dapat melakukan presensi dan mengisi penilaian psikomotor event ini.</p>
        </div>

        <form method="POST" action="{{ route('admin.events.fasilitator.update', $event) }}" class="space-y-4">
            @csrf
            <div class="grid sm:grid-cols-2 gap-3">
                @forelse($fasilitatorOptions as $f)
                <label class="flex items-center gap-3 p-3.5 rounded-lg border border-slate-200 bg-slate-50/40 hover:bg-white hover:border-mpksdi-400 has-[:checked]:border-mpksdi-500 has-[:checked]:bg-mpksdi-50/40 cursor-pointer transition">
                    <input type="checkbox" name="fasilitator_ids[]" value="{{ $f->id }}"
                        @checked(in_array($f->id, $assignedFasilitatorIds))
                        class="w-4 h-4 rounded border-slate-300 text-mpksdi-600 focus:ring-mpksdi-500">
                    <div>
                        <span class="text-xs sm:text-sm font-semibold text-slate-900 block leading-tight">{{ $f->name }}</span>
                        <span class="text-[11px] text-slate-400">{{ $f->email }}</span>
                    </div>
                </label>
                @empty
                <div class="sm:col-span-2 py-6 text-center text-slate-400 text-xs">
                    Belum ada akun dengan role fasilitator yang terdaftar dalam sistem.
                </div>
                @endforelse
            </div>

            @if($fasilitatorOptions->count())
            <div class="pt-2 flex justify-end">
                <button class="px-5 py-2.5 bg-mpksdi-600 hover:bg-mpksdi-700 active:bg-mpksdi-800 text-white text-xs font-bold rounded-lg shadow-xs transition">
                    Simpan Penugasan Fasilitator
                </button>
            </div>
            @endif
        </form>
    </div>
    @endif
</div>
@endsection
