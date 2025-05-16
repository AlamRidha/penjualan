<?php
require_once '../config/Database.php';

$database = new Database();
$conn = $database->getConnection();

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Query untuk membuat tabel users
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id_user INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    Role ENUM('admin','customer') NOT NULL
)";

// Query untuk membuat tabel categories
$sql_categories = "CREATE TABLE IF NOT EXISTS categories (
    id_category INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_category VARCHAR(100) NOT NULL
)";

// Query untuk membuat tabel products
$sql_products = "CREATE TABLE IF NOT EXISTS products (
    id_product INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_category INT(11) UNSIGNED NOT NULL,
    nama_produk VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    harga DECIMAL(10,2) NOT NULL,
    stok INT(11) NOT NULL DEFAULT 0,
    gambar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_category) REFERENCES categories(id_category) ON DELETE CASCADE ON UPDATE CASCADE
)";

// Query untuk membuat tabel orders
$sql_orders = "CREATE TABLE IF NOT EXISTS orders (
    id_order INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_user INT(11) UNSIGNED NOT NULL,
    tanggal_order DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'diproses', 'selesai') NOT NULL DEFAULT 'pending',
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE ON UPDATE CASCADE
)";

// Query untuk membuat tabel order_details
$sql_order_details = "CREATE TABLE IF NOT EXISTS order_details (
    id_detail INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_order INT(11) UNSIGNED NOT NULL,
    id_product INT(11) UNSIGNED NOT NULL,
    jumlah INT(11) NOT NULL DEFAULT 1,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_order) REFERENCES orders(id_order) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_product) REFERENCES products(id_product) ON DELETE CASCADE ON UPDATE CASCADE
)";

// Query untuk membuat tabel payments
$sql_payments = "CREATE TABLE IF NOT EXISTS payments (
    id_payment INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_order INT(11) UNSIGNED NOT NULL,
    metode VARCHAR(50) NOT NULL,
    bukti_transfer VARCHAR(255),
    tanggal_bayar DATETIME,
    FOREIGN KEY (id_order) REFERENCES orders(id_order) ON DELETE CASCADE ON UPDATE CASCADE
)";

$queries = [
    'users' => $sql_users,
    'categories' => $sql_categories,
    'products' => $sql_products,
    'orders' => $sql_orders,
    'order_details' => $sql_order_details,
    'payments' => $sql_payments
];

foreach ($queries as $tableName => $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Tabel <b>" . htmlspecialchars($tableName) . "</b> berhasil dibuat.<br>";
    } else {
        echo "Error membuat tabel <b>" . htmlspecialchars($tableName) . "</b>: " . $conn->error . "<br>";
    }
}

$conn->close();
