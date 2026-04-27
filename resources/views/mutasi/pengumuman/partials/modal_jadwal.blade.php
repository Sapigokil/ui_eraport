<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            
            {{-- Header Modal --}}
            <div class="modal-header {{ $jenis == 'kelulusan' ? 'bg-gradient-warning' : 'bg-gradient-primary' }}">
                <h5 class="modal-title text-white" id="{{ $id }}Label">
                    <i class="fas fa-clock me-2"></i> Pengaturan Waktu {{ ucfirst($jenis) }}
                </h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- FORM SIMPAN JADWAL --}}
            <form id="form-simpan-{{ $jenis }}" action="{{ route('mutasi.update_jadwal') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <input type="hidden" name="jenis" value="{{ $jenis }}">
                    <input type="hidden" name="tahun_ajaran" value="{{ $ta }}">

                    <div class="alert alert-light text-sm p-3 border mb-4">
                        <i class="fas fa-info-circle text-info me-1"></i> 
                        Tentukan rentang waktu pengumuman. Saat disimpan, status siswa akan <b>Otomatis di-Publish</b> namun terkunci oleh hitung mundur.
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-weight-bold text-dark">Waktu Pengumuman Dibuka</label>
                        <input type="datetime-local" name="waktu_buka" class="form-control" required
                               value="{{ $current && $current->waktu_buka ? $current->waktu_buka->format('Y-m-d\TH:i') : '' }}">
                    </div>

                    <div class="mb-0">
                        <label class="form-label font-weight-bold text-dark">Waktu Pengumuman Ditutup</label>
                        <input type="datetime-local" name="waktu_tutup" class="form-control" required
                               value="{{ $current && $current->waktu_tutup ? $current->waktu_tutup->format('Y-m-d\TH:i') : '' }}">
                    </div>
                </div>
            </form>
            
            {{-- FOOTER BESERTA TOMBOL HAPUS --}}
            <div class="modal-footer bg-light border-top d-flex justify-content-between">
                
                {{-- Jika jadwal sudah ada, munculkan tombol hapus --}}
                @if($current)
                    <form action="{{ route('mutasi.delete_jadwal') }}" method="POST" class="m-0 p-0">
                        @csrf
                        <input type="hidden" name="jenis" value="{{ $jenis }}">
                        <input type="hidden" name="tahun_ajaran" value="{{ $ta }}">
                        <button type="submit" class="btn btn-outline-danger mb-0 shadow-sm" onclick="return confirm('Yakin ingin menghapus jadwal ini? Status pengumuman siswa akan otomatis ditarik kembali (Hold).')">
                            <i class="fas fa-trash-alt me-1"></i> Hapus Jadwal
                        </button>
                    </form>
                @else
                    <div></div> {{-- Spacer agar tombol simpan tetap di kanan --}}
                @endif

                <div>
                    <button type="button" class="btn btn-secondary mb-0 shadow-sm" data-bs-dismiss="modal">Batal</button>
                    {{-- Tombol submit yang menunjuk ke form simpan menggunakan atribut 'form' --}}
                    <button type="submit" form="form-simpan-{{ $jenis }}" class="btn {{ $jenis == 'kelulusan' ? 'btn-warning text-white' : 'btn-primary' }} mb-0 shadow-sm">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                </div>

            </div>
            
        </div>
    </div>
</div>