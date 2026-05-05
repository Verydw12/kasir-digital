<?php
/**
 * Script untuk Test API Connection
 * Jalankan di browser: http://localhost/kasir/test-api.php
 */

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head><meta charset='UTF-8'><title>API Test</title>";
echo "<style>
body{font-family:Arial;padding:20px;background:#f5f5f5;}
.test{background:white;padding:15px;margin:10px 0;border-radius:5px;border-left:4px solid #999;}
.success{border-left-color:green;color:green;}
.error{border-left-color:red;color:red;}
.info{border-left-color:blue;color:blue;}
pre{background:#f0f0f0;padding:10px;border-radius:3px;overflow-x:auto;}
h1{color:#333;}
</style>";
echo "</head><body>";

echo "<h1>🧪 API Connection Test</h1>";

// Test 1: Database Connection
echo "<div class='test info'><strong>Test 1: Database Connection</strong>";
try {
    require 'koneksi.php';
    $result = $pdo->query("SELECT 1");
    echo "<p class='success'>✓ Database connected successfully!</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    echo "</div>";
    exit;
}
echo "</div>";

// Test 2: Fetch Products
echo "<div class='test info'><strong>Test 2: Fetch Produk (GET /api.php?action=init)</strong>";
try {
    $produk = $pdo->query("SELECT * FROM produk LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
    echo "<p class='success'>✓ Found " . count($produk) . " products</p>";
    echo "<pre>" . json_encode($produk, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 3: Fetch Tabungan
echo "<div class='test info'><strong>Test 3: Fetch Tabungan (GET /api.php?action=get_tabungan)</strong>";
try {
    $tabungan = $pdo->query("SELECT * FROM tabungan")->fetchAll(PDO::FETCH_ASSOC);
    echo "<p class='success'>✓ Found " . count($tabungan) . " tabungan records</p>";
    echo "<pre>" . json_encode($tabungan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 4: Fetch Gudang Log
echo "<div class='test info'><strong>Test 4: Fetch Gudang Log (GET /api.php?action=init)</strong>";
try {
    $gudang = $pdo->query("SELECT * FROM gudang_log LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
    echo "<p class='success'>✓ Found " . count($gudang) . " gudang_log records</p>";
    echo "<pre>" . json_encode($gudang, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 5: All Tables
echo "<div class='test info'><strong>Test 5: Table Count Summary</strong>";
try {
    $tables = ['produk', 'supplier', 'hutang', 'transaksi', 'transaksi_item', 'log', 'gudang_log', 'tabungan'];
    echo "<table border='1' cellpadding='10' style='border-collapse:collapse;'>";
    echo "<tr style='background:#f0f0f0;'><th>Tabel</th><th>Records</th></tr>";
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "<tr><td>$table</td><td>$count</td></tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

echo "<h2 style='margin-top:30px;'>Next Steps</h2>";
echo "<ol>";
echo "<li>Jika semua test ✓ berhasil, buka: <strong><a href='index.php'>index.php</a></strong></li>";
echo "<li>Jika ada ✗ error, pastikan:<br>";
echo "   - MySQL sudah running<br>";
echo "   - Database 'kasir' sudah dibuat<br>";
echo "   - Run <strong><a href='init-db.php'>init-db.php</a></strong> untuk setup";
echo "</li>";
echo "</ol>";

echo "</body></html>";
?>
