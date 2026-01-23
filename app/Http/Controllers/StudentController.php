<?php

namespace App\Http\Controllers;

use App\Exports\StudentTemplateExport; // Pastikan ini ada
use App\Imports\StudentsImport;        // Pastikan ini ada
use Maatwebsite\Excel\Facades\Excel;   // Pastikan ini ada
use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class StudentController extends Controller
{
    // =========================================================================
    // BAGIAN 1: MANAJEMEN DATA SISWA (CRUD)
    // =========================================================================

    // 1. TAMPILKAN DAFTAR SISWA
    public function index(Request $request)
{
    $query = Student::query();

    // 1. Fitur Cari Nama / NISN
    if ($request->filled('search')) {
        $query->where(function($q) use ($request) {
            $q->where('nama', 'like', '%' . $request->search . '%')
              ->orWhere('nisn', 'like', '%' . $request->search . '%');
        });
    }

    // 2. Fitur Filter per Kelas (Baru)
    if ($request->filled('kelas')) {
        $query->where('kelas', $request->kelas);
    }

    // 3. Ambil daftar kelas unik untuk pilihan di dropdown
    $classes = Student::select('kelas')->distinct()->orderBy('kelas', 'asc')->pluck('kelas');

    // Tampilkan data dengan pagination
    $students = $query->latest()->paginate(10)->withQueryString();
    
    return view('students.index', compact('students', 'classes'));
}

    // 2. FORM TAMBAH SISWA
    public function create()
    {
        return view('students.create');
    }

    // 3. SIMPAN DATA BARU
    public function store(Request $request)
    {
        $request->validate([
            'nisn'  => 'required|unique:students,nisn|numeric',
            'nama'  => 'required|string|max:255',
            'kelas' => 'required|string',
            'foto'  => 'nullable|image|mimes:webp|max:1024',
        ], [
            'foto.mimes' => 'Foto harus berformat WebP',
            'foto.max' => 'Ukuran foto maksimal 1MB',
        ]);

        $data = [
            'nisn'  => $request->nisn,
            'nama'  => $request->nama,
            'kelas' => $request->kelas,
        ];

        // Handle upload foto
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $fotoName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('students', $fotoName, 'public');
            $data['foto'] = $fotoName;
        }

        Student::create($data);

        return redirect()->route('students.index')->with('success', 'Data siswa berhasil ditambahkan!');
    }

    // 4. FORM EDIT SISWA
    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    // 5. UPDATE DATA SISWA
    public function update(Request $request, Student $student)
    {
        $request->validate([
            'nisn'  => 'required|numeric|unique:students,nisn,' . $student->id,
            'nama'  => 'required|string|max:255',
            'kelas' => 'required|string',
            'foto'  => 'nullable|image|mimes:webp|max:1024',
        ], [
            'foto.mimes' => 'Foto harus berformat WebP',
            'foto.max' => 'Ukuran foto maksimal 1MB',
        ]);

        $data = [
            'nisn'  => $request->nisn,
            'nama'  => $request->nama,
            'kelas' => $request->kelas,
        ];

        // Handle upload foto
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($student->foto) {
                Storage::disk('public')->delete('students/' . $student->foto);
            }
            
            $file = $request->file('foto');
            $fotoName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('students', $fotoName, 'public');
            $data['foto'] = $fotoName;
        }

        $student->update($data);

        return redirect()->route('students.index')->with('success', 'Data siswa berhasil diperbarui!');
    }

    // 6. HAPUS SISWA
    public function destroy(Student $student)
    {
        if ($student->foto) {
            Storage::delete('public/students/' . $student->foto);
        }
        $student->delete();
        return redirect()->route('students.index')->with('success', 'Data siswa dihapus.');
    }


    // =========================================================================
    // BAGIAN 2: FITUR CETAK KARTU PELAJAR (ID CARD)
    // =========================================================================

    public function printCard($id)
    {
        $student = Student::findOrFail($id);
        
        // Optimasi foto untuk single card juga
        if ($student->foto) {
            $fotoPath = public_path('storage/students/' . $student->foto);
            if (file_exists($fotoPath)) {
                try {
                    $imageData = file_get_contents($fotoPath);
                    $image = imagecreatefromstring($imageData);
                    
                    if ($image !== false) {
                        $width = imagesx($image);
                        $height = imagesy($image);
                        
                        if ($width > 200 || $height > 200) {
                            $newWidth = 200;
                            $newHeight = 200;
                            $resized = imagecreatetruecolor($newWidth, $newHeight);
                            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                            
                            ob_start();
                            imagejpeg($resized, null, 75);
                            $resizedData = ob_get_clean();
                            $student->foto_base64 = 'data:image/jpeg;base64,' . base64_encode($resizedData);
                            
                            imagedestroy($resized);
                        } else {
                            $student->foto_base64 = 'data:image/jpeg;base64,' . base64_encode($imageData);
                        }
                        
                        imagedestroy($image);
                    }
                } catch (\Exception $e) {
                    $student->foto_base64 = null;
                }
            }
        }
        
        $pdf = Pdf::loadView('students.card', compact('student'));
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption(['dpi' => 96, 'defaultFont' => 'sans-serif']);

        return $pdf->stream('Kartu_Pelajar_' . $student->nisn . '.pdf');
    }


    // =========================================================================
    // BAGIAN 3: FITUR LAPORAN BULANAN (REKAP PRESENSI)
    // =========================================================================

    public function reportForm(Student $student)
    {
        return view('students.report_form', compact('student'));
    }

    public function downloadReport(Request $request, Student $student)
    {
        $request->validate([
            'bulan' => 'required|numeric|min:1|max:12',
            'tahun' => 'required|numeric|min:2020|max:'.(date('Y')+1),
        ]);

        $bulan = $request->bulan;
        $tahun = $request->tahun;

        $attendances = Attendance::where('student_id', $student->id)
                        ->whereYear('created_at', $tahun)
                        ->whereMonth('created_at', $bulan)
                        ->orderBy('created_at', 'asc')
                        ->get();

        $summary = [
            'hadir'     => $attendances->where('status_masuk', 'Hadir')->count(),
            'terlambat' => $attendances->where('status_masuk', 'Terlambat')->count(),
            'sakit'     => $attendances->where('status_masuk', 'Sakit')->count(), 
            'izin'      => $attendances->where('status_masuk', 'Izin')->count(),
            'alpha'     => $attendances->where('status_masuk', 'Alpha')->count(),
        ];

        $namaBulan = Carbon::create()->month((int)$bulan)->translatedFormat('F');

        $pdf = Pdf::loadView('students.report_pdf', compact('student', 'attendances', 'summary', 'namaBulan', 'tahun'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Laporan_Absensi_' . $student->nama . '_' . $namaBulan . '.pdf');
    }


    // =========================================================================
    // BAGIAN 4: FITUR EXCEL (IMPORT & TEMPLATE)
    // =========================================================================

    // A. DOWNLOAD TEMPLATE KOSONG
    public function downloadTemplate()
    {
        return Excel::download(new StudentTemplateExport, 'template_siswa.xlsx');
    }

    // B. IMPORT DATA EXCEL
    public function import(Request $request) 
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);
 
        try {
            Excel::import(new StudentsImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data Siswa Berhasil Diimport!');
        } catch (\Exception $e) {
            // Tampilkan pesan error detail agar Admin tahu jika ada kolom yang salah
            return redirect()->back()->with('error', 'Gagal Import: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // FITUR CETAK KARTU MASSAL (BULK PRINT)
    // =========================================================================
    
    public function printAllCards(Request $request)
    {
        // Optimasi performa server
        ini_set('max_execution_time', 600); 
        ini_set('memory_limit', '1024M');   
        
        // Jika tidak ada kelas yang dipilih, redirect kembali
        if (!$request->has('kelas')) {
            return redirect()->back()->with('error', 'Silakan pilih kelas terlebih dahulu.');
        }

        $kelas = $request->kelas;
        
        // Ambil semua siswa di kelas tersebut
        $students = Student::where('kelas', $kelas)->orderBy('nama', 'asc')->get();

        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada siswa di kelas ini.');
        }

        // OPTIMASI: Resize foto untuk mengurangi beban memory
        foreach ($students as $student) {
            if ($student->foto) {
                $fotoPath = public_path('storage/students/' . $student->foto);
                if (file_exists($fotoPath)) {
                    // Baca dan resize foto menjadi max 200x200px (cukup untuk kartu)
                    try {
                        $imageData = file_get_contents($fotoPath);
                        $image = imagecreatefromstring($imageData);
                        
                        if ($image !== false) {
                            $width = imagesx($image);
                            $height = imagesy($image);
                            
                            // Resize hanya jika foto lebih besar dari 200px
                            if ($width > 200 || $height > 200) {
                                $newWidth = 200;
                                $newHeight = 200;
                                $resized = imagecreatetruecolor($newWidth, $newHeight);
                                imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                                
                                // Convert ke base64 untuk embed langsung di HTML
                                ob_start();
                                imagejpeg($resized, null, 75); // Quality 75% untuk balance size & quality
                                $resizedData = ob_get_clean();
                                $student->foto_base64 = 'data:image/jpeg;base64,' . base64_encode($resizedData);
                                
                                imagedestroy($resized);
                            } else {
                                // Foto sudah kecil, langsung convert ke base64
                                $student->foto_base64 = 'data:image/jpeg;base64,' . base64_encode($imageData);
                            }
                            
                            imagedestroy($image);
                        }
                    } catch (\Exception $e) {
                        // Jika gagal, gunakan foto asli
                        $student->foto_base64 = null;
                    }
                }
            }
        }

        // Gunakan view baru 'students.cards_bulk'
        $pdf = Pdf::loadView('students.cards_bulk', compact('students', 'kelas'));
        
        // Kertas A4 Portrait dengan DPI lebih rendah untuk performa
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption([
            'dpi' => 96,  // Turunkan dari 150 ke 96 untuk performa lebih cepat
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,  // Disable remote loading untuk keamanan & performa
        ]);

        return $pdf->stream('Kartu_Pelajar_Kelas_' . $kelas . '.pdf');
    }
}