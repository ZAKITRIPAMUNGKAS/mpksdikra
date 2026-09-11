<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AngketJawaban extends Model
{
    protected $table = 'angket_jawaban';

    protected $fillable = [
        'event_id',
        'anggota_id',
        'item_id',
        'skor',
    ];

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(AngketItem::class, 'item_id');
    }
}
