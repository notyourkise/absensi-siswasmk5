# 📋 DOKUMENTASI TEKNIS: FITUR LOG AKTIVITAS (AUDIT TRAIL) & SOFT DELETES
**Sistem Absensi SMKN 5 Samarinda**

---

## 🎯 TUJUAN SISTEM

Membangun sistem pengawasan (monitoring) yang transparan di mana setiap perubahan data krusial—baik yang dilakukan oleh **Admin** maupun oleh **User** (Wali Kelas/Petugas) terhadap data diri sendiri atau data siswa—tercatat secara otomatis dalam database. Selain itu, mencegah kehilangan data User secara permanen melalui mekanisme **Soft Deletes**.

### Manfaat Utama:
✅ **Transparansi**: Semua aktivitas tercatat dengan jelas  
✅ **Akuntabilitas**: Siapa melakukan apa, kapan, dan dari mana  
✅ **Keamanan**: Deteksi aktivitas mencurigakan (reset password, perubahan role)  
✅ **Recovery**: Data user yang dihapus dapat dipulihkan  
✅ **Compliance**: Memenuhi standar audit untuk sistem informasi sekolah  

---

## 🗄️ STRUKTUR DATABASE

### A. Tabel Baru: `activity_logs`
Berfungsi menyimpan riwayat aktivitas sistem.

**Schema:**
```sql
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Deskripsi Kolom:**
| Kolom | Tipe Data | Nullable | Keterangan |
|-------|-----------|----------|------------|
| `id` | BIGINT UNSIGNED | NO | Primary Key, Auto Increment |
| `user_id` | BIGINT UNSIGNED | NO | FK ke tabel users (siapa yang melakukan) |
| `action` | VARCHAR(255) | NO | Jenis aktivitas (UPDATE USER, HAPUS SISWA, dll) |
| `description` | TEXT | NO | Detail lengkap aktivitas |
| `ip_address` | VARCHAR(45) | YES | IP Address pelaku (IPv4/IPv6) |
| `created_at` | TIMESTAMP | YES | Waktu kejadian |
| `updated_at` | TIMESTAMP | YES | Laravel timestamp |

**Indexing:**
- Primary Key: `id`
- Foreign Key: `user_id` → `users.id` (ON DELETE CASCADE)
- Index tambahan (disarankan): `created_at`, `action`

---

### B. Perubahan Tabel: `users`

**Penambahan Kolom:**
```sql
ALTER TABLE users ADD COLUMN deleted_at TIMESTAMP NULL;
```

**Tujuan:** 
Mengaktifkan fitur **Soft Deletes** (data tidak hilang saat dihapus, hanya disembunyikan).

**Cara Kerja:**
- Saat `deleted_at` = `NULL` → User aktif
- Saat `deleted_at` = `[timestamp]` → User "dihapus" (disembunyikan dari query normal)
- Data masih ada di database dan bisa direstore

---

## 🔍 LOGIKA PENCATATAN AKTIVITAS (TRIGGER LOG)

Sistem akan memicu pencatatan log pada **3 Controller utama**:

### A. UserController (Wilayah Kerja Admin)

#### 1. **Saat Admin Membuat User Baru**
**Method:** `store()`

**Trigger Log:**
```php
ActivityLog::create([
    'user_id' => auth()->id(),
    'action' => 'TAMBAH USER',
    'description' => "Menambahkan user baru: {$newUser->name} ({$newUser->email}) dengan role {$newUser->role}",
    'ip_address' => request()->ip(),
]);
```

**Contoh Output:**
```
Action: TAMBAH USER
Description: Menambahkan user baru: Budi Santoso (budi@sekolah.com) dengan role petugas
IP: 192.168.1.100
```

---

#### 2. **Saat Admin Mengedit User Lain**
**Method:** `update()`

**Logika Deteksi Perubahan:**
```php
// Deteksi perubahan nama
if ($user->name !== $request->name) {
    $changes[] = "nama dari '{$user->name}' menjadi '{$request->name}'";
}

// Deteksi perubahan email
if ($user->email !== $request->email) {
    $changes[] = "email dari '{$user->email}' menjadi '{$request->email}'";
}

// Deteksi perubahan role
if ($user->role !== $request->role) {
    $changes[] = "role dari '{$user->role}' menjadi '{$request->role}'";
}

// DETEKSI RESET PASSWORD (PENTING!)
if ($request->filled('password')) {
    $passwordReset = true;
}
```

**Trigger Log:**
```php
if (!empty($changes) || $passwordReset) {
    $description = "Memperbarui data user: {$user->name}";
    
    if (!empty($changes)) {
        $description .= " - Perubahan: " . implode(', ', $changes);
    }
    
    if ($passwordReset) {
        $description .= " - PASSWORD DIRESET oleh Admin";
    }

    ActivityLog::create([
        'user_id' => auth()->id(),
        'action' => 'UPDATE USER',
        'description' => $description,
        'ip_address' => request()->ip(),
    ]);
}
```

**Contoh Output:**
```
Action: UPDATE USER
Description: Memperbarui data user: Siti Aminah - Perubahan: role dari 'petugas' 
             menjadi 'wali_kelas' - PASSWORD DIRESET oleh Admin
IP: 192.168.1.100
```

---

#### 3. **Saat Admin Menghapus User**
**Method:** `destroy()`

**Logika:**
```php
// Simpan data sebelum dihapus
$userName = $user->name;
$userEmail = $user->email;
$userRole = $user->role;

// Soft Delete
$user->delete();

// Log
ActivityLog::create([
    'user_id' => auth()->id(),
    'action' => 'HAPUS USER',
    'description' => "Menghapus user: {$userName} ({$userEmail}) dengan role {$userRole} [SOFT DELETE]",
    'ip_address' => request()->ip(),
]);
```

**Contoh Output:**
```
Action: HAPUS USER
Description: Menghapus user: Ahmad Rizki (ahmad@sekolah.com) dengan role petugas [SOFT DELETE]
IP: 192.168.1.100
```

**Catatan:** Tag `[SOFT DELETE]` menandakan data masih bisa dipulihkan.

---

### B. ProfileController (Wilayah Kerja Pribadi/Semua Role)

#### 1. **Saat User Mengupdate Nama/Email Sendiri**
**Method:** `update()`

**Logika Deteksi:**
```php
$changes = [];
$oldName = $request->user()->name;
$oldEmail = $request->user()->email;

// Deteksi perubahan nama
if ($request->user()->isDirty('name')) {
    $changes[] = "nama dari '{$oldName}' menjadi '{$request->user()->name}'";
}

// Deteksi perubahan email
if ($request->user()->isDirty('email')) {
    $changes[] = "email dari '{$oldEmail}' menjadi '{$request->user()->email}'";
}
```

**Trigger Log:**
```php
if (!empty($changes)) {
    ActivityLog::create([
        'user_id' => auth()->id(),
        'action' => 'UPDATE PROFIL',
        'description' => "Memperbarui profil sendiri - Perubahan: " . implode(', ', $changes),
        'ip_address' => request()->ip(),
    ]);
}
```

**Contoh Output:**
```
Action: UPDATE PROFIL
Description: Memperbarui profil sendiri - Perubahan: email dari 'old@sekolah.com' 
             menjadi 'new@sekolah.com'
IP: 192.168.1.50
```

---

#### 2. **Saat User Mengganti Password Sendiri**
**Method:** `updatePassword()`

**Trigger Log:**
```php
ActivityLog::create([
    'user_id' => auth()->id(),
    'action' => 'GANTI PASSWORD',
    'description' => "Mengganti password akun sendiri: {$request->user()->name} ({$request->user()->email})",
    'ip_address' => request()->ip(),
]);
```

**Contoh Output:**
```
Action: GANTI PASSWORD
Description: Mengganti password akun sendiri: Siti Fatimah (siti@sekolah.com)
IP: 192.168.1.75
```

**Perbedaan dengan Reset Password Admin:**
- Ganti password sendiri: User mengetikkan password lama + baru
- Reset password admin: Admin langsung set password baru (tidak perlu password lama)

---

### C. StudentController (Wilayah Kerja Wali Kelas/Admin)

#### 1. **Saat Mengedit Data Siswa**
**Method:** `update()`

**Logika Deteksi Perubahan:**
```php
$changes = [];

if ($student->nisn != $request->nisn) {
    $changes[] = "NISN dari '{$student->nisn}' menjadi '{$request->nisn}'";
}

if ($student->nama != $request->nama) {
    $changes[] = "nama dari '{$student->nama}' menjadi '{$request->nama}'";
}

if ($student->kelas != $request->kelas) {
    $changes[] = "kelas dari '{$student->kelas}' menjadi '{$request->kelas}'";
}

if ($request->hasFile('foto')) {
    $changes[] = "foto diperbarui";
}
```

**Trigger Log:**
```php
if (!empty($changes)) {
    ActivityLog::create([
        'user_id' => auth()->id(),
        'action' => 'UPDATE SISWA',
        'description' => "Memperbarui data siswa: {$student->nama} - Perubahan: " . implode(', ', $changes),
        'ip_address' => request()->ip(),
    ]);
}
```

**Contoh Output:**
```
Action: UPDATE SISWA
Description: Memperbarui data siswa: Andi Wijaya - Perubahan: NISN dari '1234567890' 
             menjadi '0987654321', kelas dari 'XII RPL 1' menjadi 'XII RPL 2'
IP: 192.168.1.60
```

---

#### 2. **Saat Menghapus Siswa**
**Method:** `destroy()`

**Logika:**
```php
// Simpan data sebelum dihapus
$studentName = $student->nama;
$studentNisn = $student->nisn;
$studentClass = $student->kelas;

// Hapus siswa (hard delete)
$student->delete();

// Log
ActivityLog::create([
    'user_id' => auth()->id(),
    'action' => 'HAPUS SISWA',
    'description' => "Menghapus data siswa: {$studentName} (NISN: {$studentNisn}) Kelas {$studentClass}",
    'ip_address' => request()->ip(),
]);
```

**Contoh Output:**
```
Action: HAPUS SISWA
Description: Menghapus data siswa: Rina Kusuma (NISN: 1122334455) Kelas XI TKJ 1
IP: 192.168.1.60
```

**Catatan:** Siswa menggunakan hard delete (dihapus permanen), berbeda dengan User yang soft delete.

---

## 🔐 HAK AKSES & ANTARMUKA (UI)

### Menu Sidebar
**Lokasi:** Sidebar Admin (hanya muncul untuk role `admin`)

**Kode Blade:**
```blade
@if(Auth::user()->role == 'admin')
    <a href="{{ route('activity-logs.index') }}" 
       class="{{ $baseClass }} {{ request()->routeIs('activity-logs.*') ? $activeClass : $inactiveClass }}">
        <i class="fas fa-history w-6 text-center text-lg"></i>
        <span x-show="!sidebarCollapsed" class="ml-3">Log Aktivitas</span>
    </a>
@endif
```

---

### Hak Akses (RBAC)

| Role | Akses Menu | Akses Data |
|------|-----------|-----------|
| **Admin** | ✅ Ya | ✅ Semua log |
| **Wali Kelas** | ❌ Tidak | ❌ Tidak ada |
| **Petugas** | ❌ Tidak | ❌ Tidak ada |

**Route Protection:**
```php
Route::middleware('role:admin')->group(function () {
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])
        ->name('activity-logs.index');
});
```

---

### Tampilan Halaman Log Aktivitas

#### **Fitur Utama:**

1. **Filter Jenis Aktivitas**
   - Dropdown berisi semua jenis action unik
   - Opsi: Semua, TAMBAH USER, UPDATE USER, HAPUS USER, dll.

2. **Pencarian**
   - Input search untuk mencari berdasarkan deskripsi
   - Realtime filter saat submit form

3. **Tabel Responsif**
   Kolom yang ditampilkan:
   - **Waktu** (Tanggal + Jam)
   - **Pengguna** (Nama, Email, Badge Role)
   - **Aktivitas** (Badge berwarna)
   - **Deskripsi** (Detail lengkap)
   - **IP Address**

4. **Badge Warna Aktivitas:**
   - 🟢 **Hijau**: TAMBAH (create)
   - 🔵 **Biru**: UPDATE/GANTI (update)
   - 🔴 **Merah**: HAPUS (delete)
   - ⚪ **Abu-abu**: Lainnya

5. **Pagination**
   - 15 item per halaman
   - Laravel default pagination dengan query string preserved

6. **Info Box**
   - Penjelasan tujuan log aktivitas
   - Panduan penggunaan

---

## 📁 IMPLEMENTASI FILE UTAMA

### 1. **Model: ActivityLog.php**
**Path:** `app/Models/ActivityLog.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

**Relasi:**
- `belongsTo(User::class)`: Setiap log dimiliki oleh 1 user

---

### 2. **Model: User.php (Update)**
**Path:** `app/Models/User.php`

**Perubahan:**
```php
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes; // Tambah SoftDeletes
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Tambahan untuk RBAC
    ];
    
    // Relasi ke ActivityLog
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
```

**Fitur Baru:**
- `SoftDeletes` trait: Aktifkan soft delete
- `activityLogs()` relasi: 1 user punya banyak log

---

### 3. **Controller: ActivityLogController.php**
**Path:** `app/Http/Controllers/ActivityLogController.php`

**Method:**
```php
public function index(Request $request)
{
    $query = ActivityLog::with('user');

    // Filter by action
    if ($request->filled('action')) {
        $query->where('action', $request->action);
    }

    // Search by description
    if ($request->filled('search')) {
        $query->where('description', 'like', '%' . $request->search . '%');
    }

    // Latest first, paginate
    $logs = $query->latest()->paginate(15)->withQueryString();

    // Get unique actions for filter dropdown
    $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');

    return view('activity_logs.index', compact('logs', 'actions'));
}
```

**Fitur:**
- Eager loading `user` untuk performa
- Filter berdasarkan action
- Search berdasarkan description
- Pagination 15 item
- Query string preserved (filter tetap ada saat pindah halaman)

---

### 4. **Controller: UserController.php (Update)**
**Path:** `app/Http/Controllers/UserController.php`

**Import Tambahan:**
```php
use App\Models\ActivityLog;
```

**Method yang Dimodifikasi:**
- `store()`: Log saat tambah user baru
- `update()`: Log saat edit user dengan deteksi perubahan
- `destroy()`: Log saat hapus user (soft delete)

**Lihat section "Logika Pencatatan Aktivitas" untuk detail implementasi.**

---

### 5. **Controller: ProfileController.php (Update)**
**Path:** `app/Http/Controllers/ProfileController.php`

**Import Tambahan:**
```php
use App\Models\ActivityLog;
```

**Method yang Dimodifikasi:**
- `update()`: Log saat user update profil sendiri
- `updatePassword()`: Log saat user ganti password sendiri

**Fitur Khusus:**
- Menggunakan `isDirty()` untuk deteksi perubahan
- Membandingkan nilai lama vs baru sebelum save

---

### 6. **Controller: StudentController.php (Update)**
**Path:** `app/Http/Controllers/StudentController.php`

**Import Tambahan:**
```php
use App\Models\ActivityLog;
```

**Method yang Dimodifikasi:**
- `update()`: Log saat edit data siswa
- `destroy()`: Log saat hapus siswa

**Catatan:**
- Siswa menggunakan **hard delete** (data hilang permanen)
- User menggunakan **soft delete** (data bisa dipulihkan)

---

### 7. **Migration: create_activity_logs_table.php**
**Path:** `database/migrations/xxxx_create_activity_logs_table.php`

```php
Schema::create('activity_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('action');
    $table->text('description');
    $table->string('ip_address')->nullable();
    $table->timestamps();
});
```

**Foreign Key Behavior:**
- `onDelete('cascade')`: Jika user dihapus permanent, log-nya ikut terhapus
- **Note:** Karena user pakai soft delete, cascade jarang terjadi

---

### 8. **Migration: add_soft_deletes_to_users_table.php**
**Path:** `database/migrations/xxxx_add_soft_deletes_and_create_logs_table.php`

```php
Schema::table('users', function (Blueprint $table) {
    $table->softDeletes(); // Tambah kolom deleted_at
});
```

---

### 9. **View: activity_logs/index.blade.php**
**Path:** `resources/views/activity_logs/index.blade.php`

**Komponen Utama:**
1. Header dengan judul
2. Form filter (action, search)
3. Tabel responsif dengan data log
4. Badge warna untuk status
5. Pagination
6. Info box

**Blade Directives yang Digunakan:**
- `@forelse`: Loop dengan fallback jika kosong
- `@if($log->user)`: Cek apakah user masih ada
- `{{ $log->created_at->format() }}`: Format tanggal
- `{{ $logs->links() }}`: Pagination

---

### 10. **Route: web.php (Update)**
**Path:** `routes/web.php`

**Route Baru:**
```php
use App\Http\Controllers\ActivityLogController;

Route::middleware('role:admin')->group(function () {
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])
        ->name('activity-logs.index');
});
```

**Middleware:**
- `auth`: Harus login
- `role:admin`: Hanya admin yang bisa akses

---

## 📊 CONTOH DATA LOG DALAM DATABASE

### Tabel `activity_logs`:

| id | user_id | action | description | ip_address | created_at |
|----|---------|--------|-------------|------------|------------|
| 1 | 1 | TAMBAH USER | Menambahkan user baru: Budi (budi@sekolah.com) dengan role petugas | 192.168.1.100 | 2026-02-03 14:30:15 |
| 2 | 1 | UPDATE USER | Memperbarui data user: Siti - Perubahan: role dari 'petugas' menjadi 'wali_kelas' - PASSWORD DIRESET oleh Admin | 192.168.1.100 | 2026-02-03 14:35:22 |
| 3 | 2 | UPDATE PROFIL | Memperbarui profil sendiri - Perubahan: email dari 'old@email.com' menjadi 'new@email.com' | 192.168.1.50 | 2026-02-03 15:10:45 |
| 4 | 2 | GANTI PASSWORD | Mengganti password akun sendiri: Siti (siti@sekolah.com) | 192.168.1.50 | 2026-02-03 15:15:30 |
| 5 | 1 | UPDATE SISWA | Memperbarui data siswa: Andi Wijaya - Perubahan: kelas dari 'XII RPL 1' menjadi 'XII RPL 2' | 192.168.1.100 | 2026-02-03 16:20:10 |
| 6 | 1 | HAPUS USER | Menghapus user: Ahmad Rizki (ahmad@sekolah.com) dengan role petugas [SOFT DELETE] | 192.168.1.100 | 2026-02-03 16:45:00 |

---

## 🔧 CARA PENGGUNAAN

### Untuk Admin:

1. **Akses Menu**
   - Login sebagai Admin
   - Klik menu "Log Aktivitas" di sidebar

2. **Filter Data**
   - Pilih jenis aktivitas dari dropdown
   - Atau gunakan search untuk mencari kata kunci

3. **Lihat Detail**
   - Tabel menampilkan semua informasi lengkap
   - Waktu, pelaku, aktivitas, deskripsi, IP

4. **Interpretasi Badge:**
   - Badge hijau = Penambahan data
   - Badge biru = Perubahan data
   - Badge merah = Penghapusan data

---

## 🛡️ KEAMANAN & BEST PRACTICES

### 1. **IP Address Tracking**
```php
request()->ip()
```
- Mencatat IP address pelaku
- Berguna untuk deteksi akses tidak sah
- Support IPv4 dan IPv6

### 2. **Foreign Key Cascade**
```php
->onDelete('cascade')
```
- Jika user dihapus permanent, log-nya ikut terhapus
- Menghindari orphan records

### 3. **Soft Delete Protection**
```php
$user->delete(); // Soft delete (data tetap ada)
```
- User tidak bisa dihapus permanent secara default
- Admin harus eksplisit jika ingin force delete

### 4. **Smart Change Detection**
```php
if ($user->name !== $request->name) {
    $changes[] = "...";
}
```
- Hanya log jika ada perubahan
- Hindari spam log untuk update tanpa perubahan

### 5. **Password Reset Detection**
```php
if ($passwordReset) {
    $description .= " - PASSWORD DIRESET oleh Admin";
}
```
- Khusus menandai jika admin mereset password orang lain
- Penting untuk audit keamanan

---

## 📈 SKALABILITAS & PERFORMA

### Optimasi Query:

1. **Eager Loading**
   ```php
   ActivityLog::with('user')->latest()->paginate(15);
   ```
   - Hindari N+1 query problem
   - Load user data sekaligus

2. **Indexing Database**
   ```sql
   CREATE INDEX idx_created_at ON activity_logs(created_at);
   CREATE INDEX idx_action ON activity_logs(action);
   ```
   - Percepat filtering dan sorting

3. **Pagination**
   - 15 item per halaman
   - Hindari load semua data sekaligus

### Log Retention Policy (Opsional):

Untuk mencegah tabel terlalu besar, bisa dibuat scheduled job untuk hapus log lama:

```php
// app/Console/Commands/CleanOldLogs.php
ActivityLog::where('created_at', '<', now()->subMonths(12))->delete();
```

**Jadwal:**
- Hapus log lebih dari 12 bulan
- Jalankan setiap akhir bulan

---

## ✅ CHECKLIST IMPLEMENTASI

- [x] Migration `activity_logs` table
- [x] Migration `add soft deletes to users`
- [x] Model `ActivityLog` dengan relasi
- [x] Model `User` dengan SoftDeletes trait
- [x] Controller `ActivityLogController`
- [x] Update `UserController` (store, update, destroy)
- [x] Update `ProfileController` (update, updatePassword)
- [x] Update `StudentController` (update, destroy)
- [x] View `activity_logs/index.blade.php`
- [x] Route `activity-logs.index` dengan middleware
- [x] Menu sidebar "Log Aktivitas" (admin only)
- [x] Testing manual semua fitur

---

## 🧪 TESTING MANUAL

### Test Case 1: Log Tambah User
1. Login sebagai Admin
2. Tambah user baru via menu "Manajemen User"
3. Cek menu "Log Aktivitas"
4. ✅ Harus muncul log "TAMBAH USER"

### Test Case 2: Log Update User (Reset Password)
1. Login sebagai Admin
2. Edit user lain, ganti password
3. Cek log
4. ✅ Harus ada tag "PASSWORD DIRESET oleh Admin"

### Test Case 3: Log Soft Delete User
1. Login sebagai Admin
2. Hapus user (bukan diri sendiri)
3. Cek log
4. ✅ Harus ada tag "[SOFT DELETE]"
5. ✅ Data user masih ada di database (cek via Tinker)

### Test Case 4: Log Update Profil
1. Login sebagai user biasa
2. Edit nama/email di menu "Profile"
3. Cek log (sebagai admin)
4. ✅ Harus muncul "UPDATE PROFIL"

### Test Case 5: Log Ganti Password
1. Login sebagai user biasa
2. Ganti password via menu "Profile"
3. Cek log
4. ✅ Harus muncul "GANTI PASSWORD"

### Test Case 6: Filter & Search
1. Login sebagai Admin
2. Buka menu "Log Aktivitas"
3. Pilih filter action "UPDATE USER"
4. ✅ Hanya tampil log update user
5. Coba search kata "password"
6. ✅ Hanya tampil log yang mengandung kata password

---

## 📚 REFERENSI

### Laravel Documentation:
- [Eloquent Soft Deletes](https://laravel.com/docs/12.x/eloquent#soft-deleting)
- [Query Scopes](https://laravel.com/docs/12.x/eloquent#query-scopes)
- [Pagination](https://laravel.com/docs/12.x/pagination)
- [Middleware](https://laravel.com/docs/12.x/middleware)

### Best Practices:
- [OWASP Logging Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Logging_Cheat_Sheet.html)
- [Database Audit Trail Best Practices](https://www.sqlshack.com/database-audit-trail-best-practices/)

---

## 🤝 KONTRIBUTOR

**Developer:** Tim Developer SMKN 5 Samarinda  
**Versi:** 1.0  
**Tanggal:** 3 Februari 2026  
**Status:** ✅ Production Ready

---

## 📞 SUPPORT

Jika ada pertanyaan atau bug, hubungi:
- Email: admin@smkn5samarinda.sch.id
- Developer: [Masukkan kontak developer]

---

**© 2026 SMKN 5 Samarinda - Sistem Absensi Siswa**
