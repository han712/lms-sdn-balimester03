<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;

use App\Http\Controllers\Guru\DashboardController as GuruDashboard; 
use App\Http\Controllers\Guru\MateriController;
use App\Http\Controllers\Guru\QuizController;
use App\Http\Controllers\Guru\AbsensiController;
use App\Http\Controllers\Guru\GuruProfileController;

use App\Http\Controllers\Siswa\SiswaController;
use App\Http\Controllers\Siswa\SiswaProfileController;
use App\Http\Controllers\Siswa\DashboardController as SiswaDashboardController;
use App\Http\Controllers\Siswa\MateriController as SiswaMateri;
use App\Http\Controllers\Siswa\KuisController as SiswaKuis;
use App\Http\Controllers\Siswa\AbsensiController as SiswaAbsensi;


Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard redirect based on role
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        return match($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'guru' => redirect()->route('guru.materi.index'),
            'siswa' => redirect()->route('siswa.dashboard'),
            default => abort(403)
        };
    })->name('dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');        
        // User Management - CRUD Complete
        // Route::resource('users', AdminController::class)->except(['create', 'store']);
        // Route::get('users/create', [AdminController::class, 'create'])->name('users.create');
        // Route::post('users', [AdminController::class, 'store'])->name('users.store');
        
        // User Management
        Route::get('users/create', [AdminController::class, 'create'])->name('users.create');
        Route::post('users', [AdminController::class, 'store'])->name('users.store');
        Route::delete('users/bulk-delete', [AdminController::class, 'bulkDelete'])->name('users.bulk-delete');
        Route::post('users/bulk-toggle-active', [AdminController::class, 'bulkToggleActive'])->name('users.bulk-toggle-active');

        Route::resource('users', AdminController::class)->except(['create', 'store']);

        // User Actions
        Route::post('users/{user}/toggle-active', [AdminController::class, 'toggleActive'])
            ->name('users.toggle-active');
        Route::post('users/{user}/reset-password', [AdminController::class, 'resetPassword'])
            ->name('users.reset-password');
        
        // Bulk Actions
        // Route::post('users/bulk-delete', [AdminController::class, 'bulkDelete'])
        //     ->name('users.bulk-delete');
        // Route::post('users/bulk-toggle-active', [AdminController::class, 'bulkToggleActive'])
        //     ->name('users.bulk-toggle-active');
        
        // Import/Export
        Route::post('users/import', [AdminController::class, 'importUsers'])
            ->name('users.import');
        Route::get('users/export', [AdminController::class, 'exportUsers'])
            ->name('users.export');
        
        // View All Materi
        Route::get('materi', [AdminController::class, 'allMateri'])
            ->name('materi.index');
        
        // View All Absensi
        Route::get('absensi', [AdminController::class, 'allAbsensi'])
            ->name('absensi.index');
    });

/*
|--------------------------------------------------------------------------
| Guru Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:guru'])
    ->prefix('guru')
    ->name('guru.')
    ->group(function () {
        
        // 1. DASHBOARD
        // PENTING: Pakai [GuruDashboard::class, 'index'], BUKAN [GuruController::class, 'dashboard']
        Route::get('/dashboard', [GuruDashboard::class, 'index'])->name('dashboard');

        // 2. MANAJEMEN MATERI (CRUD)
        Route::resource('materi', MateriController::class);
        
        // Custom Actions Materi (Sekarang pakai MateriController, BUKAN GuruController lagi)
        Route::post('materi/{materi}/duplicate', [MateriController::class, 'duplicate'])->name('materi.duplicate');
        Route::post('materi/{materi}/toggle-publish', [MateriController::class, 'togglePublish'])->name('materi.toggle-publish');
        Route::post('materi/bulk-publish', [MateriController::class, 'bulkPublish'])->name('materi.bulk-publish');
        Route::post('materi/bulk-delete', [MateriController::class, 'bulkDelete'])->name('materi.bulk-delete');

        // 3. MANAJEMEN KUIS
        Route::get('kuis', [QuizController::class, 'index'])->name('kuis.index');
        Route::get('kuis/create', [QuizController::class, 'create'])->name('kuis.create');
        Route::post('kuis', [QuizController::class, 'store'])->name('kuis.store');

        Route::get('kuis/{materi}/edit', [QuizController::class, 'edit'])->name('kuis.edit');
        Route::put('kuis/{materi}', [QuizController::class, 'update'])->name('kuis.update');
        
        // Hasil & Penilaian Kuis
        Route::get('materi/{materi}/hasil-kuis', [QuizController::class, 'hasil'])->name('kuis.hasil');
        Route::get('kuis/{jawaban}/nilai', [QuizController::class, 'detailJawaban'])->name('kuis.detail');
        Route::put('kuis/{jawaban}/nilai', [QuizController::class, 'nilaiJawaban'])->name('kuis.nilai');

        // 4. MANAJEMEN ABSENSI (Pakai AbsensiController)
        Route::get('materi/{materi}/absensi', [AbsensiController::class, 'index'])->name('materi.absensi');
        Route::put('materi/{materi}/absensi', [AbsensiController::class, 'update'])->name('materi.absensi.update');
        Route::get('laporan/absensi', [AbsensiController::class, 'exportRekap'])->name('laporan.absensi');

        // 5. PROFILE GURU
        Route::get('profile', [GuruProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [GuruProfileController::class, 'update'])->name('profile.update');
        Route::put('password', [GuruProfileController::class, 'updatePassword'])->name('password.update');
    });

// Route::middleware(['auth', 'verified', 'role:guru'])

//         ->prefix('guru')
//         ->name('guru.') // Ini memberi awalan nama route 'guru.'
//         ->group(function () {
        
//         // 1. Dashboard
//         Route::get('/dashboard', [GuruController::class, 'dashboard'])->name('dashboard');

//         // 2. Materi CRUD (PENTING: Ini yang memperbaiki error kamu)
//         // Fungsi resource ini otomatis membuat route:
//         // guru.materi.index, guru.materi.create, guru.materi.store, dll.
//         // Pastikan menggunakan GuruController::class
        
//         // --- MANAJEMEN MATERI (BAHAN AJAR) ---
//         Route::resource('materi', MateriController::class);
//         // Route::resource('materi', GuruController::class);
        
//         // CUSTOM ACTIONS MATERI
//         Route::post('materi/{materi}/duplicate', [MateriController::class, 'duplicate'])->name('materi.duplicate');
//         Route::post('materi/{materi}/toggle-publish', [MateriController::class, 'togglePublish'])->name('materi.toggle-publish');
//         Route::post('materi/bulk-publish', [MateriController::class, 'bulkPublish'])->name('materi.bulk-publish');
//         Route::post('materi/bulk-delete', [MateriController::class, 'bulkDelete'])->name('materi.bulk-delete');
        
//         // KUIS & GRADING (PENILAIAN
//         Route::get('kuis/create', [QuizController::class, 'create'])->name('kuis.create');
//         Route::post('kuis', [QuizController::class, 'store'])->name('kuis.store');

//         // ABSENSI
//         Route::get('materi/{materi}/absensi', [AbsensiController::class, 'index'])->name('materi.absensi');
//         Route::put('materi/{materi}/absensi', [AbsensiController::class, 'update'])->name('materi.absensi.update');
//         Route::get('laporan/absensi', [AbsensiController::class, 'exportRekap'])->name('laporan.absensi');
        
//         // 3. Custom Routes Materi (Duplicate & Publish)
//         // Route::post('materi/{materi}/duplicate', [GuruController::class, 'duplicate'])->name('materi.duplicate');
//         // Route::post('materi/{materi}/toggle-publish', [GuruController::class, 'togglePublish'])->name('materi.toggle-publish');
//         // Route::post('materi/bulk-publish', [GuruController::class, 'bulkPublish'])->name('materi.bulk-publish');
//         // Route::post('materi/bulk-delete', [GuruController::class, 'bulkDelete'])->name('materi.bulk-delete');

//         // 4. Absensi Management
//         // Route::get('materi/{materi}/absensi', [GuruController::class, 'absensi'])->name('materi.absensi');
//         // Route::put('materi/{materi}/absensi', [GuruController::class, 'updateAbsensi'])->name('materi.absensi.update');
//         // Route::get('materi/{materi}/hasil-kuis', [GuruController::class, 'hasilKuis'])->name('kuis.hasil');
//         // Route::get('laporan/absensi', [GuruController::class, 'exportRekapAbsensi'])->name('laporan.absensi');

//         // 5. Kuis & Penilaian
//         // Route::get('materi/{materi}/hasil-kuis', [GuruController::class, 'hasilKuis'])->name('kuis.hasil');
//         // Route::get('kuis/{jawaban}/nilai', [GuruController::class, 'detailJawaban'])->name('kuis.detail'); 
//         // Route::put('kuis/{jawaban}/nilai', [GuruController::class, 'nilaiJawaban'])->name('kuis.nilai');

//         // 6. Profil
//         Route::get('profile', [GuruProfileController::class, 'edit'])->name('profile.edit');
//         Route::put('profile', [GuruProfileController::class, 'update'])->name('profile.update');
//         Route::put('password', [GuruProfileController::class, 'updatePassword'])->name('password.update');
//     });

/*
|--------------------------------------------------------------------------
| Siswa Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:siswa'])
    ->prefix('siswa')
    ->name('siswa.')
    ->group(function () {
        
        // 1. Dashboard
        Route::get('/dashboard', [SiswaDashboardController::class, 'index'])->name('dashboard');

        // 2. Profile Management (SiswaProfileController)
        Route::get('/profile', [SiswaProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [SiswaProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [SiswaProfileController::class, 'updatePassword'])->name('password.update');
        Route::delete('/profile/avatar', [SiswaProfileController::class, 'deleteAvatar'])->name('profile.delete-avatar');
        Route::get('/profile/activity', [SiswaProfileController::class, 'activityLog'])->name('profile.activity');

        // 3. Materi & Kuis (SiswaController)
        Route::get('/materi', [SiswaMateri::class, 'index'])->name('materi.index');
        Route::get('/materi/{materi}', [SiswaMateri::class, 'show'])->name('materi.show');
        
        // Submit Kuis
        Route::post('/materi/{materi}/submit-kuis', [SiswaController::class, 'submitKuis'])->name('materi.submit-kuis');
        
        // 4. Riwayat
        Route::post('/materi/{materi}/submit-kuis', [SiswaKuis::class, 'store'])->name('materi.submit-kuis');
        Route::get('/riwayat-kuis', [SiswaKuis::class, 'history'])->name('riwayat-kuis');

        Route::get('/riwayat-absensi', [SiswaAbsensi::class, 'index'])->name('riwayat-absensi');
    });


require __DIR__.'/auth.php';
