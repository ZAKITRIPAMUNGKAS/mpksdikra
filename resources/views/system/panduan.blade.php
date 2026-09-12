@extends('layouts.app')

@section('title', 'Panduan & Tutorial Sistem')

@section('content')
<div class="space-y-6 sm:space-y-8" 
     x-data="{ 
         activeTab: '{{ auth()->user()->isAnggota() ? 'kader' : (auth()->user()->isFasilitator() ? 'fasilitator' : (auth()->user()->isExecutive() ? 'executive' : 'admin')) }}',
         activeFaq: null,
         toggleFaq(index) {
             this.activeFaq = this.activeFaq === index ? null : index;
         }
     }">

    {{-- Header Banner dengan Watermark & Nuansa Berkelas --}}
    <div class="relative bg-white rounded-xl border border-slate-200/90 p-6 sm:p-8 shadow-2xs overflow-hidden">
        <x-batik-corner position="top-right" class="w-48 h-48 sm:w-60 sm:h-60 text-[#005c8a] opacity-[0.24] pointer-events-none" />

        <div class="relative z-10 max-w-3xl">
            <div class="flex items-center gap-2.5 flex-wrap mb-3">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-[#005c8a] text-white shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-[#f8bb00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <span>Pusat Panduan Resmi</span>
                </span>
                <span class="text-xs text-slate-500">Sistem Perkaderan MPKSDI PDM Karanganyar</span>
            </div>

            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                Panduan &amp; Tutorial Penggunaan Sistem
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 mt-1.5 leading-relaxed">
                Petunjuk terpadu pengelolaan pangkalan data kader, pelaksanaan kegiatan Baitul Arqam, presensi QR, evaluasi nilai akademik, dan penerbitan dokumen resmi.
            </p>

            {{-- Role Tab Switcher --}}
            <div class="mt-6 flex items-center gap-2 overflow-x-auto pb-1 text-xs font-semibold select-none">
                <button type="button" @click="activeTab = 'admin'" 
                        :class="activeTab === 'admin' ? 'bg-[#005c8a] text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3.5 py-2 rounded-lg transition duration-150 shrink-0 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Administrator</span>
                </button>

                <button type="button" @click="activeTab = 'kader'" 
                        :class="activeTab === 'kader' ? 'bg-[#005c8a] text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3.5 py-2 rounded-lg transition duration-150 shrink-0 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Kader / Peserta</span>
                </button>

                <button type="button" @click="activeTab = 'fasilitator'" 
                        :class="activeTab === 'fasilitator' ? 'bg-[#005c8a] text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3.5 py-2 rounded-lg transition duration-150 shrink-0 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span>Fasilitator &amp; Instruktur</span>
                </button>

                <button type="button" @click="activeTab = 'executive'" 
                        :class="activeTab === 'executive' ? 'bg-[#005c8a] text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3.5 py-2 rounded-lg transition duration-150 shrink-0 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Pimpinan Eksekutif</span>
                </button>
            </div>
        </div>
    </div>

    {{-- KONTEN 1: PANDUAN ADMINISTRATOR --}}
    <div x-show="activeTab === 'admin'" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            {{-- Modul 1 --}}
            <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <span class="w-7 h-7 rounded-lg bg-[#005c8a] text-white font-bold text-xs flex items-center justify-center">1</span>
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base">Verifikasi &amp; Validasi Pendaftar</h3>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Calon kader mendaftar mandiri via form registrasi publik. Admin bertugas memeriksa kelengkapan data:
                    </p>
                    <ul class="mt-3 space-y-2 text-xs text-slate-600 list-disc list-inside">
                        <li>Buka menu <strong>Master Anggota</strong> &rarr; filter status <em>Menunggu Verifikasi</em>.</li>
                        <li>Periksa NIK 16 digit, kesesuaian pasfoto formal, dan cabang asal PCM.</li>
                        <li>Klik <strong>Setujui</strong> untuk mengaktifkan akun pendaftar.</li>
                        <li>Jika data belum lengkap, klik <strong>Tolak</strong> dan sertakan catatan perbaikan. Sistem menyediakan tombol kirim pesan konfirmasi instan via WhatsApp.</li>
                    </ul>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <a href="{{ route('admin.anggota.index') }}" class="text-xs font-semibold text-[#005c8a] hover:underline inline-flex items-center gap-1">
                        <span>Buka Master Anggota</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            {{-- Modul 2 --}}
            <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <span class="w-7 h-7 rounded-lg bg-[#005c8a] text-white font-bold text-xs flex items-center justify-center">2</span>
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base">Penyelenggaraan Event Baitul Arqam</h3>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Tahapan menginisiasi pelatihan perkaderan resmi tingkat Cabang maupun Daerah:
                    </p>
                    <ul class="mt-3 space-y-2 text-xs text-slate-600 list-disc list-inside">
                        <li>Klik tombol <strong>Buat Event Baru</strong> di dashboard atau menu Event BA.</li>
                        <li>Isi nama kegiatan, tanggal mulai &amp; selesai, lokasi, dan batas kuota maksimal.</li>
                        <li>Enroll peserta dari kader aktif terverifikasi (peserta tidak perlu mendaftar ulang).</li>
                        <li>Tugaskan Tim Instruktur / Fasilitator yang mendampingi kegiatan.</li>
                    </ul>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <a href="{{ route('admin.events.index') }}" class="text-xs font-semibold text-[#005c8a] hover:underline inline-flex items-center gap-1">
                        <span>Kelola Event BA</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            {{-- Modul 3 --}}
            <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <span class="w-7 h-7 rounded-lg bg-[#005c8a] text-white font-bold text-xs flex items-center justify-center">3</span>
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base">Presensi &amp; Absensi QR Scanner</h3>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Alur presensi kehadiran kehadiran per sesi materi:
                    </p>
                    <ul class="mt-3 space-y-2 text-xs text-slate-600 list-disc list-inside">
                        <li>Buka detail event &rarr; pilih sesi kegiatan &rarr; klik <strong>Scan Presensi</strong>.</li>
                        <li>Gunakan kamera laptop/smartphone untuk memindai QR Code di ID Card peserta.</li>
                        <li>Bila terkendala teknis pada kamera, admin dapat menggunakan fitur <em>Presensi Manual</em> dengan mencari nama peserta.</li>
                        <li>Admin dapat mengunduh <strong>Lembar Presensi PDF</strong> resmi bertanda tangan.</li>
                    </ul>
                </div>
            </div>

            {{-- Modul 4 --}}
            <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <span class="w-7 h-7 rounded-lg bg-[#005c8a] text-white font-bold text-xs flex items-center justify-center">4</span>
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base">Pengacak Kelompok Outbound</h3>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Membagi regu kegiatan lapangan dan tadabur alam secara adil dan berimbang:
                    </p>
                    <ul class="mt-3 space-y-2 text-xs text-slate-600 list-disc list-inside">
                        <li>Buka tab <strong>Kelompok Outbound</strong> pada detail event.</li>
                        <li>Tentukan jumlah pos / kelompok yang dikehendaki (misal: 4 pos).</li>
                        <li>Pilih mode algoritma: <em>Pemerataan Gender &amp; Cabang PCM</em> atau <em>Acak Murni</em>.</li>
                        <li>Klik <strong>Acak &amp; Simpan Kelompok</strong>. Daftar regu dapat langsung dicetak untuk panitia pos.</li>
                    </ul>
                </div>
            </div>

            {{-- Modul 5 --}}
            <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <span class="w-7 h-7 rounded-lg bg-[#005c8a] text-white font-bold text-xs flex items-center justify-center">5</span>
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base">Evaluasi 5 Aspek &amp; Nilai Akhir</h3>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Formula baku penentuan kelulusan peserta Baitul Arqam:
                    </p>
                    <ul class="mt-3 space-y-2 text-xs text-slate-600 list-disc list-inside">
                        <li><strong>Kognitif</strong>: Nilai CBT Pretest &amp; Posttest (dihitung otomatis).</li>
                        <li><strong>Psikomotorik</strong>: Penilaian praktik ibadah dan ketangkasan kader oleh instruktur.</li>
                        <li><strong>Afektif</strong>: Sikap, adab, dan kedisiplinan selama karantina.</li>
                        <li><strong>RTL</strong>: Rencana Tindak Lanjut dan komitmen kaderisasi di AUM/PCM.</li>
                        <li><strong>Keaktifan</strong>: Poin keaktifan tanya-jawab di forum materi.</li>
                    </ul>
                </div>
            </div>

            {{-- Modul 6 --}}
            <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <span class="w-7 h-7 rounded-lg bg-[#005c8a] text-white font-bold text-xs flex items-center justify-center">6</span>
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base">Penerbitan Dokumen Resmi &amp; Laporan</h3>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Semua dokumen resmi dapat dicetak langsung berstandar MPKSDI:
                    </p>
                    <ul class="mt-3 space-y-2 text-xs text-slate-600 list-disc list-inside">
                        <li><strong>Laporan Hasil Belajar PDF</strong>: Berita acara resmi dengan rekapitulasi nilai dan tanda tangan pimpinan.</li>
                        <li><strong>Rekapitulasi Excel (.xlsx)</strong>: Data nilai lengkap per komponen untuk arsip sekretariat.</li>
                        <li><strong>E-Sertifikat Kelulusan</strong>: Dilengkapi nomor seri formal MPKSDI dan QR Code verifikasi.</li>
                        <li><strong>ID Card Peserta PDF</strong>: Kartu tanda peserta ber-QR siap potong ukuran standar.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- KONTEN 2: PANDUAN KADER / PESERTA --}}
    <div x-show="activeTab === 'kader'" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <span class="w-7 h-7 rounded-lg bg-sky-600 text-white font-bold text-xs flex items-center justify-center">1</span>
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base">Pendaftaran Akun &amp; Pasfoto</h3>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Calon kader mendaftar melalui form 4 langkah. Pastikan menyiapkan:
                    </p>
                    <ul class="mt-3 space-y-2 text-xs text-slate-600 list-disc list-inside">
                        <li>NIK 16 digit sesuai e-KTP dan Nomor Baku Muhammadiyah (NBM bila ada).</li>
                        <li>Pasfoto formal berlatar belakang rapi dengan rasio 3:4 (wajah tampak jelas).</li>
                        <li>Data domisili cabang (PCM) dan ranting (PRM) asal.</li>
                        <li>Setelah mendaftar, akun akan berstatus <em>Menunggu Verifikasi</em> oleh admin MPKSDI.</li>
                    </ul>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <a href="{{ route('cek-status') }}" target="_blank" class="text-xs font-semibold text-[#005c8a] hover:underline inline-flex items-center gap-1">
                        <span>Cek Status Akun Publik</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <span class="w-7 h-7 rounded-lg bg-sky-600 text-white font-bold text-xs flex items-center justify-center">2</span>
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base">Mengikuti Ujian CBT Online</h3>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Pelaksanaan Pretest dan Posttest kognitif Baitul Arqam:
                    </p>
                    <ul class="mt-3 space-y-2 text-xs text-slate-600 list-disc list-inside">
                        <li>Buka menu <strong>Ujian &amp; Tes</strong> pada Portal Kader saat sesi ujian dibuka panitia.</li>
                        <li>Perhatikan <em>countdown timer</em> di bagian atas halaman ujian.</li>
                        <li>Gunakan grid nomor soal untuk melompat antar pertanyaan (nomor hijau = sudah terjawab).</li>
                        <li>Klik <strong>Kumpulkan Jawaban</strong> dan konfirmasi modal sebelum waktu berakhir. Jawaban tersimpan otomatis.</li>
                    </ul>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <a href="{{ route('portal.tes.index') }}" class="text-xs font-semibold text-[#005c8a] hover:underline inline-flex items-center gap-1">
                        <span>Buka Menu Ujian CBT</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <span class="w-7 h-7 rounded-lg bg-sky-600 text-white font-bold text-xs flex items-center justify-center">3</span>
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base">Angket &amp; Rencana Tindak Lanjut (RTL)</h3>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Evaluasi pasca-pelatihan sebagai komitmen kepengurusan:
                    </p>
                    <ul class="mt-3 space-y-2 text-xs text-slate-600 list-disc list-inside">
                        <li>Isi angket evaluasi pelayanan instruktur dan penyelenggaraan acara.</li>
                        <li>Tuliskan rancangan program Rencana Tindak Lanjut (RTL) pada lembar kerja digital.</li>
                        <li>Unggah dokumen atau foto bukti realisasi RTL bila dipersyaratkan oleh tim instruktur.</li>
                    </ul>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <span class="w-7 h-7 rounded-lg bg-sky-600 text-white font-bold text-xs flex items-center justify-center">4</span>
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base">Unduh ID Card &amp; Sertifikat</h3>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Pengambilan dokumen tanda kepesertaan dan kelulusan:
                    </p>
                    <ul class="mt-3 space-y-2 text-xs text-slate-600 list-disc list-inside">
                        <li>Unduh <strong>ID Card Peserta PDF</strong> sebelum acara dimulai untuk presensi QR.</li>
                        <li>Setelah status kelulusan diumumkan dan nilai akhir dipublikasikan, tombol <strong>Unduh E-Sertifikat</strong> akan aktif di Portal Kader.</li>
                        <li>E-Sertifikat resmi dilengkapi QR Code yang dapat diverifikasi keabsahannya kapan saja.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- KONTEN 3: PANDUAN FASILITATOR --}}
    <div x-show="activeTab === 'fasilitator'" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs">
                <div class="flex items-center gap-3 mb-3">
                    <span class="w-7 h-7 rounded-lg bg-emerald-600 text-white font-bold text-xs flex items-center justify-center">1</span>
                    <h3 class="font-bold text-slate-900 text-sm sm:text-base">Penilaian Psikomotorik Kader</h3>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Fasilitator bertugas menguji keterampilan ibadah praktis (salat sesuai HPT Tarjih, wudu, tayamum, tilawah Quran):
                </p>
                <ul class="mt-3 space-y-2 text-xs text-slate-600 list-disc list-inside">
                    <li>Buka menu <strong>Tugas Event BA</strong> &rarr; pilih event aktif &rarr; klik tab <strong>Psikomotorik</strong>.</li>
                    <li>Pilih rubrik kriteria yang sedang diuji.</li>
                    <li>Beri skor (skala 0–100) pada tiap peserta kelompok binaan. Nilai tersimpan secara realtime.</li>
                </ul>
            </div>

            <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs">
                <div class="flex items-center gap-3 mb-3">
                    <span class="w-7 h-7 rounded-lg bg-emerald-600 text-white font-bold text-xs flex items-center justify-center">2</span>
                    <h3 class="font-bold text-slate-900 text-sm sm:text-base">Pemantauan Sikap &amp; Afektif</h3>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Evaluasi kepribadian dan adab kader selama masa perkaderan:
                </p>
                <ul class="mt-3 space-y-2 text-xs text-slate-600 list-disc list-inside">
                    <li>Buka sub-menu <strong>Afektif</strong> pada event terkait.</li>
                    <li>Amati kedisiplinan salat berjamaah tepat waktu, tadarus, kebersihan kamar, dan interaksi kelompok.</li>
                    <li>Input butir skor afektif sebelum sidang yudisium kelulusan dimulai.</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- KONTEN 4: PANDUAN EXECUTIVE --}}
    <div x-show="activeTab === 'executive'" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs">
                <div class="flex items-center gap-3 mb-3">
                    <span class="w-7 h-7 rounded-lg bg-[#002b40] text-[#f8bb00] font-bold text-xs flex items-center justify-center">1</span>
                    <h3 class="font-bold text-slate-900 text-sm sm:text-base">Executive Overview &amp; Monitoring</h3>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Pimpinan PDM memiliki hak akses pemantauan tingkat tinggi (*read-only strategic view*):
                </p>
                <ul class="mt-3 space-y-2 text-xs text-slate-600 list-disc list-inside">
                    <li>Memantau total pendaftar kader aktif se-Kabupaten Karanganyar.</li>
                    <li>Melihat persentase kelulusan perkaderan dan tren nilai rata-rata akhir.</li>
                    <li>Menganalisis peta sebaran kader per 17 Pimpinan Cabang Muhammadiyah (PCM).</li>
                </ul>
            </div>

            <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs">
                <div class="flex items-center gap-3 mb-3">
                    <span class="w-7 h-7 rounded-lg bg-[#002b40] text-[#f8bb00] font-bold text-xs flex items-center justify-center">2</span>
                    <h3 class="font-bold text-slate-900 text-sm sm:text-base">Unduh Laporan &amp; Rekapitulasi</h3>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Executive dapat mengunduh dokumen laporan resmi kapan saja:
                </p>
                <ul class="mt-3 space-y-2 text-xs text-slate-600 list-disc list-inside">
                    <li>Ekspor rekapitulasi data nilai dalam format spreadsheet Excel (.xlsx).</li>
                    <li>Mengunduh Berita Acara &amp; Laporan Kelulusan PDF untuk bahan rapat pleno Pimpinan Daerah.</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- SECTION FAQ: Pertanyaan Kerap Diajukan --}}
    <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs space-y-4">
        <div class="pb-3 border-b border-slate-100">
            <h2 class="text-base font-bold text-slate-900 tracking-tight flex items-center gap-2">
                <svg class="w-5 h-5 text-[#005c8a]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Pertanyaan yang Sering Diajukan (FAQ)</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Solusi cepat untuk kendala teknis operasional umum</p>
        </div>

        <div class="divide-y divide-slate-100 text-xs">
            {{-- FAQ 1 --}}
            <div class="py-3.5">
                <button type="button" @click="toggleFaq(1)" class="w-full flex items-center justify-between gap-4 text-left font-semibold text-slate-800 hover:text-[#005c8a] transition">
                    <span>Bagaimana jika kader lupa kata sandi login?</span>
                    <svg class="w-4 h-4 text-slate-400 transform transition-transform" :class="{ 'rotate-180': activeFaq === 1 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="activeFaq === 1" x-collapse class="mt-2 text-slate-600 leading-relaxed pl-1" style="display: none;">
                    Administrator dapat membuka detail kader di <strong>Master Anggota</strong> lalu mengklik tombol <strong>Reset Password</strong>. Kata sandi kader akan di-reset otomatis menjadi 6 digit terakhir NIK pendaftar.
                </div>
            </div>

            {{-- FAQ 2 --}}
            <div class="py-3.5">
                <button type="button" @click="toggleFaq(2)" class="w-full flex items-center justify-between gap-4 text-left font-semibold text-slate-800 hover:text-[#005c8a] transition">
                    <span>Apa yang harus dilakukan jika kamera scanner QR tidak muncul?</span>
                    <svg class="w-4 h-4 text-slate-400 transform transition-transform" :class="{ 'rotate-180': activeFaq === 2 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="activeFaq === 2" x-collapse class="mt-2 text-slate-600 leading-relaxed pl-1" style="display: none;">
                    Pastikan browser diizinkan mengakses kamera (*Permission: Allow Camera*). Jika perangkat tidak memiliki kamera web, admin/panitia dapat menggunakan fitur <strong>Presensi Manual</strong> di halaman yang sama dengan mencari nama atau NIK peserta.
                </div>
            </div>

            {{-- FAQ 3 --}}
            <div class="py-3.5">
                <button type="button" @click="toggleFaq(3)" class="w-full flex items-center justify-between gap-4 text-left font-semibold text-slate-800 hover:text-[#005c8a] transition">
                    <span>Apakah lembar jawaban ujian CBT yang sudah dikumpulkan bisa diulang?</span>
                    <svg class="w-4 h-4 text-slate-400 transform transition-transform" :class="{ 'rotate-180': activeFaq === 3 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="activeFaq === 3" x-collapse class="mt-2 text-slate-600 leading-relaxed pl-1" style="display: none;">
                    Tidak bisa. Demi menjaga objektivitas dan integritas perkaderan, sistem otomatis mengunci lembar ujian setelah peserta mengklik submit atau waktu countdown habis.
                </div>
            </div>

            {{-- FAQ 4 --}}
            <div class="py-3.5">
                <button type="button" @click="toggleFaq(4)" class="w-full flex items-center justify-between gap-4 text-left font-semibold text-slate-800 hover:text-[#005c8a] transition">
                    <span>Mengapa tombol unduh sertifikat tidak muncul di portal kader?</span>
                    <svg class="w-4 h-4 text-slate-400 transform transition-transform" :class="{ 'rotate-180': activeFaq === 4 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="activeFaq === 4" x-collapse class="mt-2 text-slate-600 leading-relaxed pl-1" style="display: none;">
                    Sertifikat hanya diterbitkan bagi peserta yang dinyatakan <strong>LULUS</strong> dan status kegiatan Baitul Arqam telah diubah menjadi <strong>Selesai</strong> oleh Administrator.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
