<?php
require_once '../config/Database.php';

$database = new Database();
$conn = $database->getConnection();

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// 1. Tabel pelanggan
$sql_pelanggan = "CREATE TABLE IF NOT EXISTS pelanggan (
    id_pelanggan INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email_pelanggan VARCHAR(100) UNIQUE NOT NULL,
    password_pelanggan VARCHAR(255) NOT NULL,
    nama_pelanggan VARCHAR(100) NOT NULL,
    telephone_pelanggan VARCHAR(20),
    alamat_pelanggan TEXT
)";

// 2. Tabel admin
$sql_admin = "CREATE TABLE IF NOT EXISTS admin (
    id_admin INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL
)";

// 3. Tabel produk
$sql_produk = "CREATE TABLE IF NOT EXISTS produk (
    id_produk INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_produk VARCHAR(100) NOT NULL,
    harga_produk DECIMAL(12,2) NOT NULL,
    foto_produk VARCHAR(255),
    deskripsi_produk TEXT,
    stok_produk INT(11) NOT NULL DEFAULT 0
)";

// 4. Tabel ongkir
$sql_ongkir = "CREATE TABLE IF NOT EXISTS ongkir (
    id_ongkir INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_kota VARCHAR(100) NOT NULL,
    tarif DECIMAL(10,2) NOT NULL
)";

// 5. Tabel pembelian
$sql_pembelian = "CREATE TABLE IF NOT EXISTS pembelian (
    id_pembelian INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_pelanggan INT(11) UNSIGNED NOT NULL,
    id_ongkir INT(11) UNSIGNED NOT NULL,
    tanggal_pembelian DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total_pembelian DECIMAL(12,2) NOT NULL,
    nama_kota VARCHAR(100),
    tarif DECIMAL(10,2),
    alamat_pengiriman TEXT,
    status_pembelian ENUM('pending', 'dibayar', 'dikirim', 'selesai', 'batal') DEFAULT 'pending',
    resi_pengiriman VARCHAR(100),
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_ongkir) REFERENCES ongkir(id_ongkir) ON DELETE CASCADE ON UPDATE CASCADE
)";

// 6. Tabel pembayaran
$sql_pembayaran = "CREATE TABLE IF NOT EXISTS pembayaran (
    id_pembayaran INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_pembelian INT(11) UNSIGNED NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    bank VARCHAR(50) NOT NULL,
    jumlah DECIMAL(12,2) NOT NULL,
    tanggal_pembayaran DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    bukti_pembayaran VARCHAR(255),
    FOREIGN KEY (id_pembelian) REFERENCES pembelian(id_pembelian) ON DELETE CASCADE ON UPDATE CASCADE
)";

// 7. Tabel pembelian_produk
$sql_pembelian_produk = "CREATE TABLE IF NOT EXISTS pembelian_produk (
    id_pembelian_produk INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_pembelian INT(11) UNSIGNED NOT NULL,
    id_produk INT(11) UNSIGNED NOT NULL,
    jumlah INT(11) NOT NULL,
    nama_produk VARCHAR(100) NOT NULL,
    harga DECIMAL(12,2) NOT NULL,
    sub_harga DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (id_pembelian) REFERENCES pembelian(id_pembelian) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_produk) REFERENCES produk(id_produk) ON DELETE CASCADE ON UPDATE CASCADE
)";

// Daftar query
$queries = [
    'pelanggan' => $sql_pelanggan,
    'admin' => $sql_admin,
    'produk' => $sql_produk,
    'ongkir' => $sql_ongkir,
    'pembelian' => $sql_pembelian,
    'pembayaran' => $sql_pembayaran,
    'pembelian_produk' => $sql_pembelian_produk
];

// Eksekusi query
foreach ($queries as $tableName => $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Tabel <b>" . htmlspecialchars($tableName) . "</b> berhasil dibuat.<br>";
    } else {
        echo "Error membuat tabel <b>" . htmlspecialchars($tableName) . "</b>: " . $conn->error . "<br>";
    }
}

$conn->close();
