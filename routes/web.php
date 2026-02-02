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

// 2. GROUP AUTH UTAMA (Harus Login Dulu)
Route::middleware('auth')->group(function () {

    // --- A. DASHBOARD & PROFILE (SEMUA ROLE BISA AKSES) ---
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('verified') // Opsional jika pakai verifikasi email
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // [BARU] Route Ganti Password
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');

    // --- B. FITUR SCAN (ADMIN & PETUGAS) ---
    // Wali Kelas DILARANG masuk sini
    Route::middleware('role:admin,petugas')->group(function () {
        Route::get('/scan', [ScanController::class, 'index'])->name('scan.index');
        Route::post('/scan', [ScanController::class, 'store'])->name('scan.store');
    });


    // --- C. LAPORAN & INPUT MANUAL (ADMIN & WALI KELAS) ---
    // Petugas DILARANG masuk sini (takut salah hapus/lihat data)
    Route::middleware('role:admin,wali_kelas')->group(function () {
        
        // 1. Laporan Harian
        Route::get('/laporan-harian', [ReportController::class, 'daily'])->name('report.daily');

        // 2. Laporan Rekap Kelas (Jurnal)
        Route::get('/report', [ReportController::class, 'index'])->name('report.index');
        Route::post('/report/download', [ReportController::class, 'downloadClassReport'])->name('report.download');

        // 3. Input Manual (Sakit/Izin)
        Route::get('/input-manual', [ManualAbsenController::class, 'create'])->name('manual.create');
        Route::post('/input-manual', [ManualAbsenController::class, 'store'])->name('manual.store');
    });


    // --- D. MANAJEMEN SISWA FULL (KHUSUS ADMIN) ---
    // Petugas & Wali Kelas DILARANG edit data siswa
    Route::middleware('role:admin')->group(function () {

        // 1. MANAJEMEN USER (BARU)
        // Letakkan di paling atas group Admin agar rapi
        Route::resource('users', \App\Http\Controllers\UserController::class);

        // [BARU] Route Backup Database
        Route::get('/backup/download', [App\Http\Controllers\BackupController::class, 'download'])->name('backup.download');

        // [PENTING] Route Template & Import harus DI ATAS Resource
        Route::get('/students/template', [StudentController::class, 'downloadTemplate'])->name('students.template');
        Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');

        // Cetak Kartu Massal
        Route::get('/students/print-all', [StudentController::class, 'printAllCards'])->name('students.print.all');

        // Fitur Per-Siswa (Kartu & Laporan Pribadi)
        Route::get('/students/{student}/print', [StudentController::class, 'printCard'])->name('students.print');
        Route::get('/students/{student}/report', [StudentController::class, 'reportForm'])->name('students.report');
        Route::post('/students/{student}/report/download', [StudentController::class, 'downloadReport'])->name('students.report.download');

        // CRUD Utama (Resource) - [PENTING] Taruh paling bawah
        Route::resource('students', StudentController::class);
    });

});

require __DIR__.'/auth.php';