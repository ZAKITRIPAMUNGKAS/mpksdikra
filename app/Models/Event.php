<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nama_event',
        'tanggal_mulai',
        'tanggal_selesai',
        'lokasi',
        'deskripsi',
        'kuota',
        'status',
        'format_nomor_sertifikat',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'kuota' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sesi(): HasMany
    {
        return $this->hasMany(EventSesi::class)->orderBy('urutan');
    }

    public function peserta(): HasMany
    {
        return $this->hasMany(EventPeserta::class);
    }

    public function pesertaAktif(): HasMany
    {
        return $this->hasMany(EventPeserta::class)->where('status_aktif', true);
    }

    public function soal(): HasMany
    {
        return $this->hasMany(Soal::class);
    }

    public function sesiTes(): HasMany
    {
        return $this->hasMany(SesiTes::class);
    }

    public function psikomotorTemplate(): HasMany
    {
        return $this->hasMany(PsikomotorTemplate::class);
    }

    public function afektifSubAspek(): HasMany
    {
        return $this->hasMany(AfektifSubAspek::class)->orderBy('urutan');
    }

    public function angketItem(): HasMany
    {
        return $this->hasMany(AngketItem::class)->orderBy('urutan');
    }

    public function rtlSoal(): HasMany
    {
        return $this->hasMany(RtlSoal::class)->orderBy('urutan');
    }

    public function penilaianAkhir(): HasMany
    {
        return $this->hasMany(PenilaianAkhir::class);
    }

    public function fasilitator()
    {
        return $this->belongsToMany(User::class, 'event_fasilitator')->withTimestamps();
    }

    public function kelompokOutbound(): HasMany
    {
        return $this->hasMany(OutboundKelompok::class);
    }

    public function generateNomorSertifikat(int|string $nomorUrut): string
    {
        $nomorFormatted = is_numeric($nomorUrut) ? sprintf('%03d', (int) $nomorUrut) : (string) $nomorUrut;
        $bulanAngka = (int) ($this->tanggal_mulai?->format('n') ?? date('n'));
        $romawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];
        $bulanRomawi = $romawi[$bulanAngka] ?? 'I';
        $tahun = $this->tanggal_mulai?->format('Y') ?? date('Y');

        $template = $this->format_nomor_sertifikat ?: '{nomor}/BA-MPKSDI/{bulan_romawi}/{tahun}';

        return str_replace(
            ['{nomor}', '{bulan_romawi}', '{bulan}', '{tahun}', '{nama_event}'],
            [$nomorFormatted, $bulanRomawi, sprintf('%02d', $bulanAngka), $tahun, $this->nama_event],
            $template
        );
    }
}
