<?php

namespace App\Models;

use Database\Factories\AnggotaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Anggota extends Model
{
    /** @use HasFactory<AnggotaFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'anggota';

    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'alamat_rumah',
        'jenis_kelamin',
        'nik',
        'nbm',
        'jabatan_aum',
        'tempat_lahir',
        'tanggal_lahir',
        'umur',
        'status_pernikahan',
        'desa_kelurahan',
        'kecamatan',
        'kabupaten',
        'no_wa',
        'email',
        'foto',
        'catatan_verifikasi',
        'pendidikan_sd',
        'bahasa_dikuasai',
        'kemampuan_baca_quran',
        'hafalan_quran',
        'aktivitas_sholat_masjid',
        'aktivitas_kajian_agama',
        'jumlah_buku_agama',
        'sumber_info_muhammadiyah',
        'langganan_suara_muhammadiyah',
        'lembaga_zis',
        'tokoh_berpengaruh',
        'alasan_pilih_tokoh',
        'keaktifan_muhammadiyah',
        'keaktifan_ortom',
        'organisasi_lain',
        'aktifitas_jamaah_masjid',
        'keaktifan_takmir',
        'jabatan_takmir',
        'keaktifan_kemasyarakatan',
        'harapan_pcm',
        'harapan_ba',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'bahasa_dikuasai' => 'array',
        'sumber_info_muhammadiyah' => 'array',
        'lembaga_zis' => 'array',
        'keaktifan_muhammadiyah' => 'array',
        'keaktifan_ortom' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organisasi(): BelongsToMany
    {
        return $this->belongsToMany(Organisasi::class, 'anggota_organisasi')
            ->withPivot(['jabatan', 'periode'])
            ->withTimestamps();
    }

    public function eventPeserta()
    {
        return $this->hasMany(EventPeserta::class);
    }

    public function penilaianAkhir()
    {
        return $this->hasMany(PenilaianAkhir::class);
    }
}
