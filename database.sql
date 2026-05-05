-- Database KASIR
CREATE DATABASE IF NOT EXISTS kasir;
USE kasir;

-- Tabel PRODUK
CREATE TABLE IF NOT EXISTS produk (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  barcode VARCHAR(50),
  kategori VARCHAR(50),
  hpp DECIMAL(10, 2),
  harga DECIMAL(10, 2),
  stok INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel SUPPLIER
CREATE TABLE IF NOT EXISTS supplier (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  telp VARCHAR(20),
  kategori VARCHAR(50),
  alamat TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel HUTANG (Hutang & Piutang)
CREATE TABLE IF NOT EXISTS hutang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tanggal DATE,
  nama VARCHAR(100),
  tipe ENUM('hutang', 'piutang') DEFAULT 'hutang',
  ket TEXT,
  jumlah DECIMAL(12, 2),
  jatuh_tempo DATE,
  lunas TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel TRANSAKSI
CREATE TABLE IF NOT EXISTS transaksi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tanggal DATE,
  waktu TIME,
  total DECIMAL(12, 2),
  bayar DECIMAL(12, 2),
  kembali DECIMAL(12, 2),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel TRANSAKSI_ITEM
CREATE TABLE IF NOT EXISTS transaksi_item (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_transaksi INT,
  id_produk INT,
  nama VARCHAR(100),
  harga DECIMAL(10, 2),
  hpp DECIMAL(10, 2),
  qty INT,
  FOREIGN KEY (id_transaksi) REFERENCES transaksi(id) ON DELETE CASCADE,
  FOREIGN KEY (id_produk) REFERENCES produk(id) ON DELETE SET NULL
);

-- Tabel LOG
CREATE TABLE IF NOT EXISTS log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tanggal DATE,
  waktu TIME,
  aksi VARCHAR(100),
  detail TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample Data PRODUK
INSERT INTO produk (nama, barcode, kategori, hpp, harga, stok) VALUES
('Indomie Goreng', 'SKU-001', 'Makanan', 2800, 3500, 120),
('Aqua 600ml', 'SKU-002', 'Minuman', 2500, 3500, 80),
('Teh Botol 350ml', 'SKU-003', 'Minuman', 3500, 5000, 60),
('Roti Tawar Sari Roti', 'SKU-004', 'Makanan', 10000, 14000, 15),
('Chitato BBQ', 'SKU-005', 'Snack', 8500, 12000, 40),
('Sabun Mandi Lifebuoy', 'SKU-006', 'Perawatan', 4000, 6000, 3),
('Susu Ultra 250ml', 'SKU-007', 'Minuman', 4500, 6000, 55),
('Minyak Goreng 1L', 'SKU-008', 'Kebutuhan Rumah', 18000, 22000, 25);

-- Sample Data SUPPLIER
INSERT INTO supplier (nama, telp, kategori, alamat) VALUES
('CV. Sumber Makmur', '08123456789', 'Sembako, Makanan', 'Jl. Raya No. 10'),
('PT. Maju Bersama', '08567891234', 'Minuman, Snack', 'Jl. Industri No. 5');

-- Tabel TABUNGAN (Target Pembelian Inventaris)
CREATE TABLE IF NOT EXISTS tabungan (
  id int(11) NOT NULL AUTO_INCREMENT,
  nama_barang varchar(255) NOT NULL,
  harga double NOT NULL,
  tanggal_target date NOT NULL,
  terkumpul double DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel GUDANG_LOG (Riwayat Barang Masuk/Keluar)
CREATE TABLE IF NOT EXISTS gudang_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tanggal DATE,
  produk VARCHAR(100),
  id_produk INT,
  tipe ENUM('masuk', 'keluar') DEFAULT 'masuk',
  qty INT,
  keterangan TEXT,
  saldo INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_produk) REFERENCES produk(id) ON DELETE SET NULL
);

-- Sample Data TABUNGAN
INSERT INTO tabungan (nama_barang, harga, tanggal_target, terkumpul) VALUES
('Etalase Kaca Baru', 1500000, '2026-12-31', 250000),
('AC Toko 1/2 PK', 3200000, '2027-02-15', 0);

-- Sample Data GUDANG_LOG
INSERT INTO gudang_log (tanggal, produk, id_produk, tipe, qty, keterangan, saldo) VALUES
('2026-05-04', 'Indomie Goreng', 1, 'masuk', 50, 'Restock dari CV. Sumber Makmur', 170),
('2026-05-04', 'Aqua 600ml', 2, 'masuk', 24, 'Restock dari PT. Maju Bersama', 104),
('2026-05-03', 'Teh Botol 350ml', 3, 'keluar', 5, 'Penjualan STR-0001', 55),
('2026-05-03', 'Minyak Goreng 1L', 8, 'masuk', 10, 'Restock', 35);
