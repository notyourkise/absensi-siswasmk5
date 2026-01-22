<x-app-layout>
    
    {{-- HEADER PAGE --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <div>
            <h2 class="font-extrabold text-2xl text-[#14213D] leading-tight flex items-center gap-2">
                <i class="fas fa-file-invoice text-[#FCA311]"></i> {{ __('Laporan Rekapitulasi') }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">Unduh jurnal kehadiran kelas bulanan (PDF)</p>
        </div>
    </div>

    <div class="py-6">
        <div class="max-w-2xl mx-auto">
            
            {{-- ALERT ERROR --}}
            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" 
                     class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-800 p-4 rounded-r shadow-sm flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-3 text-lg"></i>
                        <span class="font-bold">{{ session('error') }}</span>
                    </div>
                    <button @click="show = false"><i class="fas fa-times"></i></button>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-2xl rounded-3xl border border-gray-100 relative">
                
                {{-- Hiasan Background Atas --}}
                <div class="absolute top-0 left-0 right-0 h-2 bg-[#14213D]"></div>
                <div class="absolute top-0 right-0 -mt-10 -mr-10 w-32 h-32 bg-[#FCA311] rounded-full opacity-10 blur-2xl"></div>

                <div class="p-8 md:p-10">
                    
                    {{-- Judul Form --}}
                    <div class="text-center mb-10">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-[#14213D] mb-5 shadow-lg shadow-indigo-900/20">
                            <i class="fas fa-print text-3xl text-[#FCA311]"></i>
                        </div>
                        <h3 class="text-2xl font-black text-[#14213D] tracking-tight">Cetak Jurnal Absensi</h3>
                        <p class="text-sm text-gray-500 mt-2 max-w-sm mx-auto">
                            Silakan pilih filter di bawah ini untuk mengunduh rekapitulasi kehadiran siswa dalam format PDF.
                        </p>
                    </div>

                    {{-- Form Utama --}}
                    <form action="{{ route('report.download') }}" method="POST" target="_blank" class="space-y-6">
                        @csrf
                        
                        {{-- 1. PILIH KELAS --}}
                        <div>
                            <label for="kelas" class="block text-xs font-bold text-[#14213D] uppercase mb-2 tracking-wider">
                                Pilih Kelas
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#FCA311] transition">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                                <select name="kelas" id="kelas" class="block w-full pl-11 pr-10 py-3.5 bg-[#E5E5E5] border-transparent text-[#14213D] font-bold rounded-xl focus:ring-2 focus:ring-[#FCA311] focus:bg-white focus:border-transparent transition-all shadow-sm cursor-pointer" required>
                                    <option value="" disabled selected>-- Pilih Kelas --</option>
                                    @foreach($classes as $kls)
                                        <option value="{{ $kls }}">{{ $kls }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- 2. PILIH BULAN --}}
                            <div>
                                <label for="bulan" class="block text-xs font-bold text-[#14213D] uppercase mb-2 tracking-wider">
                                    Bulan
                                </label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#FCA311] transition">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <select name="bulan" id="bulan" class="block w-full pl-11 py-3.5 bg-[#E5E5E5] border-transparent text-[#14213D] font-bold rounded-xl focus:ring-2 focus:ring-[#FCA311] focus:bg-white focus:border-transparent transition-all shadow-sm cursor-pointer" required>
                                        @foreach(range(1, 12) as $m)
                                            <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- 3. PILIH TAHUN --}}
                            <div>
                                <label for="tahun" class="block text-xs font-bold text-[#14213D] uppercase mb-2 tracking-wider">
                                    Tahun
                                </label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#FCA311] transition">
                                        <i class="fas fa-history"></i>
                                    </div>
                                    <select name="tahun" id="tahun" class="block w-full pl-11 py-3.5 bg-[#E5E5E5] border-transparent text-[#14213D] font-bold rounded-xl focus:ring-2 focus:ring-[#FCA311] focus:bg-white focus:border-transparent transition-all shadow-sm cursor-pointer" required>
                                        @for($y = date('Y'); $y >= 2024; $y--)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- TOMBOL DOWNLOAD --}}
                        <div class="pt-6">
                            <button type="submit" class="w-full flex justify-center items-center py-4 px-6 border border-transparent rounded-xl shadow-lg text-sm font-black text-white bg-[#14213D] hover:bg-[#0f1a30] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#14213D] transition-all transform hover:-translate-y-1 active:scale-95 group">
                                <span class="bg-white/20 p-1.5 rounded-md mr-3 group-hover:bg-[#FCA311] group-hover:text-[#14213D] transition-colors">
                                    <i class="fas fa-file-pdf text-lg"></i>
                                </span>
                                DOWNLOAD REKAPITULASI (PDF)
                            </button>
                        </div>
                    </form>

                </div>
                
                {{-- Footer Info --}}
                <div class="bg-[#F9FAFB] px-8 py-5 border-t border-gray-100 flex items-center justify-center gap-2">
                    <i class="fas fa-info-circle text-gray-400"></i>
                    <p class="text-xs text-gray-500 font-medium">
                        Dokumen dicetak otomatis dalam format <span class="font-bold text-[#14213D]">A4 Landscape</span>.
                    </p>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>