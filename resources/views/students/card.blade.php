<!DOCTYPE html>
<html>
<head>
    <title>ID Card - {{ $student->nama }}</title>
    <style>
        /* --- SETTING KERTAS A4 --- */
        @page { margin: 0; size: a4 portrait; }
        body { font-family: 'Arial', sans-serif; margin: 0; padding: 20px; background-color: #fff; }

        /* --- CONTAINER KARTU --- */
        .lanyard-card {
            width: 70mm;
            /* SOLUSI UTAMA: Tinggi ditambah 1cm (115mm) biar lega */
            height: 115mm; 
            border: 1px solid #aaa; 
            position: relative;
            background-color: #fff;
            float: left;
            margin-right: 10mm;
            margin-bottom: 10mm;
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
            top: 25mm; /* Posisi Foto */
            width: 100%; text-align: center; z-index: 3;
        }
        .photo-frame {
            width: 32mm; height: 32mm; /* Ukuran Foto proporsional */
            background: white; padding: 1mm;
            border-radius: 50%; border: 1mm solid #FFD700; margin: 0 auto; overflow: hidden;
        }
        .student-photo { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }

        /* --- 3. DATA SISWA (Nama, NISN, Kelas) --- */
        .info-container {
            position: absolute; 
            top: 61mm; /* Jarak aman di bawah foto */
            width: 100%; text-align: center; z-index: 4;
        }
        .student-name {
            font-size: 11pt; font-weight: 900; color: #000; text-transform: uppercase;
            margin-bottom: 1mm; line-height: 1.1; padding: 0 2mm;
        }
        .student-nisn {
            font-size: 9pt; color: #444; font-weight: bold; margin-bottom: 2mm;
        }
        .student-class-box {
            display: inline-block; background-color: #006400; color: white;
            padding: 1.5mm 5mm; border-radius: 4mm; font-size: 8pt; font-weight: bold;
        }

        /* --- 4. QR CODE (DIPOSISIKAN DARI BAWAH) --- */
        .qr-container {
            position: absolute;
            /* KUNCI SUKSES: Tempel ke bawah setinggi 12mm dari dasar kartu */
            /* Ini menjamin QR Code TIDAK AKAN PERNAH menimpa Footer */
            bottom: 12mm; 
            width: 100%; text-align: center; z-index: 5;
        }
        .qr-img {
            width: 22mm; height: 22mm; /* Ukuran pas, tidak terlalu besar/kecil */
            background: white; padding: 1mm; /* Frame putih biar barcode jelas */
        }

        /* --- 5. FOOTER TEXT (DIPOSISIKAN DARI BAWAH) --- */
        .footer-text {
            position: absolute;
            bottom: 4mm; /* Mepet dasar kartu */
            width: 100%; text-align: center; 
            font-size: 6pt; color: #999; 
            text-transform: uppercase; letter-spacing: 0.5px;
        }

        /* Watermark */
        .watermark {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            width: 45mm; opacity: 0.08; z-index: 0; filter: grayscale(100%);
        }
    </style>
</head>
<body>

    <div class="lanyard-card">
        
        {{-- Watermark Logo di Tengah Background --}}
        <img src="{{ public_path('logo/sekolah.png') }}" class="watermark">

        {{-- 1. Logo (Paling Atas) --}}
        <div class="logo-container">
            <img src="{{ public_path('logo/kaltim.webp') }}" class="logo-img">
            <img src="{{ public_path('logo/sekolah.png') }}" class="logo-img">
        </div>

        {{-- 2. Nama Sekolah (Warna Hijau Gelap) --}}
        <div class="school-title">SMK NEGERI 5 SAMARINDA</div>

        {{-- 3. Garis Kuning (Pemisah) --}}
        <div class="header-line"></div>

        {{-- 4. Foto Profil (Aman tidak nabrak garis) --}}
        <div class="photo-container">
            <div class="photo-frame">
                @if(isset($student->foto_base64))
                    {{-- Gunakan foto yang sudah di-resize dan di-optimize --}}
                    <img src="{{ $student->foto_base64 }}" class="student-photo">
                @elseif($student->foto)
                    <img src="{{ public_path('storage/students/'.$student->foto) }}" class="student-photo">
                @else
                    {{-- Fallback ke avatar placeholder (base64 agar tidak perlu load eksternal) --}}
                    <div style="width:100%;height:100%;background:#006400;display:flex;align-items:center;justify-content:center;color:white;font-size:18pt;font-weight:bold;border-radius:50%;">
                        {{ strtoupper(substr($student->nama, 0, 1)) }}
                    </div>
                @endif
            </div>
        </div>

        {{-- 5. Info Siswa --}}
        <div class="info-container">
            <div class="student-name">{{ $student->nama }}</div>
            <div class="student-nisn">NISN: {{ $student->nisn }}</div>
            <div class="student-class-box">{{ $student->kelas }}</div>
        </div>

        {{-- 6. QR Code --}}
        <div class="qr-container">
            <img src="data:image/svg+xml;base64, {{ base64_encode(QrCode::format('svg')->size(100)->generate($student->nisn)) }}" class="qr-img">
        </div>

        {{-- Footer --}}
        <div class="footer-text">KARTU PELAJAR & ABSENSI DIGITAL</div>

    </div>

</body>
</html>