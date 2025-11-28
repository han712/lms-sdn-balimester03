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
        Schema::create('materi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->string('judul');
            $table->longText('keterangan')->nullable();
            $table->enum('tipe', ['materi', 'kuis'])->default('materi');
            $table->string('file')->nullable();
            $table->string('link', 500)->nullable();
            $table->string('video', 500)->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->string('kelas', 10);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes untuk performance
            $table->index('guru_id');
            $table->index('tipe');
            $table->index('kelas');
            $table->index('tanggal_mulai');
            $table->index('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materi');
    }
};