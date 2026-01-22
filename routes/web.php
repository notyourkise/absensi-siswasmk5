<?php

use App\Http\Controllers\ManualAbsenController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScanController; 
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. HALAMAN DEPAN
Route::get('/', function () {
    return redirect()->route('login');
});

// 2. DASHBOARD
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// 3. GROUP AUTH (Semua fitur admin ada di sini)
Route::middleware('auth')->group(function () {

    // --- A. PROFILE USER ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    // --- B. MANAJEMEN SISWA (URUTAN SANGAT PENTING!) ---
    Route::get('/laporan-harian', [ReportController::class, 'daily'])->name('report.daily');
    // [PENTING] Route Template & Import harus DI ATAS Resource
    // Agar kata "template" tidak dianggap sebagai {id} siswa
    Route::get('/students/template', [StudentController::class, 'downloadTemplate'])->name('students.template');
    Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');

    // Route Fitur Spesifik per Siswa (Cetak Kartu & Laporan Pribadi)
    // Route Cetak Kartu Massal per Kelas
    Route::get('/students/print-all', [StudentController::class, 'printAllCards'])->name('students.print.all');
    Route::get('/students/{student}/print', [StudentController::class, 'printCard'])->name('students.print');
    Route::get('/students/{student}/report', [StudentController::class, 'reportForm'])->name('students.report');
    Route::post('/students/{student}/report/download', [StudentController::class, 'downloadReport'])->name('students.report.download');

    // Route Resource (CRUD Utama: index, create, store, edit, update, destroy, show)
    // [PENTING] Harus ditaruh paling bawah di grup siswa
    Route::resource('students', StudentController::class);


    // --- C. MESIN ABSENSI (SCANNER) ---
    Route::get('/scan', [ScanController::class, 'index'])->name('scan.index');
    Route::post('/scan', [ScanController::class, 'store'])->name('scan.store');


    // --- D. INPUT MANUAL (SAKIT/IZIN) ---
    Route::get('/input-manual', [ManualAbsenController::class, 'create'])->name('manual.create');
    Route::post('/input-manual', [ManualAbsenController::class, 'store'])->name('manual.store');


    // --- E. LAPORAN REKAPITULASI KELAS (JURNAL) ---
    Route::get('/report', [ReportController::class, 'index'])->name('report.index');
    Route::post('/report/download', [ReportController::class, 'downloadClassReport'])->name('report.download');

});

require __DIR__.'/auth.php';