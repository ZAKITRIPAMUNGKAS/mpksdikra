<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventSesi extends Model
{
    protected $table = 'event_sesi';

    public const JENIS = ['materi', 'sholat', 'outbound'];

    protected $fillable = [
        'event_id',
        'nama_sesi',
        'jenis',
        'urutan',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'pemateri',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'urutan' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class, 'sesi_id');
    }
}
