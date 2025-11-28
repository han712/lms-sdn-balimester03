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
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materi_id')
                ->constrained('materi')
                ->onDelete('cascade');
            $table->foreignId('siswa_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->enum('status', ['hadir', 'tidak_hadir', 'sakit', 'izin'])
                ->default('hadir');
            $table->timestamp('waktu_akses')->nullable();
            $table->integer('durasi_akses')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            // Unique constraint - satu siswa per materi hanya 1 record
            $table->unique(['materi_id', 'siswa_id']);
            
            // Indexes
            $table->index('materi_id');
            $table->index('siswa_id');
            $table->index('status');
            $table->index('waktu_akses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};