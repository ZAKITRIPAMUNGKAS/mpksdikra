<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Organisasi extends Model
{
    protected $table = 'organisasi';

    public const KATEGORI = ['pdm', 'pcm', 'prm', 'aum', 'ortom', 'lain'];

    protected $fillable = ['nama', 'kategori', 'pimpinan'];

    public function anggota(): BelongsToMany
    {
        return $this->belongsToMany(Anggota::class, 'anggota_organisasi')
            ->withPivot(['jabatan', 'periode'])
            ->withTimestamps();
    }
}
