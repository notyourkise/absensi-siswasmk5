<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Input Absensi Manual') }}
        </h2>
    </x-slot>

    {{-- 1. Tambahkan CSS Select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 font-bold">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 font-bold">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <form action="{{ route('manual.store') }}" method="POST">
                        @csrf
                        
                        {{-- 2. Dropdown Siswa dengan ID khusus --}}
                        <div class="mb-4">
                            <label class="block font-medium text-sm text-gray-700">Nama Siswa</label>
                            <select id="select-siswa" name="student_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1" required>
                                <option value="">-- Cari Nama Siswa --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->nama }} ({{ $student->kelas }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tanggal --}}
                        <div class="mb-4">
                            <label class="block font-medium text-sm text-gray-700">Tanggal Absen</label>
                            <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1" required>
                        </div>

                        {{-- Keterangan Status --}}
                        <div class="mb-6">
                            <label class="block font-medium text-sm text-gray-700 mb-2">Keterangan</label>
                            <div class="flex gap-4">
                                <label class="flex items-center">
                                    <input type="radio" name="status" value="Hadir" class="text-indigo-600 focus:ring-indigo-500" checked>
                                    <span class="ml-2">Hadir (Lupa Bawa Kartu)</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="status" value="Sakit" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2">Sakit</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="status" value="Izin" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2">Izin</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="status" value="Alpha" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2">Alpha</span>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Simpan Data
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>

    {{-- 3. Script Mengaktifkan Select2 --}}
    <script>
        $(document).ready(function() {
            $('#select-siswa').select2({
                placeholder: "-- Ketik Nama Siswa --",
                allowClear: true,
                width: '100%' // Supaya lebar ngikutin form
            });
        });
    </script>
</x-app-layout>