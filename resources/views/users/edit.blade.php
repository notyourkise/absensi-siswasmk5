<x-app-layout>
    <div class="max-w-4xl mx-auto mt-10">
        
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-extrabold text-2xl text-[#14213D]">
                <i class="fas fa-user-edit text-[#FCA311] mr-2"></i> Edit Data User
            </h2>
            <a href="{{ route('users.index') }}" class="text-gray-500 hover:text-[#14213D] font-bold">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>

        <div class="bg-white shadow-lg rounded-xl border border-gray-100 p-8">
            
            <form action="{{ route('users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                {{-- 1. IDENTITAS (NAMA & EMAIL) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full rounded-lg border-gray-300 focus:ring-[#FCA311] focus:border-[#FCA311]" required>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Email Login</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full rounded-lg border-gray-300 focus:ring-[#FCA311] focus:border-[#FCA311]" required>
                    </div>
                </div>

                {{-- 2. ROLE --}}
                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Role (Hak Akses)</label>
                    <select name="role" class="w-full rounded-lg border-gray-300 focus:ring-[#FCA311] focus:border-[#FCA311]">
                        <option value="petugas" {{ $user->role == 'petugas' ? 'selected' : '' }}>Petugas Piket</option>
                        <option value="wali_kelas" {{ $user->role == 'wali_kelas' ? 'selected' : '' }}>Wali Kelas</option>
                        <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Administrator</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Hati-hati memberikan akses Administrator.</p>
                </div>

                <hr class="my-6 border-gray-200">

                {{-- 3. RESET PASSWORD (KHUSUS ADMIN) --}}
                <div class="bg-yellow-50 p-6 rounded-lg border border-yellow-200">
                    <h3 class="font-bold text-[#14213D] mb-2 flex items-center">
                        <i class="fas fa-key mr-2"></i> Reset Password User
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Isi kolom di bawah <b>HANYA JIKA</b> ingin mengganti password user ini. Jika tidak, biarkan kosong.
                        <br><span class="text-red-500 font-bold">*Admin tidak perlu memasukkan password lama user.*</span>
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Password Baru</label>
                            <input type="password" name="password" placeholder="Minimal 8 karakter" class="w-full rounded-lg border-gray-300 focus:ring-[#FCA311] focus:border-[#FCA311]">
                            @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Ulangi Password Baru</label>
                            <input type="password" name="password_confirmation" placeholder="Konfirmasi password" class="w-full rounded-lg border-gray-300 focus:ring-[#FCA311] focus:border-[#FCA311]">
                        </div>
                    </div>
                </div>

                {{-- TOMBOL AKSI --}}
                <div class="flex justify-end gap-3 mt-8">
                    <a href="{{ route('users.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-bold">Batal</a>
                    <button type="submit" class="px-6 py-2 bg-[#14213D] text-white rounded-lg font-bold hover:bg-[#FCA311] hover:text-[#14213D] transition shadow-lg">
                        <i class="fas fa-save mr-2"></i> Update Data User
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>