<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AfektifSubAspek extends Model
{
    protected $table = 'afektif_sub_aspek';

    protected $fillable = [
        'event_id',
        'nama_sub_aspek',
        'urutan',
        'aktif',
    ];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function butir(): HasMany
    {
        return $this->hasMany(AfektifButir::class, 'sub_aspek_id')->orderBy('urutan');
    }
}
