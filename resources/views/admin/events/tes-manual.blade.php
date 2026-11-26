@extends('layouts.app')

@php $label = $tipe === 'pretest' ? 'Pretest' : 'Posttest'; @endphp

@section('title', 'Input Jawaban Kertas '.$label.' — '.$event->nama_event)

@section('content')
<script>
    window.tesManualRow = (kunci, awal) => ({
        val: awal || '',
        scan: null,
        isiDariScan(d) {
            const sebelum = this.val.trim();
            this.scan = Object.assign({}, d, { sebelum: sebelum && sebelum.toUpperCase().replace(/\s+/g, '') !== d.jawaban ? sebelum : '' });
            this.val = d.jawaban;
        },
        kembalikan() { this.val = this.scan.sebelum; this.scan = null; },
        get bersih() { return this.val.toUpperCase().replace(/\s+/g, ''); },
        get invalid() { return /[^A-E-]/.test(this.bersih); },
        get benar() {
            let b = 0;
            for (let i = 0; i < kunci.length; i++) { if (this.bersih[i] && this.bersih[i] === kunci[i]) b++; }
            return b;
        },
        get skor() { return kunci.length ? (this.benar / kunci.length * 100).toFixed(1) : '0.0'; },
        get lengkap() { return this.bersih.length === kunci.length && !this.invalid; },
        get pesan() {
            if (this.bersih.includes('?')) return 'Ada jawaban ganda (?) — cek kertas';
            if (this.invalid) return 'Huruf tidak valid';
            return this.benar + ' benar · ' + this.bersih.length + '/' + kunci.length + ' terisi';
        },
    });

    window.omrScanner = (layout, ctx, peserta) => {
        let stream = null, timer = null, kanvas = null, audio = null, membuka = false, uid = 0;
        let kandidat = null, stabil = 0, kandidatId = null, stabilId = 0, menunggu = null, kosongBeruntun = 0, riwayat = [];
        let sudahIds = new Set(), sudahKosong = new Set();
        const itemBaru = (nama, pesan) => ({ uid: ++uid, nama, status: 'proses', pesan, pratinjau: null, jawaban: '', ragu: [], pilihId: '', timpaId: null });
        const resetKandidat = () => { kandidat = null; stabil = 0; kandidatId = null; stabilId = 0; riwayat = []; };

        return {
        hasil: [],
        proses: 0,
        terisi: {},
        kamera: false,
        kameraPesan: '',
        kameraOk: false,
        jumlahKamera: 0,
        olah(item, r) {
            if (!r.ok) {
                Object.assign(item, { status: 'gagal', pesan: r.pesan });
                return;
            }
            Object.assign(item, { jawaban: r.jawaban, ragu: r.ragu, pratinjau: r.pratinjau });
            if (r.anggotaId === 0) {
                Object.assign(item, { status: 'pilih', pesan: 'Lembar kosong tanpa nama — pilih pesertanya.' });
            } else {
                this.terapkan(item, r.anggotaId);
            }
        },
        async mulaiKamera() {
            if (this.kamera || membuka) return;
            this.kameraPesan = '';
            if (!window.isSecureContext || !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.kameraPesan = 'Kamera hanya bisa dipakai lewat alamat https:// (atau localhost). Gunakan tombol foto sebagai gantinya.';
                return;
            }
            membuka = true;
            try {
                audio = audio || new (window.AudioContext || window.webkitAudioContext)();
                if (audio.state === 'suspended') audio.resume();
            } catch (e) { audio = null; }
            let s;
            try {
                s = await navigator.mediaDevices.getUserMedia({
                    audio: false,
                    video: { facingMode: { ideal: 'environment' }, width: { ideal: 1920 }, height: { ideal: 1080 } },
                });
            } catch (err) {
                membuka = false;
                this.kameraPesan = err && err.name === 'NotAllowedError'
                    ? 'Izin kamera ditolak. Izinkan kamera di pengaturan browser lalu coba lagi.'
                    : 'Kamera tidak bisa dibuka: ' + (err && err.message ? err.message : 'tidak ditemukan') + '.';
                return;
            }
            membuka = false;
            stream = s;
            stream.getVideoTracks().forEach(t => t.addEventListener('ended', () => {
                if (stream === s) { this.tutupKamera(); this.kameraPesan = 'Kamera terputus. Tekan tombol scan lagi untuk melanjutkan.'; }
            }));
            kanvas = kanvas || document.createElement('canvas');
            resetKandidat();
            menunggu = null; kosongBeruntun = 0;
            sudahIds = new Set(); sudahKosong = new Set();
            document.documentElement.style.overflow = 'hidden';
            this.kamera = true;
            this.kameraOk = false;
            this.kameraPesan = 'Arahkan kamera ke lembar jawaban sampai keempat kotak hitam terlihat.';
            await this.$nextTick();
            const v = document.getElementById('omr-video');
            v.srcObject = stream;
            try { await v.play(); } catch (e) {}
            this.putar();
        },
        tutupKamera() {
            this.kamera = false;
            clearTimeout(timer);
            document.documentElement.style.overflow = '';
            if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
            const o = document.getElementById('omr-overlay');
            if (o) o.getContext('2d').clearRect(0, 0, o.width, o.height);
        },
        putar() {
            if (!this.kamera) return;
            const v = document.getElementById('omr-video');
            if (v && v.readyState >= 2) {
                let r;
                try { r = window.OmrLembar.bacaVideo(v, layout, Object.assign({}, ctx, { pratinjau: false }), kanvas, 1280); }
                catch (e) { r = { ok: false }; }
                this.gambarSudut(r);
                this.prosesFrame(r);
            }
            timer = setTimeout(() => this.putar(), 220);
        },
        gambarSudut(r) {
            const v = document.getElementById('omr-video'), o = document.getElementById('omr-overlay');
            if (!v || !o) return;
            o.width = o.clientWidth;
            o.height = o.clientHeight;
            const g = o.getContext('2d');
            g.clearRect(0, 0, o.width, o.height);
            if (!r.ok || !r.sudut) return;
            const s = Math.min(o.width / v.videoWidth, o.height / v.videoHeight);
            const ox = (o.width - v.videoWidth * s) / 2, oy = (o.height - v.videoHeight * s) / 2;
            const k = r.lebar / v.videoWidth;
            const pt = ([x, y]) => [ox + (x / k) * s, oy + (y / k) * s];
            g.strokeStyle = menunggu !== null && r.anggotaId === menunggu ? '#38bdf8' : '#22c55e';
            g.lineWidth = 4;
            g.beginPath();
            r.sudut.map(pt).forEach(([x, y], i) => i ? g.lineTo(x, y) : g.moveTo(x, y));
            g.closePath();
            g.stroke();
        },
        prosesFrame(r) {
            if (!r.ok) {
                this.kameraOk = false;
                resetKandidat();
                if (++kosongBeruntun >= 3) menunggu = null;
                this.kameraPesan = menunggu !== null
                    ? 'Terbaca. Ganti ke lembar berikutnya…'
                    : 'Arahkan kamera ke lembar jawaban sampai keempat kotak hitam terlihat.';
                return;
            }
            kosongBeruntun = 0;
            this.kameraOk = true;
            const p = peserta[r.anggotaId];
            if (menunggu !== null && r.anggotaId === menunggu) {
                this.kameraPesan = 'Sudah terbaca. Ganti ke lembar berikutnya…';
                return;
            }
            if (r.anggotaId !== 0 && sudahIds.has(r.anggotaId)) {
                this.kameraPesan = (p ? 'No. ' + p.no + ' ' + p.nama : 'Lembar ini') + ' sudah discan. Ganti lembar.';
                return;
            }
            if (r.anggotaId === 0 && sudahKosong.has(r.jawaban)) {
                this.kameraPesan = 'Lembar tanpa nama ini sudah discan. Ganti lembar.';
                return;
            }
            const kunci = r.anggotaId + ':' + r.jawaban;
            stabil = kunci === kandidat ? stabil + 1 : 1;
            kandidat = kunci;
            if (r.anggotaId !== kandidatId) riwayat = [];
            stabilId = r.anggotaId === kandidatId ? stabilId + 1 : 1;
            kandidatId = r.anggotaId;
            riwayat.push(r.jawaban);
            if (stabil < 2 && stabilId < 4) {
                this.kameraPesan = 'Lembar terdeteksi, tahan sebentar…';
                return;
            }
            const lengkap = window.OmrLembar.bacaCanvas(kanvas, layout, ctx);
            const final = lengkap.ok && lengkap.anggotaId === r.anggotaId ? lengkap : r;
            const goyah = [];
            for (let i = 0; i < final.jawaban.length; i++) {
                if (riwayat.some(j => j[i] !== final.jawaban[i])) goyah.push(i + 1);
            }
            final.ragu = Array.from(new Set(final.ragu.concat(goyah))).sort((a, b) => a - b);
            const item = itemBaru('Kamera', '');
            this.hasil.unshift(item);
            this.olah(this.hasil[0], final);
            const berhasil = this.hasil[0].status !== 'gagal';
            if (berhasil) this.jumlahKamera++;
            this.hasil[0].nama = 'Kamera #' + (berhasil ? this.jumlahKamera : '–');
            if (final.anggotaId !== 0) sudahIds.add(final.anggotaId); else sudahKosong.add(final.jawaban);
            menunggu = final.anggotaId;
            resetKandidat();
            this.kameraPesan = this.hasil[0].pesan || 'Terbaca.';
            this.bip(berhasil);
        },
        bip(berhasil = true) {
            try {
                if (audio) {
                    if (audio.state === 'suspended') audio.resume();
                    const nada = berhasil ? [[1320, 0]] : [[330, 0], [330, 0.2]];
                    nada.forEach(([f, t]) => {
                        const o = audio.createOscillator(), g = audio.createGain();
                        o.frequency.value = f;
                        g.gain.value = 0.12;
                        o.connect(g); g.connect(audio.destination);
                        o.start(audio.currentTime + t); o.stop(audio.currentTime + t + 0.14);
                    });
                }
            } catch (e) {}
            if (navigator.vibrate) navigator.vibrate(berhasil ? 80 : [60, 80, 60]);
        },
        async pilih(e) {
            const files = Array.from(e.target.files || []);
            e.target.value = '';
            for (const file of files) {
                this.proses++;
                this.hasil.unshift(itemBaru(file.name, 'Membaca…'));
                const item = this.hasil[0];
                try {
                    this.olah(item, await window.OmrLembar.baca(file, layout, ctx));
                } catch (err) {
                    Object.assign(item, { status: 'gagal', pesan: err.message || 'Gagal membaca foto.' });
                }
                this.proses--;
            }
        },
        terapkan(item, id, paksa = false) {
            const p = peserta[id];
            if (!p) { Object.assign(item, { status: 'gagal', pesan: 'Peserta di lembar ini tidak terdaftar aktif di kegiatan ini.' }); return; }
            if (p.online) { Object.assign(item, { status: 'gagal', pesan: 'No. ' + p.no + ' ' + p.nama + ' sudah mengerjakan online — dilewati.' }); return; }
            const lama = this.terisi[id];
            if (lama && lama !== item && !paksa) {
                Object.assign(item, { status: 'timpa', timpaId: id, pesan: 'No. ' + p.no + ' ' + p.nama + ' sudah terisi dari foto ' + lama.nama + '. Foto ganda?' });
                return;
            }
            if (lama && lama !== item) {
                Object.assign(lama, { status: 'gagal', pesan: 'Ditimpa oleh foto ' + item.nama + '.' });
            }
            this.terisi[id] = item;
            window.dispatchEvent(new CustomEvent('omr-isi', { detail: { id: Number(id), jawaban: item.jawaban, ragu: item.ragu } }));
            const kosong = !/[A-Z]/.test(item.jawaban);
            const catatan = kosong ? ' — tidak ada bulatan hitam terbaca, cek kertas' : (item.ragu.length ? ' — cek soal ' + item.ragu.join(', ') : '');
            Object.assign(item, { status: kosong || item.ragu.length ? 'cek' : 'ok', timpaId: null, pesan: 'Masuk ke No. ' + p.no + ' ' + p.nama + catatan });
        },
        };
    };
</script>
@if($layout)
<script src="{{ asset('aset/js/omr-lembar.js') }}?v={{ @filemtime(public_path('aset/js/omr-lembar.js')) }}"></script>
@endif

<div class="space-y-6">
    <div>
        <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200/90 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-600 transition shadow-2xs mb-3">
            &larr; Kembali ke {{ $event->nama_event }}
        </a>
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Input Jawaban Kertas — {{ $label }}</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl">
                        Untuk peserta yang mengerjakan {{ strtolower($label) }} di lembar kertas. Ketik jawaban berurutan, satu huruf per soal
                        (contoh <span class="font-mono font-semibold text-slate-700">ACBDA BCDAB</span>; spasi boleh). Pakai <span class="font-mono font-semibold">-</span> untuk soal yang tidak dijawab.
                        Nilai dihitung otomatis dengan rumus yang sama seperti tes online.
                    </p>
                </div>
                <div class="flex gap-2">
                    @foreach(['pretest' => 'Pretest', 'posttest' => 'Posttest'] as $t => $l)
                        <a href="{{ route('admin.events.tes-manual.index', [$event, $t]) }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition {{ $t === $tipe ? 'bg-sky-600 text-white border-sky-600' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">{{ $l }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @if($soal->isEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 text-sm text-amber-900">
            Bank soal {{ strtolower($label) }} masih kosong.
            <a href="{{ route('admin.events.soal.index', [$event, $tipe]) }}" class="font-semibold underline">Tambahkan soal</a> terlebih dahulu.
        </div>
    @else
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-2xs">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4 pb-4 border-b border-slate-100">
                <p class="text-xs text-slate-500">Cetak untuk peserta yang mengerjakan di kertas. Lembar jawaban sudah bernama dan bernomor sesuai urutan tabel di bawah (plus 1 lembar kosong).</p>
                <div class="flex gap-2">
                    <a href="{{ route('admin.events.tes-manual.cetak', [$event, $tipe, 'soal']) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 transition">Cetak Lembar Soal</a>
                    <a href="{{ route('admin.events.tes-manual.cetak', [$event, $tipe, 'jawaban']) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 transition">Cetak Lembar Jawaban</a>
                </div>
            </div>
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-slate-900 text-sm">Kunci Jawaban ({{ $soal->count() }} soal)</h2>
                @if(str_contains($kunci, '?'))
                    <span class="text-xs font-semibold text-rose-700">Ada soal tanpa kunci (?) — periksa bank soal.</span>
                @endif
            </div>
            <div class="flex flex-wrap gap-x-4 gap-y-2 font-mono text-sm">
                @foreach(str_split($kunci, 5) as $g => $kelompok)
                    <div class="flex flex-col">
                        <span class="text-[10px] text-slate-400">{{ $g * 5 + 1 }}–{{ $g * 5 + strlen($kelompok) }}</span>
                        <span class="font-bold tracking-[0.3em] text-slate-800">{{ $kelompok }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        @if($layout)
        @php
            $pesertaScan = $peserta->values()->mapWithKeys(fn ($ep, $i) => [$ep->anggota_id => [
                'no' => $i + 1,
                'nama' => $ep->anggota->nama_lengkap,
                'online' => (bool) (($st = $status->get($ep->anggota_id)) && $st->sumber === 'online' && $st->waktu_selesai),
            ]]);
        @endphp
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-2xs"
             x-data="omrScanner(@js($layout), @js(['eventId' => $event->id, 'tipe' => $tipe]), @js($pesertaScan))"
             @pagehide.window="tutupKamera()" @keydown.escape.window="kamera && tutupKamera()"
             @visibilitychange.document="if (document.hidden && kamera) { tutupKamera(); kameraPesan = 'Kamera dimatikan karena halaman ditinggalkan. Tekan tombol scan lagi untuk melanjutkan.'; }">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="max-w-2xl">
                    <h2 class="font-bold text-slate-900 text-sm">Pindai Foto Lembar Jawaban</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Foto lembar jawaban (cetakan dari tombol di atas) lurus dari atas dengan keempat kotak hitam terlihat. Bisa pilih banyak foto sekaligus.
                        Foto <b>dibaca di perangkat ini</b>, tidak diunggah ke server. Hasilnya otomatis mengisi baris peserta di tabel &mdash;
                        <b>cocokkan dengan kertas</b>, terutama soal yang ditandai, lalu klik Simpan.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="mulaiKamera()" :disabled="kamera" class="disabled:opacity-60 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-2xs transition">Scan Langsung dengan Kamera</button>
                    <label class="cursor-pointer px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg shadow-2xs transition">
                        <span x-text="proses ? 'Membaca ' + proses + ' foto…' : 'Pilih / Ambil Foto'">Pilih / Ambil Foto</span>
                        <input type="file" accept="image/*" multiple class="hidden" @change="pilih($event)">
                    </label>
                </div>
            </div>
            <p class="mt-3 text-xs font-semibold text-rose-700" x-show="!kamera && kameraPesan" x-text="kameraPesan" x-cloak></p>

            <template x-teleport="body">
            <div x-show="kamera" x-cloak class="fixed inset-0 z-[1000] bg-black flex flex-col" role="dialog" aria-modal="true" aria-label="Scan lembar jawaban dengan kamera">
                <div class="relative flex-1 min-h-0">
                    <video id="omr-video" playsinline muted autoplay class="absolute inset-0 w-full h-full object-contain"></video>
                    <canvas id="omr-overlay" class="absolute inset-0 w-full h-full pointer-events-none"></canvas>
                </div>
                <div class="bg-slate-950 text-white px-4 py-3 flex flex-wrap items-center gap-3">
                    <span class="w-2.5 h-2.5 rounded-full shrink-0" :class="kameraOk ? 'bg-emerald-400' : 'bg-slate-500'"></span>
                    <p class="flex-1 min-w-[180px] text-sm" x-text="kameraPesan" aria-live="polite"></p>
                    <span class="text-xs text-slate-400" x-text="jumlahKamera + ' lembar terbaca'"></span>
                    <button type="button" @click="tutupKamera()" class="px-4 py-2 bg-white text-slate-900 text-xs font-bold rounded-lg">Selesai</button>
                </div>
            </div>
            </template>
            <template x-if="hasil.length">
                <ul class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <template x-for="h in hasil" :key="h.uid">
                        <li class="border rounded-lg p-3 text-xs"
                            :class="{ 'border-emerald-200 bg-emerald-50/50': h.status === 'ok', 'border-amber-300 bg-amber-50/60': ['cek', 'pilih', 'timpa'].includes(h.status), 'border-rose-200 bg-rose-50/60': h.status === 'gagal', 'border-slate-200': h.status === 'proses' }">
                            <div class="font-semibold text-slate-700 truncate" x-text="h.nama"></div>
                            <div class="mt-1" :class="h.status === 'gagal' ? 'text-rose-700' : 'text-slate-600'" x-text="h.pesan"></div>
                            <div class="mt-1 font-mono tracking-wider text-slate-800 break-all" x-show="h.jawaban" x-text="h.jawaban"></div>
                            <template x-if="h.status === 'timpa'">
                                <button type="button" class="mt-2 px-2 py-1 rounded-md border border-amber-400 bg-white text-amber-900 font-semibold" @click="terapkan(h, h.timpaId, true)">Pakai foto ini (timpa)</button>
                            </template>
                            <template x-if="h.status === 'pilih'">
                                <div class="mt-2 flex gap-2">
                                    <select x-model="h.pilihId" class="flex-1 min-w-0 border border-slate-200 rounded-md px-2 py-1 text-xs">
                                        <option value="">&mdash; pilih peserta &mdash;</option>
                                        @foreach($pesertaScan as $aid => $ps)
                                            @unless($ps['online'])<option value="{{ $aid }}">{{ $ps['no'] }}. {{ $ps['nama'] }}</option>@endunless
                                        @endforeach
                                    </select>
                                    <button type="button" class="px-2 py-1 rounded-md bg-sky-600 text-white font-semibold disabled:opacity-40" :disabled="!h.pilihId" @click="terapkan(h, h.pilihId)">Isi</button>
                                </div>
                            </template>
                            <details class="mt-2" x-show="h.pratinjau">
                                <summary class="cursor-pointer text-slate-500">Lihat hasil deteksi</summary>
                                <img :src="h.pratinjau" alt="Pratinjau deteksi" class="mt-2 w-full rounded border border-slate-200">
                                <p class="mt-1 text-[11px] text-slate-500">Hijau = terbaca jelas, kuning = perlu dicek.</p>
                            </details>
                        </li>
                    </template>
                </ul>
            </template>
        </div>
        @elseif($layoutError)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-900">Pindai foto tidak tersedia: {{ $layoutError }} Jawaban tetap bisa diketik manual.</div>
        @endif

        @php
            $bisaDiisi = $peserta->contains(fn ($ep) => ! (($st = $status->get($ep->anggota_id)) && $st->sumber === 'online' && $st->waktu_selesai));
        @endphp
        <form method="POST" action="{{ route('admin.events.tes-manual.store', [$event, $tipe]) }}" class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
            @csrf
            @error('jawaban')
                <div class="px-4 py-3 bg-rose-50 border-b border-rose-100 text-xs font-semibold text-rose-700">Belum ada jawaban yang diisi.</div>
            @enderror
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="py-3 px-4 text-left w-10">#</th>
                            <th class="py-3 px-4 text-left">Peserta</th>
                            <th class="py-3 px-4 text-left">Jawaban (urut nomor soal)</th>
                            <th class="py-3 px-4 text-center w-36">Hasil</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($peserta as $i => $ep)
                            @php
                                $aid = $ep->anggota_id;
                                $st = $status->get($aid);
                                $online = $st && $st->sumber === 'online' && $st->waktu_selesai;
                                $manual = $st && $st->sumber === 'manual';
                                $sedangOnline = $st && $st->sumber === 'online' && ! $st->waktu_selesai;
                                $awal = old("jawaban.$aid", $tersimpan[$aid] ?? '');
                            @endphp
                            <tr class="align-top {{ $errors->has("jawaban.$aid") ? 'bg-rose-50/60' : '' }}" @unless($online) x-data="tesManualRow(@js($kunci), @js($awal))" @omr-isi.window="if ($event.detail.id === {{ $aid }}) { isiDariScan($event.detail); $el.scrollIntoView({ block: 'center', behavior: 'smooth' }); }" :class="scan ? 'bg-amber-50/40' : ''" @endunless>
                                <td class="py-3 px-4 text-slate-400 font-mono text-xs">{{ $i + 1 }}</td>
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-slate-900">{{ $ep->anggota->nama_lengkap }}</div>
                                    <div class="mt-1">
                                        @if($online)
                                            <span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold bg-sky-50 text-sky-800 border border-sky-200">Online</span>
                                        @elseif($manual)
                                            <span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-900 border border-amber-200">Kertas</span>
                                            <span class="text-[11px] text-slate-400">{{ $st->updated_at?->translatedFormat('d M H:i') }}</span>
                                            <button type="submit" form="hapus-{{ $aid }}" class="ml-1 text-[11px] font-semibold text-rose-600 hover:text-rose-800 hover:underline">Hapus</button>
                                        @else
                                            @if($sedangOnline)
                                                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-900 border border-amber-200" title="Peserta sudah membuka tes online tetapi belum mengirim. Jika diisi dari kertas, tes onlinenya ditutup.">Sedang online</span>
                                            @else
                                                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-500">Belum</span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                                @if($online)
                                    <td class="py-3 px-4 text-xs text-slate-500">Sudah mengerjakan online — nilai tidak bisa ditimpa dari sini.</td>
                                    <td class="py-3 px-4 text-center font-mono font-bold text-slate-800">{{ number_format((float) ($nilai[$aid] ?? 0), 1) }}</td>
                                @else
                                    <td class="py-3 px-4">
                                        <input type="text" name="jawaban[{{ $aid }}]" x-model="val"
                                               autocomplete="off" spellcheck="false" autocapitalize="characters"
                                               placeholder="{{ str_repeat('·', min(40, $soal->count())) }}"
                                               class="w-full min-w-[260px] px-3 py-2 font-mono text-sm uppercase tracking-[0.25em] bg-slate-50/60 border rounded-lg outline-none focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition"
                                               :class="invalid ? 'border-rose-400' : 'border-slate-200/90'">
                                        <div class="mt-1.5 flex flex-wrap gap-px font-mono text-[11px]" x-show="bersih.length">
                                            <template x-for="(c, idx) in bersih.split('')" :key="idx">
                                                <span class="w-4 text-center rounded-sm"
                                                      :class="c === @js($kunci)[idx] ? 'bg-emerald-100 text-emerald-800' : (c === '-' ? 'bg-slate-100 text-slate-400' : 'bg-rose-100 text-rose-700')"
                                                      :title="'Soal ' + (idx + 1)" x-text="c"></span>
                                            </template>
                                        </div>
                                        <p class="mt-1 text-[11px] font-semibold text-amber-700" x-show="scan" x-cloak>
                                            Dari foto &mdash; cocokkan dengan kertas<span x-show="scan && scan.ragu.length" x-text="'; cek soal ' + (scan ? scan.ragu.join(', ') : '')"></span>.
                                        </p>
                                        <p class="mt-1 text-[11px] text-slate-600" x-show="scan && scan.sebelum" x-cloak>
                                            Menggantikan isian sebelumnya <span class="font-mono" x-text="scan ? scan.sebelum : ''"></span>.
                                            <button type="button" class="ml-1 font-semibold text-sky-700 hover:underline" @click="kembalikan()">Kembalikan</button>
                                        </p>
                                        @error("jawaban.$aid")
                                            <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <template x-if="bersih.length">
                                            <div>
                                                <div class="font-mono font-bold text-slate-900" x-text="skor"></div>
                                                <div class="text-[11px]" :class="lengkap ? 'text-slate-500' : 'text-rose-600 font-semibold'"
                                                     x-text="pesan"></div>
                                            </div>
                                        </template>
                                        <template x-if="!bersih.length">
                                            <span class="text-xs text-slate-300">—</span>
                                        </template>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 bg-slate-50 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                <p class="text-xs text-slate-500">Hanya baris yang baru diisi atau diubah yang disimpan. Jika ada baris yang salah, tidak ada yang disimpan.</p>
                @if($bisaDiisi)
                    <button class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold rounded-lg shadow-2xs transition">Simpan &amp; Hitung Nilai</button>
                @else
                    <span class="text-xs font-semibold text-slate-500">{{ $peserta->isEmpty() ? 'Belum ada peserta aktif.' : 'Semua peserta sudah mengerjakan online.' }}</span>
                @endif
            </div>
        </form>

        @foreach($peserta as $ep)
            @if(optional($status->get($ep->anggota_id))->sumber === 'manual')
                <form id="hapus-{{ $ep->anggota_id }}" method="POST" action="{{ route('admin.events.tes-manual.destroy', [$event, $tipe, $ep->anggota_id]) }}" class="hidden"
                      onsubmit="return confirm({{ \Illuminate\Support\Js::from('Hapus input kertas '.strtolower($label).' untuk '.$ep->anggota->nama_lengkap.'? Nilainya kembali 0 dan peserta bisa mengerjakan lagi.') }})">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        @endforeach
    @endif
</div>
@endsection
