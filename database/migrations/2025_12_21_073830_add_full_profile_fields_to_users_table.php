<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // --- DATA KHUSUS SISWA ---
            // NISN sudah ada di migrasi sebelumnya
            $table->string('nis', 20)->nullable()->unique()->after('nisn');
            $table->year('tahun_masuk')->nullable()->after('kelas');
            $table->string('tempat_lahir')->nullable()->after('tahun_masuk');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('tanggal_lahir');
            $table->string('agama')->nullable()->after('jenis_kelamin');
            
            // Data Ortu Siswa
            $table->string('nama_ayah')->nullable()->after('agama');
            $table->string('nama_ibu')->nullable()->after('nama_ayah');
            $table->string('no_hp_ortu')->nullable()->after('nama_ibu');
            $table->string('pekerjaan_ortu')->nullable()->after('no_hp_ortu');

            // --- DATA KHUSUS GURU ---
            // NIP sudah ada di migrasi sebelumnya
            $table->enum('status_kepegawaian', ['PNS', 'GTY', 'GTT'])->nullable()->after('nip');
            $table->string('jabatan_tambahan')->nullable()->after('status_kepegawaian');
            $table->string('wali_kelas')->nullable()->comment('Menyimpan kelas yg diampu, misal: 1, 2, 6')->after('jabatan_tambahan');
            $table->string('mata_pelajaran_utama')->nullable()->after('wali_kelas');
            $table->string('pendidikan_terakhir')->nullable()->after('mata_pelajaran_utama');

            // --- DATA KHUSUS ADMIN ---
            $table->string('id_pegawai')->nullable()->unique()->after('role');
            $table->string('posisi')->nullable()->comment('TU, IT Support, dll')->after('id_pegawai');
            $table->string('level_akses')->default('super_admin')->after('posisi');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nis', 'tahun_masuk', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'agama',
                'nama_ayah', 'nama_ibu', 'no_hp_ortu', 'pekerjaan_ortu',
                'status_kepegawaian', 'jabatan_tambahan', 'wali_kelas', 'mata_pelajaran_utama', 'pendidikan_terakhir',
                'id_pegawai', 'posisi', 'level_akses'
            ]);
        });
    }
};