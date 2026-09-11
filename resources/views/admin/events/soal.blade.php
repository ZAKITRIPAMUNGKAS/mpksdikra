@extends('layouts.app')

@section('title', ucfirst($tipe) . ' — ' . $event->nama_event)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-mpksdi-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke {{ $event->nama_event }}
    </a>
</div>

<div class="bg-white rounded-xl border border-slate-200 p-6 sm:p-7 shadow-xs mb-6">
    <div class="flex items-center justify-between gap-4 flex-wrap mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Bank Soal {{ ucfirst($tipe) }} ({{ $soal->count() }})</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola butir soal pilihan ganda dan status sesi pengerjaan peserta</p>
        </div>
        <div class="flex gap-2">
            @if($sesi && $sesi->status === 'aktif')
                <form method="POST" action="{{ route('admin.events.soal.close', [$event, $tipe]) }}">
                    @csrf
                    <button class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-lg shadow-xs transition">Tutup {{ ucfirst($tipe) }}</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.events.soal.open', [$event, $tipe]) }}" class="flex gap-2 items-center">
                    @csrf
                    <div class="flex items-center gap-1.5">
                        <label class="text-xs text-slate-500 font-medium">Durasi:</label>
                        <input type="number" name="durasi_menit" value="{{ $sesi->durasi_menit ?? 30 }}" min="5" max="240" required
                            class="w-20 px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-bold text-center outline-none focus:bg-white focus:ring-2 focus:ring-mpksdi-500 transition">
                        <span class="text-xs text-slate-500">menit</span>
                    </div>
                    <button class="px-4 py-2 bg-mpksdi-600 hover:bg-mpksdi-700 text-white text-xs font-bold rounded-lg shadow-xs transition">Buka {{ ucfirst($tipe) }}</button>
                </form>
            @endif
        </div>
    </div>

    <form method="POST" action="{{ route('admin.events.soal.store', [$event, $tipe]) }}" class="border border-slate-200 rounded-lg p-5 bg-slate-50/40"
        x-data="{ pilihan: [{teks: '', is_benar: true}, {teks: '', is_benar: false}, {teks: '', is_benar: false}, {teks: '', is_benar: false}] }">
        @csrf
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tambah Soal Baru</label>
        <textarea name="teks_soal" rows="2" required placeholder="Tuliskan pertanyaan soal…"
            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg text-xs outline-none focus:ring-2 focus:ring-mpksdi-500 transition resize-none mb-3">{{ old('teks_soal') }}</textarea>

        <template x-for="(p, i) in pilihan" :key="i">
            <div class="flex items-center gap-2 mb-2">
                <input type="radio" name="benar_idx" :value="i" x-model.number="pilihan.findIndex(x => x.is_benar)" hidden>
                <span class="w-7 h-7 grid place-items-center rounded-md bg-slate-200 text-xs font-bold text-slate-700 font-mono" x-text="['A','B','C','D','E','F'][i]"></span>
                <input :name="`pilihan[${i}][teks]`" x-model="p.teks" required placeholder="Teks pilihan…"
                    class="flex-1 px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs outline-none focus:ring-2 focus:ring-mpksdi-500 transition">
                <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-600 shrink-0 cursor-pointer">
                    <input type="checkbox" :name="`pilihan[${i}][is_benar]`" :value="1" x-model="p.is_benar" @change="pilihan.forEach((x, j) => { if (j !== i) x.is_benar = false })" class="rounded border-slate-300 text-mpksdi-600 focus:ring-mpksdi-500">
                    Kunci Benar
                </label>
                <button type="button" @click="pilihan.length > 2 && pilihan.splice(i, 1)" class="text-slate-400 hover:text-red-600 text-base font-bold px-2 transition">&times;</button>
            </div>
        </template>

        <div class="flex gap-2 mt-4 pt-3 border-t border-slate-200/80">
            <button type="button" @click="pilihan.length < 6 && pilihan.push({teks: '', is_benar: false})"
                class="px-4 py-2 border border-slate-300 text-xs font-semibold text-slate-700 rounded-lg hover:bg-white transition">+ Tambah Pilihan</button>
            <button class="px-5 py-2 bg-mpksdi-600 hover:bg-mpksdi-700 text-white text-xs font-bold rounded-lg shadow-xs transition">Simpan Butir Soal</button>
        </div>
    </form>
</div>

<div class="space-y-3">
    @forelse($soal as $i => $s)
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs">
        <div class="flex items-start justify-between gap-4">
            <p class="font-semibold text-slate-900 text-xs sm:text-sm leading-relaxed">{{ $i + 1 }}. {{ $s->teks_soal }}</p>
            <form method="POST" action="{{ route('admin.events.soal.destroy', [$event, $s]) }}" onsubmit="return confirm('Hapus butir soal ini?')">
                @csrf @method('DELETE')
                <button class="px-2.5 py-1 rounded-lg border border-red-200 text-red-700 text-xs font-semibold hover:bg-red-50 shrink-0 transition">Hapus</button>
            </form>
        </div>
        <div class="mt-3 grid sm:grid-cols-2 gap-2">
            @foreach($s->pilihan as $p)
            <div class="flex items-center gap-2 text-xs p-2.5 rounded-lg {{ $p->is_benar ? 'bg-mpksdi-50 text-mpksdi-900 border border-mpksdi-200 font-semibold' : 'bg-slate-50 text-slate-700 border border-slate-100' }}">
                <span class="w-5 h-5 grid place-items-center rounded bg-white text-[10px] font-bold {{ $p->is_benar ? 'text-mpksdi-700 border border-mpksdi-200' : 'text-slate-500 border border-slate-200' }}">{{ $p->label }}</span>
                <span class="min-w-0">{{ $p->teks }}</span>
                @if($p->is_benar)<span class="ml-auto text-[10px] font-bold text-mpksdi-700 bg-white px-1.5 py-0.5 rounded border border-mpksdi-200">✓ KUNCI</span>@endif
            </div>
            @endforeach
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-slate-200 p-10 text-center text-slate-400 text-xs">Belum ada soal {{ $tipe }} yang ditambahkan.</div>
    @endforelse
</div>
@endsection
