<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OutboundKelompok extends Model
{
    protected $table = 'outbound_kelompok';

    protected $fillable = [
        'event_id',
        'nama_kelompok',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function peserta(): HasMany
    {
        return $this->hasMany(EventPeserta::class, 'outbound_kelompok_id');
    }
}
