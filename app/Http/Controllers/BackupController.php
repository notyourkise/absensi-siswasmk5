<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BackupController extends Controller
{
    public function download()
    {
        // 1. Nama File
        $filename = "backup-smkn5-" . date('Y-m-d_H-i-s') . ".sql";
        $filePath = storage_path("app/" . $filename);
        
        // 2. Konfigurasi Database
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');

        // 3. Susun Perintah (Linux Style)
        // Di Ubuntu, kita cukup panggil 'mysqldump' langsung.
        // Kita tambahkan --column-statistics=0 untuk jaga-jaga jika Ubuntu pakai MySQL 8
        // agar tidak error "Unknown table 'COLUMN_STATISTICS'".
        
        $passwordCmd = $dbPass ? "--password=\"$dbPass\"" : "";
        
        $command = "mysqldump --user=\"$dbUser\" $passwordCmd --host=\"$dbHost\" --column-statistics=0 \"$dbName\" > \"$filePath\"";

        // 4. Eksekusi
        // Timeout kita set 120 detik (2 menit) biar aman kalau data sudah besar
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(120); 
        $process->run();

        // 5. Cek Error
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // 6. Download & Hapus File Temp
        return Response::download($filePath)->deleteFileAfterSend(true);
    }
}