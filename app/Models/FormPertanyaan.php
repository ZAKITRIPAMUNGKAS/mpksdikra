<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

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
