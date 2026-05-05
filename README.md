# KASIR - Sistem Kasir Digital

Aplikasi Point of Sale (POS) berbasis PHP dan MySQL untuk manajemen toko.

<img width="1899" height="889" alt="image" src="https://github.com/user-attachments/assets/0ace4a70-4aa8-4ceb-a0b4-bfc917dee1fb" />


## Fitur

✅ Dashboard dengan analitik penjualan  
✅ Kasir/POS dengan keranjang belanja  
✅ Manajemen Produk  
✅ Manajemen Supplier  
✅ Pencatatan Hutang & Piutang  
✅ Laporan Keuangan  
✅ Log Aktivitas  
✅ Manajemen Gudang  
✅ Notifikasi stok menipis  

## Setup

### 1. Install Database
Buka phpMyAdmin atau MySQL CLI dan jalankan query dari `database.sql`:

```bash
mysql -u root -p < database.sql
```

Atau copy-paste isi `database.sql` ke phpMyAdmin.

### 2. Konfigurasi Koneksi Database
Edit file `koneksi.php`:

```php
$host = 'localhost';
$dbname = 'kasir';      // nama database
$username = 'root';     // username MySQL
$password = '';         // password MySQL (default XAMPP kosong)
```

### 3. Jalankan Aplikasi
Buka di browser:
```
http://localhost/kasir
```

## Struktur File

```
kasir/
├── index..php          # Frontend (HTML + JavaScript)
├── api.php            # Backend API
├── koneksi.php        # Koneksi Database PDO
└── database.sql       # Script setup database
```

## API Endpoints

### GET /api.php?action=init
Mengambil semua data (produk, supplier, hutang, transaksi, log)

### POST /api.php?action=save_produk
Simpan/update produk

### POST /api.php?action=save_supplier
Simpan supplier

### POST /api.php?action=save_hutang
Catat hutang/piutang

### POST /api.php?action=save_transaksi
Simpan transaksi penjualan

### POST /api.php?action=lunas_hutang
Tandai hutang sebagai lunas

### POST /api.php?action=add_log
Catat aktivitas ke log

## Troubleshooting

**Error: "Koneksi Database Gagal"**
- Pastikan XAMPP/MySQL sudah running
- Pastikan database `kasir` sudah dibuat
- Cek konfigurasi username/password di `koneksi.php`

**Error: "API Error"**
- Buka Developer Tools (F12) → Console untuk melihat detail error
- Cek bahwa `api.php` dan `koneksi.php` tidak memiliki syntax error

**Halaman blank**
- Cek bahwa semua file sudah di tempat yang benar
- Clear browser cache (Ctrl+Shift+Delete)
