<?php

namespace App\Imports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return Student::updateOrCreate(
            ['nisn' => $row['nisn']], // Acuan Update (Unique Key)
            [
                'nama'          => $row['nama'], 
                'kelas'         => $row['kelas'],
                'jenis_kelamin' => $row['jenis_kelamin'], // Tambahan baru
            ]
        );
    }
}