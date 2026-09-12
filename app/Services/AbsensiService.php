<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\EventPeserta;
use App\Models\EventSesi;
use App\Models\PenilaianAkhir;
use Illuminate\Support\Carbon;

class AbsensiService
{
    public function __construct(private readonly QrService $qr) {}

    public function scan(string $qrCode, int $sesiId, int $scannedBy): array
    {
        $sesi = EventSesi::find($sesiId);

        if (! $sesi) {
            return ['status' => 'error', 'message' => 'Sesi tidak ditemukan'];
        }

        $ep = $this->qr->verify($qrCode);

        if (! $ep) {
            return ['status' => 'error', 'message' => 'QR tidak valid'];
        }

        if ($ep->event_id !== $sesi->event_id) {
            return ['status' => 'error', 'message' => 'QR bukan peserta event ini'];
        }

        if (! $ep->status_aktif) {
            return ['status' => 'error', 'message' => 'Peserta tidak aktif pada event ini'];
        }

        return $this->record($sesi, $ep, $scannedBy, 'qr');
    }

    public function manual(int $sesiId, int $anggotaId, int $scannedBy): array
    {
        $sesi = EventSesi::find($sesiId);

        if (! $sesi) {
            return ['status' => 'error', 'message' => 'Sesi tidak ditemukan'];
        }

        $ep = EventPeserta::where('event_id', $sesi->event_id)
            ->where('anggota_id', $anggotaId)
            ->first();

        if (! $ep || ! $ep->status_aktif) {
            return ['status' => 'error', 'message' => 'Peserta tidak terdaftar atau tidak aktif'];
        }

        return $this->record($sesi, $ep, $scannedBy, 'manual');
    }

    private function record(EventSesi $sesi, EventPeserta $ep, int $scannedBy, string $metode): array
    {
        $existing = Absensi::where('sesi_id', $sesi->id)
            ->where('anggota_id', $ep->anggota_id)
            ->first();

        $nama = $ep->anggota->nama_lengkap;

        if ($existing) {
            return [
                'status' => 'duplicate',
                'nama' => $nama,
                'waktu_scan' => $existing->waktu_scan->format('H:i:s'),
            ];
        }

        $now = Carbon::now();

        Absensi::create([
            'event_id' => $sesi->event_id,
            'sesi_id' => $sesi->id,
            'anggota_id' => $ep->anggota_id,
            'waktu_scan' => $now,
            'scanned_by' => $scannedBy,
            'metode' => $metode,
        ]);

        $this->recalculateAttendance($sesi->event_id, $ep->anggota_id);

        return [
            'status' => 'success',
            'nama' => $nama,
            'waktu_scan' => $now->format('H:i:s'),
        ];
    }

    public function recalculateAttendance(int $eventId, int $anggotaId): float
    {
        $totalSesi = EventSesi::where('event_id', $eventId)->count();

        if ($totalSesi === 0) {
            return 0.0;
        }

        $hadir = Absensi::where('event_id', $eventId)
            ->where('anggota_id', $anggotaId)
            ->distinct('sesi_id')
            ->count('sesi_id');

        $persen = round(($hadir / $totalSesi) * 100, 2);

        PenilaianAkhir::updateOrCreate(
            ['event_id' => $eventId, 'anggota_id' => $anggotaId],
            ['nilai_kehadiran' => $persen, 'status_kelulusan' => $this->statusKelulusan($persen)]
        );

        return $persen;
    }

    public function statusKelulusan(float $kehadiran): string
    {
        if ($kehadiran >= config('arqam.kehadiran.lulus', 100)) {
            return 'Lulus';
        }

        if ($kehadiran >= config('arqam.kehadiran.lulus_bersyarat', 75)) {
            return 'Lulus Bersyarat';
        }

        return 'Tidak Lulus';
    }
}
