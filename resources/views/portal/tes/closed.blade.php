@extends('layouts.app')

@section('title', ucfirst($tipe) . ' Belum Dibuka')

@section('content')
<div class="bg-white rounded-2xl border border-gray-200 p-10 text-center max-w-lg mx-auto">
    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-amber-50 border border-amber-200 grid place-items-center text-2xl">🔒</div>
    <h1 class="text-lg font-bold text-gray-800 mb-2">{{ ucfirst($tipe) }} Belum Dibuka</h1>
    <p class="text-sm text-gray-500">Sesi {{ $tipe }} untuk <b>{{ $event->nama_event }}</b> belum dibuka atau sudah ditutup panitia.</p>
    <a href="{{ route('portal.tes.index') }}" class="inline-block mt-6 px-5 py-2.5 rounded-xl border border-gray-300 text-sm font-semibold hover:bg-gray-50">Kembali</a>
</div>
@endsection
