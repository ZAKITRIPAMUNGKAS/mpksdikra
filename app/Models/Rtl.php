<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rtl extends Model
{
    protected $table = 'rtl';

    public const STATUS = ['draf', 'terkirim', 'disetujui', 'ditolak'];

    protected $fillable = [
        'event_id',
        'anggota_id',
        'status',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }

    public function jawaban(): HasMany
    {
        return $this->hasMany(RtlJawaban::class, 'rtl_id');
    }
}
