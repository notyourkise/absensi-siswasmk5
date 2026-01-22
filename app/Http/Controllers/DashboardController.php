<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // --- BAGIAN 1: STATISTIK HARIAN (KARTU ATAS) ---
        $totalSiswa = Student::count();
        $hadirHariIni = Attendance::whereDate('tanggal', Carbon::today())->count();
        $telatHariIni = Attendance::whereDate('tanggal', Carbon::today())
                        ->whereTime('jam_masuk', '>', '07:15:00')
                        ->count();
        $alphaHariIni = $totalSiswa - $hadirHariIni;

        // --- BAGIAN 2: GRAFIK TREN 7 HARI (GARIS) ---
        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = Carbon::today()->subDays($i);
            $chartLabels[] = $tanggal->format('d M');
            $chartData[] = Attendance::whereDate('tanggal', $tanggal)->count();
        }

        // --- BAGIAN 3: STATISTIK BULANAN (FILTER) ---
        
        // Ambil input bulan & tahun dari request, kalau kosong pakai bulan/tahun sekarang
        $filterBulan = $request->input('bulan', Carbon::now()->month); 
        $filterTahun = $request->input('tahun', Carbon::now()->year);

        // Hitung Terlambat Bulan Ini
        $rekapTelat = Attendance::whereYear('tanggal', $filterTahun)
                        ->whereMonth('tanggal', $filterBulan)
                        ->whereTime('jam_masuk', '>', '07:15:00')
                        ->count();

        // Hitung Tepat Waktu Bulan Ini
        $rekapTepat = Attendance::whereYear('tanggal', $filterTahun)
                        ->whereMonth('tanggal', $filterBulan)
                        ->whereTime('jam_masuk', '<=', '07:15:00')
                        ->count();

        // Kirim data bulan untuk dropdown
        $bulanOptions = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return view('dashboard', compact(
            'totalSiswa', 'hadirHariIni', 'telatHariIni', 'alphaHariIni', // Data Harian
            'chartLabels', 'chartData', // Data Tren
            'rekapTelat', 'rekapTepat', // Data Bulanan (Baru)
            'filterBulan', 'filterTahun', 'bulanOptions' // Data Filter
        ));
    }
}