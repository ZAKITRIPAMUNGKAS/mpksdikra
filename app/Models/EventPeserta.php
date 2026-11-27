<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class EventPeserta extends Model
{
    protected $table = 'event_peserta';

    protected $fillable = [
        'event_id',
        'anggota_id',
        'qr_token',
        'outbound_kelompok_id',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return ['status_aktif' => 'boolean'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }

    public function kelompokOutbound(): BelongsTo
    {
        return $this->belongsTo(OutboundKelompok::class, 'outbound_kelompok_id');
    }

    /**
     * Ambil daftar ID EventPeserta terpilih dari query/request (?peserta_ids[]=1 atau ?peserta_ids=1,2).
     *
     * @return array<int, int>
     */
    public static function selectedIdsFromRequest(Request $request): array
    {
        $raw = $request->input('peserta_ids', []);
        if (is_string($raw)) {
            $raw = explode(',', $raw);
        }
        if (! is_array($raw)) {
            return [];
        }

        return collect($raw)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}
