<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RtlSoal extends Model
{
    protected $table = 'rtl_soal';

    protected $fillable = [
        'event_id',
        'pertanyaan',
        'tipe',
        'urutan',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
