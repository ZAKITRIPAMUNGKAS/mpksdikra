<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventPeserta extends Model
{
    protected $table = 'event_peserta';

    protected $fillable = [
        'event_id',
        'anggota_id',
        'qr_token',
        'outbound_kelompok_id',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return ['status_aktif' => 'boolean'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }

    public function kelompokOutbound(): BelongsTo
    {
        return $this->belongsTo(OutboundKelompok::class, 'outbound_kelompok_id');
    }
}
