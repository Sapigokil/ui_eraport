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

    'current_version' => '1.0.20',
    
    'last_updated' => '2026-04-14',

    'changelog' => [
        [
            'version' => '1.0.20',
            'date'    => '2026-04-14',
            'notes'   => [
                'Penambahan Season Pada Form Biodata untuk mengatur periode pembaruan biodata siswa',
                'Penambahan Validasi pada Form Biodata untuk memastikan data yang diinput sesuai dengan periode yang sudah diatur',
                'Penambahan Fitur Reset Jadwal Biodata untuk mengembalikan status portal biodata menjadi belum diatur',
                'Penambahan Menu Laporan PSTS, untuk melihat dan mencetak laporan PSTS per periode (tahun ajaran + semester) dan kelas',
                'Rollback halaman login sistem agar lebuh aman menggunakan Username dan Password',
                ],
        ],
        [
            'version' => '1.0.19',
            'date'    => '2026-04-10',
            'notes'   => [
                'Perbaikah Halaman Data Siswa, untuk mendukung pengajuan perubahan Data',
                'Penambahan Menu Validasi Biodata untuk Admin memproses pengajuan perubahan biodata siswa',
                'Optimasi Proses Pengajuan Perubahan Biodata dengan menggunakan JSON untuk menyimpan data perubahan dan keputusan admin agar lebih fleksibel dan mudah dikembangkan di masa depan',
            ],
        ],
        [
            'version' => '1.0.18',
            'date'    => '2026-04-02',
            'notes'   => [
                'Penambahan Tombol Massal pada halaman cetak Rapor PKL',
                'Perbaikan Halaman Input Nilai PKL',
                'Optimasi Proses Generate Rapor PKL untuk meningkatkan kecepatan dan mengurangi beban server',
            ],
        ],
        [
            'version' => '1.0.17',
            'date'    => '2026-04-01',
            'notes'   => [
                'Perbaikan Halaman Input Nilai PKL',
                'Perbaikan Halaman Cetak Rapor PKL',
                'Perbaikan Template PDF Rapor PKL',
                'Penambahan Kolom Program Keahlian dan Konsentrasi Keahlian pada Data Kelas',
                'Menautkan Data PKL dengan Data Kelas untuk digunakan dalam Rapor PKL',
            ],
        ],
        [
            'version' => '1.0.16',
            'date'    => '2026-03-31',
            'notes'   => [
                'Penambahan Fitur Import Pada Input Nilai Prakerin untuk memudahkan input nilai dari file Excel',
                'Perbaikan Halaman User untuk opsi menambah pengguna baru dengan role guru dan siswa agar bisa langsung menautkan ke data guru/siswa yang sudah ada',
                'Perbaikan Bug pada halaman Penempatan PKL',
                'Perbaikan Bug pada halaman cetak Rapor',
                'Perbaikan Bug pada fitur Import Excel untuk Tempat PKL',
                'Perbaikan proses generate Rapor agar melakukan pengecekan data lebih ketat untuk menghindari error saat generate',
                'Portal Siswa (on progress) untuk memudahkan siswa melihat nilai dan rapor mereka secara online',
            ],
        ],
        [
            'version' => '1.0.15',
            'date'    => '2026-03-16',
            'notes'   => [
                'Perbaikan Template PDF Rapor PKL untuk penyesuaian format baru',
            ],
        ],
        [
            'version' => '1.0.14',
            'date'    => '2026-03-14',
            'notes'   => [
                'Menambahkan Mode Simulasi kedalam sistem',
                'Menambahkan Database simulasi untuk testing fitur baru tanpa mengganggu data utama',
                'Menambahkan Middleware untuk switch database secara otomatis berdasarkan session mode_simulasi',
                'Menambahkan tombol toggle mode simulasi di sidebar untuk memudahkan pengujian fitur baru',
                'Dengan mode simulasi, admin dapat menguji fitur baru dengan data dummy tanpa risiko',
                'Menambahkan menu pengaturan simulasi untuk mengelola data dummy dan melihat status sinkronisasi terakhir',
                'Memidahkan Backup & Restore ke menu terpisah agar lebih fokus pada fitur simulasi',
            ],
        ],
        [
            'version' => '1.0.13',
            'date'    => '2026-03-13',
            'notes'   => [
                'Memperbaiki Bug Import Export Excel untuk Tempat PKL',
                'Memperbaiki halaman user agar Admin bisa mengupdate password user lain tanpa harus mengisi password baru (password tetap jika kolom password dikosongkan)',
                'Menu Baru backup & restore database untuk keamanan data',    
                'Penerapan Filter Data bagi Role Guru untuk hanya melihat data yang relevan dengan kelas dan mapel yang dia ampu',
            ],
        ],
        [
            'version' => '1.0.12',
            'date'    => '2026-03-11',
            'notes'   => [
                'Menambahkan halaman Cetak Cover Rapor',
                'Optimasi beberapa query untuk meningkatkan performa aplikasi',
                'Perombakan ulang terkait roles dan permissions untuk menyesuaikan dengan fitur rapor PKL',
            ],
        ],
        [
            'version' => '1.0.11',
            'date'    => '2026-03-10',
            'notes'   => [
                'Menambahkan menu monitoring rapor PKL untuk memantau kesiapan data rapor sebelum dicetak',
                'Menambahkan menu cetak rapor PKL untuk memudahkan proses generate dan finalisasi rapor PKL',
                'Menambahkan fitur finalisasi rapor PKL untuk mengunci data rapor agar tidak bisa diubah setelah final',
                'Menambahkan fitur unlock rapor PKL untuk membuka kunci data rapor jika diperlukan',
                'Menambahkan fitur pencarian dan filter pada halaman monitoring rapor PKL',
                'Menambahkan fitur download PDF rapor PKL per siswa ataupun massal dengan opsi merge menjadi satu file PDF',
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