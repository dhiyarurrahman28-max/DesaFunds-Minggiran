<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'desafunds_minggiran';

// Variabel global untuk koneksi database
global $pdo;

// Cek koneksi database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Test koneksi
    $pdo->query("SELECT 1");
    
} catch(PDOException $e) {
    // Jika database tidak ada, buat database
    if ($e->getCode() == 1049) {
        try {
            $pdo_temp = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
            $pdo_temp->exec("CREATE DATABASE IF NOT EXISTS $database");
            $pdo_temp->exec("USE $database");
            $pdo = $pdo_temp;
            
            // Buat tabel secara manual
            createDatabaseTables($pdo);
            
        } catch(PDOException $e2) {
            die("Gagal membuat database: " . $e2->getMessage());
        }
    } else {
        die("Koneksi database gagal: " . $e->getMessage());
    }
}

// Fungsi untuk membuat tabel jika file SQL tidak ada
function createDatabaseTables($pdo) {
    try {
        // Tabel admin
        $pdo->exec("CREATE TABLE IF NOT EXISTS admin (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            nama_lengkap VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Tabel dusun (Desa Minggiran)
        $pdo->exec("CREATE TABLE IF NOT EXISTS dusun (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama_dusun VARCHAR(100) NOT NULL UNIQUE
        )");
        
        // Tabel kategori forum
        $pdo->exec("CREATE TABLE IF NOT EXISTS kategori_forum (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama_kategori VARCHAR(100) NOT NULL UNIQUE
        )");
        
        // Tabel forum posts
        $pdo->exec("CREATE TABLE IF NOT EXISTS forum_posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            dusun_id INT NOT NULL,
            kategori_id INT NOT NULL,
            nama VARCHAR(100) NOT NULL,
            pesan TEXT NOT NULL,
            admin_reply TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_admin_post BOOLEAN DEFAULT FALSE
        )");
        
        // Tabel APBDes
        $pdo->exec("CREATE TABLE IF NOT EXISTS apbdes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tahun YEAR NOT NULL,
            total_anggaran DECIMAL(15,2) NOT NULL,
            total_pemasukan DECIMAL(15,2) NOT NULL,
            total_pengeluaran DECIMAL(15,2) NOT NULL,
            sisa_dana DECIMAL(15,2) NOT NULL
        )");
        
        // Tabel jenis pemasukan
        $pdo->exec("CREATE TABLE IF NOT EXISTS jenis_pemasukan (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama_jenis VARCHAR(100) NOT NULL UNIQUE
        )");
        
        // Tabel pemasukan
        $pdo->exec("CREATE TABLE IF NOT EXISTS pemasukan (
            id INT AUTO_INCREMENT PRIMARY KEY,
            apbdes_id INT NOT NULL,
            jenis_pemasukan_id INT NOT NULL,
            jumlah DECIMAL(15,2) NOT NULL,
            keterangan TEXT,
            tanggal DATE NOT NULL
        )");
        
        // Tabel jenis pengeluaran
        $pdo->exec("CREATE TABLE IF NOT EXISTS jenis_pengeluaran (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama_jenis VARCHAR(100) NOT NULL UNIQUE
        )");
        
        // Tabel pengeluaran
        $pdo->exec("CREATE TABLE IF NOT EXISTS pengeluaran (
            id INT AUTO_INCREMENT PRIMARY KEY,
            apbdes_id INT NOT NULL,
            jenis_pengeluaran_id INT NOT NULL,
            nama_kegiatan VARCHAR(255) NOT NULL,
            anggaran DECIMAL(15,2) NOT NULL,
            realisasi DECIMAL(15,2) NOT NULL DEFAULT 0,
            sisa_anggaran DECIMAL(15,2) NOT NULL,
            keterangan TEXT,
            tanggal DATE NOT NULL
        )");
        
        // Tabel kegiatan
        $pdo->exec("CREATE TABLE IF NOT EXISTS kegiatan (
            id INT AUTO_INCREMENT PRIMARY KEY,
            pengeluaran_id INT NOT NULL,
            nama_kegiatan VARCHAR(255) NOT NULL,
            deskripsi TEXT NOT NULL,
            lokasi VARCHAR(255) NOT NULL,
            tanggal_mulai DATE NOT NULL,
            tanggal_selesai DATE NULL,
            anggaran DECIMAL(15,2) NOT NULL,
            realisasi DECIMAL(15,2) NOT NULL DEFAULT 0,
            sisa_anggaran DECIMAL(15,2) NOT NULL,
            status ENUM('rencana', 'berjalan', 'selesai') DEFAULT 'rencana',
            dokumentasi TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Tabel detail penggunaan dana kegiatan
        $pdo->exec("CREATE TABLE IF NOT EXISTS detail_penggunaan_kegiatan (
            id INT AUTO_INCREMENT PRIMARY KEY,
            kegiatan_id INT NOT NULL,
            item VARCHAR(255) NOT NULL,
            jumlah DECIMAL(15,2) NOT NULL,
            satuan VARCHAR(50) NOT NULL,
            harga_satuan DECIMAL(15,2) NOT NULL,
            total DECIMAL(15,2) NOT NULL,
            keterangan TEXT
        )");
        
        // Insert data default
        $pdo->exec("INSERT INTO admin (username, password, nama_lengkap) VALUES 
            ('admin', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator Desa Minggiran')");
        
        // Dusun Desa Minggiran
        $pdo->exec("INSERT INTO dusun (nama_dusun) VALUES 
            ('Minggiran'), ('Moranggan'), ('Rejowinangun')");
        
        $pdo->exec("INSERT INTO kategori_forum (nama_kategori) VALUES 
            ('Infrastruktur'), ('Pendidikan'), ('Kesehatan'), ('Kesejahteraan Ekonomi'), ('Lainnya')");
        
        $pdo->exec("INSERT INTO jenis_pemasukan (nama_jenis) VALUES 
            ('Dana Desa'), ('Bagi Hasil Pajak'), ('Bantuan Provinsi'), ('Bantuan Kabupaten'), ('Pendapatan Asli Desa'), ('Hibah')");
        
        $pdo->exec("INSERT INTO jenis_pengeluaran (nama_jenis) VALUES 
            ('Pembangunan Infrastruktur'), ('Pemberdayaan Masyarakat'), ('Pelayanan Publik'), ('Administrasi Desa'), ('Bantuan Sosial'), ('Lainnya')");
        
        // APBDes untuk beberapa tahun
        $pdo->exec("INSERT INTO apbdes (tahun, total_anggaran, total_pemasukan, total_pengeluaran, sisa_dana) VALUES 
            (2024, 750000000.00, 720000000.00, 600000000.00, 120000000.00),
            (2025, 850000000.00, 820000000.00, 650000000.00, 170000000.00)");
        
        // Insert data pemasukan 2025
        $pdo->exec("INSERT INTO pemasukan (apbdes_id, jenis_pemasukan_id, jumlah, keterangan, tanggal) VALUES 
            (2, 1, 720000000.00, 'Dana Desa dari Pemerintah Pusat', '2025-01-15'),
            (2, 2, 50000000.00, 'Bagi Hasil Pajak Kabupaten', '2025-02-10'),
            (2, 3, 30000000.00, 'Bantuan Provinsi Jawa Timur', '2025-03-05'),
            (2, 5, 20000000.00, 'Hasil BUMDes', '2025-04-20')");
        
        // Insert data pengeluaran 2025
        $pdo->exec("INSERT INTO pengeluaran (apbdes_id, jenis_pengeluaran_id, nama_kegiatan, anggaran, realisasi, sisa_anggaran, keterangan, tanggal) VALUES 
            (2, 1, 'Perbaikan Jalan Dusun Minggiran', 120000000.00, 95000000.00, 25000000.00, 'Perbaikan jalan sepanjang 2 km', '2025-01-20'),
            (2, 1, 'Pembangunan Balai Desa', 200000000.00, 150000000.00, 50000000.00, 'Pembangunan balai desa 2 lantai', '2025-02-15'),
            (2, 2, 'Pelatihan UMKM', 50000000.00, 35000000.00, 15000000.00, 'Pelatihan untuk 50 pelaku UMKM', '2025-03-10')");
        
        // Insert data kegiatan
        $pdo->exec("INSERT INTO kegiatan (pengeluaran_id, nama_kegiatan, deskripsi, lokasi, tanggal_mulai, tanggal_selesai, anggaran, realisasi, sisa_anggaran, status, dokumentasi) VALUES 
            (1, 'Perbaikan Jalan Dusun Minggiran', 'Pengerasan dan pengaspalan jalan sepanjang 2 km di Dusun Minggiran yang rusak akibat hujan', 'Dusun Minggiran', '2025-01-25', '2025-03-15', 120000000.00, 95000000.00, 25000000.00, 'selesai', 'foto_jalan_sebelum.jpg,foto_jalan_sesudah.jpg'),
            (2, 'Pembangunan Balai Desa', 'Pembangunan balai desa 2 lantai dengan fasilitas ruang pertemuan, perpustakaan, dan ruang administrasi', 'Lingkungan Balai Desa', '2025-02-20', '2025-06-30', 200000000.00, 150000000.00, 50000000.00, 'berjalan', 'foto_balai_desa.jpg')");
        
        // Insert forum posts contoh
        $pdo->exec("INSERT INTO forum_posts (dusun_id, kategori_id, nama, pesan, admin_reply, created_at) VALUES 
            (1, 1, 'Budi Santoso', 'Mohon perbaikan jalan di depan balai desa, banyak lubang yang membahayakan pengendara', 'Terima kasih atas masukannya Pak Budi. Sudah kami catat dan akan kami prioritaskan dalam rencana perbaikan jalan triwulan depan.', '2025-01-10 08:30:00'),
            (2, 4, 'Siti Rahayu', 'Apakah ada program bantuan modal UMKM untuk ibu-ibu di Dusun Moranggan?', 'Iya Bu Siti, ada program bantuan modal UMKM. Silakan datang ke kantor desa dengan membawa proposal usaha untuk pengajuan.', '2025-01-15 14:20:00')");
        
        echo "<!-- Database dan tabel berhasil dibuat otomatis -->";
        
    } catch(PDOException $e) {
        die("Gagal membuat tabel: " . $e->getMessage());
    }
}

// Fungsi untuk mencegah SQL injection
function clean_input($data) {
    if (!isset($data)) return '';
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Cek login admin
$is_admin_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Fungsi untuk menentukan kelas avatar
function getAvatarClass($name) {
    if (empty($name)) return 'avatar-budi';
   
    $firstChar = strtoupper(substr($name, 0, 1));
    $avatarMap = [
        'B' => 'avatar-budi',
        'A' => 'avatar-ani',
        'R' => 'avatar-rina',
        'J' => 'avatar-johan',
        'H' => 'avatar-harji',
        'S' => 'avatar-siti',
        'M' => 'avatar-maya'
    ];
   
    return $avatarMap[$firstChar] ?? 'avatar-budi';
}

// Pastikan $pdo tersedia secara global
if (!isset($pdo)) {
    die("Error: Koneksi database tidak tersedia. Periksa konfigurasi database.");
}
?>