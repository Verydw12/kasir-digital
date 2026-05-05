<?php
header('Content-Type: application/json');
require 'koneksi.php';

// Cek apakah tabel sudah ada
try {
    $tables = ['produk', 'supplier', 'hutang', 'transaksi', 'transaksi_item', 'log', 'gudang_log', 'tabungan'];
    $missing = [];
    
    foreach ($tables as $table) {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() === 0) {
            $missing[] = $table;
        }
    }
    
    if (count($missing) > 0) {
        // Setup database
        $sql = file_get_contents('database.sql');
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Database setup berhasil']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Database sudah setup']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
