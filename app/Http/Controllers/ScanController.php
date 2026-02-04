<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ScanController extends Controller
{
    /**
     * Menampilkan Halaman Scan
     */
    public function index()
    {
        // Ambil 5 data scan terakhir hari ini
        $query = Attendance::with('student')
                        ->whereDate('tanggal', Carbon::today());
        
        // ===== DATA ISOLATION: Wali Kelas hanya melihat scan siswa di kelasnya =====
        if (auth()->user()->role === 'wali_kelas') {
            $query->whereHas('student', function($q) {
                $q->where('kelas', auth()->user()->kelas);
            });
        }
        
        $latest_scans = $query->latest('updated_at') 
                        ->take(5)
                        ->get();

        return view('scan.index', compact('latest_scans'));
    }

    /**
     * Proses Logic Scan
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'nisn' => 'required|numeric',
        ]);

        // 2. Cari Siswa
        $student = Student::where('nisn', $request->nisn)->first();

        if (!$student) {
            return redirect()->route('scan.index')->with('error', 'NISN Tidak Dikenal!');
        }

        // 3. Cek Absensi Hari Ini
        $attendance = Attendance::where('student_id', $student->id)
                                ->whereDate('tanggal', Carbon::today()) // Mengacu kolom 'tanggal'
                                ->first();

        // 4. Variabel Waktu
        $jamSekarang = Carbon::now();
        
        // --- ATURAN WAKTU SEKOLAH ---
        // Batas Terlambat Masuk
        $batasTerlambat = Carbon::today()->setTime(7, 15, 0); 
        
        // ===== RENTANG WAKTU PULANG: TESTING 12:04 - 12:06 =====
        $jamBukaPulang  = Carbon::today()->setTime(12, 4, 0); 
        $jamTutupPulang = Carbon::today()->setTime(12, 6, 0); 


        // ==========================================
        // SKENARIO 1: ABSEN MASUK (Belum ada data)
        // ==========================================
        if (!$attendance) {
            
            // Cek status terlambat
            if ($jamSekarang->greaterThan($batasTerlambat)) {
                $status = 'Terlambat'; 
                $pesan  = 'Absen Masuk (TERLAMBAT)';
                $warna  = 'orange'; 
            } else {
                $status = 'Hadir';
                $pesan  = 'Absen Masuk (Tepat Waktu)';
                $warna  = 'green';
            }

            Attendance::create([
                'student_id'   => $student->id,
                'tanggal'      => Carbon::today(),
                'jam_masuk'    => $jamSekarang,
                'status_masuk' => $status, //
            ]);

            return redirect()->route('scan.index')
                ->with('success', $pesan)
                ->with('student_data', $student)
                ->with('status_color', $warna);
        }

        // ==========================================
        // SKENARIO 2: ABSEN PULANG (Sudah ada data Masuk)
        // ==========================================
        else {
            
            // A. VALIDASI SUDAH PULANG?
            if ($attendance->jam_keluar) {
                return redirect()->route('scan.index')->with('error', 'Anda sudah absen pulang hari ini!');
            }

            // B. VALIDASI JAM PULANG (RENTANG TESTING: 12:04 - 12:06)
            // Jika scan sebelum jam testing
            if ($jamSekarang->lessThan($jamBukaPulang)) {
                return redirect()->route('scan.index')
                    ->with('error', 'Belum waktunya pulang! (Dibuka 12:04 untuk testing). Jika Sakit atau Izin dispensasi, lapor Admin untuk Input Manual.');
            }

            // Jika scan setelah jam 12:06 -> DITOLAK (sudah otomatis Alpha jam 12:07)
            if ($jamSekarang->greaterThan($jamTutupPulang)) {
                 return redirect()->route('scan.index')
                     ->with('error', 'Waktu absen pulang sudah lewat! (12:04-12:06). Status Anda akan otomatis Alpha jam 12:07.');
            }

            // C. LOLOS VALIDASI (15:30 - 16:30) -> TEPAT WAKTU
            $attendance->update([
                'jam_keluar' => $jamSekarang,
                'status_pulang' => 'Tepat Waktu'
            ]);

            return redirect()->route('scan.index')
                ->with('success', 'Hati-hati di jalan! (Absen Pulang Tepat Waktu)')
                ->with('student_data', $student)
                ->with('status_color', 'blue');
        }
    }
}