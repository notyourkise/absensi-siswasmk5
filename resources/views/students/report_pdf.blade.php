<!DOCTYPE html>
<html>
<head>
    <title>Laporan Absensi - {{ $student->nama }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid black; padding-bottom: 10px; }
        .logo { width: 60px; height: auto; position: absolute; top: 0; left: 0; }
        .logo-right { width: 60px; height: auto; position: absolute; top: 0; right: 0; }
        
        h2, h3, p { margin: 0; }
        .title { font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .subtitle { font-size: 10px; margin-top: 5px; }

        .biodata { margin-bottom: 15px; width: 100%; }
        .biodata td { padding: 3px; }

        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data th, table.data td { border: 1px solid black; padding: 6px; text-align: center; }
        table.data th { background-color: #f0f0f0; }
        
        .status-hadir { color: green; font-weight: bold; }
        .status-terlambat { color: orange; font-weight: bold; }
        .status-alpha { color: red; font-weight: bold; }

        .summary-box { margin-top: 20px; border: 1px solid #000; padding: 10px; width: 40%; }
        
        .footer { margin-top: 40px; text-align: right; margin-right: 30px; }
    </style>
</head>
<body>

    {{-- KOP SURAT --}}
    <div class="header">
        <img src="{{ public_path('logo/kaltim.webp') }}" class="logo">
        <img src="{{ public_path('logo/sekolah.png') }}" class="logo-right">
        
        <h3>PEMERINTAH PROVINSI KALIMANTAN TIMUR</h3>
        <h3>DINAS PENDIDIKAN DAN KEBUDAYAAN</h3>
        <h2 class="title">SMK NEGERI 5 SAMARINDA</h2>
        <p class="subtitle">Jl. KH. Wahid Hasyim I, Sempaja Selatan, Samarinda Utara</p>
    </div>

    <center>
        <h3>LAPORAN KEHADIRAN SISWA</h3>
        <p>Periode: {{ $namaBulan }} {{ $tahun }}</p>
    </center>
    <br>

    {{-- BIODATA SISWA --}}
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

    {{-- TABEL ABSENSI --}}
    <table class="data">
        <thead>
            <tr>
                <th width="30">No</th>
                <th>Tanggal</th>
                <th>Jam Masuk</th>
                <th>Jam Pulang</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                {{-- Format Tanggal Indonesia (Senin, 20 Jan 2026) --}}
                <td style="text-align: left; padding-left: 10px;">
                    {{ \Carbon\Carbon::parse($row->created_at)->translatedFormat('l, d F Y') }}
                </td>
                <td>{{ \Carbon\Carbon::parse($row->jam_masuk)->format('H:i') }}</td>
                <td>
                    {{ $row->jam_keluar ? \Carbon\Carbon::parse($row->jam_keluar)->format('H:i') : '-' }}
                </td>
                <td>
                    @if($row->status_masuk == 'Hadir')
                        <span class="status-hadir">Hadir</span>
                    @elseif($row->status_masuk == 'Terlambat')
                        <span class="status-terlambat">Terlambat</span>
                    @else
                        <span class="status-alpha">{{ $row->status_masuk }}</span>
                    @endif
                </td>
                <td>
                    {{-- Hitung Durasi Sekolah --}}
                    @if($row->jam_keluar)
                        {{ \Carbon\Carbon::parse($row->jam_masuk)->diffInHours(\Carbon\Carbon::parse($row->jam_keluar)) }} Jam
                    @else
                        -
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6">Tidak ada data absensi pada bulan ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- RINGKASAN / STATISTIK --}}
    <div class="summary-box">
        <strong>Ringkasan Kehadiran:</strong><br>
        <table width="100%" style="margin-top: 5px;">
            <tr>
                <td>✅ Hadir (Tepat Waktu)</td>
                <td>: {{ $summary['hadir'] }} hari</td>
            </tr>
            <tr>
                <td>⚠️ Terlambat</td>
                <td>: {{ $summary['terlambat'] }} hari</td>
            </tr>
            <tr>
                <td>🤒 Sakit / Izin</td>
                <td>: {{ $summary['sakit'] + $summary['izin'] }} hari</td>
            </tr>
            <tr>
                <td>❌ Alpha (Tanpa Ket)</td>
                <td>: {{ $summary['alpha'] }} hari</td>
            </tr>
        </table>
    </div>

    {{-- TANDA TANGAN --}}
    <div class="footer">
        <p>Samarinda, {{ date('d F Y') }}</p>
        <p>Wali Kelas / Guru Piket,</p>
        <br><br><br>
        <p>__________________________</p>
    </div>

</body>
</html>