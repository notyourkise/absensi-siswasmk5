# DOKUMENTASI TEKNIS: NOTIFIKASI INTERAKTIF & KONFIRMASI MODAL (SWEETALERT2)
## Sistem Absensi Siswa SMKN 5 Samarinda

---

## 📋 DAFTAR ISI
1. [Tujuan & Fitur](#tujuan--fitur)
2. [Instalasi & Konfigurasi](#instalasi--konfigurasi)
3. [Logika Flash Message](#logika-flash-message)
4. [Logika Konfirmasi Delete](#logika-konfirmasi-delete)
5. [Logika Konfirmasi Restore](#logika-konfirmasi-restore)
6. [Implementasi di View](#implementasi-di-view)
7. [Testing & Validasi](#testing--validasi)

---

## 🎯 TUJUAN & FITUR

### Tujuan Implementasi
Mengganti notifikasi browser standar (`alert()`, `confirm()`) dan flash message Laravel yang kaku dengan **SweetAlert2** yang memiliki:
- ✅ Animasi smooth & modern
- ✅ Icon yang menarik (sukses, error, warning, info)
- ✅ Responsif & mobile-friendly
- ✅ Customizable (warna, timer, button text)

### Fitur Utama

#### A. Flash Message Otomatis
Menampilkan popup notification secara otomatis setelah user melakukan aksi:
- ✅ **Sukses** (Hijau) - Simpan/Update/Restore berhasil
- ✅ **Error** (Merah) - Validasi gagal/Error sistem
- ✅ **Profile Updated** - Khusus update profil
- ✅ **Password Updated** - Khusus ganti password

#### B. Konfirmasi Delete
Mencegah penghapusan data yang tidak disengaja dengan konfirmasi dialog:
- ⚠️ Warning icon (Kuning)
- ⚠️ Text warning: "Data yang dihapus tidak bisa dikembalikan (kecuali User)!"
- ⚠️ 2 tombol: "Ya, Hapus!" (Merah) & "Batal" (Abu-abu)

#### C. Konfirmasi Restore
Konfirmasi sebelum memulihkan data siswa dari trash:
- ℹ️ Question icon (Biru)
- ℹ️ Text: "Data siswa akan dikembalikan ke daftar aktif"
- ℹ️ 2 tombol: "Ya, Pulihkan!" (Hijau) & "Batal" (Abu-abu)

---

## 🔧 INSTALASI & KONFIGURASI

### File Target
**`resources/views/layouts/app.blade.php`**

### Step 1: Tambahkan CDN SweetAlert2

**Lokasi:** Di dalam tag `<head>`, setelah Font Awesome

```blade
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

{{-- SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@vite(['resources/css/app.css', 'resources/js/app.js'])
```

**Penjelasan:**
- CDN dari `jsdelivr.net` (versi latest @11)
- Tidak perlu instalasi via npm/composer
- Auto-update ke versi terbaru (patch & minor)

---

### Step 2: Tambahkan Script Handler

**Lokasi:** Sebelum penutup tag `</body>`

```blade
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
        const deleteForms = document.querySelectorAll('.delete-form');

        deleteForms.forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

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
                        form.submit();
                    }
                });
            });
        });

        // ===== KONFIRMASI RESTORE =====
        const restoreForms = document.querySelectorAll('.restore-form');

        restoreForms.forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

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
                        form.submit();
                    }
                });
            });
        });
    });
</script>
```

---

## 📨 LOGIKA FLASH MESSAGE

### Cara Kerja
1. **Controller** mengirim flash message via `session()`
2. **Blade Directive** `@if(session('key'))` mendeteksi pesan
3. **SweetAlert2** menampilkan popup dengan konfigurasi:
   - Icon (success/error)
   - Title
   - Text (isi pesan)
   - Timer auto-close (3 detik untuk success)

### Contoh di Controller

#### Success Message
```php
// UserController.php - Method store
return redirect()->route('users.index')
    ->with('success', 'User berhasil ditambahkan!');
```

#### Error Message
```php
// UserController.php - Method destroy
if ($user->id == auth()->user()->id) {
    return back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
}
```

#### Profile Status
```php
// ProfileController.php - Method update
return Redirect::route('profile.edit')
    ->with('status', 'profile-updated');
```

### Konfigurasi SweetAlert2

| Property | Nilai | Keterangan |
|----------|-------|------------|
| `icon` | `'success'` / `'error'` | Icon yang ditampilkan |
| `title` | `'Berhasil!'` / `'Gagal!'` | Judul popup |
| `text` | `{{ session('key') }}` | Pesan dari Controller |
| `confirmButtonColor` | `'#10B981'` / `'#EF4444'` | Warna tombol (Tailwind Green/Red) |
| `timer` | `3000` | Auto-close setelah 3 detik (ms) |
| `timerProgressBar` | `true` | Progress bar countdown |

---

## 🗑️ LOGIKA KONFIRMASI DELETE

### Cara Kerja
1. User klik tombol **Hapus**
2. JavaScript **intercept** form submission (`e.preventDefault()`)
3. SweetAlert2 tampilkan **warning modal**
4. Jika user klik **"Ya, Hapus!"** → `form.submit()`
5. Jika user klik **"Batal"** → Tidak terjadi apa-apa

### Implementasi di View

**File:** `resources/views/users/index.blade.php`

```blade
{{-- PENTING: Tambahkan class 'delete-form' --}}
<form action="{{ route('users.destroy', $user->id) }}" method="POST" class="delete-form">
    @csrf 
    @method('DELETE')
    <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 p-2 rounded-md">
        <i class="fas fa-trash"></i>
    </button>
</form>
```

**File:** `resources/views/students/index.blade.php`

```blade
<form action="{{ route('students.destroy', $student->id) }}" method="POST" 
      class="inline delete-form">
    @csrf
    @method('DELETE')
    <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-600">
        <i class="fas fa-trash"></i>
    </button>
</form>
```

### ⚠️ PENTING: Class Selector
- **WAJIB** tambahkan `class="delete-form"` ke setiap `<form>` delete
- **HAPUS** atribut `onsubmit="return confirm(...)"` (sudah diganti SweetAlert2)

---

## ♻️ LOGIKA KONFIRMASI RESTORE

### Cara Kerja
Sama seperti konfirmasi delete, namun dengan:
- ✅ Icon `question` (biru)
- ✅ Text yang lebih informatif
- ✅ Button hijau "Ya, Pulihkan!"

### Implementasi di View

**File:** `resources/views/students/trashed.blade.php`

```blade
{{-- PENTING: Tambahkan class 'restore-form' --}}
<form action="{{ route('students.restore', $student->id) }}" method="POST" class="inline restore-form">
    @csrf
    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg">
        <i class="fas fa-undo text-xs"></i>
        <span>Restore</span>
    </button>
</form>
```

### Force Delete (Hapus Permanen)

**File:** `resources/views/students/trashed.blade.php`

```blade
{{-- Gunakan class 'delete-form' untuk konfirmasi warning --}}
<form action="{{ route('students.forceDelete', $student->id) }}" method="POST" class="inline delete-form">
    @csrf
    @method('DELETE')
    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg">
        <i class="fas fa-trash text-xs"></i>
        <span>Hapus Permanen</span>
    </button>
</form>
```

---

## 📂 IMPLEMENTASI DI VIEW

### Daftar File yang Dimodifikasi

| File | Modifikasi | Class Selector |
|------|------------|----------------|
| `layouts/app.blade.php` | ✅ CDN SweetAlert2<br>✅ Script handler | - |
| `users/index.blade.php` | ✅ Hapus `onsubmit`<br>✅ Tambah `delete-form` | `.delete-form` |
| `students/index.blade.php` | ✅ Hapus `onsubmit`<br>✅ Tambah `delete-form` | `.delete-form` |
| `students/trashed.blade.php` | ✅ Hapus `onclick confirm`<br>✅ Tambah `restore-form`<br>✅ Tambah `delete-form` | `.restore-form`<br>`.delete-form` |

### Template Form Delete (Standard)

```blade
{{-- TEMPLATE DELETE FORM --}}
<form action="{{ route('nama.destroy', $item->id) }}" method="POST" class="delete-form">
    @csrf 
    @method('DELETE')
    <button type="submit" class="btn btn-danger">
        <i class="fas fa-trash"></i> Hapus
    </button>
</form>
```

### Template Form Restore

```blade
{{-- TEMPLATE RESTORE FORM --}}
<form action="{{ route('nama.restore', $item->id) }}" method="POST" class="restore-form">
    @csrf
    <button type="submit" class="btn btn-success">
        <i class="fas fa-undo"></i> Restore
    </button>
</form>
```

---

## 🧪 TESTING & VALIDASI

### Checklist Testing

#### 1. Flash Message - Success
- [ ] Tambah User → Muncul popup hijau "User berhasil ditambahkan!"
- [ ] Update Student → Muncul popup hijau "Data siswa berhasil diperbarui!"
- [ ] Update Profile → Muncul popup hijau "Profil Anda berhasil diperbarui."
- [ ] Change Password → Muncul popup hijau "Password Anda berhasil diubah."
- [ ] Restore Student → Muncul popup hijau "Data siswa berhasil dipulihkan!"

#### 2. Flash Message - Error
- [ ] Hapus akun sendiri (User) → Muncul popup merah "Anda tidak bisa menghapus akun sendiri!"
- [ ] Validasi error (email duplikat) → Muncul popup merah dengan pesan error

#### 3. Konfirmasi Delete
- [ ] Klik tombol Hapus User → Muncul modal warning
- [ ] Klik "Batal" → Modal tertutup, data TIDAK terhapus
- [ ] Klik "Ya, Hapus!" → Data terhapus + popup sukses
- [ ] Klik tombol Hapus Student → Sama seperti di atas
- [ ] Klik tombol Hapus Permanen (trashed) → Sama seperti di atas

#### 4. Konfirmasi Restore
- [ ] Klik tombol Restore → Muncul modal question (biru)
- [ ] Klik "Batal" → Modal tertutup, data TIDAK restore
- [ ] Klik "Ya, Pulihkan!" → Data restore + popup sukses

#### 5. Responsiveness
- [ ] Desktop (1920x1080) → Modal center & full visible
- [ ] Tablet (768x1024) → Modal tetap responsif
- [ ] Mobile (375x667) → Modal tidak overflow, button tetap clickable

---

## 🎨 KUSTOMISASI LANJUTAN

### Custom Timer & Auto-Close

```javascript
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: 'Data berhasil disimpan!',
    timer: 2000,        // Auto-close setelah 2 detik
    timerProgressBar: true,
    showConfirmButton: false  // Sembunyikan tombol OK
});
```

### Custom Icon & HTML Content

```javascript
Swal.fire({
    icon: 'info',
    title: '<strong>Informasi Penting</strong>',
    html: 'Data akan diekspor ke format <b>Excel</b><br>Proses membutuhkan waktu <u>5-10 detik</u>.',
    confirmButtonText: 'Oke, Mengerti!'
});
```

### Loading State (Saat Proses Async)

```javascript
Swal.fire({
    title: 'Mohon Tunggu...',
    html: 'Sedang mengekspor data...',
    allowOutsideClick: false,
    didOpen: () => {
        Swal.showLoading();
    }
});

// Setelah proses selesai:
Swal.fire({
    icon: 'success',
    title: 'Selesai!',
    text: 'File berhasil diunduh.'
});
```

---

## 🔍 TROUBLESHOOTING

### Masalah 1: Modal Tidak Muncul
**Penyebab:**
- CDN SweetAlert2 tidak terload
- JavaScript error di console

**Solusi:**
1. Cek Network tab → Pastikan CDN status 200 OK
2. Cek Console → Lihat error message
3. Pastikan script di dalam `<body>`, bukan `<head>`

---

### Masalah 2: Konfirmasi Delete Tidak Bekerja
**Penyebab:**
- Class `delete-form` tidak ditambahkan ke `<form>`
- Ada attribute `onsubmit="return confirm(...)"` yang mengganggu

**Solusi:**
1. Pastikan `<form>` punya `class="delete-form"`
2. Hapus semua `onsubmit` atau `onclick` dengan `confirm()`

---

### Masalah 3: Flash Message Tidak Muncul
**Penyebab:**
- Controller tidak mengirim session flash
- Typo di session key (`succes` vs `success`)

**Solusi:**
```php
// BENAR ✅
return redirect()->route('users.index')->with('success', 'Berhasil!');

// SALAH ❌
return redirect()->route('users.index')->with('succes', 'Berhasil!');
```

---

### Masalah 4: Timer Tidak Jalan
**Penyebab:**
- Property `timer` tidak diset
- User klik tombol sebelum timer habis

**Solusi:**
```javascript
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: 'Data berhasil disimpan!',
    timer: 3000,               // ✅ Set timer
    timerProgressBar: true,    // ✅ Tampilkan progress bar
    showConfirmButton: false   // ✅ Hilangkan tombol OK (opsional)
});
```

---

## 📚 REFERENSI & DOKUMENTASI

### Official Documentation
- **SweetAlert2 Docs:** https://sweetalert2.github.io/
- **Examples:** https://sweetalert2.github.io/#examples
- **Configuration:** https://sweetalert2.github.io/#configuration

### CDN Alternative
```html
<!-- jsDelivr (Current) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- UNPKG (Alternative) -->
<script src="https://unpkg.com/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<!-- cdnjs (Alternative) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.3/sweetalert2.all.min.js"></script>
```

### Icon Options
- `success` - Centang hijau ✅
- `error` - Silang merah ❌
- `warning` - Seru kuning ⚠️
- `info` - Info biru ℹ️
- `question` - Tanda tanya biru ❓

---

## ✅ KESIMPULAN

### Sebelum (Native Alert)
```javascript
// ❌ Kaku, tidak menarik
if (confirm('Apakah Anda yakin?')) {
    form.submit();
}

alert('Berhasil disimpan!');
```

### Sesudah (SweetAlert2)
```javascript
// ✅ Modern, animasi smooth, icon menarik
Swal.fire({
    title: 'Apakah Anda Yakin?',
    text: 'Data akan dihapus permanen!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, Hapus!'
}).then((result) => {
    if (result.isConfirmed) {
        form.submit();
    }
});
```

### Keuntungan Implementasi
✅ User Experience lebih baik (animasi, icon, warna)  
✅ Mencegah kesalahan hapus data (konfirmasi modal)  
✅ Feedback yang jelas (sukses/error dengan warna berbeda)  
✅ Konsisten di seluruh aplikasi (satu script global)  
✅ Mobile-friendly & responsif  
✅ Auto-close dengan timer (tidak perlu klik OK)

---

**Dokumentasi Dibuat:** 3 Februari 2026  
**Versi:** 1.0  
**Author:** GitHub Copilot  
**Project:** Sistem Absensi Siswa SMKN 5 Samarinda
