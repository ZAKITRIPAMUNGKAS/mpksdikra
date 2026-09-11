<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PsikomotorNilai extends Model
{
    protected $table = 'psikomotor_nilai';

    protected $fillable = [
        'event_id',
        'anggota_id',
        'template_id',
        'skor',
        'dinilai_oleh',
    ];

    protected function casts(): array
    {
        return ['skor' => 'integer'];
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PsikomotorTemplate::class, 'template_id');
    }
}
