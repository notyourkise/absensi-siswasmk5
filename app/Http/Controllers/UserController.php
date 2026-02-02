<?php

namespace App\Http\Controllers;

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

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
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

        // Cek apakah password diisi? Kalau iya, update. Kalau kosong, biarkan password lama.
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Data user diperbarui!');
    }

    // 6. HAPUS USER
    public function destroy(User $user)
    {
        // Proteksi: Admin tidak boleh menghapus dirinya sendiri
        if ($user->id == auth()->user()->id) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
        }

        $user->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }
}