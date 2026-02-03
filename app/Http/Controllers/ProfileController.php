<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash; // [PENTING] Tambahan Import untuk Hash Password

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Tracking perubahan
        $changes = [];
        $oldName = $request->user()->name;
        $oldEmail = $request->user()->email;

        $request->user()->fill($request->validated());

        // Deteksi perubahan nama
        if ($request->user()->isDirty('name')) {
            $changes[] = "nama dari '{$oldName}' menjadi '{$request->user()->name}'";
        }

        // Deteksi perubahan email
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
            $changes[] = "email dari '{$oldEmail}' menjadi '{$request->user()->email}'";
        }

        $request->user()->save();

        // Log aktivitas jika ada perubahan
        if (!empty($changes)) {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'UPDATE PROFIL',
                'description' => "Memperbarui profil sendiri - Perubahan: " . implode(', ', $changes),
                'ip_address' => request()->ip(),
            ]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * [BARU] Update password user yang sedang login.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        // 1. Validasi Input
        // 'current_password' adalah validasi bawaan Laravel untuk cek password lama
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        // 2. Update ke Database (Hash dulu password barunya)
        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        // 3. Log aktivitas ganti password
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'GANTI PASSWORD',
            'description' => "Mengganti password akun sendiri: {$request->user()->name} ({$request->user()->email})",
            'ip_address' => request()->ip(),
        ]);

        // 4. Kembali dengan pesan sukses
        return back()->with('status', 'password-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}