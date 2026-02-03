<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-history mr-2"></i>
            {{ __('Log Aktivitas Sistem') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Card Header dengan Filter --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-4">
                <div class="p-6 border-b border-gray-200">
                    <form method="GET" action="{{ route('activity-logs.index') }}" class="space-y-4">
                        
                        {{-- Baris 1: Pencarian & Tombol --}}
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="flex-1">
                                <input 
                                    type="text" 
                                    name="search" 
                                    value="{{ request('search') }}"
                                    placeholder="Cari berdasarkan deskripsi..." 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
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
                                    href="{{ route('activity-logs.index') }}" 
                                    class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition flex items-center gap-2"
                                >
                                    <i class="fas fa-redo"></i>
                                    <span>Reset</span>
                                </a>
                            </div>
                        </div>

                        {{-- Baris 2: Filter Dropdown --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Filter Jenis Aktivitas</label>
                                <select 
                                    name="action" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="">-- Semua Aktivitas --</option>
                                    @foreach($actions as $action)
                                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                            {{ $action }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tabel Log Aktivitas --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    
                    {{-- Info Jumlah Data --}}
                    <div class="mb-4 flex justify-between items-center">
                        <p class="text-gray-600">
                            Total <strong class="text-blue-600">{{ $logs->total() }}</strong> log aktivitas
                        </p>
                        <div class="text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Data diurutkan dari yang terbaru
                        </div>
                    </div>

                    {{-- Responsive Table --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Waktu
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Pengguna
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aktivitas
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Deskripsi
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        IP Address
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($logs as $log)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{ $log->created_at->format('d/m/Y') }}</span>
                                                <span class="text-gray-500 text-xs">{{ $log->created_at->format('H:i:s') }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                                            @if($log->user)
                                                <div class="flex flex-col">
                                                    <span class="font-medium text-gray-900">{{ $log->user->name }}</span>
                                                    <span class="text-gray-500 text-xs">{{ $log->user->email }}</span>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium mt-1
                                                        @if($log->user->role == 'admin') bg-red-100 text-red-800
                                                        @elseif($log->user->role == 'petugas') bg-blue-100 text-blue-800
                                                        @else bg-green-100 text-green-800
                                                        @endif">
                                                        {{ ucfirst($log->user->role) }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-gray-400 italic">User dihapus</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                                @if(str_contains($log->action, 'TAMBAH')) bg-green-100 text-green-800
                                                @elseif(str_contains($log->action, 'UPDATE') || str_contains($log->action, 'GANTI')) bg-blue-100 text-blue-800
                                                @elseif(str_contains($log->action, 'HAPUS')) bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                <i class="fas 
                                                    @if(str_contains($log->action, 'TAMBAH')) fa-plus-circle
                                                    @elseif(str_contains($log->action, 'UPDATE') || str_contains($log->action, 'GANTI')) fa-edit
                                                    @elseif(str_contains($log->action, 'HAPUS')) fa-trash
                                                    @else fa-info-circle
                                                    @endif
                                                    mr-1"></i>
                                                {{ $log->action }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-700">
                                            <div class="max-w-md">
                                                {{ $log->description }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <i class="fas fa-network-wired mr-1"></i>
                                            {{ $log->ip_address }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                            <div class="flex flex-col items-center gap-2">
                                                <i class="fas fa-inbox text-4xl text-gray-300"></i>
                                                <p class="font-medium">Tidak ada log aktivitas</p>
                                                <p class="text-sm">Belum ada aktivitas yang tercatat dalam sistem</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($logs->hasPages())
                        <div class="mt-6">
                            {{ $logs->links() }}
                        </div>
                    @endif

                </div>
            </div>

            {{-- Info Box --}}
            <div class="mt-4 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Informasi:</strong> Log aktivitas mencatat semua perubahan penting dalam sistem termasuk 
                            manajemen user, data siswa, dan perubahan profil. Data ini digunakan untuk audit dan keamanan sistem.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
