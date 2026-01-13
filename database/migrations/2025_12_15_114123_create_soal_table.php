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
        Schema::create('soals', function (Blueprint $table) {
            $table->id();
            // Terhubung ke tabel materi (karena kuis adalah tipe materi)
            $table->foreignId('materi_id')->constrained('materi')->onDelete('cascade');
            
            $table->enum('tipe_soal', ['pilihan_ganda', 'essay']);
            $table->text('pertanyaan');
            $table->string('gambar')->nullable(); // Fitur Image Upload
            
            // Menyimpan opsi jawaban a,b,c,d (bisa null jika essay)
            $table->json('opsi_jawaban')->nullable(); 
            
            // Kunci jawaban (misal: 'a' atau teks jawaban singkat)
            $table->text('kunci_jawaban');
            
            $table->integer('bobot_nilai')->default(10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soal');
    }
};
