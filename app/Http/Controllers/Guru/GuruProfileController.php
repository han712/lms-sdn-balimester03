<?php

namespace App\Http\Controllers\Guru;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\UpdateProfileRequest;
use App\Http\Requests\Guru\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class GuruProfileController extends Controller
{
    public function edit(): View
    {
        // Menggunakan view edit.blade.php yang sudah Anda buat
        // Pastikan file tersebut ada di resources/views/guru/profile/edit.blade.php
        // Atau sesuaikan path-nya jika ada di folder lain.
        return view('guru.edit', [
            'user' => auth()->user()
        ]);
        // Catatan: Sesuaikan nama view ('guru.materi.edit_profile') 
        // dengan lokasi file edit.blade.php Anda sebenarnya.
        // Jika file Anda bernama 'edit.blade.php' dan ada di folder 'guru', 
        // maka gunakan 'guru.edit'.
    }

    /**
     * Update informasi data diri (Nama & NIP).
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        try {
            $user = $request->user();
            
            // Validasi sudah ditangani oleh UpdateProfileRequest
            // Kita tinggal ambil data yang sudah divalidasi
            $validated = $request->validated();

            $user->fill($validated);

            // Cek jika ada perubahan (opsional, untuk efisiensi database)
            if ($user->isDirty('email')) {
                $user->email_verified_at = null; // Jika email berubah, perlu verifikasi ulang
            }

            $user->save();

            Log::info("Guru ID {$user->id} updated their profile.");

            return back()->with('success', 'Profil berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error("Failed updating profile for Guru ID " . auth()->id() . ": " . $e->getMessage());
            
            return back()->with('error', 'Terjadi kesalahan saat memperbarui profil.');
        }
    }

    /**
     * Update password user.
     */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        try {
            $user = $request->user();
            $validated = $request->validated();

            // Update password
            $user->update([
                'password' => Hash::make($validated['new_password']),
            ]);

            Log::info("Guru ID {$user->id} changed their password.");

            return back()->with('success', 'Password berhasil diubah.');

        } catch (\Exception $e) {
            Log::error("Failed updating password for Guru ID " . auth()->id() . ": " . $e->getMessage());

            return back()->with('error', 'Gagal mengubah password.');
        }
    }
}
