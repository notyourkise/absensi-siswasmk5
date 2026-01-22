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
        $latest_scans = Attendance::with('student')
                        ->whereDate('tanggal', Carbon::today()) // Sesuai kolom 'tanggal' di tabel Anda
                        ->latest('updated_at') 
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
        
        // Jam Mulai Boleh Absen Pulang (Scan Barcode)
        // Sebelum jam ini, scan akan DITOLAK.
        $jamBukaPulang  = Carbon::today()->setTime(15, 30, 0); 
        
        // (Opsional) Batas Akhir Absen Pulang
        $jamTutupPulang = Carbon::today()->setTime(17, 30, 0); 


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

            // B. VALIDASI JAM PULANG (SOLUSI MASALAH NO 2 & 3 ANDA)
            // Jika jam sekarang BELUM jam 15:30, tolak scan ini.
            if ($jamSekarang->lessThan($jamBukaPulang)) {
                return redirect()->route('scan.index')
                    ->with('error', 'Belum waktunya pulang! (Dibuka 15:30). Jika Sakit/Izin, lapor Admin untuk Input Manual.');
            }

            // Jika lewat jam tutup (misal jam 8 malam gaboleh scan lagi)
            if ($jamSekarang->greaterThan($jamTutupPulang)) {
                 return redirect()->route('scan.index')->with('error', 'Absen pulang sudah ditutup.');
            }

            // C. LOLOS VALIDASI -> UPDATE PULANG
            $attendance->update([
                'jam_keluar' => $jamSekarang
            ]);

            return redirect()->route('scan.index')
                ->with('success', 'Hati-hati di jalan! (Absen Pulang)')
                ->with('student_data', $student)
                ->with('status_color', 'blue');
        }
    }
}