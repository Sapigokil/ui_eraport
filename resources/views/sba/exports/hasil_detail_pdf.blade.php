<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Detail Hasil Asesmen - {{ $hasil->uji->judul }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11pt; color: #333; line-height: 1.4; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2 { margin: 0; text-transform: uppercase; }
        .header p { margin: 2px 0 0; font-size: 9pt; color: #666; }
        
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 5px 8px; font-size: 10pt; vertical-align: top; }
        .info-table td.label { font-weight: bold; width: 22%; background-color: #f5f5f5; border: 1px solid #ddd; }
        .info-table td.value { border: 1px solid #ddd; }
        
        .soal-card { border: 1px solid #ddd; margin-bottom: 15px; padding: 12px; border-radius: 5px; page-break-inside: avoid; }
        .soal-header { font-weight: bold; font-size: 10pt; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 8px; color: #2c3e50; }
        .pertanyaan { margin-bottom: 10px; font-size: 10.5pt; }
        .soal-img { max-height: 160px; max-width: 100%; margin-bottom: 10px; border: 1px solid #ddd; padding: 2px; border-radius: 3px; }
        
        .opsi-box { border: 1px solid #e0e0e0; padding: 6px 10px; margin-bottom: 5px; border-radius: 3px; font-size: 9.5pt; }
        .opsi-correct { background-color: #e8f5e9; border-color: #a5d6a7; font-weight: bold; }
        .opsi-wrong { background-color: #ffebee; border-color: #ef9a9a; }
        .opsi-img { max-height: 90px; max-width: 240px; margin-top: 4px; display: block; border: 1px solid #ccc; padding: 2px; background: #fff; }
        
        .badge { display: inline-block; padding: 2px 6px; font-size: 8pt; font-weight: bold; border-radius: 3px; float: right; }
        .badge-success { background-color: #4caf50; color: #fff; }
        .badge-danger { background-color: #f44336; color: #fff; }
        .badge-info { background-color: #2196f3; color: #fff; }
        
        .jawaban-esai { background-color: #fafafa; border: 1px dashed #ccc; padding: 8px; font-style: italic; font-size: 10pt; margin-top: 5px; }
    </style>
</head>
<body>

    @php
        $resolvePdfImage = function (?string $storagePath): ?string {
            if (empty($storagePath)) {
                return null;
            }
            $fullPath = public_path('storage/' . ltrim($storagePath, '/'));
            if (!file_exists($fullPath)) {
                return null;
            }
            $mime = mime_content_type($fullPath) ?: 'image/jpeg';
            $data = base64_encode(file_get_contents($fullPath));
            return "data:{$mime};base64,{$data}";
        };
    @endphp

    <div class="header">
        <h2>LEMBAR HASIL ASESMEN DIGITAL (SBA)</h2>
        <p>Kode Uji: {{ strtoupper($hasil->uji->kode_uji) }} | Judul: {{ $hasil->uji->judul }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Nama Peserta</td>
            <td class="value">{{ $hasil->asesi ? $hasil->asesi->name : ($hasil->nama_peserta_publik ?? 'Guest') }}</td>
            <td class="label">Total Skor Akhir</td>
            <td class="value"><strong>{{ number_format($hasil->total_skor, 0) }} / 100 Poin</strong></td>
        </tr>
        <tr>
            <td class="label">ID Asesi / Status</td>
            <td class="value">{{ $hasil->asesi_id ? 'USER #'.$hasil->asesi_id : 'Peserta Publik' }} ({{ strtoupper($hasil->status_pengerjaan) }})</td>
            <td class="label">Percobaan Ke</td>
            <td class="value">Ke-{{ $hasil->percobaan_ke }}</td>
        </tr>
        <tr>
            <td class="label">Waktu Pengerjaan</td>
            <td class="value">{{ $hasil->waktu_mulai ? $hasil->waktu_mulai->format('d/m/Y H:i') : '-' }}</td>
            <td class="label">Durasi / Pelanggaran</td>
            <td class="value">{{ $hasil->durasi_formatted }} | {{ $hasil->jumlah_pelanggaran }} Pelanggaran</td>
        </tr>
    </table>

    <h4 style="margin-bottom: 10px; border-bottom: 1px solid #333; padding-bottom: 4px;">RINCIAN JAWABAN PESERTA</h4>

    @foreach($hasil->uji->soals as $index => $soal)
        @php
            $jawabanSiswa = $mapJawaban->get($soal->id);
            $jawabanRaw = $jawabanSiswa ? $jawabanSiswa->jawaban : null;
            $skorDidapat = $jawabanSiswa ? (float) $jawabanSiswa->skor_didapat : 0;
            $kunciArray = is_array($soal->kunci_jawaban) ? $soal->kunci_jawaban : [];
            $soalImgSrc = $resolvePdfImage($soal->media ?? null);
        @endphp

        <div class="soal-card">
            <div class="soal-header">
                Soal #{{ $index + 1 }} (Tipe: {{ strtoupper(str_replace('_', ' ', $soal->tipe)) }}) - Bobot: {{ $soal->bobot_nilai }} Poin
                @if($soal->tipe !== 'esai')
                    @if($skorDidapat >= (float) $soal->bobot_nilai && $skorDidapat > 0)
                        <span class="badge badge-success">BENAR (+{{ round($skorDidapat, 2) }})</span>
                    @elseif($skorDidapat > 0)
                        <span class="badge badge-info">SEBAGIAN (+{{ round($skorDidapat, 2) }})</span>
                    @else
                        <span class="badge badge-danger">SALAH (0)</span>
                    @endif
                @else
                    <span class="badge badge-info">Skor Esai: {{ round($skorDidapat, 2) }} / {{ $soal->bobot_nilai }}</span>
                @endif
            </div>

            <div class="pertanyaan">{!! nl2br(e($soal->pertanyaan)) !!}</div>

            @if($soalImgSrc)
                <div>
                    <img src="{{ $soalImgSrc }}" alt="Gambar Soal" class="soal-img">
                </div>
            @endif

            @if(in_array($soal->tipe, ['pg', 'pg_kompleks']))
                @php
                    $opsiTeksList = is_array($soal->opsi_jawaban) ? $soal->opsi_jawaban : [];
                    $opsiMediaList = is_array($soal->opsi_media) ? $soal->opsi_media : [];
                    $jumlahSlotOpsi = max(count($opsiTeksList), count($opsiMediaList));
                @endphp

                @for($optIdx = 0; $optIdx < $jumlahSlotOpsi; $optIdx++)
                    @php
                        $opsiTeks = trim((string) ($opsiTeksList[$optIdx] ?? ''));
                        $opsiImgSrc = $resolvePdfImage($opsiMediaList[$optIdx] ?? null);
                    @endphp
                    @if($opsiTeks !== '' || $opsiImgSrc)
                        @php
                            $isKunci = in_array((string)$optIdx, array_map('strval', $kunciArray));
                            $isChosen = false;
                            if ($soal->tipe === 'pg_kompleks') {
                                $siswaArr = is_array($jawabanRaw) ? $jawabanRaw : (json_decode((string) $jawabanRaw, true) ?? []);
                                $isChosen = in_array((string)$optIdx, array_map('strval', $siswaArr));
                            } else {
                                $isChosen = ((string)$optIdx === (string)$jawabanRaw && $jawabanRaw !== null);
                            }

                            $boxClass = 'opsi-box';
                            if ($isKunci) $boxClass .= ' opsi-correct';
                            elseif ($isChosen && !$isKunci) $boxClass .= ' opsi-wrong';
                        @endphp

                        <div class="{{ $boxClass }}">
                            <strong>{{ chr(65 + $optIdx) }}.</strong>
                            @if($opsiTeks !== '')
                                {{ $opsiTeks }}
                            @endif
                            @if($isKunci) [KUNCI BENAR] @endif
                            @if($isChosen) &laquo; (Pilihan Asesi) @endif

                            @if($opsiImgSrc)
                                <img src="{{ $opsiImgSrc }}" alt="Gambar Opsi {{ chr(65 + $optIdx) }}" class="opsi-img">
                            @endif
                        </div>
                    @endif
                @endfor

            @elseif($soal->tipe === 'benar_salah')
                @php
                    $bsPernyataanList = is_array($soal->opsi_jawaban) ? $soal->opsi_jawaban : [];
                    $siswaBsArr = is_array($jawabanRaw) ? $jawabanRaw : (json_decode((string) $jawabanRaw, true) ?? []);
                @endphp

                @foreach($bsPernyataanList as $stmtIdx => $stmtItem)
                    @php
                        $teksPernyataan = is_array($stmtItem) ? ($stmtItem['teks'] ?? '') : (string) $stmtItem;
                        $kunciStmt = is_array($stmtItem)
                            ? strtolower(trim((string) ($stmtItem['kunci'] ?? ($kunciArray[$stmtIdx] ?? ''))))
                            : strtolower(trim((string) ($kunciArray[$stmtIdx] ?? '')));
                        $jawabStmt = strtolower(trim((string) ($siswaBsArr[$stmtIdx] ?? '')));

                        $boxClass = 'opsi-box';
                        if ($kunciStmt !== '' && $jawabStmt === $kunciStmt) {
                            $boxClass .= ' opsi-correct';
                        } elseif ($kunciStmt !== '' && $jawabStmt !== $kunciStmt) {
                            $boxClass .= ' opsi-wrong';
                        }
                    @endphp

                    <div class="{{ $boxClass }}">
                        <strong>{{ $stmtIdx + 1 }}.</strong> {{ $teksPernyataan }}
                        @if($kunciStmt !== '')
                            [Kunci: {{ ucfirst($kunciStmt) }}]
                        @endif
                        &laquo; (Asesi: {{ $jawabStmt !== '' ? ucfirst($jawabStmt) : 'Tidak Dijawab' }})
                    </div>
                @endforeach

            @elseif($soal->tipe === 'esai')
                <div class="jawaban-esai">
                    <strong>Jawaban Uraian Asesi:</strong><br>
                    {{ $jawabanRaw ? $jawabanRaw : '- Tidak Menjawab -' }}
                </div>
            @endif
        </div>
    @endforeach

</body>
</html>