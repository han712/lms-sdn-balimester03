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
        Schema::table('users', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['super_admin', 'guru', 'siswa'])
                    ->default('siswa')
                    ->after('email');
            }
            
            if (!Schema::hasColumn('users', 'nisn')) {
                $table->string('nisn', 20)->nullable()->unique()->after('role');
            }
            
            if (!Schema::hasColumn('users', 'nip')) {
                $table->string('nip', 30)->nullable()->unique()->after('nisn');
            }
            
            if (!Schema::hasColumn('users', 'kelas')) {
                $table->string('kelas', 10)->nullable()->after('nip');
            }
            
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('kelas');
            }
            
            if (!Schema::hasColumn('users', 'last_activity')) {
                $table->timestamp('last_activity')->nullable()->after('is_active');
            }
            
            // Indexes
            if (!Schema::hasColumn('users', 'role')) {
                return;
            }
            
            $table->index('role');
            $table->index('kelas');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop indexes
            if (Schema::hasColumn('users', 'role')) {
                $table->dropIndex(['role']);
            }
            if (Schema::hasColumn('users', 'kelas')) {
                $table->dropIndex(['kelas']);
            }
            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropIndex(['is_active']);
            }
            
            // Drop unique constraints
            if (Schema::hasColumn('users', 'nisn')) {
                $table->dropUnique(['nisn']);
            }
            if (Schema::hasColumn('users', 'nip')) {
                $table->dropUnique(['nip']);
            }
            
            // Drop columns
            $table->dropColumn([
                'role',
                'nisn',
                'nip',
                'kelas',
                'is_active',
                'last_activity'
            ]);
        });
    }
};