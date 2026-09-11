<?php

return [
    'nama_sistem' => env('ARQAM_NAMA_SISTEM', 'ARQAM MPKSDI Karanganyar'),

    'bobot' => [
        'kognitif' => 0.40,
        'psikomotor' => 0.35,
        'afektif' => 0.25,
        'posttest_dalam_kognitif' => 0.70,
        'ngain_dalam_kognitif' => 0.30,
    ],

    'keaktifan' => [
        'bonus_maks' => 10.0,
        'bobot_rubrik' => 0.90,
        'bobot_keaktifan' => 0.10,
        'jenis' => ['tanya', 'jawab', 'kultum', 'imam', 'adzan', 'juara_outbound'],
    ],

    'kehadiran' => [
        'lulus' => 100.0,
        'lulus_bersyarat' => 75.0,
    ],

    'opsi_form' => [
        'jenis_kelamin' => ['L' => 'Laki-laki', 'P' => 'Perempuan'],
        'status_pernikahan' => ['Nikah', 'Belum Nikah', 'Cerai'],
        'kemampuan_baca_quran' => [
            'Belum bisa membaca',
            'Bisa tetapi belum lancar',
            'Lancar belum fasih',
            'Lancar dan fasih',
        ],
        'hafalan_quran' => [
            'Belum hafal',
            '1-5 surat',
            '6-15 surat',
            '16-30 surat',
            '1 juz',
            '2-5 juz',
            'Lebih dari 5 juz',
        ],
        'aktivitas_sholat_masjid' => [
            'Selalu sholat 5 waktu di masjid',
            'Sering sholat 5 waktu di masjid',
            'Kadang-kadang sholat di masjid',
            'Jarang sholat di masjid',
        ],
        'aktivitas_kajian_agama' => [
            'Seminggu lebih dari 3x',
            'Seminggu 1 - 3 x',
            'Seminggu 1x',
            'Sebulan 1x',
            'Tidak pernah ikut kajian',
        ],
        'jumlah_buku_agama' => [
            'Tidak punya',
            '1 - 5 judul buku',
            '6 - 10 judul buku',
            '11 - 20 judul buku',
            'Lebih dari 20 judul buku',
        ],
        'sumber_info_muhammadiyah' => [
            'Kajian yang diikuti',
            'Buku yang dibaca',
            'Internet/Media sosial',
            'Teman/keluarga',
        ],
        'langganan_suara_muhammadiyah' => ['Rutin beli', 'Terkadang beli', 'Tidak pernah beli'],
        'lembaga_zis' => ['LAZISMU', 'BAZNAS', 'LEMBAGA LAIN'],
        'bahasa_dikuasai' => ['Bahasa Indonesia', 'Bahasa Jawa', 'Bahasa Arab', 'Bahasa Inggris', 'Lainnya'],
        'keaktifan_muhammadiyah' => [
            'Pimpinan Daerah Muhammadiyah',
            'Pimpinan Cabang Muhammadiyah',
            'Pimpinan Ranting Muhammadiyah',
            'Amal Usaha Muhammadiyah',
            'Tidak aktif',
        ],
        'keaktifan_ortom' => [
            'Pemuda Muhammadiyah',
            'Nasyiatul Aisyiyah',
            'Aisyiyah',
            'Ikatan Pelajar Muhammadiyah',
            'Hizbul Wathon',
            'Tapak Suci',
            'Tidak aktif',
        ],
    ],
];
