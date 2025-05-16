<?php
require_once base_path('app/config/Database.php');

// Function untuk mendapatkan semua kategori
function getAllCategories()
{
    $database = new Database();
    $conn = $database->getConnection();

    $sql = "SELECT * FROM categories ORDER BY nama_category ASC";
    $result = $conn->query($sql);

    $categories = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }

    return $categories;
}

// Function untuk mendapatkan satu kategori berdasarkan ID
function getCategoryById($id_category)
{
    $database = new Database();
    $conn = $database->getConnection();

    $id_category = $conn->real_escape_string($id_category);
    $sql = "SELECT * FROM categories WHERE id_category = '$id_category'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return null;
}

// Function untuk membuat kategori baru
function createCategory($data)
{
    $database = new Database();
    $conn = $database->getConnection();

    $nama_category = $conn->real_escape_string($data['nama_category']);

    $sql = "INSERT INTO categories (nama_category) VALUES ('$nama_category')";

    if ($conn->query($sql)) {
        return true;
    }

    return false;
}

// Function untuk memperbarui kategori
function updateCategory($id_category, $data)
{
    $database = new Database();
    $conn = $database->getConnection();

    $id_category = $conn->real_escape_string($id_category);
    $nama_category = $conn->real_escape_string($data['nama_category']);

    $sql = "UPDATE categories SET nama_category = '$nama_category' WHERE id_category = '$id_category'";

    if ($conn->query($sql)) {
        return true;
    }

    return false;
}

// Function untuk menghapus kategori
function deleteCategory($id_category)
{
    $database = new Database();
    $conn = $database->getConnection();

    // Cek apakah kategori digunakan di tabel produk
    $id_category = $conn->real_escape_string($id_category);
    $check_sql = "SELECT COUNT(*) as count FROM products WHERE id_category = '$id_category'";
    $check_result = $conn->query($check_sql);
    $check_data = $check_result->fetch_assoc();

    if ($check_data['count'] > 0) {
        // Kategori masih digunakan oleh produk
        return false;
    }

    $sql = "DELETE FROM categories WHERE id_category = '$id_category'";

    if ($conn->query($sql)) {
        return true;
    }

    return false;
}
