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
        // Ambil daftar kelas
        $classes = Student::select('kelas')->distinct()->orderBy('kelas', 'asc')->pluck('kelas');
        
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

        // Ambil Siswa
        $students = Student::where('kelas', $kelas)->orderBy('nama', 'asc')->get();

        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada siswa di kelas ini.');
        }

        // Ambil Absensi
        $attendances = Attendance::whereIn('student_id', $students->pluck('id'))
                        ->whereYear('created_at', $tahun)
                        ->whereMonth('created_at', $bulan)
                        ->get();

        // Mapping Data
        $attendanceData = [];
        foreach ($attendances as $row) {
            $tgl = (int) Carbon::parse($row->created_at)->format('d');
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

        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('Rekap_Absen_Kelas_'.$kelas.'_'.$namaBulan.'.pdf');
    }

    public function daily(Request $request)
    {
        // 1. Ambil tanggal dari input, default hari ini
        $date = $request->date ?? Carbon::today()->toDateString();
        
        // 2. Ambil daftar semua kelas yang ada di database untuk pilihan dropdown
        $classes = Student::select('kelas')->distinct()->orderBy('kelas', 'asc')->pluck('kelas');

        // 3. Bangun Query Siswa
        $studentsQuery = Student::orderBy('kelas', 'asc')->orderBy('nama', 'asc');
        
        // Tambahkan filter jika kelas dipilih
        if ($request->filled('kelas')) {
            $studentsQuery->where('kelas', $request->kelas);
        }
        
        $students = $studentsQuery->get();
        
        // 4. Ambil data absensi pada tanggal tersebut
        $attendances = Attendance::whereDate('created_at', $date)
                        ->get()
                        ->keyBy('student_id');

        return view('report.daily', compact('students', 'attendances', 'date', 'classes'));
    }
}