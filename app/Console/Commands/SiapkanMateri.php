<?php

namespace App\Console\Commands;

use App\Models\AfektifButir;
use App\Models\AfektifSubAspek;
use App\Models\AngketItem;
use App\Models\Event;
use App\Models\PsikomotorTemplate;
use App\Models\RtlSoal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SiapkanMateri extends Command
{
    protected $signature = 'ba:siapkan-materi {event : ID event yang akan diisi rubrik default}';

    protected $description = 'Isi rubrik psikomotor, butir afektif, dan pertanyaan RTL default untuk sebuah event (diadaptasi dari Arqam Apps referensi).';

    public function handle(): int
    {
        $event = Event::find($this->argument('event'));

        if (! $event) {
            $this->error('Event tidak ditemukan.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($event) {
            $this->seedPsikomotor($event);
            $this->seedAfektif($event);
            $this->seedRtl($event);
            $this->seedAngket($event);
        });

        $this->info("Rubrik default untuk '{$event->nama_event}' siap. Soal pretest/posttest diisi manual oleh admin.");

        return self::SUCCESS;
    }

    private function seedPsikomotor(Event $event): void
    {
        if (PsikomotorTemplate::where('event_id', $event->id)->exists()) {
            return;
        }

        $templates = [
            'Praktik Wudhu dan Tayamum' => 'ibadah',
            'Praktik Shalat Fardhu dan Jama Qashar' => 'ibadah',
            'Tahsin Membaca Al-Quran' => 'ibadah',
            'Kerja Sama Tim' => 'outbound',
            'Kedisiplinan' => 'outbound',
            'Semangat dan Keaktifan' => 'outbound',
        ];

        foreach ($templates as $nama => $jenis) {
            PsikomotorTemplate::create([
                'event_id' => $event->id,
                'nama_aspek' => $nama,
                'jenis' => $jenis,
                'skor_maks' => 4,
            ]);
        }
    }

    private function seedAfektif(Event $event): void
    {
        if (AfektifSubAspek::where('event_id', $event->id)->exists()) {
            return;
        }

        $subAspeks = [
            'A. Sikap Keislaman' => [
                ['Saya melaksanakan shalat berjamaah lima waktu di masjid.', true],
                ['Saya sering mengabaikan tilawah Al-Quran harian.', false],
                ['Saya antusias mengikuti kajian keislaman.', true],
                ['Membayar zakat dan sedekah terasa memberatkan bagi saya.', false],
            ],
            'B. Sikap Kemuhammadiyahan' => [
                ['Saya bangga dan memahami identitas Muhammadiyah sebagai gerakan dakwah tajdid.', true],
                ['Saya kurang tertarik menyosialisasikan program Muhammadiyah.', false],
                ['Saya aktif mendukung program amal usaha Muhammadiyah.', true],
                ['Aktivitas Ranting/Cabang Muhammadiyah membuang banyak waktu saya.', false],
            ],
            'C. Etos Kerja dan Kepemimpinan' => [
                ['Saya menyelesaikan tugas dengan dedikasi tinggi dan tepat waktu.', true],
                ['Menunda pekerjaan lumrah jika tidak diawasi.', false],
                ['Saya berinisiatif memberikan solusi saat tim terkendala.', true],
                ['Saya menghindari tanggung jawab kepemimpinan.', false],
            ],
            'D. Sikap Sosial Kemasyarakatan' => [
                ['Saya peduli dan membantu warga sekitar yang tertimpa musibah.', true],
                ['Saya ragu meluangkan waktu untuk bakti sosial.', false],
                ['Menjaga silaturahmi dengan tetangga adalah prioritas saya.', true],
                ['Saya acuh terhadap isu kemiskinan di lingkungan saya.', false],
            ],
        ];

        $urutanSub = 0;
        foreach ($subAspeks as $nama => $butirs) {
            $urutanSub++;

            $sa = AfektifSubAspek::create([
                'event_id' => $event->id,
                'nama_sub_aspek' => $nama,
                'urutan' => $urutanSub,
                'aktif' => true,
            ]);

            foreach ($butirs as $urutan => [$teks, $positif]) {
                AfektifButir::create([
                    'sub_aspek_id' => $sa->id,
                    'teks_butir' => $teks,
                    'is_positif' => $positif,
                    'urutan' => $urutan + 1,
                ]);
            }
        }
    }

    private function seedRtl(Event $event): void
    {
        if (RtlSoal::where('event_id', $event->id)->exists()) {
            return;
        }

        $questions = [
            ['Unggah bukti Log-In Muhammadiyah (pendaftaran/konversi E-KTAM).', 'upload'],
            ['Tuliskan kontribusi Anda di Ranting/Cabang Muhammadiyah domisili.', 'essay'],
            ['Tuliskan satu rencana pengabdian di Cabang atau Ranting.', 'essay'],
            ['Tuliskan komitmen tindak lanjut setelah mengikuti Baitul Arqam ini.', 'essay'],
        ];

        foreach ($questions as $index => [$teks, $tipe]) {
            RtlSoal::create([
                'event_id' => $event->id,
                'pertanyaan' => $teks,
                'tipe' => $tipe,
                'urutan' => $index + 1,
            ]);
        }
    }

    private function seedAngket(Event $event): void
    {
        if (AngketItem::where('event_id', $event->id)->exists()) {
            return;
        }

        $defaultItems = [
            'A' => [
                'Materi perkaderan relevan dengan penguatan ideologi & kebutuhan persyarikatan.',
                'Alokasi waktu pemaparan materi dan forum diskusi/tanya-jawab memadai.',
                'Materi mudah dipahami dan aplikatif untuk diterapkan di Cabang/Ranting/AUM.',
            ],
            'B' => [
                'Narasumber menguasai materi secara mendalam, inspiratif, dan komunikatif.',
                'Master of Training / Tim Instruktur mendampingi dinamika kelas dengan disiplin dan solutif.',
                'Keteladanan ibadah dan kedisiplinan tim fasilitator sangat baik.',
            ],
            'C' => [
                'Kenyamanan ruangan, pencahayaan, proyektor, dan tata suara audio memuaskan.',
                'Kualitas konsumsi, kudapan, dan ketepatan jadwal istirahat/ibadah terjaga baik.',
                'Kesiapan panitia dalam melayani kebutuhan dan respon kendala peserta sangat memuaskan.',
            ],
        ];

        foreach ($defaultItems as $kategori => $teksList) {
            foreach ($teksList as $urutan => $teks) {
                AngketItem::create([
                    'event_id' => $event->id,
                    'kategori' => $kategori,
                    'urutan' => $urutan + 1,
                    'teks_item' => $teks,
                ]);
            }
        }
    }
}
