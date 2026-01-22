<!DOCTYPE html>
<html>
<head>
    <title>Rekap Absensi - {{ $kelas }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        
        /* HEADER SURAT */
        .header { text-align: center; margin-bottom: 15px; }
        .header h2 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 11px; }

        /* TABEL UTAMA */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 3px 1px; text-align: center; vertical-align: middle; }
        
        /* LEBAR KOLOM */
        th.no { width: 20px; }
        th.nama { width: 150px; text-align: left; padding-left: 5px; }
        th.tgl { width: 18px; font-size: 9px; } /* Kolom Tanggal Kecil */
        th.rekap { width: 25px; background-color: #f0f0f0; }

        /* WARNA STATUS */
        .h { background-color: #d1fae5; color: green; font-weight: bold; } /* Hijau Muda */
        .t { background-color: #fef3c7; color: orange; font-weight: bold; } /* Kuning Muda */
        .s { color: blue; font-weight: bold; }
        .i { color: purple; font-weight: bold; }
        .a { background-color: #fee2e2; color: red; font-weight: bold; } /* Merah Muda */
        .libur { background-color: #ddd; } /* Warna abu untuk hari minggu (opsional logicnya nanti) */

        .footer { margin-top: 20px; width: 100%; }
        .ttd-box { float: right; width: 200px; text-align: center; }
    </style>
</head>
<body>

    <div class="header">
        <h2>REKAPITULASI KEHADIRAN SISWA</h2>
        <p>Kelas: <strong>{{ $kelas }}</strong> | Periode: <strong>{{ $namaBulan }} {{ $tahun }}</strong></p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" class="no">No</th>
                <th rowspan="2" class="nama">Nama Siswa</th>
                {{-- LOOPING TANGGAL 1-31 --}}
                <th colspan="{{ $daysInMonth }}">Tanggal</th>
                {{-- KOLOM REKAP TOTAL --}}
                <th colspan="4">Total</th>
            </tr>
            <tr>
                {{-- SUB HEADER TANGGAL --}}
                @for($d = 1; $d <= $daysInMonth; $d++)
                    <th class="tgl">{{ $d }}</th>
                @endfor
                {{-- SUB HEADER TOTAL --}}
                <th class="rekap" title="Hadir">H</th>
                <th class="rekap" title="Sakit">S</th>
                <th class="rekap" title="Izin">I</th>
                <th class="rekap" title="Alpha">A</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $s)
                @php
                    // Inisialisasi Counter Per Siswa
                    $h = 0; $s_count = 0; $i = 0; $a = 0;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="text-align: left; padding-left: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">
                        {{ strtoupper($s->nama) }}
                    </td>

                    {{-- LOOPING ISI TANGGAL --}}
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $status = $attendanceData[$s->id][$d] ?? '-';
                            $kode = '';
                            $class = '';

                            // Mapping Status Database ke Kode Singkat
                            if ($status == 'Hadir')      { $kode = '•'; $class = 'h'; $h++; } // • Titik Hadir
                            elseif ($status == 'Terlambat') { $kode = 'T'; $class = 't'; $h++; } // T Terlambat dihitung Hadir
                            elseif ($status == 'Sakit')  { $kode = 'S'; $class = 's'; $s_count++; }
                            elseif ($status == 'Izin')   { $kode = 'I'; $class = 'i'; $i++; }
                            elseif ($status == 'Alpha')  { $kode = 'A'; $class = 'a'; $a++; }
                        @endphp

                        <td class="{{ $class }}">{{ $kode }}</td>
                    @endfor

                    {{-- KOLOM TOTAL --}}
                    <th>{{ $h }}</th>
                    <th>{{ $s_count }}</th>
                    <th>{{ $i }}</th>
                    <th>{{ $a }}</th>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="ttd-box">
            <p>Samarinda, {{ date('d F Y') }}</p>
            <p>Wali Kelas,</p>
            <br><br><br>
            <p>_______________________</p>
        </div>
    </div>

</body>
</html>