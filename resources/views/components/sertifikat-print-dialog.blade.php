@props(['id', 'event', 'items'])

{{--
    Dialog cetak sertifikat panitia: bisa dipakai kapan saja. Peserta Lulus/Lulus Bersyarat tercentang otomatis,
    peserta lain bisa dicentang manual (dicetak sebagai "Peserta"). Mode cetak mendukung printer duplex maupun
    bolak-balik manual (depan dulu, lalu belakang).
--}}
<x-bulk-print-dialog
    :id="$id"
    title="Pilih Peserta untuk Cetak Sertifikat"
    subtitle="Yang Lulus / Lulus Bersyarat sudah tercentang. Peserta lain boleh dicentang dan akan dicetak sebagai “Peserta” sesuai nilai saat ini."
    :items="$items"
    :default-badges="\App\Support\SertifikatPilihan::STATUS_LULUS"
    :send-all-ids="true"
    quickBadge="Lulus"
    quickBadgeLabel="Hanya Lulus Murni"
    :actions="[
        ['label' => 'Pratinjau PDF', 'url' => route('admin.events.certificates.batch', $event), 'target' => '_blank', 'download' => false, 'class' => 'border border-slate-200 bg-white hover:bg-slate-50 text-slate-700'],
        ['label' => 'Unduh ZIP', 'url' => route('admin.events.certificates.zip', $event), 'target' => '_self', 'download' => false, 'class' => 'border border-indigo-300 bg-indigo-50 hover:bg-indigo-100 text-indigo-800'],
        ['label' => 'Unduh 1 PDF', 'url' => route('admin.events.certificates.batch', $event), 'target' => '_self', 'download' => true, 'class' => 'bg-indigo-600 hover:bg-indigo-700 text-white'],
    ]"
>
    <div x-data="{ sisi: 'duplex', balik: false }" data-mode-cetak>
        <input type="hidden" name="sisi" :value="sisi" form="{{ $id }}-form">
        <template x-if="sisi === 'belakang' && balik">
            <input type="hidden" name="balik" value="1" form="{{ $id }}-form">
        </template>

        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Mode cetak (PDF)</p>
        <div class="grid grid-cols-3 gap-1.5" role="radiogroup" aria-label="Mode cetak sertifikat">
            @foreach([
                'duplex' => ['Bolak-balik', 'Printer duplex'],
                'depan' => ['Depan saja', 'Sertifikat'],
                'belakang' => ['Belakang saja', 'Transkrip'],
            ] as $nilai => [$judul, $ket])
                <label class="cursor-pointer rounded-lg border px-2 py-1.5 text-center transition"
                       :class="sisi === '{{ $nilai }}' ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500' : 'border-slate-200 bg-white hover:bg-slate-50'">
                    <input type="radio" class="sr-only" value="{{ $nilai }}" x-model="sisi">
                    <span class="block text-xs font-bold text-slate-800">{{ $judul }}</span>
                    <span class="block text-[10px] text-slate-500">{{ $ket }}</span>
                </label>
            @endforeach
        </div>

        <label class="mt-2 flex items-start gap-2 text-[11px] text-slate-600 cursor-pointer" x-show="sisi === 'belakang'" x-cloak>
            <input type="checkbox" x-model="balik" class="mt-0.5 w-3.5 h-3.5 rounded border-slate-300 text-indigo-600">
            <span>Urutan dibalik &mdash; centang bila tumpukan hasil cetak depan berurutan terbalik (peserta terakhir di paling atas).</span>
        </label>

        <details class="mt-2 text-[11px] text-slate-600">
            <summary class="cursor-pointer font-semibold text-indigo-700">Panduan setelan printer</summary>
            <ul class="mt-1.5 space-y-1 list-disc pl-4">
                <li>Kertas <b>F4 (215 × 330 mm)</b>, orientasi <b>Landscape</b>, skala <b>100% / Actual size</b> (bukan "Fit to page").</li>
                <li><b>Printer duplex:</b> pilih mode <i>Bolak-balik</i>, lalu di printer aktifkan <b>2 sisi, balik di sisi pendek</b> (<i>Flip on short edge</i>).</li>
                <li><b>Printer biasa:</b> (1) cetak <i>Depan saja</i>; (2) masukkan lagi tumpukan kertas ke baki dengan sisi kosong siap dicetak &mdash; uji dulu dengan 1 lembar agar arah kertas benar; (3) cetak <i>Belakang saja</i> dengan pilihan peserta yang sama.</li>
                <li>Unduh ZIP selalu berisi 1 file per peserta (depan + belakang).</li>
            </ul>
        </details>
    </div>
</x-bulk-print-dialog>
