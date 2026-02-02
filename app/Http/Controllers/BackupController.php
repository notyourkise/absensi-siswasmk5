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
        $filename = "backup-smkn5-" . date('Y-m-d_H-i-s') . ".sql";
        $filePath = storage_path("app/" . $filename);
        
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');

        // PERINTAH BERSIH (Cocok untuk Server Linux/Ubuntu)
        // Kita hapus --column-statistics=0 agar kompatibel dengan semua versi MySQL/MariaDB Server
        $passwordCmd = $dbPass ? "--password=\"$dbPass\"" : "";
        
        $command = "mysqldump --user=\"$dbUser\" $passwordCmd --host=\"$dbHost\" \"$dbName\" > \"$filePath\"";

        $process = Process::fromShellCommandline($command);
        $process->setTimeout(120); 
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return Response::download($filePath)->deleteFileAfterSend(true);
    }
}