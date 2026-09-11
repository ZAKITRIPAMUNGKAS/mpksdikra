@extends('layouts.app')

@section('title', 'Tes')

@section('content')
<h1 class="text-xl font-bold text-gray-800 mb-4">Pretest &amp; Posttest</h1>

<div class="space-y-4">
    @forelse($events as $ep)
    <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <h2 class="font-bold text-gray-800">{{ $ep->event->nama_event }}</h2>
        <p class="text-xs text-gray-500 mb-4">{{ $ep->event->tanggal_mulai->format('d M Y') }} — {{ $ep->event->tanggal_selesai->format('d M Y') }}</p>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('portal.tes.show', [$ep->event, 'pretest']) }}" class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold">Kerjakan Pretest</a>
            <a href="{{ route('portal.tes.show', [$ep->event, 'posttest']) }}" class="px-5 py-2.5 rounded-xl border border-emerald-600 text-emerald-700 hover:bg-emerald-50 text-sm font-semibold">Kerjakan Posttest</a>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-2xl border border-gray-200 p-10 text-center text-gray-400 text-sm">Belum ada kegiatan.</div>
    @endforelse
</div>
@endsection
