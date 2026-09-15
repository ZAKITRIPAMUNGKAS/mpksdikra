@extends('layouts.app')

@section('title', 'Pengaturan Formulir Kader — Google Form Editor')

@section('content')
<div class="space-y-5" x-data="{
    showAddModal: false,
    showEditModal: false,
    editData: {
        id: null,
        label: '',
        tipe: 'text',
        seksi: 'diri',
        placeholder: '',
        bantuan: '',
        is_wajib: false,
        is_aktif: true,
        opsi_raw: '',
        updateUrl: ''
    },
    openEdit(item, url) {
        this.editData.id = item.id;
        this.editData.label = item.label;
        this.editData.tipe = item.tipe;
        this.editData.seksi = item.seksi;
        this.editData.placeholder = item.placeholder || '';
        this.editData.bantuan = item.bantuan || '';
        this.editData.is_wajib = Boolean(item.is_wajib);
        this.editData.is_aktif = Boolean(item.is_aktif);
        this.editData.opsi_raw = Array.isArray(item.opsi) ? item.opsi.join('\n') : '';
        this.editData.updateUrl = url;
        this.showEditModal = true;
    }
}">
    {{-- Header Banner with Batik Corner --}}
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs p-5 sm:p-6 relative overflow-hidden">
        <x-batik-corner position="top-right" class="w-36 h-36 text-[#005c8a] opacity-[0.16]" />
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-md bg-sky-50 text-sky-800 border border-sky-200 text-xs font-semibold uppercase tracking-wider">
                        Editor Formulir Dinamis
                    </span>
                    <span class="text-xs text-slate-500 font-normal">
                        {{ $totalAktif }} aktif dari {{ $totalPertanyaan }} pertanyaan
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Pengaturan Formulir Pendaftaran Kader</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl font-normal leading-relaxed">
                    Kelola dan sesuaikan daftar pertanyaan pendaftaran kader seperti Google Forms. Admin Daerah dapat menambahkan pertanyaan baru, mengatur teks panduan, opsi pilihan, dan status wajib pengisian.
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('anggota.register') }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 transition shadow-2xs" title="Buka form pendaftaran publik">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>Lihat Formulir Publik</span>
                </a>
                <button type="button" @click="showAddModal = true" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white text-xs font-bold shadow-2xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Pertanyaan</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Seksi / Tab Navigation (Google Form Style) --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-200">
        @foreach($seksiList as $key => $title)
            @php
                $count = isset($pertanyaanGrouped[$key]) ? $pertanyaanGrouped[$key]->count() : 0;
                $isActive = $activeSeksi === $key;
            @endphp
            <a href="{{ route('admin.form-kader.index', ['seksi' => $key]) }}"
               class="px-3.5 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 whitespace-nowrap {{ $isActive ? 'bg-sky-600 text-white shadow-2xs' : 'bg-white hover:bg-slate-100 text-slate-600 border border-slate-200/80' }}">
                <span>{{ $title }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $isActive ? 'bg-white/25 text-white' : 'bg-slate-100 text-slate-700' }}">
                    {{ $count }}
                </span>
            </a>
        @endforeach
    </div>

    {{-- Daftar Pertanyaan pada Seksi Terpilih --}}
    @php
        $currentQuestions = $pertanyaanGrouped[$activeSeksi] ?? collect();
    @endphp

    @if($currentQuestions->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-slate-300 p-10 text-center">
            <div class="w-12 h-12 rounded-full bg-sky-50 text-sky-600 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Belum ada pertanyaan di seksi ini</h3>
            <p class="text-xs text-slate-500 mt-1 mb-4">Tambahkan pertanyaan kustom baru untuk ditampilkan pada formulir kader.</p>
            <button type="button" @click="showAddModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold shadow-2xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Pertanyaan Pertama</span>
            </button>
        </div>
    @else
        <div class="space-y-3">
            @foreach($currentQuestions as $index => $q)
                <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 transition hover:border-sky-300 relative {{ $q->is_aktif ? 'border-l-4 border-l-sky-500' : 'border-l-4 border-l-slate-300 opacity-70 bg-slate-50/50' }}">
                    <div class="flex items-start justify-between gap-3 flex-wrap">
                        <div class="flex items-start gap-3 flex-1 min-w-[280px]">
                            {{-- Number badge --}}
                            <span class="w-6 h-6 rounded-md bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">
                                {{ $loop->iteration }}
                            </span>

                            <div class="space-y-1.5 flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-sm font-bold text-slate-900 tracking-tight">
                                        {{ $q->label }}
                                    </h3>

                                    {{-- Badges --}}
                                    @if($q->is_wajib)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                                            Wajib Diisi *
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                            Opsional
                                        </span>
                                    @endif

                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-sky-50 text-sky-800 border border-sky-200">
                                        {{ \App\Models\FormPertanyaan::TIPE[$q->tipe] ?? $q->tipe }}
                                    </span>

                                    @if($q->is_custom)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                            Kustom Admin
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-50 text-slate-500 border border-slate-200">
                                            Standar Sistem
                                        </span>
                                    @endif

                                    @if(!$q->is_aktif)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-300">
                                            Disembunyikan
                                        </span>
                                    @endif
                                </div>

                                {{-- Bantuan / Deskripsi --}}
                                @if($q->bantuan)
                                    <p class="text-xs text-slate-500 font-normal leading-relaxed">
                                        ℹ️ {{ $q->bantuan }}
                                    </p>
                                @endif

                                {{-- Placeholder --}}
                                @if($q->placeholder)
                                    <div class="text-[11px] text-slate-400 font-mono">
                                        Placeholder: "{{ $q->placeholder }}"
                                    </div>
                                @endif

                                {{-- Opsi Pilihan Jika Select/Checkbox --}}
                                @if(is_array($q->opsi) && count($q->opsi) > 0)
                                    <div class="pt-1 flex items-center gap-1.5 flex-wrap">
                                        <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Opsi:</span>
                                        @foreach($q->opsi as $opt)
                                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200 text-[11px]">
                                                {{ $opt }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Action Controls --}}
                        <div class="flex items-center gap-1.5 shrink-0 self-start sm:self-center">
                            {{-- Reorder buttons --}}
                            <form method="POST" action="{{ route('admin.form-kader.reorder', $q) }}" class="inline">
                                @csrf
                                <input type="hidden" name="arah" value="up">
                                <button type="submit" class="w-7 h-7 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 flex items-center justify-center transition shadow-2xs" title="Pindah ke atas">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.form-kader.reorder', $q) }}" class="inline">
                                @csrf
                                <input type="hidden" name="arah" value="down">
                                <button type="submit" class="w-7 h-7 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 flex items-center justify-center transition shadow-2xs" title="Pindah ke bawah">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </form>

                            {{-- Toggle Wajib --}}
                            <form method="POST" action="{{ route('admin.form-kader.toggle-wajib', $q) }}" class="inline">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-semibold border transition shadow-2xs {{ $q->is_wajib ? 'bg-rose-50 border-rose-200 text-rose-700 hover:bg-rose-100' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' }}" title="Klik untuk mengubah status wajib">
                                    {{ $q->is_wajib ? '★ Wajib' : '☆ Opsional' }}
                                </button>
                            </form>

                            {{-- Toggle Aktif --}}
                            <form method="POST" action="{{ route('admin.form-kader.toggle-aktif', $q) }}" class="inline">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-semibold border transition shadow-2xs {{ $q->is_aktif ? 'bg-emerald-50 border-emerald-200 text-emerald-700 hover:bg-emerald-100' : 'bg-slate-100 border-slate-300 text-slate-500 hover:bg-slate-200' }}" title="Klik untuk aktifkan / sembunyikan">
                                    {{ $q->is_aktif ? 'Aktif' : 'Sembunyi' }}
                                </button>
                            </form>

                            {{-- Edit button --}}
                            <button type="button"
                                    @click="openEdit({{ Js::from($q) }}, '{{ route('admin.form-kader.update', $q) }}')"
                                    class="px-2.5 py-1 rounded-lg bg-sky-50 border border-sky-200 hover:bg-sky-100 text-sky-800 text-xs font-semibold transition shadow-2xs">
                                Edit
                            </button>

                            {{-- Delete button (custom only) --}}
                            @if($q->is_custom)
                                <form method="POST" action="{{ route('admin.form-kader.destroy', $q) }}" onsubmit="return confirm('Hapus pertanyaan ini dari formulir?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-7 h-7 rounded-lg border border-red-200 bg-white hover:bg-red-50 text-red-600 flex items-center justify-center transition shadow-2xs" title="Hapus pertanyaan">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Modal: Tambah Pertanyaan Baru (Google Form Style) --}}
    <div x-show="showAddModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;" x-cloak>
        <div @click.away="showAddModal = false" class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 border border-slate-200 relative" x-data="{ tipeAdd: 'text' }">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-sm">+</span>
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">Tambah Pertanyaan Baru</h3>
                </div>
                <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-700 text-lg">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.form-kader.store') }}" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Teks Pertanyaan / Label:</label>
                    <input type="text" name="label" required placeholder="Contoh: Nama Ayah, Tempat Mengajar, Ukuran Kaos..."
                           class="w-full text-xs p-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Letakkan di Seksi / Tab:</label>
                        <select name="seksi" required class="w-full text-xs p-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition">
                            @foreach($seksiList as $sKey => $sTitle)
                                <option value="{{ $sKey }}" {{ $activeSeksi === $sKey ? 'selected' : '' }}>{{ $sTitle }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Tipe Jawaban:</label>
                        <select name="tipe" x-model="tipeAdd" required class="w-full text-xs p-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition">
                            @foreach($tipeList as $tKey => $tTitle)
                                <option value="{{ $tKey }}">{{ $tTitle }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Input Opsi jika tipe select atau checkbox --}}
                <div x-show="tipeAdd === 'select' || tipeAdd === 'checkbox'" class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Opsi Pilihan (Pisahkan dengan Baris Baru atau Koma):</label>
                    <textarea name="opsi_raw" rows="3" placeholder="Pilihan 1&#10;Pilihan 2&#10;Pilihan 3"
                              class="w-full text-xs p-2.5 rounded-lg border border-slate-200 bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition"></textarea>
                    <span class="text-[11px] text-slate-500 block">Kader akan memilih salah satu (dropdown) atau lebih dari satu (checkbox).</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Petunjuk Pengisian / Teks Bantuan (Opsional):</label>
                    <input type="text" name="bantuan" placeholder="Contoh: Isikan nama lengkap tanpa singkatan..."
                           class="w-full text-xs p-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Placeholder (Opsional):</label>
                    <input type="text" name="placeholder" placeholder="Contoh: Tuliskan di sini..."
                           class="w-full text-xs p-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition">
                </div>

                <div class="pt-2 flex items-center gap-4">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                        <input type="checkbox" name="is_wajib" value="1" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 w-4 h-4">
                        <span>Wajib Diisi Kader (Required)</span>
                    </label>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showAddModal = false" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Batal</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white rounded-lg transition shadow-2xs">Simpan Pertanyaan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Edit Pertanyaan --}}
    <div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;" x-cloak>
        <div @click.away="showEditModal = false" class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 border border-slate-200 relative">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-sm">✎</span>
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">Edit Pertanyaan</h3>
                </div>
                <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-700 text-lg">&times;</button>
            </div>

            <form method="POST" :action="editData.updateUrl" class="space-y-3.5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Teks Pertanyaan / Label:</label>
                    <input type="text" name="label" x-model="editData.label" required
                           class="w-full text-xs p-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Pindahkan ke Seksi / Tab:</label>
                    <select name="seksi" x-model="editData.seksi" class="w-full text-xs p-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition">
                        @foreach($seksiList as $sKey => $sTitle)
                            <option value="{{ $sKey }}">{{ $sTitle }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Input Opsi jika tipe select atau checkbox --}}
                <div x-show="editData.tipe === 'select' || editData.tipe === 'checkbox'" class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Opsi Pilihan (Pisahkan dengan Baris Baru atau Koma):</label>
                    <textarea name="opsi_raw" x-model="editData.opsi_raw" rows="4"
                              class="w-full text-xs p-2.5 rounded-lg border border-slate-200 bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Petunjuk Pengisian / Bantuan:</label>
                    <input type="text" name="bantuan" x-model="editData.bantuan"
                           class="w-full text-xs p-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Placeholder:</label>
                    <input type="text" name="placeholder" x-model="editData.placeholder"
                           class="w-full text-xs p-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition">
                </div>

                <div class="pt-2 flex items-center gap-5">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                        <input type="checkbox" name="is_wajib" value="1" x-model="editData.is_wajib" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 w-4 h-4">
                        <span>Wajib Diisi Kader</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                        <input type="checkbox" name="is_aktif" value="1" x-model="editData.is_aktif" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                        <span>Aktifkan di Formulir</span>
                    </label>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showEditModal = false" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Batal</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white rounded-lg transition shadow-2xs">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
