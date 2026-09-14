<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AnggotaExport implements FromView, ShouldAutoSize
{
    use Exportable;

    public function __construct(
        private readonly Collection $anggotas,
        private readonly string $subTitle = 'PIMPINAN DAERAH MUHAMMADIYAH KABUPATEN KARANGANYAR',
        private readonly ?string $pcm = null
    ) {}

    public function view(): View
    {
        return view('exports.anggota', [
            'anggotas' => $this->anggotas,
            'subTitle' => $this->subTitle,
            'pcm' => $this->pcm,
            'totalKader' => $this->anggotas->count(),
            'totalAktif' => $this->anggotas->where('user.status', 'aktif')->count(),
            'totalPending' => $this->anggotas->where('user.status', 'menunggu_verifikasi')->count(),
            'totalLaki' => $this->anggotas->where('jenis_kelamin', 'L')->count(),
            'totalPerempuan' => $this->anggotas->where('jenis_kelamin', 'P')->count(),
        ]);
    }
}
