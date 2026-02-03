# 📋 QUICK GUIDE: LOGIN WALI KELAS

## 🔐 Kredensial Login (30 Akun)

### Kelas 10 (X)

| Kelas | Email | Password |
|-------|-------|----------|
| X TJKT 1 | wali10tjkt1@sekolah.com | password |
| X TJKT 2 | wali10tjkt2@sekolah.com | password |
| X MPLB 1 | wali10mplb1@sekolah.com | password |
| X MPLB 2 | wali10mplb2@sekolah.com | password |
| X PS 1 | wali10ps1@sekolah.com | password |
| X PS 2 | wali10ps2@sekolah.com | password |
| X PM 1 | wali10pm1@sekolah.com | password |
| X PM 2 | wali10pm2@sekolah.com | password |
| X DKV 1 | wali10dkv1@sekolah.com | password |
| X DKV 2 | wali10dkv2@sekolah.com | password |

### Kelas 11 (XI)

| Kelas | Email | Password |
|-------|-------|----------|
| XI TJKT 1 | wali11tjkt1@sekolah.com | password |
| XI TJKT 2 | wali11tjkt2@sekolah.com | password |
| XI MPLB 1 | wali11mplb1@sekolah.com | password |
| XI MPLB 2 | wali11mplb2@sekolah.com | password |
| XI PS 1 | wali11ps1@sekolah.com | password |
| XI PS 2 | wali11ps2@sekolah.com | password |
| XI PM 1 | wali11pm1@sekolah.com | password |
| XI PM 2 | wali11pm2@sekolah.com | password |
| XI DKV 1 | wali11dkv1@sekolah.com | password |
| XI DKV 2 | wali11dkv2@sekolah.com | password |

### Kelas 12 (XII)

| Kelas | Email | Password |
|-------|-------|----------|
| XII TJKT 1 | wali12tjkt1@sekolah.com | password |
| XII TJKT 2 | wali12tjkt2@sekolah.com | password |
| XII MPLB 1 | wali12mplb1@sekolah.com | password |
| XII MPLB 2 | wali12mplb2@sekolah.com | password |
| XII PS 1 | wali12ps1@sekolah.com | password |
| XII PS 2 | wali12ps2@sekolah.com | password |
| XII PM 1 | wali12pm1@sekolah.com | password |
| XII PM 2 | wali12pm2@sekolah.com | password |
| XII DKV 1 | wali12dkv1@sekolah.com | password |
| XII DKV 2 | wali12dkv2@sekolah.com | password |

---

## 🎯 Contoh Testing

### Test 1: Login sebagai Wali Kelas XII TJKT 1
```
Email: wali12tjkt1@sekolah.com
Password: password
```

**Yang Akan Terlihat:**
- Dashboard: Total siswa hanya dari kelas XII TJKT 1
- Data Siswa: Hanya siswa kelas XII TJKT 1
- Laporan: Hanya bisa download kelas XII TJKT 1

---

### Test 2: Login sebagai Wali Kelas XI MPLB 2
```
Email: wali11mplb2@sekolah.com
Password: password
```

**Yang Akan Terlihat:**
- Dashboard: Total siswa hanya dari kelas XI MPLB 2
- Data Siswa: Hanya siswa kelas XI MPLB 2
- Laporan: Hanya bisa download kelas XI MPLB 2

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
