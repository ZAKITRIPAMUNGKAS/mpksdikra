@extends('layouts.app')

@section('title', 'RTL — ' . $event->nama_event)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs relative overflow-hidden">
        <x-batik-corner position="top-right" class="w-36 h-36 text-[#005c8a] opacity-[0.18]" />
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-11 h-11 rounded-lg bg-sky-50 border border-sky-200/80 flex items-center justify-center text-sky-700 shrink-0 shadow-2xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-sky-50 border border-sky-200/80 text-xs font-semibold text-sky-800 uppercase tracking-wider mb-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-ping"></span>
                        Komitmen &amp; Rencana Aksi
                    </span>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Rencana Tindak Lanjut (RTL)</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 font-normal">{{ $event->nama_event }}</p>
                </div>
            </div>

            @if($rtl)
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-semibold uppercase tracking-wider shadow-2xs
                    {{ $rtl->status === 'terkirim' ? 'bg-sky-50 text-sky-900 border border-sky-300/80' : 'bg-amber-50 text-amber-900 border border-amber-300/80' }}">
                    <span class="w-2 h-2 rounded-full {{ $rtl->status === 'terkirim' ? 'bg-[#005c8a] animate-pulse' : 'bg-amber-500' }}"></span>
                    Status: {{ $rtl->status }}
                </span>
            </div>
            @endif
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
        <div class="bg-white rounded-xl border border-dashed border-slate-300 p-10 text-center text-slate-400 text-sm shadow-2xs">
            Pertanyaan Rencana Tindak Lanjut (RTL) belum disiapkan panitia.
        </div>
    @else
    <form method="POST" action="{{ route('portal.rtl.submit', $event) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @foreach($soal as $i => $s)
        @php $prev = $jawaban->get($s->id); $locked = $rtl && $rtl->status === 'terkirim'; @endphp
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs">
            <label class="block text-sm font-semibold text-slate-900 mb-3 leading-snug">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-sky-50 text-sky-900 border border-sky-200/80 text-xs font-semibold mr-2 shadow-2xs">
                    {{ $i + 1 }}
                </span>
                {{ $s->pertanyaan }}
            </label>

            @if($s->tipe === 'essay')
                <textarea name="jawaban[{{ $s->id }}]" rows="5" @disabled($locked) required
                    placeholder="Tuliskan rencana dan komitmen tindak lanjut Anda..."
                    class="w-full px-3.5 py-2.5 border border-slate-200/90 rounded-lg outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition resize-none disabled:bg-slate-50 disabled:text-slate-500 text-sm bg-white">{{ old('jawaban.' . $s->id, $prev?->jawaban) }}</textarea>
            @else
                <div class="p-4 rounded-lg bg-slate-50/70 border border-slate-200/90">
                    @if($prev?->file_bukti)
                        <div class="flex items-center gap-2 mb-3 px-3 py-1.5 rounded-lg bg-sky-50 border border-sky-200/80 text-xs font-semibold text-sky-900">
                            <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Berkas bukti telah diunggah sebelumnya.</span>
                        </div>
                    @endif
                    <input type="file" name="jawaban[{{ $s->id }}]" accept=".pdf,.jpg,.jpeg,.png" @required(! $prev?->file_bukti) @disabled($locked)
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-900 hover:file:bg-sky-100 cursor-pointer disabled:opacity-50 transition">
                    <p class="text-xs text-slate-400 mt-2">Format yang didukung: PDF, JPG, PNG (maksimal 2 MB).</p>
                </div>
            @endif
        </div>
        @endforeach

        @if(! ($rtl && $rtl->status === 'terkirim'))
        <div class="sticky bottom-4 z-20 bg-white/95 backdrop-blur-md p-4 rounded-xl border border-slate-200/90 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-slate-500 font-medium">RTL yang telah dikirim akan dikunci untuk ditinjau panitia.</p>
            <button type="submit" onclick="return confirm('Kirimkan RTL? Jawaban akan dikunci setelah pengiriman.')"
                class="w-full sm:w-auto px-6 py-2.5 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-semibold text-xs sm:text-sm rounded-lg shadow-2xs transition">
                Kirim Dokumen RTL
            </button>
        </div>
        @else
        <div class="p-4 rounded-xl bg-sky-50 border border-sky-200/80 text-sky-950 text-sm text-center font-semibold shadow-2xs">
            RTL telah berhasil dikirim dan tersimpan. Terima kasih atas komitmen dakwah dan perkaderan Anda.
        </div>
        @endif
    </form>
    @endif
</div>
@endsection
