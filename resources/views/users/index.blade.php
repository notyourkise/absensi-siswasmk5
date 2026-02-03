<x-app-layout>
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h2 class="font-extrabold text-2xl text-[#14213D]">
            <i class="fas fa-users-cog text-[#FCA311] mr-2"></i> Manajemen User
        </h2>
        <a href="{{ route('users.create') }}" class="bg-[#14213D] text-white px-4 py-2 rounded-lg hover:bg-[#FCA311] hover:text-[#14213D] transition font-bold text-sm">
            <i class="fas fa-plus mr-1"></i> Tambah User
        </a>
    </div>

    {{-- Alert Sukses/Error --}}
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">{{ session('error') }}</div>
    @endif

    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-[#14213D] text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap font-bold text-[#14213D]">{{ $user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($user->role == 'admin')
                                <span class="px-2 py-1 text-xs font-bold bg-purple-100 text-purple-700 rounded-full border border-purple-200">ADMIN</span>
                            @elseif($user->role == 'petugas')
                                <span class="px-2 py-1 text-xs font-bold bg-blue-100 text-blue-700 rounded-full border border-blue-200">PETUGAS</span>
                            @else
                                <span class="px-2 py-1 text-xs font-bold bg-green-100 text-green-700 rounded-full border border-green-200">WALI KELAS</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <div class="flex justify-center space-x-2">
                                <a href="{{ route('users.edit', $user->id) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 p-2 rounded-md"><i class="fas fa-edit"></i></a>
                                
                                {{-- Tombol Hapus (Kecuali Hapus Diri Sendiri) --}}
                                @if($user->id != auth()->user()->id)
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="delete-form">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 p-2 rounded-md"><i class="fas fa-trash"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>