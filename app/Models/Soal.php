<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Soal extends Model
{
    protected $table = 'soal';

    public const TIPE = ['pretest', 'posttest'];

    protected $fillable = [
        'event_id',
        'tipe',
        'teks_soal',
        'urutan',
    ];

    protected function casts(): array
    {
        return ['urutan' => 'integer'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function pilihan(): HasMany
    {
        return $this->hasMany(PilihanJawaban::class)->orderBy('label');
    }
}
