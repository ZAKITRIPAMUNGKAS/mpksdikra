@extends('layouts.app')

@section('title', 'Kelola Pengguna & Staf')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl border border-slate-200 border-t-4 border-t-mpksdi-600 p-6 sm:p-8 shadow-xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Manajemen Akun Staf &amp; Pengguna</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola akun Admin, Fasilitator kegiatan, dan Eksekutif (Pimpinan Daerah/Majelis).</p>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-mpksdi-600 hover:bg-mpksdi-700 text-white rounded-lg text-xs font-bold shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Tambah Akun Staf</span>
                </a>
            </div>
        </div>

        <div class="mt-5 pt-5 border-t border-slate-100 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex gap-1.5 flex-wrap">
                <a href="{{ route('admin.users.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ empty($currentRole) ? 'bg-mpksdi-900 text-gold-400' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }} transition">
                    Semua
                </a>
                <a href="{{ route('admin.users.index', ['role' => 'fasilitator']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $currentRole === 'fasilitator' ? 'bg-mpksdi-900 text-gold-400' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }} transition">
                    Fasilitator
                </a>
                <a href="{{ route('admin.users.index', ['role' => 'executive']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $currentRole === 'executive' ? 'bg-mpksdi-900 text-gold-400' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }} transition">
                    Eksekutif
                </a>
                <a href="{{ route('admin.users.index', ['role' => 'admin']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $currentRole === 'admin' ? 'bg-mpksdi-900 text-gold-400' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }} transition">
                    Admin
                </a>
            </div>

            <form method="GET" class="flex items-center gap-2">
                @if($currentRole)
                    <input type="hidden" name="role" value="{{ $currentRole }}">
                @endif
                <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama atau email..."
                    class="px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-300 focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 bg-white">
                <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-bold transition">Cari</button>
            </form>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-700">
                        <th class="py-3.5 px-6">Nama</th>
                        <th class="py-3.5 px-6">Email / Kontak</th>
                        <th class="py-3.5 px-6">Peran (Role)</th>
                        <th class="py-3.5 px-6">Status</th>
                        <th class="py-3.5 px-6 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $u)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-4 px-6 font-bold text-slate-900">
                            {{ $u->name }}
                            @if($u->id === auth()->id())
                                <span class="ml-1 text-[10px] font-bold text-mpksdi-700 bg-mpksdi-50 px-1.5 py-0.5 rounded border border-mpksdi-200">Anda</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-xs text-slate-600">
                            <div>{{ $u->email }}</div>
                            @if($u->no_wa)
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $u->no_wa }}</div>
                            @endif
                        </td>
                        <td class="py-4 px-6">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider
                                {{ $u->role === 'admin' ? 'bg-purple-50 text-purple-700 border border-purple-200' : '' }}
                                {{ $u->role === 'fasilitator' ? 'bg-mpksdi-50 text-mpksdi-800 border border-mpksdi-200' : '' }}
                                {{ $u->role === 'executive' ? 'bg-amber-50 text-amber-800 border border-amber-200' : '' }}">
                                {{ $u->role }}
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold
                                {{ $u->status === 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $u->status }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right">
                            @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.toggle', $u) }}" class="inline-block"
                                onsubmit="return confirm('Ubah status akun {{ $u->name }}?')">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg border {{ $u->status === 'aktif' ? 'border-amber-200 text-amber-800 hover:bg-amber-50' : 'border-emerald-200 text-emerald-800 hover:bg-emerald-50' }} transition">
                                    {{ $u->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                            @else
                            <span class="text-xs text-slate-400 italic">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400 text-xs">
                            Tidak ada akun ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
