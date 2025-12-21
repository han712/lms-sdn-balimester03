<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Data Kepegawaian
            // NIP sudah ada di migrasi sebelumnya? Jika belum, uncomment baris ini:
            // $table->string('nip')->nullable()->unique()->after('email'); 
            
            $table->enum('status_kepegawaian', ['PNS', 'GTY', 'GTT'])->nullable()->after('nip')
                  ->comment('PNS, GTY (Guru Tetap Yayasan), GTT (Guru Tidak Tetap/Honorer)');
            
            $table->string('jabatan_tambahan')->nullable()->after('status_kepegawaian')
                  ->comment('Contoh: Kepala Sekolah, Wakasek, dll');

            // Data Akademik
            $table->string('pendidikan_terakhir')->nullable()->after('jabatan_tambahan');
            $table->string('mata_pelajaran_utama')->nullable()->after('pendidikan_terakhir')
                  ->comment('Guru Kelas, PJOK, Agama, dll');
            
            $table->string('wali_kelas')->nullable()->after('mata_pelajaran_utama')
                  ->comment('Menyimpan angka kelas (1-6) jika dia wali kelas');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'status_kepegawaian', 
                'jabatan_tambahan', 
                'pendidikan_terakhir', 
                'mata_pelajaran_utama', 
                'wali_kelas'
            ]);
        });
    }
};