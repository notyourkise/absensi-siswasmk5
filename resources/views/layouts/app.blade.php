<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SMKN 5 Samarinda') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Tambahkan ?v=2 di belakang nama file --}}
    <link rel="icon" href="{{ asset('logo/sekolah.png') }}?v=2" type="image/png">
    <link rel="shortcut icon" href="{{ asset('logo/sekolah.png') }}?v=2" type="image/png">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- SweetAlert2 CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        
        /* Transisi halus untuk lebar sidebar */
        .sidebar-transition {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>
<body class="font-sans antialiased bg-[#E5E5E5]">
    
    {{-- 
        x-data STATE:
        - sidebarOpen: Untuk Mobile (Buka/Tutup Overlay)
        - sidebarCollapsed: Untuk Desktop (Kecilkan jadi ikon saja)
        - Kita simpan di localStorage agar browser 'ingat' posisi terakhir user
    --}}
    <div x-data="{ 
            sidebarOpen: false, 
            sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
            toggleCollapse() {
                this.sidebarCollapsed = !this.sidebarCollapsed;
                localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
            }
         }" 
         class="flex h-screen overflow-hidden">
        
        <aside class="sidebar-transition absolute inset-y-0 left-0 z-50 bg-[#14213D] text-white shadow-xl
                      transform lg:static lg:translate-x-0"
               :class="{
                   'translate-x-0': sidebarOpen,
                   '-translate-x-full': !sidebarOpen,
                   'w-64': !sidebarCollapsed,
                   'w-20': sidebarCollapsed
               }">
            
            <div class="relative flex items-center h-20 border-b border-white/10 bg-[#0f1a30]"
                 :class="sidebarCollapsed ? 'justify-center px-0' : 'justify-start px-6'">
                
                <img src="{{ asset('logo/sekolah.png') }}" class="w-8 h-8 transition-all duration-300" alt="Logo">
                
                <div x-show="!sidebarCollapsed" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform -translate-x-2"
                     x-transition:enter-end="opacity-100 transform translate-x-0"
                     class="ml-3">
                    <span class="block text-lg font-bold tracking-wider text-[#FCA311]">
                        @if(Auth::user()->role == 'admin')
                            ADMINISTRATOR
                        @elseif(Auth::user()->role == 'petugas')
                            PETUGAS PIKET
                        @else
                            WALI KELAS
                        @endif
                    </span>
                    <span class="block text-[10px] text-gray-400">SMKN 5 Samarinda</span>
                </div>

                <button @click="toggleCollapse()" 
                        class="hidden lg:flex absolute -right-3 top-8 bg-[#FCA311] text-[#14213D] rounded-full p-1 shadow-lg hover:bg-white hover:scale-110 transition items-center justify-center w-6 h-6 z-50">
                    <i class="fas text-xs" :class="sidebarCollapsed ? 'fa-chevron-right' : 'fa-chevron-left'"></i>
                </button>
            </div>

            <nav class="mt-5 px-3 space-y-2 overflow-y-auto h-[calc(100vh-140px)]">
                
                @php
                    $baseClass = "flex items-center py-3 rounded-lg transition-all duration-200 group relative";
                    $activeClass = "bg-[#FCA311] text-[#14213D] font-bold shadow-md";
                    $inactiveClass = "text-gray-300 hover:bg-white/10 hover:text-[#FCA311]";
                @endphp

                {{-- 1. DASHBOARD (SEMUA ROLE) --}}
                <a href="{{ route('dashboard') }}" 
                   class="{{ $baseClass }} {{ request()->routeIs('dashboard') ? $activeClass : $inactiveClass }}"
                   :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'">
                    <i class="fas fa-home w-6 text-center text-lg"></i>
                    
                    <span x-show="!sidebarCollapsed" class="ml-3 truncate">Dashboard</span>
                    
                    <div x-show="sidebarCollapsed" class="absolute left-14 bg-[#14213D] text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 whitespace-nowrap pointer-events-none border border-[#FCA311]">
                        Dashboard
                    </div>
                </a>

                {{-- 2. SCAN QR (ADMIN & PETUGAS) --}}
                @if(Auth::user()->role == 'admin' || Auth::user()->role == 'petugas')
                    <a href="{{ route('scan.index') }}" 
                       class="{{ $baseClass }} {{ request()->routeIs('scan.*') ? $activeClass : $inactiveClass }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'">
                        <i class="fas fa-qrcode w-6 text-center text-lg"></i>
                        
                        <span x-show="!sidebarCollapsed" class="ml-3 truncate">Scan Absen</span>

                        <div x-show="sidebarCollapsed" class="absolute left-14 bg-[#14213D] text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 whitespace-nowrap pointer-events-none border border-[#FCA311]">
                            Scan Absen
                        </div>
                    </a>
                @endif

                {{-- 3. MENU ADMIN (KHUSUS ADMIN) --}}
                @if(Auth::user()->role == 'admin')
                    <div x-show="!sidebarCollapsed" class="mt-6 mb-2 px-4 text-[10px] font-bold text-gray-500 uppercase tracking-wider">
                        Administrator
                    </div>

                    <a href="{{ route('students.index') }}" 
                       class="{{ $baseClass }} {{ request()->routeIs('students.*') ? $activeClass : $inactiveClass }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'">
                        <i class="fas fa-users w-6 text-center text-lg"></i>
                        
                        <span x-show="!sidebarCollapsed" class="ml-3 truncate">Data Siswa</span>

                        <div x-show="sidebarCollapsed" class="absolute left-14 bg-[#14213D] text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 whitespace-nowrap pointer-events-none border border-[#FCA311]">
                            Data Siswa
                        </div>
                    </a>
                    {{-- MENU BARU (MANAJEMEN USER) --}}
                    <a href="{{ route('users.index') }}" 
                    class="{{ $baseClass }} {{ request()->routeIs('users.*') ? $activeClass : $inactiveClass }}"
                    :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'">
                        <i class="fas fa-users-cog w-6 text-center text-lg"></i>
                        
                        <span x-show="!sidebarCollapsed" class="ml-3 truncate">Manajemen User</span>

                        <div x-show="sidebarCollapsed" class="absolute left-14 bg-[#14213D] text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 whitespace-nowrap pointer-events-none border border-[#FCA311]">
                            Manajemen User
                        </div>
                    </a>

                    {{-- MENU LOG AKTIVITAS (AUDIT TRAIL) --}}
                    <a href="{{ route('activity-logs.index') }}" 
                    class="{{ $baseClass }} {{ request()->routeIs('activity-logs.*') ? $activeClass : $inactiveClass }}"
                    :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'">
                        <i class="fas fa-history w-6 text-center text-lg"></i>
                        
                        <span x-show="!sidebarCollapsed" class="ml-3 truncate">Log Aktivitas</span>

                        <div x-show="sidebarCollapsed" class="absolute left-14 bg-[#14213D] text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 whitespace-nowrap pointer-events-none border border-[#FCA311]">
                            Log Aktivitas
                        </div>
                    </a>

                    {{-- MENU SISWA TERHAPUS (TRASH) --}}
                    <a href="{{ route('students.trashed') }}" 
                    class="{{ $baseClass }} {{ request()->routeIs('students.trashed') ? $activeClass : $inactiveClass }}"
                    :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'">
                        <i class="fas fa-trash-restore w-6 text-center text-lg"></i>
                        
                        <span x-show="!sidebarCollapsed" class="ml-3 truncate">Siswa Terhapus</span>

                        <div x-show="sidebarCollapsed" class="absolute left-14 bg-[#14213D] text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 whitespace-nowrap pointer-events-none border border-[#FCA311]">
                            Siswa Terhapus
                        </div>
                    </a>
                @endif

                {{-- 4. MENU LAPORAN (ADMIN & WALI KELAS) --}}
                @if(Auth::user()->role == 'admin' || Auth::user()->role == 'wali_kelas')
                    <div x-show="!sidebarCollapsed" class="mt-6 mb-2 px-4 text-[10px] font-bold text-gray-500 uppercase tracking-wider">
                        Laporan & Input
                    </div>

                    {{-- Input Manual --}}
                    <a href="{{ route('manual.create') }}" 
                        class="{{ $baseClass }} {{ request()->routeIs('manual.*') ? $activeClass : $inactiveClass }}"
                        :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'">
                         <i class="fas fa-pen-to-square w-6 text-center text-lg"></i>
                         
                         <span x-show="!sidebarCollapsed" class="ml-3 truncate">Input Manual</span>
 
                         <div x-show="sidebarCollapsed" class="absolute left-14 bg-[#14213D] text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 whitespace-nowrap pointer-events-none border border-[#FCA311]">
                             Input Manual
                         </div>
                     </a>

                    {{-- Laporan Harian --}}
                    <a href="{{ route('report.daily') }}" 
                       class="{{ $baseClass }} {{ request()->routeIs('report.daily') ? $activeClass : $inactiveClass }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'">
                        <i class="fas fa-calendar-day w-6 text-center text-lg"></i>
                        
                        <span x-show="!sidebarCollapsed" class="ml-3 truncate">Laporan Harian</span>

                        <div x-show="sidebarCollapsed" class="absolute left-14 bg-[#14213D] text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 whitespace-nowrap pointer-events-none border border-[#FCA311]">
                            Laporan Harian
                        </div>
                    </a>

                    {{-- Laporan Bulanan --}}
                    <a href="{{ route('report.index') }}" 
                       class="{{ $baseClass }} {{ request()->routeIs('report.index') ? $activeClass : $inactiveClass }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'">
                        <i class="fas fa-file-invoice w-6 text-center text-lg"></i>
                        
                        <span x-show="!sidebarCollapsed" class="ml-3 truncate">Rekap Bulanan</span>

                        <div x-show="sidebarCollapsed" class="absolute left-14 bg-[#14213D] text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 whitespace-nowrap pointer-events-none border border-[#FCA311]">
                            Rekap Bulanan
                        </div>
                    </a>
                @endif
            {{-- 5. MENU PROFIL (SEMUA USER) --}}
                {{-- Letakkan ini di bagian paling bawah nav, sebelum </nav> --}}
                
                <div x-show="!sidebarCollapsed" class="mt-6 mb-2 px-4 text-[10px] font-bold text-gray-500 uppercase tracking-wider">
                    Pengaturan
                </div>

                <a href="{{ route('profile.edit') }}" 
                   class="{{ $baseClass }} {{ request()->routeIs('profile.*') ? $activeClass : $inactiveClass }}"
                   :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'">
                    <i class="fas fa-user-gear w-6 text-center text-lg"></i>
                    
                    <span x-show="!sidebarCollapsed" class="ml-3 truncate">Profil Saya</span>

                    <div x-show="sidebarCollapsed" class="absolute left-14 bg-[#14213D] text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 whitespace-nowrap pointer-events-none border border-[#FCA311]">
                        Profil Saya
                    </div>
                </a>
                {{-- [BARU] MENU BACKUP DATABASE (HANYA ADMIN) --}}
                @if(Auth::user()->role == 'admin')
                    <a href="{{ route('backup.download') }}" 
                       class="{{ $baseClass }} text-gray-300 hover:bg-red-500 hover:text-white"
                       :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'">
                        <i class="fas fa-database w-6 text-center text-lg"></i>
                        
                        <span x-show="!sidebarCollapsed" class="ml-3 truncate">Backup Database</span>

                        <div x-show="sidebarCollapsed" class="absolute left-14 bg-red-600 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 whitespace-nowrap pointer-events-none">
                            Backup SQL
                        </div>
                    </a>
                @endif
            </nav>

            <div class="absolute bottom-0 w-full p-4 border-t border-white/10 bg-[#0f1a30]">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center w-full text-gray-300 hover:text-red-400 transition group"
                        :class="sidebarCollapsed ? 'justify-center' : ''">
                        <i class="fas fa-sign-out-alt w-6 text-lg"></i>
                        <span x-show="!sidebarCollapsed" class="ml-3 font-medium">Keluar</span>
                        
                        <div x-show="sidebarCollapsed" class="absolute left-14 bg-red-600 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 whitespace-nowrap pointer-events-none">
                            Keluar
                        </div>
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden relative transition-all duration-300">
            
            <header class="flex items-center justify-between px-6 py-4 bg-white border-b lg:hidden">
                <button @click="sidebarOpen = true" class="text-[#14213D] focus:outline-none">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
                <div class="flex items-center gap-2">
                    <img src="{{ asset('logo/sekolah.png') }}" class="w-8 h-8" alt="Logo">
                    <span class="font-bold text-[#14213D]">SMKN 5 Samarinda</span>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#E5E5E5] p-4 md:p-6">
                <div x-show="sidebarOpen" @click="sidebarOpen = false" 
                     x-transition:enter="transition-opacity ease-linear duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-50"
                     x-transition:leave="transition-opacity ease-linear duration-300"
                     x-transition:leave-start="opacity-50"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 z-40 bg-black lg:hidden"></div>
                
                {{ $slot }}

                <div class="mt-10 mb-6 text-center">
                    <p class="text-[10px] text-gray-500">
                        Built by <span class="font-bold text-[#14213D] hover:text-[#FCA311] transition cursor-pointer">Fikri Haikal</span>
                    </p>
                </div>
            </main>
        </div>
    </div>

    {{-- SCRIPT FLASH MESSAGE & KONFIRMASI DELETE --}}
    <script>
        // ===== FLASH MESSAGE OTOMATIS =====
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#10B981',
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ session('error') }}',
                confirmButtonColor: '#EF4444'
            });
        @endif

        @if(session('status') === 'profile-updated')
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Profil Anda berhasil diperbarui.',
                confirmButtonColor: '#10B981',
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        @if(session('status') === 'password-updated')
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Password Anda berhasil diubah.',
                confirmButtonColor: '#10B981',
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        // ===== KONFIRMASI HAPUS =====
        document.addEventListener('DOMContentLoaded', function () {
            // Tangkap semua form dengan class 'delete-form'
            const deleteForms = document.querySelectorAll('.delete-form');

            deleteForms.forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault(); // Hentikan pengiriman form

                    Swal.fire({
                        title: 'Apakah Anda Yakin?',
                        text: 'Data yang dihapus tidak bisa dikembalikan (kecuali User)!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#EF4444',
                        cancelButtonColor: '#6B7280',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit(); // Kirim form jika user konfirmasi
                        }
                    });
                });
            });

            // ===== KONFIRMASI RESTORE =====
            const restoreForms = document.querySelectorAll('.restore-form');

            restoreForms.forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault(); // Hentikan pengiriman form

                    Swal.fire({
                        title: 'Pulihkan Data Siswa?',
                        text: 'Data siswa yang dipilih akan dikembalikan ke daftar siswa aktif.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#10B981',
                        cancelButtonColor: '#6B7280',
                        confirmButtonText: 'Ya, Pulihkan!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit(); // Kirim form jika user konfirmasi
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>