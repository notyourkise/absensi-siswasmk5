<x-app-layout>
    {{-- 
        WRAPPER UTAMA: 
        h-[calc(100vh-65px)]: Mengambil tinggi layar dikurangi tinggi Navbar (asumsi 65px).
        overflow-hidden: Mencegah body scroll.
    --}}
    <div class="flex flex-col lg:flex-row h-[calc(100vh-65px)] overflow-hidden bg-white">

        {{-- 
            PANEL KIRI (FOTO) - FIXED
            Warna Navy (#14213D), Teks Putih
        --}}
        <div class="w-full lg:w-5/12 bg-[#14213D] relative flex flex-col justify-center items-center p-8 text-center shadow-2xl z-10">
            
            {{-- Hiasan Background --}}
            <div class="absolute top-0 left-0 w-32 h-32 bg-[#FCA311] rounded-br-full opacity-20"></div>
            <div class="absolute bottom-0 right-0 w-32 h-32 bg-[#FCA311] rounded-tl-full opacity-20"></div>

            <div class="relative z-10">
                <h2 class="text-3xl font-black text-white mb-2">Siswa Baru</h2>
                <p class="text-indigo-200 text-sm mb-8">Upload foto profil siswa di sini.</p>

                {{-- FORM WRAPPER (Hanya untuk trigger input file di luar form utama, via JS) --}}
                <div class="relative group cursor-pointer w-64 h-64 mx-auto" onclick="document.getElementById('foto-input').click()">
                    {{-- Preview Image --}}
                    <img id="preview-img" 
                         src="https://ui-avatars.com/api/?name=New+Student&background=E5E5E5&color=14213D&size=256" 
                         class="w-full h-full object-cover rounded-full border-8 border-white/10 group-hover:border-[#FCA311] shadow-2xl transition-all duration-300">
                    
                    {{-- Overlay Icon --}}
                    <div class="absolute bottom-4 right-4 bg-[#FCA311] text-[#14213D] w-12 h-12 rounded-full flex items-center justify-center shadow-lg transform group-hover:scale-110 transition">
                        <i class="fas fa-camera text-xl"></i>
                    </div>
                </div>
                
                <p class="text-xs text-indigo-300 mt-6 animate-pulse">Klik lingkaran di atas untuk memilih foto</p>
                <p class="text-[10px] text-indigo-200 mt-2 bg-white/10 rounded-lg px-3 py-2">
                    <i class="fas fa-info-circle mr-1"></i> Format: <strong>WebP</strong> • Maks: <strong>1MB</strong>
                </p>
            </div>
        </div>

        {{-- 
            PANEL KANAN (FORM) - SCROLLABLE
            Warna Putih, punya overflow-y-auto sendiri
        --}}
        <div class="w-full lg:w-7/12 h-full overflow-y-auto bg-white p-8 lg:p-12 relative">
            
            <div class="max-w-xl mx-auto">
                <div class="flex justify-between items-center mb-8 border-b border-gray-100 pb-4">
                    <h3 class="text-2xl font-bold text-[#14213D]">Data Akademik</h3>
                    <a href="{{ route('students.index') }}" class="text-sm font-bold text-gray-400 hover:text-[#14213D] transition">
                        <i class="fas fa-times text-lg"></i>
                    </a>
                </div>

                <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    {{-- Input File Tersembunyi (Dikaitkan dengan Panel Kiri) --}}
                    <input type="file" name="foto" id="foto-input" class="hidden" accept="image/webp" onchange="previewImage(event)">

                    <div class="space-y-6">
                        
                        {{-- NISN --}}
                        <div class="group">
                            <label class="block text-xs font-bold text-[#14213D] uppercase mb-2 tracking-wider">NISN</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                    <i class="fas fa-id-card"></i>
                                </span>
                                <input type="number" name="nisn" value="{{ old('nisn') }}" required autofocus
                                    class="w-full pl-11 bg-[#E5E5E5] border-transparent rounded-xl px-4 py-3.5 text-sm font-bold text-[#14213D] focus:bg-white focus:ring-2 focus:ring-[#FCA311] transition-all placeholder-gray-400"
                                    placeholder="Nomor Induk Siswa Nasional">
                            </div>
                            @error('nisn')
                                <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- NAMA LENGKAP --}}
                        <div class="group">
                            <label class="block text-xs font-bold text-[#14213D] uppercase mb-2 tracking-wider">Nama Lengkap</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" name="nama" value="{{ old('nama') }}" required
                                    class="w-full pl-11 bg-[#E5E5E5] border-transparent rounded-xl px-4 py-3.5 text-sm font-bold text-[#14213D] focus:bg-white focus:ring-2 focus:ring-[#FCA311] transition-all placeholder-gray-400"
                                    placeholder="Nama Sesuai Ijazah">
                            </div>
                        </div>

                        {{-- KELAS --}}
                        <div class="group">
                            <label class="block text-xs font-bold text-[#14213D] uppercase mb-2 tracking-wider">Kelas</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                    <i class="fas fa-chalkboard"></i>
                                </span>
                                <input type="text" name="kelas" value="{{ old('kelas') }}" required
                                    class="w-full pl-11 bg-[#E5E5E5] border-transparent rounded-xl px-4 py-3.5 text-sm font-bold text-[#14213D] focus:bg-white focus:ring-2 focus:ring-[#FCA311] transition-all placeholder-gray-400"
                                    placeholder="Contoh: XII RPL 1">
                            </div>
                        </div>

                        {{-- TOMBOL AKSI --}}
                        <div class="pt-8 flex items-center gap-4">
                            <button type="submit" class="flex-1 bg-[#14213D] hover:bg-[#0f1a30] text-white font-bold py-4 rounded-xl shadow-lg transform hover:-translate-y-1 transition-all">
                                SIMPAN DATA
                            </button>
                            <a href="{{ route('students.index') }}" class="flex-none px-6 py-4 rounded-xl border border-gray-300 text-gray-500 font-bold hover:bg-gray-50 transition">
                                BATAL
                            </a>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Script Preview --}}
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function(){
                const output = document.getElementById('preview-img');
                output.src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</x-app-layout>