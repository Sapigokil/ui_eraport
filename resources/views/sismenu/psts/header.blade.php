{{-- resources/views/sismenu/psts/header.blade.php --}}
<table class="rapor-header" style="width: 100%; border-collapse: collapse; font-size: 11pt; margin-bottom: 5px;">
    <tr>
        <td width="65%" style="vertical-align: top;">
            <table style="width: 100%; border: none;">
                <tr>
                    <td style="width: 110px; padding: 2px 0;">Nama</td>
                    <td style="width: 15px; padding: 2px 0; text-align: center;">:</td>
                    <td style="padding: 2px 0; font-weight: bold;">{{ strtoupper($siswa->nama_siswa) }}</td>
                </tr>
                <tr>
                    <td style="padding: 2px 0;">NIS / NISN</td>
                    <td style="padding: 2px 0; text-align: center;">:</td>
                    <td style="padding: 2px 0;">{{ $siswa->nipd }} / {{ $siswa->nisn }}</td>
                </tr>
                <tr>
                    <td style="padding: 2px 0;">Nama Sekolah</td>
                    <td style="padding: 2px 0; text-align: center;">:</td>
                    <td style="padding: 2px 0;">{{ $infoSekolah->nama_sekolah ?? 'SMKN 1 SALATIGA' }}</td>
                </tr>
                <tr>
                    <td style="padding: 2px 0; font-size: 11pt;">Alamat</td>
                    <td style="padding: 2px 0; text-align: center;">:</td>
                    <td style="padding: 2px 0; font-size: 10pt;">{{ $infoSekolah->jalan ?? 'JL. NAKULA SADEWA 1/3 SALATIGA' }}</td>
                </tr>
            </table>
        </td>

        <td width="35%" style="vertical-align: top;">
            <table style="width: 100%; border: none;">
                <tr>
                    <td style="width: 120px; padding: 2px 0;">Kelas</td>
                    <td style="width: 15px; padding: 2px 0; text-align: center;">:</td>
                    <td style="padding: 2px 0;">{{ $siswa->kelas->nama_kelas }}</td>
                </tr>
                <tr>
                    <td style="padding: 2px 0;">Fase</td>
                    <td style="padding: 2px 0; text-align: center;">:</td>
                    <td style="padding: 2px 0;">{{ $fase ?? 'F' }}</td>
                </tr>
                <tr>
                    <td style="padding: 2px 0;">Semester</td>
                    <td style="padding: 2px 0; text-align: center;">:</td>
                    <td style="padding: 2px 0;">{{ $semesterInt }} ({{ $semester }})</td>
                </tr>
                <tr>
                    <td style="padding: 2px 0;">Tahun Pelajaran</td>
                    <td style="padding: 2px 0; text-align: center;">:</td>
                    <td style="padding: 2px 0;">{{ $tahun_ajaran }}</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="padding: 0;">
            <div style="border-bottom: 2px solid #000; margin-top: 5px; margin-bottom: 10px;"></div>
        </td>
    </tr>
</table>