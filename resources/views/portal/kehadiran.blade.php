@extends('layouts.app')

@section('title', 'ID Card & Kehadiran — ' . $event->nama_event)

@section('content')
<div class="max-w-4xl mx-auto space-y-4 sm:space-y-6" x-data="{ mobileTab: 'qr' }">
    {{-- Compact Top Bar --}}
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('portal.dashboard') }}" class="min-h-11 inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-slate-200/90 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali</span>
        </a>
        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ count($hadir) === $sesi->count() && $sesi->count() > 0 ? 'bg-emerald-50 text-emerald-800 border border-emerald-300/80' : 'bg-white text-slate-700 border border-slate-200' }}">
            Hadir: {{ count($hadir) }}/{{ $sesi->count() }} Sesi
        </span>
    </div>

    <div>
        <h1 class="text-lg sm:text-2xl font-bold text-slate-900 tracking-tight">{{ $event->nama_event }}</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kartu Tanda Peserta &amp; Presensi Sesi Perkaderan</p>
    </div>

    {{-- Mobile Tab Switcher (Membuat QR & Jadwal sama-sama 1 tap di HP) --}}
    <div class="md:hidden grid grid-cols-2 gap-1.5 p-1 bg-slate-200/70 rounded-xl">
        <button type="button" @click="mobileTab = 'qr'"
            :class="mobileTab === 'qr' ? 'bg-white text-[#005c8a] shadow-xs font-bold' : 'text-slate-600 font-semibold'"
            class="min-h-11 rounded-lg text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
            <span>Kartu QR</span>
        </button>
        <button type="button" @click="mobileTab = 'sesi'"
            :class="mobileTab === 'sesi' ? 'bg-white text-[#005c8a] shadow-xs font-bold' : 'text-slate-600 font-semibold'"
            class="min-h-11 rounded-lg text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
            <span>Jadwal &amp; Materi ({{ $sesi->count() }})</span>
        </button>
    </div>

    <div class="grid md:grid-cols-2 gap-5 sm:gap-6 items-start">
        {{-- Digital Card Pass --}}
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden" :class="mobileTab === 'qr' ? 'block' : 'hidden md:block'">
            <div class="bg-gradient-to-b from-slate-950 via-[#003d5c] to-[#005c8a] p-4 sm:p-6 text-white text-center relative border-b-2 border-[#005c8a]">
                <img src="{{ asset('aset/logo-mpksdi.webp') }}" alt="Logo MPKSDI" class="h-8 sm:h-10 w-auto mx-auto mb-1.5 drop-shadow-xs">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-white/10 text-[11px] font-semibold tracking-wider text-amber-300 uppercase mb-1.5 border border-white/15">
                    Kartu Peserta Baitul Arqam
                </div>
                <h2 class="text-base sm:text-lg font-bold leading-tight">{{ $ep->anggota->nama_lengkap }}</h2>
                <p class="text-xs text-slate-300 mt-0.5">NIK: {{ substr($ep->anggota->nik, 0, 6) }}******</p>
            </div>

            <div class="p-5 sm:p-6 text-center">
                <div class="inline-block p-3.5 bg-white border-2 border-dashed border-sky-300 rounded-xl shadow-2xs">
                    {!! $qrSvg !!}
                </div>
                <p class="text-xs text-slate-500 mt-3 max-w-xs mx-auto font-normal">
                    Tunjukkan QR Code ini ke petugas pemindai presensi di setiap sesi kegiatan.
                </p>

                <div class="mt-5 pt-4 border-t border-slate-100">
                    <a href="{{ route('portal.idcard.download', $event) }}" class="min-h-12 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white text-xs sm:text-sm font-bold shadow-2xs transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Unduh ID Card PDF Resmi</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Attendance Timeline --}}
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs p-4 sm:p-6" :class="mobileTab === 'sesi' ? 'block' : 'hidden md:block'">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <div>
                    <h2 class="font-bold text-slate-900 text-base tracking-tight">Rekap Kehadiran Sesi</h2>
                    <p class="text-xs text-slate-500 font-normal">Syarat kelulusan: 100% penuh / 75% bersyarat</p>
                </div>
                <span class="px-2.5 py-0.5 rounded-md text-xs font-semibold {{ count($hadir) === $sesi->count() && $sesi->count() > 0 ? 'bg-emerald-50 text-emerald-800 border border-emerald-300/80' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                    {{ count($hadir) }} / {{ $sesi->count() }} Sesi
                </span>
            </div>

            <div class="space-y-2.5 max-h-[26rem] overflow-y-auto pr-1">
                @forelse($sesi as $s)
                <div class="p-3 rounded-lg border border-slate-100 bg-slate-50/60 hover:bg-white hover:border-sky-200 hover:shadow-2xs transition">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs sm:text-sm font-semibold text-slate-900 leading-snug">{{ $s->urutan }}. {{ $s->nama_sesi }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                <span class="capitalize font-medium text-slate-600">{{ $s->jenis }}</span>
                                @if($s->pemateri) &bull; Narasumber: <span class="font-medium text-slate-700">{{ $s->pemateri }}</span>@endif
                                @if($s->jam_mulai) &bull; <span class="text-slate-500">{{ $s->jam_mulai }} @if($s->jam_selesai)- {{ $s->jam_selesai }}@endif WIB</span>@endif
                            </p>
                        </div>
                        @if(in_array($s->id, $hadir))
                            <span class="shrink-0 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-semibold">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Hadir</span>
                            </span>
                        @else
                            <span class="shrink-0 px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-500 text-xs font-medium">
                                Belum Hadir
                            </span>
                        @endif
                    </div>

                    @if($s->file_materi || $s->link_materi || $s->deskripsi)
                    <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between gap-2 flex-wrap text-xs">
                        <div class="text-slate-500 text-[11px] truncate max-w-xs">
                            {{ $s->deskripsi ?: 'Bahan tayang / modul sesi' }}
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            @if($s->file_materi)
                            <a href="{{ route('sesi.materi.download', $s) }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-sky-50 text-sky-800 border border-sky-200 hover:bg-sky-100 text-[11px] font-semibold transition">
                                <svg class="w-3 h-3 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                <span>Unduh Slide/Modul</span>
                            </a>
                            @endif
                            @if($s->link_materi)
                            <a href="{{ $s->link_materi }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200 hover:bg-slate-200 text-[11px] font-semibold transition">
                                <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                <span>Tautan Materi</span>
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
                @empty
                <div class="text-center py-10 text-slate-400">
                    <p class="text-xs">Belum ada sesi yang dijadwalkan untuk kegiatan ini.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Keaktifan Kader Transparency Card --}}
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs p-5 sm:p-6">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 flex-wrap gap-2">
            <div>
                <h2 class="font-bold text-slate-900 text-base tracking-tight">Rekam Keaktifan &amp; Partisipasi</h2>
                <p class="text-xs text-slate-500 font-normal">Poin keaktifan yang dicatat oleh fasilitator selama forum berlangsung</p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 text-amber-900 border border-amber-300 text-xs font-bold">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                Total: {{ isset($keaktifanList) ? $keaktifanList->count() : 0 }} Poin
            </span>
        </div>

        @if(isset($keaktifanList) && $keaktifanList->isNotEmpty())
        <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-3">
            @foreach($keaktifanList as $k)
            <div class="p-3 rounded-lg border border-slate-200 bg-slate-50/60 flex items-start justify-between gap-2">
                <div>
                    <span class="text-xs font-bold text-slate-900 capitalize block">{{ str_replace('_', ' ', $k->jenis) }}</span>
                    <span class="text-[11px] text-slate-500">{{ $k->keterangan ?: 'Tercatat dalam forum' }}</span>
                </div>
                <span class="px-2 py-0.5 rounded text-[11px] font-extrabold bg-sky-100 text-sky-800 shrink-0">+1 Poin</span>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-6 text-slate-400 text-xs">
            Belum ada catatan keaktifan. Berpartisipasilah secara aktif dalam tanya-jawab, diskusi materi, kultum, ibadah sholat, atau dinamika kelompok untuk memperoleh poin tambahan.
        </div>
        @endif
    </div>
</div>
@endsection

