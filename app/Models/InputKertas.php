<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Penanda bahwa jawaban afektif/angket seorang peserta diinput admin dari lembar kertas.
 */
class InputKertas extends Model
{
    protected $table = 'input_kertas';

    protected $fillable = [
        'event_id',
        'anggota_id',
        'instrumen',
        'diinput_oleh',
    ];

    public static function ada(int $eventId, int $anggotaId, string $instrumen): bool
    {
        return static::where('event_id', $eventId)
            ->where('anggota_id', $anggotaId)
            ->where('instrumen', $instrumen)
            ->exists();
    }

    public function penginput(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diinput_oleh');
    }
}
