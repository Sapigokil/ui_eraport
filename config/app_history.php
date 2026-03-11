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

    'current_version' => '1.0.11c',
    
    'last_updated' => '2026-03-10',

    'changelog' => [
        
        [
            'version' => '1.0.11c',
            'date'    => '2026-03-10',
            'notes'   => [
                'Menambahkan menu monitoring rapor PKL untuk memantau kesiapan data rapor sebelum dicetak',
                'Menambahkan menu cetak rapor PKL untuk memudahkan proses generate dan finalisasi rapor PKL',
                'Menambahkan fitur finalisasi rapor PKL untuk mengunci data rapor agar tidak bisa diubah setelah final',
                'Menambahkan fitur unlock rapor PKL untuk membuka kunci data rapor jika diperlukan',
                'Menambahkan fitur pencarian dan filter pada halaman monitoring rapor PKL',
                'Menambahkan fitur download PDF rapor PKL per siswa ataupun massal dengan opsi merge menjadi satu file PDF',
                'Menambahkan halaman Cetak Cover Rapor',
                'Optimasi beberapa query untuk meningkatkan performa aplikasi',
            ],
        ],
        [
            'version' => '1.0.10',
            'date'    => '2026-03-09',
            'notes'   => [
                'Menambahkan Menu Prakerin sebagai menu utama untuk menampung semua fitur terkait Prakerin / PKL / Magang',
                'menambahkan Menu Pengaturan Rapor PKL untuk mengelola indikator penilaian dan setting terkait PKL',
                'Menambahkan Menu Input Nilai Prakerin untuk memudahkan input nilai PKL',
            ],
        ],
        [
            'version' => '1.0.9',
            'date'    => '2026-03-04',
            'notes'   => [
                'Menambahkan Menu Daftar Tempat Prakerin / PKL / Magang',
                'Menambahkan Menu Guru Pembimbing PKL',
                'Menambahkan Menu Penempatan PKL',
                'Merombak Ledger untuk mendukung pembacaan data history setelah mutasi siswa',
            ],
        ],
        [
            'version' => '1.0.8',
            'date'    => '2026-02-21',
            'notes'   => [
                'Menambahkan Fitur Mutasi Siswa Keluar, Pindah, Kenaikan, dan Kelulusan',
                'Menambahkan Hak Akses untuk fitur mutasi siswa',
                'Perbaikan Rule terkait inputan nilai dan generate Rapor untuk siswa mutasi',
                'Penataan ulang beberapa tampilan halaman untuk menyesuaikan dengan fitur mutasi',
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