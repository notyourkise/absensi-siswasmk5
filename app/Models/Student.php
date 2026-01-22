<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    // Kolom yang boleh diisi
    protected $fillable = [
        'nisn',
        'nama',
        'kelas',
        'foto',
        'jenis_kelamin'
    ];

    // Relasi: Satu siswa punya banyak absensi
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}