<x-app-layout>
    
    {{-- HEADER PAGE --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <div>
            <h2 class="font-extrabold text-2xl text-[#14213D] leading-tight flex items-center gap-2">
                <i class="fas fa-users text-[#FCA311]"></i> {{ __('Data Siswa') }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                @if(auth()->user()->role === 'wali_kelas')
                    Kelola siswa kelas binaan: <strong class="text-[#FCA311]">{{ auth()->user()->kelas }}</strong>
                @else
                    Kelola data peserta didik SMKN 5 Samarinda
                @endif
            </p>
        </div>
        
        {{-- Breadcrumb / Info Kecil --}}
        <div class="mt-4 md:mt-0">
            <span class="bg-white border border-gray-200 px-4 py-1 rounded-full text-xs font-bold text-gray-600 shadow-sm">
                Total: <span class="text-[#14213D]">{{ $students->total() }}</span> Siswa
            </span>
        </div>
    </div>

    {{-- CDN FontAwesome (Pastikan ini ada jika belum di layout utama) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- ALERT SUKSES --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
             class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-800 p-4 rounded-r shadow-sm flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-3 text-lg"></i> 
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-green-600 hover:text-green-800"><i class="fas fa-times"></i></button>
        </div>
    @endif

    <div class="bg-white overflow-hidden shadow-lg sm:rounded-2xl border border-gray-100">
        <div class="p-6">
            
            {{-- TOOLBAR: PENCARIAN & FILTER --}}
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-8">
                
                {{-- KIRI: Form Pencarian & Filter Kelas --}}
                <form action="{{ route('students.index') }}" method="GET" class="w-full lg:w-2/3 flex flex-col md:flex-row gap-3">
                    
                    {{-- Dropdown Filter Kelas --}}
                    <div class="w-full md:w-1/3">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">
                                <i class="fas fa-filter"></i>
                            </div>
                            <select name="kelas" onchange="this.form.submit()" 
                                    class="w-full pl-10 bg-[#E5E5E5] border-transparent focus:bg-white focus:border-[#FCA311] focus:ring-[#FCA311] rounded-xl text-sm font-medium text-[#14213D] py-2.5 transition-all"
                                    @if(auth()->user()->role === 'wali_kelas') disabled @endif>
                                @if(auth()->user()->role === 'wali_kelas')
                                    <option value="{{ auth()->user()->kelas }}" selected>{{ auth()->user()->kelas }}</option>
                                @else
                                    <option value="">Semua Kelas</option>
                                    @foreach($classes as $item)
                                        <option value="{{ $item }}" {{ request('kelas') == $item ? 'selected' : '' }}>
                                            {{ $item }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    {{-- Input Pencarian --}}
                    <div class="w-full md:w-2/3 flex shadow-sm rounded-xl overflow-hidden">
                        <input type="text" name="search" value="{{ request('search') }}" 
                                placeholder="Cari Nama Siswa atau NISN..." 
                                class="w-full bg-[#E5E5E5] border-transparent focus:bg-white focus:ring-0 text-sm font-medium text-[#14213D] py-2.5 px-4 placeholder-gray-500 transition-all">
                        <button type="submit" class="bg-[#14213D] hover:bg-[#0f1a30] text-white px-5 transition-colors">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                {{-- KANAN: Tombol Aksi --}}
                <div class="flex flex-wrap gap-2 w-full lg:w-auto justify-end">
                    
                    {{-- Dropdown Aksi Massal (Navy) --}}
                    <div x-data="{ open: false }" class="relative" @click.away="open = false">
                        <button @click="open = !open" 
                                class="bg-[#14213D] hover:bg-[#0f1a30] text-white font-bold py-2.5 px-5 rounded-xl shadow-md transition-all flex items-center text-sm">
                            <i class="fas fa-cog mr-2"></i> Aksi Massal 
                            <i class="fas fa-chevron-down ml-2 text-xs transition-transform duration-200" :class="{'rotate-180': open}"></i>
                        </button>

                        {{-- Menu Dropdown --}}
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-2xl z-50 border border-gray-100 overflow-hidden" 
                             style="display: none;">
                            
                            <div class="px-4 py-2 text-[10px] font-extrabold text-gray-400 uppercase tracking-widest bg-gray-50">Cetak Dokumen</div>

                            {{-- Form Cetak --}}
                            <div class="p-3">
                                <form action="{{ route('students.print.all') }}" method="GET" target="_blank">
                                    <label class="text-xs font-bold text-[#14213D] mb-1 block">Pilih Kelas:</label>
                                    <div class="flex">
                                        <select name="kelas" class="text-xs border-gray-300 rounded-l-lg w-full focus:ring-[#14213D] focus:border-[#14213D] bg-gray-50" required>
                                            <option value="" disabled selected>- Kelas -</option>
                                            @foreach($classes as $k)
                                                <option value="{{ $k }}">{{ $k }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="bg-[#14213D] text-white px-3 rounded-r-lg hover:bg-[#0f1a30]">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="border-t border-gray-100"></div>
                            <div class="px-4 py-2 text-[10px] font-extrabold text-gray-400 uppercase tracking-widest bg-gray-50">Manajemen Data</div>

                            <a href="{{ route('students.template') }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-[#FCA311] hover:text-[#14213D] transition">
                                <i class="fas fa-download mr-3 w-4 text-center"></i> Download Template
                            </a>

                            <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="file" name="file" id="excel-upload" class="hidden" onchange="this.form.submit()">
                                <label for="excel-upload" class="block w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-[#FCA311] hover:text-[#14213D] cursor-pointer transition">
                                    <i class="fas fa-file-excel mr-3 w-4 text-center"></i> Import Excel
                                </label>
                            </form>
                        </div>
                    </div>

                    {{-- Tombol Tambah (Orange) --}}
                    <a href="{{ route('students.create') }}" class="bg-[#FCA311] hover:bg-orange-400 text-[#14213D] font-bold py-2.5 px-5 rounded-xl shadow-md transition-all flex items-center text-sm">
                        <i class="fas fa-plus-circle mr-2"></i> Tambah Data
                    </a>
                </div>
            </div>

            {{-- TABEL DATA SISWA --}}
            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="min-w-full table-auto border-collapse bg-white">
                    <thead class="bg-[#14213D]">
                        <tr class="text-white uppercase text-xs font-bold tracking-wider text-left">
                            <th class="px-6 py-4 border-b border-[#0f1a30]">Identitas</th>
                            <th class="px-6 py-4 border-b border-[#0f1a30]">NISN</th>
                            <th class="px-6 py-4 border-b border-[#0f1a30]">Kelas</th>
                            <th class="px-6 py-4 border-b border-[#0f1a30] text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700 divide-y divide-gray-100">
                        @forelse ($students as $student)
                        <tr class="hover:bg-gray-50 transition duration-200 group">
                            
                            {{-- Kolom Identitas (Foto + Nama) --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        @if($student->foto)
                                            <img class="h-10 w-10 rounded-full object-cover border-2 border-white shadow-md group-hover:border-[#FCA311] transition" 
                                                 src="{{ asset('storage/students/' . $student->foto) }}" alt="">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-[#14213D] flex items-center justify-center text-[#FCA311] font-bold border-2 border-white shadow-md">
                                                {{ substr($student->nama, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-bold text-gray-900 group-hover:text-[#14213D] transition">{{ $student->nama }}</div>
                                        <div class="text-xs text-gray-500">Siswa Aktif</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Kolom NISN --}}
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-[#14213D] font-medium">
                                {{ $student->nisn }}
                            </td>

                            {{-- Kolom Kelas --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-indigo-50 text-[#14213D] border border-indigo-100">
                                    {{ $student->kelas }}
                                </span>
                            </td>

                            {{-- Kolom Aksi --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex justify-center items-center gap-2">
                                    
                                    {{-- Cetak --}}
                                    <a href="{{ route('students.print', $student->id) }}" target="_blank" 
                                       class="w-8 h-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center hover:bg-green-500 hover:text-white transition shadow-sm"
                                       title="Cetak Kartu">
                                        <i class="fas fa-id-card"></i>
                                    </a>

                                    {{-- Laporan --}}
                                    <a href="{{ route('students.report', $student->id) }}" 
                                       class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center hover:bg-purple-500 hover:text-white transition shadow-sm"
                                       title="Laporan Absensi">
                                        <i class="fas fa-file-alt"></i>
                                    </a>

                                    {{-- Edit --}}
                                    <a href="{{ route('students.edit', $student->id) }}" 
                                       class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-500 hover:text-white transition shadow-sm"
                                       title="Edit Data">
                                        <i class="fas fa-pen"></i>
                                    </a>

                                    {{-- Hapus --}}
                                    <form action="{{ route('students.destroy', $student->id) }}" method="POST" 
                                          class="inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-500 hover:text-white transition shadow-sm"
                                                title="Hapus Data">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500 italic bg-gray-50 rounded-b-xl">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-[#E5E5E5] rounded-full flex items-center justify-center mb-3 text-gray-400">
                                        <i class="fas fa-search text-2xl"></i>
                                    </div>
                                    <p class="font-medium text-gray-600">Data tidak ditemukan.</p>
                                    <p class="text-xs text-gray-400 mt-1">Coba sesuaikan filter kelas atau kata kunci pencarian.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- PAGINATION --}}
            <div class="mt-6">
                {{ $students->links() }}
            </div>

        </div>
    </div>
</x-app-layout>