<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function soal(): HasMany
    {
        return $this->hasMany(Soal::class, 'event_id', 'event_id')
            ->where('tipe', $this->tipe);
    }
}
