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
        // ===== DATA ISOLATION: Wali Kelas hanya melihat siswa di kelasnya =====
        $userRole = auth()->user()->role;
        $userKelas = auth()->user()->kelas;

        // Query base untuk student count
        $studentQuery = Student::query();
        if ($userRole === 'wali_kelas') {
            $studentQuery->where('kelas', $userKelas);
        }

        // --- BAGIAN 1: STATISTIK HARIAN (KARTU ATAS) ---
        $totalSiswa = $studentQuery->count();
        
        // Query untuk attendance (dengan filter kelas jika wali kelas)
        $attendanceQuery = Attendance::whereDate('tanggal', Carbon::today());
        if ($userRole === 'wali_kelas') {
            $attendanceQuery->whereHas('student', function($q) use ($userKelas) {
                $q->where('kelas', $userKelas);
            });
        }
        $hadirHariIni = $attendanceQuery->count();
        
        // Query untuk telat
        $telatQuery = Attendance::whereDate('tanggal', Carbon::today())
                        ->whereTime('jam_masuk', '>', '07:15:00');
        if ($userRole === 'wali_kelas') {
            $telatQuery->whereHas('student', function($q) use ($userKelas) {
                $q->where('kelas', $userKelas);
            });
        }
        $telatHariIni = $telatQuery->count();
        
        $alphaHariIni = $totalSiswa - $hadirHariIni;

        // --- BAGIAN 2: GRAFIK TREN 7 HARI (GARIS) ---
        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = Carbon::today()->subDays($i);
            $chartLabels[] = $tanggal->format('d M');
            
            $chartAttendance = Attendance::whereDate('tanggal', $tanggal);
            if ($userRole === 'wali_kelas') {
                $chartAttendance->whereHas('student', function($q) use ($userKelas) {
                    $q->where('kelas', $userKelas);
                });
            }
            $chartData[] = $chartAttendance->count();
        }

        // --- BAGIAN 3: STATISTIK BULANAN (FILTER) ---
        
        // Ambil input bulan & tahun dari request, kalau kosong pakai bulan/tahun sekarang
        $filterBulan = $request->input('bulan', Carbon::now()->month); 
        $filterTahun = $request->input('tahun', Carbon::now()->year);

        // Hitung Terlambat Bulan Ini
        $rekapTelatQuery = Attendance::whereYear('tanggal', $filterTahun)
                        ->whereMonth('tanggal', $filterBulan)
                        ->whereTime('jam_masuk', '>', '07:15:00');
        if ($userRole === 'wali_kelas') {
            $rekapTelatQuery->whereHas('student', function($q) use ($userKelas) {
                $q->where('kelas', $userKelas);
            });
        }
        $rekapTelat = $rekapTelatQuery->count();

        // Hitung Tepat Waktu Bulan Ini
        $rekapTepatQuery = Attendance::whereYear('tanggal', $filterTahun)
                        ->whereMonth('tanggal', $filterBulan)
                        ->whereTime('jam_masuk', '<=', '07:15:00');
        if ($userRole === 'wali_kelas') {
            $rekapTepatQuery->whereHas('student', function($q) use ($userKelas) {
                $q->where('kelas', $userKelas);
            });
        }
        $rekapTepat = $rekapTepatQuery->count();

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