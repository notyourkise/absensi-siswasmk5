<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Input Absensi Manual') }}
        </h2>
        @if(auth()->user()->role === 'wali_kelas')
            <p class="text-sm text-gray-600 mt-1">
                Kelas binaan: <strong class="text-[#FCA311]">{{ auth()->user()->kelas }}</strong>
            </p>
        @endif
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
                            <p class="text-xs text-gray-500 mt-1">
                                Total: <strong>{{ $students->count() }}</strong> siswa
                                @if(auth()->user()->role === 'wali_kelas')
                                    di kelas <strong class="text-[#FCA311]">{{ auth()->user()->kelas }}</strong>
                                @endif
                            </p>
                        </div>

                        {{-- Tanggal --}}
                        <div class="mb-4">
                            <label class="block font-medium text-sm text-gray-700">Tanggal Absen</label>
                            <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1" required>
                            <p class="text-xs text-blue-600 mt-1">💡 Anda bisa memilih tanggal masa lalu untuk sinkronisasi data historis</p>
                        </div>

                        {{-- Keterangan Status --}}
                        <div class="mb-4">
                            <label class="block font-medium text-sm text-gray-700 mb-2">Keterangan</label>
                            <div class="flex gap-4">
                                <label class="flex items-center">
                                    <input type="radio" name="status" value="Hadir" class="status-radio text-indigo-600 focus:ring-indigo-500" checked>
                                    <span class="ml-2">Hadir (Lupa Bawa Kartu)</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="status" value="Sakit" class="status-radio text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2">Sakit</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="status" value="Izin" class="status-radio text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2">Izin</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="status" value="Alpha" class="status-radio text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2">Alpha</span>
                                </label>
                            </div>
                        </div>

                        {{-- Jam Masuk & Keluar (Hanya untuk status Hadir) --}}
                        <div id="jam-section" class="mb-6 space-y-4 border border-gray-300 rounded-lg p-4 bg-gray-50">
                            <p class="text-sm font-medium text-gray-700 mb-3">⏰ Input Jam (Opsional - Kosongkan jika menggunakan jam sekarang)</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Jam Masuk</label>
                                    <input type="text" name="jam_masuk" id="jam_masuk" 
                                           maxlength="5"
                                           placeholder="07:00"
                                           class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1">
                                    <p class="text-xs text-gray-500 mt-1">Format 24 jam (contoh: 07:00)</p>
                                </div>
                                
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Jam Keluar</label>
                                    <input type="text" name="jam_keluar" id="jam_keluar" 
                                           maxlength="5"
                                           placeholder="15:00"
                                           class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1">
                                    <p class="text-xs text-gray-500 mt-1">Format 24 jam (contoh: 15:00)</p>
                                </div>
                            </div>

                            <div class="bg-blue-50 border-l-4 border-blue-500 p-3 text-sm">
                                <p class="font-medium text-blue-800">📝 Panduan Input Jam:</p>
                                <ul class="text-blue-700 list-disc ml-5 mt-1 space-y-1">
                                    <li>Untuk data historis Januari, isi <strong>Jam Masuk</strong> (contoh: 07:00) dan <strong>Jam Keluar</strong> (contoh: 14:30)</li>
                                    <li>Untuk input real-time hari ini, kosongkan kedua field (sistem pakai jam sekarang)</li>
                                    <li>Jam menggunakan format 24 jam (00:00 - 23:59)</li>
                                </ul>
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

            // Toggle visibilitas section jam berdasarkan status
            function toggleJamSection() {
                const status = $('input[name="status"]:checked').val();
                if (status === 'Hadir') {
                    $('#jam-section').slideDown();
                } else {
                    $('#jam-section').slideUp();
                    // Clear values jika tidak Hadir
                    $('#jam_masuk').val('');
                    $('#jam_keluar').val('');
                }
            }

            // Jalankan saat pertama kali load
            toggleJamSection();

            // Jalankan saat radio button berubah
            $('.status-radio').change(function() {
                toggleJamSection();
            });

            // Auto-format time inputs (24-hour format)
            $('#jam_masuk, #jam_keluar').on('input', function(e) {
                let value = $(this).val().replace(/[^0-9]/g, '');
                
                if (value.length >= 2) {
                    value = value.substring(0, 2) + ':' + value.substring(2, 4);
                }
                
                $(this).val(value);
            });

            // Validate time format on blur
            $('#jam_masuk, #jam_keluar').on('blur', function(e) {
                const value = $(this).val();
                const timeRegex = /^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/;
                
                if (value && !timeRegex.test(value)) {
                    alert('Format jam tidak valid! Gunakan format HH:MM (00:00 - 23:59)');
                    $(this).val('');
                }
            });
        });
    </script>
</x-app-layout>