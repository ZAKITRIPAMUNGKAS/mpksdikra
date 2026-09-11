<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PsikomotorTemplate extends Model
{
    protected $table = 'psikomotor_template';

    public const JENIS = ['ibadah', 'outbound'];

    protected $fillable = [
        'event_id',
        'jenis',
        'nama_aspek',
        'skor_maks',
    ];

    protected function casts(): array
    {
        return ['skor_maks' => 'integer'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
