<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class StudentTemplateExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return collect([]); // Data kosong, hanya butuh header
    }

    public function headings(): array
    {
        // SESUAIKAN DENGAN NAMA KOLOM DI DATABASE ANDA
        return [
            'nisn',
            'nama',
            'kelas',
            'jenis_kelamin', // L atau P
        ];
    }
}