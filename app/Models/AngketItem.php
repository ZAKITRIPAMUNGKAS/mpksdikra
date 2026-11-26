<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AngketItem extends Model
{
    protected $table = 'angket_item';

    public const DEFAULT_ITEMS = [
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

    protected $fillable = [
        'event_id',
        'kategori',
        'teks_item',
        'urutan',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Menambahkan butir angket standar MPKSDI tanpa menduplikasi butir yang sudah ada.
     */
    public static function seedDefaultsFor(Event $event): int
    {
        $existing = self::where('event_id', $event->id)->get();
        $existingByCat = $existing->groupBy('kategori');
        $added = 0;

        foreach (self::DEFAULT_ITEMS as $kategori => $teksList) {
            $catItems = $existingByCat->get($kategori, collect());
            $knownTexts = $catItems->map(fn ($item) => mb_strtolower(trim((string) $item->teks_item)))->flip();
            $nextUrutan = (int) $catItems->max('urutan');

            foreach ($teksList as $teks) {
                $key = mb_strtolower(trim($teks));
                if ($knownTexts->has($key)) {
                    continue;
                }

                self::create([
                    'event_id' => $event->id,
                    'kategori' => $kategori,
                    'urutan' => ++$nextUrutan,
                    'teks_item' => $teks,
                ]);
                $knownTexts->put($key, true);
                $added++;
            }
        }

        return $added;
    }
}
