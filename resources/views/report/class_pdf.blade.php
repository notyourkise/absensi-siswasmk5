<!DOCTYPE html>
<html>
<head>
    <title>Rekap Absensi - {{ $kelas }}</title>
    <style>
        /* 1. RESET & FONT */
        body { font-family: 'Times New Roman', Times, serif; font-size: 11px; margin: 0; padding: 0; }
        
        /* 2. KOP SURAT (Layout Tabel) */
        .kop-surat { width: 100%; border-bottom: 4px double #000; padding-bottom: 10px; margin-bottom: 15px; }
        .kop-logo { width: 75px; height: auto; }
        .kop-tengah { text-align: center; line-height: 1.1; }
        .kop-prov { font-size: 12pt; }
        .kop-dinas { font-size: 12pt; font-weight: bold; }
        .kop-nama { font-size: 14pt; font-weight: bold; margin: 2px 0; }
        .kop-alamat { font-size: 9pt; font-style: italic; }

        /* 3. TABEL DATA */
        table.data { width: 100%; border-collapse: collapse; border: 1px solid #000; }
        table.data th, table.data td { border: 1px solid #000; padding: 2px; text-align: center; vertical-align: middle; }
        table.data th { background-color: #E5E7EB; font-weight: bold; font-size: 10px; }
        
        /* Kolom Khusus */
        .col-no { width: 20px; }
        .col-nama { text-align: left; padding-left: 5px; width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .col-tgl { width: 18px; font-size: 9px; }

        /* 4. WARNA STATUS (Color Coding) */
        /* Menggunakan !important agar warna background mencetak */
        .bg-hijau { background-color: #86efac !important; color: #000; } /* H (Green-300) */
        .bg-kuning { background-color: #fde047 !important; color: #000; } /* S/I (Yellow-300) */
        .bg-merah { background-color: #fca5a5 !important; color: #000; }  /* A (Red-300) */
        .bg-abu { background-color: #f3f4f6 !important; } /* Hari Libur/Kosong */

        /* 5. LEGEND & TTD */
        .footer-info { margin-top: 15px; width: 100%; }
        .legend-box { width: 12px; height: 12px; display: inline-block; border: 1px solid #000; vertical-align: middle; }
        .ttd-table { width: 100%; margin-top: 20px; border: none; }
        .ttd-table td { border: none; text-align: center; }
    </style>
</head>
<body>

    {{-- 1. KOP SURAT RESMI --}}
    <table class="kop-surat">
        <tr>
            {{-- Logo Kiri (Kaltim) --}}
            <td width="15%" align="center">
                {{-- Pastikan file ada di public/logo/kaltim.webp --}}
                <img src="{{ public_path('logo/kaltim.webp') }}" class="kop-logo" alt="Logo">
            </td>
            
            {{-- Teks Tengah --}}
            <td width="70%" class="kop-tengah">
                <span class="kop-prov">PEMERINTAH PROVINSI KALIMANTAN TIMUR</span><br>
                <span class="kop-dinas">DINAS PENDIDIKAN DAN KEBUDAYAAN</span><br>
                <div class="kop-nama">SMK NEGERI 5 SAMARINDA</div>
                <span class="kop-alamat">
                    Jalan KH. Wahid Hasyim Nomor 75 RT. 08 Kel. Sempaja Selatan<br>
                    Samarinda, Kalimantan Timur 75119<br>
                    Laman: http://smkn5smd.sch.id/
                </span>
            </td>

            {{-- Logo Kanan (Sekolah) --}}
            <td width="15%" align="center">
                <img src="{{ public_path('logo/sekolah.png') }}" class="kop-logo" alt="Logo">
            </td>
        </tr>
    </table>

    {{-- 2. JUDUL LAPORAN --}}
    <div style="text-align: center; margin-bottom: 15px;">
        <h3 style="margin: 0; text-transform: uppercase;">REKAPITULASI KEHADIRAN SISWA</h3>
        <p style="margin: 3px 0; font-size: 10pt;">
            Kelas: <strong>{{ $kelas }}</strong> | Periode: <strong>{{ $namaBulan }} {{ $tahun }}</strong>
        </p>
    </div>

    {{-- 3. TABEL DATA --}}
    <table class="data">
        <thead>
            <tr>
                <th rowspan="2" class="col-no">No</th>
                <th rowspan="2" class="col-nama" style="text-align: center;">Nama Siswa</th>
                {{-- Loop Header Tanggal --}}
                <th colspan="{{ $daysInMonth }}">Tanggal</th>
                {{-- Loop Header Total --}}
                <th colspan="4">Total</th>
            </tr>
            <tr>
                @for($d = 1; $d <= $daysInMonth; $d++)
                    <th class="col-tgl">{{ $d }}</th>
                @endfor
                <th width="20" class="bg-hijau">H</th>
                <th width="20" class="bg-kuning">S</th>
                <th width="20" class="bg-kuning">I</th>
                <th width="20" class="bg-merah">A</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $s)
                @php
                    // Reset Counter
                    $h = 0; $s_count = 0; $i = 0; $a = 0;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="col-nama">{{ strtoupper($s->nama) }}</td>

                    {{-- LOOPING TANGGAL (ISI) --}}
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $status = $attendanceData[$s->id][$d] ?? '-';
                            $bgClass = '';
                            $kode = '';

                            // Logika Pewarnaan & Kode
                            if ($status == 'Hadir') { 
                                $bgClass = 'bg-hijau'; 
                                $kode = '•'; 
                                $h++; 
                            } elseif ($status == 'Terlambat') { 
                                $bgClass = 'bg-hijau'; // Terlambat tetap dihitung Hadir (Hijau)
                                $kode = 'T'; 
                                $h++; 
                            } elseif ($status == 'Sakit') { 
                                $bgClass = 'bg-kuning'; 
                                $kode = 'S'; 
                                $s_count++; 
                            } elseif ($status == 'Izin') { 
                                $bgClass = 'bg-kuning'; 
                                $kode = 'I'; 
                                $i++; 
                            } elseif ($status == 'Alpha') { 
                                $bgClass = 'bg-merah'; 
                                $kode = 'A'; 
                                $a++; 
                            }
                        @endphp

                        <td class="{{ $bgClass }}">{{ $kode }}</td>
                    @endfor

                    {{-- TOTAL COUNT --}}
                    <td class="bg-hijau">{{ $h }}</td>
                    <td class="bg-kuning">{{ $s_count }}</td>
                    <td class="bg-kuning">{{ $i }}</td>
                    <td class="bg-merah">{{ $a }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- 4. FOOTER (KETERANGAN & TTD) --}}
    <div class="footer-info">
        <table style="width: 100%; border: none;">
            <tr>
                {{-- Kiri: Legend Warna --}}
                <td style="width: 60%; vertical-align: top; border: none; text-align: left;">
                    <strong>Keterangan:</strong><br>
                    <div style="margin-top: 5px; font-size: 10px;">
                        <span class="legend-box bg-hijau"></span> Hadir / Terlambat &nbsp;&nbsp;
                        <span class="legend-box bg-kuning"></span> Sakit / Izin &nbsp;&nbsp;
                        <span class="legend-box bg-merah"></span> Alpha
                    </div>
                </td>
                
                {{-- Kanan: Tanda Tangan --}}
                <td style="width: 40%; border: none;">
                    <table class="ttd-table">
                        <tr>
                            <td>Samarinda, {{ date('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td>Wali Kelas,</td>
                        </tr>
                        <tr>
                            <td style="height: 60px;"></td> </tr>
                        <tr>
                            <td><strong>( .................................... )</strong></td>
                        </tr>
                        <tr>
                            <td style="font-size: 9pt;">NIP. ...........................</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>