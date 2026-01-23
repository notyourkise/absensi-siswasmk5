<x-app-layout>
    
    {{-- LIBRARY SCANNER - Ganti dengan jsQR yang lebih ringan dan reliable --}}
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>

    {{-- HEADER PAGE --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <div>
            <h2 class="font-extrabold text-2xl text-[#14213D] leading-tight flex items-center gap-2">
                <i class="fas fa-qrcode text-[#FCA311]"></i> {{ __('Scan Absensi') }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">Terminal Absensi Digital SMKN 5 Samarinda</p>
        </div>
        
        {{-- JAM DIGITAL LIVE --}}
        <div class="mt-4 md:mt-0 text-right bg-[#14213D] text-white px-6 py-2 rounded-xl shadow-lg border-b-4 border-[#FCA311]">
            <div class="text-[10px] font-bold text-[#FCA311] uppercase tracking-widest">Waktu Sekarang</div>
            <div id="live-clock" class="text-2xl font-mono font-black tracking-wider">--:--:--</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- KOLOM KIRI: KAMERA (Lebar 5/12) --}}
        <div class="lg:col-span-5 flex flex-col gap-6">
            
            {{-- AREA SCANNER --}}
            <div class="bg-white p-1 rounded-2xl shadow-xl border border-gray-200 relative overflow-hidden group">
                
                {{-- HEADER KAMERA & TOMBOL KONTROL --}}
                <div class="bg-[#14213D] text-white px-4 py-3 rounded-t-xl flex justify-between items-center">
                    <span class="font-bold flex items-center gap-2">
                        <i class="fas fa-camera"></i> 
                        <span id="camera-status-text">Kamera Aktif</span>
                    </span>
                    
                    {{-- TOMBOL START / STOP --}}
                    <div class="flex gap-2">
                        <button id="btn-start" onclick="startScanner()" class="hidden bg-green-500 hover:bg-green-600 text-white text-xs font-bold px-3 py-1 rounded shadow transition">
                            <i class="fas fa-play mr-1"></i> MULAI
                        </button>
                        <button id="btn-stop" onclick="stopScanner()" class="bg-red-500 hover:bg-red-600 text-white text-xs font-bold px-3 py-1 rounded shadow transition">
                            <i class="fas fa-stop mr-1"></i> STOP
                        </button>
                    </div>
                </div>
                
                {{-- WADAH KAMERA --}}
                <div class="relative bg-gray-900 rounded-b-xl overflow-hidden" style="min-height: 400px;">
                    
                    {{-- VIDEO ELEMENT untuk stream kamera --}}
                    <video id="video" autoplay playsinline style="width: 100%; height: 100%; object-fit: cover; display: block; border-radius: 0 0 1rem 1rem;"></video>
                    
                    {{-- CANVAS untuk decode QR (hidden) --}}
                    <canvas id="canvas" style="display: none;"></canvas>
                    
                    {{-- Overlay "PAUSED" (Muncul saat Stop) --}}
                    <div id="paused-overlay" class="hidden absolute inset-0 bg-gray-900 flex flex-col items-center justify-center text-white" style="z-index: 100;">
                        <div class="w-20 h-20 bg-gray-800 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-video-slash text-3xl text-gray-500"></i>
                        </div>
                        <h3 class="font-bold text-lg">Scanner Dinonaktifkan</h3>
                        <p class="text-xs text-gray-400 mb-4">Klik tombol MULAI untuk mengaktifkan kembali.</p>
                        <button onclick="startScanner()" class="bg-[#FCA311] text-[#14213D] px-6 py-2 rounded-full font-bold shadow hover:scale-105 transition">
                            Aktifkan Kamera
                        </button>
                    </div>

                    {{-- Hiasan Garis Laser (Hanya muncul saat Scanning) --}}
                    @if(!session('success'))
                    <div id="laser-line" class="absolute inset-0 pointer-events-none flex flex-col justify-center items-center opacity-50" style="z-index: 10;">
                        <div class="w-full h-0.5 bg-red-500 shadow-[0_0_15px_rgba(255,0,0,0.8)] animate-scan"></div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- INPUT MANUAL (BACKUP) --}}
            <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase mb-2">Alternatif (Input Manual)</p>
                <form id="form-scan" action="{{ route('scan.store') }}" method="POST">
                    @csrf
                    <div class="flex gap-2">
                        <input type="text" id="nisn-input" name="nisn" 
                            class="w-full bg-[#E5E5E5] border-transparent rounded-lg px-4 py-2 text-sm font-bold text-[#14213D] focus:bg-white focus:ring-2 focus:ring-[#FCA311] transition" 
                            placeholder="Ketik NISN Siswa..." autocomplete="off">
                        <button type="submit" class="bg-[#14213D] hover:bg-[#0f1a30] text-white px-4 py-2 rounded-lg transition">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
                {{-- Error Message --}}
                @if(session('error'))
                    <div class="mt-3 p-3 bg-red-100 border-l-4 border-red-500 text-red-700 text-xs font-bold rounded flex items-center gap-2 animate-pulse">
                        <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                    </div>
                @endif
            </div>

        </div>

        {{-- KOLOM KANAN: HASIL SCAN & HISTORY (Lebar 7/12) --}}
        <div class="lg:col-span-7 flex flex-col gap-6">

            {{-- AREA HASIL SCAN --}}
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden flex flex-col justify-center min-h-[350px] relative">
                
                {{-- Background Hiasan --}}
                <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-[#FCA311] rounded-full opacity-10 blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-[#14213D] rounded-full opacity-5 blur-3xl"></div>

                @if(session('success') && session('student_data'))
                    @php
                        $mhs = session('student_data');
                        $fotoPath = $mhs->foto ? asset('storage/students/' . $mhs->foto) : 'https://ui-avatars.com/api/?name='.urlencode($mhs->nama).'&background=14213D&color=fff&size=256';
                        
                        $statusColor = session('status_color'); 
                        $ringColor = match($statusColor) {
                            'orange' => 'ring-[#FCA311] text-[#FCA311]',
                            'blue' => 'ring-blue-500 text-blue-600',
                            default => 'ring-green-500 text-green-600',
                        };
                        $bgBadge = match($statusColor) {
                            'orange' => 'bg-orange-100 text-orange-800',
                            'blue' => 'bg-blue-100 text-blue-800',
                            default => 'bg-green-100 text-green-800',
                        };
                    @endphp

                    {{-- TAMPILAN SUKSES --}}
                    <div class="flex flex-col items-center justify-center p-8 z-10 animate-fade-in-up">
                        
                        <div class="relative mb-6">
                            <div class="absolute inset-0 rounded-full animate-ping opacity-20 {{ str_replace('text', 'bg', $ringColor) }}"></div>
                            <img src="{{ $fotoPath }}" class="w-40 h-40 rounded-full object-cover ring-8 ring-offset-4 {{ $ringColor }} shadow-2xl">
                            <div class="absolute bottom-0 right-0 bg-white rounded-full p-2 shadow-lg text-2xl">
                                @if($statusColor == 'orange') ⚠️ @elseif($statusColor == 'blue') 👋 @else ✅ @endif
                            </div>
                        </div>

                        <h2 class="text-3xl font-black text-[#14213D] mb-1 text-center">{{ $mhs->nama }}</h2>
                        <div class="flex items-center gap-2 text-gray-500 font-medium mb-6">
                            <span class="bg-gray-100 px-2 py-1 rounded text-xs border border-gray-200">{{ $mhs->kelas }}</span>
                            <span>•</span>
                            <span class="font-mono text-sm">{{ $mhs->nisn }}</span>
                        </div>

                        <div class="px-8 py-3 rounded-full text-lg font-bold shadow-sm {{ $bgBadge }}">
                            {{ session('success') }}
                        </div>

                        <audio autoplay>
                            <source src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" type="audio/mpeg">
                        </audio>
                    </div>

                @else
                    {{-- TAMPILAN STANDBY --}}
                    <div class="flex flex-col items-center justify-center text-gray-400 p-8 z-10 opacity-60">
                        <div class="w-32 h-32 bg-gray-100 rounded-full flex items-center justify-center mb-4 animate-pulse">
                            <i class="fas fa-id-card text-5xl text-gray-300"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-500">Menunggu Scan...</h3>
                        <p class="text-sm">Silakan arahkan kartu pelajar ke kamera</p>
                    </div>
                @endif
            </div>

            {{-- TABEL RIWAYAT --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden flex-1">
                <div class="bg-[#14213D] px-4 py-3 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-white text-sm">Riwayat Aktivitas</h3>
                    <span class="text-[10px] bg-[#FCA311] text-[#14213D] px-2 py-0.5 rounded font-bold">LIVE</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Jam</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Nama</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Kelas</th>
                                <th class="px-4 py-2 text-center text-xs font-bold text-gray-500 uppercase">Ket</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($latest_scans as $scan)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-2 font-mono text-[#14213D] font-bold">
                                        {{ \Carbon\Carbon::parse($scan->updated_at)->format('H:i') }}
                                    </td>
                                    <td class="px-4 py-2 font-medium text-gray-700 truncate max-w-[150px]">
                                        {{ $scan->student->nama }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-500">{{ $scan->student->kelas }}</td>
                                    <td class="px-4 py-2 text-center">
                                        @if($scan->jam_keluar)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">PLG</span>
                                        @elseif($scan->status_masuk == 'Terlambat')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-orange-100 text-orange-700">TLT</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700">MSK</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- CUSTOM CSS ANIMATION --}}
    <style>
        @keyframes scan {
            0%, 100% { top: 0%; opacity: 0; }
            50% { opacity: 1; }
            100% { top: 100%; opacity: 0; }
        }
        .animate-scan {
            animation: scan 2.5s cubic-bezier(0.4, 0, 0.2, 1) infinite;
            position: absolute;
            width: 100%;
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease-out forwards;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    {{-- SCRIPT --}}
    <script>
        // --- 1. JAM REALTIME ---
        function updateClock() {
            const now = new Date();
            document.getElementById('live-clock').textContent = now.toLocaleTimeString('id-ID', { hour12: false });
        }
        setInterval(updateClock, 1000);
        updateClock();

        // --- 2. LOGIC AUTO REFRESH ---
        @if(session('success') || session('error'))
            setTimeout(function() {
                window.location.href = "{{ route('scan.index') }}";
            }, 3500);
        @endif

        // --- 3. SCANNER LOGIC (NATIVE IMPLEMENTATION) ---
        @if(!session('success'))
            
            let video = document.getElementById('video');
            let canvas = document.getElementById('canvas');
            let canvasContext = canvas.getContext('2d');
            let scanning = false;
            let stream = null;

            // Fungsi untuk Memulai Scanner
            async function startScanner() {
                try {
                    console.log("Starting camera...");
                    
                    // UI Updates
                    document.getElementById('btn-start').classList.add('hidden');
                    document.getElementById('btn-stop').classList.remove('hidden');
                    document.getElementById('paused-overlay').classList.add('hidden');
                    document.getElementById('camera-status-text').textContent = "Kamera Aktif";
                    document.getElementById('camera-status-text').classList.remove('text-red-400');
                    if(document.getElementById('laser-line')) document.getElementById('laser-line').classList.remove('hidden');

                    // Cek browser support
                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        throw new Error("Browser tidak mendukung akses kamera");
                    }

                    // Request kamera dengan constraints yang tepat
                    const constraints = {
                        video: { 
                            facingMode: { ideal: "environment" },
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        },
                        audio: false
                    };

                    stream = await navigator.mediaDevices.getUserMedia(constraints);
                    console.log("Camera stream obtained:", stream);
                    
                    // Attach stream ke video element
                    video.srcObject = stream;
                    video.setAttribute('playsinline', true);
                    video.play();
                    
                    console.log("Video element playing");
                    
                    scanning = true;
                    
                    // Tunggu video ready lalu mulai scan
                    video.addEventListener('loadedmetadata', () => {
                        console.log("Video metadata loaded, starting scan loop");
                        requestAnimationFrame(tick);
                    });
                    
                } catch (error) {
                    console.error("Error accessing camera:", error);
                    let errorMsg = "Gagal mengakses kamera: ";
                    
                    if (error.name === 'NotAllowedError') {
                        errorMsg += "Izin kamera ditolak. Silakan izinkan akses kamera di browser.";
                    } else if (error.name === 'NotFoundError') {
                        errorMsg += "Kamera tidak ditemukan.";
                    } else if (error.name === 'NotReadableError') {
                        errorMsg += "Kamera sedang digunakan aplikasi lain.";
                    } else {
                        errorMsg += error.message;
                    }
                    
                    alert(errorMsg);
                    
                    // Rollback UI
                    document.getElementById('btn-start').classList.remove('hidden');
                    document.getElementById('btn-stop').classList.add('hidden');
                }
            }

            // Fungsi untuk Menghentikan Scanner
            function stopScanner() {
                console.log("Stopping camera...");
                scanning = false;
                
                if (stream) {
                    stream.getTracks().forEach(track => {
                        track.stop();
                        console.log("Track stopped:", track);
                    });
                    stream = null;
                }
                
                video.srcObject = null;
                
                // UI Updates
                document.getElementById('btn-start').classList.remove('hidden');
                document.getElementById('btn-stop').classList.add('hidden');
                document.getElementById('paused-overlay').classList.remove('hidden');
                document.getElementById('camera-status-text').textContent = "Kamera Nonaktif";
                document.getElementById('camera-status-text').classList.add('text-red-400');
                if(document.getElementById('laser-line')) document.getElementById('laser-line').classList.add('hidden');
            }

            // Loop untuk scan QR code
            function tick() {
                if (!scanning) return;
                
                if (video.readyState === video.HAVE_ENOUGH_DATA) {
                    // Set ukuran canvas sesuai video
                    canvas.height = video.videoHeight;
                    canvas.width = video.videoWidth;
                    
                    // Draw frame dari video ke canvas
                    canvasContext.drawImage(video, 0, 0, canvas.width, canvas.height);
                    
                    // Get image data untuk di-scan
                    const imageData = canvasContext.getImageData(0, 0, canvas.width, canvas.height);
                    
                    // Scan QR code menggunakan jsQR
                    const code = jsQR(imageData.data, imageData.width, imageData.height, {
                        inversionAttempts: "dontInvert",
                    });
                    
                    if (code) {
                        console.log("QR Code detected:", code.data);
                        handleQRCode(code.data);
                        return; // Stop loop setelah berhasil scan
                    }
                }
                
                // Continue scanning
                requestAnimationFrame(tick);
            }

            // Handle QR code yang terdeteksi
            function handleQRCode(qrData) {
                scanning = false;
                
                console.log("Processing QR data:", qrData);
                
                // Sound effect
                let audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
                audio.play().catch(e => console.log('Audio play failed:', e));
                
                // Submit form
                document.getElementById('nisn-input').value = qrData;
                document.getElementById('form-scan').submit();
                
                // Stop camera
                stopScanner();
            }

            // Auto start saat page load
            document.addEventListener("DOMContentLoaded", function() {
                console.log("DOM loaded, starting scanner in 500ms...");
                setTimeout(() => {
                    startScanner();
                }, 500);
            });

            // Cleanup saat page close
            window.addEventListener('beforeunload', function() {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                }
            });

        @endif
    </script>
</x-app-layout>