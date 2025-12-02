<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Siswa\UpdateProfileRequest;
use App\Http\Requests\Siswa\UpdatePasswordRequest as SiswaProfileUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;

class SiswaProfileController extends Controller
{
    /**
     * Show the profile edit form
     */
    public function edit()
    {
        $siswa = auth()->user();
        
        // Get activity statistics
        $stats = [
            'total_materi_dibuka' => \App\Models\Absensi::where('siswa_id', $siswa->id)->count(),
            'total_kuis_dikerjakan' => \App\Models\JawabanKuis::where('siswa_id', $siswa->id)->count(),
            'rata_rata_nilai' => \App\Models\JawabanKuis::where('siswa_id', $siswa->id)
                ->whereNotNull('nilai')
                ->avg('nilai') ?? 0,
            'member_since' => $siswa->created_at->diffForHumans(),
        ];
        
        return view('siswa.profile.edit', compact('siswa', 'stats'));
    }

    /**
     * Update profile information
     */
    public function update(SiswaProfileUpdateRequest $request)
    {
        $siswa = auth()->user();

        DB::beginTransaction();
        try {
            // Handle avatar upload if provided
            if ($request->hasFile('avatar')) {
                // Delete old avatar
                if ($siswa->avatar && Storage::disk('public')->exists($siswa->avatar)) {
                    Storage::disk('public')->delete($siswa->avatar);
                }

                // Store new avatar with proper naming
                $file = $request->file('avatar');
                $extension = $file->getClientOriginalExtension();
                $fileName = 'siswa_' . $siswa->id . '_' . time() . '.' . $extension;
                $avatarPath = $file->storeAs('avatars', $fileName, 'public');
                
                $siswa->avatar = $avatarPath;
            }

            // Update basic information
            $siswa->fill($request->validated());

            // Reset email verification if email changed
            if ($siswa->isDirty('email')) {
                $siswa->email_verified_at = null;
            }

            $siswa->save();

            DB::commit();

            return redirect()
                ->route('siswa.profile.edit')
                ->with('success', 'Profil berhasil diperbarui! 🎉');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded file if transaction fails
            if (isset($avatarPath) && Storage::disk('public')->exists($avatarPath)) {
                Storage::disk('public')->delete($avatarPath);
            }

            return back()
                ->with('error', 'Terjadi kesalahan saat memperbarui profil: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update password
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
            ],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        try {
            $siswa = auth()->user();
            $siswa->update([
                'password' => Hash::make($validated['password'])
            ]);

            return redirect()
                ->route('siswa.profile.edit')
                ->with('success', 'Password berhasil diperbarui! 🔒');

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Terjadi kesalahan saat memperbarui password: ' . $e->getMessage());
        }
    }

    /**
     * Delete avatar
     */
    public function deleteAvatar()
    {
        $siswa = auth()->user();

        try {
            if ($siswa->avatar && Storage::disk('public')->exists($siswa->avatar)) {
                Storage::disk('public')->delete($siswa->avatar);
            }

            $siswa->update(['avatar' => null]);

            return back()->with('success', 'Foto profil berhasil dihapus.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat menghapus foto profil.');
        }
    }

    /**
     * Show activity log
     */
    public function activityLog()
    {
        $siswa = auth()->user();
        
        // Get recent activities
        $activities = collect();
        
        // Recent kuis submissions
        $recentKuis = \App\Models\JawabanKuis::where('siswa_id', $siswa->id)
            ->with('materi:id,judul')
            ->latest()
            ->take(10)
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'kuis',
                    'description' => 'Mengumpulkan kuis: ' . $item->materi->judul,
                    'date' => $item->created_at,
                    'icon' => 'file-text',
                    'color' => 'primary'
                ];
            });
        
        // Recent absensi
        $recentAbsensi = \App\Models\Absensi::where('siswa_id', $siswa->id)
            ->with('materi:id,judul')
            ->latest()
            ->take(10)
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'absensi',
                    'description' => 'Hadir di materi: ' . $item->materi->judul,
                    'date' => $item->created_at,
                    'icon' => 'check-circle',
                    'color' => 'success'
                ];
            });
        
        // Merge and sort by date
        $activities = $activities->merge($recentKuis)
            ->merge($recentAbsensi)
            ->sortByDesc('date')
            ->take(20);
        
        return view('siswa.profile.activity', compact('activities'));
    }
}