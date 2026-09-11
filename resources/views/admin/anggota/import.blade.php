@extends('layouts.app')

@section('title', 'Impor Data Kader (Excel)')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    {{-- Header Banner --}}
    <div class="bg-[#f3f5f8] rounded-3xl border border-sky-100/80 p-6 sm:p-8 shadow-xs relative overflow-hidden card-lift-sm">
        <x-batik-corner position="top-right" class="w-44 h-44 text-[#005c8a] opacity-[0.22]" />
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-3 flex-wrap mb-2">
                    <a href="{{ route('admin.anggota.index') }}" class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold bg-white text-slate-700 border border-slate-200 hover:border-sky-400 transition shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Kembali ke Data Kader</span>
                    </a>
                    <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                        Impor Massal Excel
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Impor Pangkalan Data Kader</h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1 max-w-2xl font-medium">
                    Tambahkan banyak kader sekaligus dari file spreadsheet Excel atau CSV dengan validasi NIK otomatis dan pembuatan akun langsung aktif.
                </p>
            </div>
            <div>
                <a href="{{ route('admin.anggota.import.template') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs hover:scale-[1.01]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>Unduh Format Template (.xlsx)</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Alert Hasil Impor --}}
    @if(session('import_success_count') !== null)
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4 card-lift-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Hasil Pemrosesan Berkas Excel</h3>
                    <p class="text-xs text-slate-500 font-medium">Laporan status baris data yang diproses ke dalam pangkalan data.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100">
                    <div class="text-xs font-bold text-emerald-800">Berhasil Diimpor</div>
                    <div class="text-2xl font-black text-emerald-700 mt-1">{{ session('import_success_count') }} Kader</div>
                </div>
                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-100">
                    <div class="text-xs font-bold text-amber-800">Dilewati (Duplikat/Kosong)</div>
                    <div class="text-2xl font-black text-amber-700 mt-1">{{ count(session('import_skipped') ?? []) }} Baris</div>
                </div>
                <div class="p-4 rounded-2xl bg-red-50 border border-red-100">
                    <div class="text-xs font-bold text-red-800">Format Salah / Error</div>
                    <div class="text-2xl font-black text-red-700 mt-1">{{ count(session('import_errors') ?? []) }} Baris</div>
                </div>
            </div>

            @if(!empty(session('import_skipped')))
                <div class="mt-4 p-4 rounded-2xl bg-amber-50/70 border border-amber-200/80">
                    <h4 class="text-xs font-bold text-amber-900 mb-2">Catatan Data yang Dilewati:</h4>
                    <ul class="text-xs text-amber-800 space-y-1 list-disc list-inside">
                        @foreach(session('import_skipped') as $skip)
                            <li>{{ $skip }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!empty(session('import_errors')))
                <div class="mt-3 p-4 rounded-2xl bg-red-50/70 border border-red-200/80">
                    <h4 class="text-xs font-bold text-red-900 mb-2">Daftar Kesalahan Baris:</h4>
                    <ul class="text-xs text-red-800 space-y-1 list-disc list-inside">
                        @foreach(session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    {{-- Form & Petunjuk Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Formulir Upload --}}
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-8 card-lift-sm">
                <h2 class="text-lg font-black text-slate-900 tracking-tight mb-1">Unggah Berkas Spreadsheet</h2>
                <p class="text-xs text-slate-500 font-medium mb-6">Pilih berkas Excel (.xlsx, .xls) atau .csv yang sudah diisi sesuai format template.</p>

                <form action="{{ route('admin.anggota.import.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Pilih File Excel / CSV <span class="text-red-500">*</span>
                        </label>
                        <div class="relative border-2 border-dashed border-slate-200 hover:border-sky-400 rounded-3xl p-6 sm:p-8 text-center transition bg-slate-50/50 hover:bg-sky-50/30 group cursor-pointer">
                            <input type="file" name="file_excel" accept=".xlsx,.xls,.csv" required
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                onchange="document.getElementById('file-chosen-name').textContent = this.files[0] ? this.files[0].name : 'Belum ada file dipilih';">
                            <div class="space-y-3">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center mx-auto group-hover:scale-110 transition">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-sm font-bold text-slate-800 group-hover:text-sky-600">Klik di sini untuk memilih berkas</span>
                                    <p class="text-xs text-slate-400 mt-0.5">Mendukung format .xlsx, .xls, .csv (Maksimal 5MB)</p>
                                </div>
                                <div id="file-chosen-name" class="inline-block px-4 py-1.5 bg-white border border-slate-200 rounded-full text-xs font-bold text-emerald-700 shadow-2xs">
                                    Belum ada file dipilih
                                </div>
                            </div>
                        </div>
                        @error('file_excel')
                            <p class="text-xs text-red-600 font-bold mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full sm:w-auto px-8 py-3 rounded-full bg-sky-500 hover:bg-sky-600 text-white font-bold text-xs transition shadow-xs hover:scale-[1.01] flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            <span>Mulai Proses Impor Kader</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Petunjuk & Ketentuan --}}
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-8 card-lift-sm">
                <h3 class="text-base font-extrabold text-slate-900 tracking-tight mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#f8bb00]"></span>
                    Ketentuan Format Impor
                </h3>

                <ul class="space-y-3.5 text-xs text-slate-600">
                    <li class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-sky-50 text-sky-700 font-black text-[10px] flex items-center justify-center shrink-0 border border-sky-100">1</span>
                        <span><strong>Wajib NIK 16 Digit:</strong> NIK digunakan sebagai pengenal unik kader dan kata sandi awal akun.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-sky-50 text-sky-700 font-black text-[10px] flex items-center justify-center shrink-0 border border-sky-100">2</span>
                        <span><strong>Kata Sandi Bawaan:</strong> Kata sandi login kader otomatis dibuat dari <strong>6 digit terakhir NIK</strong>.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-sky-50 text-sky-700 font-black text-[10px] flex items-center justify-center shrink-0 border border-sky-100">3</span>
                        <span><strong>Status Akun:</strong> Seluruh kader yang diimpor oleh Admin langsung berstatus <strong>Aktif</strong> (tidak perlu verifikasi ulang).</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-sky-50 text-sky-700 font-black text-[10px] flex items-center justify-center shrink-0 border border-sky-100">4</span>
                        <span><strong>Email Otomatis:</strong> Jika kolom email dikosongkan di file Excel, sistem otomatis menghasilkan email berformat <code class="bg-slate-100 px-1 py-0.5 rounded text-[11px]">kader.[NIK]@mpksdi.test</code>.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-sky-50 text-sky-700 font-black text-[10px] flex items-center justify-center shrink-0 border border-sky-100">5</span>
                        <span><strong>Pencegahan Duplikat:</strong> Baris data dengan NIK yang sudah ada di sistem akan otomatis dilewati secara aman tanpa merusak data yang telah ada.</span>
                    </li>
                </ul>

                <div class="mt-6 pt-6 border-t border-slate-100">
                    <a href="{{ route('admin.anggota.import.template') }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-full bg-slate-50 hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 border border-slate-200 hover:border-emerald-300 text-xs font-bold transition">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Unduh File Contoh Format</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
