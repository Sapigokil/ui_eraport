@extends('layouts.app') 

@section('page-title', 'Backup & Restore Database')

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5">
            <div class="row">
                <div class="col-12">
                    
                    {{-- Alert Bahaya (Informasional) --}}
                    <div class="alert mb-5 shadow-sm" role="alert" style="background-color: #fff3cd; border-left: 4px solid #ffc107; border-radius: 6px;">
                        <span class="text-dark">
                            <strong><i class="fas fa-exclamation-triangle text-warning me-2"></i> Peringatan Penting!</strong><br>
                            Fitur Restore menggunakan metode <strong>Standar Overwrite</strong>. Melakukan restore akan menimpa data yang ada saat ini dengan data dari file backup. Lakukan dengan sangat hati-hati!
                        </span>
                    </div>

                    {{-- Margin top diubah menjadi mt-5 agar tidak bertabrakan dengan header ungu --}}
                    <div class="card mt-5 mb-4 shadow-xs border">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3 mb-0">
                                    <i class="fas fa-database me-2"></i> Manajemen Backup Database
                                </h6>
                                
                                <div class="d-flex me-3">
                                    {{-- Tombol Trigger Modal Upload --}}
                                    <button type="button" class="btn btn-info btn-sm mb-0 me-2" data-bs-toggle="modal" data-bs-target="#uploadBackupModal">
                                        <i class="fas fa-upload me-1"></i> Upload File Backup (.zip)
                                    </button>
                                    
                                    {{-- PERBAIKAN ROUTE: settings.backup.create --}}
                                    <form action="{{ route('settings.backup.create') }}" method="POST" onsubmit="showProcessingAlert('Sedang meng-generate backup database...')">
                                        @csrf
                                        <button type="submit" class="btn btn-white btn-sm mb-0">
                                            <i class="fas fa-plus-circle me-1"></i> Buat Backup Sekarang
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="card-body px-0 pb-2">
                            
                            {{-- Notifikasi --}}
                            @if (session('success'))
                                <div class="alert bg-gradient-success mx-4 alert-dismissible text-white" role="alert">
                                    <span class="text-sm">{{ session('success') }}</span>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif
                            @if (session('error'))
                                <div class="alert bg-gradient-danger mx-4 alert-dismissible text-white" role="alert">
                                    <span class="text-sm">{{ session('error') }}</span>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            {{-- Tabel Daftar File Backup --}}
                            <div class="table-responsive p-0 mt-3">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-4">Nama File Backup</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Ukuran File</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Waktu Dibuat</th>
                                            <th class="text-center text-secondary opacity-7">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($files as $file)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-file-archive text-secondary fa-lg me-3"></i>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $file['name'] }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <span class="badge badge-sm bg-gradient-light text-dark">{{ $file['size'] }}</span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-secondary text-xs font-weight-bold">{{ $file['date'] }}</span>
                                            </td>
                                            <td class="align-middle text-center">
                                                
                                                {{-- PERBAIKAN ROUTE: settings.backup.download --}}
                                                <a href="{{ route('settings.backup.download', $file['name']) }}" class="text-success font-weight-bold text-xs me-3" data-bs-toggle="tooltip" title="Download File">
                                                    <i class="fas fa-download me-1"></i> Unduh
                                                </a>

                                                {{-- PERBAIKAN ROUTE: settings.backup.restore --}}
                                                <form action="{{ route('settings.backup.restore', $file['name']) }}" method="POST" class="d-inline" onsubmit="return confirmRestore(this)">
                                                    @csrf
                                                    <button type="submit" class="btn btn-link text-warning p-0 m-0 text-xs me-3" title="Restore Database">
                                                        <i class="fas fa-sync-alt me-1"></i> Restore
                                                    </button>
                                                </form>

                                                {{-- PERBAIKAN ROUTE: settings.backup.delete --}}
                                                <form action="{{ route('settings.backup.delete', $file['name']) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link text-danger p-0 m-0 text-xs" 
                                                            onclick="return confirm('Yakin hapus file backup fisik ini secara permanen dari server?')" title="Hapus File">
                                                        <i class="fas fa-trash-alt me-1"></i> Hapus
                                                    </button>
                                                </form>
                                                
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-sm text-secondary">
                                                <i class="fas fa-folder-open fa-3x mb-3 text-light"></i><br>
                                                Belum ada file backup ditemukan di server.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            
            <x-app.footer />
        </div>
    </main>

    {{-- MODAL UPLOAD MANUAL --}}
    <div class="modal fade" id="uploadBackupModal" tabindex="-1" aria-labelledby="uploadBackupModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-gradient-info">
                    <h5 class="modal-title text-white" id="uploadBackupModalLabel"><i class="fas fa-upload me-2"></i> Upload File Backup Lokal</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                {{-- PERBAIKAN ROUTE: settings.backup.upload --}}
                <form action="{{ route('settings.backup.upload') }}" method="POST" enctype="multipart/form-data" onsubmit="showProcessingAlert('Sedang mengunggah file. Mohon tunggu...')">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="backup_file" class="form-label font-weight-bold">Pilih File (.zip) <span class="text-danger">*</span></label>
                            <input class="form-control border px-2 py-1" type="file" id="backup_file" name="backup_file" accept=".zip" required>
                            <small class="text-muted text-xs">Pastikan file ZIP berisi struktur dump database yang valid dari sistem ini.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary mb-0" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn bg-gradient-info mb-0">Upload File</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- SCRIPT JAVASCRIPT --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // Custom Confirm untuk Restore yang menakutkan
            window.confirmRestore = function(form) {
                if (confirm('BAHAYA: Anda yakin ingin melakukan RESTORE dari file ini?\n\nSemua perubahan data terbaru akan ditimpa! Proses ini tidak bisa dibatalkan.')) {
                    showProcessingAlert('Sedang merestore database. Proses ini memakan waktu. JANGAN TUTUP BROWSER ANDA!');
                    return true;
                }
                return false;
            }

            // Popup Loading Modal
            window.showProcessingAlert = function(message) {
                const existingAlert = document.getElementById('processingAlert');
                if (existingAlert) return;

                const alertHtml = `
                    <div class="alert bg-gradient-dark text-white text-center shadow-lg" role="alert" id="processingAlert" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999; padding: 30px; border-radius: 15px; border: 1px solid #444;">
                        <h5 class="alert-heading text-white mb-3"><i class="fas fa-cog fa-spin me-2"></i> MEMPROSES PERMINTAAN</h5>
                        <p class="mb-0 text-sm">${message}</p>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', alertHtml);
            }
        });
    </script>
@endsection