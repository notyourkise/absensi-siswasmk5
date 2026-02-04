<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ManualAbsenController extends Controller
{
    // 1. Tampilkan Formulir
    public function create()
    {
        // ===== DATA ISOLATION: Wali Kelas hanya melihat siswa di kelasnya =====
        $query = Student::query();
        
        if (auth()->user()->role === 'wali_kelas') {
            $query->where('kelas', auth()->user()->kelas);
        }
        
        // Ambil data siswa untuk pilihan dropdown (urutkan nama biar mudah cari)
        $students = $query->orderBy('nama', 'asc')->get();
        
        return view('manual.create', compact('students'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'tanggal' => 'required|date|before_or_equal:today', // Validasi: Tidak boleh tanggal masa depan
            'status' => 'required|in:Hadir,Sakit,Izin,Alpha',
            'jam_masuk' => 'nullable|date_format:H:i', // Format 24 jam
            'jam_keluar' => 'nullable|date_format:H:i',
        ], [
            'tanggal.before_or_equal' => 'Tanggal tidak boleh di masa depan!',
            'jam_masuk.date_format' => 'Format jam masuk harus HH:MM (contoh: 07:00)',
            'jam_keluar.date_format' => 'Format jam keluar harus HH:MM (contoh: 15:30)',
        ]);

        // ===== PROTEKSI: Wali Kelas hanya bisa input absen siswa di kelasnya =====
        $student = Student::findOrFail($request->student_id);
        
        if (auth()->user()->role === 'wali_kelas') {
            if ($student->kelas !== auth()->user()->kelas) {
                return back()->with('error', 'Anda hanya bisa menginput absensi siswa di kelas Anda sendiri!');
            }
        }

        // Cek Data Absensi Hari Itu
        $attendance = Attendance::where('student_id', $request->student_id)
                        ->whereDate('tanggal', $request->tanggal)
                        ->first();

        // ===== LOGIKA JAM MASUK/KELUAR =====
        // Jika ada input manual jam, pakai itu. Kalau tidak, pakai jam sekarang.
        $jamMasuk = $request->filled('jam_masuk') 
            ? Carbon::parse($request->tanggal . ' ' . $request->jam_masuk)
            : Carbon::now();
            
        $jamKeluar = $request->filled('jam_keluar') 
            ? Carbon::parse($request->tanggal . ' ' . $request->jam_keluar)
            : Carbon::now();

        // --- SKENARIO 1: INPUT STATUS SAKIT / IZIN / ALPHA ---
        if ($request->status != 'Hadir') {
            
            // A. KASUS: Data Hadir sudah ada, update jadi Sakit/Izin
            if ($attendance) {
                $attendance->update([
                    'status_masuk' => $request->status,
                    'jam_keluar' => $jamKeluar,
                ]);
                
                return redirect()->route('manual.create')->with('success', 'Status diperbarui: ' . $request->status);
            }

            // B. KASUS: Belum ada data, buat baru
            Attendance::create([
                'student_id' => $request->student_id,
                'tanggal' => $request->tanggal,
                'status_masuk' => $request->status,
                'jam_masuk' => null, // Tidak ada jam masuk untuk Sakit/Izin/Alpha
                'jam_keluar' => null,
            ]);
            
            return redirect()->route('manual.create')->with('success', 'Status ' . $request->status . ' berhasil disimpan untuk tanggal ' . Carbon::parse($request->tanggal)->format('d M Y'));
        }

        // --- SKENARIO 2: INPUT HADIR ---
        if ($request->status == 'Hadir') {
            
            if (!$attendance) {
                // ===== VALIDASI TERLAMBAT: Cek apakah jam masuk > 07:15 =====
                $batasTerlambat = Carbon::parse($request->tanggal . ' 07:15:00');
                $statusMasuk = 'Hadir';
                
                // Jika jam masuk lebih dari 07:15, otomatis jadi Terlambat
                if ($jamMasuk && $jamMasuk->greaterThan($batasTerlambat)) {
                    $statusMasuk = 'Terlambat';
                }
                
                // Absen Masuk Manual (bisa untuk hari ini atau historis)
                Attendance::create([
                    'student_id' => $request->student_id,
                    'tanggal' => $request->tanggal,
                    'status_masuk' => $statusMasuk,
                    'jam_masuk' => $jamMasuk,
                    'jam_keluar' => $request->filled('jam_keluar') ? $jamKeluar : null,
                ]);
                
                $statusText = $statusMasuk === 'Terlambat' ? 'Terlambat (absen setelah 07:15)' : 'Hadir';
                $message = 'Absen berhasil disimpan sebagai ' . $statusText . ' untuk tanggal ' . Carbon::parse($request->tanggal)->format('d M Y');
                return redirect()->route('manual.create')->with('success', $message);
            }

            // Absen Pulang Manual (update jam keluar)
            if ($attendance->jam_masuk && $attendance->jam_keluar == null) {
                $attendance->update([
                    'jam_keluar' => $jamKeluar
                ]);
                return redirect()->route('manual.create')->with('success', 'Jam keluar berhasil diperbarui.');
            }

            return back()->with('error', 'Siswa ini sudah absen masuk dan pulang pada tanggal tersebut!');
        }
    }
}