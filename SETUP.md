# 🔧 KASIR - Setup & Connection Guide

## 1️⃣ Prasyarat
- XAMPP terinstall (dengan PHP & MySQL)
- MySQL sudah running
- File aplikasi di: `c:\xampp\htdocs\kasir\`

## 2️⃣ Setup Database (Pilih Salah Satu)

### Opsi A: Automatic Setup (Recommended ✓)
1. Buka browser
2. Akses: **http://localhost/kasir/init-db.php**
3. Ikuti instruksi hingga selesai
4. Database otomatis di-create dengan semua tabel dan sample data

### Opsi B: Manual Setup
1. Buka **phpMyAdmin**: http://localhost/phpmyadmin
2. Buat database baru bernama `kasir`
3. Pilih database `kasir`, kemudian klik **Import**
4. Pilih file `database.sql`
5. Klik **Go/Import**

## 3️⃣ Verifikasi Koneksi
Sebelum membuka aplikasi, test koneksi:
1. Buka: **http://localhost/kasir/test-api.php**
2. Pastikan semua test menunjukkan **✓** (hijau)
3. Jika ada **✗** (merah), periksa error message

## 4️⃣ Login & Gunakan Aplikasi
1. Buka: **http://localhost/kasir/index.php**
2. Aplikasi akan otomatis load semua data dari MySQL
3. Mulai gunakan fitur kasir, produk, laporan, dll

## 📋 Struktur Database

### Tabel yang diperlukan:
- **produk** - Daftar barang & stok
- **supplier** - Data supplier
- **hutang** - Hutang & piutang
- **transaksi** - Riwayat penjualan
- **transaksi_item** - Detail item per transaksi
- **gudang_log** - Riwayat masuk/keluar barang ⭐ **(BARU)**
- **tabungan** - Target pembelian inventaris
- **log** - Riwayat aktivitas

## 🔍 Troubleshooting

### Error: "Koneksi Database Gagal"
```
✓ Pastikan MySQL running (di XAMPP, klik Start MySQL)
✓ Periksa kredensial di koneksi.php:
  - Host: localhost
  - Database: kasir
  - Username: root
  - Password: (kosong)
✓ Run init-db.php untuk membuat database
```

### Error: "Tabel tidak ditemukan"
```
✓ Run: http://localhost/kasir/init-db.php
✓ Atau import manual database.sql via phpMyAdmin
```

### Aplikasi blank/tidak ada data
```
✓ Buka test-api.php untuk check koneksi
✓ Lihat Console (F12 → Network tab) untuk error API
✓ Check error log MySQL di XAMPP
```

## 📝 Config File

### `koneksi.php` - Database Connection
```php
$host = 'localhost';
$dbname = 'kasir';
$username = 'root';
$password = '';
```

Sesuaikan dengan config MySQL Anda jika berbeda.

## 🚀 Yang Sudah Diperbaiki
✓ Menghapus localStorage - semua data dari MySQL  
✓ Menonaktifkan seeder data lokal  
✓ Menambah tabel `gudang_log` ke database  
✓ Membuat script setup otomatis  
✓ Membuat script test koneksi  

## ✅ Checklist Setup
- [ ] MySQL running
- [ ] Database 'kasir' dibuat
- [ ] Run init-db.php
- [ ] Test via test-api.php (semua ✓)
- [ ] Buka index.php
- [ ] Data tampil dengan benar

---
**Need help?** Check:
- init-db.php - Database initialization
- test-api.php - Connection testing
- koneksi.php - Database credentials
