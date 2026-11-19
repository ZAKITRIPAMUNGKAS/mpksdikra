@extends('layouts.app')

@section('title', 'Presensi Checklist: ' . $sesi->nama_sesi . ' — ' . $event->nama_event)

@section('content')
<div class="space-y-6" x-data="{
    search: '',
    statusFilter: 'all',
    matches(nama, nbm, hadir) {
        const q = this.search.toLowerCase().trim();
        const matchText = !q || nama.toLowerCase().includes(q) || nbm.toLowerCase().includes(q);
        const matchStatus = this.statusFilter === 'all' ||
            (this.statusFilter === 'hadir' && hadir) ||
            (this.statusFilter === 'belum' && !hadir);
        return matchText && matchStatus;
    }
}">

    {{-- Breadcrumb & Back --}}
    <div class="flex items-center justify-between gap-3 flex-wrap">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 transition shadow-2xs">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Kembali ke {{ $event->nama_event }}</span>
            </a>
            <span class="text-slate-300">/</span>
            <span class="text-xs font-medium text-slate-500">Sesi {{ sprintf('%02d', $sesi->urutan) }}</span>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.events.absensi.scan', [$event, $sesi]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-sky-50 text-sky-900 hover:bg-sky-100 border border-sky-200 text-xs font-semibold transition shadow-2xs">
                <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                <span>Mode Scanner QR</span>
            </a>
            <a href="{{ route('admin.events.absensi.cetak', [$event, $sesi]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-semibold transition shadow-2xs">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Lembar PDF</span>
            </a>
        </div>
    </div>

    {{-- Session Banner & Statistics --}}
    <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs relative overflow-hidden">
        <x-batik-corner position="top-right" class="w-36 h-36 text-[#005c8a] opacity-[0.20]" />
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-medium bg-amber-50 text-amber-800 border border-amber-200 mb-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    Presensi Manual / Checklist
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                    {{ $sesi->nama_sesi }}
                </h1>
                <p class="text-xs text-slate-500 font-medium flex items-center gap-2 flex-wrap">
                    <span>Sesi {{ sprintf('%02d', $sesi->urutan) }} &bull; {{ ucfirst($sesi->jenis) }}</span>
                    @if($sesi->pemateri)<span class="text-slate-300">&bull;</span><span><b>Narasumber:</b> {{ $sesi->pemateri }}</span>@endif
                    @if($sesi->tanggal)<span class="text-slate-300">&bull;</span><span>{{ $sesi->tanggal->format('d/m/Y') }}</span>@endif
                    @if($sesi->jam_mulai)<span class="text-slate-300">&bull;</span><span>{{ $sesi->jam_mulai }} @if($sesi->jam_selesai)- {{ $sesi->jam_selesai }}@endif WIB</span>@endif
                </p>
            </div>

            {{-- Metric Card --}}
            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 min-w-[240px] shrink-0 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-semibold uppercase tracking-wider">Tingkat Kehadiran</span>
                    <span class="font-extrabold text-[#063A60] text-sm">
                        {{ $totalHadir }} / {{ $totalPeserta }}
                        <span class="text-slate-400 font-normal">({{ $totalPeserta > 0 ? round(($totalHadir / $totalPeserta) * 100) : 0 }}%)</span>
                    </span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-emerald-500 h-2.5 rounded-full transition-all duration-300"
                         style="width: {{ $totalPeserta > 0 ? min(100, round(($totalHadir / $totalPeserta) * 100)) : 0 }}%"></div>
                </div>
                <p class="text-[11px] text-slate-500">
                    {{ $totalPeserta - $totalHadir }} peserta belum tercatat hadir.
                </p>
            </div>
        </div>
    </div>

    {{-- Fast Actions Toolbar (1-Click Bulk & Copy Previous Session) --}}
    <div class="bg-gradient-to-r from-sky-50 via-indigo-50 to-emerald-50 rounded-xl border border-sky-200/80 p-4 sm:p-5 shadow-2xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="space-y-1">
                <span class="text-xs font-bold uppercase tracking-wider text-sky-900 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Aksi Cepat Presensi (Sesi Berdekatan)
                </span>
                <p class="text-xs text-slate-600 max-w-xl">
                    Jika ada 2 sesi berurutan di ruangan yang sama, Anda tidak perlu scan ulang. Gunakan tombol centang semua atau salin dari sesi sebelumnya.
                </p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                {{-- Bulk All Button --}}
                <form method="POST" action="{{ route('admin.events.absensi.bulk-all', [$event, $sesi]) }}"
                      onsubmit="return confirm('Tandai SEMUA {{ $totalPeserta - $totalHadir }} peserta yang belum hadir menjadi HADIR pada sesi ini?')">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-2xs transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>⚡ Tandai Semua Hadir ({{ $totalPeserta - $totalHadir }})</span>
                    </button>
                </form>

                {{-- Copy from Previous Session Dropdown Form --}}
                @if($previousSessions->isNotEmpty())
                <form method="POST" action="{{ route('admin.events.absensi.copy', [$event, $sesi]) }}"
                      class="inline-flex items-center gap-1.5 bg-white p-1 rounded-lg border border-slate-300 shadow-2xs"
                      onsubmit="return confirm('Salin daftar kehadiran dari sesi terpilih ke sesi ini?')">
                    @csrf
                    <select name="source_sesi_id" required class="text-xs font-semibold text-slate-700 border-none bg-transparent outline-none py-1 px-2 cursor-pointer focus:ring-0">
                        <option value="">-- Salin dari Sesi Lain --</option>
                        @foreach($previousSessions as $ps)
                        <option value="{{ $ps->id }}">Sesi {{ $ps->urutan }}: {{ Str::limit($ps->nama_sesi, 28) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-md transition cursor-pointer">
                        Salin
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Main Checklist Table Card --}}
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
        {{-- Search and Filter Toolbar --}}
        <div class="p-4 sm:p-5 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="relative flex-1 max-w-sm">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       x-model="search"
                       placeholder="Cari nama peserta atau NBM..."
                       class="w-full pl-9 pr-3.5 py-2 bg-slate-50 border border-slate-200/90 rounded-lg text-xs outline-none focus:bg-white focus:ring-2 focus:ring-[#063A60]/20 focus:border-[#063A60] transition">
            </div>

            <div class="flex items-center gap-1.5">
                <button type="button"
                        @click="statusFilter = 'all'"
                        :class="statusFilter === 'all' ? 'bg-[#063A60] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer">
                    Semua ({{ $totalPeserta }})
                </button>
                <button type="button"
                        @click="statusFilter = 'hadir'"
                        :class="statusFilter === 'hadir' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer">
                    Hadir ({{ $totalHadir }})
                </button>
                <button type="button"
                        @click="statusFilter = 'belum'"
                        :class="statusFilter === 'belum' ? 'bg-rose-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer">
                    Belum ({{ $totalPeserta - $totalHadir }})
                </button>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider text-left">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Peserta</th>
                        <th class="py-3 px-4">Cabang / Instansi</th>
                        <th class="py-3 px-4 text-center">Status Kehadiran</th>
                        <th class="py-3 px-4 text-center">Waktu Presensi</th>
                        <th class="py-3 px-4 text-center w-36">Aksi Checklist</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($peserta as $index => $p)
                    @php
                        $hadirRecord = $absensiMap->get($p->anggota_id);
                        $isHadir = !is_null($hadirRecord);
                        $nama = $p->anggota->nama_lengkap ?? '-';
                        $nbm = $p->anggota->nbm ?? '-';
                        $foto = $p->anggota->foto_url ?? null;
                    @endphp
                    <tr x-show="matches('{{ addslashes($nama) }}', '{{ addslashes($nbm) }}', {{ $isHadir ? 'true' : 'false' }})"
                        class="hover:bg-slate-50/80 transition {{ $isHadir ? 'bg-emerald-50/20' : '' }}">
                        <td class="py-3 px-4 text-center font-mono text-slate-400">
                            {{ $index + 1 }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-slate-200 shrink-0 overflow-hidden border border-slate-300/80 flex items-center justify-center font-bold text-slate-600 text-xs">
                                    @if($foto)
                                    <img src="{{ $foto }}" alt="{{ $nama }}" class="w-full h-full object-cover">
                                    @else
                                    {{ strtoupper(substr($nama, 0, 2)) }}
                                    @endif
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs sm:text-sm">{{ $nama }}</div>
                                    <div class="font-mono text-[11px] text-slate-500">NBM: {{ $nbm }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            {{ $p->anggota->cabang ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($isHadir)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Hadir ({{ ucfirst($hadirRecord->metode ?? 'manual') }})</span>
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-500 border border-slate-200">
                                Belum Hadir
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center font-mono text-[11px] text-slate-500">
                            @if($isHadir && $hadirRecord->waktu_scan)
                            {{ \Carbon\Carbon::parse($hadirRecord->waktu_scan)->format('H:i:s') }} WIB
                            @else
                            -
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <form method="POST" action="{{ route('admin.events.absensi.toggle', [$event, $sesi, $p->anggota_id]) }}">
                                @csrf
                                @if($isHadir)
                                <button type="submit"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-white hover:bg-rose-50 text-rose-700 hover:text-rose-800 border border-rose-200 text-xs font-semibold transition shadow-2xs cursor-pointer"
                                        title="Klik untuk membatalkan status hadir">
                                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    <span>Batal Hadir</span>
                                </button>
                                @else
                                <button type="submit"
                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-2xs cursor-pointer"
                                        title="Klik untuk menandai hadir">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Centang Hadir</span>
                                </button>
                                @endif
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-slate-400">
                            Belum ada peserta terdaftar pada kegiatan ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
