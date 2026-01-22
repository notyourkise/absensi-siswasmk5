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
        // Ambil semua data siswa untuk pilihan dropdown (urutkan nama biar mudah cari)
        $students = Student::orderBy('nama', 'asc')->get();
        return view('manual.create', compact('students'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:Hadir,Sakit,Izin,Alpha',
        ]);

        // Cek Data Absensi Hari Itu
        $attendance = Attendance::where('student_id', $request->student_id)
                        ->whereDate('tanggal', $request->tanggal)
                        ->first();

        // --- SKENARIO 1: INPUT STATUS SAKIT / IZIN / ALPHA ---
        if ($request->status != 'Hadir') {
            
            // A. KASUS BARU: SAKIT TENGAH HARI (Data Hadir sudah ada)
            if ($attendance) {
                // Kita update statusnya jadi Sakit/Izin, DAN catat jam pulangnya sekarang
                $attendance->update([
                    'status_masuk' => $request->status, // Ubah jadi Sakit/Izin
                    'jam_keluar' => Carbon::now(),      // Pulang saat ini juga
                ]);
                
                return redirect()->route('manual.create')->with('success', 'Status diperbarui: Siswa pulang awal karena ' . $request->status);
            }

            // B. KASUS LAMA: SAKIT DARI PAGI (Belum ada data)
            Attendance::create([
                'student_id' => $request->student_id,
                'tanggal' => $request->tanggal,
                'status_masuk' => $request->status,
                'jam_masuk' => null, // Tidak ada jam masuk
                'jam_keluar' => null,
            ]);
            
            return redirect()->route('manual.create')->with('success', 'Status ' . $request->status . ' berhasil disimpan.');
        }

        // --- SKENARIO 2: INPUT HADIR (Sama seperti sebelumnya) ---
        if ($request->status == 'Hadir') {
            
            if (!$attendance) {
                // Absen Masuk Manual
                Attendance::create([
                    'student_id' => $request->student_id,
                    'tanggal' => $request->tanggal,
                    'status_masuk' => 'Hadir',
                    'jam_masuk' => Carbon::now(),
                    'jam_keluar' => null,
                ]);
                return redirect()->route('manual.create')->with('success', 'Absen MASUK manual berhasil.');
            }

            // Absen Pulang Manual
            if ($attendance->jam_masuk && $attendance->jam_keluar == null) {
                $attendance->update([
                    'jam_keluar' => Carbon::now()
                ]);
                return redirect()->route('manual.create')->with('success', 'Absen PULANG manual berhasil.');
            }

            return back()->with('error', 'Siswa ini sudah absen Masuk & Pulang hari ini!');
        }
    }
}