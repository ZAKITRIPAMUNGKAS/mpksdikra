<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AngketItem extends Model
{
    protected $table = 'angket_item';

    protected $fillable = [
        'event_id',
        'kategori',
        'teks_item',
        'urutan',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
