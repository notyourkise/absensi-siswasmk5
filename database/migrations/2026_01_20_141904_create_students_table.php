<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nisn')->unique(); // Ini akan jadi isi QR Code
            $table->string('nama');
            $table->string('kelas'); // Contoh: "XII RPL 1"
            $table->string('jenis_kelamin')->nullable(); // L/P
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};