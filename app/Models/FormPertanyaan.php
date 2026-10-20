<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;

class FormPertanyaan extends Model
{
    use HasFactory;

    protected $table = 'form_pertanyaan';

    protected $fillable = [
        'seksi',
        'kode',
        'label',
        'tipe',
        'opsi',
        'placeholder',
        'bantuan',
        'is_wajib',
        'is_aktif',
        'is_custom',
        'urutan',
    ];

    protected $casts = [
        'opsi' => 'array',
        'is_wajib' => 'boolean',
        'is_aktif' => 'boolean',
        'is_custom' => 'boolean',
        'urutan' => 'integer',
    ];

    public const SEKSI = [
        'diri' => '1. Biodata Diri',
        'agama' => '2. Keagamaan & Ibadah',
        'organisasi' => '3. Data Organisasi',
        'kemasyarakatan' => '4. Kemasyarakatan & Kemasjidan',
        'tambahan' => '5. Pertanyaan Tambahan',
    ];

    public const TIPE = [
        'text' => 'Jawaban Singkat (Teks)',
        'textarea' => 'Paragraf (Teks Panjang)',
        'select' => 'Pilihan Ganda (Dropdown)',
        'checkbox' => 'Kotak Centang (Banyak Pilihan)',
        'number' => 'Angka / Bilangan',
    ];

    protected static ?Collection $configCache = null;

    protected static function booted(): void
    {
        static::saved(function () {
            static::clearConfigCache();
        });
        static::deleted(function () {
            static::clearConfigCache();
        });
    }

    public static function configMap(): Collection
    {
        if (static::$configCache === null) {
            static::$configCache = static::all()->keyBy('kode');
        }

        return static::$configCache;
    }

    public static function clearConfigCache(): void
    {
        static::$configCache = null;
    }

    public static function isWajib(string $kode, bool $default = false): bool
    {
        $q = static::configMap()->get($kode);

        return $q ? (bool) $q->is_wajib : $default;
    }

    public static function isAktif(string $kode, bool $default = true): bool
    {
        $q = static::configMap()->get($kode);

        return $q ? (bool) $q->is_aktif : $default;
    }

    public static function label(string $kode, string $default): string
    {
        $q = static::configMap()->get($kode);

        return $q && ! empty($q->label) ? $q->label : $default;
    }

    public static function bantuan(string $kode, ?string $default = null): ?string
    {
        $q = static::configMap()->get($kode);

        return $q && ! empty($q->bantuan) ? $q->bantuan : $default;
    }

    public static function placeholder(string $kode, ?string $default = null): ?string
    {
        $q = static::configMap()->get($kode);

        return $q && ! empty($q->placeholder) ? $q->placeholder : $default;
    }

    public static function opsi(string $kode, array $default = []): array
    {
        $q = static::configMap()->get($kode);

        if ($q && is_array($q->opsi) && count($q->opsi) > 0) {
            return $q->opsi;
        }

        return $default;
    }

    public static function getField(string $kode): ?self
    {
        return static::configMap()->get($kode);
    }

    public static function applyStandardValidationRules(array $baseRules): array
    {
        $standardQuestions = static::where('is_custom', false)->get()->keyBy('kode');

        foreach ($standardQuestions as $kode => $q) {
            if (! isset($baseRules[$kode])) {
                continue;
            }

            $ruleList = (array) $baseRules[$kode];

            if ($q->is_wajib) {
                $ruleList = array_map(fn ($r) => $r === 'nullable' ? 'required' : $r, $ruleList);
                if (! in_array('required', $ruleList, true)) {
                    array_unshift($ruleList, 'required');
                }
            } else {
                $coreRequired = ['nama_lengkap', 'nik', 'no_wa', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'status_pernikahan', 'alamat_rumah', 'desa_kelurahan', 'kecamatan', 'kabupaten', 'foto'];
                if (! in_array($kode, $coreRequired, true)) {
                    $ruleList = array_map(fn ($r) => $r === 'required' ? 'nullable' : $r, $ruleList);
                }
            }

            if (! $q->is_aktif) {
                $ruleList = array_map(fn ($r) => $r === 'required' ? 'nullable' : $r, $ruleList);
            }

            $skipOpsiValidation = ['jenis_kelamin', 'pendidikan_terakhir', 'nama_aum', 'jabatan_aum'];

            if (! in_array($kode, $skipOpsiValidation, true) && $q->opsi && is_array($q->opsi) && count($q->opsi) > 0) {
                if (in_array($q->tipe, ['select', 'text'], true)) {
                    $allowedOpsi = $q->opsi;
                    if ($kode === 'pcm' && ! in_array('Lainnya', $allowedOpsi, true)) {
                        $allowedOpsi[] = 'Lainnya';
                    }
                    $cleanRules = [];
                    foreach ($ruleList as $rule) {
                        if ($rule instanceof Rule || $rule instanceof In) {
                            continue;
                        }
                        $cleanRules[] = $rule;
                    }
                    $cleanRules[] = Rule::in($allowedOpsi);
                    $ruleList = $cleanRules;
                } elseif ($q->tipe === 'checkbox') {
                    $baseRules[$kode.'.*'] = [Rule::in($q->opsi)];
                }
            }

            $baseRules[$kode] = $ruleList;
        }

        return $baseRules;
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_aktif', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('urutan')->orderBy('id');
    }

    public function scopeSeksi(Builder $query, string $seksi): Builder
    {
        return $query->where('seksi', $seksi);
    }

    public static function activeValidationRules(): array
    {
        $rules = [];

        foreach (static::aktif()->where('is_custom', true)->ordered()->get() as $question) {
            $key = 'custom_fields.'.$question->kode;
            $required = $question->is_wajib ? 'required' : 'nullable';

            $rules[$key] = match ($question->tipe) {
                'number' => [$required, 'numeric'],
                'select' => array_merge([$required, 'string'], $question->opsi ? [Rule::in($question->opsi)] : []),
                'checkbox' => [$required, 'array'],
                default => [$required, 'string', 'max:5000'],
            };

            if ($question->tipe === 'checkbox') {
                $rules[$key.'.*'] = array_merge(['string'], $question->opsi ? [Rule::in($question->opsi)] : []);
            }
        }

        return $rules;
    }
}
