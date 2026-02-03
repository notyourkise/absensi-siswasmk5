<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    // 1. Tambah Soft Deletes ke Tabel Users (dan Students jika mau)
    Schema::table('users', function (Blueprint $table) {
        $table->softDeletes(); // Menambah kolom 'deleted_at'
    });

    // Kalau Boss mau Students juga soft delete, uncomment baris bawah ini:
    // Schema::table('students', function (Blueprint $table) {
    //     $table->softDeletes();
    // });

    // 2. Buat Tabel Log Aktivitas
    Schema::create('activity_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Siapa pelakunya
        $table->string('action'); // Contoh: "DELETE USER", "UPDATE SISWA"
        $table->text('description'); // Detail: "Menghapus user bernama Budi"
        $table->string('ip_address')->nullable(); // Alamat IP pelakunya
        $table->timestamps(); // Mencatat kapan (created_at)
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
