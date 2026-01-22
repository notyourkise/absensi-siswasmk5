<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Export Absensi: {{ $student->nama }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <h3 class="text-lg font-bold mb-4 text-center">Pilih Periode Laporan</h3>

                <form action="{{ route('students.report.download', $student->id) }}" method="POST" target="_blank">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Bulan</label>
                        <select name="bulan" class="w-full border rounded px-3 py-2">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 font-bold mb-2">Tahun</label>
                        <select name="tahun" class="w-full border rounded px-3 py-2">
                            @for($y = date('Y'); $y >= date('Y')-2; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="flex justify-between">
                        <a href="{{ route('students.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">Kembali</a>
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold px-4 py-2 rounded flex items-center">
                            <i class="fas fa-file-pdf mr-2"></i> Download PDF
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>