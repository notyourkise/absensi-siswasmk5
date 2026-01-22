<x-guest-layout>
    <div class="flex h-screen w-full overflow-hidden">

        {{-- 
            SISI KIRI: BACKGROUND NAVY BLUE (#14213D) 
        --}}
        <div class="hidden lg:flex w-1/2 relative bg-[#14213D] items-center justify-center">
            
            {{-- Gambar Background (Opacity rendah agar warna Navy tetap dominan) --}}
            <div class="absolute inset-0 bg-cover bg-center opacity-20"
                 style="background-image: url('{{ asset('images/bangunan-sekolah.jpg') }}');">
            </div>
            
            {{-- Gradient Overlay (Navy ke Hitam transparan) --}}
            <div class="absolute inset-0 bg-gradient-to-br from-[#14213D] to-[#000000] opacity-80"></div>

            <div class="relative z-10 p-12 text-center flex flex-col items-center">
                
                {{-- ANIMASI LOTTIE --}}
                <div class="mb-6 drop-shadow-2xl">
                    <dotlottie-wc 
                        src="https://lottie.host/c3ef1ab7-3c6b-4379-aefb-89eb949a6728/lKzFa9ymGW.lottie" 
                        autoplay 
                        loop 
                        style="width: 300px; height: 300px;">
                    </dotlottie-wc>
                </div>

                {{-- Judul Sekolah (Putih & Orange) --}}
                <h1 class="text-4xl font-extrabold text-[#FFFFFF] tracking-tight mb-3 uppercase drop-shadow-md">
                    SMKN 5 Samarinda
                </h1>
                
                {{-- Garis Aksen Orange (#FCA311) --}}
                <div class="h-1.5 w-24 bg-[#FCA311] rounded-full mb-5"></div>
                
                <p class="text-lg text-[#E5E5E5] font-medium tracking-wide">
                    Sistem Absensi & Identitas Digital
                </p>
            </div>
        </div>

        {{-- 
            SISI KANAN: BACKGROUND PUTIH (#FFFFFF)
        --}}
        <div class="w-full lg:w-1/2 flex items-center justify-center bg-[#FFFFFF] relative">
            
            <div class="w-full max-w-md p-8 relative z-10">
                
                {{-- LOGO SEKOLAH --}}
                <div class="flex justify-center mb-8">
                    <img src="{{ asset('logo/sekolah.png') }}" 
                         alt="Logo SMKN 5 Samarinda" 
                         class="w-28 h-28 object-contain drop-shadow-md ">
                </div>

                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-[#000000]">Selamat Datang!</h2>
                    <p class="text-sm text-gray-500">Silakan login untuk mengelola sistem.</p>
                </div>

                {{-- FORM LOGIN --}}
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    {{-- Input Email (Bg: #E5E5E5) --}}
                    <div>
                        <label class="block text-sm font-bold text-[#14213D] mb-1">Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <input id="email" type="email" name="email" :value="old('email')" required autofocus 
                                class="w-full bg-[#E5E5E5] border-transparent focus:border-[#FCA311] focus:bg-white focus:ring-2 focus:ring-[#FCA311]/50 rounded-lg pl-10 pr-4 py-3 text-[#000000] font-medium outline-none transition-all duration-200"
                                placeholder="admin@smkn5.sch.id">
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-600 text-xs font-bold" />
                    </div>

                    {{-- Input Password (Bg: #E5E5E5) --}}
                    <div>
                        <label class="block text-sm font-bold text-[#14213D] mb-1">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input id="password" type="password" name="password" required 
                                class="w-full bg-[#E5E5E5] border-transparent focus:border-[#FCA311] focus:bg-white focus:ring-2 focus:ring-[#FCA311]/50 rounded-lg pl-10 pr-4 py-3 text-[#000000] font-medium outline-none transition-all duration-200"
                                placeholder="••••••••">
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-600 text-xs font-bold" />
                    </div>

                    {{-- Remember Me & Forgot --}}
                    <div class="flex items-center justify-between">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded border-gray-300 text-[#14213D] focus:ring-[#FCA311]">
                            <span class="ml-2 text-sm text-[#14213D] font-semibold">Ingat Saya</span>
                        </label>
                    </div>

                    {{-- Tombol Login (Warna Utama: #14213D, Hover: Sedikit lebih terang/darken) --}}
                    <button type="submit" 
                        class="w-full bg-[#14213D] hover:bg-[#0f1a30] text-[#FFFFFF] font-bold py-3.5 rounded-lg shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-1 active:scale-95 border-b-4 border-[#0a1120] active:border-b-0">
                        MASUK SEKARANG
                    </button>
                </form>

                {{-- Footer & Credits --}}
                <div class="mt-10 text-center space-y-1">
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">
                        &copy; {{ date('Y') }} SMKN 5 Samarinda
                    </p>
                    <p class="text-[11px] text-gray-400">
                        Built by <span class="font-extrabold text-[#FCA311] hover:text-[#14213D] transition-colors cursor-pointer">Fikri Haikal</span>
                    </p>
                </div>
            </div>
        </div>

    </div>
</x-guest-layout>