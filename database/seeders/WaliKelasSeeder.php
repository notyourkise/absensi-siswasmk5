<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class WaliKelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Generate 30 Akun Wali Kelas otomatis:
     * - 5 Jurusan: TJKT, MPLB, PS, PM, DKV
     * - 3 Tingkat: 10, 11, 12
     * - 2 Rombel: 1, 2
     * - Total: 5 x 3 x 2 = 30 Akun
     * 
     * Format Email: wali[tingkat][jurusan][nomor]@sekolah.com
     * Contoh: wali12tjkt1@sekolah.com
     * Password Default: password
     */
    public function run(): void
    {
        // Data Master
        $jurusanList = ['TJKT', 'MPLB', 'PS', 'PM', 'DKV'];
        $tingkatList = [10, 11, 12];
        $rombelList = [1, 2];

        $counter = 0;

        // Loop Triple Nested untuk Generate 30 Akun
        foreach ($tingkatList as $tingkat) {
            foreach ($jurusanList as $jurusan) {
                foreach ($rombelList as $rombel) {
                    $counter++;

                    // Format: "12 TJKT 1"
                    $kelas = "{$tingkat} {$jurusan} {$rombel}";

                    // Format Email: wali12tjkt1@sekolah.com (huruf kecil)
                    $email = 'wali' . $tingkat . strtolower($jurusan) . $rombel . '@sekolah.com';

                    // Nama: Wali Kelas 12 TJKT 1
                    $name = "Wali Kelas {$kelas}";

                    User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make('password'),
                        'role' => 'wali_kelas',
                        'kelas' => $kelas,
                    ]);

                    echo "[{$counter}/30] ✅ {$name} -> {$email}\n";
                }
            }
        }

        echo "\n🎉 Berhasil generate 30 Akun Wali Kelas!\n";
    }
}

