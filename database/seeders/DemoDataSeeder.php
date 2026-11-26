<?php

namespace Database\Seeders;

use App\Models\Absensi;
use App\Models\AfektifButir;
use App\Models\AfektifJawaban;
use App\Models\AfektifSubAspek;
use App\Models\Anggota;
use App\Models\AngketItem;
use App\Models\AngketJawaban;
use App\Models\AngketKomentar;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\EventSesi;
use App\Models\Keaktifan;
use App\Models\Organisasi;
use App\Models\OutboundKelompok;
use App\Models\PenilaianAkhir;
use App\Models\PesertaTesMulai;
use App\Models\PilihanJawaban;
use App\Models\PsikomotorNilai;
use App\Models\PsikomotorTemplate;
use App\Models\Rtl;
use App\Models\RtlJawaban;
use App\Models\RtlSoal;
use App\Models\SesiTes;
use App\Models\Soal;
use App\Models\User;
use App\Services\NilaiService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $admin = $this->seedUsers();
            $anggotaList = $this->seedAnggota();
            $this->seedOrganisasi($anggotaList);
            $this->seedEvents($admin, $anggotaList);
        });
    }

    private function seedUsers(): User
    {
        $admin = User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@mpksdi.test')],
            [
                'name' => 'Admin MPKSDI Karanganyar',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'ubah-segera')),
                'role' => 'admin',
                'status' => 'aktif',
                'no_wa' => '081226001001',
                'verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'fasilitator@mpksdi.test'],
            [
                'name' => 'Drs. H. Sriyanto, M.Pd.',
                'password' => Hash::make('password'),
                'role' => 'fasilitator',
                'status' => 'aktif',
                'no_wa' => '081226001002',
                'verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'instruktur@mpksdi.test'],
            [
                'name' => 'Ust. Ahmad Dahlan Al-Hafidz',
                'password' => Hash::make('password'),
                'role' => 'fasilitator',
                'status' => 'aktif',
                'no_wa' => '081226001003',
                'verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'executive@mpksdi.test'],
            [
                'name' => 'Dr. Muhammad Ihsan, M.Ag.',
                'password' => Hash::make('password'),
                'role' => 'executive',
                'status' => 'aktif',
                'no_wa' => '081226001004',
                'verified_at' => now(),
            ]
        );

        return $admin;
    }

    private function seedAnggota(): array
    {
        $kaderData = [
            // 20 Aktif
            [
                'name' => 'Ahmad Fathoni, S.T.',
                'email' => 'fathoni@mpksdi.test',
                'gender' => 'L',
                'nik' => '3313011504880001',
                'nbm' => '1084201',
                'desa' => 'Baturan',
                'kecamatan' => 'Colomadu',
                'status' => 'aktif',
                'jabatan_aum' => 'Kepala Lab Komputer SMK Muh 2 Karanganyar',
            ],
            [
                'name' => 'Siti Nurjanah, S.Pd.I.',
                'email' => 'nurjanah@mpksdi.test',
                'gender' => 'P',
                'nik' => '3313025206910002',
                'nbm' => '1084202',
                'desa' => 'Papahan',
                'kecamatan' => 'Tasikmadu',
                'status' => 'aktif',
                'jabatan_aum' => 'Guru PAI MIM Tasikmadu',
            ],
            [
                'name' => 'Bambang Kusuma Wardana, M.M.',
                'email' => 'bambang@mpksdi.test',
                'gender' => 'L',
                'nik' => '3313031008850003',
                'nbm' => '1084203',
                'desa' => 'Dagen',
                'kecamatan' => 'Jaten',
                'status' => 'aktif',
                'jabatan_aum' => 'Staf Tata Usaha SMA Muh 1 Karanganyar',
            ],
            [
                'name' => 'H. Joko Prasetyo, S.Sos.',
                'email' => 'joko@mpksdi.test',
                'gender' => 'L',
                'nik' => '3313042211830004',
                'nbm' => '1084204',
                'desa' => 'Cangakan',
                'kecamatan' => 'Karanganyar',
                'status' => 'aktif',
                'jabatan_aum' => 'Sekretaris PCM Karanganyar Kota',
            ],
            [
                'name' => 'Dewi Rahmawati, S.Kep., Ns.',
                'email' => 'dewi@mpksdi.test',
                'gender' => 'P',
                'nik' => '3313056003940005',
                'nbm' => '1084205',
                'desa' => 'Tawangmangu',
                'kecamatan' => 'Tawangmangu',
                'status' => 'aktif',
                'jabatan_aum' => 'Perawat Klinik PKU Muhammadiyah',
            ],
            [
                'name' => 'Muhammad Arif Hidayat, M.Pd.',
                'email' => 'arif@mpksdi.test',
                'gender' => 'L',
                'nik' => '3313061205900006',
                'nbm' => '1084206',
                'desa' => 'Kemiri',
                'kecamatan' => 'Kebakkramat',
                'status' => 'aktif',
                'jabatan_aum' => 'Wakil Kepala Sekolah Bidang Kurikulum',
            ],
            [
                'name' => 'Sri Wahyuni, S.E.',
                'email' => 'sri.wahyuni@mpksdi.test',
                'gender' => 'P',
                'nik' => '3313074409890007',
                'nbm' => '1084207',
                'desa' => 'Jeruksawit',
                'kecamatan' => 'Gondangrejo',
                'status' => 'aktif',
                'jabatan_aum' => 'Bendahara BTM Surya Mandiri Gondangrejo',
            ],
            [
                'name' => 'Dwi Handayani, S.Pd.',
                'email' => 'dwi.h@mpksdi.test',
                'gender' => 'P',
                'nik' => '3313086512930008',
                'nbm' => '1084208',
                'desa' => 'Girilayu',
                'kecamatan' => 'Matesih',
                'status' => 'aktif',
                'jabatan_aum' => 'Guru TK ABA Matesih',
            ],
            [
                'name' => 'Tri Wibowo, S.Ag.',
                'email' => 'tri.wibowo@mpksdi.test',
                'gender' => 'L',
                'nik' => '3313090802870009',
                'nbm' => '1084209',
                'desa' => 'Suruh',
                'kecamatan' => 'Tasikmadu',
                'status' => 'aktif',
                'jabatan_aum' => 'Ketua Takmir Masjid Al-Furqon',
            ],
            [
                'name' => 'Eko Prasetyo Utomo, S.Kom.',
                'email' => 'eko.p@mpksdi.test',
                'gender' => 'L',
                'nik' => '3313101807920010',
                'nbm' => '1084210',
                'desa' => 'Paulan',
                'kecamatan' => 'Colomadu',
                'status' => 'aktif',
                'jabatan_aum' => 'Administrator TI Lazismu Karanganyar',
            ],
            [
                'name' => 'Fatimah Zahra, M.Si.',
                'email' => 'fatimah@mpksdi.test',
                'gender' => 'P',
                'nik' => '3313115501950011',
                'nbm' => '1084211',
                'desa' => 'Klodran',
                'kecamatan' => 'Colomadu',
                'status' => 'aktif',
                'jabatan_aum' => 'Dosen Kemuhammadiyahan ITS PKU',
            ],
            [
                'name' => 'Guntur Kurniawan, S.Pd.',
                'email' => 'guntur@mpksdi.test',
                'gender' => 'L',
                'nik' => '3313121909860012',
                'nbm' => '1084212',
                'desa' => 'Jumapolo',
                'kecamatan' => 'Jumapolo',
                'status' => 'aktif',
                'jabatan_aum' => 'Guru SMK Muhammadiyah 6 Karanganyar',
            ],
            [
                'name' => 'Hendra Setiawan, S.Farm., Apt.',
                'email' => 'hendra@mpksdi.test',
                'gender' => 'L',
                'nik' => '3313132103910013',
                'nbm' => '1084213',
                'desa' => 'Plesungan',
                'kecamatan' => 'Gondangrejo',
                'status' => 'aktif',
                'jabatan_aum' => 'Apoteker Penanggung Jawab RS PKU',
            ],
            [
                'name' => 'Indah Puspitasari, S.Pd.',
                'email' => 'indah.p@mpksdi.test',
                'gender' => 'P',
                'nik' => '3313144804960014',
                'nbm' => '1084214',
                'desa' => 'Ngringo',
                'kecamatan' => 'Jaten',
                'status' => 'aktif',
                'jabatan_aum' => 'Pembina Ikatan Pelajar Muhammadiyah Jaten',
            ],
            [
                'name' => 'Kurniawan Adi Nugroho, S.H.',
                'email' => 'kurniawan@mpksdi.test',
                'gender' => 'L',
                'nik' => '3313150505890015',
                'nbm' => '1084215',
                'desa' => 'Popongan',
                'kecamatan' => 'Karanganyar',
                'status' => 'aktif',
                'jabatan_aum' => 'Advokat LBH Muhammadiyah Karanganyar',
            ],
            [
                'name' => 'Laila Nur Azizah, S.Psi.',
                'email' => 'laila@mpksdi.test',
                'gender' => 'P',
                'nik' => '3313166208970016',
                'nbm' => '1084216',
                'desa' => 'Kalisoro',
                'kecamatan' => 'Tawangmangu',
                'status' => 'aktif',
                'jabatan_aum' => 'Konselor Remaja Nasyiatul Aisyiyah',
            ],
            [
                'name' => 'Miftahuddin Al-Faqih, Lc.',
                'email' => 'miftah@mpksdi.test',
                'gender' => 'L',
                'nik' => '3313171406840017',
                'nbm' => '1084217',
                'desa' => 'Buran',
                'kecamatan' => 'Tasikmadu',
                'status' => 'aktif',
                'jabatan_aum' => 'Pengasuh Pondok Pesantren TrenSains',
            ],
            [
                'name' => 'Novi Kartikasari, S.E.',
                'email' => 'novi@mpksdi.test',
                'gender' => 'P',
                'nik' => '3313015011940018',
                'nbm' => '1084218',
                'desa' => 'Malangjiwan',
                'kecamatan' => 'Colomadu',
                'status' => 'aktif',
                'jabatan_aum' => 'Staf Keuangan Majelis Dikdasmen',
            ],
            [
                'name' => 'Rahmat Hidayatullah, S.T.',
                'email' => 'rahmat@mpksdi.test',
                'gender' => 'L',
                'nik' => '3313022510900019',
                'nbm' => '1084219',
                'desa' => 'Kebak',
                'kecamatan' => 'Kebakkramat',
                'status' => 'aktif',
                'jabatan_aum' => 'Staf Perencanaan Fasilitas PKU',
            ],
            [
                'name' => 'Zulfa Choirunnisa, S.Gz.',
                'email' => 'zulfa@mpksdi.test',
                'gender' => 'P',
                'nik' => '3313036809980020',
                'nbm' => '1084220',
                'desa' => 'Bejen',
                'kecamatan' => 'Karanganyar',
                'status' => 'aktif',
                'jabatan_aum' => 'Nutrisionis RS PKU Muhammadiyah',
            ],

            // 3 Menunggu Verifikasi
            [
                'name' => 'Bagus Setiawan, S.Pd.',
                'email' => 'bagus.pending@mpksdi.test',
                'gender' => 'L',
                'nik' => '3313041903960021',
                'nbm' => '1084221',
                'desa' => 'Tuban',
                'kecamatan' => 'Gondangrejo',
                'status' => 'menunggu_verifikasi',
                'jabatan_aum' => 'Calon Guru SD Muhammadiyah Gondangrejo',
            ],
            [
                'name' => 'Ratna Dewi Kusuma, S.Sos.',
                'email' => 'ratna.pending@mpksdi.test',
                'gender' => 'P',
                'nik' => '3313054112950022',
                'nbm' => '1084222',
                'desa' => 'Blumbang',
                'kecamatan' => 'Tawangmangu',
                'status' => 'menunggu_verifikasi',
                'jabatan_aum' => 'Staf Pelayanan Wisata AUM',
            ],
            [
                'name' => 'Yusuf Maulana, S.Kom.',
                'email' => 'yusuf.pending@mpksdi.test',
                'gender' => 'L',
                'nik' => '3313060207930023',
                'nbm' => '1084223',
                'desa' => 'Macanan',
                'kecamatan' => 'Kebakkramat',
                'status' => 'menunggu_verifikasi',
                'jabatan_aum' => 'Staf Publikasi PCM Kebakkramat',
            ],

            // 1 Ditolak
            [
                'name' => 'Wahyu Santoso (KTP Luar Daerah)',
                'email' => 'wahyu.ditolak@mpksdi.test',
                'gender' => 'L',
                'nik' => '3372011002900024',
                'nbm' => null,
                'desa' => 'Mojosongo',
                'kecamatan' => 'Jebres',
                'status' => 'ditolak',
                'catatan' => 'Identitas KTP bukan beralamat di wilayah Kabupaten Karanganyar. Mohon melampirkan Surat Mandat/Keterangan dari Cabang asal di Karanganyar.',
                'jabatan_aum' => 'Relawan',
            ],
        ];

        $createdAnggota = [];

        $this->ensureKaderPhotosExist();

        foreach ($kaderData as $idx => $d) {
            $user = User::updateOrCreate(
                ['email' => $d['email']],
                [
                    'name' => $d['name'],
                    'password' => Hash::make('password'),
                    'role' => 'anggota',
                    'status' => $d['status'],
                    'no_wa' => '08139'.sprintf('%07d', $idx + 100),
                    'verified_at' => $d['status'] === 'aktif' ? now() : null,
                ]
            );

            $birthYear = 1980 + ($idx % 20);
            $birthDate = Carbon::create($birthYear, ($idx % 12) + 1, ($idx % 25) + 1);

            $fotoNumber = ($idx % 20) + 1;
            $fotoPath = 'anggota/foto/'.sprintf('kader-%02d.jpg', $fotoNumber);

            $rawJabatan = $d['jabatan_aum'] ?? null;
            $namaAum = null;
            $jabatanAum = null;

            if (! empty($d['nama_aum'])) {
                $namaAum = $d['nama_aum'];
                $jabatanAum = $d['jabatan_aum'] ?? null;
            } elseif ($rawJabatan) {
                if (str_contains($rawJabatan, 'SMK Muh 2')) {
                    $namaAum = 'SMK Muhammadiyah 2 Karanganyar';
                    $jabatanAum = 'Kepala Lab Komputer';
                } elseif (str_contains($rawJabatan, 'MIM')) {
                    $namaAum = 'MIM Karanganyar';
                    $jabatanAum = 'Guru / Pendidik';
                } elseif (str_contains($rawJabatan, 'SMA Muh 1')) {
                    $namaAum = 'SMA Muhammadiyah 1 Karanganyar';
                    $jabatanAum = 'Tenaga Kependidikan / Administrasi / TU';
                } elseif (str_contains($rawJabatan, 'Klinik PKU')) {
                    $namaAum = 'Klinik PKU Muhammadiyah Karanganyar';
                    $jabatanAum = 'Tenaga Medis / Kesehatan (Dokter, Perawat, Bidan, Nakes)';
                } elseif (str_contains($rawJabatan, 'RS PKU')) {
                    $namaAum = 'RS PKU Muhammadiyah Karanganyar';
                    $jabatanAum = 'Tenaga Medis / Kesehatan (Dokter, Perawat, Bidan, Nakes)';
                } elseif (str_contains($rawJabatan, 'SMK Muhammadiyah 6')) {
                    $namaAum = 'SMK Muhammadiyah 6 Karanganyar';
                    $jabatanAum = 'Guru / Pendidik';
                } elseif (str_contains($rawJabatan, 'Lazismu')) {
                    $namaAum = 'LAZISMU Karanganyar';
                    $jabatanAum = 'Karyawan / Staf';
                } elseif (str_contains($rawJabatan, 'BTM')) {
                    $namaAum = 'BMT / BTM Muhammadiyah Karanganyar';
                    $jabatanAum = 'Karyawan / Staf';
                } elseif (str_contains($rawJabatan, 'Sekolah')) {
                    $namaAum = 'SMA Muhammadiyah 2 Karanganyar';
                    $jabatanAum = 'Wakil Kepala Sekolah / Madrasah';
                } elseif (str_contains($rawJabatan, 'SD Muhammadiyah')) {
                    $namaAum = 'SD Muhammadiyah 1 Karanganyar';
                    $jabatanAum = 'Guru / Pendidik';
                } else {
                    $namaAum = null;
                    $jabatanAum = $rawJabatan;
                }
            }

            $anggota = Anggota::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nama_lengkap' => $d['name'],
                    'alamat_rumah' => 'Jl. Pahlawan RT 02/RW 04, '.$d['desa'],
                    'jenis_kelamin' => $d['gender'],
                    'nik' => $d['nik'],
                    'nbm' => $d['nbm'],
                    'foto' => $fotoPath,
                    'nama_aum' => $namaAum,
                    'jabatan_aum' => $jabatanAum ?: $rawJabatan,
                    'pekerjaan' => $rawJabatan,
                    'pcm' => $d['kecamatan'],
                    'prm' => 'PRM '.$d['desa'],
                    'tempat_lahir' => 'Karanganyar',
                    'tanggal_lahir' => $birthDate->toDateString(),
                    'umur' => (int) $birthDate->diffInYears(now()),
                    'status_pernikahan' => 'Nikah',
                    'desa_kelurahan' => $d['desa'],
                    'kecamatan' => $d['kecamatan'],
                    'kabupaten' => 'Karanganyar',
                    'no_wa' => $user->no_wa,
                    'email' => $user->email,
                    'catatan_verifikasi' => $d['catatan'] ?? null,
                    'bahasa_dikuasai' => ['Bahasa Indonesia', 'Bahasa Jawa'],
                    'kemampuan_baca_quran' => 'Lancar dan fasih',
                    'hafalan_quran' => '1 juz',
                    'aktivitas_sholat_masjid' => 'Selalu sholat 5 waktu di masjid',
                    'aktivitas_kajian_agama' => 'Seminggu 1 - 3 x',
                    'jumlah_buku_agama' => '11 - 20 judul buku',
                    'sumber_info_muhammadiyah' => ['Kajian yang diikuti', 'Internet/Media sosial'],
                    'langganan_suara_muhammadiyah' => 'Rutin beli',
                    'lembaga_zis' => ['LAZISMU'],
                    'keaktifan_muhammadiyah' => ['Pimpinan Cabang Muhammadiyah'],
                    'jabatan_muhammadiyah' => [
                        'Pimpinan Cabang Muhammadiyah' => 'Anggota Majelis',
                    ],
                    'keaktifan_ortom' => [$d['gender'] === 'L' ? 'Pemuda Muhammadiyah' : 'Nasyiatul Aisyiyah'],
                    'jabatan_ortom' => [
                        ($d['gender'] === 'L' ? 'Pemuda Muhammadiyah' : 'Nasyiatul Aisyiyah') => 'Pengurus Cabang',
                    ],
                    'aktifitas_jamaah_masjid' => 'Jamaah aktif dan imam rawatib',
                    'keaktifan_takmir' => 'Ya',
                    'jabatan_takmir' => 'Seksi Dakwah dan Peribadatan',
                    'keaktifan_kemasyarakatan' => 'Pengurus RT dan pegiat posyandu',
                    'harapan_pcm' => 'Meningkatkan frekuensi kajian ideologis dan pengkaderan intensif berbasis ranting.',
                    'harapan_ba' => 'Menjadi kader Muhammadiyah yang militan, istiqomah berjuang di persyarikatan dan AUM.',
                ]
            );

            $createdAnggota[] = $anggota;
        }

        return $createdAnggota;
    }

    private function ensureKaderPhotosExist(): void
    {
        $storageDir = storage_path('app/private/anggota/foto');
        if (! is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $assetsDir = database_path('seeders/assets/kader');
        if (! is_dir($assetsDir)) {
            mkdir($assetsDir, 0755, true);
        }

        $f1 = base_path('foto1.png');
        $f2 = base_path('foto2.png');

        $grid1 = [
            'cols' => [[3, 304], [311, 614], [622, 915], [922, 1225], [1232, 1532]],
            'rows' => [[3, 506], [512, 1020]],
        ];
        $grid2 = [
            'cols' => [[0, 303], [309, 612], [617, 918], [924, 1226], [1232, 1535]],
            'rows' => [[0, 506], [511, 1023]],
        ];

        $img1 = null;
        $img2 = null;

        for ($i = 1; $i <= 20; $i++) {
            $filename = sprintf('kader-%02d.jpg', $i);
            $targetPath = $storageDir.DIRECTORY_SEPARATOR.$filename;

            if (file_exists($targetPath)) {
                continue;
            }

            $assetPath = $assetsDir.DIRECTORY_SEPARATOR.$filename;
            if (file_exists($assetPath)) {
                copy($assetPath, $targetPath);

                continue;
            }

            if (extension_loaded('gd') && file_exists($f1) && file_exists($f2)) {
                $img1 ??= @imagecreatefrompng($f1);
                $img2 ??= @imagecreatefrompng($f2);

                $portraitMap = [
                    1 => [$img1, $grid1['cols'][0], $grid1['rows'][0]],
                    2 => [$img1, $grid1['cols'][1], $grid1['rows'][0]],
                    3 => [$img1, $grid1['cols'][2], $grid1['rows'][0]],
                    4 => [$img1, $grid1['cols'][4], $grid1['rows'][0]],
                    5 => [$img1, $grid1['cols'][3], $grid1['rows'][0]],
                    6 => [$img1, $grid1['cols'][0], $grid1['rows'][1]],
                    7 => [$img1, $grid1['cols'][1], $grid1['rows'][1]],
                    8 => [$img1, $grid1['cols'][3], $grid1['rows'][1]],
                    9 => [$img1, $grid1['cols'][2], $grid1['rows'][1]],
                    10 => [$img1, $grid1['cols'][4], $grid1['rows'][1]],
                    11 => [$img2, $grid2['cols'][1], $grid2['rows'][0]],
                    12 => [$img2, $grid2['cols'][0], $grid2['rows'][0]],
                    13 => [$img2, $grid2['cols'][2], $grid2['rows'][0]],
                    14 => [$img2, $grid2['cols'][3], $grid2['rows'][0]],
                    15 => [$img2, $grid2['cols'][1], $grid2['rows'][1]],
                    16 => [$img2, $grid2['cols'][0], $grid2['rows'][1]],
                    17 => [$img2, $grid2['cols'][4], $grid2['rows'][0]],
                    18 => [$img2, $grid2['cols'][2], $grid2['rows'][1]],
                    19 => [$img2, $grid2['cols'][3], $grid2['rows'][1]],
                    20 => [$img2, $grid2['cols'][4], $grid2['rows'][1]],
                ];

                if (isset($portraitMap[$i]) && $portraitMap[$i][0]) {
                    [$src, $cRange, $rRange] = $portraitMap[$i];
                    $colW = $cRange[1] - $cRange[0] + 1;
                    $cropW = $colW - 8;
                    $cropH = (int) round($cropW * 4 / 3);
                    $srcX = $cRange[0] + 4;
                    $srcY = $rRange[0] + 8;
                    if ($srcY + $cropH > $rRange[1]) {
                        $srcY = $rRange[1] - $cropH;
                    }
                    $dest = imagecreatetruecolor(300, 400);
                    imagecopyresampled($dest, $src, 0, 0, $srcX, $srcY, 300, 400, $cropW, $cropH);
                    imagejpeg($dest, $assetPath, 95);
                    imagedestroy($dest);
                    copy($assetPath, $targetPath);
                }
            }
        }

        if ($img1) {
            imagedestroy($img1);
        }
        if ($img2) {
            imagedestroy($img2);
        }
    }

    private function seedOrganisasi(array $anggotaList): void
    {
        $orgs = [
            ['nama' => 'PDM Kabupaten Karanganyar', 'kategori' => 'pdm', 'pimpinan' => 'Pimpinan Daerah'],
            ['nama' => 'PCM Colomadu', 'kategori' => 'pcm', 'pimpinan' => 'Pimpinan Cabang'],
            ['nama' => 'PCM Jaten', 'kategori' => 'pcm', 'pimpinan' => 'Pimpinan Cabang'],
            ['nama' => 'PCM Tasikmadu', 'kategori' => 'pcm', 'pimpinan' => 'Pimpinan Cabang'],
            ['nama' => 'PCM Karanganyar', 'kategori' => 'pcm', 'pimpinan' => 'Pimpinan Cabang'],
            ['nama' => 'PRM Baturan Colomadu', 'kategori' => 'prm', 'pimpinan' => 'Pimpinan Ranting'],
            ['nama' => 'PRM Papahan Tasikmadu', 'kategori' => 'prm', 'pimpinan' => 'Pimpinan Ranting'],
            ['nama' => 'AUM RS PKU Muhammadiyah Karanganyar', 'kategori' => 'aum', 'pimpinan' => 'Direksi Rumah Sakit'],
            ['nama' => 'AUM SMA Muhammadiyah 1 Karanganyar', 'kategori' => 'aum', 'pimpinan' => 'Kepala Sekolah'],
            ['nama' => 'PD Pemuda Muhammadiyah Karanganyar', 'kategori' => 'ortom', 'pimpinan' => 'Pimpinan Daerah Pemuda'],
            ['nama' => 'PD Nasyiatul Aisyiyah Karanganyar', 'kategori' => 'ortom', 'pimpinan' => 'Pimpinan Daerah NA'],
        ];

        $createdOrgs = [];
        foreach ($orgs as $o) {
            $createdOrgs[] = Organisasi::updateOrCreate(
                ['nama' => $o['nama']],
                $o
            );
        }

        // Hubungkan beberapa kader dengan organisasi
        foreach (array_slice($anggotaList, 0, 15) as $i => $anggota) {
            $org = $createdOrgs[$i % count($createdOrgs)];
            $anggota->organisasi()->syncWithoutDetaching([
                $org->id => [
                    'jabatan' => $i % 2 === 0 ? 'Ketua / Koordinator' : 'Anggota / Anggota Bidang',
                    'periode' => '2022 - 2027',
                ],
            ]);
        }
    }

    private function seedEvents(User $admin, array $anggotaList): void
    {
        $fasilitator1 = User::where('email', 'fasilitator@mpksdi.test')->first();
        $fasilitator2 = User::where('email', 'instruktur@mpksdi.test')->first();

        // -------------------------------------------------------------
        // EVENT 1: Selesai (Baitul Arqam Pimpinan PCM & PRM se-Kabupaten Karanganyar)
        // -------------------------------------------------------------
        $eventSelesai = Event::updateOrCreate(
            ['nama_event' => 'Baitul Arqam Pimpinan PCM & PRM se-Kabupaten Karanganyar 2026'],
            [
                'tanggal_mulai' => Carbon::now()->subDays(12)->toDateString(),
                'tanggal_selesai' => Carbon::now()->subDays(10)->toDateString(),
                'lokasi' => 'Pusdiklat Balai Diklat Karanganyar',
                'deskripsi' => 'Pelatihan perkaderan Baitul Arqam resmi bagi jajaran pimpinan cabang dan ranting Muhammadiyah se-Daerah Karanganyar guna peneguhan ideologi gerakan dakwah tajdid dan tata kelola organisasi modern.',
                'kuota' => 30,
                'status' => 'selesai',
                'format_nomor_sertifikat' => '{nomor}/BA-MPKSDI/{bulan_romawi}/{tahun}',
                'created_by' => $admin->id,
            ]
        );

        if ($fasilitator1) {
            $eventSelesai->fasilitator()->syncWithoutDetaching([$fasilitator1->id, $fasilitator2->id]);
        }

        // Buat 6 Sesi Event Selesai
        $sesiData = [
            ['nama' => 'Kontrak Belajar & Orientasi Baitul Arqam', 'jenis' => 'materi', 'jam_m' => '08:00', 'jam_s' => '10:00', 'pemateri' => 'Tim Instruktur MPKSDI'],
            ['nama' => 'Pedoman Hidup Islami Warga Muhammadiyah (PHIWM)', 'jenis' => 'materi', 'jam_m' => '10:15', 'jam_s' => '12:00', 'pemateri' => 'Drs. H. Sriyanto, M.Pd.'],
            ['nama' => 'Sholat Lail & Tadabbur Al-Quran Berjamaah', 'jenis' => 'sholat', 'jam_m' => '03:30', 'jam_s' => '05:00', 'pemateri' => 'Ust. Ahmad Dahlan Al-Hafidz'],
            ['nama' => 'Matan Keyakinan dan Cita-cita Hidup Muhammadiyah (MKCHM)', 'jenis' => 'materi', 'jam_m' => '08:00', 'jam_s' => '11:45', 'pemateri' => 'Dr. Muhammad Ihsan, M.Ag.'],
            ['nama' => 'Dinamika Kelompok & Outbound Kepemimpinan', 'jenis' => 'outbound', 'jam_m' => '13:30', 'jam_s' => '16:00', 'pemateri' => 'Tim Outbound MPKSDI'],
            ['nama' => 'Rencana Tindak Lanjut (RTL) & Penutupan', 'jenis' => 'materi', 'jam_m' => '16:15', 'jam_s' => '17:30', 'pemateri' => 'Ketua PDM Karanganyar'],
        ];

        $createdSesi = [];
        foreach ($sesiData as $idx => $s) {
            $createdSesi[] = EventSesi::updateOrCreate(
                ['event_id' => $eventSelesai->id, 'urutan' => $idx + 1],
                [
                    'nama_sesi' => $s['nama'],
                    'jenis' => $s['jenis'],
                    'tanggal' => Carbon::now()->subDays(11)->toDateString(),
                    'jam_mulai' => $s['jam_m'],
                    'jam_selesai' => $s['jam_s'],
                    'pemateri' => $s['pemateri'],
                ]
            );
        }

        // Outbound Kelompok
        $kelompokAhmadDahlan = OutboundKelompok::updateOrCreate(
            ['event_id' => $eventSelesai->id, 'nama_kelompok' => 'Kelompok KH Ahmad Dahlan']
        );
        $kelompokNyaiWalidah = OutboundKelompok::updateOrCreate(
            ['event_id' => $eventSelesai->id, 'nama_kelompok' => 'Kelompok Nyai Siti Walidah']
        );
        $kelompokBuyaHamka = OutboundKelompok::updateOrCreate(
            ['event_id' => $eventSelesai->id, 'nama_kelompok' => 'Kelompok Buya Hamka']
        );
        $kelompokList = [$kelompokAhmadDahlan, $kelompokNyaiWalidah, $kelompokBuyaHamka];

        // Rubrik Afektif
        $this->seedAfektifDefault($eventSelesai);

        // Rubrik Psikomotor
        $this->seedPsikomotorDefault($eventSelesai);

        // Pertanyaan RTL
        $this->seedRtlDefault($eventSelesai);

        // Soal Pretest & Posttest
        $this->seedSoalTes($eventSelesai);

        // Sesi Tes (Pretest & Posttest sudah ditutup)
        SesiTes::updateOrCreate(
            ['event_id' => $eventSelesai->id, 'tipe' => 'pretest'],
            ['status' => 'tutup', 'durasi_menit' => 30]
        );
        SesiTes::updateOrCreate(
            ['event_id' => $eventSelesai->id, 'tipe' => 'posttest'],
            ['status' => 'tutup', 'durasi_menit' => 30]
        );

        // Daftarkan 14 Kader pertama ke Event Selesai
        $pesertaEvent1 = array_slice($anggotaList, 0, 14);

        // Nilai simulasi komprehensif untuk 14 kader
        // 10 Lulus (Kehadiran 100%, Nilai Akhir > 75)
        // 2 Lulus Bersyarat (Kehadiran 5/6 = 83.33%, Nilai Akhir 70-74)
        // 2 Tidak Lulus (Kehadiran 3/6 = 50%, Nilai Akhir < 60)
        $simulasiNilai = [
            ['pre' => 60, 'post' => 95, 'afk' => 94.5, 'psi' => 95.0, 'akt' => 9, 'hadir_count' => 6], // Rank 1
            ['pre' => 65, 'post' => 92, 'afk' => 92.0, 'psi' => 93.0, 'akt' => 8, 'hadir_count' => 6], // Rank 2
            ['pre' => 55, 'post' => 90, 'afk' => 90.0, 'psi' => 91.0, 'akt' => 7, 'hadir_count' => 6], // Rank 3
            ['pre' => 50, 'post' => 88, 'afk' => 88.5, 'psi' => 89.0, 'akt' => 8, 'hadir_count' => 6], // Rank 4
            ['pre' => 58, 'post' => 86, 'afk' => 87.0, 'psi' => 88.0, 'akt' => 6, 'hadir_count' => 6], // Rank 5
            ['pre' => 52, 'post' => 85, 'afk' => 86.0, 'psi' => 86.5, 'akt' => 7, 'hadir_count' => 6], // Rank 6
            ['pre' => 50, 'post' => 82, 'afk' => 85.0, 'psi' => 85.0, 'akt' => 5, 'hadir_count' => 6], // Rank 7
            ['pre' => 45, 'post' => 80, 'afk' => 83.5, 'psi' => 84.0, 'akt' => 5, 'hadir_count' => 6], // Rank 8
            ['pre' => 48, 'post' => 78, 'afk' => 82.0, 'psi' => 82.0, 'akt' => 4, 'hadir_count' => 6], // Rank 9
            ['pre' => 42, 'post' => 76, 'afk' => 80.0, 'psi' => 80.0, 'akt' => 4, 'hadir_count' => 6], // Rank 10
            ['pre' => 45, 'post' => 74, 'afk' => 75.0, 'psi' => 76.0, 'akt' => 3, 'hadir_count' => 5], // Bersyarat 1
            ['pre' => 40, 'post' => 72, 'afk' => 73.0, 'psi' => 74.0, 'akt' => 2, 'hadir_count' => 5], // Bersyarat 2
            ['pre' => 35, 'post' => 55, 'afk' => 60.0, 'psi' => 58.0, 'akt' => 1, 'hadir_count' => 3], // Tidak Lulus 1
            ['pre' => 30, 'post' => 50, 'afk' => 55.0, 'psi' => 52.0, 'akt' => 0, 'hadir_count' => 3], // Tidak Lulus 2
        ];

        $appKey = config('app.key') ?: 'base64:UBQOxlDOtOJ8DWZ20Fz3gNaxoVXs+Vki3Zffm9/xl6Y=';
        $bobot = config('arqam.bobot');
        $keaktifanCfg = config('arqam.keaktifan');

        foreach ($pesertaEvent1 as $pIdx => $anggota) {
            $kelompok = $kelompokList[$pIdx % count($kelompokList)];

            EventPeserta::updateOrCreate(
                ['event_id' => $eventSelesai->id, 'anggota_id' => $anggota->id],
                [
                    'qr_token' => 'QR-EV'.$eventSelesai->id.'-AG'.$anggota->id.'-'.strtoupper(bin2hex(random_bytes(4))),
                    'outbound_kelompok_id' => $kelompok->id,
                    'status_aktif' => true,
                ]
            );

            $dataNilai = $simulasiNilai[$pIdx] ?? $simulasiNilai[0];

            // Absensi
            for ($s = 0; $s < $dataNilai['hadir_count']; $s++) {
                $sesiTarget = $createdSesi[$s];
                Absensi::updateOrCreate(
                    ['event_id' => $eventSelesai->id, 'sesi_id' => $sesiTarget->id, 'anggota_id' => $anggota->id],
                    [
                        'waktu_scan' => Carbon::now()->subDays(11)->setTime(8 + $s, 15, 0),
                        'scanned_by' => $admin->id,
                        'metode' => $s % 2 === 0 ? 'qr' : 'manual',
                    ]
                );
            }

            // Keaktifan
            $jenisKeaktifan = ['tanya', 'jawab', 'kultum', 'imam', 'adzan', 'juara_outbound'];
            for ($k = 0; $k < $dataNilai['akt']; $k++) {
                Keaktifan::create([
                    'event_id' => $eventSelesai->id,
                    'anggota_id' => $anggota->id,
                    'jenis' => $jenisKeaktifan[$k % count($jenisKeaktifan)],
                    'sesi_id' => $createdSesi[$k % count($createdSesi)]->id,
                    'dicatat_oleh' => $admin->id,
                    'keterangan' => 'Partisipasi aktif dan kontributif saat sesi diskusi.',
                ]);
            }

            // Tes Mulai (Pretest & Posttest)
            PesertaTesMulai::updateOrCreate(
                ['event_id' => $eventSelesai->id, 'anggota_id' => $anggota->id, 'tipe' => 'pretest'],
                ['waktu_mulai' => Carbon::now()->subDays(11)->setTime(8, 0), 'waktu_selesai' => Carbon::now()->subDays(11)->setTime(8, 25)]
            );
            PesertaTesMulai::updateOrCreate(
                ['event_id' => $eventSelesai->id, 'anggota_id' => $anggota->id, 'tipe' => 'posttest'],
                ['waktu_mulai' => Carbon::now()->subDays(11)->setTime(16, 0), 'waktu_selesai' => Carbon::now()->subDays(11)->setTime(16, 25)]
            );

            // Hitung nilai matematis tepat
            $pre = (float) $dataNilai['pre'];
            $post = (float) $dataNilai['post'];
            $afk = (float) $dataNilai['afk'];
            $psi = (float) $dataNilai['psi'];
            $aktPoin = min((float) $dataNilai['akt'], 10.0);
            $aktScale = round(($aktPoin / 10.0) * 100, 2);

            $kehadiranPersen = round(($dataNilai['hadir_count'] / count($createdSesi)) * 100, 2);

            $denominator = 100 - $pre;
            $nGain = ($post === 100.0) ? 1.0 : ($denominator > 0 ? round(($post - $pre) / $denominator, 3) : 0.0);
            $ngainScale = $nGain * 100;

            $kognitif = ($post * $bobot['posttest_dalam_kognitif']) + ($ngainScale * $bobot['ngain_dalam_kognitif']);
            $psikomotor = ($psi * $keaktifanCfg['bobot_rubrik']) + ($aktScale * $keaktifanCfg['bobot_keaktifan']);
            $nilaiAkhir = round(($kognitif * $bobot['kognitif']) + ($psikomotor * $bobot['psikomotor']) + ($afk * $bobot['afektif']), 2);

            $statusKelulusan = 'Lulus';
            if ($kehadiranPersen < 75.0) {
                $statusKelulusan = 'Tidak Lulus';
            } elseif ($kehadiranPersen < 100.0) {
                $statusKelulusan = 'Lulus Bersyarat';
            }

            PenilaianAkhir::updateOrCreate(
                ['event_id' => $eventSelesai->id, 'anggota_id' => $anggota->id],
                [
                    'nilai_pretest' => $pre,
                    'nilai_posttest' => $post,
                    'nilai_afektif' => $afk,
                    'nilai_psikomotor' => $psi,
                    'nilai_keaktifan' => $aktScale,
                    'nilai_kehadiran' => $kehadiranPersen,
                    'nilai_akhir' => $nilaiAkhir,
                    'ranking' => $pIdx + 1,
                    'status_kelulusan' => $statusKelulusan,
                    'verification_hash' => hash('sha256', $eventSelesai->id.'|'.$anggota->id.'|'.$appKey),
                ]
            );
        }

        // -------------------------------------------------------------
        // EVENT 2: Berlangsung (Baitul Arqam Guru & Karyawan AUM)
        // -------------------------------------------------------------
        $eventBerlangsung = Event::updateOrCreate(
            ['nama_event' => 'Baitul Arqam Guru & Karyawan AUM Karanganyar Angkatan IV'],
            [
                'tanggal_mulai' => Carbon::now()->subDay()->toDateString(),
                'tanggal_selesai' => Carbon::now()->addDay()->toDateString(),
                'lokasi' => 'Aula SMA Muhammadiyah 1 Karanganyar',
                'deskripsi' => 'Penguatan nilai Al-Islam dan Kemuhammadiyahan (AIK) serta etos kerja Islami bagi guru dan tenaga kependidikan Amal Usaha Muhammadiyah se-Kabupaten Karanganyar.',
                'kuota' => 40,
                'status' => 'berlangsung',
                'format_nomor_sertifikat' => '{nomor}/BA-GURU-MPKSDI/{bulan_romawi}/{tahun}',
                'created_by' => $admin->id,
            ]
        );

        if ($fasilitator1) {
            $eventBerlangsung->fasilitator()->syncWithoutDetaching([$fasilitator1->id]);
        }

        // Sesi Event Berlangsung
        EventSesi::updateOrCreate(
            ['event_id' => $eventBerlangsung->id, 'urutan' => 1],
            [
                'nama_sesi' => 'Hakikat Pendidikan dan Nilai AIK di Amal Usaha',
                'jenis' => 'materi',
                'tanggal' => Carbon::now()->toDateString(),
                'jam_mulai' => '08:00',
                'jam_selesai' => '10:00',
                'pemateri' => 'Drs. H. Sriyanto, M.Pd.',
            ]
        );
        EventSesi::updateOrCreate(
            ['event_id' => $eventBerlangsung->id, 'urutan' => 2],
            [
                'nama_sesi' => 'Etos Kerja Islami Tenaga Pendidik Muhammadiyah',
                'jenis' => 'materi',
                'tanggal' => Carbon::now()->toDateString(),
                'jam_mulai' => '10:15',
                'jam_selesai' => '12:00',
                'pemateri' => 'Ust. Ahmad Dahlan Al-Hafidz',
            ]
        );

        // Daftarkan 6 peserta ke Event Berlangsung
        foreach (array_slice($anggotaList, 14, 6) as $pIdx => $anggota) {
            EventPeserta::updateOrCreate(
                ['event_id' => $eventBerlangsung->id, 'anggota_id' => $anggota->id],
                [
                    'qr_token' => 'QR-EV'.$eventBerlangsung->id.'-AG'.$anggota->id.'-'.strtoupper(bin2hex(random_bytes(4))),
                    'status_aktif' => true,
                ]
            );
        }

        // -------------------------------------------------------------
        // EVENT 3: Berlangsung (Baitul Arqam Tenaga Kesehatan RS PKU)
        // -------------------------------------------------------------
        $eventPersiapan = Event::updateOrCreate(
            ['nama_event' => 'Baitul Arqam Tenaga Kesehatan RS PKU Muhammadiyah Karanganyar'],
            [
                'tanggal_mulai' => Carbon::now()->subDay()->toDateString(),
                'tanggal_selesai' => Carbon::now()->addDays(2)->toDateString(),
                'lokasi' => 'Auditorium RS PKU Muhammadiyah Karanganyar',
                'deskripsi' => 'Pelatihan perkaderan Baitul Arqam bagi dokter, perawat, dan tenaga medis guna mewujudkan pelayanan kesehatan bernuansa dakwah dan berorientasi ibadah.',
                'kuota' => 35,
                'status' => 'berlangsung',
                'format_nomor_sertifikat' => '{nomor}/BA-PKU-MPKSDI/{bulan_romawi}/{tahun}',
                'created_by' => $admin->id,
            ]
        );

        if ($fasilitator2) {
            $eventPersiapan->fasilitator()->syncWithoutDetaching([$fasilitator2->id]);
        }
        if ($fasilitator1) {
            $eventPersiapan->fasilitator()->syncWithoutDetaching([$fasilitator1->id]);
        }

        // 5 Sesi Terjadwal Lengkap
        $sesiListPku = [
            [1, 'Falsafah Pelayanan Kesehatan Islami Penolong Kesengsaraan Oemoem', 'materi', '08:00', '10:00', 'Dr. Muhammad Ihsan, M.Ag.'],
            [2, 'Fiqih Ibadah Praktis bagi Pasien & Tenaga Medis', 'materi', '10:15', '12:00', 'Ust. H. Sarilan M.Ali, M.Pd.'],
            [3, 'Sholat Berjamaah & Bimbingan Ruhiyah Medis', 'sholat', '12:00', '13:00', 'Ust. Fahruddin, S.Ag.'],
            [4, 'Outbound Dinamika Tim Medis & Sinergi RS PKU', 'outbound', '14:00', '16:30', 'Tim Instruktur MPKSDI'],
            [5, 'Rencana Tindak Lanjut (RTL) & Komitmen Dakwah', 'materi', '19:30', '21:00', 'dr. H. Adnan Faris, Sp.A.'],
        ];

        $createdSesiPku = [];
        foreach ($sesiListPku as [$urutan, $nama, $jenis, $mulai, $selesai, $pemateri]) {
            $createdSesiPku[] = EventSesi::updateOrCreate(
                ['event_id' => $eventPersiapan->id, 'urutan' => $urutan],
                [
                    'nama_sesi' => $nama,
                    'jenis' => $jenis,
                    'tanggal' => Carbon::now()->toDateString(),
                    'jam_mulai' => $mulai,
                    'jam_selesai' => $selesai,
                    'pemateri' => $pemateri,
                ]
            );
        }

        // Rubrik Penilaian & Soal
        $this->seedAfektifDefault($eventPersiapan);
        $this->seedPsikomotorDefault($eventPersiapan);
        $this->seedRtlDefault($eventPersiapan);
        $this->seedSoalTes($eventPersiapan);

        // Regu Outbound
        $reguNames = [
            'Kelompok 1 - Ibnu Sina (Medis)',
            'Kelompok 2 - Ar-Razi (Keperawatan)',
            'Kelompok 3 - Al-Zahrawi (Farmasi & Penunjang)',
        ];
        $reguModels = [];
        foreach ($reguNames as $rName) {
            $reguModels[] = OutboundKelompok::updateOrCreate(
                ['event_id' => $eventPersiapan->id, 'nama_kelompok' => $rName]
            );
        }

        // Daftarkan 12 Peserta dari berbagai profesi nakes
        $pesertaPkuList = array_slice($anggotaList, 4, 12);
        foreach ($pesertaPkuList as $pIdx => $anggota) {
            $reguTarget = $reguModels[$pIdx % count($reguModels)];
            EventPeserta::updateOrCreate(
                ['event_id' => $eventPersiapan->id, 'anggota_id' => $anggota->id],
                [
                    'qr_token' => 'QR-EV'.$eventPersiapan->id.'-AG'.$anggota->id.'-'.strtoupper(bin2hex(random_bytes(4))),
                    'status_aktif' => true,
                    'outbound_kelompok_id' => $reguTarget->id,
                ]
            );
        }

        // CBT Sesi Tes (Pretest & Posttest)
        SesiTes::updateOrCreate(
            ['event_id' => $eventPersiapan->id, 'tipe' => 'pretest'],
            [
                'durasi_menit' => 30,
                'status' => 'tutup',
            ]
        );
        SesiTes::updateOrCreate(
            ['event_id' => $eventPersiapan->id, 'tipe' => 'posttest'],
            [
                'durasi_menit' => 45,
                'status' => 'aktif',
            ]
        );

        // Presensi & Aktivitas Peserta
        $appKey = config('app.key');
        $templatesPsi = PsikomotorTemplate::where('event_id', $eventPersiapan->id)->get();
        $butirAfk = AfektifButir::whereHas('subAspek', fn ($q) => $q->where('event_id', $eventPersiapan->id))->get();
        $rtlSoals = RtlSoal::where('event_id', $eventPersiapan->id)->get();

        foreach ($pesertaPkuList as $pIdx => $anggota) {
            // Tentukan jumlah sesi yang dihadiri untuk menghasilkan variasi status kelulusan yang realistis:
            // 5 peserta teratas hadir 5 sesi (100% = Lulus)
            // 4 peserta berikutnya hadir 4 sesi (80% = Lulus Bersyarat)
            // sisanya hadir 3 sesi (60% = Tidak Lulus)
            $jumlahSesiHadir = $pIdx < 5 ? 5 : ($pIdx < 9 ? 4 : 3);

            for ($sIdx = 0; $sIdx < $jumlahSesiHadir; $sIdx++) {
                if (isset($createdSesiPku[$sIdx])) {
                    Absensi::updateOrCreate(
                        ['event_id' => $eventPersiapan->id, 'sesi_id' => $createdSesiPku[$sIdx]->id, 'anggota_id' => $anggota->id],
                        [
                            'waktu_scan' => Carbon::now()->setTime(8 + ($sIdx * 2), 10 + $pIdx),
                            'scanned_by' => $admin->id,
                            'metode' => $sIdx % 2 === 0 ? 'qr' : 'manual',
                        ]
                    );
                }
            }

            // Keaktifan untuk beberapa peserta
            if ($pIdx % 3 === 0) {
                Keaktifan::create([
                    'event_id' => $eventPersiapan->id,
                    'anggota_id' => $anggota->id,
                    'jenis' => 'tanya',
                    'sesi_id' => $createdSesiPku[0]->id,
                    'dicatat_oleh' => $admin->id,
                    'keterangan' => 'Mengajukan pertanyaan etika medis dalam pandangan Majelis Tarjih.',
                ]);
            }
            if ($pIdx % 4 === 0) {
                Keaktifan::create([
                    'event_id' => $eventPersiapan->id,
                    'anggota_id' => $anggota->id,
                    'jenis' => 'kultum',
                    'sesi_id' => $createdSesiPku[2]->id,
                    'dicatat_oleh' => $admin->id,
                    'keterangan' => 'Memberikan kultum ba\'da Dzuhur tentang keutamaan merawat orang sakit.',
                ]);
            }

            // Pengerjaan Tes Mulai & Selesai
            PesertaTesMulai::updateOrCreate(
                ['event_id' => $eventPersiapan->id, 'anggota_id' => $anggota->id, 'tipe' => 'pretest'],
                ['waktu_mulai' => Carbon::now()->subHours(4), 'waktu_selesai' => Carbon::now()->subHours(4)->addMinutes(22)]
            );

            $preScore = 55.0 + ($pIdx * 3);
            $postScore = 0.0;
            if ($pIdx < 8) {
                PesertaTesMulai::updateOrCreate(
                    ['event_id' => $eventPersiapan->id, 'anggota_id' => $anggota->id, 'tipe' => 'posttest'],
                    ['waktu_mulai' => Carbon::now()->subMinutes(25), 'waktu_selesai' => Carbon::now()->subMinutes(5)]
                );
                $postScore = min(98.0, 72.0 + ($pIdx * 2.5));
            }

            // Afektif Nilai
            foreach ($butirAfk as $b) {
                AfektifJawaban::updateOrCreate(
                    ['event_id' => $eventPersiapan->id, 'anggota_id' => $anggota->id, 'butir_id' => $b->id],
                    ['skor' => ($pIdx + $b->id) % 2 === 0 ? 4 : 3]
                );
            }

            // Psikomotor Nilai
            foreach ($templatesPsi as $t) {
                PsikomotorNilai::updateOrCreate(
                    ['event_id' => $eventPersiapan->id, 'anggota_id' => $anggota->id, 'template_id' => $t->id],
                    ['skor' => 3 + (($pIdx + $t->id) % 2), 'dinilai_oleh' => $admin->id]
                );
            }

            // RTL Status & Jawaban
            $rtlStatus = $pIdx < 4 ? 'disetujui' : ($pIdx < 8 ? 'terkirim' : ($pIdx < 10 ? 'draf' : null));
            if ($rtlStatus) {
                $rtl = Rtl::updateOrCreate(
                    ['event_id' => $eventPersiapan->id, 'anggota_id' => $anggota->id],
                    ['status' => $rtlStatus]
                );

                foreach ($rtlSoals as $sIdx => $s) {
                    RtlJawaban::updateOrCreate(
                        ['rtl_id' => $rtl->id, 'soal_id' => $s->id],
                        [
                            'jawaban' => $s->tipe === 'upload' ? 'uploads/rtl_demo.pdf' : 'Komitmen integrasi dakwah dalam pelayanan medis pasien poli dan rawat inap.',
                            'file_bukti' => $s->tipe === 'upload' ? 'uploads/rtl_demo.pdf' : null,
                        ]
                    );
                }
            }

            // Simpan initial scores di PenilaianAkhir sebelum hitungUlang
            PenilaianAkhir::updateOrCreate(
                ['event_id' => $eventPersiapan->id, 'anggota_id' => $anggota->id],
                [
                    'nilai_pretest' => $preScore,
                    'nilai_posttest' => $postScore,
                    'verification_hash' => hash('sha256', $eventPersiapan->id.'|'.$anggota->id.'|'.$appKey),
                ]
            );
        }

        // Jalankan kalkulasi nilai resmi dari NilaiService
        app(NilaiService::class)->hitungUlang($eventPersiapan);

        $this->seedAngketDemo([$eventSelesai, $eventBerlangsung, $eventPersiapan]);
    }

    private function seedAngketDemo(array $events): void
    {
        $sampleComments = [
            'Alhamdulillah materi Manhaj Tarjih & Ideologi Muhammadiyah sangat membuka wawasan dan aplikatif untuk pembinaan di Cabang kami.',
            'Pelayanan panitia MPKSDI sangat ramah dan sigap. Fasilitas aula nyaman, hanya sound system perlu sedikit penyesuaian saat sesi malam.',
            'Narasumber sangat inspiratif dan interaktif. Waktu diskusi kelompok dan problem solving terasa sangat hidup dan berbobot.',
            'Kegiatan Baitul Arqam ini memberikan suntikan ghiroh luar biasa bagi pengabdian kami di Amal Usaha Muhammadiyah Karanganyar.',
            'Secara umum sangat memuaskan, jadwal shalat berjamaah dan qiyamul lail sangat disiplin dan membina ruhani.',
        ];

        $samplePerasaan = [
            'Sangat bersyukur dan bangga bisa mengikuti Baitul Arqam ini. Hati terasa lebih sejuk, pemahaman Al-Islam dan Kemuhammadiyahan semakin kokoh, serta tumbuh ghiroh berjuang yang luar biasa.',
            'Merasa tercerahkan dan tercharge kembali semangat dakwahnya. Kebersamaan shalat berjamaah dan tadabbur memberikan ketenangan batin yang mendalam.',
            'Awalnya merasa tegang karena jadwalnya padat, tetapi setelah dijalani suasananya sangat menyenangkan, akrab, dan penuh hikmah persaudaraan.',
            'Luar biasa berkesan! Materi yang disampaikan membuka mata hati saya untuk lebih ikhlas dan istiqomah mengabdi di persyarikatan dan AUM.',
            'Merasa lebih mantap dan percaya diri dalam mengamalkan ibadah sesuai HPT, serta termotivasi untuk terus menyebarkan syiar amar ma\'ruf nahi munkar.',
        ];

        foreach ($events as $event) {
            AngketItem::seedDefaultsFor($event);

            $items = AngketItem::where('event_id', $event->id)->get();
            $peserta = EventPeserta::where('event_id', $event->id)->where('status_aktif', true)->take(6)->get();

            $idx = 0;
            foreach ($peserta as $p) {
                foreach ($items as $it) {
                    AngketJawaban::updateOrCreate(
                        ['event_id' => $event->id, 'anggota_id' => $p->anggota_id, 'item_id' => $it->id],
                        ['skor' => ($idx % 2 === 0) ? 5 : 4]
                    );
                }

                if (isset($sampleComments[$idx]) || isset($samplePerasaan[$idx])) {
                    AngketKomentar::updateOrCreate(
                        ['event_id' => $event->id, 'anggota_id' => $p->anggota_id],
                        [
                            'perasaan' => $samplePerasaan[$idx] ?? null,
                            'komentar' => $sampleComments[$idx] ?? null,
                        ]
                    );
                }
                $idx++;
            }
        }
    }

    private function seedAfektifDefault(Event $event): void
    {
        if (AfektifSubAspek::where('event_id', $event->id)->exists()) {
            return;
        }

        $subAspeks = [
            'A. Sikap Keislaman' => [
                ['Saya melaksanakan shalat berjamaah lima waktu tepat waktu di masjid.', true],
                ['Saya rutin mengistiqomahkan tilawah Al-Quran minimal satu juz per hari.', true],
                ['Saya mengabaikan ajakan shalat tahajud dan qiyamul lail.', false],
                ['Saya ikhlas menyalurkan zakat, infak, dan sedekah melalui LAZISMU.', true],
            ],
            'B. Sikap Kemuhammadiyahan' => [
                ['Saya bangga dan berkomitmen membela persyarikatan Muhammadiyah.', true],
                ['Saya mempelajari dan memahami Matan Keyakinan dan Cita-cita Hidup Muhammadiyah.', true],
                ['Saya merasa terbebani jika ditugaskan menjadi panitia kegiatan persyarikatan.', false],
                ['Saya aktif menggerakkan jamaah shalat dan kajian di Ranting tempat tinggal.', true],
            ],
            'C. Etos Kerja dan Kepemimpinan' => [
                ['Saya menunjukkan keteladanan akhlak mulia dalam memimpin tim.', true],
                ['Saya enggan menerima saran perbaikan dari rekan atau peserta lain.', false],
                ['Saya menyelesaikan amanah dan tugas perkaderan dengan penuh dedikasi.', true],
            ],
        ];

        $urutan = 0;
        foreach ($subAspeks as $nama => $butirs) {
            $urutan++;
            $sa = AfektifSubAspek::create([
                'event_id' => $event->id,
                'nama_sub_aspek' => $nama,
                'urutan' => $urutan,
                'aktif' => true,
            ]);

            foreach ($butirs as $bIdx => [$teks, $positif]) {
                AfektifButir::create([
                    'sub_aspek_id' => $sa->id,
                    'teks_butir' => $teks,
                    'is_positif' => $positif,
                    'urutan' => $bIdx + 1,
                ]);
            }
        }
    }

    private function seedPsikomotorDefault(Event $event): void
    {
        if (PsikomotorTemplate::where('event_id', $event->id)->exists()) {
            return;
        }

        $templates = [
            ['Praktik Wudhu dan Tayamum Sesuai HPT', 'ibadah', 4],
            ['Praktik Shalat Fardhu dan Sujud Sahwi', 'ibadah', 4],
            ['Tahsin Membaca Al-Quran dan Tajwid', 'ibadah', 4],
            ['Kerja Sama Tim & Problem Solving', 'outbound', 4],
            ['Kedisiplinan & Ketepatan Waktu', 'outbound', 4],
            ['Ketangkasan dan Semangat Kebersamaan', 'outbound', 4],
        ];

        foreach ($templates as [$nama, $jenis, $skorMaks]) {
            PsikomotorTemplate::create([
                'event_id' => $event->id,
                'nama_aspek' => $nama,
                'jenis' => $jenis,
                'skor_maks' => $skorMaks,
            ]);
        }
    }

    private function seedRtlDefault(Event $event): void
    {
        if (RtlSoal::where('event_id', $event->id)->exists()) {
            return;
        }

        $questions = [
            ['Unggah bukti pendaftaran atau kartu anggota NBM / E-KTAM Anda.', 'upload'],
            ['Tuliskan komitmen program dakwah di Ranting atau Cabang domisili Anda pasca-BA.', 'essay'],
            ['Sebutkan satu inovasi kemasjidan atau amal usaha yang akan Anda rintis.', 'essay'],
        ];

        foreach ($questions as $idx => [$pertanyaan, $tipe]) {
            RtlSoal::create([
                'event_id' => $event->id,
                'pertanyaan' => $pertanyaan,
                'tipe' => $tipe,
                'urutan' => $idx + 1,
            ]);
        }
    }

    private function seedSoalTes(Event $event): void
    {
        if (Soal::where('event_id', $event->id)->exists()) {
            return;
        }

        $soalList = [
            [
                'soal' => 'Siapakah pendiri gerakan Persyarikatan Muhammadiyah di Yogyakarta pada tahun 1912?',
                'pilihan' => [
                    ['A', 'K.H. Ahmad Dahlan', true],
                    ['B', 'K.H. Hasyim Asyari', false],
                    ['C', 'K.H. Mas Mansyur', false],
                    ['D', 'Buya Hamka', false],
                ],
            ],
            [
                'soal' => 'Muhammadiyah menetapkan sumber ajaran Islam yang utama bersumber dari:',
                'pilihan' => [
                    ['A', 'Al-Quran dan As-Sunnah As-Shahihah', true],
                    ['B', 'Pendapat mayoritas ulama madzhab', false],
                    ['C', 'Tradisi lokal yang dilestarikan', false],
                    ['D', 'Kitab kuning klasik semata', false],
                ],
            ],
            [
                'soal' => 'Kepanjangan dari MKCHM dalam khazanah ideologi Muhammadiyah adalah:',
                'pilihan' => [
                    ['A', 'Matan Keyakinan dan Cita-cita Hidup Muhammadiyah', true],
                    ['B', 'Majelis Kerja Cabang Hidup Muhammadiyah', false],
                    ['C', 'Musyawarah Kader Cabang Hidup Muhammadiyah', false],
                    ['D', 'Manhaj Kepemimpinan dan Cita Hidup Muhammadiyah', false],
                ],
            ],
            [
                'soal' => 'Lembaga amil zakat, infak, dan sedekah resmi di bawah naungan Persyarikatan Muhammadiyah bernama:',
                'pilihan' => [
                    ['A', 'LAZISMU', true],
                    ['B', 'BAZNAS', false],
                    ['C', 'Dompet Dhuafa', false],
                    ['D', 'Rumah Zakat', false],
                ],
            ],
            [
                'soal' => 'Prinsip dakwah amar maruf nahi munkar dalam kepemimpinan persyarikatan dilaksanakan dengan cara:',
                'pilihan' => [
                    ['A', 'Hikmah, mauizhah hasanah, dan mujadalah bil ahsan', true],
                    ['B', 'Paksaan dan sanksi sosial keras', false],
                    ['C', 'Menghindari interaksi sosial masyarakat luas', false],
                    ['D', 'Eksklusif hanya di internal warga persyarikatan', false],
                ],
            ],
        ];

        foreach (['pretest', 'posttest'] as $tipe) {
            foreach ($soalList as $idx => $s) {
                $soal = Soal::create([
                    'event_id' => $event->id,
                    'tipe' => $tipe,
                    'teks_soal' => $s['soal'],
                    'urutan' => $idx + 1,
                ]);

                foreach ($s['pilihan'] as [$label, $teks, $benar]) {
                    PilihanJawaban::create([
                        'soal_id' => $soal->id,
                        'label' => $label,
                        'teks' => $teks,
                        'is_benar' => $benar,
                    ]);
                }
            }
        }
    }
}
