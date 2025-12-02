<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Materi;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;


class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_admin' => User::Admin()->count(),
            'total_guru' => User::guru()->count(),
            'total_siswa' => User::siswa()->count(),
            'total_materi' => Materi::count(),
            'total_kuis' => Materi::where('tipe', 'kuis')->count(),
            'total_absensi' => Absensi::count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
        ];

        // Recent activities
        $recentUsers = User::latest()->take(5)->get();
        $recentMateri = Materi::with('guru')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentMateri'));
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by kelas (for siswa)
        if ($request->filled('kelas')) {
            $query->where('kelas', $request->kelas);
        }

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $users = $query->paginate(20)->withQueryString();

        // Statistics
        $stats = [
            'total' => User::count(),
            'admin' => User::Admin()->count(),
            'guru' => User::guru()->count(),
            'siswa' => User::siswa()->count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'guru', 'siswa'])],
            'nisn' => ['nullable', 'string', 'max:20', 'unique:users,nisn'],
            'nip' => ['nullable', 'string', 'max:30', 'unique:users,nip'],
            'kelas' => ['required_if:role,siswa', 'nullable', Rule::in(['1', '2', '3', '4', '5', '6'])],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.unique' => 'Email sudah digunakan',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'role.required' => 'Role wajib dipilih',
            'nisn.unique' => 'NISN sudah digunakan',
            'nip.unique' => 'NIP sudah digunakan',
            'kelas.required_if' => 'Kelas wajib diisi untuk siswa',
        ]);

        DB::beginTransaction();
        
        try {
            $validated['password'] = Hash::make($validated['password']);
            $validated['is_active'] = $request->has('is_active') ? true : false;

            // Clear unnecessary fields based on role
            if ($validated['role'] !== 'siswa') {
                $validated['nisn'] = null;
                $validated['kelas'] = null;
            }
            if ($validated['role'] !== 'guru') {
                $validated['nip'] = null;
            }

            $user = User::create($validated);

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('success', 'User berhasil ditambahkan');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()
                ->with('error', 'Gagal menambahkan user: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load(['materi', 'absensi.materi', 'jawabanKuis.materi']);
        
        // Statistics
        $stats = [];
        
        if ($user->isGuru()) {
            $stats = [
                'total_materi' => $user->materi()->count(),
                'materi_published' => $user->materi()->where('is_published', true)->count(),
                'total_kuis' => $user->materi()->where('tipe', 'kuis')->count(),
            ];
        } elseif ($user->isSiswa()) {
            $stats = [
                'total_absensi' => $user->absensi()->count(),
                'hadir' => $user->absensi()->where('status', 'hadir')->count(),
                'tidak_hadir' => $user->absensi()->where('status', 'tidak_hadir')->count(),
                'total_kuis_dijawab' => $user->jawabanKuis()->count(),
                'kuis_dinilai' => $user->jawabanKuis()->sudahDinilai()->count(),
                'rata_nilai' => $user->jawabanKuis()->sudahDinilai()->avg('nilai'),
            ];
        }
        
        return view('admin.users.show', compact('user', 'stats'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'guru', 'siswa'])],
            'nisn' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'nip' => ['nullable', 'string', 'max:30', Rule::unique('users')->ignore($user->id)],
            'kelas' => ['required_if:role,siswa', 'nullable', Rule::in(['1', '2', '3', '4', '5', '6'])],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.unique' => 'Email sudah digunakan',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'role.required' => 'Role wajib dipilih',
            'nisn.unique' => 'NISN sudah digunakan',
            'nip.unique' => 'NIP sudah digunakan',
            'kelas.required_if' => 'Kelas wajib diisi untuk siswa',
        ]);

        DB::beginTransaction();
        
        try {
            // Update password hanya jika diisi
            if ($request->filled('password')) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $validated['is_active'] = $request->has('is_active') ? true : false;

            // Clear unnecessary fields based on role
            if ($validated['role'] !== 'siswa') {
                $validated['nisn'] = null;
                $validated['kelas'] = null;
            }
            if ($validated['role'] !== 'guru') {
                $validated['nip'] = null;
            }

            $user->update($validated);

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('success', 'User berhasil diupdate');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()
                ->with('error', 'Gagal mengupdate user: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri');
        }

        // Prevent deleting last super admin
        if ($user->isAdmin() && User::Admin()->count() === 1) {
            return back()->with('error', 'Tidak dapat menghapus super admin terakhir');
        }

        DB::beginTransaction();
        
        try {
            $name = $user->name;
            $user->delete();

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('success', "User '{$name}' berhasil dihapus");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete users.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        DB::beginTransaction();
        
        try {
            // Filter out current user and check super admin count
            $userIds = collect($request->user_ids)->filter(function($id) {
                return $id != auth()->id();
            });

            $users = User::whereIn('id', $userIds)->get();
            $count = 0;

            foreach ($users as $user) {
                // Don't delete last super admin
                if ($user->isAdmin() && User::Admin()->count() <= 1) {
                    continue;
                }
                
                $user->delete();
                $count++;
            }

            DB::commit();

            return back()->with('success', "{$count} user berhasil dihapus");
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }

    /**
     * Toggle user active status.
     */
    public function toggleActive(User $user)
    {
        // Prevent deactivating self
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menonaktifkan akun sendiri');
        }

        // Prevent deactivating last super admin
        if ($user->isAdmin() && $user->is_active && User::Admin()->where('is_active', true)->count() === 1) {
            return back()->with('error', 'Tidak dapat menonaktifkan super admin terakhir');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        return back()->with('success', "User berhasil {$status}");
    }

    /**
     * Bulk toggle active status.
     */
    public function bulkToggleActive(Request $request)
    {
        $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
            'is_active' => ['required', 'boolean'],
        ]);

        DB::beginTransaction();
        
        try {
            // Filter out current user
            $userIds = collect($request->user_ids)->filter(function($id) {
                return $id != auth()->id();
            });

            User::whereIn('id', $userIds)
                ->update(['is_active' => $request->is_active]);

            DB::commit();

            $status = $request->is_active ? 'diaktifkan' : 'dinonaktifkan';
            
            return back()->with('success', count($userIds) . " user berhasil {$status}");
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengupdate status: ' . $e->getMessage());
        }
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        return back()->with('success', 'Password berhasil direset');
    }

    /**
     * View all materi from all guru.
     */
    public function allMateri(Request $request)
    {
        $query = Materi::with(['guru']);

        // Filters
        if ($request->filled('guru_id')) {
            $query->where('guru_id', $request->guru_id);
        }

        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        if ($request->filled('kelas')) {
            $query->where('kelas', $request->kelas);
        }

        if ($request->filled('is_published')) {
            $query->where('is_published', $request->is_published === '1');
        }

        if ($request->filled('search')) {
            $query->where('judul', 'like', "%{$request->search}%");
        }

        $materi = $query->orderBy('created_at', 'desc')->paginate(20);
        $guruList = User::guru()->orderBy('name')->get();

        return view('admin.materi.index', compact('materi', 'guruList'));
    }

    /**
     * View all absensi.
     */
    public function allAbsensi(Request $request)
    {
        $query = Absensi::with(['siswa', 'materi.guru']);

        // Filters
        if ($request->filled('kelas')) {
            $query->byKelas($request->kelas);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('waktu_akses', [
                $request->start_date,
                $request->end_date
            ]);
        }

        $absensi = $query->orderBy('waktu_akses', 'desc')->paginate(20);

        return view('admin.absensi.index', compact('absensi'));
    }

    /**
     * Import users from CSV.
     */
    public function importUsers(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
            'role' => ['required', Rule::in(['guru', 'siswa'])],
        ]);

        DB::beginTransaction();
        
        try {
            $file = $request->file('file');
            $csvData = array_map('str_getcsv', file($file->getRealPath()));
            $header = array_shift($csvData);
            
            $imported = 0;
            $errors = [];

            foreach ($csvData as $index => $row) {
                try {
                    $data = array_combine($header, $row);
                    
                    User::create([
                        'name' => $data['name'] ?? $data['nama'],
                        'email' => $data['email'],
                        'password' => Hash::make($data['password'] ?? 'password'),
                        'role' => $request->role,
                        'nisn' => $data['nisn'] ?? null,
                        'nip' => $data['nip'] ?? null,
                        'kelas' => $data['kelas'] ?? null,
                        'is_active' => true,
                    ]);
                    
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Baris " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "{$imported} user berhasil diimport";
            if (count($errors) > 0) {
                $message .= ". " . count($errors) . " error: " . implode(', ', array_slice($errors, 0, 3));
            }

            return back()->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengimport user: ' . $e->getMessage());
        }
    }

    /**
     * Export users to CSV.
     */
    public function exportUsers(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('kelas')) {
            $query->where('kelas', $request->kelas);
        }

        $users = $query->get();

        $filename = "users_export_" . now()->format('YmdHis') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['ID', 'Nama', 'Email', 'Role', 'NISN', 'NIP', 'Kelas', 'Status', 'Dibuat']);

            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->role_name,
                    $user->nisn ?? '-',
                    $user->nip ?? '-',
                    $user->kelas ?? '-',
                    $user->is_active ? 'Aktif' : 'Nonaktif',
                    $user->created_at->format('d/m/Y'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
