@extends('layouts.app')

@section('title', 'Informasi & Pembaruan Sistem')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- Header Ringkas --}}
    <div class="relative bg-white rounded-xl border border-slate-200/90 p-4 sm:p-6 shadow-2xs overflow-hidden">
        <x-batik-corner position="top-right" class="w-32 h-32 sm:w-48 sm:h-48 text-[#005c8a] opacity-[0.16] pointer-events-none" />

        <div class="relative z-10 space-y-2.5">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-[#005c8a] text-white shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-[#f8bb00]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                    <span>Versi 2.6.0 Stable</span>
                </span>
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Sistem Normal</span>
                </span>
                <span class="text-xs text-slate-500">26 November 2026</span>
            </div>

            <div>
                <h1 class="text-lg sm:text-2xl font-bold text-slate-900 tracking-tight">
                    Pusat Informasi &amp; Pembaruan Sistem
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1 leading-relaxed max-w-2xl">
                    Catatan rilis, pembaruan fitur, dan peningkatan keamanan Sistem Perkaderan Baitul Arqam MPKSDI PDM Karanganyar.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 pt-2 border-t border-slate-100 text-[11px] sm:text-xs text-slate-500">
                <span><strong class="text-slate-700 font-semibold">Stack:</strong> Laravel 12 &bull; Tailwind CSS 4</span>
                <span class="text-slate-300">&bull;</span>
                <span><strong class="text-slate-700 font-semibold">DB:</strong> MariaDB / MySQL 8.2+</span>
                <span class="text-slate-300">&bull;</span>
                <span><strong class="text-slate-700 font-semibold">Tim:</strong> MPKSDI &amp; gemala.dev</span>
            </div>
        </div>
    </div>

    {{-- Bagian Rilis --}}
    <div class="space-y-3 sm:space-y-4">
        <div class="flex items-center justify-between gap-3 pb-2.5 border-b border-slate-200">
            <div class="min-w-0">
                <h2 class="text-sm sm:text-base font-bold text-slate-900 tracking-tight">Riwayat Pembaruan Rilis</h2>
                <p class="text-xs text-slate-500 truncate">Ringkasan perubahan antarmuka, fitur, dan stabilitas</p>
            </div>
            <a href="{{ route('panduan') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-slate-50 text-[#005c8a] border border-slate-200 text-xs font-semibold rounded-lg transition shadow-2xs shrink-0 whitespace-nowrap">
                <span>Panduan</span>
                <span aria-hidden="true">&rarr;</span>
            </a>
        </div>

        {{-- Versi 2.6.0 (Terbaru) --}}
        <article class="bg-white rounded-xl border border-emerald-200/80 p-4 sm:p-5 shadow-2xs">
            <div class="flex items-center justify-between gap-2 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-600 text-white">v2.6.0</span>
                    <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Terbaru</span>
                    <h3 class="font-bold text-slate-900 text-sm sm:text-base">Overhaul UI Mobile &amp; Keamanan Ujian CBT</h3>
                </div>
                <span class="text-[11px] sm:text-xs font-medium text-slate-400 shrink-0">26 Nov 2026</span>
            </div>

            <ul class="mt-3 space-y-1.5 text-xs text-slate-600 list-disc pl-4 leading-relaxed">
                <li><strong>Navigasi &amp; Tampilan Mobile:</strong> <em>Bottom navigation bar</em> 3-tab (Beranda, Ujian, Profil), kartu kegiatan ringkas, dan tata letak responsif untuk layar ponsel.</li>
                <li><strong>Kunci Ujian &amp; Anti-Curang:</strong> Pemblokiran pindah halaman/tab dan <em>copy-paste</em> selama ujian berlangsung, dengan auto-kumpul otomatis saat pelanggaran ke-2.</li>
                <li><strong>Fokus Pengerjaan Soal:</strong> Bilah aksi bawah ujian menempel rapi tanpa celah serta daftar nomor soal cepat diakses tanpa menutupi soal.</li>
            </ul>
        </article>

        {{-- Versi 2.5.0 --}}
        <article class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-5 shadow-2xs">
            <div class="flex items-center justify-between gap-2 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-[#005c8a] text-white">v2.5.0</span>
                    <h3 class="font-bold text-slate-900 text-sm sm:text-base">Mode Kertas OMR, ID Card A4 &amp; Konsolidasi Angket</h3>
                </div>
                <span class="text-[11px] sm:text-xs font-medium text-slate-400 shrink-0">Nov 2026</span>
            </div>

            <ul class="mt-3 space-y-1.5 text-xs text-slate-600 list-disc pl-4 leading-relaxed">
                <li><strong>Ujian &amp; Evaluasi Mode Kertas:</strong> Cetak naskah soal (mendukung teks Arab), lembar jawaban (LJK), serta input manual &amp; pindai foto LJK via kamera (<em>OMR Scan</em>).</li>
                <li><strong>ID Card &amp; Presensi Cepat:</strong> Tata letak kartu peserta A4 2&times;2 dengan QR Code penuh, presensi manual massal, serta salin sesi dan instrumen afektif antarkegiatan.</li>
                <li><strong>Perapian Angket &amp; Feedback:</strong> Konsolidasi butir baku evaluasi di model terpusat agar konsisten antara pengisian digital dan versi cetak.</li>
            </ul>
        </article>

        {{-- Arsip Versi Sebelumnya (Accordion Native <details>) --}}
        <section aria-labelledby="older-releases-heading" class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <div class="px-4 py-2.5 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
                <h3 id="older-releases-heading" class="text-xs font-bold uppercase tracking-wider text-slate-500">Arsip Rilis Sebelumnya</h3>
                <span class="text-[11px] text-slate-400">Ketuk untuk lihat detail</span>
            </div>

            <div class="divide-y divide-slate-100">
                <details class="group">
                    <summary class="list-none flex items-center justify-between gap-2 px-4 py-3 cursor-pointer hover:bg-slate-50/80 transition select-none">
                        <div class="flex items-center gap-2 min-w-0">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200 group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200 shrink-0">v2.4.0</span>
                            <span class="text-xs sm:text-sm font-semibold text-slate-800 truncate">Manajemen Akun Staf, Dokumen Biodata &amp; Performa Animasi</span>
                        </div>
                        <span class="text-[11px] text-slate-400 shrink-0">16 Sep 2026</span>
                    </summary>
                    <ul class="px-4 pb-3 pt-2 text-xs text-slate-600 space-y-1 list-disc pl-8 bg-slate-50/40 border-t border-slate-100/80">
                        <li>Manajemen akun staf terpusat (Admin, Instruktur, Pimpinan) beserta pencabutan akses aman tanpa menghapus jejak evaluasi.</li>
                        <li>Unggah dokumen biodata PDF, sinkronisasi alamat wilayah, serta optimasi performa animasi antarmuka dan penulisan kode PIN QR.</li>
                    </ul>
                </details>

                <details class="group">
                    <summary class="list-none flex items-center justify-between gap-2 px-4 py-3 cursor-pointer hover:bg-slate-50/80 transition select-none">
                        <div class="flex items-center gap-2 min-w-0">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200 group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200 shrink-0">v2.3.1</span>
                            <span class="text-xs sm:text-sm font-semibold text-slate-800 truncate">Integrasi Pusat Informasi, Panduan &amp; Aksen Batik Persyarikatan</span>
                        </div>
                        <span class="text-[11px] text-slate-400 shrink-0">15 Sep 2026</span>
                    </summary>
                    <ul class="px-4 pb-3 pt-2 text-xs text-slate-600 space-y-1 list-disc pl-8 bg-slate-50/40 border-t border-slate-100/80">
                        <li>Halaman Panduan Operasional interaktif lintas peran, halaman Pembaruan Sistem, serta sentuhan motif Batik Kawung pada latar belakang.</li>
                    </ul>
                </details>

                <details class="group">
                    <summary class="list-none flex items-center justify-between gap-2 px-4 py-3 cursor-pointer hover:bg-slate-50/80 transition select-none">
                        <div class="flex items-center gap-2 min-w-0">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200 group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200 shrink-0">v2.3.0</span>
                            <span class="text-xs sm:text-sm font-semibold text-slate-800 truncate">Polesan Antarmuka Presisi &amp; Penegakan Alur Ujian Ketat</span>
                        </div>
                        <span class="text-[11px] text-slate-400 shrink-0">14 Sep 2026</span>
                    </summary>
                    <ul class="px-4 pb-3 pt-2 text-xs text-slate-600 space-y-1 list-disc pl-8 bg-slate-50/40 border-t border-slate-100/80">
                        <li><em>Anti-bypass</em> navigasi soal ujian, kartu ringkasan instruktur, penjadwalan materi fleksibel, dan optimasi tipografi UI.</li>
                    </ul>
                </details>

                <details class="group">
                    <summary class="list-none flex items-center justify-between gap-2 px-4 py-3 cursor-pointer hover:bg-slate-50/80 transition select-none">
                        <div class="flex items-center gap-2 min-w-0">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200 group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200 shrink-0">v2.2.0</span>
                            <span class="text-xs sm:text-sm font-semibold text-slate-800 truncate">Desain Responsif &amp; Sertifikasi Perkaderan</span>
                        </div>
                        <span class="text-[11px] text-slate-400 shrink-0">13 Sep 2026</span>
                    </summary>
                    <ul class="px-4 pb-3 pt-2 text-xs text-slate-600 space-y-1 list-disc pl-8 bg-slate-50/40 border-t border-slate-100/80">
                        <li>Sidebar <em>collapsible</em> dengan navigasi responsif, cetak ID Card &amp; Sertifikat kelulusan PDF, serta penjadwalan materi fleksibel.</li>
                    </ul>
                </details>

                <details class="group">
                    <summary class="list-none flex items-center justify-between gap-2 px-4 py-3 cursor-pointer hover:bg-slate-50/80 transition select-none">
                        <div class="flex items-center gap-2 min-w-0">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200 group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200 shrink-0">v2.1.0</span>
                            <span class="text-xs sm:text-sm font-semibold text-slate-800 truncate">Penjaminan Mutu, Keamanan Sistem &amp; Suite Pengujian</span>
                        </div>
                        <span class="text-[11px] text-slate-400 shrink-0">12 Sep 2026</span>
                    </summary>
                    <ul class="px-4 pb-3 pt-2 text-xs text-slate-600 space-y-1 list-disc pl-8 bg-slate-50/40 border-t border-slate-100/80">
                        <li>Audit otorisasi peran (RBAC), penguncian atomik presensi dan kumpul ujian, serta suite uji otomatis komprehensif.</li>
                    </ul>
                </details>

                <details class="group">
                    <summary class="list-none flex items-center justify-between gap-2 px-4 py-3 cursor-pointer hover:bg-slate-50/80 transition select-none">
                        <div class="flex items-center gap-2 min-w-0">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200 group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200 shrink-0">v2.0.0</span>
                            <span class="text-xs sm:text-sm font-semibold text-slate-800 truncate">Integrasi Multi-Role &amp; Portal Perkaderan Baitul Arqam</span>
                        </div>
                        <span class="text-[11px] text-slate-400 shrink-0">10 Sep 2026</span>
                    </summary>
                    <ul class="px-4 pb-3 pt-2 text-xs text-slate-600 space-y-1 list-disc pl-8 bg-slate-50/40 border-t border-slate-100/80">
                        <li>4 pilar peran (Admin, Instruktur, Anggota, Pimpinan), evaluasi 5 aspek, dan presensi QR Scanner terenkripsi.</li>
                    </ul>
                </details>

                <details class="group">
                    <summary class="list-none flex items-center justify-between gap-2 px-4 py-3 cursor-pointer hover:bg-slate-50/80 transition select-none">
                        <div class="flex items-center gap-2 min-w-0">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200 group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200 shrink-0">v1.0.0</span>
                            <span class="text-xs sm:text-sm font-semibold text-slate-800 truncate">Fondasi Sistem &amp; Pangkalan Data Kader 17 PCM</span>
                        </div>
                        <span class="text-[11px] text-slate-400 shrink-0">Sep 2026</span>
                    </summary>
                    <ul class="px-4 pb-3 pt-2 text-xs text-slate-600 space-y-1 list-disc pl-8 bg-slate-50/40 border-t border-slate-100/80">
                        <li>Pangkalan data master kader, pendaftaran mandiri, pemetaan 17 PCM se-Kabupaten Karanganyar, dan otentikasi dasar.</li>
                    </ul>
                </details>
            </div>
        </section>
    </div>

    {{-- Roadmap Ringkas --}}
    <div class="bg-gradient-to-br from-[#002b40] to-[#001724] rounded-xl p-4 sm:p-5 text-white shadow-md relative overflow-hidden">
        <div class="absolute right-0 top-0 w-64 h-full bg-pattern-stripes opacity-10 pointer-events-none"></div>

        <div class="relative z-10">
            <div class="flex items-center gap-2 text-[#f8bb00] text-xs font-bold uppercase tracking-wider">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                <span>Rencana Pengembangan (Roadmap)</span>
            </div>

            <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                <div class="p-3 rounded-lg bg-white/[0.08] border border-white/10">
                    <span class="text-[11px] text-[#f8bb00] font-semibold block">Tahap Berikutnya</span>
                    <strong class="text-xs text-white block mt-0.5">WhatsApp Gateway Bot</strong>
                    <p class="text-[11px] text-slate-300 mt-0.5">Notifikasi verifikasi akun &amp; terbit nilai via WA.</p>
                </div>

                <div class="p-3 rounded-lg bg-white/[0.08] border border-white/10">
                    <span class="text-[11px] text-sky-300 font-semibold block">Integrasi Sistem</span>
                    <strong class="text-xs text-white block mt-0.5">Sinkronisasi SIMPEKA</strong>
                    <p class="text-[11px] text-slate-300 mt-0.5">Integrasi biodata pimpinan &amp; kader aktif persyarikatan.</p>
                </div>

                <div class="p-3 rounded-lg bg-white/[0.08] border border-white/10">
                    <span class="text-[11px] text-emerald-300 font-semibold block">Analitik Lanjut</span>
                    <strong class="text-xs text-white block mt-0.5">Indeks Perkaderan PCM</strong>
                    <p class="text-[11px] text-slate-300 mt-0.5">Metrik sebaran kaderisasi 17 cabang Karanganyar.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
