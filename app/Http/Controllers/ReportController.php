<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        // ===== DATA ISOLATION: Wali Kelas hanya melihat kelasnya =====
        if (auth()->user()->role === 'wali_kelas') {
            $classes = collect([auth()->user()->kelas]);
        } else {
            // Ambil daftar kelas
            $classes = Student::select('kelas')->distinct()->orderBy('kelas', 'asc')->pluck('kelas');
        }
        
        // PERBAIKAN DI SINI: Ubah 'reports.index' jadi 'report.index'
        return view('report.index', compact('classes'));
    }

    public function downloadClassReport(Request $request)
    {
        $request->validate([
            'kelas' => 'required',
            'bulan' => 'required|numeric|min:1|max:12',
            'tahun' => 'required|numeric|min:2020',
        ]);

        $kelas = $request->kelas;
        $bulan = (int) $request->bulan;
        $tahun = $request->tahun;

        // ===== DATA ISOLATION: Wali Kelas hanya bisa download kelasnya =====
        if (auth()->user()->role === 'wali_kelas') {
            if ($kelas !== auth()->user()->kelas) {
                return redirect()->back()->with('error', 'Anda hanya bisa mengunduh laporan kelas Anda sendiri!');
            }
        }

        // Ambil Siswa
        $students = Student::where('kelas', $kelas)->orderBy('nama', 'asc')->get();

        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada siswa di kelas ini.');
        }

        // Ambil Absensi - PERBAIKAN: Gunakan kolom 'tanggal' bukan 'created_at'
        $attendances = Attendance::whereIn('student_id', $students->pluck('id'))
                        ->whereYear('tanggal', $tahun)
                        ->whereMonth('tanggal', $bulan)
                        ->get();

        // Mapping Data
        $attendanceData = [];
        foreach ($attendances as $row) {
            $tgl = (int) Carbon::parse($row->tanggal)->format('d'); // Gunakan 'tanggal' bukan 'created_at'
            $attendanceData[$row->student_id][$tgl] = $row->status_masuk; 
        }

        $daysInMonth = Carbon::createFromDate($tahun, $bulan)->daysInMonth;
        $namaBulan   = Carbon::create()->month($bulan)->translatedFormat('F');

        // PERBAIKAN DI SINI: Ubah 'reports.class_pdf' jadi 'report.class_pdf'
        // Pastikan file class_pdf.blade.php ada di dalam folder resources/views/report/
        $pdf = Pdf::loadView('report.class_pdf', compact(
            'students', 
            'attendanceData', 
            'daysInMonth', 
            'kelas', 
            'namaBulan', 
            'tahun'
        ));
        $customPaper = [0, 0, 609.4488, 935.433];
        $pdf->setPaper($customPaper, 'landscape');

        return $pdf->stream('Rekap_Absen_Kelas_'.$kelas.'_'.$namaBulan.'.pdf');
    }

    public function daily(Request $request)
    {
        // 1. Ambil tanggal dari input, default hari ini
        $date = $request->date ?? Carbon::today()->toDateString();
        
        // ===== CEK HARI LIBUR (Sabtu/Minggu) =====
        $isWeekend = Carbon::parse($date)->isWeekend();
        
        // 2. Ambil daftar semua kelas yang ada di database untuk pilihan dropdown
        // ===== DATA ISOLATION: Wali Kelas hanya melihat kelasnya =====
        if (auth()->user()->role === 'wali_kelas') {
            $classes = collect([auth()->user()->kelas]);
        } else {
            $classes = Student::select('kelas')->distinct()->orderBy('kelas', 'asc')->pluck('kelas');
        }

        // 3. Bangun Query Siswa
        $studentsQuery = Student::orderBy('kelas', 'asc')->orderBy('nama', 'asc');
        
        // ===== DATA ISOLATION: Wali Kelas otomatis terfilter =====
        if (auth()->user()->role === 'wali_kelas') {
            $studentsQuery->where('kelas', auth()->user()->kelas);
        } elseif ($request->filled('kelas')) {
            // Tambahkan filter jika kelas dipilih (untuk Admin/Petugas)
            $studentsQuery->where('kelas', $request->kelas);
        }
        
        $students = $studentsQuery->get();
        
        // 4. Ambil data absensi pada tanggal tersebut - PERBAIKAN: Gunakan 'tanggal' bukan 'created_at'
        $attendances = Attendance::whereDate('tanggal', $date)
                        ->get()
                        ->keyBy('student_id');

        return view('report.daily', compact('students', 'attendances', 'date', 'classes', 'isWeekend'));
    }
    public function updateAttendance(Request $request, $id)
    {
        // Validasi: Hanya admin yang bisa edit
        if (auth()->user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengubah data absensi!');
        }

        $request->validate([
            'status_masuk' => 'required|in:Hadir,Terlambat,Sakit,Izin,Alpha,Libur',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_keluar' => 'nullable|date_format:H:i',
        ], [
            'jam_masuk.date_format' => 'Format jam masuk harus HH:MM (contoh: 07:00)',
            'jam_keluar.date_format' => 'Format jam keluar harus HH:MM (contoh: 15:30)',
        ]);

        $attendance = Attendance::findOrFail($id);

        // Ambil tanggal dari data attendance yang ada
        $tanggal = $attendance->tanggal;

        // Logika jam masuk/keluar
        if ($request->status_masuk === 'Hadir' || $request->status_masuk === 'Terlambat') {
            // Jika status Hadir/Terlambat, pakai jam yang diinput atau pertahankan jam lama
            $jamMasuk = $request->filled('jam_masuk') 
                ? Carbon::parse($tanggal . ' ' . $request->jam_masuk)
                : $attendance->jam_masuk; // Pertahankan jam lama jika tidak diubah
                
            $jamKeluar = $request->filled('jam_keluar') 
                ? Carbon::parse($tanggal . ' ' . $request->jam_keluar)
                : $attendance->jam_keluar; // Pertahankan jam lama jika tidak diubah
            
            // ===== AUTO-VALIDASI TERLAMBAT: Jika jam masuk > 07:15 dan status masih Hadir =====
            $batasTerlambat = Carbon::parse($tanggal . ' 07:15:00');
            if ($jamMasuk && $jamMasuk->greaterThan($batasTerlambat) && $request->status_masuk === 'Hadir') {
                $request->merge(['status_masuk' => 'Terlambat']);
            }
        } elseif ($request->status_masuk === 'Sakit' || $request->status_masuk === 'Izin') {
            // ===== KASUS SAKIT/IZIN =====
            // 1. Siswa sempat masuk lalu pulang awal (ada jam_masuk existing atau input baru)
            // 2. Siswa sakit/izin seharian (tidak ada jam sama sekali)
            
            if ($request->filled('jam_masuk') || $attendance->jam_masuk) {
                // Ada jam masuk (dari input baru atau data lama) -> catat jam masuk dan keluar
                $jamMasuk = $request->filled('jam_masuk')
                    ? Carbon::parse($tanggal . ' ' . $request->jam_masuk)
                    : $attendance->jam_masuk; // Pertahankan jam lama jika tidak diubah
                    
                $jamKeluar = $request->filled('jam_keluar') 
                    ? Carbon::parse($tanggal . ' ' . $request->jam_keluar)
                    : $attendance->jam_keluar; // Pertahankan jam lama jika tidak diubah
            } else {
                // Tidak ada jam masuk sama sekali -> sakit/izin seharian
                $jamMasuk = null;
                $jamKeluar = null;
            }
        } else {
            // Jika Alpha atau Libur, set jam jadi null
            $jamMasuk = null;
            $jamKeluar = null;
        }

        // Update data
        $attendance->update([
            'status_masuk' => $request->status_masuk,
            'jam_masuk' => $jamMasuk,
            'jam_keluar' => $jamKeluar,
        ]);

        // Log aktivitas
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'description' => 'Mengubah data absensi siswa ' . $attendance->student->nama . ' menjadi ' . $request->status_masuk . ' pada tanggal ' . Carbon::parse($tanggal)->format('d M Y'),
        ]);

        return redirect()->back()->with('success', 'Data absensi berhasil diperbarui!');
    }

    public function createAttendance(Request $request)
    {
        // Validasi: Hanya admin yang bisa edit
        if (auth()->user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk membuat data absensi!');
        }

        $request->validate([
            'student_id' => 'required|exists:students,id',
            'tanggal' => 'required|date',
            'status_masuk' => 'required|in:Hadir,Terlambat,Sakit,Izin,Alpha,Libur',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_keluar' => 'nullable|date_format:H:i',
        ], [
            'jam_masuk.date_format' => 'Format jam masuk harus HH:MM (contoh: 07:00)',
            'jam_keluar.date_format' => 'Format jam keluar harus HH:MM (contoh: 15:30)',
        ]);

        // Cek apakah sudah ada data untuk siswa di tanggal tersebut
        $existing = Attendance::where('student_id', $request->student_id)
            ->whereDate('tanggal', $request->tanggal)
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Data absensi untuk siswa ini pada tanggal tersebut sudah ada!');
        }

        $student = Student::findOrFail($request->student_id);
        $tanggal = $request->tanggal;

        // Logika jam masuk/keluar
        if ($request->status_masuk === 'Hadir' || $request->status_masuk === 'Terlambat') {
            $jamMasuk = $request->filled('jam_masuk') 
                ? Carbon::parse($tanggal . ' ' . $request->jam_masuk)
                : Carbon::now();
                
            $jamKeluar = $request->filled('jam_keluar') 
                ? Carbon::parse($tanggal . ' ' . $request->jam_keluar)
                : null;
            
            // ===== AUTO-VALIDASI TERLAMBAT: Jika jam masuk > 07:15 dan status masih Hadir =====
            $batasTerlambat = Carbon::parse($tanggal . ' 07:15:00');
            if ($jamMasuk && $jamMasuk->greaterThan($batasTerlambat) && $request->status_masuk === 'Hadir') {
                $request->merge(['status_masuk' => 'Terlambat']);
            }
        } elseif ($request->status_masuk === 'Sakit' || $request->status_masuk === 'Izin') {
            // ===== KASUS SAKIT/IZIN =====
            // 1. Jika ada jam_masuk (dengan/tanpa jam_keluar) -> catat sesuai input
            // 2. Jika tidak ada jam_masuk -> sakit/izin seharian
            
            if ($request->filled('jam_masuk')) {
                // Ada jam masuk -> catat jam masuk dan keluar (jika ada)
                $jamMasuk = Carbon::parse($tanggal . ' ' . $request->jam_masuk);
                $jamKeluar = $request->filled('jam_keluar')
                    ? Carbon::parse($tanggal . ' ' . $request->jam_keluar)
                    : null;
            } else {
                // Tidak ada jam masuk -> sakit/izin seharian
                $jamMasuk = null;
                $jamKeluar = null;
            }
        } else {
            // Jika Alpha atau Libur, set jam jadi null
            $jamMasuk = null;
            $jamKeluar = null;
        }

        // Create data baru
        Attendance::create([
            'student_id' => $request->student_id,
            'tanggal' => $tanggal,
            'status_masuk' => $request->status_masuk,
            'jam_masuk' => $jamMasuk,
            'jam_keluar' => $jamKeluar,
        ]);

        // Log aktivitas
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'description' => 'Membuat data absensi siswa ' . $student->nama . ' sebagai ' . $request->status_masuk . ' pada tanggal ' . Carbon::parse($tanggal)->format('d M Y'),
        ]);

        return redirect()->back()->with('success', 'Data absensi berhasil ditambahkan!');
    }

    public function validasiAbsenPulang(Request $request)
    {
        // Validasi: Hanya admin yang bisa validasi
        if (auth()->user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk validasi absensi!');
        }

        $tanggal = $request->tanggal ?? Carbon::today()->format('Y-m-d');
        $jamSekarang = Carbon::now();
        
        // Cari siswa yang:
        // 1. Sudah absen masuk hari ini (jam_masuk tidak null)
        // 2. Belum absen pulang (jam_keluar null)
        // 3. Status masuk bukan Sakit/Izin/Alpha (karena mereka dikecualikan)
        $siswaLupaAbsen = Attendance::whereDate('tanggal', $tanggal)
            ->whereNotNull('jam_masuk')
            ->whereNull('jam_keluar')
            ->whereIn('status_masuk', ['Hadir', 'Terlambat'])
            ->get();
        
        $count = 0;
        foreach ($siswaLupaAbsen as $attendance) {
            $attendance->update([
                'jam_keluar' => $jamSekarang,
                'status_pulang' => 'Alpha',
            ]);
            $count++;
            
            // Log aktivitas
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'description' => 'Validasi absen pulang: ' . $attendance->student->nama . ' ditandai Alpha (tidak absen pulang) pada ' . Carbon::parse($tanggal)->format('d M Y'),
            ]);
        }
        
        if ($count > 0) {
            return redirect()->back()->with('success', "✅ Validasi selesai! {$count} siswa ditandai Alpha karena tidak absen pulang.");
        } else {
            return redirect()->back()->with('info', 'ℹ️ Semua siswa yang absen masuk sudah absen pulang.');
        }
    }
}
