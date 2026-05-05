<?php
/**
 * Script untuk Inisialisasi Database KASIR
 * Jalankan di browser: http://localhost/kasir/init-db.php
 */

// Konfigurasi Database
$host = 'localhost';
$dbname = 'kasir';
$username = 'root';
$password = '';

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head><meta charset='UTF-8'><title>Database Setup</title>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} pre{background:#f0f0f0;padding:10px;border-radius:5px;}</style>";
echo "</head><body>";

echo "<h1>🔧 KASIR Database Initialization</h1>";

// Step 1: Test Connection
echo "<h2>Step 1: Cek Koneksi MySQL</h2>";
try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    echo "<p class='success'>✓ Koneksi MySQL berhasil!</p>";
} catch (PDOException $e) {
    echo "<p class='error'>✗ Gagal terhubung ke MySQL: " . $e->getMessage() . "</p>";
    echo "<p class='info'>Pastikan MySQL sudah running. Di XAMPP, klik 'Start' pada MySQL.</p>";
    exit;
}

// Step 2: Create Database
echo "<h2>Step 2: Buat Database</h2>";
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo->exec("USE $dbname");
    echo "<p class='success'>✓ Database '$dbname' berhasil dibuat/sudah ada!</p>";
} catch (PDOException $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
    exit;
}

// Step 3: Import SQL
echo "<h2>Step 3: Import Tabel dari database.sql</h2>";
try {
    $sqlFile = __DIR__ . '/database.sql';
    if (!file_exists($sqlFile)) {
        echo "<p class='error'>✗ File database.sql tidak ditemukan!</p>";
        exit;
    }
    
    $sql = file_get_contents($sqlFile);
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $success = 0;
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
            $success++;
        }
    }
    
    echo "<p class='success'>✓ $success SQL statement berhasil dijalankan!</p>";
} catch (PDOException $e) {
    echo "<p class='error'>✗ Error saat import: " . $e->getMessage() . "</p>";
    exit;
}

// Step 4: Verify Tables
echo "<h2>Step 4: Verifikasi Tabel</h2>";
try {
    $requiredTables = ['produk', 'supplier', 'hutang', 'transaksi', 'transaksi_item', 'log', 'gudang_log', 'tabungan'];
    $result = $pdo->query("SHOW TABLES");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    
    $missing = array_diff($requiredTables, $tables);
    
    if (empty($missing)) {
        echo "<p class='success'>✓ Semua tabel ada!</p>";
        echo "<h3>Tabel yang berhasil dibuat:</h3>";
        echo "<ul>";
        foreach ($requiredTables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='error'>✗ Tabel yang hilang: " . implode(', ', $missing) . "</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}

// Step 5: Check Data
echo "<h2>Step 5: Verifikasi Data Sample</h2>";
try {
    $counts = [];
    foreach ($requiredTables as $table) {
        $res = $pdo->query("SELECT COUNT(*) FROM $table");
        $counts[$table] = $res->fetchColumn();
    }
    
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Tabel</th><th>Jumlah Record</th></tr>";
    foreach ($counts as $table => $count) {
        echo "<tr><td>$table</td><td>$count</td></tr>";
    }
    echo "</table>";
} catch (PDOException $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}

// Final Status
echo "<h2 style='margin-top:30px;'>✓ Setup Selesai!</h2>";
echo "<p class='success' style='font-size:16px;'>";
echo "Database sudah siap. Buka aplikasi di: <strong>http://localhost/kasir/index.php</strong>";
echo "</p>";

echo "</body></html>";
?>
