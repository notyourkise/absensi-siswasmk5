<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ===== AUTO MARK SISWA YANG TIDAK ABSEN PULANG =====
// TESTING: Berjalan setiap hari jam 12:07
Schedule::command('absen:auto-pulang')
    ->dailyAt('12:07')
    ->weekdays() // Hanya Senin-Jumat
    ->timezone('Asia/Jakarta');
