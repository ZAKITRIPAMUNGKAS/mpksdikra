<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AfektifJawaban extends Model
{
    protected $table = 'afektif_jawaban';

    protected $fillable = [
        'event_id',
        'anggota_id',
        'butir_id',
        'skor',
    ];

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }

    public function butir(): BelongsTo
    {
        return $this->belongsTo(AfektifButir::class, 'butir_id');
    }
}
