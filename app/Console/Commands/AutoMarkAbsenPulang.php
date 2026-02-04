<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use Carbon\Carbon;

class AutoMarkAbsenPulang extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'absen:auto-pulang';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis tandai siswa sebagai Alpha (pulang) jika tidak absen pulang jam 15:30-16:30';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $jamOtomatis = Carbon::today()->setTime(12, 7, 0);
        
        // Cari siswa yang:
        // 1. Sudah absen masuk hari ini (jam_masuk tidak null)
        // 2. Belum absen pulang (jam_keluar null ATAU status_pulang null)
        // 3. Status masuk bukan Sakit/Izin/Alpha (karena mereka dikecualikan)
        $siswaLupaAbsen = Attendance::whereDate('tanggal', $today)
            ->whereNotNull('jam_masuk')
            ->where(function($query) {
                $query->whereNull('jam_keluar')
                      ->orWhereNull('status_pulang');
            })
            ->whereIn('status_masuk', ['Hadir', 'Terlambat'])
            ->get();
        
        $count = 0;
        foreach ($siswaLupaAbsen as $attendance) {
            $attendance->update([
                'jam_keluar' => $jamOtomatis,
                'status_pulang' => 'Alpha',
            ]);
            $count++;
            
            $this->info("✓ {$attendance->student->nama} ditandai Alpha (tidak absen pulang)");
        }
        
        if ($count > 0) {
            $this->info("\n🎯 Total {$count} siswa ditandai Alpha karena tidak absen pulang.");
        } else {
            $this->info("✅ Semua siswa yang absen masuk sudah absen pulang.");
        }
        
        return 0;
    }
}
