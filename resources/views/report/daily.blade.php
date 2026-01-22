<x-app-layout>
    
    {{-- HEADER PAGE --}}
    <div class="flex flex-col md:flex-row justify-between items-end mb-6">
        <div>
            <h2 class="font-extrabold text-2xl text-[#14213D] leading-tight flex items-center gap-2">
                <i class="fas fa-calendar-check text-[#FCA311]"></i> {{ __('Laporan Harian') }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">Rekapitulasi kehadiran siswa per hari.</p>
        </div>
        
        {{-- Tanggal Besar --}}
        <div class="mt-4 md:mt-0 text-right">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tanggal Laporan</p>
            <h3 class="text-xl font-black text-[#14213D]">
                {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}
            </h3>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-lg sm:rounded-2xl border border-gray-100">
        
        {{-- BAGIAN FILTER --}}
        <div class="p-6 border-b border-gray-100 bg-white">
            <form method="GET" action="{{ route('report.daily') }}" class="flex flex-col lg:flex-row gap-4 items-end">
                
                {{-- Input Tanggal --}}
                <div class="w-full lg:w-1/4">
                    <label for="date" class="block text-xs font-bold text-[#14213D] uppercase mb-2">Pilih Tanggal</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <input type="date" name="date" id="date" value="{{ $date }}" 
                               class="w-full pl-10 bg-[#E5E5E5] border-transparent rounded-xl text-sm font-bold text-[#14213D] focus:ring-[#FCA311] focus:bg-white focus:border-[#FCA311] transition-colors py-2.5">
                    </div>
                </div>

                {{-- Input Kelas --}}
                <div class="w-full lg:w-1/4">
                    <label for="kelas" class="block text-xs font-bold text-[#14213D] uppercase mb-2">Filter Kelas</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-filter"></i>
                        </div>
                        <select name="kelas" id="kelas" 
                                class="w-full pl-10 bg-[#E5E5E5] border-transparent rounded-xl text-sm font-bold text-[#14213D] focus:ring-[#FCA311] focus:bg-white focus:border-[#FCA311] transition-colors py-2.5 cursor-pointer">
                            <option value="">Tampilkan Semua Kelas</option>
                            @foreach($classes as $item)
                                <option value="{{ $item }}" {{ request('kelas') == $item ? 'selected' : '' }}>
                                    {{ $item }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Tombol Filter --}}
                <div class="w-full lg:w-auto pb-[1px]">
                    <button type="submit" class="w-full lg:w-auto bg-[#14213D] hover:bg-[#0f1a30] text-white px-6 py-2.5 rounded-xl font-bold text-sm shadow-md transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-search"></i> Tampilkan Data
                    </button>
                </div>

                {{-- Info Count (Digeser ke Kanan) --}}
                <div class="w-full lg:flex-1 flex justify-end items-center pb-[1px]">
                    <span class="bg-indigo-50 text-indigo-700 px-4 py-2 rounded-xl text-xs font-bold border border-indigo-100 flex items-center gap-2">
                        <i class="fas fa-users"></i>
                        Total: {{ $students->count() }} Siswa
                    </span>
                </div>
            </form>
        </div>

        {{-- BAGIAN TABEL --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-[#14213D]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Nama Siswa</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Kelas</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Jam Masuk</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Jam Pulang</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($students as $student)
                        @php $absen = $attendances->get($student->id); @endphp
                        <tr class="hover:bg-gray-50 transition duration-150 group">
                            
                            {{-- Nama --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-[#14213D]">{{ $student->nama }}</div>
                            </td>

                            {{-- Kelas --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-md bg-gray-100 text-gray-600 text-xs font-bold border border-gray-200">
                                    {{ $student->kelas }}
                                </span>
                            </td>

                            {{-- Status Kehadiran (Badge Modern) --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($absen)
                                    @php
                                        $badgeClass = match($absen->status_masuk) {
                                            'Hadir' => 'bg-green-100 text-green-700 border-green-200',
                                            'Terlambat' => 'bg-[#FCA311]/20 text-orange-700 border-orange-200',
                                            'Sakit' => 'bg-purple-100 text-purple-700 border-purple-200',
                                            'Izin' => 'bg-blue-100 text-blue-700 border-blue-200',
                                            default => 'bg-red-100 text-red-700 border-red-200',
                                        };
                                    @endphp
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-extrabold rounded-full border {{ $badgeClass }}">
                                        {{ $absen->status_masuk }}
                                    </span>
                                @else
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-gray-100 text-gray-400 border border-gray-200">
                                        <i class="fas fa-minus mr-1 mt-0.5"></i> Alpha
                                    </span>
                                @endif
                            </td>

                            {{-- Jam Masuk --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-mono text-[#14213D]">
                                @if($absen && $absen->jam_masuk)
                                    <span class="bg-gray-50 px-2 py-1 rounded border border-gray-200">
                                        {{ \Carbon\Carbon::parse($absen->jam_masuk)->format('H:i') }}
                                    </span>
                                @else
                                    <span class="text-gray-300">--:--</span>
                                @endif
                            </td>

                            {{-- Jam Pulang --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-mono text-[#14213D]">
                                @if($absen && $absen->jam_keluar)
                                    <span class="bg-gray-50 px-2 py-1 rounded border border-gray-200">
                                        {{ \Carbon\Carbon::parse($absen->jam_keluar)->format('H:i') }}
                                    </span>
                                @else
                                    <span class="text-gray-300">--:--</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500 italic">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                        <i class="fas fa-filter text-gray-400"></i>
                                    </div>
                                    <p class="font-medium">Data tidak ditemukan.</p>
                                    <p class="text-xs">Coba ubah tanggal atau pilihan kelas.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
    </div>
</x-app-layout>