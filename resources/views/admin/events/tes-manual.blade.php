@extends('layouts.app')

@php $label = $tipe === 'pretest' ? 'Pretest' : 'Posttest'; @endphp

@section('title', 'Input Jawaban Kertas '.$label.' — '.$event->nama_event)

@section('content')
<script>
    window.tesManualRow = (kunci, awal) => ({
        val: awal || '',
        scan: null,
        isiDariScan(d) { this.val = d.jawaban; this.scan = d; },
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

    window.omrScanner = (layout, ctx, peserta) => ({
        hasil: [],
        proses: 0,
        terisi: {},
        async pilih(e) {
            const files = Array.from(e.target.files || []);
            e.target.value = '';
            for (const file of files) {
                this.proses++;
                this.hasil.unshift({ nama: file.name, status: 'proses', pesan: 'Membaca…', pratinjau: null, jawaban: '', ragu: [], pilihId: '', timpaId: null });
                const item = this.hasil[0];
                try {
                    const r = await window.OmrLembar.baca(file, layout, ctx);
                    if (!r.ok) {
                        Object.assign(item, { status: 'gagal', pesan: r.pesan });
                    } else {
                        Object.assign(item, { jawaban: r.jawaban, ragu: r.ragu, pratinjau: r.pratinjau });
                        if (r.anggotaId === 0) {
                            Object.assign(item, { status: 'pilih', pesan: 'Lembar kosong tanpa nama — pilih pesertanya.' });
                        } else {
                            this.terapkan(item, r.anggotaId);
                        }
                    }
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
    });
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
             x-data="omrScanner(@js($layout), @js(['eventId' => $event->id, 'tipe' => $tipe]), @js($pesertaScan))">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="max-w-2xl">
                    <h2 class="font-bold text-slate-900 text-sm">Pindai Foto Lembar Jawaban</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Foto lembar jawaban (cetakan dari tombol di atas) lurus dari atas dengan keempat kotak hitam terlihat. Bisa pilih banyak foto sekaligus.
                        Foto <b>dibaca di perangkat ini</b>, tidak diunggah ke server. Hasilnya otomatis mengisi baris peserta di tabel &mdash;
                        <b>cocokkan dengan kertas</b>, terutama soal yang ditandai, lalu klik Simpan.
                    </p>
                </div>
                <label class="cursor-pointer px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg shadow-2xs transition">
                    <span x-text="proses ? 'Membaca ' + proses + ' foto…' : 'Pilih / Ambil Foto'">Pilih / Ambil Foto</span>
                    <input type="file" accept="image/*" multiple class="hidden" @change="pilih($event)">
                </label>
            </div>
            <template x-if="hasil.length">
                <ul class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <template x-for="(h, i) in hasil" :key="i">
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
