<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SesiTes extends Model
{
    protected $table = 'sesi_tes';

    protected $fillable = [
        'event_id',
        'tipe',
        'status',
        'durasi_menit',
    ];

    protected function casts(): array
    {
        return ['durasi_menit' => 'integer'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
