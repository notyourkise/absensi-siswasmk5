<!DOCTYPE html>
<html>
<head>
    <title>Laporan Absensi - {{ $student->nama }}</title>
    <style>
        /* SETUP HALAMAN */
        body { font-family: sans-serif; font-size: 12px; }
        
        /* HEADER / KOP SURAT */
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid black; padding-bottom: 10px; position: relative; }
        .logo { width: 70px; height: auto; position: absolute; top: 0; left: 0; }
        .logo-right { width: 70px; height: auto; position: absolute; top: 0; right: 0; }
        
        h2, h3, p { margin: 0; }
        .title { font-size: 16px; font-weight: bold; text-transform: uppercase; margin-top: 5px; }
        .subtitle { font-size: 11px; margin-top: 5px; }

        /* BIODATA */
        .biodata { margin-bottom: 20px; width: 100%; font-size: 13px; }
        .biodata td { padding: 4px; vertical-align: top; }

        /* TABEL DATA */
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data th, table.data td { border: 1px solid black; padding: 8px; text-align: center; vertical-align: middle; }
        table.data th { background-color: #E5E7EB; font-weight: bold; text-transform: uppercase; font-size: 11px; }
        
        /* WARNA STATUS */
        .status-hadir { color: #15803d; font-weight: bold; } /* Hijau Tua */
        .status-terlambat { color: #c2410c; font-weight: bold; } /* Orange Tua */
        .status-alpha { color: #b91c1c; font-weight: bold; } /* Merah Tua */

        /* SUMMARY BOX */
        .summary-box { margin-top: 30px; border: 1px solid #000; padding: 15px; width: 45%; }
        
        /* FOOTER TTD */
        .footer { margin-top: 50px; width: 100%; }
        .ttd-box { float: right; width: 250px; text-align: center; }
    </style>
</head>
<body>

    {{-- 1. KOP SURAT --}}
    <div class="header">
        <img src="{{ public_path('logo/kaltim.webp') }}" class="logo">
        <img src="{{ public_path('logo/sekolah.png') }}" class="logo-right">
        
        <h3>PEMERINTAH PROVINSI KALIMANTAN TIMUR</h3>
        <h3>DINAS PENDIDIKAN DAN KEBUDAYAAN</h3>
        <h2 class="title">SMK NEGERI 5 SAMARINDA</h2>
        <p class="subtitle">Jl. KH. Wahid Hasyim I, Sempaja Selatan, Samarinda Utara</p>
    </div>

    <center>
        <h3 style="text-decoration: underline; margin-bottom: 5px;">LAPORAN KEHADIRAN SISWA</h3>
        <p>Periode: <strong>{{ $namaBulan }} {{ $tahun }}</strong></p>
    </center>
    <br>

    {{-- 2. BIODATA SISWA --}}
    <table class="biodata">
        <tr>
            <td width="120">Nama Siswa</td>
            <td width="10">:</td>
            <td><strong>{{ strtoupper($student->nama) }}</strong></td>
            <td width="100">Kelas</td>
            <td width="10">:</td>
            <td>{{ $student->kelas }}</td>
        </tr>
        <tr>
            <td>NISN</td>
            <td>:</td>
            <td>{{ $student->nisn }}</td>
            <td>Dicetak Tgl</td>
            <td>:</td>
            <td>{{ date('d-m-Y') }}</td>
        </tr>
    </table>

    {{-- 3. TABEL ABSENSI UTAMA --}}
    <table class="data">
        <thead>
            <tr>
                <th width="30">No</th>
                <th>Tanggal</th>
                <th width="80">Jam Masuk</th>
                <th width="80">Jam Pulang</th>
                <th width="100">Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                
                {{-- Tanggal --}}
                <td style="text-align: left; padding-left: 15px;">
                    {{ \Carbon\Carbon::parse($row->created_at)->translatedFormat('l, d F Y') }}
                </td>

                {{-- Jam Masuk --}}
                <td>
                    {{ \Carbon\Carbon::parse($row->jam_masuk)->format('H:i') }}
                </td>

                {{-- Jam Pulang --}}
                <td>
                    {{ $row->jam_keluar ? \Carbon\Carbon::parse($row->jam_keluar)->format('H:i') : '-' }}
                </td>

                {{-- Status (Dengan Warna) --}}
                <td>
                    @if($row->status_masuk == 'Hadir')
                        <span class="status-hadir">Hadir</span>
                    @elseif($row->status_masuk == 'Terlambat')
                        <span class="status-terlambat">Terlambat</span>
                    @elseif($row->status_masuk == 'Sakit')
                        <span style="color: blue; font-weight: bold;">Sakit</span>
                    @elseif($row->status_masuk == 'Izin')
                        <span style="color: purple; font-weight: bold;">Izin</span>
                    @else
                        <span class="status-alpha">Alpha</span>
                    @endif
                </td>

                {{-- KETERANGAN (LOGIKA BARU DARI CONTROLLER) --}}
                <td style="font-size: 11px;">
                    {{-- 
                        Ini akan menampilkan: 
                        - "10 Menit" 
                        - "1 Jam 5 Menit" 
                        - "-" (jika tepat waktu)
                        Sesuai hasil hitungan Controller.
                    --}}
                    {{ $row->keterangan_telat }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="padding: 20px;">
                    <em>Tidak ada data absensi pada periode ini.</em>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- 4. RINGKASAN --}}
    <div class="summary-box">
        <strong>Ringkasan Kehadiran:</strong>
        <table width="100%" style="margin-top: 10px; font-size: 12px; border: none;">
            <tr>
                <td style="border: none; width: 150px;">Hadir (Tepat Waktu)</td>
                <td style="border: none;">: {{ $summary['hadir'] }} hari</td>
            </tr>
            <tr>
                <td style="border: none;">Terlambat</td>
                <td style="border: none;">: {{ $summary['terlambat'] }} hari</td>
            </tr>
            <tr>
                <td style="border: none;">Sakit / Izin</td>
                <td style="border: none;">: {{ $summary['sakit'] + $summary['izin'] }} hari</td>
            </tr>
            <tr>
                <td style="border: none;">Alpha</td>
                <td style="border: none;">: {{ $summary['alpha'] }} hari</td>
            </tr>
        </table>
    </div>

    {{-- 5. TANDA TANGAN --}}
    <div class="footer">
        <div class="ttd-box">
            <p>Samarinda, {{ date('d F Y') }}</p>
            <p>Wali Kelas / Guru Piket,</p>
            <br><br><br><br>
            <p style="border-bottom: 1px solid black; display: inline-block; width: 200px;"></p>
        </div>
    </div>

</body>
</html>