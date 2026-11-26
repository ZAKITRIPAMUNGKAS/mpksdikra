@extends('layouts.app')

@section('title', 'RTL — ' . $event->nama_event)

@section('content')
<div class="max-w-3xl mx-auto space-y-4 sm:space-y-6">
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('portal.dashboard') }}" class="min-h-11 inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-slate-200/90 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali</span>
        </a>
        @if($rtl)
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-wider shadow-2xs
            {{ $rtl->status === 'terkirim' ? 'bg-sky-50 text-sky-900 border border-sky-300/80' : 'bg-amber-50 text-amber-900 border border-amber-300/80' }}">
            <span class="w-1.5 h-1.5 rounded-full {{ $rtl->status === 'terkirim' ? 'bg-[#005c8a]' : 'bg-amber-500' }}"></span>
            {{ ucfirst($rtl->status) }}
        </span>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-6 shadow-2xs relative overflow-hidden">
        <x-batik-corner position="top-right" class="w-28 h-28 sm:w-36 sm:h-36 text-[#005c8a] opacity-[0.16]" />
        <div class="relative z-10 flex items-start gap-3 sm:gap-4">
            <div class="hidden sm:flex w-11 h-11 rounded-lg bg-sky-50 border border-sky-200/80 items-center justify-center text-sky-700 shrink-0 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <div>
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-sky-50 border border-sky-200/80 text-[11px] font-semibold text-sky-800 uppercase tracking-wider mb-1">
                    Komitmen &amp; Rencana Aksi
                </span>
                <h1 class="text-lg sm:text-2xl font-bold text-slate-900 tracking-tight">Rencana Tindak Lanjut (RTL)</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5 font-normal">{{ $event->nama_event }}</p>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="px-4 py-3 rounded-lg bg-red-50 border-l-4 border-red-600 text-red-950 text-sm shadow-2xs">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($soal->isEmpty())
        <div class="bg-white rounded-2xl border-2 border-dashed border-slate-300 p-10 text-center text-slate-500 text-base shadow-xs">
            Pertanyaan Rencana Tindak Lanjut (RTL) belum disiapkan panitia.
        </div>
    @else
    <form method="POST" action="{{ route('portal.rtl.submit', $event) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @foreach($soal as $i => $s)
        @php $prev = $jawaban->get($s->id); $locked = $rtl && $rtl->status === 'terkirim'; @endphp
        <div class="bg-white rounded-2xl border-2 border-slate-200 p-5 sm:p-7 shadow-sm">
            <label class="block text-base sm:text-lg font-bold text-slate-900 mb-3.5 leading-snug">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-sky-100 text-sky-900 border border-sky-300 text-sm font-black mr-2 shadow-xs">
                    {{ $i + 1 }}
                </span>
                {{ $s->pertanyaan }}
            </label>

            @if($s->tipe === 'essay')
                <textarea name="jawaban[{{ $s->id }}]" rows="5" @disabled($locked) required
                    placeholder="Tuliskan rencana dan komitmen tindak lanjut Anda..."
                    class="w-full px-4 py-3.5 border-2 border-slate-300 rounded-2xl outline-none focus:border-sky-600 focus:ring-4 focus:ring-sky-100 transition resize-none disabled:bg-slate-100 disabled:text-slate-500 text-base bg-white font-medium placeholder:text-slate-400 leading-relaxed">{{ old('jawaban.' . $s->id, $prev?->jawaban) }}</textarea>
            @else
                <div class="p-5 rounded-2xl bg-slate-50 border-2 border-slate-200">
                    @if($prev?->file_bukti)
                        <div class="flex items-center gap-2 mb-3.5 px-4 py-2 rounded-xl bg-sky-100 border border-sky-200 text-xs sm:text-sm font-bold text-sky-900">
                            <svg class="w-5 h-5 text-sky-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Berkas bukti telah diunggah sebelumnya.</span>
                        </div>
                    @endif
                    <input type="file" name="jawaban[{{ $s->id }}]" accept=".pdf,.jpg,.jpeg,.png" @required(! $prev?->file_bukti) @disabled($locked)
                        class="w-full text-sm text-slate-600 file:mr-3.5 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs sm:file:text-sm file:font-extrabold file:bg-sky-600 file:text-white hover:file:bg-sky-700 cursor-pointer disabled:opacity-50 transition">
                    <p class="text-xs sm:text-sm text-slate-500 mt-2.5 font-medium">Format yang didukung: PDF, JPG, PNG (maksimal 2 MB).</p>
                </div>
            @endif
        </div>
        @endforeach

        @if(! ($rtl && $rtl->status === 'terkirim'))
        <div class="fixed bottom-[calc(3.5rem+max(0.5rem,env(safe-area-inset-bottom,0px)))] inset-x-0 z-20 lg:sticky lg:bottom-4 lg:inset-auto px-4 py-3 lg:p-4 bg-white/95 backdrop-blur-md lg:rounded-2xl border-t lg:border border-slate-200/90 shadow-[0_-4px_12px_rgba(15,23,42,0.06)] lg:shadow-md flex items-center justify-between gap-3">
            <p class="text-[11px] sm:text-xs text-slate-600 font-medium">Dikunci setelah pengiriman.</p>
            <button type="submit" onclick="return confirm('Kirimkan RTL? Jawaban akan dikunci setelah pengiriman.')"
                class="min-h-11 px-5 sm:px-7 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-bold text-xs sm:text-sm rounded-xl shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer shrink-0">
                <span>Kirim Dokumen RTL</span>
                <span>&rarr;</span>
            </button>
        </div>
        @else
        <div class="p-5 rounded-2xl bg-sky-50 border-2 border-sky-300 text-sky-950 text-sm sm:text-base text-center font-bold shadow-xs">
            RTL telah berhasil dikirim dan tersimpan. Terima kasih atas komitmen dakwah dan perkaderan Anda.
        </div>
        @endif
    </form>
    @endif
</div>
@endsection
