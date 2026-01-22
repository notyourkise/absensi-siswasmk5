<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'status_masuk',
    ];

    // Relasi: Absensi ini milik satu siswa
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}