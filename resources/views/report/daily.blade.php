<x-app-layout>
    
    {{-- HEADER PAGE --}}
    <div class="flex flex-col md:flex-row justify-between items-end mb-6">
        <div>
            <h2 class="font-extrabold text-2xl text-[#14213D] leading-tight flex items-center gap-2">
                <i class="fas fa-calendar-check text-[#FCA311]"></i> {{ __('Laporan Absensi') }}
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

                {{-- Info Count + Button Validator --}}
                <div class="w-full lg:flex-1 flex justify-end items-center gap-3 pb-[1px]">
                    @if(auth()->user()->role === 'admin')
                        <form method="POST" action="{{ route('report.validasi-pulang') }}" 
                              onsubmit="return confirm('⚠️ Validasi Absen Pulang?\n\nSemua siswa yang belum absen pulang hari ini akan otomatis ditandai ALPHA dengan jam sekarang.\n\nLanjutkan?');"
                              class="inline">
                            @csrf
                            <input type="hidden" name="tanggal" value="{{ $date }}">
                            <button type="submit" 
                                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-xl font-bold text-xs shadow-md transition-all flex items-center gap-2 whitespace-nowrap">
                                <i class="fas fa-check-double"></i> Validasi Absen Pulang
                            </button>
                        </form>
                    @endif
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
                        @if(auth()->user()->role === 'admin')
                            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @if($isWeekend)
                        {{-- TAMPILAN KHUSUS HARI LIBUR --}}
                        <tr>
                            <td colspan="{{ auth()->user()->role === 'admin' ? '6' : '5' }}" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <div class="bg-blue-100 rounded-full p-4 mb-2">
                                        <i class="fas fa-calendar-times text-blue-600 text-4xl"></i>
                                    </div>
                                    <h3 class="text-xl font-bold text-blue-700">Hari Libur</h3>
                                    <p class="text-sm text-gray-600">
                                        {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
                                    </p>
                                    <span class="px-4 py-2 bg-blue-50 text-blue-700 rounded-full text-xs font-bold border border-blue-200">
                                        Tidak ada aktivitas absensi di hari libur
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @else
                        {{-- TAMPILAN NORMAL HARI KERJA --}}
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
                                            'Libur' => 'bg-sky-100 text-sky-700 border-sky-200',
                                            default => 'bg-red-100 text-red-700 border-red-200',
                                        };
                                    @endphp
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-extrabold rounded-full border {{ $badgeClass }}">
                                        {{ $absen->status_masuk }}
                                    </span>
                                @else
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-gray-100 text-gray-400 border border-gray-200">
                                         Belum Absen
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

                            {{-- Kolom Aksi (Hanya Admin) --}}
                            @if(auth()->user()->role === 'admin')
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button onclick="openEditModal({{ $absen ? $absen->id : 0 }}, {{ $student->id }}, '{{ $student->nama }}', '{{ $absen ? $absen->status_masuk : 'Alpha' }}', '{{ $absen && $absen->jam_masuk ? \Carbon\Carbon::parse($absen->jam_masuk)->format('H:i') : '' }}', '{{ $absen && $absen->jam_keluar ? \Carbon\Carbon::parse($absen->jam_keluar)->format('H:i') : '' }}', '{{ $date }}')" 
                                       class="bg-[#FCA311] hover:bg-orange-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all inline-flex items-center gap-1">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->role === 'admin' ? '6' : '5' }}" class="px-6 py-12 text-center text-gray-500 italic">
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
                    @endif
                </tbody>
            </table>
        </div>
        
    </div>

    {{-- MODAL EDIT ABSENSI (Hanya Admin) --}}
    @if(auth()->user()->role === 'admin')
    <div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            {{-- Header Modal --}}
            <div class="bg-[#14213D] text-white px-6 py-4 rounded-t-2xl flex justify-between items-center">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <i class="fas fa-edit text-[#FCA311]"></i> Edit Data Absensi
                </h3>
                <button onclick="closeEditModal()" class="text-white hover:text-[#FCA311] transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            {{-- Body Modal --}}
            <form id="editForm" method="POST" class="p-6">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="PUT">
                <input type="hidden" name="student_id" id="modal-student-id">
                <input type="hidden" name="tanggal" id="modal-tanggal">

                {{-- Info Siswa --}}
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
                    <p class="text-sm font-medium text-blue-800">Siswa:</p>
                    <p class="text-lg font-bold text-blue-900" id="modal-student-name"></p>
                    <p class="text-xs text-blue-600 mt-1">Tanggal: <span id="modal-date"></span></p>
                </div>

                {{-- Status Kehadiran --}}
                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-3">Status Kehadiran</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <label class="relative">
                            <input type="radio" name="status_masuk" value="Hadir" class="peer sr-only" required>
                            <div class="cursor-pointer border-2 border-gray-300 rounded-lg p-3 text-center transition-all peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-green-300">
                                <i class="fas fa-check-circle text-2xl text-green-600"></i>
                                <p class="text-xs font-bold mt-1">Hadir</p>
                            </div>
                        </label>

                        <label class="relative">
                            <input type="radio" name="status_masuk" value="Terlambat" class="peer sr-only">
                            <div class="cursor-pointer border-2 border-gray-300 rounded-lg p-3 text-center transition-all peer-checked:border-orange-500 peer-checked:bg-orange-50 hover:border-orange-300">
                                <i class="fas fa-clock text-2xl text-orange-600"></i>
                                <p class="text-xs font-bold mt-1">Terlambat</p>
                            </div>
                        </label>

                        <label class="relative">
                            <input type="radio" name="status_masuk" value="Sakit" class="peer sr-only">
                            <div class="cursor-pointer border-2 border-gray-300 rounded-lg p-3 text-center transition-all peer-checked:border-purple-500 peer-checked:bg-purple-50 hover:border-purple-300">
                                <i class="fas fa-notes-medical text-2xl text-purple-600"></i>
                                <p class="text-xs font-bold mt-1">Sakit</p>
                            </div>
                        </label>

                        <label class="relative">
                            <input type="radio" name="status_masuk" value="Izin" class="peer sr-only">
                            <div class="cursor-pointer border-2 border-gray-300 rounded-lg p-3 text-center transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-blue-300">
                                <i class="fas fa-envelope text-2xl text-blue-600"></i>
                                <p class="text-xs font-bold mt-1">Izin</p>
                            </div>
                        </label>

                        <label class="relative">
                            <input type="radio" name="status_masuk" value="Alpha" class="peer sr-only">
                            <div class="cursor-pointer border-2 border-gray-300 rounded-lg p-3 text-center transition-all peer-checked:border-red-500 peer-checked:bg-red-50 hover:border-red-300">
                                <i class="fas fa-ban text-2xl text-red-600"></i>
                                <p class="text-xs font-bold mt-1">Alpha</p>
                            </div>
                        </label>

                        <label class="relative">
                            <input type="radio" name="status_masuk" value="Libur" class="peer sr-only">
                            <div class="cursor-pointer border-2 border-gray-300 rounded-lg p-3 text-center transition-all peer-checked:border-sky-500 peer-checked:bg-sky-50 hover:border-sky-300">
                                <i class="fas fa-umbrella-beach text-2xl text-sky-600"></i>
                                <p class="text-xs font-bold mt-1">Libur</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Jam Masuk & Keluar --}}
                <div id="jam-section-modal" class="space-y-4 mb-6">
                    <p class="text-sm font-medium text-gray-700 mb-3" id="jam-section-title">⏰ Input Waktu Absensi</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Jam Masuk</label>
                            <input type="text" name="jam_masuk" id="modal-jam-masuk" 
                                   maxlength="5"
                                   placeholder="07:00"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-[#FCA311] focus:ring focus:ring-[#FCA311]/20">
                            <p class="text-xs text-gray-500 mt-1" id="jam-masuk-hint">Jam siswa tiba di sekolah</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Jam Keluar</label>
                            <input type="text" name="jam_keluar" id="modal-jam-keluar"
                                   maxlength="5"
                                   placeholder="15:00"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-[#FCA311] focus:ring focus:ring-[#FCA311]/20">
                            <p class="text-xs text-gray-500 mt-1" id="jam-keluar-hint">Jam siswa pulang dari sekolah</p>
                        </div>
                    </div>

                    <div id="jam-info-box" class="bg-blue-50 border-l-4 border-blue-500 p-3 text-sm">
                        <p class="font-medium text-blue-800">💡 Panduan:</p>
                        <ul class="text-blue-700 list-disc ml-5 mt-1 space-y-1 text-xs">
                            <li><strong>Hadir/Terlambat:</strong> Isi jam masuk & jam keluar</li>
                            <li><strong>Sakit/Izin (pulang awal):</strong> Isi jam masuk & jam keluar (saat pulang awal)</li>
                            <li><strong>Sakit/Izin (tidak masuk):</strong> Kosongkan semua jam</li>
                            <li><strong>Alpha:</strong> Kosongkan semua jam</li>
                        </ul>
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="flex gap-3 justify-end pt-4 border-t">
                    <button type="button" onclick="closeEditModal()" 
                            class="px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-bold transition-all">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-6 py-2.5 bg-[#FCA311] hover:bg-orange-600 text-white rounded-lg font-bold transition-all flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- JavaScript untuk Modal --}}
    <script>
        function openEditModal(attendanceId, studentId, nama, status, jamMasuk, jamKeluar, tanggal) {
            // Set form action dan method
            if (attendanceId > 0) {
                // Update existing record
                document.getElementById('editForm').action = `/attendance/${attendanceId}/update`;
                document.getElementById('form-method').value = 'PUT';
            } else {
                // Create new record
                document.getElementById('editForm').action = `/attendance/create`;
                document.getElementById('form-method').value = 'POST';
            }
            
            // Set hidden fields
            document.getElementById('modal-student-id').value = studentId;
            document.getElementById('modal-tanggal').value = tanggal;
            
            // Set data siswa
            document.getElementById('modal-student-name').textContent = nama;
            document.getElementById('modal-date').textContent = new Date(tanggal).toLocaleDateString('id-ID', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            
            // Set status (check radio button)
            document.querySelector(`input[name="status_masuk"][value="${status}"]`).checked = true;
            
            // Set jam
            document.getElementById('modal-jam-masuk').value = jamMasuk || '';
            document.getElementById('modal-jam-keluar').value = jamKeluar || '';
            
            // Toggle jam section based on status
            toggleJamSection(status);
            
            // Show modal
            document.getElementById('editModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function toggleJamSection(status) {
            const jamSection = document.getElementById('jam-section-modal');
            const jamMasukHint = document.getElementById('jam-masuk-hint');
            const jamKeluarHint = document.getElementById('jam-keluar-hint');
            const jamInfoBox = document.getElementById('jam-info-box');
            
            // Section jam selalu tampil untuk semua status
            jamSection.style.display = 'block';
            
            // Update hint berdasarkan status
            if (status === 'Sakit' || status === 'Izin') {
                jamMasukHint.textContent = 'Isi jika siswa sempat masuk lalu pulang awal';
                jamKeluarHint.textContent = 'Waktu siswa pulang awal karena ' + status.toLowerCase();
                jamInfoBox.classList.remove('border-blue-500', 'bg-blue-50', 'border-red-500', 'bg-red-50', 'border-sky-500', 'bg-sky-50');
                jamInfoBox.classList.add('border-yellow-500', 'bg-yellow-50');
                jamInfoBox.querySelector('.font-medium').className = 'font-medium text-yellow-800';
                jamInfoBox.querySelector('ul').className = 'text-yellow-700 list-disc ml-5 mt-1 space-y-1 text-xs';
            } else if (status === 'Alpha') {
                jamMasukHint.textContent = 'Kosongkan untuk Alpha';
                jamKeluarHint.textContent = 'Kosongkan untuk Alpha';
                jamInfoBox.classList.remove('border-yellow-500', 'bg-yellow-50', 'border-blue-500', 'bg-blue-50', 'border-sky-500', 'bg-sky-50');
                jamInfoBox.classList.add('border-red-500', 'bg-red-50');
                jamInfoBox.querySelector('.font-medium').className = 'font-medium text-red-800';
                jamInfoBox.querySelector('ul').className = 'text-red-700 list-disc ml-5 mt-1 space-y-1 text-xs';
            } else if (status === 'Libur') {
                jamMasukHint.textContent = 'Kosongkan untuk hari libur';
                jamKeluarHint.textContent = 'Kosongkan untuk hari libur';
                jamInfoBox.classList.remove('border-yellow-500', 'bg-yellow-50', 'border-red-500', 'bg-red-50', 'border-blue-500', 'bg-blue-50');
                jamInfoBox.classList.add('border-sky-500', 'bg-sky-50');
                jamInfoBox.querySelector('.font-medium').className = 'font-medium text-sky-800';
                jamInfoBox.querySelector('ul').className = 'text-sky-700 list-disc ml-5 mt-1 space-y-1 text-xs';
            } else {
                jamMasukHint.textContent = 'Jam siswa tiba di sekolah';
                jamKeluarHint.textContent = 'Jam siswa pulang dari sekolah';
                jamInfoBox.classList.remove('border-yellow-500', 'bg-yellow-50', 'border-red-500', 'bg-red-50', 'border-sky-500', 'bg-sky-50');
                jamInfoBox.classList.add('border-blue-500', 'bg-blue-50');
                jamInfoBox.querySelector('.font-medium').className = 'font-medium text-blue-800';
                jamInfoBox.querySelector('ul').className = 'text-blue-700 list-disc ml-5 mt-1 space-y-1 text-xs';
            }
        }

        // Event listener untuk radio button status
        document.querySelectorAll('input[name="status_masuk"]').forEach(radio => {
            radio.addEventListener('change', function() {
                toggleJamSection(this.value);
            });
        });

        // Close modal saat klik di luar
        document.getElementById('editModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });

        // Force 24-hour format for time inputs
        document.addEventListener('DOMContentLoaded', function() {
            const timeInputs = ['modal-jam-masuk', 'modal-jam-keluar'];
            
            timeInputs.forEach(id => {
                const input = document.getElementById(id);
                if (input) {
                    // Auto-format as user types
                    input.addEventListener('input', function(e) {
                        let value = e.target.value.replace(/[^0-9]/g, '');
                        
                        if (value.length >= 2) {
                            value = value.substring(0, 2) + ':' + value.substring(2, 4);
                        }
                        
                        e.target.value = value;
                    });
                    
                    // Validate on blur
                    input.addEventListener('blur', function(e) {
                        const value = e.target.value;
                        const timeRegex = /^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/;
                        
                        if (value && !timeRegex.test(value)) {
                            alert('Format jam tidak valid! Gunakan format HH:MM (00:00 - 23:59)');
                            e.target.value = '';
                        }
                    });
                }
            });
        });
    </script>
    @endif
</x-app-layout>