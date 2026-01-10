<tr>
            <td colspan="4" class="no-border-header">
                <table class="rapor-header">
                <tr>
                    <td width="65%">
                        <span class="h-label">Nama</span><span class="h-sep">:</span>
                        <span class="h-val nama-siswa">{{ strtoupper($data['siswa']->nama_siswa) }}</span><br>
                        <span class="h-label">NIS / NISN</span><span class="h-sep">:</span>
                        <span class="h-val">{{ $data['siswa']->nipd }} / {{ $data['siswa']->nisn }}</span><br>
                        <span class="h-label">Nama Sekolah</span><span class="h-sep">:</span>
                        <span class="h-val">{{ $data['sekolah'] }}</span><br>
                        <span class="h-label" style="font-size:11pt">Alamat</span><span class="h-sep">:</span>
                        <span class="h-val" style="font-size:10pt;display:inline-block;max-width:300px;">{{ $data['infoSekolah'] ?? '-' }}</span>
                    </td>

                    <td width="35%">
                        <span class="h-label">Kelas</span><span class="h-sep">:</span>
                        <span class="h-val">{{ $data['siswa']->kelas->nama_kelas }}</span><br>
                        <span class="h-label">Fase</span><span class="h-sep">:</span>
                        <span class="h-val">{{ $data['fase'] }}</span><br>
                        <span class="h-label">Semester</span><span class="h-sep">:</span>
                        <span class="h-val">{{ $data['semesterInt'] }} ({{ $data['semester'] }})</span><br>
                        <span class="h-label">Tahun Pelajaran</span><span class="h-sep">:</span>
                        <span class="h-val">{{ $data['tahun_ajaran'] }}</span>
                    </td>
                </tr>

                <tr>
                    <td colspan="4" style="padding:0;">
                        <div style="border-bottom:2px solid #000; margin-top:-6px;"></div>
                    </td>
                </tr>

                @if(!empty($showTitle))
                <tr>
                    <td colspan="2" class="judul-header"
                    style="font-size:14pt;font-weight:bold;text-align:center;padding:20px 0 6px;">
                        LAPORAN HASIL BELAJAR
                    </td>
                </tr>
                @endif
            </table>
            </td>
        </tr>