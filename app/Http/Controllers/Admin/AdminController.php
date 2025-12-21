<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Materi;
use App\Models\Absensi;
use App\Models\JawabanKuis;
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
            'total_admin' => User::where('role', 'admin')->count(),
            'total_guru' => User::where('role', 'guru')->count(),
            'total_siswa' => User::where('role', 'siswa')->count(),
            'total_materi' => Materi::count(),
            'total_kuis' => Materi::where('tipe', 'kuis')->count(),
            'total_absensi' => Absensi::count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
        ];

        $recentUsers = User::latest()->take(5)->get();
        $recentMateri = Materi::with('guru')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentMateri'));
    }

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('kelas')) {
            $query->where('kelas', $request->kelas);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    // --- PERBAIKAN DI METHOD STORE (GABUNGAN LOGIKA LU + FITUR BARU) ---
    public function store(Request $request)
    {
        // 1. Validasi Dasar (Wajib untuk semua)
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'guru', 'siswa'])],
            'is_active' => ['nullable', 'boolean'], // Checkbox returns '1' or null
        ];

        // 2. Validasi Dinamis Berdasarkan Role (Ini tambahan baru yang penting)
        if ($request->role == 'siswa') {
            $rules += [
                'nisn' => 'required|unique:users,nisn|max:20',
                'nis' => 'nullable|unique:users,nis|max:20',
                'kelas' => ['required', Rule::in(['1', '2', '3', '4', '5', '6'])],
                'tahun_masuk' => 'required|digits:4',
                'jenis_kelamin' => 'required|in:L,P',
                'nama_ibu' => 'required|string',
                'tanggal_lahir' => 'required|date',
            ];
        } elseif ($request->role == 'guru') {
            $rules += [
                'nip' => 'required|unique:users,nip|max:30',
                'status_kepegawaian' => 'required|in:PNS,GTY,GTT',
                'pendidikan_terakhir' => 'required|string',
                'mata_pelajaran_utama' => 'required|string',
            ];
        } elseif ($request->role == 'admin') {
            $rules += [
                'id_pegawai' => 'nullable|unique:users,id_pegawai',
                'posisi' => 'nullable|string',
            ];
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            $validated['password'] = Hash::make($validated['password']);
            // Convert checkbox value to boolean
            $validated['is_active'] = $request->has('is_active') || $request->input('is_active') == '1';

            // Bersihkan field yang tidak relevan dengan role yang dipilih
            if ($validated['role'] !== 'siswa') {
                $validated['nisn'] = null;
                $validated['kelas'] = null;
                // Hapus field siswa lain dari array validated jika ada
                unset($validated['nis'], $validated['tahun_masuk'], $validated['nama_ibu']); 
            }
            if ($validated['role'] !== 'guru') {
                $validated['nip'] = null;
                // Hapus field guru lain
                unset($validated['status_kepegawaian'], $validated['mata_pelajaran_utama']);
            }

            User::create($validated);

            DB::commit();
            return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan dengan data lengkap.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function show(User $user)
    {
        $user->load(['materi', 'absensi.materi', 'jawabanKuis.materi']);
        
        $stats = [];
        if ($user->role == 'guru') {
            $stats = [
                'total_materi' => $user->materi()->count(),
                'materi_published' => $user->materi()->where('is_published', true)->count(),
                'total_kuis' => $user->materi()->where('tipe', 'kuis')->count(),
            ];
        } elseif ($user->role == 'siswa') {
            $stats = [
                'total_absensi' => $user->absensi()->count(),
                'hadir' => $user->absensi()->where('status', 'hadir')->count(),
                'total_kuis_dijawab' => $user->jawabanKuis()->count(),
                'rata_nilai' => $user->jawabanKuis()->whereNotNull('nilai')->avg('nilai') ?? 0,
            ];
        }
        
        return view('admin.users.show', compact('user', 'stats'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    // --- PERBAIKAN DI METHOD UPDATE (GABUNGAN LOGIKA LU + FITUR BARU) ---
    public function update(Request $request, User $user)
    {
        // 1. Validasi Dasar
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'guru', 'siswa'])],
            'is_active' => ['nullable', 'boolean'],
        ];

        // 2. Validasi Dinamis saat Update
        if ($request->role == 'siswa') {
            $rules += [
                'nisn' => ['required', Rule::unique('users')->ignore($user->id), 'max:20'],
                'nis' => ['nullable', Rule::unique('users')->ignore($user->id)],
                'kelas' => ['required', Rule::in(['1', '2', '3', '4', '5', '6'])],
                'tahun_masuk' => 'required|digits:4',
            ];
        } elseif ($request->role == 'guru') {
            $rules += [
                'nip' => ['required', Rule::unique('users')->ignore($user->id), 'max:30'],
                'status_kepegawaian' => 'required|in:PNS,GTY,GTT',
            ];
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            if ($request->filled('password')) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $validated['is_active'] = $request->has('is_active') || $request->input('is_active') == '1';

            // Bersihkan data yang tidak sesuai role
            if ($validated['role'] !== 'siswa') {
                $validated['nisn'] = null;
                $validated['kelas'] = null;
            }
            if ($validated['role'] !== 'guru') {
                $validated['nip'] = null;
            }

            $user->update($validated);

            DB::commit();
            return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

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

    // --- FITUR BULK ACTION & EXPORT (TETAP DIPERTAHANKAN) ---
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

    public function toggleActive(User $user)
    {
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

    public function allMateri(Request $request)
    {
        $query = Materi::with('guru');
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
        $materi = $query->latest()->paginate(20);
        $guruList = User::where('role', 'guru')->get();
        return view('admin.materi.index', compact('materi', 'guruList'));
    }

    public function allAbsensi()
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
}