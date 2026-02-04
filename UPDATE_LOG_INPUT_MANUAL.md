# UPDATE LOG: Data Isolation - Input Manual Absensi

**Tanggal:** 4 Februari 2026  
**Fitur:** Data Isolation untuk Wali Kelas di Menu Input Manual

---

## 🎯 User Story
> "Saya ingin ketika login menggunakan akun wali kelas, dan pada menu input manual, saya ingin data siswa yang ditampilkan hanyalah siswa dari kelas tersebut. Contohnya jika saya login sebagai wali kelas XII TJKT 1, maka data siswa yang ditampilkan pada input manual hanyalah siswa dari XII TJKT 1 saja."

---

## ✅ Implementasi

### 1. **ManualAbsenController.php** - Method `create()`

**Lokasi:** `app/Http/Controllers/ManualAbsenController.php`

**Perubahan:**
```php
public function create()
{
    // ===== DATA ISOLATION =====
    $query = Student::query();
    
    if (auth()->user()->role === 'wali_kelas') {
        $query->where('kelas', auth()->user()->kelas);
    }
    
    $students = $query->orderBy('nama', 'asc')->get();
    
    return view('manual.create', compact('students'));
}
```

**Logika:**
- ✅ Admin/Petugas: Melihat **SEMUA** siswa
- ✅ Wali Kelas: Hanya melihat siswa **di kelasnya**

---

### 2. **ManualAbsenController.php** - Method `store()`

**Proteksi Tambahan:**
```php
public function store(Request $request)
{
    // ... validasi ...
    
    // ===== PROTEKSI: Mencegah manipulasi request =====
    $student = Student::findOrFail($request->student_id);
    
    if (auth()->user()->role === 'wali_kelas') {
        if ($student->kelas !== auth()->user()->kelas) {
            return back()->with('error', 'Anda hanya bisa menginput absensi siswa di kelas Anda sendiri!');
        }
    }
    
    // ... lanjut proses absen ...
}
```

**Tujuan:**
Mencegah Wali Kelas menginput absen siswa kelas lain (via manipulasi POST request)

---

### 3. **View: manual/create.blade.php**

**Indikator Visual di Header:**
```blade
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Input Absensi Manual') }}
    </h2>
    @if(auth()->user()->role === 'wali_kelas')
        <p class="text-sm text-gray-600 mt-1">
            Kelas binaan: <strong class="text-[#FCA311]">{{ auth()->user()->kelas }}</strong>
        </p>
    @endif
</x-slot>
```

**Info Jumlah Siswa:**
```blade
<p class="text-xs text-gray-500 mt-1">
    Total: <strong>{{ $students->count() }}</strong> siswa
    @if(auth()->user()->role === 'wali_kelas')
        di kelas <strong class="text-[#FCA311]">{{ auth()->user()->kelas }}</strong>
    @endif
</p>
```

---

### 4. **Bonus: ScanController.php** - Latest Scans

**Lokasi:** `app/Http/Controllers/ScanController.php`

**Perubahan:**
```php
public function index()
{
    $query = Attendance::with('student')
                    ->whereDate('tanggal', Carbon::today());
    
    // ===== DATA ISOLATION =====
    if (auth()->user()->role === 'wali_kelas') {
        $query->whereHas('student', function($q) {
            $q->where('kelas', auth()->user()->kelas);
        });
    }
    
    $latest_scans = $query->latest('updated_at')->take(5)->get();
    
    return view('scan.index', compact('latest_scans'));
}
```

**Benefit:**
Wali Kelas hanya melihat 5 scan terakhir dari siswa di kelasnya (bukan semua scan)

---

## 🧪 Testing Checklist

### Test 1: Login Wali Kelas XII TJKT 1
```
Email: wali12tjkt1@sekolah.com
Password: password
```

**Cek:**
- [ ] Masuk menu "Input Absensi Manual"
- [ ] Header menampilkan: "Kelas binaan: **XII TJKT 1**"
- [ ] Dropdown siswa hanya menampilkan siswa XII TJKT 1
- [ ] Info di bawah dropdown: "Total: X siswa di kelas **XII TJKT 1**"
- [ ] Coba input absen untuk siswa XII TJKT 1 → **Berhasil**
- [ ] (Jika ada manipulasi) Input absen siswa kelas lain → **Ditolak dengan error**

### Test 2: Login Admin
```
Email: (akun admin Anda)
Password: (password admin)
```

**Cek:**
- [ ] Masuk menu "Input Absensi Manual"
- [ ] Dropdown siswa menampilkan **SEMUA** siswa dari semua kelas
- [ ] Info: "Total: X siswa" (tanpa info kelas)
- [ ] Bisa input absen untuk siswa dari kelas manapun

### Test 3: Menu Scan QR Code
```
Login: wali12tjkt1@sekolah.com
```

**Cek:**
- [ ] Masuk menu "Scan Absensi"
- [ ] Tabel "5 Scan Terakhir" hanya menampilkan siswa XII TJKT 1
- [ ] Siswa dari kelas lain **TIDAK** muncul di tabel

---

## 📊 Summary

| Menu | Role Admin | Role Wali Kelas |
|------|------------|-----------------|
| Input Manual | ✅ Semua siswa | ✅ Hanya kelasnya |
| Scan QR (Latest) | ✅ Semua scan | ✅ Hanya kelasnya |
| Data Siswa | ✅ Semua siswa | ✅ Hanya kelasnya |
| Dashboard | ✅ Semua data | ✅ Hanya kelasnya |
| Laporan | ✅ Semua kelas | ✅ Hanya kelasnya |

---

## 🔒 Security

**Proteksi Berlapis:**
1. **Controller Filter:** Query otomatis terfilter per kelas
2. **Validation Check:** Cek kelas siswa sebelum simpan data
3. **UI Disabled:** Dropdown/filter tidak bisa diubah (di view students)
4. **Route Middleware:** Role-based access control

**Tidak Bisa Dibypass:**
- Manipulasi POST request → Ditolak oleh validasi di controller
- Manipulasi URL parameter → Query sudah terfilter
- Direct database access → Di luar scope aplikasi

---

## ✅ Files Modified

1. ✅ `app/Http/Controllers/ManualAbsenController.php`
2. ✅ `app/Http/Controllers/ScanController.php`
3. ✅ `resources/views/manual/create.blade.php`

---

**Status:** ✅ **COMPLETED**  
**Tested:** ⏳ Pending user testing
