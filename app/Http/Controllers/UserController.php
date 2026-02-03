<?php

namespace App\Http\Controllers;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    // 1. TAMPILKAN DAFTAR USER
    public function index()
    {
        // Ambil data user terbaru, paginate 10 per halaman
        $users = User::latest()->paginate(10);
        return view('users.index', compact('users'));
    }

    // 2. FORM TAMBAH USER
    public function create()
    {
        return view('users.create');
    }

    // 3. SIMPAN USER BARU
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,petugas,wali_kelas'],
        ]);

        $newUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // Log aktivitas penambahan user
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'TAMBAH USER',
            'description' => "Menambahkan user baru: {$newUser->name} ({$newUser->email}) dengan role {$newUser->role}",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan!');
    }

    // 4. FORM EDIT USER
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    // 5. UPDATE USER
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id], // Abaikan email milik sendiri
            'role' => ['required', 'in:admin,petugas,wali_kelas'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()], // Password boleh kosong
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        // Variabel untuk tracking perubahan
        $changes = [];
        $passwordReset = false;

        // Deteksi perubahan nama
        if ($user->name !== $request->name) {
            $changes[] = "nama dari '{$user->name}' menjadi '{$request->name}'";
        }

        // Deteksi perubahan email
        if ($user->email !== $request->email) {
            $changes[] = "email dari '{$user->email}' menjadi '{$request->email}'";
        }

        // Deteksi perubahan role
        if ($user->role !== $request->role) {
            $changes[] = "role dari '{$user->role}' menjadi '{$request->role}'";
        }

        // Cek apakah password diisi? Kalau iya, update. Kalau kosong, biarkan password lama.
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
            $passwordReset = true;
        }

        $user->update($data);

        // Log aktivitas update user
        if (!empty($changes) || $passwordReset) {
            $description = "Memperbarui data user: {$user->name}";
            
            if (!empty($changes)) {
                $description .= " - Perubahan: " . implode(', ', $changes);
            }
            
            if ($passwordReset) {
                $description .= " - PASSWORD DIRESET oleh Admin";
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'UPDATE USER',
                'description' => $description,
                'ip_address' => request()->ip(),
            ]);
        }

        return redirect()->route('users.index')->with('success', 'Data user diperbarui!');
    }

    // 6. HAPUS USER
    public function destroy(User $user)
    {
        // Proteksi: Admin tidak boleh menghapus dirinya sendiri
        if ($user->id == auth()->user()->id) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
        }

        // Simpan data user sebelum dihapus untuk logging
        $userName = $user->name;
        $userEmail = $user->email;
        $userRole = $user->role;

        // Soft delete user
        $user->delete();

        // Log aktivitas penghapusan user
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'HAPUS USER',
            'description' => "Menghapus user: {$userName} ({$userEmail}) dengan role {$userRole} [SOFT DELETE]",
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'User berhasil dihapus.');
    }
}