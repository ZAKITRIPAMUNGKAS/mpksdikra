<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AngketKomentar extends Model
{
    protected $table = 'angket_komentar';

    protected $fillable = [
        'event_id',
        'anggota_id',
        'komentar',
    ];

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }
}
