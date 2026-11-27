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
        'link_grup_wa',
        'deskripsi',
        'kuota',
        'status',
        'format_nomor_sertifikat',
        'tanggal_terbit_sertifikat',
        'nama_penandatangan_sertifikat',
        'nbm_penandatangan_sertifikat',
        'jabatan_penandatangan_sertifikat',
        'nama_penandatangan_kiri',
        'jabatan_penandatangan_kiri',
        'keterangan_penandatangan_kiri',
        'ttd_kiri_path',
        'ttd_kanan_path',
        'bobot_kognitif',
        'bobot_psikomotor',
        'bobot_afektif',
        'bobot_keaktifan',
        'gunakan_grade',
        'rtl_wajib',
        'afektif_status',
        'angket_status',
        'created_by',
    ];

    /** Isian online yang dapat dibuka/ditutup admin. */
    public const ISIAN_BUKA_TUTUP = ['afektif', 'angket'];

    protected $attributes = [
        'afektif_status' => 'aktif',
        'angket_status' => 'aktif',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'tanggal_terbit_sertifikat' => 'date',
            'kuota' => 'integer',
            'bobot_kognitif' => 'float',
            'bobot_psikomotor' => 'float',
            'bobot_afektif' => 'float',
            'bobot_keaktifan' => 'float',
            'gunakan_grade' => 'boolean',
            'rtl_wajib' => 'boolean',
        ];
    }

    public function getBobotKomposisi(): array
    {
        $cfg = config('arqam.bobot', []);

        return [
            'kognitif' => $this->bobot_kognitif !== null ? (float) $this->bobot_kognitif : (float) ($cfg['kognitif'] ?? 0.30),
            'psikomotor' => $this->bobot_psikomotor !== null ? (float) $this->bobot_psikomotor : (float) ($cfg['psikomotor'] ?? 0.30),
            'afektif' => $this->bobot_afektif !== null ? (float) $this->bobot_afektif : (float) ($cfg['afektif'] ?? 0.30),
            'keaktifan' => $this->bobot_keaktifan !== null ? (float) $this->bobot_keaktifan : (float) ($cfg['keaktifan'] ?? 0.10),
            'posttest_dalam_kognitif' => (float) ($cfg['posttest_dalam_kognitif'] ?? 0.70),
            'ngain_dalam_kognitif' => (float) ($cfg['ngain_dalam_kognitif'] ?? 0.30),
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

    /**
     * Apakah pengisian online afektif/angket sedang dibuka untuk peserta.
     */
    public function isianDibuka(string $jenis): bool
    {
        abort_unless(in_array($jenis, self::ISIAN_BUKA_TUTUP, true), 404);

        return ($this->{$jenis.'_status'} ?? 'aktif') !== 'tutup';
    }

    /**
     * RTL menjadi syarat kelulusan bila diwajibkan (default) dan kegiatan memiliki soal RTL.
     */
    public function rtlSyaratKelulusan(): bool
    {
        return ($this->rtl_wajib ?? true) && $this->rtlSoal()->exists();
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
