<?php
header('Content-Type: application/json');
require 'koneksi.php';

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
// Ambil data JSON dari body request
$data = json_decode(file_get_contents('php://input'), true);

try {
    // 1. INIT: Ambil semua data untuk dimuat di tampilan Frontend
    if ($action == 'init' && $method == 'GET') {
        // Ambil produk dengan minStok default 5
        $produk = $pdo->query("SELECT *,5 as minStok FROM produk")->fetchAll(PDO::FETCH_ASSOC);
        $supplier = $pdo->query("SELECT * FROM supplier")->fetchAll(PDO::FETCH_ASSOC);
        $hutang = $pdo->query("SELECT * FROM hutang ORDER BY lunas ASC, jatuh_tempo ASC")->fetchAll(PDO::FETCH_ASSOC);
        $transaksi = $pdo->query("SELECT * FROM transaksi ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        
        // Tambahkan items dan hitung laba untuk setiap transaksi
        $stmtItems = $pdo->prepare("SELECT * FROM transaksi_item WHERE id_transaksi = ?");
        foreach($transaksi as &$t) {
            $stmtItems->execute([$t['id']]);
            $t['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
            $t['noStruk'] = 'STR-' . str_pad($t['id'], 4, '0', STR_PAD_LEFT);
            // Hitung laba
            $labaKotor = 0;
            foreach($t['items'] as $item) {
                $labaKotor += ($item['harga'] - $item['hpp']) * $item['qty'];
            }
            $t['labaKotor'] = $labaKotor;
            $t['labaBersih'] = $labaKotor;
            $t['diskon'] = 0;
        }
        
        $log = $pdo->query("SELECT * FROM log ORDER BY id DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
        // Ambil history gudang (masuk/keluar)
        $gudang_log = $pdo->query("SELECT * FROM gudang_log ORDER BY id DESC LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(compact('produk', 'supplier', 'hutang', 'transaksi', 'log', 'gudang_log'));
        exit;
    }

    // 2. PRODUK
    if ($action == 'save_produk' && $method == 'POST') {
        if (!empty($data['id'])) {
            $stmt = $pdo->prepare("UPDATE produk SET nama=?, barcode=?, kategori=?, hpp=?, harga=?, stok=? WHERE id=?");
            $stmt->execute([$data['nama'], $data['barcode'], $data['kategori'], $data['hpp'], $data['harga'], $data['stok'], $data['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO produk (nama, barcode, kategori, hpp, harga, stok) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['nama'], $data['barcode'], $data['kategori'], $data['hpp'], $data['harga'], $data['stok']]);
        }
        echo json_encode(["status" => "success"]); exit;
    }

    if ($action == 'delete_produk' && $method == 'POST') {
        $pdo->prepare("DELETE FROM produk WHERE id=?")->execute([$data['id']]);
        echo json_encode(["status" => "success"]); exit;
    }
    
    if ($action == 'update_produk' && $method == 'POST') {
        $stmt = $pdo->prepare("UPDATE produk SET nama=?, barcode=?, kategori=?, hpp=?, harga=?, stok=? WHERE id=?");
        $stmt->execute([$data['nama'], $data['barcode'], $data['kategori'], $data['hpp'], $data['harga'], $data['stok'], $data['id']]);
        echo json_encode(["status" => "success"]); exit;
    }

    // 3. SUPPLIER
    if ($action == 'save_supplier' && $method == 'POST') {
        $stmt = $pdo->prepare("INSERT INTO supplier (nama, telp, kategori, alamat) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['nama'], $data['telp'], $data['kategori'], $data['alamat']]);
        echo json_encode(["status" => "success"]); exit;
    }
    if ($action == 'delete_supplier' && $method == 'POST') {
        $pdo->prepare("DELETE FROM supplier WHERE id=?")->execute([$data['id']]);
        echo json_encode(["status" => "success"]); exit;
    }
    
    if ($action == 'delete_hutang' && $method == 'POST') {
        $pdo->prepare("DELETE FROM hutang WHERE id=?")->execute([$data['id']]);
        echo json_encode(["status" => "success"]); exit;
    }

    // 4. HUTANG / PIUTANG
    if ($action == 'save_hutang' && $method == 'POST') {
        $stmt = $pdo->prepare("INSERT INTO hutang (tanggal, nama, tipe, ket, jumlah, jatuh_tempo, lunas) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['tanggal'], $data['nama'], $data['tipe'], $data['ket'], $data['jumlah'], $data['jatuh_tempo'], 0]);
        echo json_encode(["status" => "success"]); exit;
    }
    if ($action == 'lunas_hutang' && $method == 'POST') {
        $pdo->prepare("UPDATE hutang SET lunas=1 WHERE id=?")->execute([$data['id']]);
        echo json_encode(["status" => "success"]); exit;
    }

    // 5. RIWAYAT / LOG
    if ($action == 'add_log' && $method == 'POST') {
        $stmt = $pdo->prepare("INSERT INTO log (tanggal, waktu, aksi, detail) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['tanggal'], $data['waktu'], $data['aksi'], $data['detail']]);
        echo json_encode(["status" => "success"]); exit;
    }

    // 6. TRANSAKSI (Dengan Database Transaction / Rollback otomatis jika error)
    if ($action == 'save_transaksi' && $method == 'POST') {
        $pdo->beginTransaction();

        // Simpan Transaksi Utama
        $stmt = $pdo->prepare("INSERT INTO transaksi (tanggal, waktu, total, bayar, kembali) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['tanggal'], $data['waktu'], $data['total'], $data['bayar'], $data['kembali']]);
        $id_transaksi = $pdo->lastInsertId();

        // Persiapan Query Item dan Stok
        $stmtItem = $pdo->prepare("INSERT INTO transaksi_item (id_transaksi, id_produk, nama, harga, hpp, qty) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtStok = $pdo->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");

        $stmtInsertGudang = $pdo->prepare("INSERT INTO gudang_log (tanggal, produk, id_produk, tipe, qty, keterangan, saldo) VALUES (?, ?, ?, 'keluar', ?, ?, ?)");

        // Loop keranjang dan potong stok
        foreach($data['items'] as $item) {
            $stmtItem->execute([$id_transaksi, $item['id'], $item['nama'], $item['harga'], $item['hpp'], $item['qty']]);
            $stmtStok->execute([$item['qty'], $item['id']]);
            // Ambil saldo saat ini lalu catat ke gudang_log sebagai keluar
            $saldo = $pdo->query("SELECT stok FROM produk WHERE id = " . intval($item['id']))->fetchColumn();
            $noStruk = 'STR-' . str_pad($id_transaksi, 4, '0', STR_PAD_LEFT);
            $keterangan = 'Penjualan ' . $noStruk;
            $stmtInsertGudang->execute([$data['tanggal'], $item['nama'], $item['id'], $item['qty'], $keterangan, $saldo]);
        }

        // Catat otomatis ke Log
        $stmtLog = $pdo->prepare("INSERT INTO log (tanggal, waktu, aksi, detail) VALUES (?, ?, ?, ?)");
        $detailLog = "Total Rp " . number_format($data['total'], 0, ',', '.') . " (" . count($data['items']) . " item)";
        $stmtLog->execute([$data['tanggal'], $data['waktu'], 'Transaksi Kasir', $detailLog]);

        $pdo->commit();
        echo json_encode(["status" => "success"]); exit;
    }

    // Simpan Barang Masuk / Gudang
    if ($action == 'save_gudang' && $method == 'POST') {
        $pdo->beginTransaction();
        try {
            $id_produk = $data['id_produk'];
            $qty = intval($data['qty']);
            $tanggal = $data['tanggal'];
            $tipe = isset($data['tipe']) ? $data['tipe'] : 'masuk';
            $keterangan = isset($data['keterangan']) ? $data['keterangan'] : '';

            $stmtProd = $pdo->prepare("SELECT nama FROM produk WHERE id = ?");
            $stmtProd->execute([$id_produk]);
            $prod = $stmtProd->fetch(PDO::FETCH_ASSOC);
            if (!$prod) throw new Exception('Produk tidak ditemukan');

            if ($tipe === 'masuk') {
                $stmtUpdate = $pdo->prepare("UPDATE produk SET stok = stok + ? WHERE id = ?");
                $stmtUpdate->execute([$qty, $id_produk]);
            } else {
                $stmtUpdate = $pdo->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");
                $stmtUpdate->execute([$qty, $id_produk]);
            }

            $saldo = $pdo->query("SELECT stok FROM produk WHERE id = " . intval($id_produk))->fetchColumn();
            $stmtInsert = $pdo->prepare("INSERT INTO gudang_log (tanggal, produk, id_produk, tipe, qty, keterangan, saldo) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmtInsert->execute([$tanggal, $prod['nama'], $id_produk, $tipe, $qty, $keterangan, $saldo]);

            // tambah ke log umum
            $waktu = date('H:i');
            $aksi = $tipe === 'masuk' ? 'Barang Masuk' : 'Barang Keluar';
            $detail = $prod['nama'] . ' ' . ($tipe === 'masuk' ? '+' : '-') . $qty . ' — ' . $keterangan;
            $stmtLog = $pdo->prepare("INSERT INTO log (tanggal, waktu, aksi, detail) VALUES (?, ?, ?, ?)");
            $stmtLog->execute([$tanggal, $waktu, $aksi, $detail]);

            $pdo->commit();
            echo json_encode(['status' => 'success']); exit;
        } catch (Exception $ex) {
            $pdo->rollBack();
            throw $ex;
        }
    }

    // 7. TABUNGAN (Target Pembelian Inventaris)
    if ($action == 'get_tabungan' && $method == 'GET') {
        $tabungan = $pdo->query("SELECT * FROM tabungan ORDER BY tanggal_target ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        // Hitung target harian dan sisa untuk setiap tabungan
        foreach($tabungan as &$t) {
            $tanggal_target = new DateTime($t['tanggal_target']);
            $tanggal_hari_ini = new DateTime();
            $hari_tersisa = $tanggal_target->diff($tanggal_hari_ini)->days;
            
            // Jika sudah melewati tanggal target, hari tersisa = 0
            if ($tanggal_hari_ini > $tanggal_target) {
                $hari_tersisa = 0;
            }
            
            $sisa_tabungan = $t['harga'] - $t['terkumpul'];
            $target_harian = $hari_tersisa > 0 ? round($sisa_tabungan / $hari_tersisa, 2) : $sisa_tabungan;
            $persentase_progress = $t['harga'] > 0 ? round(($t['terkumpul'] / $t['harga']) * 100, 1) : 0;
            
            $t['hari_tersisa'] = $hari_tersisa;
            $t['sisa_tabungan'] = $sisa_tabungan;
            $t['target_harian'] = $target_harian;
            $t['persentase_progress'] = $persentase_progress;
        }
        
        echo json_encode($tabungan); exit;
    }

    if ($action == 'save_tabungan' && $method == 'POST') {
        if (!empty($data['id'])) {
            // Update
            $stmt = $pdo->prepare("UPDATE tabungan SET nama_barang=?, harga=?, tanggal_target=?, terkumpul=? WHERE id=?");
            $stmt->execute([$data['nama_barang'], $data['harga'], $data['tanggal_target'], $data['terkumpul'], $data['id']]);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO tabungan (nama_barang, harga, tanggal_target, terkumpul) VALUES (?, ?, ?, ?)");
            $stmt->execute([$data['nama_barang'], $data['harga'], $data['tanggal_target'], $data['terkumpul'] ?? 0]);
        }
        echo json_encode(["status" => "success"]); exit;
    }

    if ($action == 'delete_tabungan' && $method == 'POST') {
        $pdo->prepare("DELETE FROM tabungan WHERE id=?")->execute([$data['id']]);
        echo json_encode(["status" => "success"]); exit;
    }

    if ($action == 'update_terkumpul_tabungan' && $method == 'POST') {
        $stmt = $pdo->prepare("UPDATE tabungan SET terkumpul=? WHERE id=?");
        $stmt->execute([$data['terkumpul'], $data['id']]);
        echo json_encode(["status" => "success"]); exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack(); // Batalkan semua proses jika ada query yang gagal
    }
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>