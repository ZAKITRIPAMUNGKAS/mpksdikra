<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AfektifButir extends Model
{
    protected $table = 'afektif_butir';

    protected $fillable = [
        'sub_aspek_id',
        'teks_butir',
        'is_positif',
        'urutan',
    ];

    protected function casts(): array
    {
        return ['is_positif' => 'boolean'];
    }

    public function subAspek(): BelongsTo
    {
        return $this->belongsTo(AfektifSubAspek::class, 'sub_aspek_id');
    }
}
