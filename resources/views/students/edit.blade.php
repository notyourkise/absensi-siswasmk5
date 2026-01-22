<x-app-layout>
    {{-- WRAPPER FIXED SCREEN --}}
    <div class="flex flex-col lg:flex-row h-[calc(100vh-65px)] overflow-hidden bg-white">

        {{-- PANEL KIRI (FOTO) - FIXED NAVY --}}
        <div class="w-full lg:w-5/12 bg-[#14213D] relative flex flex-col justify-center items-center p-8 text-center shadow-2xl z-10">
            
            {{-- Hiasan --}}
            <div class="absolute top-0 right-0 w-32 h-32 bg-[#FCA311] rounded-bl-full opacity-20"></div>

            <div class="relative z-10">
                <span class="bg-[#FCA311] text-[#14213D] text-[10px] font-black px-3 py-1 rounded mb-4 inline-block">MODE EDIT</span>
                <h2 class="text-3xl font-black text-white mb-1">{{ $student->nama }}</h2>
                <p class="text-indigo-200 text-sm font-mono mb-8">{{ $student->nisn }}</p>

                {{-- TRIGGER UPLOAD FOTO --}}
                <div class="relative group cursor-pointer w-64 h-64 mx-auto" onclick="document.getElementById('foto-input').click()">
                    
                    @php
                        $fotoUrl = $student->foto ? asset('storage/students/' . $student->foto) : 'https://ui-avatars.com/api/?name='.urlencode($student->nama).'&background=E5E5E5&color=14213D&size=256';
                    @endphp

                    <img id="preview-img" src="{{ $fotoUrl }}" 
                         class="w-full h-full object-cover rounded-full border-8 border-white/10 group-hover:border-[#FCA311] shadow-2xl transition-all duration-300">
                    
                    <div class="absolute bottom-4 right-4 bg-[#FCA311] text-[#14213D] w-12 h-12 rounded-full flex items-center justify-center shadow-lg transform group-hover:scale-110 transition">
                        <i class="fas fa-pen text-xl"></i>
                    </div>
                </div>
                
                <p class="text-xs text-indigo-300 mt-6">Ketuk foto untuk mengganti</p>
                <p class="text-[10px] text-indigo-200 mt-2 bg-white/10 rounded-lg px-3 py-2">
                    <i class="fas fa-info-circle mr-1"></i> Format: <strong>WebP</strong> • Maks: <strong>1MB</strong>
                </p>
            </div>
        </div>

        {{-- PANEL KANAN (FORM) - SCROLLABLE WHITE --}}
        <div class="w-full lg:w-7/12 h-full overflow-y-auto bg-white p-8 lg:p-12 relative">
            
            <div class="max-w-xl mx-auto">
                <div class="flex justify-between items-center mb-8 border-b border-gray-100 pb-4">
                    <h3 class="text-2xl font-bold text-[#14213D]">Update Informasi</h3>
                    <a href="{{ route('students.index') }}" class="text-sm font-bold text-gray-400 hover:text-[#14213D] transition">
                        <i class="fas fa-times text-lg"></i>
                    </a>
                </div>

                @if($errors->any())
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-circle text-red-500 mr-3 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-bold text-red-800 mb-2">Terdapat kesalahan:</p>
                                <ul class="text-xs text-red-700 list-disc list-inside space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('students.update', $student->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <input type="file" name="foto" id="foto-input" class="hidden" accept="image/webp" onchange="previewImage(event)">

                    <div class="space-y-6">
                        
                        {{-- NISN --}}
                        <div class="group">
                            <label class="block text-xs font-bold text-[#14213D] uppercase mb-2 tracking-wider">NISN</label>
                            <input type="number" name="nisn" value="{{ old('nisn', $student->nisn) }}" required
                                class="w-full bg-[#E5E5E5] border-transparent rounded-xl px-4 py-3.5 text-sm font-bold text-[#14213D] focus:bg-white focus:ring-2 focus:ring-[#FCA311] transition-all">
                            @error('nisn')
                                <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- NAMA --}}
                        <div class="group">
                            <label class="block text-xs font-bold text-[#14213D] uppercase mb-2 tracking-wider">Nama Lengkap</label>
                            <input type="text" name="nama" value="{{ old('nama', $student->nama) }}" required
                                class="w-full bg-[#E5E5E5] border-transparent rounded-xl px-4 py-3.5 text-sm font-bold text-[#14213D] focus:bg-white focus:ring-2 focus:ring-[#FCA311] transition-all">
                            @error('nama')
                                <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- KELAS --}}
                        <div class="group">
                            <label class="block text-xs font-bold text-[#14213D] uppercase mb-2 tracking-wider">Kelas</label>
                            <input type="text" name="kelas" value="{{ old('kelas', $student->kelas) }}" required
                                class="w-full bg-[#E5E5E5] border-transparent rounded-xl px-4 py-3.5 text-sm font-bold text-[#14213D] focus:bg-white focus:ring-2 focus:ring-[#FCA311] transition-all">
                            @error('kelas')
                                <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- BUTTONS --}}
                        <div class="pt-8 flex items-center gap-4">
                            <button type="submit" class="flex-1 bg-[#FCA311] hover:bg-orange-400 text-[#14213D] font-bold py-4 rounded-xl shadow-lg transform hover:-translate-y-1 transition-all">
                                UPDATE PERUBAHAN
                            </button>
                            <a href="{{ route('students.index') }}" class="flex-none px-6 py-4 rounded-xl border border-gray-300 text-gray-500 font-bold hover:bg-gray-50 transition">
                                BATAL
                            </a>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function(){
                const output = document.getElementById('preview-img');
                output.src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</x-app-layout>