<!DOCTYPE html>
<html>
<head>
    <title>Cetak Kartu Identitas Masal - {{ $kelas }}</title>
    <style>
        /* --- SETTING KERTAS A4 --- */
        @page { 
            margin: 0; 
            size: a4 portrait; 
        }
        
        body { 
            font-family: 'Arial', sans-serif; 
            margin: 0; 
            padding: 10mm; /* Margin luar kertas */
            background-color: #fff; 
        }

        /* --- CONTAINER UTAMA --- */
        .page-wrapper {
            width: 100%;
        }

        /* --- GRID UNTUK CETAK BANYAK (2x2) --- */
        .card-container {
            display: inline-block;
            vertical-align: top;
            width: 70mm;
            height: 115mm;
            margin: 5mm; /* Jarak antar kartu untuk ruang potong */
            position: relative;
            border: 1px solid #aaa;
            background-color: #fff;
            overflow: hidden;
            -webkit-print-color-adjust: exact;
        }

        /* --- 1. HEADER (LOGO & JUDUL) --- */
        .logo-container {
            position: absolute; top: 4mm; 
            width: 100%; text-align: center; z-index: 2;
        }
        .logo-img { width: 11mm; height: auto; margin: 0 3mm; vertical-align: middle; }

        .school-title {
            position: absolute; top: 16mm; 
            width: 100%; text-align: center; color: #006400; 
            font-size: 8pt; font-weight: 900; z-index: 2; text-transform: uppercase;
        }

        .header-line {
            position: absolute; top: 21mm; 
            left: 15%; width: 70%; height: 1mm; background-color: #FFD700; z-index: 1;
        }

        /* --- 2. FOTO PROFIL --- */
        .photo-container {
            position: absolute; 
            top: 25mm; 
            width: 100%; text-align: center; z-index: 3;
        }
        .photo-frame {
            width: 32mm; height: 32mm; 
            background: white; padding: 1mm;
            border-radius: 50%; border: 1mm solid #FFD700; margin: 0 auto; overflow: hidden;
        }
        .student-photo { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }

        /* --- 3. DATA SISWA --- */
        .info-container {
            position: absolute; 
            top: 61mm; 
            width: 100%; text-align: center; z-index: 4;
        }
        .student-name {
            font-size: 11pt; font-weight: 900; color: #000; text-transform: uppercase;
            margin-bottom: 1mm; line-height: 1.1; padding: 0 2mm;
            height: 10mm; display: flex; align-items: center; justify-content: center;
        }
        .student-nisn {
            font-size: 9pt; color: #444; font-weight: bold; margin-bottom: 2mm;
        }
        .student-class-box {
            display: inline-block; background-color: #006400; color: white;
            padding: 1.5mm 5mm; border-radius: 4mm; font-size: 8pt; font-weight: bold;
        }

        /* --- 4. QR CODE --- */
        .qr-container {
            position: absolute;
            bottom: 12mm; 
            width: 100%; text-align: center; z-index: 5;
        }
        .qr-img {
            width: 22mm; height: 22mm; 
            background: white; padding: 1mm; 
        }

        /* --- 5. FOOTER TEXT --- */
        .footer-text {
            position: absolute;
            bottom: 4mm; 
            width: 100%; text-align: center; 
            font-size: 6pt; color: #999; 
            text-transform: uppercase; letter-spacing: 0.5px;
            font-weight: bold;
        }

        /* Watermark Background */
        .watermark {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            width: 45mm; opacity: 0.08; z-index: 0; filter: grayscale(100%);
        }

        /* Utility Page Break */
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <div class="page-wrapper">
        {{-- Membagi data siswa menjadi grup berisi 4 per halaman --}}
        @foreach($students->chunk(4) as $chunk)
            @foreach($chunk as $student)
                <div class="card-container">
                    
                    {{-- Watermark --}}
                    <img src="{{ public_path('logo/sekolah.png') }}" class="watermark">

                    {{-- 1. Logo Header --}}
                    <div class="logo-container">
                        <img src="{{ public_path('logo/kaltim.webp') }}" class="logo-img">
                        <img src="{{ public_path('logo/sekolah.png') }}" class="logo-img">
                    </div>

                    {{-- 2. Nama Sekolah --}}
                    <div class="school-title">SMK NEGERI 5 SAMARINDA</div>

                    {{-- 3. Garis Kuning --}}
                    <div class="header-line"></div>

                    {{-- 4. Foto Profil --}}
                    <div class="photo-container">
                        <div class="photo-frame">
                            @if($student->foto && file_exists(public_path('storage/students/'.$student->foto)))
                                <img src="{{ public_path('storage/students/'.$student->foto) }}" class="student-photo">
                            @else
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($student->nama) }}&background=006400&color=fff" class="student-photo">
                            @endif
                        </div>
                    </div>

                    {{-- 5. Info Siswa --}}
                    <div class="info-container">
                        <div class="student-name">{{ $student->nama }}</div>
                        <div class="student-nisn">NISN: {{ $student->nisn }}</div>
                        <div class="student-class-box">{{ $student->kelas }}</div>
                    </div>

                    {{-- 6. QR Code (Format SVG agar tajam) --}}
                    <div class="qr-container">
                        <img src="data:image/svg+xml;base64, {{ base64_encode(QrCode::format('svg')->size(100)->margin(0)->generate($student->nisn)) }}" class="qr-img">
                    </div>

                    {{-- 7. Footer --}}
                    <div class="footer-text">KARTU PELAJAR & ABSENSI DIGITAL</div>

                </div>
            @endforeach

            {{-- Paksa ganti halaman setelah 4 kartu (kecuali halaman terakhir) --}}
            @if (!$loop->last)
                <div class="page-break"></div>
            @endif
        @endforeach
    </div>

</body>
</html>