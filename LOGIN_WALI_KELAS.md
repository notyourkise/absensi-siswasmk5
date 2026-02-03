# 📋 QUICK GUIDE: LOGIN WALI KELAS

## 🔐 Kredensial Login (30 Akun)

### Kelas 10

| Kelas | Email | Password |
|-------|-------|----------|
| 10 TJKT 1 | wali10tjkt1@sekolah.com | password |
| 10 TJKT 2 | wali10tjkt2@sekolah.com | password |
| 10 MPLB 1 | wali10mplb1@sekolah.com | password |
| 10 MPLB 2 | wali10mplb2@sekolah.com | password |
| 10 PS 1 | wali10ps1@sekolah.com | password |
| 10 PS 2 | wali10ps2@sekolah.com | password |
| 10 PM 1 | wali10pm1@sekolah.com | password |
| 10 PM 2 | wali10pm2@sekolah.com | password |
| 10 DKV 1 | wali10dkv1@sekolah.com | password |
| 10 DKV 2 | wali10dkv2@sekolah.com | password |

### Kelas 11

| Kelas | Email | Password |
|-------|-------|----------|
| 11 TJKT 1 | wali11tjkt1@sekolah.com | password |
| 11 TJKT 2 | wali11tjkt2@sekolah.com | password |
| 11 MPLB 1 | wali11mplb1@sekolah.com | password |
| 11 MPLB 2 | wali11mplb2@sekolah.com | password |
| 11 PS 1 | wali11ps1@sekolah.com | password |
| 11 PS 2 | wali11ps2@sekolah.com | password |
| 11 PM 1 | wali11pm1@sekolah.com | password |
| 11 PM 2 | wali11pm2@sekolah.com | password |
| 11 DKV 1 | wali11dkv1@sekolah.com | password |
| 11 DKV 2 | wali11dkv2@sekolah.com | password |

### Kelas 12

| Kelas | Email | Password |
|-------|-------|----------|
| 12 TJKT 1 | wali12tjkt1@sekolah.com | password |
| 12 TJKT 2 | wali12tjkt2@sekolah.com | password |
| 12 MPLB 1 | wali12mplb1@sekolah.com | password |
| 12 MPLB 2 | wali12mplb2@sekolah.com | password |
| 12 PS 1 | wali12ps1@sekolah.com | password |
| 12 PS 2 | wali12ps2@sekolah.com | password |
| 12 PM 1 | wali12pm1@sekolah.com | password |
| 12 PM 2 | wali12pm2@sekolah.com | password |
| 12 DKV 1 | wali12dkv1@sekolah.com | password |
| 12 DKV 2 | wali12dkv2@sekolah.com | password |

---

## 🎯 Contoh Testing

### Test 1: Login sebagai Wali Kelas 12 TJKT 1
```
Email: wali12tjkt1@sekolah.com
Password: password
```

**Yang Akan Terlihat:**
- Dashboard: Total siswa hanya dari kelas 12 TJKT 1
- Data Siswa: Hanya siswa kelas 12 TJKT 1
- Laporan: Hanya bisa download kelas 12 TJKT 1

---

### Test 2: Login sebagai Wali Kelas 11 MPLB 2
```
Email: wali11mplb2@sekolah.com
Password: password
```

**Yang Akan Terlihat:**
- Dashboard: Total siswa hanya dari kelas 11 MPLB 2
- Data Siswa: Hanya siswa kelas 11 MPLB 2
- Laporan: Hanya bisa download kelas 11 MPLB 2

---

## ⚠️ Catatan Penting

1. **Semua password default adalah:** `password`
2. **Wali Kelas TIDAK BISA:**
   - Melihat data siswa kelas lain
   - Download laporan kelas lain
   - Filter/pilih kelas lain (dropdown disabled)
3. **Wali Kelas BISA:**
   - Melihat siswa di kelasnya sendiri
   - Tambah/Edit/Hapus siswa di kelasnya
   - Download laporan kelasnya sendiri
   - Ganti password via menu Profile

---

## 🔄 Reset Password

Jika Wali Kelas lupa password:

1. **Via Admin:**
   - Login sebagai Admin
   - Masuk menu "Kelola User"
   - Edit akun Wali Kelas yang bersangkutan
   - Isi password baru + konfirmasi
   - Simpan

2. **Via Database (Manual):**
```bash
php artisan tinker
>>> $user = User::where('email', 'wali12tjkt1@sekolah.com')->first();
>>> $user->password = Hash::make('password_baru');
>>> $user->save();
>>> exit
```

---

**Dibuat:** 3 Februari 2026  
**Untuk:** Testing & Demo Sistem Wali Kelas
