<x-app-layout>
    
   {{-- 
        HEADER DASHBOARD (DENGAN JAM REALTIME)
    --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-8">
        <div>
            <h2 class="text-3xl font-extrabold text-[#14213D]">Dashboard Eksekutif</h2>
            <p class="text-sm text-gray-500 mt-1">Pantauan Statistik SMKN 5 Samarinda</p>
        </div>
        
        <div class="mt-4 md:mt-0">
            <div class="bg-[#14213D] text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-4 border-b-4 border-[#FCA311]">
                
                <div class="flex items-center gap-2 border-r border-white/20 pr-4">
                    <i class="fas fa-calendar-day text-[#FCA311]"></i>
                    <div class="flex flex-col text-right">
                        <span id="header-day" class="text-[10px] font-bold uppercase tracking-widest text-gray-300 leading-tight">
                            {{ \Carbon\Carbon::now()->translatedFormat('l') }}
                        </span>
                        <span id="header-date" class="text-xs font-bold leading-tight">
                            {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <i class="fas fa-clock text-gray-400 text-xs"></i>
                    <span id="header-clock" class="text-2xl font-mono font-black text-[#FCA311] tracking-widest min-w-[120px] text-center">
                        00:00:00
                    </span>
                    <span class="text-[10px] font-bold text-gray-400 self-end mb-1">WITA</span>
                </div>

            </div>
        </div>
    </div>

    {{-- 
        BAGIAN 1: KARTU STATISTIK HARIAN
        Data diambil dari Controller: $totalSiswa, $hadirHariIni, $telatHariIni, $alphaHariIni
    --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        <div class="bg-white rounded-2xl shadow-sm p-6 border-l-8 border-[#14213D] flex items-center justify-between hover:shadow-lg transition-all duration-300">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Siswa</p>
                <h3 class="text-4xl font-black text-[#14213D] mt-2">{{ $totalSiswa }}</h3> 
            </div>
            <div class="w-14 h-14 bg-[#E5E5E5] rounded-full flex items-center justify-center text-[#14213D] text-2xl shadow-inner">
                <i class="fas fa-users"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-6 border-l-8 border-green-500 flex items-center justify-between hover:shadow-lg transition-all duration-300">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Hadir Hari Ini</p>
                <h3 class="text-4xl font-black text-[#14213D] mt-2">{{ $hadirHariIni }}</h3>
            </div>
            <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center text-green-600 text-2xl shadow-inner">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-6 border-l-8 border-[#FCA311] flex items-center justify-between hover:shadow-lg transition-all duration-300">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Terlambat</p>
                <h3 class="text-4xl font-black text-[#14213D] mt-2">{{ $telatHariIni }}</h3>
            </div>
            <div class="w-14 h-14 bg-yellow-100 rounded-full flex items-center justify-center text-[#FCA311] text-2xl shadow-inner">
                <i class="fas fa-running"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-6 border-l-8 border-red-500 flex items-center justify-between hover:shadow-lg transition-all duration-300">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Belum Absen</p>
                <h3 class="text-4xl font-black text-[#14213D] mt-2">{{ $alphaHariIni }}</h3>
            </div>
            <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center text-red-500 text-2xl shadow-inner">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
    </div>

    {{-- 
        BAGIAN 2 & 3: GRAFIK & FILTER BULANAN
        Menggunakan Layout Grid 2 Kolom (Kiri Besar, Kanan Kecil)
    --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm p-6 border border-gray-100 relative overflow-hidden">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="font-bold text-[#14213D] text-lg">Tren Kehadiran (7 Hari Terakhir)</h3>
                    <p class="text-xs text-gray-400">Grafik jumlah siswa yang hadir tepat waktu & terlambat</p>
                </div>
                <button class="bg-[#E5E5E5] text-[#14213D] p-2 rounded-lg hover:bg-gray-200 transition">
                    <i class="fas fa-chart-line"></i>
                </button>
            </div>
            
            <div class="relative h-72 w-full">
                <canvas id="dailyTrendChart"></canvas>
            </div>
        </div>

        <div class="bg-[#14213D] rounded-2xl shadow-lg p-6 text-white relative">
            <div class="absolute top-0 right-0 -mt-6 -mr-6 w-32 h-32 bg-[#FCA311] rounded-full opacity-10 blur-2xl"></div>
            
            <h3 class="font-bold text-[#FCA311] text-lg mb-1 border-b border-white/10 pb-4">
                <i class="fas fa-filter mr-2"></i> Rekap Bulanan
            </h3>

            {{-- FORM FILTER BULAN & TAHUN --}}
            <form action="{{ route('dashboard') }}" method="GET" class="mt-4 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    {{-- Select Bulan --}}
                    <div>
                        <label class="text-[10px] uppercase font-bold text-gray-400 mb-1 block">Bulan</label>
                        <select name="bulan" onchange="this.form.submit()" 
                                class="w-full bg-[#0f1a30] border border-gray-600 text-white text-sm rounded-lg focus:ring-[#FCA311] focus:border-[#FCA311]">
                            @foreach($bulanOptions as $key => $val)
                                <option value="{{ $key }}" {{ $filterBulan == $key ? 'selected' : '' }}>
                                    {{ $val }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Select Tahun --}}
                    <div>
                        <label class="text-[10px] uppercase font-bold text-gray-400 mb-1 block">Tahun</label>
                        <select name="tahun" onchange="this.form.submit()"
                                class="w-full bg-[#0f1a30] border border-gray-600 text-white text-sm rounded-lg focus:ring-[#FCA311] focus:border-[#FCA311]">
                            @for($i = date('Y'); $i >= date('Y')-2; $i--)
                                <option value="{{ $i }}" {{ $filterTahun == $i ? 'selected' : '' }}>
                                    {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
            </form>

            {{-- HASIL STATISTIK BULANAN --}}
            <div class="mt-8 space-y-6">
                <div class="bg-white/5 p-4 rounded-xl border border-white/10">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-medium text-gray-300">Tepat Waktu</span>
                        <span class="text-xl font-bold text-green-400">{{ $rekapTepat }}</span>
                    </div>
                    {{-- Progress Bar Visual --}}
                    @php 
                        $totalRekap = $rekapTepat + $rekapTelat;
                        $persenTepat = $totalRekap > 0 ? ($rekapTepat / $totalRekap) * 100 : 0;
                    @endphp
                    <div class="w-full bg-gray-700 rounded-full h-1.5">
                        <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ $persenTepat }}%"></div>
                    </div>
                </div>

                <div class="bg-white/5 p-4 rounded-xl border border-white/10">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-medium text-gray-300">Terlambat</span>
                        <span class="text-xl font-bold text-[#FCA311]">{{ $rekapTelat }}</span>
                    </div>
                    @php 
                        $persenTelat = $totalRekap > 0 ? ($rekapTelat / $totalRekap) * 100 : 0;
                    @endphp
                    <div class="w-full bg-gray-700 rounded-full h-1.5">
                        <div class="bg-[#FCA311] h-1.5 rounded-full" style="width: {{ $persenTelat }}%"></div>
                    </div>
                </div>
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('report.index') }}" class="text-xs text-[#FCA311] hover:text-white hover:underline transition">
                    Lihat Laporan Lengkap &rarr;
                </a>
            </div>
        </div>

    </div>

    {{-- SCRIPT CHART.JS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('dailyTrendChart').getContext('2d');
            
            // Mengambil Data dari Controller PHP ke JavaScript
            const labels = @json($chartLabels);
            const data = @json($chartData);

            new Chart(ctx, {
                type: 'line', // Jenis Grafik Garis
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Kehadiran',
                        data: data,
                        borderColor: '#14213D', // Warna Garis Navy
                        backgroundColor: 'rgba(20, 33, 61, 0.1)', // Warna Arsiran Bawah Transparan
                        borderWidth: 3,
                        pointBackgroundColor: '#FCA311', // Warna Titik Orange
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        tension: 0.4, // Membuat garis melengkung halus (spline)
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }, // Sembunyikan legenda agar bersih
                        tooltip: {
                            backgroundColor: '#14213D',
                            titleColor: '#FCA311',
                            bodyColor: '#fff',
                            padding: 10,
                            cornerRadius: 8,
                            displayColors: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { borderDash: [5, 5], color: '#e5e5e5' },
                            ticks: { font: { family: 'Inter' } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'Inter' } }
                        }
                    }
                }
            });
        });
    </script>
    {{-- 
        SCRIPT JAM REALTIME 
        (Letakkan kode ini di bagian paling bawah file dashboard.blade.php, sebelum tag penutup </x-app-layout>)
    --}}
    <script>
        function updateDashboardClock() {
            const now = new Date();
            
            // Format Jam (HH:mm:ss)
            const timeString = now.toLocaleTimeString('id-ID', { 
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

            // Update elemen HTML
            document.getElementById('header-clock').textContent = timeString;
            
            // Opsional: Update tanggal juga jika berganti hari saat dashboard terbuka
            // const optionsDate = { day: 'numeric', month: 'long', year: 'numeric' };
            // const optionsDay = { weekday: 'long' };
            // document.getElementById('header-date').textContent = now.toLocaleDateString('id-ID', optionsDate);
            // document.getElementById('header-day').textContent = now.toLocaleDateString('id-ID', optionsDay);
        }

        // Jalankan setiap 1 detik
        setInterval(updateDashboardClock, 1000);
        
        // Jalankan segera saat halaman dimuat (agar tidak nunggu 1 detik baru muncul)
        updateDashboardClock();
    </script>

</x-app-layout>