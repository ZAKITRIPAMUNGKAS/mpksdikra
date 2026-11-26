<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class PesertaTesMulai extends Model
{
    protected $table = 'peserta_tes_mulai';

    protected $fillable = [
        'event_id',
        'anggota_id',
        'tipe',
        'sumber',
        'waktu_mulai',
        'waktu_selesai',
        'diinput_oleh',
    ];

    protected function casts(): array
    {
        return [
            'waktu_mulai' => 'datetime',
            'waktu_selesai' => 'datetime',
        ];
    }

    /**
     * @return Collection<int, array<int, string>> anggota_id => ['pretest', ...]
     */
    public static function tesKertasMap(int $eventId)
    {
        return static::where('event_id', $eventId)
            ->where('sumber', 'manual')
            ->get(['anggota_id', 'tipe'])
            ->groupBy('anggota_id')
            ->map(fn ($rows) => $rows->pluck('tipe')->all());
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function penginput(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diinput_oleh');
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }
}
