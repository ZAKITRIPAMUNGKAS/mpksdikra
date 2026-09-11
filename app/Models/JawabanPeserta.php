<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JawabanPeserta extends Model
{
    protected $table = 'jawaban_peserta';

    protected $fillable = [
        'event_id',
        'anggota_id',
        'soal_id',
        'pilihan_id',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }

    public function soal(): BelongsTo
    {
        return $this->belongsTo(Soal::class);
    }

    public function pilihan(): BelongsTo
    {
        return $this->belongsTo(PilihanJawaban::class, 'pilihan_id');
    }
}
