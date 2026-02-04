<!DOCTYPE html>
<html>
<head>
    <title>Rekap Absensi - {{ $kelas }}</title>
    <style>
        /* --- 1. SETTING HALAMAN (MARGIN TIPIS UNTUK F4) --- */
        @page { 
            /* Margin: Atas 10px, Kanan 15px, Bawah 5px, Kiri 15px */
            /* Margin Bawah 5px sangat krusial agar footer tidak lompat halaman */
            margin: 10px 15px 5px 15px; 
        }

        /* --- 2. RESET & FONT --- */
        body { 
            font-family: 'Times New Roman', Times, serif; 
            font-size: 9pt; /* Ukuran standar diperkecil */
            margin: 0; 
            padding: 0; 
        }

        /* --- 3. KOP SURAT (DIPADATKAN) --- */
        .kop-surat { 
            width: 100%; 
            border-bottom: 2px double #000; 
            padding-bottom: 3px; 
            margin-bottom: 5px; /* Jarak ke judul dirapatkan */
        }
        .kop-logo { width: 50px; height: auto; } /* Logo diperkecil dikit */
        .kop-tengah { text-align: center; line-height: 1; }
        .kop-prov { font-size: 10pt; font-weight: bold; }
        .kop-dinas { font-size: 10pt; font-weight: bold; }
        .kop-nama { font-size: 12pt; font-weight: bold; margin: 2px 0; }
        .kop-alamat { font-size: 8pt; font-style: italic; }

        /* --- 4. TABEL DATA (SUPER COMPACT MODE) --- */
        table.data { 
            width: 100%; 
            border-collapse: collapse; 
            border: 1px solid #000; 
            font-size: 8pt; /* Font Tabel Diperkecil agar muat 37 baris */
        }
        
        table.data th, table.data td { 
            border: 1px solid #000; 
            padding: 1px 2px; /* PADDING TIPIS: Kunci utama hemat tempat vertikal */
            text-align: center; 
            vertical-align: middle; 
            line-height: 1; /* Hapus spasi antar baris teks */
            height: 11px; /* Paksa tinggi baris minimal */
        }

        table.data th { background-color: #E5E7EB; font-weight: bold; height: 14px; }
        
        /* Lebar Kolom */
        .col-no { width: 15px; }
        .col-nama { 
            text-align: left; 
            padding-left: 3px; 
            width: 140px; 
            white-space: nowrap; 
            overflow: hidden; 
            text-overflow: ellipsis; 
        }
        .col-tgl { width: 13px; font-size: 7pt; } /* Tanggal diperkecil */

        /* --- 5. WARNA STATUS --- */
        .bg-hijau { background-color: #86efac !important; }
        .bg-kuning { background-color: #fde047 !important; }
        .bg-merah { background-color: #fca5a5 !important; }
        .bg-biru { background-color: #93c5fd !important; } /* Warna biru untuk libur */
        
        /* --- 6. FOOTER (ANTI LOMPAT) --- */
        .footer-wrapper {
            width: 100%;
            margin-top: 5px;
            /* Mencegah footer terpotong ke halaman baru jika mepet */
            page-break-inside: avoid; 
        }
        
        .ttd-table { width: 100%; border: none; font-size: 9pt; }
        .ttd-table td { border: none; text-align: center; padding: 0; }
    </style>
</head>
<body>

    {{-- 1. KOP SURAT --}}
    <table class="kop-surat">
        <tr>
            <td width="10%" align="center"><img src="{{ public_path('logo/kaltim.webp') }}" class="kop-logo" alt="Logo"></td>
            <td width="80%" class="kop-tengah">
                <div class="kop-prov">PEMERINTAH PROVINSI KALIMANTAN TIMUR</div>
                <div class="kop-dinas">DINAS PENDIDIKAN DAN KEBUDAYAAN</div>
                <div class="kop-nama">SMK NEGERI 5 SAMARINDA</div>
                <div class="kop-alamat">Jalan KH. Wahid Hasyim Nomor 75 RT. 08 Kel. Sempaja Selatan, Samarinda</div>
            </td>
            <td width="10%" align="center"><img src="{{ public_path('logo/sekolah.png') }}" class="kop-logo" alt="Logo"></td>
        </tr>
    </table>

    {{-- 2. JUDUL LAPORAN --}}
    <div style="text-align: center; margin-bottom: 5px;">
        <h4 style="margin: 0; font-size: 11pt; text-transform: uppercase;">REKAPITULASI KEHADIRAN SISWA</h4>
        <div style="font-size: 9pt; margin-top: 2px;">
            Kelas: <strong>{{ $kelas }}</strong> | Periode: <strong>{{ $namaBulan }} {{ $tahun }}</strong>
        </div>
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
                <th width="15" class="bg-hijau">H</th>
                <th width="15" class="bg-kuning">S</th>
                <th width="15" class="bg-kuning">I</th>
                <th width="15" class="bg-merah">A</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $s)
                @php
                    $h = 0; $s_count = 0; $i = 0; $a = 0; $libur = 0;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="col-nama">{{ strtoupper($s->nama) }}</td>

                    {{-- LOOP TANGGAL --}}
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            // Cek apakah hari ini weekend (Sabtu/Minggu)
                            $tanggalIni = \Carbon\Carbon::create($tahun, date('m', strtotime($namaBulan)), $d);
                            $isWeekend = $tanggalIni->isWeekend();
                            
                            $status = $attendanceData[$s->id][$d] ?? '-';
                            $bgClass = '';
                            $kode = '';

                            // Prioritas: Cek status Libur dari database terlebih dahulu
                            if ($status == 'Libur') {
                                // Status Libur dari database (diset manual oleh admin)
                                $bgClass = 'bg-biru'; 
                                $kode = 'L';
                                $libur++;
                            } elseif ($isWeekend) {
                                // Hari Sabtu/Minggu otomatis = Libur (background biru)
                                $bgClass = 'bg-biru'; 
                                $kode = 'L';
                                $libur++;
                            } elseif ($status == 'Hadir' || $status == 'Terlambat') { 
                                $bgClass = 'bg-hijau'; 
                                $kode = ($status == 'Terlambat') ? 'T' : 'H';
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

    {{-- 4. FOOTER / TTD --}}
    <div class="footer-wrapper">
        <table style="width: 100%; border: none;">
            <tr>
                {{-- Keterangan (Kiri) --}}
                <td style="width: 60%; vertical-align: top; font-size: 8pt; text-align: left;">
                    <strong>Ket:</strong> 
                    <span style="background:#86efac; border:1px solid #000; width:8px; height:8px; display:inline-block;"></span> Hadir
                    <span style="background:#fde047; border:1px solid #000; width:8px; height:8px; display:inline-block;"></span> Sakit/Izin
                    <span style="background:#fca5a5; border:1px solid #000; width:8px; height:8px; display:inline-block;"></span> Alpha
                    <span style="background:#93c5fd; border:1px solid #000; width:8px; height:8px; display:inline-block;"></span> Libur
                </td>
                
                {{-- Tanda Tangan (Kanan) --}}
                <td style="width: 40%;">
                    <table class="ttd-table">
                        <tr><td>Samarinda, {{ date('d F Y') }}</td></tr>
                        <tr><td>Wali Kelas,</td></tr>
                        {{-- Jarak TTD diperpendek jadi 40px biar muat --}}
                        <tr><td style="height: 40px;"></td></tr> 
                        <tr><td><strong>( .................................... )</strong></td></tr>
                        <tr><td>NIP. ...........................</td></tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>