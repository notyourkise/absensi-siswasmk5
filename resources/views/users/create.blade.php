<x-app-layout>
    <div class="max-w-xl mx-auto mt-10">
        <div class="bg-white shadow-lg rounded-xl border border-gray-100 p-8">
            <h2 class="font-extrabold text-2xl text-[#14213D] mb-6 flex items-center">
                <i class="fas fa-user-plus text-[#FCA311] mr-3"></i> Tambah User Baru
            </h2>

            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                
                {{-- Nama --}}
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nama Lengkap</label>
                    <input type="text" name="name" class="w-full rounded-lg border-gray-300 focus:ring-[#FCA311] focus:border-[#FCA311]" required>
                </div>

                {{-- Email --}}
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Email Login</label>
                    <input type="email" name="email" class="w-full rounded-lg border-gray-300 focus:ring-[#FCA311] focus:border-[#FCA311]" required>
                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- Role --}}
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Role (Hak Akses)</label>
                    <select name="role" class="w-full rounded-lg border-gray-300 focus:ring-[#FCA311] focus:border-[#FCA311]">
                        <option value="petugas">Petugas Piket (Scan Only)</option>
                        <option value="wali_kelas">Wali Kelas (Laporan Only)</option>
                        <option value="admin">Administrator (Full Akses)</option>
                    </select>
                </div>

                {{-- Password --}}
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" class="w-full rounded-lg border-gray-300 focus:ring-[#FCA311] focus:border-[#FCA311]" required>
                </div>

                {{-- Confirm Password --}}
                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="w-full rounded-lg border-gray-300 focus:ring-[#FCA311] focus:border-[#FCA311]" required>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('users.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-bold">Batal</a>
                    <button type="submit" class="px-4 py-2 bg-[#14213D] text-white rounded-lg font-bold hover:bg-[#FCA311] hover:text-[#14213D] transition">Simpan User</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>