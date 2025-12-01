<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Guru\GuruController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Siswa\SiswaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// ==========================
// Authenticated routes
// ==========================
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard redirect based on role
    Route::get('/dashboard', function () {
        $user = auth()->user();
        return match($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'guru' => redirect()->route('guru.dashboard'),
            'siswa' => redirect()->route('siswa.materi.index'),
            default => abort(403)
        };
    })->name('dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ==========================
// Admin Routes
// ==========================
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // User Management
        Route::resource('users', AdminController::class)->except(['create', 'store']);
        Route::get('users/create', [AdminController::class, 'create'])->name('users.create');
        Route::post('users', [AdminController::class, 'store'])->name('users.store');

        // User Actions
        Route::post('users/{user}/toggle-active', [AdminController::class, 'toggleActive'])->name('users.toggle-active');
        Route::post('users/{user}/reset-password', [AdminController::class, 'resetPassword'])->name('users.reset-password');

        // Bulk Actions
        Route::post('users/bulk-delete', [AdminController::class, 'bulkDelete'])->name('users.bulk-delete');
        Route::post('users/bulk-toggle-active', [AdminController::class, 'bulkToggleActive'])->name('users.bulk-toggle-active');

        // Import/Export
        Route::post('users/import', [AdminController::class, 'importUsers'])->name('users.import');
        Route::get('users/export', [AdminController::class, 'exportUsers'])->name('users.export');

        // Materi & Absensi
        Route::get('materi', [AdminController::class, 'allMateri'])->name('materi.index');
        Route::get('absensi', [AdminController::class, 'allAbsensi'])->name('absensi.index');
    });

// ==========================
// Guru Routes
// ==========================
Route::middleware(['auth', 'verified', 'role:guru'])
    ->prefix('guru')
    ->name('guru.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [GuruController::class, 'index'])->name('dashboard');

        // Materi CRUD
        Route::get('materi', [GuruController::class, 'materiIndex'])->name('materi.index');
        Route::get('materi/create', [GuruController::class, 'createMateri'])->name('materi.create');
        Route::post('materi', [GuruController::class, 'storeMateri'])->name('materi.store');
        Route::get('materi/{materi}/edit', [GuruController::class, 'editMateri'])->name('materi.edit');
        Route::put('materi/{materi}', [GuruController::class, 'updateMateri'])->name('materi.update');
        Route::delete('materi/{materi}', [GuruController::class, 'destroyMateri'])->name('materi.destroy');

        // Materi Actions
        Route::post('materi/{materi}/toggle-publish', [GuruController::class, 'togglePublish'])->name('materi.toggle-publish');
        Route::post('materi/{materi}/duplicate', [GuruController::class, 'duplicate'])->name('materi.duplicate');
        Route::post('materi/bulk-delete', [GuruController::class, 'bulkDelete'])->name('materi.bulk-delete');

        // Absensi Management
        Route::get('materi/{materi}/absensi', [GuruController::class, 'absensi'])->name('materi.absensi');
        Route::post('materi/{materi}/absensi/update', [GuruController::class, 'updateAbsensi'])->name('materi.absensi.update');
        Route::post('materi/{materi}/absensi/bulk-update', [GuruController::class, 'bulkUpdateAbsensi'])->name('materi.absensi.bulk-update');
        Route::post('absensi/export', [GuruController::class, 'exportAbsensi'])->name('absensi.export');

        // Kuis & Penilaian
        Route::get('materi/{materi}/jawaban-kuis', [GuruController::class, 'jawabanKuis'])->name('materi.jawaban-kuis');
        Route::post('jawaban-kuis/{jawaban}/nilai', [GuruController::class, 'nilaiKuis'])->name('jawaban-kuis.nilai');
        Route::post('materi/{materi}/jawaban-kuis/bulk-nilai', [GuruController::class, 'bulkNilaiKuis'])->name('materi.jawaban-kuis.bulk-nilai');

        // Data Guru & Staff
        Route::get('data-guru', [GuruController::class, 'dataGuru'])->name('data-guru');
    });

// ==========================
// Siswa Routes
// ==========================
Route::middleware(['auth', 'verified', 'role:siswa'])
    ->prefix('siswa')
    ->name('siswa.')
    ->group(function () {

        // Materi & Kuis
        Route::get('materi', [SiswaController::class, 'index'])->name('materi.index');
        Route::get('materi/{materi}', [SiswaController::class, 'show'])->name('materi.show');
        Route::post('materi/{materi}/submit-kuis', [SiswaController::class, 'submitKuis'])->name('materi.submit-kuis');

        // Riwayat
        Route::get('riwayat-absensi', [SiswaController::class, 'riwayatAbsensi'])->name('riwayat-absensi');
        Route::get('riwayat-kuis', [SiswaController::class, 'riwayatKuis'])->name('riwayat-kuis');
    });

require __DIR__.'/auth.php';