<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenilaianAkhir extends Model
{
    protected $table = 'penilaian_akhir';

    protected $fillable = [
        'event_id',
        'anggota_id',
        'nilai_pretest',
        'nilai_posttest',
        'nilai_afektif',
        'nilai_psikomotor',
        'nilai_keaktifan',
        'nilai_kehadiran',
        'nilai_akhir',
        'ranking',
        'status_kelulusan',
        'verification_hash',
    ];

    protected function casts(): array
    {
        return [
            'nilai_pretest' => 'decimal:2',
            'nilai_posttest' => 'decimal:2',
            'nilai_afektif' => 'decimal:2',
            'nilai_psikomotor' => 'decimal:2',
            'nilai_keaktifan' => 'decimal:2',
            'nilai_kehadiran' => 'decimal:2',
            'nilai_akhir' => 'decimal:2',
            'ranking' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }
}
