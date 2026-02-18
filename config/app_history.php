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

    'current_version' => '1.0.8.beta',
    
    'last_updated' => '2026-02-10',

    'changelog' => [
        [
            'version' => '1.0.8.beta - On Development',
            'date'    => '2026-02-13',
            'notes'   => [
                'Menambahkan Fitur Mutasi Siswa Keluar, Pindah, Kenaikan, dan Kelulusan',
                'Menambahkan Hak Akses untuk fitur mutasi siswa',

            ],
        ],
        [
            'version' => '1.0.7',
            'date'    => '2026-02-10',
            'notes'   => [
                'Memisahkan menu input ekstrakurikuler dan input wali kelas untuk memudahkan pengelolaan nilai',
                'Halaman Input Nilai Ekstrakurikuler diperbarui',
                'Halaman Input Walikelas diperbarui menyesuaiakan dengan perubahan data Ekstrakurikuler',
                'Penataan beberapa tampilan halaman',
                'Merombak beberapa fungsi untuk meningkatkan performa aplikasi',
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