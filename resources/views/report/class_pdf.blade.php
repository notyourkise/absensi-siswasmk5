<!DOCTYPE html>
<html>
<head>
    <title>Rekap Absensi - {{ $kelas }}</title>
    <style>
        /* --- 1. SETTING HALAMAN (KUNCI AGAR TIDAK TERPOTONG) --- */
        @page { 
            margin: 10px 20px; /* Atas-Bawah 10px, Kiri-Kanan 20px */
            size: landscape;   /* Paksa Landscape lewat CSS */
        }

        /* --- 2. RESET & FONT --- */
        body { 
            font-family: 'Times New Roman', Times, serif; 
            font-size: 10px; /* DIPERKECIL (Awalnya 11px) */
            margin: 0; 
            padding: 0; 
        }
        
        /* --- 3. KOP SURAT --- */
        .kop-surat { width: 100%; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 10px; }
        .kop-logo { width: 65px; height: auto; } /* Logo diperkecil sedikit */
        .kop-tengah { text-align: center; line-height: 1.1; }
        .kop-prov { font-size: 11pt; }
        .kop-dinas { font-size: 11pt; font-weight: bold; }
        .kop-nama { font-size: 13pt; font-weight: bold; margin: 2px 0; }
        .kop-alamat { font-size: 8pt; font-style: italic; }

        /* --- 4. TABEL DATA (INI YANG KRUSIAL) --- */
        table.data { 
            width: 100%; 
            border-collapse: collapse; 
            border: 1px solid #000; 
            font-size: 9px; /* DIPERKECIL (Agar muat banyak kolom) */
        }
        
        table.data th, table.data td { 
            border: 1px solid #000; 
            padding: 1px; /* DIRAAPATKAN (Awalnya 2px) */
            text-align: center; 
            vertical-align: middle; 
            line-height: 1.1; /* Jarak antar baris teks dirapatkan */
        }

        table.data th { background-color: #E5E7EB; font-weight: bold; }
        
        /* Pengaturan Lebar Kolom */
        .col-no { width: 18px; }
        
        .col-nama { 
            text-align: left; 
            padding-left: 4px; 
            width: 130px; /* DIPERKECIL (Awalnya 150px) */
            white-space: nowrap; 
            overflow: hidden; 
            text-overflow: ellipsis; 
        }

        /* Kolom Tanggal: Hemat Pixel disini sangat berpengaruh! */
        .col-tgl { 
            width: 14px; /* DIPERKECIL (Awalnya 18px) */
            font-size: 8px; /* Angka tanggal diperkecil */
        }

        /* --- 5. WARNA STATUS --- */
        .bg-hijau { background-color: #86efac !important; color: #000; }
        .bg-kuning { background-color: #fde047 !important; color: #000; }
        .bg-merah { background-color: #fca5a5 !important; color: #000; }
        .bg-abu { background-color: #f3f4f6 !important; }

        /* --- 6. FOOTER --- */
        .footer-info { margin-top: 10px; width: 100%; font-size: 9pt; }
        .legend-box { width: 10px; height: 10px; display: inline-block; border: 1px solid #000; vertical-align: middle; }
        .ttd-table { width: 100%; margin-top: 10px; border: none; }
        .ttd-table td { border: none; text-align: center; }
    </style>
</head>
<body>

    {{-- 1. KOP SURAT --}}
    <table class="kop-surat">
        <tr>
            <td width="15%" align="center">
                <img src="{{ public_path('logo/kaltim.webp') }}" class="kop-logo" alt="Logo">
            </td>
            <td width="70%" class="kop-tengah">
                <span class="kop-prov">PEMERINTAH PROVINSI KALIMANTAN TIMUR</span><br>
                <span class="kop-dinas">DINAS PENDIDIKAN DAN KEBUDAYAAN</span><br>
                <div class="kop-nama">SMK NEGERI 5 SAMARINDA</div>
                <span class="kop-alamat">
                    Jalan KH. Wahid Hasyim Nomor 75 RT. 08 Kel. Sempaja Selatan<br>
                    Samarinda, Kalimantan Timur 75119<br>
                    Laman: https://smkn5smr.sch.id/
                </span>
            </td>
            <td width="15%" align="center">
                <img src="{{ public_path('logo/sekolah.png') }}" class="kop-logo" alt="Logo">
            </td>
        </tr>
    </table>

    {{-- 2. JUDUL LAPORAN --}}
    <div style="text-align: center; margin-bottom: 10px;">
        <h3 style="margin: 0; font-size: 12pt; text-transform: uppercase;">REKAPITULASI KEHADIRAN SISWA</h3>
        <p style="margin: 2px 0; font-size: 9pt;">
            Kelas: <strong>{{ $kelas }}</strong> | Periode: <strong>{{ $namaBulan }} {{ $tahun }}</strong>
        </p>
    </div>

    {{-- 3. TABEL DATA --}}
    <table class="data">
        <thead>
            <tr>
                <th rowspan="2" class="col-no">No</th>
                <th rowspan="2" class="col-nama" style="text-align: center;">Nama Siswa</th>
                {{-- Header Tanggal --}}
                <th colspan="{{ $daysInMonth }}">Tanggal</th>
                {{-- Header Total --}}
                <th colspan="4">Total</th>
            </tr>
            <tr>
                @for($d = 1; $d <= $daysInMonth; $d++)
                    <th class="col-tgl">{{ $d }}</th>
                @endfor
                <th width="18" class="bg-hijau">H</th>
                <th width="18" class="bg-kuning">S</th>
                <th width="18" class="bg-kuning">I</th>
                <th width="18" class="bg-merah">A</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $s)
                @php
                    $h = 0; $s_count = 0; $i = 0; $a = 0;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="col-nama">{{ strtoupper($s->nama) }}</td>

                    {{-- LOOP TANGGAL --}}
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $status = $attendanceData[$s->id][$d] ?? '-';
                            $bgClass = '';
                            $kode = '';

                            if ($status == 'Hadir' || $status == 'Terlambat') { 
                                $bgClass = 'bg-hijau'; 
                                $kode = ($status == 'Terlambat') ? 'T' : '•';
                                $h++; 
                            } elseif ($status == 'Sakit') { 
                                $bgClass = 'bg-kuning'; $kode = 'S'; $s_count++; 
                            } elseif ($status == 'Izin') { 
                                $bgClass = 'bg-kuning'; $kode = 'I'; $i++; 
                            } elseif ($status == 'Alpha') { 
                                $bgClass = 'bg-merah'; $kode = 'A'; $a++; 
                            }
                        @endphp

                        <td class="{{ $bgClass }}">{{ $kode }}</td>
                    @endfor

                    {{-- TOTAL --}}
                    <td class="bg-hijau">{{ $h }}</td>
                    <td class="bg-kuning">{{ $s_count }}</td>
                    <td class="bg-kuning">{{ $i }}</td>
                    <td class="bg-merah">{{ $a }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- 4. FOOTER --}}
    <div class="footer-info">
        <table style="width: 100%; border: none;">
            <tr>
                {{-- Legend --}}
                <td style="width: 60%; vertical-align: top; text-align: left;">
                    <strong>Keterangan:</strong><br>
                    <div style="margin-top: 3px; font-size: 9px;">
                        <span class="legend-box bg-hijau"></span> Hadir/Terlambat &nbsp;
                        <span class="legend-box bg-kuning"></span> Sakit/Izin &nbsp;
                        <span class="legend-box bg-merah"></span> Alpha
                    </div>
                </td>
                
                {{-- TTD --}}
                <td style="width: 40%;">
                    <table class="ttd-table">
                        <tr><td>Samarinda, {{ date('d F Y') }}</td></tr>
                        <tr><td>Wali Kelas,</td></tr>
                        <tr><td style="height: 50px;"></td></tr> {{-- Spasi TTD dikurangi dikit --}}
                        <tr><td><strong>( .................................... )</strong></td></tr>
                        <tr><td style="font-size: 8pt;">NIP. ...........................</td></tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>