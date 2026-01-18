<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Materi;
use App\Models\Absensi;
// Import Form Request yang baru dibuat
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest; 
// Import Service
use App\Services\Admin\UserService; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    protected $userService;

    // Inject Service via Constructor
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function dashboard()
    {
        // Logic statistik query tetap disini agar controller 'terbaca'
        // atau bisa dipindah ke DashboardService jika ingin lebih bersih lagi.
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
        // Logic pencarian & filter dipindah ke Service
        $users = $this->userService->getFilteredUsers($request);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    // Menggunakan StoreUserRequest (Validasi otomatis berjalan sebelum masuk function)
    public function store(StoreUserRequest $request)
    {
        try {
            // Logic simpan & sanitasi data dipindah ke Service
            $this->userService->createUser($request->validated());
            
            return redirect()->route('admin.users.index')
                ->with('success', 'User berhasil ditambahkan dengan data lengkap.');
        } catch (\Exception $e) {
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

    // Menggunakan UpdateUserRequest
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            // Logic update dipindah ke Service
            $this->userService->updateUser($user, $request->validated());

            return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function destroy(User $user)
    {
        try {
            $name = $user->name;
            // Logic pengecekan "cannot delete self" dipindah ke Service
            $this->userService->deleteUser($user);

            return redirect()->route('admin.users.index')
                ->with('success', "User '{$name}' berhasil dihapus");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // --- BULK ACTION ---
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        try {
            DB::transaction(function() use ($request) {
                // Filter ID (Logic ini bisa masuk service "bulkDeleteUsers")
                $userIds = collect($request->user_ids)->filter(fn($id) => $id != auth()->id());
                
                $users = User::whereIn('id', $userIds)->get();
                foreach ($users as $user) {
                    if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) continue;
                    $user->delete();
                }
            });

            return back()->with('success', "User terpilih berhasil dihapus");
        } catch (\Exception $e) {
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

        try {
            $userIds = collect($request->user_ids)->filter(fn($id) => $id != auth()->id());
            User::whereIn('id', $userIds)->update(['is_active' => $request->is_active]);

            $status = $request->is_active ? 'diaktifkan' : 'dinonaktifkan';
            return back()->with('success', count($userIds) . " user berhasil {$status}");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function toggleActive(User $user)
    {
        if ($user->id === auth()->id()) return back()->with('error', 'Tidak dapat menonaktifkan akun sendiri');
        
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        return back()->with('success', "User berhasil {$status}");
    }

    public function resetPassword(Request $request, User $user)
    {
       $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update(['password' => \Illuminate\Support\Facades\Hash::make($validated['password'])]);
        return back()->with('success', 'Password berhasil direset');
    }

    // --- MATERI SECTION ---
    
    public function allMateri(Request $request)
    {
        // [PERUBAHAN]: Saya menghapus kode unreachable yang ada di file asli Anda
        // File asli melakukan return view 2x, yang kedua tidak akan pernah jalan.
        
        $query = Materi::with('guru');

        if ($request->filled('guru_id')) $query->where('guru_id', $request->guru_id);
        if ($request->filled('tipe')) $query->where('tipe', $request->tipe);
        if ($request->filled('kelas')) $query->where('kelas', $request->kelas);
        if ($request->filled('is_published')) $query->where('is_published', $request->is_published === '1');
        if ($request->filled('search')) $query->where('judul', 'like', "%{$request->search}%");

        $materi = $query->latest()->paginate(20); // Konsisten menggunakan latest()
        $guruList = User::where('role', 'guru')->orderBy('name')->get();

        return view('admin.materi.index', compact('materi', 'guruList'));
    }

    // [PERUBAHAN]: Menambahkan parameter Request $request yang HILANG di kode asli
    public function allAbsensi(Request $request) 
    {
        $query = Absensi::with(['siswa', 'materi.guru']);

        if ($request->filled('kelas')) $query->byKelas($request->kelas); // Pastikan scope byKelas ada di Model
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('waktu_akses', [$request->start_date, $request->end_date]);
        }

        $absensi = $query->orderBy('waktu_akses', 'desc')->paginate(20);

        return view('admin.absensi.index', compact('absensi'));
    }

    // --- IMPORT / EXPORT (Bisa dipindah ke ExportService, tapi disini oke untuk sekarang) ---
    
    public function exportUsers(Request $request)
    {
        // Untuk menjaga kode pendek, logic CSV bisa tetap disini atau dipindah ke Service
        // Jika dipindah ke service: return $this->userService->exportUsersToCsv($request);
        
        $query = User::query();
        if ($request->filled('role')) $query->where('role', $request->role);
        if ($request->filled('kelas')) $query->where('kelas', $request->kelas);
        $users = $query->get();

        $filename = "users_export_" . now()->format('YmdHis') . ".csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream(function() use ($users) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM fix excel
            fputcsv($file, ['ID', 'Nama', 'Email', 'Role', 'NISN', 'NIP', 'Kelas', 'Status', 'Dibuat']);
            
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id, $user->name, $user->email, $user->role, // role_name jika accessor
                    $user->nisn ?? '-', $user->nip ?? '-', $user->kelas ?? '-',
                    $user->is_active ? 'Aktif' : 'Nonaktif', $user->created_at->format('d/m/Y'),
                ]);
            }
            fclose($file);
        }, 200, $headers);
    }
    
    public function importUsers(Request $request)
    {
        // Logic ini sebaiknya dipindah ke Service untuk kerapihan maksimal
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
            'role' => ['required', Rule::in(['guru', 'siswa'])],
        ]);

        try {
            $file = $request->file('file');
            $csvData = array_map('str_getcsv', file($file->getRealPath()));
            $header = array_shift($csvData);
            
            $imported = 0;
            $errors = [];

            DB::beginTransaction();
            foreach ($csvData as $index => $row) {
                try {
                    // Validasi panjang array sebelum combine
                    if (count($header) !== count($row)) continue;
                    
                    $data = array_combine($header, $row);
                    // Gunakan service create jika memungkinkan, atau manual disini
                    User::create([
                        'name' => $data['name'] ?? $data['nama'],
                        'email' => $data['email'],
                        'password' => \Illuminate\Support\Facades\Hash::make($data['password'] ?? 'password'),
                        'role' => $request->role,
                        'nisn' => $data['nisn'] ?? null,
                        'nip' => $data['nip'] ?? null,
                        'kelas' => $data['kelas'] ?? null,
                        'is_active' => true,
                    ]);
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Baris " . ($index + 2);
                }
            }
            DB::commit();

            $msg = "{$imported} user diimport.";
            if ($errors) $msg .= " Error pada: " . implode(', ', array_slice($errors, 0, 5));
            
            return back()->with('success', $msg);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function editMateri(Materi $materi)
    {
        return view('admin.materi.edit', compact('materi'));
    }

    public function updateMateri(Request $request, Materi $materi)
    {
        // Validasi bisa dipindah ke UpdateMateriRequest
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'kelas' => ['required', Rule::in(config('lms.daftar_kelas'))],
            'tipe' => 'required|in:materi,kuis',
            'is_published' => 'required|boolean',
            'keterangan' => 'nullable|string',
            'link' => 'nullable|url',
            'video' => 'nullable|url',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,jpg,png|max:10240',
        ]);

        try {
            if ($request->hasFile('file')) {
                if ($materi->file && Storage::disk('public')->exists($materi->file)) {
                    Storage::disk('public')->delete($materi->file);
                }
                $validated['file'] = $request->file('file')->store('materi', 'public');
            }

            $materi->update($validated);
            return redirect()->route('admin.materi.index')->with('success', 'Materi berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function deleteMateri(Materi $materi)
    {
        try {
            if ($materi->file && Storage::disk('public')->exists($materi->file)) {
                Storage::disk('public')->delete($materi->file);
            }
            $materi->delete();
            return back()->with('success', 'Materi dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
}