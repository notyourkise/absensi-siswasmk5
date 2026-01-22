<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel students
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            
            $table->date('tanggal');       // 2024-01-20
            $table->time('jam_masuk')->nullable();  // 07:00:00
            $table->time('jam_keluar')->nullable(); // 15:00:00
            
            // Status: Hadir, Terlambat, Izin, Sakit, Alpha
            $table->string('status_masuk')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};