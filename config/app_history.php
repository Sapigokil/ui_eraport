<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Version Information
    |--------------------------------------------------------------------------
    |
    | File ini digunakan untuk menyimpan riwayat versi aplikasi secara hardcode.
    | Anda dapat memanggilnya di mana saja menggunakan helper config().
    |
    */

    'current_version' => '1.0.7',
    
    'last_updated' => '2026-02-10',

    'changelog' => [
        [
            'version' => '1.0.7',
            'date'    => '2026-02-10',
            'notes'   => [
                'Halaman Input Nilai Ekstrakurikuler diperbarui',
            ],
        ],
        [
            'version' => '1.0.6',
            'date'    => '2026-01-27',
            'notes'   => [
                'Memperbaiki Rapor Siswa agar data selalu konsisten',
                'Update database nilai_akhir_rapor izinkan null pada kolom status_kenaikan',
                'Menambahkan Helper NilaiCalculator untuk perhitungan nilai akhir',
                'Penataan ulang halaman Ledger dan Sorting Data',
            ],
        ],
        [
            'version' => '1.0.5',
            'date'    => '2026-01-25',
            'notes'   => [
                'Menambahkan fitur Rekap Nilai Siswa',
                'Memperbaiki beberapa bug',
                'Optimasi performa aplikasi',
                'Memisahkan menu input nilai dan inputan wali kelas',
            ],
        ],
        
    ],
];