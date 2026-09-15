@extends('layouts.app')

@section('title', 'Pusat Pembaruan Sistem')

@section('content')
<div class="space-y-6 sm:space-y-8">
    {{-- Header Banner dengan Watermark & Nuansa Berkelas --}}
    <div class="relative bg-white rounded-xl border border-slate-200/90 p-6 sm:p-8 shadow-2xs overflow-hidden">
        <x-batik-corner position="top-right" class="w-48 h-48 sm:w-60 sm:h-60 text-[#005c8a] opacity-[0.24] pointer-events-none" />

        <div class="relative z-10 max-w-3xl">
            <div class="flex items-center gap-2.5 flex-wrap mb-3">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-[#005c8a] text-white shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-[#f8bb00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Versi 2.4.0 Stable</span>
                </span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-50 text-emerald-800 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                    Sistem Beroperasi Normal
                </span>
                <span class="text-xs text-slate-500">Pembaruan: 16 September 2026</span>
            </div>

            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                Pusat Informasi &amp; Pembaruan Sistem
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 mt-1.5 leading-relaxed">
                Catatan rilis resmi (*changelog*), riwayat perbaikan performa, peningkatan keamanan, serta rencana peta jalan pengembangan Sistem Informasi Perkaderan Baitul Arqam MPKSDI PDM Karanganyar.
            </p>

            <div class="mt-5 pt-4 border-t border-slate-100 flex items-center gap-4 sm:gap-6 text-xs text-slate-500 flex-wrap">
                <div>
                    <span class="text-slate-400 block text-xs">Arsitektur</span>
                    <strong class="text-slate-800 font-semibold">Laravel 12 &bull; Tailwind CSS 4</strong>
                </div>
                <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>
                <div>
                    <span class="text-slate-400 block text-xs">Database Engine</span>
                    <strong class="text-slate-800 font-semibold">MariaDB / MySQL 8.2+</strong>
                </div>
                <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>
                <div>
                    <span class="text-slate-400 block text-xs">Pengembang</span>
                    <strong class="text-slate-800 font-semibold">MPKSDI &amp; gemala.dev</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Timeline Riwayat Versi & Fitur --}}
    <div class="space-y-6">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200">
            <div>
                <h2 class="text-base font-bold text-slate-900 tracking-tight">Riwayat Pembaruan Rilis</h2>
                <p class="text-xs text-slate-500 mt-0.5">Catatan komprehensif perubahan antarmuka, fitur fungsional, dan stabilitas</p>
            </div>
            <a href="{{ route('panduan') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-slate-50 text-[#005c8a] border border-slate-200 text-xs font-semibold rounded-lg transition shadow-2xs">
                <span>Buka Panduan Operasional</span>
                <span>&rarr;</span>
            </a>
        </div>

        {{-- Versi 2.4.0 (Rilis Terbaru) --}}
        <div class="bg-white rounded-xl border border-emerald-200/80 p-5 sm:p-6 shadow-2xs relative overflow-hidden">
            <div class="relative z-10">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-600 text-white">v2.4.0</span>
                        <h3 class="font-bold text-slate-900 text-base">Manajemen Akun Staf, Dokumen Biodata, &amp; Performa Animasi</h3>
                    </div>
                    <span class="text-xs font-medium text-slate-400">16 September 2026</span>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 rounded-lg bg-slate-50 border border-slate-200/60">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-2 h-2 rounded-full bg-[#005c8a]"></span>
                            <h4 class="font-bold text-slate-900 text-xs">Edit Akun Staf &amp; Cabang</h4>
                        </div>
                        <ul class="text-xs text-slate-600 space-y-1.5 list-disc list-inside leading-relaxed">
                            <li>Admin daerah dapat mengedit identitas, peran, wilayah PCM, dan kata sandi akun staf dari daftar staf.</li>
                            <li>Pergantian peran akun cabang otomatis memindahkan kewenangan verifikasi ke PCM tujuan.</li>
                            <li>Kata sandi hanya berubah bila kolom sandi baru diisi; validasi email unik tetap berlaku.</li>
                        </ul>
                    </div>
                    <div class="p-4 rounded-lg bg-slate-50 border border-slate-200/60">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            <h4 class="font-bold text-slate-900 text-xs">Dokumen Biodata Kader</h4>
                        </div>
                        <ul class="text-xs text-slate-600 space-y-1.5 list-disc list-inside leading-relaxed">
                            <li>Tata letak biodata PDF didesain ulang menjadi satu halaman A4 dengan tipografi terbaca.</li>
                            <li>Ornamen batik kop surat memakai aset PNG agar render DomPDF konsisten.</li>
                            <li>Blok tanda tangan dan footer tertata sejajar di kolom kanan dokumen.</li>
                        </ul>
                    </div>
                    <div class="p-4 rounded-lg bg-slate-50 border border-slate-200/60">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <h4 class="font-bold text-slate-900 text-xs">Performa &amp; Antarmuka</h4>
                        </div>
                        <ul class="text-xs text-slate-600 space-y-1.5 list-disc list-inside leading-relaxed">
                            <li>Animasi scroll reveal dipercepat dan diringankan; efek melayang dan denyut tak terbatas dihapus.</li>
                            <li>Ikon aksi WhatsApp tampil konsisten tanpa bergantung class warna arbitrary.</li>
                            <li>Preferensi <code>prefers-reduced-motion</code> tetap dihormati pada seluruh animasi.</li>
                        </ul>
                    </div>
                </div>
                <p class="mt-4 text-xs text-slate-600 leading-relaxed">Batas saat ini: pengeditan akun terbatas pada peran staf internal; akun kader personal tetap dikelola melalui alur verifikasi dan portal profil.</p>
            </div>
        </div>

        {{-- Versi 2.3.1 --}}
        <div class="bg-white rounded-xl border border-emerald-200/80 p-5 sm:p-6 shadow-2xs relative overflow-hidden">
            <div class="relative z-10">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-600 text-white">v2.3.1</span>
                        <h3 class="font-bold text-slate-900 text-base">Validasi Pertanyaan Tambahan pada Formulir Kader</h3>
                    </div>
                    <span class="text-xs font-medium text-slate-400">16 September 2026</span>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 rounded-lg bg-slate-50 border border-slate-200/60">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-2 h-2 rounded-full bg-[#005c8a]"></span>
                            <h4 class="font-bold text-slate-900 text-xs">Editor Pertanyaan</h4>
                        </div>
                        <ul class="text-xs text-slate-600 space-y-1.5 list-disc list-inside leading-relaxed">
                            <li>Pertanyaan standar dan pertanyaan custom dikelola dari satu editor formulir.</li>
                            <li>Admin dapat mengubah label, bantuan, placeholder, opsi, seksi, dan urutan pertanyaan.</li>
                            <li>Pertanyaan custom dapat ditambahkan atau dihapus melalui editor.</li>
                        </ul>
                    </div>
                    <div class="p-4 rounded-lg bg-slate-50 border border-slate-200/60">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            <h4 class="font-bold text-slate-900 text-xs">Setting Wajib &amp; Opsional</h4>
                        </div>
                        <ul class="text-xs text-slate-600 space-y-1.5 list-disc list-inside leading-relaxed">
                            <li>Pertanyaan custom yang wajib diisi kini divalidasi di server sesuai konfigurasi editor.</li>
                            <li>Pertanyaan custom yang disembunyikan tidak ditampilkan atau diwajibkan pada pendaftaran dan portal profil.</li>
                            <li>Validasi jawaban custom mendukung teks, angka, dropdown, dan checkbox sesuai opsi database.</li>
                        </ul>
                    </div>
                    <div class="p-4 rounded-lg bg-slate-50 border border-slate-200/60">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <h4 class="font-bold text-slate-900 text-xs">Perbaikan Keandalan</h4>
                        </div>
                        <ul class="text-xs text-slate-600 space-y-1.5 list-disc list-inside leading-relaxed">
                            <li>Mencegah pertanyaan standar tampil dua kali di formulir.</li>
                            <li>Jawaban tambahan tetap disimpan pada <code>custom_fields</code>; field inti tetap memakai kolom anggota.</li>
                            <li>Test regresi mencakup pencegahan duplikasi field inti dan aturan wajib pertanyaan custom.</li>
                        </ul>
                    </div>
                </div>
                <p class="mt-4 text-xs text-slate-600 leading-relaxed">Batas saat ini: perubahan pengaturan pertanyaan standar di editor belum terhubung ke field inti pada form pendaftaran dan profil. Integrasi penuh masih dalam pengembangan.</p>
            </div>
        </div>

        {{-- Versi 2.3.0 --}}
        <div class="bg-white rounded-xl border border-sky-200/80 p-5 sm:p-6 shadow-2xs relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-sky-50 rounded-bl-full pointer-events-none -z-0"></div>
            
            <div class="relative z-10">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-[#005c8a] text-white">v2.3.0</span>
                        <h3 class="font-bold text-slate-900 text-base">Multi-Cabang (17 PCM), Analitik Demografi &amp; Kemasjidan Terpadu, serta Aksesibilitas Lansia</h3>
                    </div>
                    <span class="text-xs font-medium text-slate-400">15 September 2026</span>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Multi-Cabang & Ekspor --}}
                    <div class="p-4 rounded-lg bg-slate-50 border border-slate-200/60">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-2 h-2 rounded-full bg-[#005c8a]"></span>
                            <h4 class="font-bold text-slate-900 text-xs">Multi-Cabang &amp; Ekspor Data</h4>
                        </div>
                        <ul class="text-xs text-slate-600 space-y-1.5 list-disc list-inside leading-relaxed">
                            <li>Hak akses terisolasi untuk 17 Admin Cabang (PCM) se-Karanganyar.</li>
                            <li>Login instan nama kecamatan langsung tanpa karakter khusus (`colomadu`, `jaten`, dll).</li>
                            <li>Ekspor data pangkalan kader cabang ke Excel (.xlsx) dengan 47 atribut komprehensif.</li>
                            <li>Persetujuan dan penolakan berkas terverifikasi khusus wilayah masing-masing PCM.</li>
                        </ul>
                    </div>

                    {{-- Analitik Demografi Terpadu --}}
                    <div class="p-4 rounded-lg bg-slate-50 border border-slate-200/60">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            <h4 class="font-bold text-slate-900 text-xs">Analitik Demografi Terpadu</h4>
                        </div>
                        <ul class="text-xs text-slate-600 space-y-1.5 list-disc list-inside leading-relaxed">
                            <li>4 Kartu KPI &amp; Profil Utama di baris atas (Kader &amp; Gender, Verifikasi, Kemasjidan, Profesi Dominan).</li>
                            <li>Visual Analytics 2-Panel tanpa bento overload: Tren 6 bulan + Horizontal Status Bar dan Panel Demografi.</li>
                            <li>Agregasi real-time: profesi dominan kader, % sholat 5 waktu di masjid, dan rasio pengurus takmir.</li>
                            <li>Filter dinamis se-Kabupaten untuk PDM dan per-wilayah untuk PCM.</li>
                        </ul>
                    </div>

                    {{-- Ergonomi Lansia & Keandalan --}}
                    <div class="p-4 rounded-lg bg-slate-50 border border-slate-200/60">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <h4 class="font-bold text-slate-900 text-xs">Ergonomi Lansia &amp; Keandalan</h4>
                        </div>
                        <ul class="text-xs text-slate-600 space-y-1.5 list-disc list-inside leading-relaxed">
                            <li>Desain form ergonomis untuk kader senior (usia 40–70+ thn): input 50-56px dan kontras tinggi.</li>
                            <li>Riwayat pendidikan bertingkat dinamis (SD, SMP, SMA, S1/S2/S3).</li>
                            <li>Login fleksibel multi-format: awalan `08`, `62`, maupun `+62` otomatis dikenali.</li>
                            <li>Penanganan sesi kedaluwarsa (*Graceful CSRF 419 token recovery*) &amp; HTTPS proxy cPanel.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Versi 2.2.0 --}}
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">v2.2.0</span>
                    <h3 class="font-bold text-slate-900 text-base">Linear Minimalist, Fixed Viewport Sidebar &amp; Pusat Dokumentasi</h3>
                </div>
                <span class="text-xs font-medium text-slate-400">12 September 2026</span>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Visual & Layout --}}
                    <div class="p-4 rounded-lg bg-slate-50 border border-slate-200/60">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-2 h-2 rounded-full bg-[#005c8a]"></span>
                            <h4 class="font-bold text-slate-900 text-xs">Antarmuka &amp; Visual</h4>
                        </div>
                        <ul class="text-xs text-slate-600 space-y-1.5 list-disc list-inside leading-relaxed">
                            <li>Sidebar terang modern (*soft light minimalist*) menyatu alami dengan kanvas kerja.</li>
                            <li>Sidebar terkunci tetap (*fixed viewport*) di desktop dengan scroll mandiri.</li>
                            <li>Pembersihan elemen AI-slop (*card-lift*, bubble cards, warna pelangi kaku).</li>
                            <li>Tekstur *micro-grid pattern* halus di latar kanvas workstation.</li>
                        </ul>
                    </div>

                    {{-- Ergonomi Operasional --}}
                    <div class="p-4 rounded-lg bg-slate-50 border border-slate-200/60">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <h4 class="font-bold text-slate-900 text-xs">Ergonomi Kerja</h4>
                        </div>
                        <ul class="text-xs text-slate-600 space-y-1.5 list-disc list-inside leading-relaxed">
                            <li>*Actionable Operational Ribbon* di dashboard atas untuk antrean verifikasi baru.</li>
                            <li>Tata letak agenda perkaderan fleksibel dan touch-friendly di perangkat mobile.</li>
                            <li>Tinggi grafik adaptif (180px di HP, 240px di PC) agar tidak memakan ruang gulir.</li>
                            <li>Penataan ulang breadcrumbs dan profil header yang ringkas.</li>
                        </ul>
                    </div>

                    {{-- Dokumentasi & Bantuan --}}
                    <div class="p-4 rounded-lg bg-slate-50 border border-slate-200/60">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <h4 class="font-bold text-slate-900 text-xs">Dokumentasi &amp; Panduan</h4>
                        </div>
                        <ul class="text-xs text-slate-600 space-y-1.5 list-disc list-inside leading-relaxed">
                            <li>Halaman Panduan &amp; Tutorial Sistem interaktif multi-peran (`/panduan`).</li>
                            <li>Pusat Informasi &amp; Riwayat Pembaruan Sistem (`/updates`).</li>
                            <li>Accordion FAQ penyelesaian kendala umum operasional perkaderan.</li>
                            <li>Navigasi sidebar dan tautan footer terintegrasi.</li>
                        </ul>
                    </div>
                </div>
            </div>

        {{-- Versi 2.1.0 --}}
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">v2.1.0</span>
                    <h3 class="font-bold text-slate-900 text-base">Pengacak Kelompok Outbound, Keamanan CBT &amp; Pasfoto Privat</h3>
                </div>
                <span class="text-xs font-medium text-slate-400">11 September 2026</span>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60">
                    <h4 class="font-bold text-slate-800 text-xs mb-1.5">Pengacak Kelompok Outbound</h4>
                    <p class="text-xs text-slate-600">Algoritma otomatis pembagian kelompok outbound berimbang berdasarkan gender (ikhwan/akhwat) dan pemerataan asal cabang (PCM).</p>
                </div>
                <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60">
                    <h4 class="font-bold text-slate-800 text-xs mb-1.5">Keamanan Ujian CBT</h4>
                    <p class="text-xs text-slate-600">Pemblokiran resubmission lembar ujian yang sudah dikumpulkan dan sinkronisasi server-side deadline countdown.</p>
                </div>
                <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60">
                    <h4 class="font-bold text-slate-800 text-xs mb-1.5">Format Sertifikat &amp; Excel</h4>
                    <p class="text-xs text-slate-600">Penomoran formal sertifikat otomatis, ekspor Excel dengan breakdown keaktifan, dan pasfoto terenkapsulasi di ID Card PDF.</p>
                </div>
            </div>
        </div>

        {{-- Versi 2.0.0 --}}
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">v2.0.0</span>
                    <h3 class="font-bold text-slate-900 text-base">Integrasi Multi-Role &amp; Portal Perkaderan Baitul Arqam</h3>
                </div>
                <span class="text-xs font-medium text-slate-400">10 September 2026</span>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60">
                    <h4 class="font-bold text-slate-800 text-xs mb-1.5">4 Pilar Peran Pengguna</h4>
                    <p class="text-xs text-slate-600">Hak akses terisolasi untuk Administrator, Instruktur/Fasilitator, Anggota/Kader, dan Executive Desk Pimpinan PDM.</p>
                </div>
                <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60">
                    <h4 class="font-bold text-slate-800 text-xs mb-1.5">Siklus Evaluasi 5 Aspek</h4>
                    <p class="text-xs text-slate-600">Perhitungan nilai akhir komprehensif: Kognitif (Pretest/Posttest), Psikomotorik, Afektif, Angket Evaluasi, dan Rencana Tindak Lanjut (RTL).</p>
                </div>
                <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60">
                    <h4 class="font-bold text-slate-800 text-xs mb-1.5">Presensi QR Scanner</h4>
                    <p class="text-xs text-slate-600">Presensi per sesi kegiatan Baitul Arqam menggunakan QR Code terenkripsi dengan proteksi ganda presensi duplikat.</p>
                </div>
            </div>
        </div>

        {{-- Versi 1.0.0 --}}
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">v1.0.0</span>
                    <h3 class="font-bold text-slate-900 text-base">Scaffold Fondasi &amp; Pangkalan Data Kader 17 PCM</h3>
                </div>
                <span class="text-xs font-medium text-slate-400">Awal September 2026</span>
            </div>

            <p class="text-xs text-slate-600 mt-3 leading-relaxed">
                Pondasi awal sistem pangkalan data master anggota, pendaftaran mandiri kader persyarikatan, pemetaan 17 Pimpinan Cabang Muhammadiyah (PCM) se-Kabupaten Karanganyar, dan otentikasi akun.
            </p>
        </div>
    </div>

    {{-- Roadmap / Pengembangan Selanjutnya --}}
    <div class="bg-gradient-to-br from-[#002b40] to-[#001724] rounded-xl p-6 sm:p-7 text-white shadow-md relative overflow-hidden">
        <div class="absolute right-0 top-0 w-80 h-full bg-pattern-stripes opacity-10 pointer-events-none"></div>

        <div class="relative z-10 max-w-2xl">
            <div class="flex items-center gap-2 text-[#f8bb00] text-xs font-bold uppercase tracking-wider mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                <span>Rencana Pengembangan (Roadmap)</span>
            </div>
            <h2 class="text-lg sm:text-xl font-bold tracking-tight">Menuju Transformasi Digital Perkaderan Karanganyar</h2>
            <p class="text-xs sm:text-sm text-slate-300 mt-1.5 leading-relaxed">
                Pengembangan bertahap MPKSDI untuk memfasilitasi koordinasi perkaderan di 17 Cabang dan ratusan Ranting Muhammadiyah se-Kabupaten Karanganyar.
            </p>

            <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="p-3.5 rounded-lg bg-white/[0.08] border border-white/10 backdrop-blur-xs">
                    <span class="text-xs text-[#f8bb00] font-semibold block mb-1">Tahap Berikutnya</span>
                    <strong class="text-xs text-white block">WhatsApp Gateway Bot</strong>
                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">Notifikasi otomatis via WA saat akun diverifikasi atau nilai diterbitkan.</p>
                </div>

                <div class="p-3.5 rounded-lg bg-white/[0.08] border border-white/10 backdrop-blur-xs">
                    <span class="text-xs text-sky-300 font-semibold block mb-1">Integrasi Sistem</span>
                    <strong class="text-xs text-white block">Sinkronisasi SIMPEKA</strong>
                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">Integrasi biodata pimpinan dan kader aktif dengan sistem data persyarikatan.</p>
                </div>

                <div class="p-3.5 rounded-lg bg-white/[0.08] border border-white/10 backdrop-blur-xs">
                    <span class="text-xs text-emerald-300 font-semibold block mb-1">Analitik Lanjut</span>
                    <strong class="text-xs text-white block">Indeks Perkaderan PCM</strong>
                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">Metrik sebaran dan keberlanjutan kaderisasi per cabang di lereng Lawu &amp; pusat.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
