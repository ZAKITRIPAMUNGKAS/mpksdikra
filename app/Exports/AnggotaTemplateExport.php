<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnggotaTemplateExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'nama_lengkap',
            'nik',
            'no_wa',
            'email',
            'jenis_kelamin',
            'tempat_lahir',
            'tanggal_lahir',
            'kecamatan',
            'desa_kelurahan',
            'nbm',
            'pekerjaan',
            'pendidikan_terakhir',
            'keaktifan_takmir',
            'jabatan_takmir',
            'aktifitas_jamaah_masjid',
            'keaktifan_kemasyarakatan',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Ahmad Dahlan',
                '3313010101800001',
                '081234567890',
                'ahmad.dahlan@example.com',
                'L',
                'Karanganyar',
                '1980-01-01',
                'Karanganyar',
                'Tegalgede',
                '1234567',
                'Guru',
                'S1',
                'Ya',
                'Ketua Takmir',
                'Sholat Berjamaah 5 Waktu, Pengajian Rutin',
                'Pengurus RT',
            ],
            [
                'Siti Walidah',
                '3313014202850002',
                '081298765432',
                'siti.walidah@example.com',
                'P',
                'Karanganyar',
                '1985-02-02',
                'Tasikmadu',
                'Papahan',
                '',
                'Wiraswasta',
                'S1',
                'Tidak',
                '',
                'Pengajian Ahad Pagi, Sholat Berjamaah Subuh',
                'Kader Posyandu',
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF005C8A'],
                ],
            ],
        ];
    }
}
