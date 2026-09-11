<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    protected $table = 'absensi';

    protected $fillable = [
        'event_id',
        'sesi_id',
        'anggota_id',
        'waktu_scan',
        'scanned_by',
        'metode',
    ];

    protected function casts(): array
    {
        return ['waktu_scan' => 'datetime'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(EventSesi::class, 'sesi_id');
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }
}
