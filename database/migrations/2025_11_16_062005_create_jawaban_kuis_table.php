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
        Schema::create('jawaban_kuis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materi_id')
                ->constrained('materi')
                ->onDelete('cascade');
            $table->foreignId('siswa_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->longText('jawaban')->nullable();
            $table->integer('nilai')->nullable();
            $table->text('catatan_guru')->nullable();
            $table->timestamp('dinilai_pada')->nullable();
            $table->foreignId('dinilai_oleh')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['materi_id', 'siswa_id']);
            
            // Indexes
            $table->index('materi_id');
            $table->index('siswa_id');
            $table->index('nilai');
            $table->index('dinilai_pada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_kuis');
    }
};