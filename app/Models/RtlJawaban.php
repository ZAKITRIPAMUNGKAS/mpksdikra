<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RtlJawaban extends Model
{
    protected $table = 'rtl_jawaban';

    protected $fillable = [
        'rtl_id',
        'soal_id',
        'jawaban',
        'file_bukti',
    ];

    public function rtl(): BelongsTo
    {
        return $this->belongsTo(Rtl::class);
    }

    public function soal(): BelongsTo
    {
        return $this->belongsTo(RtlSoal::class, 'soal_id');
    }
}
