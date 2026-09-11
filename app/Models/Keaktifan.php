<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Keaktifan extends Model
{
    protected $table = 'keaktifan';

    public const JENIS = ['tanya', 'jawab', 'kultum', 'imam', 'adzan', 'juara_outbound'];

    protected $fillable = [
        'event_id',
        'anggota_id',
        'jenis',
        'sesi_id',
        'dicatat_oleh',
        'keterangan',
    ];

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(EventSesi::class, 'sesi_id');
    }
}
