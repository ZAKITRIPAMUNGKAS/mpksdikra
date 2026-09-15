@extends('layouts.app')

@section('title', 'Kelola Pengguna & Staf')

@section('content')
<div class="space-y-6">
    {{-- Header Banner dengan Ornamen Batik Solo Kawung --}}
    <div class="relative bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs overflow-hidden">
        <x-batik-corner position="top-right" class="w-44 h-44 sm:w-56 sm:h-56 text-[#005c8a] opacity-[0.22] pointer-events-none" />
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                    <span class="px-2.5 py-0.5 rounded-md text-xs font-semibold bg-[#005c8a]/10 text-[#005c8a] border border-[#005c8a]/20">
                        Manajemen Personalia &amp; Hak Akses
                    </span>
                    <span class="text-slate-300">&bull;</span>
                    <span class="text-xs text-slate-500">{{ $users->total() }} Akun Terdaftar</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Manajemen Akun Staf &amp; Pengguna</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola akun Admin, Fasilitator kegiatan, dan Eksekutif (Pimpinan Daerah/Majelis).</p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-[#005c8a] hover:bg-[#00486d] text-white rounded-lg text-xs font-semibold shadow-2xs transition">
                    <svg class="w-4 h-4 text-[#f8bb00]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Tambah Akun Staf</span>
                </a>
            </div>
        </div>

        <div class="mt-5 pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex gap-1.5 flex-wrap">
                <a href="{{ route('admin.users.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ empty($currentRole) ? 'bg-[#005c8a] text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }} transition">
                    Semua
                </a>
                <a href="{{ route('admin.users.index', ['role' => 'admin_cabang']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $currentRole === 'admin_cabang' ? 'bg-[#005c8a] text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }} transition">
                    Admin Cabang (PCM)
                </a>
                <a href="{{ route('admin.users.index', ['role' => 'admin']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $currentRole === 'admin' ? 'bg-[#005c8a] text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }} transition">
                    Admin Daerah
                </a>
                <a href="{{ route('admin.users.index', ['role' => 'fasilitator']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $currentRole === 'fasilitator' ? 'bg-[#005c8a] text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }} transition">
                    Fasilitator
                </a>
                <a href="{{ route('admin.users.index', ['role' => 'executive']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $currentRole === 'executive' ? 'bg-[#005c8a] text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }} transition">
                    Eksekutif
                </a>
            </div>

            <form method="GET" class="flex items-center gap-2">
                @if($currentRole)
                    <input type="hidden" name="role" value="{{ $currentRole }}">
                @endif
                <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama, cabang, username..."
                    class="px-3 py-1.5 text-xs rounded-lg border border-slate-200/90 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 bg-white outline-none shadow-2xs w-56">
                <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold transition shadow-2xs">Cari</button>
            </form>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-5">Nama</th>
                        <th class="py-3.5 px-5">Username / Kontak</th>
                        <th class="py-3.5 px-5">Peran (Role) &amp; Wilayah</th>
                        <th class="py-3.5 px-5">Status</th>
                        <th class="py-3.5 px-5 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $u)
                    <tr class="hover:bg-slate-50/70 transition duration-150">
                        <td class="py-3.5 px-5 font-semibold text-slate-900 text-sm">
                            {{ $u->name }}
                            @if($u->id === auth()->id())
                                <span class="ml-1.5 text-xs font-semibold text-sky-800 bg-sky-50 px-2 py-0.5 rounded-md border border-sky-200">Anda</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-5 text-xs text-slate-600">
                            <div class="font-mono font-medium text-slate-800">{{ $u->email }}</div>
                            @if($u->no_wa)
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">WA: {{ $u->no_wa }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-5">
                            @if($u->role === 'admin_cabang')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Admin Cabang
                                </span>
                                @if($u->pcm)
                                    <div class="text-[11px] text-slate-600 font-bold mt-0.5">PCM {{ $u->pcm }}</div>
                                @endif
                            @elseif($u->role === 'admin')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                    Admin Daerah
                                </span>
                                <div class="text-[11px] text-slate-400 mt-0.5">PDM Karanganyar</div>
                            @elseif($u->role === 'fasilitator')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-sky-50 text-sky-800 border border-sky-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                                    Fasilitator
                                </span>
                            @elseif($u->role === 'executive')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-amber-50 text-amber-900 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Eksekutif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium capitalize bg-slate-100 text-slate-700">
                                    {{ $u->role }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium capitalize border
                                {{ $u->status === 'aktif' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                {{ $u->status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-5 text-right">
                            @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.toggle', $u) }}" class="inline-block"
                                onsubmit="return confirm('Ubah status akun {{ $u->name }}?')">
                                @csrf
                                <button type="submit" class="px-3 py-1 text-xs font-semibold rounded-lg border {{ $u->status === 'aktif' ? 'border-amber-200 text-amber-800 hover:bg-amber-50' : 'border-emerald-200 text-emerald-800 hover:bg-emerald-50' }} transition shadow-2xs">
                                    {{ $u->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                            @else
                            <span class="text-xs text-slate-400 italic">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400 text-xs">
                            Tidak ada akun staf ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
