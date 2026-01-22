<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /**
    * 1. Ambil Data dari Database
    */
    public function collection()
    {
        // Pastikan ambil data terbaru
        return Attendance::with('student')->latest('tanggal')->get();
    }

    /**
    * 2. Bikin Judul Header
    */
    public function headings(): array
    {
        return [
            'Tanggal',
            'NISN',
            'Nama Siswa',
            'Kelas',
            'Jam Masuk',
            'Jam Pulang',
            'Status',
        ];
    }

    /**
    * 3. Mapping Data (LOGIKA BARU DISINI)
    */
   public function map($attendance): array
    {
        $jamMasuk = $attendance->jam_masuk;
        $jamKeluar = $attendance->jam_keluar;
        $status = '';

        // Setting Waktu
        $batasMasukAkhir = Carbon::parse($attendance->tanggal)->setTime(7, 15, 0);
        $batasPulangAwal = Carbon::parse($attendance->tanggal)->setTime(15, 30, 0);
        $batasPulangAkhir = Carbon::parse($attendance->tanggal)->setTime(16, 30, 0);
        $sekarang = Carbon::now();

        // 1. Cek Non-Hadir
        if (in_array($attendance->status_masuk, ['Sakit', 'Izin', 'Alpha'])) {
            $status = $attendance->status_masuk;
            $jamMasuk = '-';
        } 
        // 2. Logika Hadir
        else {
            if ($attendance->jam_masuk) {
                $waktuMasuk = Carbon::parse($attendance->jam_masuk);
                
                if ($jamKeluar) {
                    $waktuKeluar = Carbon::parse($jamKeluar);
                    if ($waktuKeluar->lt($batasPulangAwal)) {
                        $status = 'Pulang Cepat';
                    } elseif ($waktuMasuk->gt($batasMasukAkhir)) {
                        $status = 'Terlambat Masuk';
                    } else {
                        $status = 'Hadir Tepat Waktu';
                    }
                } else {
                    // Belum absen pulang
                    $isHariBerlalu = Carbon::parse($attendance->tanggal)->lessThan(Carbon::today());
                    $sudahLewatJamPulang = $sekarang->gt($batasPulangAkhir);

                    if ($isHariBerlalu || $sudahLewatJamPulang) {
                        $status = 'BOLOS (Tanpa Absen Pulang)'; 
                    } else {
                        $status = 'Belum Pulang';
                    }
                }
            } else {
                $status = 'Data Error'; 
                $jamMasuk = '-';
            }
        }

        return [
            $attendance->tanggal,
            $attendance->student->nisn,
            $attendance->student->nama,
            $attendance->student->kelas,
            $jamMasuk,
            $jamKeluar ?? '-',
            $status,
        ];
    }

    /**
    * 4. Styling Header Bold
    */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}