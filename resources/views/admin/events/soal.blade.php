@extends('layouts.app')

@section('title', ucfirst($tipe) . ' — ' . $event->nama_event)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 transition shadow-2xs">
        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span>Kembali ke {{ $event->nama_event }}</span>
    </a>
</div>

<div class="bg-white rounded-xl border border-slate-200/90 p-6 sm:p-7 shadow-2xs mb-6 relative overflow-hidden">
    <x-batik-corner position="top-right" class="w-40 h-40 text-[#005c8a] opacity-[0.22]" />
    <div class="relative z-10">
        <div class="flex items-center justify-between gap-4 flex-wrap mb-5">
            <div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-medium bg-sky-50 text-sky-800 border border-sky-200 mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
                    Bank Soal Evaluasi Kognitif
                </span>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Bank Soal {{ ucfirst($tipe) }} ({{ $soal->count() }})</h1>
                <p class="text-xs text-slate-600 mt-0.5 font-normal">Kelola butir soal pilihan ganda dan status sesi pengerjaan peserta</p>
            </div>
            <div class="flex gap-2 items-center flex-wrap">
                @if(isset($otherEvents) && $otherEvents->isNotEmpty())
                <form method="POST" action="{{ route('admin.events.soal.clone', [$event, $tipe]) }}" class="flex items-center gap-1.5 flex-wrap" onsubmit="return confirm('Salin soal dari kegiatan dan jenis tes yang dipilih ke {{ $tipe }} kegiatan ini?')">
                    @csrf
                    <select name="source_event_id" required class="px-3 py-1.5 bg-slate-50 border border-slate-200/90 rounded-lg text-xs font-semibold text-slate-700 outline-none focus:ring-2 focus:ring-sky-500/20">
                        <option value="">-- Salin dari Kegiatan Lain --</option>
                        @foreach($otherEvents as $oe)
                        <option value="{{ $oe->id }}">{{ $oe->nama_event }}</option>
                        @endforeach
                    </select>
                    <select name="source_tipe" required aria-label="Jenis soal sumber" class="px-3 py-1.5 bg-slate-50 border border-slate-200/90 rounded-lg text-xs font-semibold text-slate-700 outline-none focus:ring-2 focus:ring-sky-500/20">
                        <option value="pretest" @selected($tipe === 'pretest')>Dari Pretest</option>
                        <option value="posttest" @selected($tipe === 'posttest')>Dari Posttest</option>
                    </select>
                    <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-sky-50 text-sky-800 border border-slate-200 hover:border-sky-300 text-xs font-semibold rounded-lg transition shadow-2xs">
                        Salin Soal
                    </button>
                </form>
                @else
                <p class="max-w-64 text-xs text-slate-500" role="status">Belum ada BA lain sebagai sumber. Buat atau pilih BA lain untuk menyalin soal.</p>
                @endif

                @if($sesi && $sesi->status === 'aktif')
                    <form method="POST" action="{{ route('admin.events.soal.close', [$event, $tipe]) }}">
                        @csrf
                        <button class="px-4 py-2 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white text-xs font-semibold rounded-lg shadow-2xs transition">Tutup {{ ucfirst($tipe) }}</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.events.soal.open', [$event, $tipe]) }}" class="flex gap-2 items-center">
                        @csrf
                        <div class="flex items-center gap-1.5 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 shadow-2xs">
                            <label class="text-xs text-slate-600 font-semibold">Durasi:</label>
                            <input type="number" name="durasi_menit" value="{{ $sesi->durasi_menit ?? 30 }}" min="5" max="240" required
                                class="w-14 px-2 py-0.5 bg-white border border-slate-200 rounded text-xs font-bold text-center outline-none focus:ring-2 focus:ring-sky-500/20 transition">
                            <span class="text-xs text-slate-500 font-normal">menit</span>
                        </div>
                        <button class="px-4 py-2 bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white text-xs font-semibold rounded-lg shadow-2xs transition">Buka {{ ucfirst($tipe) }}</button>
                    </form>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('admin.events.soal.store', [$event, $tipe]) }}" class="border border-slate-200 rounded-lg p-5 bg-slate-50/50 shadow-2xs"
            x-data="{ pilihan: [{teks: '', is_benar: true}, {teks: '', is_benar: false}, {teks: '', is_benar: false}, {teks: '', is_benar: false}] }">
            @csrf
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Tambah Butir Soal Baru</label>
            <textarea name="teks_soal" rows="2" required placeholder="Tuliskan pertanyaan soal…"
                class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-lg text-xs outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition resize-none mb-3">{{ old('teks_soal') }}</textarea>

            <template x-for="(p, i) in pilihan" :key="i">
                <div class="flex items-center gap-2 mb-2">
                    <input type="radio" name="benar_idx" :value="i" x-model.number="pilihan.findIndex(x => x.is_benar)" hidden>
                    <span class="w-8 h-8 grid place-items-center rounded-lg bg-white text-xs font-bold text-slate-700 font-mono border border-slate-200" x-text="['A','B','C','D','E','F'][i]"></span>
                    <input :name="`pilihan[${i}][teks]`" x-model="p.teks" required placeholder="Teks pilihan…"
                        class="flex-1 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition">
                    <label class="flex items-center gap-1.5 text-xs font-medium text-slate-700 shrink-0 cursor-pointer bg-white px-3 py-1.5 rounded-lg border border-slate-200">
                        <input type="checkbox" :name="`pilihan[${i}][is_benar]`" :value="1" x-model="p.is_benar" @change="pilihan.forEach((x, j) => { if (j !== i) x.is_benar = false })" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                        <span>Kunci Benar</span>
                    </label>
                    <button type="button" @click="pilihan.length > 2 && pilihan.splice(i, 1)" class="text-slate-400 hover:text-red-600 text-base font-bold px-2 transition">&times;</button>
                </div>
            </template>

            <div class="flex gap-2 mt-4 pt-3 border-t border-slate-200/80">
                <button type="button" @click="pilihan.length < 6 && pilihan.push({teks: '', is_benar: false})"
                    class="px-3.5 py-1.5 border border-slate-200 text-xs font-semibold text-slate-700 rounded-lg bg-white hover:bg-slate-50 transition">+ Tambah Pilihan</button>
                <button class="px-4 py-1.5 bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white text-xs font-semibold rounded-lg shadow-2xs transition">Simpan Butir Soal</button>
            </div>
        </form>
    </div>
</div>

<div class="space-y-3">
    @forelse($soal as $i => $s)
    <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-2xs">
        <div class="flex items-start justify-between gap-4">
            <p class="font-semibold text-slate-900 text-xs sm:text-sm leading-relaxed">{{ $i + 1 }}. {{ $s->teks_soal }}</p>
            <form method="POST" action="{{ route('admin.events.soal.destroy', [$event, $s]) }}" onsubmit="return confirm('Hapus butir soal ini?')">
                @csrf @method('DELETE')
                <button class="px-2.5 py-1 rounded-md border border-red-200 text-red-700 text-xs font-semibold hover:bg-red-50 shrink-0 transition shadow-2xs">Hapus</button>
            </form>
        </div>
        <div class="mt-3.5 grid sm:grid-cols-2 gap-2">
            @foreach($s->pilihan as $p)
            <div class="flex items-center gap-2 text-xs p-2.5 rounded-lg {{ $p->is_benar ? 'bg-sky-50/80 text-sky-950 border border-sky-300 font-medium' : 'bg-slate-50/60 text-slate-700 border border-slate-200' }}">
                <span class="w-5 h-5 grid place-items-center rounded bg-white text-xs font-bold {{ $p->is_benar ? 'text-sky-700 border border-sky-300' : 'text-slate-500 border border-slate-200' }}">{{ $p->label }}</span>
                <span class="min-w-0">{{ $p->teks }}</span>
                @if($p->is_benar)<span class="ml-auto text-xs font-semibold text-sky-700 bg-white px-2 py-0.5 rounded border border-sky-300 shadow-2xs">✓ KUNCI</span>@endif
            </div>
            @endforeach
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-slate-200/90 p-10 text-center text-slate-400 text-xs shadow-2xs font-normal">Belum ada soal {{ $tipe }} yang ditambahkan.</div>
    @endforelse
</div>
@endsection
