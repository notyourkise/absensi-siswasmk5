<x-app-layout>
    {{-- 
        WRAPPER UTAMA: 
        h-[calc(100vh-65px)]: Mengatur tinggi full layar dikurangi tinggi navbar (sesuaikan angka 65px jika navbar anda lebih tinggi)
        overflow-hidden: Mematikan scroll bar browser
        flex flex-col: Agar header ada di atas dan ilustrasi ada di bawah (auto fill)
    --}}
    <div class="h-[calc(100vh-80px)] overflow-hidden flex flex-col p-6 md:p-8">

        {{-- 1. HEADER HALAMAN (Minimalis) --}}
        <div class="mb-10 flex-none">
            <h2 class="font-extrabold text-3xl text-[#14213D] leading-tight flex items-center gap-3">
                <i class="fas fa-file-invoice text-[#FCA311]"></i> {{ __('Laporan Rekapitulasi') }}
            </h2>
            <p class="text-sm text-gray-500 mt-2 ml-11 max-w-xl">
                Silakan pilih filter di bawah ini untuk mengunduh rekapitulasi kehadiran siswa dalam format PDF.
            </p>
            
            {{-- Alert Error (Jika ada) --}}
            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" class="mt-4 ml-11 bg-red-100 border-l-4 border-red-500 text-red-700 p-3 rounded shadow-sm w-fit flex items-center gap-3 text-sm">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                    <button @click="show = false" class="ml-4 hover:text-red-900"><i class="fas fa-times"></i></button>
                </div>
            @endif
        </div>

        {{-- 2. FORM AREA (Horizontal / Memanjang) --}}
        <div class="w-full flex-none">
            <form action="{{ route('report.download') }}" method="POST" target="_blank">
                @csrf
                
                {{-- Grid System: 4 Kolom Sejajar (Kelas | Bulan | Tahun | Tombol) --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                    
                    {{-- Input 1: PILIH KELAS --}}
                    <div class="w-full">
                        <label for="kelas" class="block text-xs font-bold text-[#14213D] uppercase mb-2 tracking-wider pl-1">
                            Pilih Kelas
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <select name="kelas" id="kelas" class="w-full pl-10 pr-8 py-3 bg-white border border-gray-300 text-[#14213D] font-bold rounded-xl focus:ring-2 focus:ring-[#FCA311] focus:border-[#FCA311] transition-all shadow-sm cursor-pointer" required>
                                <option value="" disabled selected>-- Pilih Kelas --</option>
                                @foreach($classes as $kls)
                                    <option value="{{ $kls }}">{{ $kls }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Input 2: PILIH BULAN --}}
                    <div class="w-full">
                        <label for="bulan" class="block text-xs font-bold text-[#14213D] uppercase mb-2 tracking-wider pl-1">
                            Bulan
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <select name="bulan" id="bulan" class="w-full pl-10 pr-8 py-3 bg-white border border-gray-300 text-[#14213D] font-bold rounded-xl focus:ring-2 focus:ring-[#FCA311] focus:border-[#FCA311] transition-all shadow-sm cursor-pointer" required>
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Input 3: PILIH TAHUN --}}
                    <div class="w-full">
                        <label for="tahun" class="block text-xs font-bold text-[#14213D] uppercase mb-2 tracking-wider pl-1">
                            Tahun
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-history"></i>
                            </div>
                            <select name="tahun" id="tahun" class="w-full pl-10 pr-8 py-3 bg-white border border-gray-300 text-[#14213D] font-bold rounded-xl focus:ring-2 focus:ring-[#FCA311] focus:border-[#FCA311] transition-all shadow-sm cursor-pointer" required>
                                @for($y = date('Y'); $y >= 2024; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    {{-- TOMBOL EKSEKUSI --}}
                    <div class="w-full">
                        <button type="submit" class="w-full flex justify-center items-center py-3 px-6 bg-[#14213D] hover:bg-[#0f1a30] text-white font-bold rounded-xl shadow-lg transform hover:-translate-y-1 transition-all duration-200 focus:ring-2 focus:ring-offset-2 focus:ring-[#14213D]">
                            <i class="fas fa-file-pdf mr-2 text-[#FCA311]"></i> CETAK PDF
                        </button>
                    </div>

                </div>
            </form>
        </div>

        {{-- 3. EMPTY STATE / ILUSTRASI (Pemanis area kosong di bawah) --}}
        <div class="flex-1 flex flex-col items-center justify-center opacity-40 pointer-events-none select-none mt-4">
            {{-- Menggunakan icon font awesome ukuran besar sebagai ganti gambar SVG --}}
            <div class="bg-gray-100 p-8 rounded-full mb-4">
                <i class="fas fa-print text-6xl text-gray-400"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-400">Siap Mencetak Laporan</h3>
            <p class="text-sm text-gray-400 mt-1">Dokumen dicetak otomatis dalam format A4 Landscape</p>
        </div>

    </div>
</x-app-layout>