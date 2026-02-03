<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fas fa-trash-restore mr-2"></i>
                {{ __('Data Siswa Terhapus (Sampah)') }}
            </h2>
            <a href="{{ route('students.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali ke Data Siswa</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Filter & Search --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-4">
                <div class="p-6">
                    <form method="GET" action="{{ route('students.trashed') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Search --}}
                            <div class="md:col-span-2">
                                <input 
                                    type="text" 
                                    name="search" 
                                    value="{{ request('search') }}"
                                    placeholder="Cari nama atau NISN siswa yang dihapus..." 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                >
                            </div>
                            
                            {{-- Filter Kelas --}}
                            <div>
                                <select 
                                    name="kelas" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="">-- Semua Kelas --</option>
                                    @foreach($classes as $kelas)
                                        <option value="{{ $kelas }}" {{ request('kelas') == $kelas ? 'selected' : '' }}>
                                            {{ $kelas }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button 
                                type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition flex items-center gap-2"
                            >
                                <i class="fas fa-search"></i>
                                <span>Cari</span>
                            </button>
                            
                            <a 
                                href="{{ route('students.trashed') }}" 
                                class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition flex items-center gap-2"
                            >
                                <i class="fas fa-redo"></i>
                                <span>Reset</span>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Alert Box --}}
            <div class="mb-4 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Perhatian:</strong> Data di halaman ini adalah siswa yang telah dihapus. 
                            Anda dapat <strong>Memulihkan (Restore)</strong> atau <strong>Menghapus Permanen (Force Delete)</strong>.
                            Data yang di-force delete <span class="font-bold text-red-600">TIDAK BISA DIPULIHKAN LAGI</span>!
                        </p>
                    </div>
                </div>
            </div>

            {{-- Tabel Data Siswa Terhapus --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    
                    {{-- Info Jumlah Data --}}
                    <div class="mb-4 flex justify-between items-center">
                        <p class="text-gray-600">
                            Total <strong class="text-red-600">{{ $students->total() }}</strong> siswa terhapus
                        </p>
                    </div>

                    {{-- Responsive Table --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">NISN</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dihapus Pada</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($students as $student)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ ($students->currentPage() - 1) * $students->perPage() + $loop->iteration }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $student->nisn }}
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-900">
                                            <div class="flex items-center gap-3">
                                                @if($student->foto)
                                                    <img src="{{ asset('storage/students/' . $student->foto) }}" 
                                                         alt="{{ $student->nama }}" 
                                                         class="w-10 h-10 rounded-full object-cover">
                                                @else
                                                    <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                        <i class="fas fa-user text-gray-600"></i>
                                                    </div>
                                                @endif
                                                <span class="font-medium">{{ $student->nama }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $student->kelas }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex flex-col">
                                                <span>{{ $student->deleted_at->format('d/m/Y') }}</span>
                                                <span class="text-xs text-gray-400">{{ $student->deleted_at->format('H:i') }} WITA</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex gap-2">
                                                {{-- Tombol Restore --}}
                                                <form action="{{ route('students.restore', $student->id) }}" method="POST" class="inline restore-form">
                                                    @csrf
                                                    <button 
                                                        type="submit" 
                                                        class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg transition flex items-center gap-1"
                                                    >
                                                        <i class="fas fa-undo text-xs"></i>
                                                        <span>Restore</span>
                                                    </button>
                                                </form>

                                                {{-- Tombol Force Delete --}}
                                                <form action="{{ route('students.forceDelete', $student->id) }}" method="POST" class="inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button 
                                                        type="submit" 
                                                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg transition flex items-center gap-1"
                                                    >
                                                        <i class="fas fa-trash text-xs"></i>
                                                        <span>Hapus Permanen</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                            <div class="flex flex-col items-center gap-2">
                                                <i class="fas fa-check-circle text-4xl text-green-400"></i>
                                                <p class="font-medium">Tidak ada siswa yang terhapus</p>
                                                <p class="text-sm">Semua data siswa aman! 🎉</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($students->hasPages())
                        <div class="mt-6">
                            {{ $students->links() }}
                        </div>
                    @endif

                </div>
            </div>

            {{-- Info Box Penjelasan --}}
            <div class="mt-4 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800 mb-2">Informasi Soft Delete:</h3>
                        <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
                            <li><strong>Restore:</strong> Mengembalikan data siswa ke daftar siswa aktif</li>
                            <li><strong>Hapus Permanen:</strong> Menghapus data secara permanen dari database (tidak bisa dikembalikan)</li>
                            <li>Data yang terhapus tidak akan muncul di halaman Data Siswa, Absensi, atau Laporan</li>
                            <li>Fitur ini berguna untuk mengarsipkan siswa yang sudah lulus atau pindah sekolah</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
