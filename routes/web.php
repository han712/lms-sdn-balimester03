<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Guru\GuruController;
use App\Http\Controllers\Guru\DataSiswaController;
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
            'guru'  => redirect()->route('guru.dashboard'),
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

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('users', AdminController::class)->except(['create', 'store']);
        Route::get('users/create', [AdminController::class, 'create'])->name('users.create');
        Route::post('users', [AdminController::class, 'store'])->name('users.store');

        Route::post('users/{user}/toggle-active', [AdminController::class, 'toggleActive'])->name('users.toggle-active');
        Route::post('users/{user}/reset-password', [AdminController::class, 'resetPassword'])->name('users.reset-password');

        Route::post('users/bulk-delete', [AdminController::class, 'bulkDelete'])->name('users.bulk-delete');
        Route::post('users/bulk-toggle-active', [AdminController::class, 'bulkToggleActive'])->name('users.bulk-toggle-active');

        Route::post('users/import', [AdminController::class, 'importUsers'])->name('users.import');
        Route::get('users/export', [AdminController::class, 'exportUsers'])->name('users.export');

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
        Route::get('/dashboard', [GuruController::class, 'dashboard'])->name('dashboard');

        // =======================================
        // MATERI CRUD (CUSTOM + REMOTE DIGABUNG)
        // =======================================

        // Custom CRUD (punyamu)
        Route::get('materi', [GuruController::class, 'materiIndex'])->name('materi.index');
        Route::get('materi/create', [GuruController::class, 'createMateri'])->name('materi.create');
        Route::post('materi', [GuruController::class, 'storeMateri'])->name('materi.store');
        Route::get('materi/{materi}/edit', [GuruController::class, 'editMateri'])->name('materi.edit');
        Route::put('materi/{materi}', [GuruController::class, 'updateMateri'])->name('materi.update');
        Route::delete('materi/{materi}', [GuruController::class, 'destroyMateri'])->name('materi.destroy');

        // Dari remote (publish, duplicate, bulk)
        Route::post('materi/{materi}/duplicate', [GuruController::class, 'duplicate'])->name('materi.duplicate');
        Route::post('materi/{materi}/toggle-publish', [GuruController::class, 'togglePublish'])->name('materi.toggle-publish');
        Route::post('materi/bulk-publish', [GuruController::class, 'bulkPublish'])->name('materi.bulk-publish');
        Route::post('materi/bulk-delete', [GuruController::class, 'bulkDelete'])->name('materi.bulk-delete');

        // Absensi
        Route::get('materi/{materi}/absensi', [GuruController::class, 'absensi'])->name('materi.absensi');
        Route::put('materi/{materi}/absensi', [GuruController::class, 'updateAbsensi'])->name('materi.absensi.update');
        Route::get('laporan/absensi', [GuruController::class, 'exportRekapAbsensi'])->name('laporan.absensi');

        // Kuis & Penilaian
        Route::get('materi/{materi}/jawaban-kuis', [GuruController::class, 'jawabanKuis'])->name('materi.jawaban-kuis');
        Route::post('jawaban-kuis/{jawaban}/nilai', [GuruController::class, 'nilaiKuis'])->name('jawaban-kuis.nilai');
        Route::post('materi/{materi}/jawaban-kuis/bulk-nilai', [GuruController::class, 'bulkNilaiKuis'])->name('materi.jawaban-kuis.bulk-nilai');

        // Data Guru
        Route::get('data-guru', [GuruController::class, 'dataGuru'])->name('data-guru');

        // Data Siswa
        Route::get('data-siswa', function () {
            $path = storage_path('app/siswa_full.json');

            $json = file_get_contents($path);
            $json = trim($json, "\xEF\xBB\xBF");

            $data = json_decode($json, true);
            return view('guru.datasiswa', compact('data'));
        })->name('data-siswa');
    });


// ==========================
// Siswa Routes (API)
// ==========================
Route::prefix('api')->group(function () {
    Route::get('/siswa', function () {

        $path = storage_path('app/siswa_full.json');
        $json = file_get_contents($path);

        // Fix BOM
        $json = trim($json, "\xEF\xBB\xBF");

        $data = json_decode($json, true);

        if ($data === null) {
            return response()->json([
                'error' => 'JSON gagal didecode',
                'json_error' => json_last_error_msg(),
            ], 500);
        }

        return response()->json($data);
    });
});

require __DIR__.'/auth.php';