@props(['event', 'jenis', 'label'])

@php
    $dibuka = $event->isianDibuka($jenis);
@endphp

<form method="POST" action="{{ route('admin.events.akses-isian', [$event, $jenis]) }}"
      class="inline-flex items-center gap-2" data-akses-isian="{{ $jenis }}"
      onsubmit="return confirm({{ \Illuminate\Support\Js::from($dibuka ? 'Tutup pengisian '.$label.'? Peserta tidak bisa mengisi sampai dibuka kembali.' : 'Buka pengisian '.$label.' untuk peserta?') }})">
    @csrf
    <input type="hidden" name="status" value="{{ $dibuka ? 'tutup' : 'aktif' }}">
    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-[11px] font-bold border {{ $dibuka ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200' }}">
        <span class="w-1.5 h-1.5 rounded-full {{ $dibuka ? 'bg-emerald-500' : 'bg-slate-400' }}" aria-hidden="true"></span>
        {{ $dibuka ? 'Dibuka' : 'Ditutup' }}
    </span>
    <button type="submit"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold shadow-2xs transition cursor-pointer whitespace-nowrap {{ $dibuka ? 'bg-white border border-rose-200 text-rose-700 hover:bg-rose-50' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
        @if($dibuka)
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            Tutup Pengisian
        @else
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
            Buka Pengisian
        @endif
    </button>
</form>
