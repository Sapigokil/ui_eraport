<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BackupRestoreController extends Controller
{
    private $disk = 'local';

    private function getBackupPath()
    {
        // Membaca nama folder langsung dari konfigurasi spatie
        return config('backup.backup.name');
    }

    /**
     * 1. Menampilkan Halaman Index & List File Backup
     */
    public function index()
    {
        $disk = Storage::disk($this->disk);
        $folder = $this->getBackupPath();
        $files = [];

        if ($disk->exists($folder)) {
            $allFiles = $disk->files($folder);
            
            // Ambil informasi tiap file (.zip)
            foreach ($allFiles as $f) {
                if (File::extension($f) === 'zip') {
                    $files[] = [
                        'name' => str_replace($folder . '/', '', $f),
                        'size' => $this->formatSizeUnits($disk->size($f)),
                        'date' => Carbon::createFromTimestamp($disk->lastModified($f))->format('d M Y - H:i:s'),
                        'raw_date' => $disk->lastModified($f)
                    ];
                }
            }
        }

        // Urutkan dari yang terbaru (descending)
        usort($files, function($a, $b) {
            return $b['raw_date'] <=> $a['raw_date'];
        });

        return view('user.backuprestore.index', compact('files'));
    }

    /**
     * 2. Membuat Backup Baru (Generate .zip)
     */
    public function createBackup()
    {
        try {
            // Matikan limit eksekusi agar backup database besar tidak terputus di VPS
            set_time_limit(0); 

            // Jalankan command spatie backup khusus database
            Artisan::call('backup:run', ['--only-db' => true]);
            
            return back()->with('success', 'Backup database berhasil dibuat.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat backup: ' . $e->getMessage());
        }
    }

    /**
     * 3. Download File Backup
     */
    public function download($file_name)
    {
        $filePath = $this->getBackupPath() . '/' . $file_name;
        
        if (!Storage::disk($this->disk)->exists($filePath)) {
            return back()->with('error', 'File tidak ditemukan di server.');
        }

        return Storage::disk($this->disk)->download($filePath);
    }

    /**
     * 4. Upload File Backup (.zip) secara manual
     */
    public function uploadBackup(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|mimes:zip|max:512000' // Maksimal 500MB
        ]);

        $file = $request->file('backup_file');
        $folder = $this->getBackupPath();
        
        // Simpan langsung ke folder backup spatie
        $file->storeAs($folder, $file->getClientOriginalName(), $this->disk);

        return back()->with('success', 'File backup berhasil diunggah.');
    }

    /**
     * 5. Menghapus File Backup Fisik
     */
    public function delete($file_name)
    {
        $filePath = $this->getBackupPath() . '/' . $file_name;

        if (Storage::disk($this->disk)->exists($filePath)) {
            Storage::disk($this->disk)->delete($filePath);
            return back()->with('success', 'File backup berhasil dihapus dari server.');
        }

        return back()->with('error', 'File tidak ditemukan.');
    }

    /**
     * 6. Restore Database dari File .zip (OPTIMASI VPS LINUX)
     */
    public function restore($file_name)
    {
        // 1. Matikan batasan waktu eksekusi
        set_time_limit(0);

        $folder = $this->getBackupPath();
        $zipPath = Storage::disk($this->disk)->path($folder . '/' . $file_name);

        if (!file_exists($zipPath)) {
            return back()->with('error', 'File zip tidak ditemukan.');
        }

        try {
            // 2. Ekstrak File ZIP Sementara ke folder spesifik
            $extractPath = storage_path('app/temp_restore_' . time());
            $zip = new \ZipArchive;
            if ($zip->open($zipPath) === TRUE) {
                $zip->extractTo($extractPath);
                $zip->close();
            } else {
                throw new \Exception('Gagal membuka file ZIP.');
            }

            // 3. Cari file .sql hasil ekstraksi
            $sqlFiles = glob($extractPath . '/db-dumps/*.sql'); 
            if (empty($sqlFiles)) {
                File::deleteDirectory($extractPath);
                throw new \Exception('File SQL tidak ditemukan di dalam struktur ZIP.');
            }
            $sqlFilePath = $sqlFiles[0]; 

            // 4. Siapkan koneksi dari konfigurasi .env
            $dbHost = env('DB_HOST', '127.0.0.1');
            $dbPort = env('DB_PORT', '3306');
            $dbName = env('DB_DATABASE');
            $dbUser = env('DB_USERNAME');
            $dbPass = env('DB_PASSWORD');

            // 5. Susun Perintah CLI Linux yang Aman dan Dinamis
            if (empty($dbPass)) {
                // Jika tidak ada password (contoh: default XAMPP)
                $command = sprintf(
                    'mysql -h %s -P %s -u %s %s < "%s" 2>&1',
                    escapeshellarg($dbHost),
                    escapeshellarg($dbPort),
                    escapeshellarg($dbUser),
                    escapeshellarg($dbName),
                    $sqlFilePath
                );
            } else {
                // Jika ada password (VPS Production)
                // Menggunakan MYSQL_PWD menghindari warning "Using a password on the CLI..."
                // escapeshellarg() melindungi dari karakter aneh di password/username
                $command = sprintf(
                    'MYSQL_PWD=%s mysql -h %s -P %s -u %s %s < "%s" 2>&1',
                    escapeshellarg($dbPass),
                    escapeshellarg($dbHost),
                    escapeshellarg($dbPort),
                    escapeshellarg($dbUser),
                    escapeshellarg($dbName),
                    $sqlFilePath
                );
            }

            // 6. Eksekusi Perintah
            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);

            // 7. Bersihkan folder temporary hasil ekstrak (WAJIB agar server tidak penuh)
            File::deleteDirectory($extractPath);

            // 8. Cek apakah ada error dari proses eksekusi
            if ($returnVar !== 0) {
                 $errorMsg = implode("\n", $output);
                 throw new \Exception('MySQL Exec Error: ' . $errorMsg);
            }

            return back()->with('success', 'Database berhasil di-restore. Data pada tabel lama telah ditimpa ke versi backup.');

        } catch (\Exception $e) {
            // Pembersihan ekstra jika terjadi error di tengah jalan
            if (isset($extractPath) && File::exists($extractPath)) {
                File::deleteDirectory($extractPath);
            }
            return back()->with('error', 'Proses Restore Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Helper untuk mengubah byte menjadi KB, MB, GB
     */
    private function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
}