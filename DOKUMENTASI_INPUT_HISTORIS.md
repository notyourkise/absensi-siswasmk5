# 📅 DOKUMENTASI INPUT ABSENSI HISTORIS

> **Tanggal Update**: 21 Januari 2026  
> **Fitur**: Input Absensi Manual untuk Tanggal Masa Lalu  
> **Tujuan**: Sinkronisasi data absensi Januari sebelum sistem diluncurkan di Februari

---

## 🎯 LATAR BELAKANG

Sebelum sistem absensi QR Code diluncurkan di Februari 2026, sekolah masih menggunakan **absensi manual fisik (kertas)** di bulan Januari. Untuk menjaga kontinuitas data, **Wali Kelas** perlu memasukkan data absensi Januari ke sistem secara retroaktif.

### Masalah Sebelumnya:
❌ Sistem hanya bisa input absensi untuk hari ini (menggunakan `Carbon::now()`)  
❌ Tidak ada cara input tanggal masa lalu  
❌ Jam masuk/keluar otomatis menggunakan waktu sekarang  

### Solusi:
✅ Input tanggal bebas (masa lalu hingga hari ini)  
✅ Input jam masuk & jam keluar manual (opsional)  
✅ Validasi: tidak boleh tanggal masa depan  
✅ Tetap menjaga **Data Isolation** (wali kelas hanya input kelasnya sendiri)

---

## 🚀 FITUR BARU

### 1. **Input Tanggal Fleksibel**
- Wali kelas bisa memilih **tanggal masa lalu** (contoh: 8 Januari 2026)
- Validasi: `before_or_equal:today` → mencegah input tanggal masa depan
- Default: Tanggal hari ini

### 2. **Input Jam Manual (Opsional)**
- **Jam Masuk**: Input manual atau default jam sekarang
- **Jam Keluar**: Input manual atau default jam sekarang
- Format: 24 jam (HH:MM) - Contoh: `07:00`, `14:30`
- Validasi: `date_format:H:i`

### 3. **Toggle Dinamis Jam Section**
- Section jam **hanya muncul untuk status "Hadir"**
- Untuk status Sakit/Izin/Alpha → section jam disembunyikan otomatis
- Menggunakan jQuery `.slideDown()` dan `.slideUp()`

---

## 📂 FILE YANG DIMODIFIKASI

### 1. **app/Http/Controllers/ManualAbsenController.php**

#### Validasi Baru:
```php
$request->validate([
    'student_id' => 'required|exists:students,id',
    'tanggal' => 'required|date|before_or_equal:today', // ✅ Tidak boleh masa depan
    'status' => 'required|in:Hadir,Sakit,Izin,Alpha',
    'jam_masuk' => 'nullable|date_format:H:i',          // ✅ Format 24 jam
    'jam_keluar' => 'nullable|date_format:H:i',
], [
    'tanggal.before_or_equal' => 'Tanggal tidak boleh di masa depan!',
    'jam_masuk.date_format' => 'Format jam masuk harus HH:MM (contoh: 07:00)',
    'jam_keluar.date_format' => 'Format jam keluar harus HH:MM (contoh: 15:30)',
]);
```

#### Logika Jam Masuk/Keluar Fleksibel:
```php
// Jika ada input manual jam, pakai itu. Kalau tidak, pakai jam sekarang.
$jamMasuk = $request->filled('jam_masuk') 
    ? Carbon::parse($request->tanggal . ' ' . $request->jam_masuk)
    : Carbon::now();
    
$jamKeluar = $request->filled('jam_keluar') 
    ? Carbon::parse($request->tanggal . ' ' . $request->jam_keluar)
    : Carbon::now();
```

#### Skenario Input:
| Status | Jam Masuk | Jam Keluar | Keterangan |
|--------|-----------|------------|------------|
| **Hadir (Historis)** | Input manual (07:00) | Input manual (14:30) | Data backfill Januari |
| **Hadir (Real-time)** | Kosongkan (pakai jam sekarang) | Kosongkan (pakai jam sekarang) | Input hari ini |
| **Sakit/Izin/Alpha** | `null` | `null` | Tidak perlu jam |

---

### 2. **resources/views/manual/create.blade.php**

#### Tambahan Input Tanggal:
```html
<input type="date" name="tanggal" 
       value="{{ date('Y-m-d') }}" 
       max="{{ date('Y-m-d') }}" 
       required>
<p class="text-xs text-blue-600 mt-1">
    💡 Anda bisa memilih tanggal masa lalu untuk sinkronisasi data historis
</p>
```

#### Section Jam Masuk/Keluar (Conditional):
```html
<div id="jam-section" class="mb-6 space-y-4 border border-gray-300 rounded-lg p-4 bg-gray-50">
    <p class="text-sm font-medium text-gray-700 mb-3">
        ⏰ Input Jam (Opsional - Kosongkan jika menggunakan jam sekarang)
    </p>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label>Jam Masuk</label>
            <input type="time" name="jam_masuk" id="jam_masuk">
            <p class="text-xs text-gray-500 mt-1">Default: Jam sekarang</p>
        </div>
        
        <div>
            <label>Jam Keluar</label>
            <input type="time" name="jam_keluar" id="jam_keluar">
            <p class="text-xs text-gray-500 mt-1">Default: Jam sekarang</p>
        </div>
    </div>
</div>
```

#### JavaScript Toggle:
```javascript
function toggleJamSection() {
    const status = $('input[name="status"]:checked').val();
    if (status === 'Hadir') {
        $('#jam-section').slideDown();
    } else {
        $('#jam-section').slideUp();
        // Clear values jika tidak Hadir
        $('#jam_masuk').val('');
        $('#jam_keluar').val('');
    }
}

// Jalankan saat pertama kali load
toggleJamSection();

// Jalankan saat radio button berubah
$('.status-radio').change(function() {
    toggleJamSection();
});
```

---

## 🧪 TESTING SKENARIO

### **Skenario 1: Input Data Historis Januari**
1. Login sebagai wali kelas: `walix1tjkt1@sekolah.com` / `password123`
2. Menu: **Input Absensi Manual**
3. Pilih siswa: **SISWA TJKT 1 Nomor 1** (Kelas X TJKT 1)
4. Tanggal: **2026-01-08**
5. Status: **Hadir**
6. Jam Masuk: **07:00**
7. Jam Keluar: **14:30**
8. Submit ✅

**Expected Result:**
```sql
INSERT INTO attendances (student_id, tanggal, status_masuk, jam_masuk, jam_keluar)
VALUES (1, '2026-01-08', 'Hadir', '2026-01-08 07:00:00', '2026-01-08 14:30:00');
```

---

### **Skenario 2: Input Sakit (Tanggal Masa Lalu)**
1. Login sebagai wali kelas: `walix1tjkt1@sekolah.com`
2. Menu: **Input Absensi Manual**
3. Pilih siswa: **SISWA TJKT 1 Nomor 2**
4. Tanggal: **2026-01-10**
5. Status: **Sakit** ✅ (Section jam otomatis hilang)
6. Submit ✅

**Expected Result:**
```sql
INSERT INTO attendances (student_id, tanggal, status_masuk, jam_masuk, jam_keluar)
VALUES (2, '2026-01-10', 'Sakit', NULL, NULL);
```

---

### **Skenario 3: Validasi Tanggal Masa Depan (Error)**
1. Login sebagai wali kelas
2. Menu: **Input Absensi Manual**
3. Pilih siswa: **SISWA TJKT 1 Nomor 3**
4. Tanggal: **2026-01-30** (Masa depan dari 21 Januari)
5. Status: **Hadir**
6. Submit ❌

**Expected Result:**
```
❌ Error: "Tanggal tidak boleh di masa depan!"
```

---

### **Skenario 4: Input Real-Time (Hari Ini)**
1. Login sebagai wali kelas: `walix1tjkt1@sekolah.com`
2. Menu: **Input Absensi Manual**
3. Pilih siswa: **SISWA TJKT 1 Nomor 4**
4. Tanggal: **2026-01-21** (Hari ini - default)
5. Status: **Hadir**
6. Jam Masuk: **Kosongkan** (pakai jam sekarang)
7. Jam Keluar: **Kosongkan**
8. Submit ✅

**Expected Result:**
```sql
INSERT INTO attendances (student_id, tanggal, status_masuk, jam_masuk, jam_keluar)
VALUES (4, '2026-01-21', 'Hadir', '2026-01-21 10:35:42', NULL);
-- Jam masuk otomatis pakai waktu sekarang (Carbon::now())
```

---

## 📊 DATA ISOLATION

Fitur ini **tetap menjaga** data isolation yang sudah diimplementasikan sebelumnya:

### ✅ Proteksi di Controller:
```php
// Wali Kelas hanya bisa input absen siswa di kelasnya
$student = Student::findOrFail($request->student_id);

if (auth()->user()->role === 'wali_kelas') {
    if ($student->kelas !== auth()->user()->kelas) {
        return back()->with('error', 'Anda hanya bisa menginput absensi siswa di kelas Anda sendiri!');
    }
}
```

### ✅ Filtering di View:
```php
// Dropdown siswa sudah difilter berdasarkan kelas wali
$students = Student::when(auth()->user()->role === 'wali_kelas', function ($q) {
    $q->where('kelas', auth()->user()->kelas);
})->orderBy('nama')->get();
```

**Contoh:**
- Wali kelas `walix1tjkt1@sekolah.com` (Kelas: **X TJKT 1**)
- ✅ Bisa input siswa: **SISWA TJKT 1 Nomor 1-30**
- ❌ Tidak bisa input siswa dari kelas **X TJKT 2** atau **XI TJKT 1**

---

## 🎨 UI/UX IMPROVEMENTS

### 1. **Visual Indicator Tanggal**
- Hint text: *"💡 Anda bisa memilih tanggal masa lalu untuk sinkronisasi data historis"*
- Max date attribute: `max="{{ date('Y-m-d') }}"` → mencegah pilih tanggal masa depan di UI

### 2. **Panduan Input Jam**
Box informasi dengan bullet points:
```
📝 Panduan Input Jam:
• Untuk data historis Januari, isi Jam Masuk (07:00) dan Jam Keluar (14:30)
• Untuk input real-time hari ini, kosongkan kedua field (sistem pakai jam sekarang)
• Jam menggunakan format 24 jam (00:00 - 23:59)
```

### 3. **Dynamic Section Toggle**
- Status **Hadir** → Section jam muncul dengan animasi `.slideDown()`
- Status **Sakit/Izin/Alpha** → Section jam hilang dengan `.slideUp()` dan clear values

---

## 🔄 WORKFLOW SINKRONISASI DATA JANUARI

### **Tahap 1: Persiapan**
1. Wali kelas mengumpulkan **absensi fisik (kertas)** Januari
2. Data yang dibutuhkan per siswa:
   - Tanggal (1-31 Januari 2026)
   - Status (Hadir/Sakit/Izin/Alpha)
   - Jam Masuk & Jam Keluar (jika Hadir)

### **Tahap 2: Input Data**
1. Login sebagai Wali Kelas
2. Buka menu **Input Absensi Manual**
3. Untuk setiap siswa di bulan Januari:
   - Pilih siswa
   - Pilih tanggal (contoh: 8 Januari 2026)
   - Pilih status:
     - **Hadir**: Isi jam masuk (07:00) dan jam keluar (14:30)
     - **Sakit/Izin/Alpha**: Langsung submit (tidak perlu jam)
   - Submit

### **Tahap 3: Verifikasi**
1. Buka menu **Laporan Absensi**
2. Filter:
   - **Tanggal Awal**: 1 Januari 2026
   - **Tanggal Akhir**: 31 Januari 2026
   - **Kelas**: Kelas binaan (contoh: X TJKT 1)
3. Validasi:
   - Jumlah record per siswa: **20-23 hari** (sesuai hari efektif sekolah)
   - Status masuk sesuai data fisik
   - Jam masuk/keluar untuk status Hadir

---

## 📝 CUSTOM ERROR MESSAGES

```php
[
    'tanggal.before_or_equal' => 'Tanggal tidak boleh di masa depan!',
    'jam_masuk.date_format' => 'Format jam masuk harus HH:MM (contoh: 07:00)',
    'jam_keluar.date_format' => 'Format jam keluar harus HH:MM (contoh: 15:30)',
]
```

**Contoh Error Display:**
```
❌ Tanggal tidak boleh di masa depan!
❌ Format jam masuk harus HH:MM (contoh: 07:00)
```

---

## ⚠️ CATATAN PENTING

### 1. **Duplikasi Data**
- Sistem tetap **mencegah duplikasi** absensi untuk tanggal yang sama
- Jika sudah ada data tanggal 8 Jan → sistem update, bukan insert baru

### 2. **Role Access**
- **Admin & Petugas**: Bisa input absensi untuk semua kelas
- **Wali Kelas**: Hanya bisa input absensi untuk kelas binaannya

### 3. **Timezone**
- Sistem menggunakan timezone dari `config/app.php`
- Default: `'timezone' => 'Asia/Jakarta'`
- Jam input akan disimpan sesuai timezone server

### 4. **Validasi Jam**
- Format: 24 jam (00:00 - 23:59)
- Contoh valid: `07:00`, `14:30`, `23:59`
- Contoh invalid: `7:00`, `14:30:00`, `25:00`

---

## 🎯 BENEFIT FITUR INI

✅ **Kontinuitas Data**: Absensi Januari tercatat lengkap di sistem  
✅ **Laporan Akurat**: Report semester 2 include data Januari  
✅ **Fleksibilitas**: Bisa input real-time atau historis  
✅ **User-Friendly**: Toggle otomatis + panduan jelas  
✅ **Data Integrity**: Validasi ketat + data isolation terjaga  

---

## 📌 SUMMARY

| Aspek | Sebelumnya | Sekarang |
|-------|------------|----------|
| **Tanggal** | Hanya hari ini | Masa lalu hingga hari ini ✅ |
| **Jam Masuk/Keluar** | Otomatis `Carbon::now()` | Manual input atau default ✅ |
| **Validasi Tanggal** | Tidak ada | `before_or_equal:today` ✅ |
| **UI Jam Section** | Tidak ada | Conditional + toggle dinamis ✅ |
| **Use Case** | Real-time only | Real-time + Historical ✅ |

---

**Dokumentasi dibuat oleh**: GitHub Copilot  
**Tanggal**: 21 Januari 2026  
**Versi**: 1.0  
