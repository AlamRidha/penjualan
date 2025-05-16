<?php
require_once base_path('app/config/Database.php');

$db = new Database();
$conn = $db->getConnection();

function getAllProducts($keyword = null)
{
    global $conn;
    // Updated query to include category name
    $query = "SELECT p.*, c.nama_category 
              FROM products p
              LEFT JOIN categories c ON p.id_category = c.id_category";

    if ($keyword) {
        $query .= " WHERE p.nama_produk LIKE '%" . mysqli_real_escape_string($conn, $keyword) . "%'";
    }
    $result = mysqli_query($conn, $query);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

function createProduk($data, $file)
{
    global $conn;
    $nama = mysqli_real_escape_string($conn, $data['nama_produk']);
    $deskripsi = mysqli_real_escape_string($conn, $data['deskripsi']);
    $harga = mysqli_real_escape_string($conn, $data['harga']);
    $stok = mysqli_real_escape_string($conn, $data['stok']);
    $id_category = mysqli_real_escape_string($conn, $data['id_category']);

    $gambar = '';
    if ($file['gambar']['error'] == 0) {
        $gambar = 'uploads/produk/' . time() . '_' . basename($file['gambar']['name']);
        move_uploaded_file($file['gambar']['tmp_name'], base_path($gambar));
    }

    // Updated query to include id_category
    $query = "INSERT INTO products (nama_produk, deskripsi, harga, stok, gambar, id_category) 
              VALUES ('$nama', '$deskripsi', '$harga', '$stok', '$gambar', '$id_category')";
    return mysqli_query($conn, $query);
}

function updateProduk($id, $data, $file)
{
    global $conn;
    $id = mysqli_real_escape_string($conn, $id); // Escape id
    $nama = mysqli_real_escape_string($conn, $data['nama_produk']);
    $deskripsi = mysqli_real_escape_string($conn, $data['deskripsi']);
    $harga = mysqli_real_escape_string($conn, $data['harga']);
    $stok = mysqli_real_escape_string($conn, $data['stok']);
    $id_category = mysqli_real_escape_string($conn, $data['id_category']);

    // Updated query to include id_category
    $query = "UPDATE products SET nama_produk='$nama', deskripsi='$deskripsi', 
              harga='$harga', stok='$stok', id_category='$id_category'";

    if ($file['gambar']['error'] == 0) {
        // Hapus gambar lama jika ada
        $old_product = getProdukById($id);
        if (!empty($old_product['gambar']) && file_exists(base_path($old_product['gambar']))) {
            unlink(base_path($old_product['gambar']));
        }

        $gambar = 'uploads/produk/' . time() . '_' . basename($file['gambar']['name']);
        move_uploaded_file($file['gambar']['tmp_name'], base_path($gambar));
        $query .= ", gambar='$gambar'";
    }

    $query .= " WHERE id_product=$id";
    return mysqli_query($conn, $query);
}

function deleteProduk($id)
{
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);

    // Hapus file gambar jika ada
    $product = getProdukById($id);
    if (!empty($product['gambar']) && file_exists(base_path($product['gambar']))) {
        unlink(base_path($product['gambar']));
    }

    $query = "DELETE FROM products WHERE id_product=$id";
    return mysqli_query($conn, $query);
}

function getProdukById($id)
{
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    // Updated query to include category information
    $query = "SELECT p.*, c.nama_category 
              FROM products p
              LEFT JOIN categories c ON p.id_category = c.id_category
              WHERE p.id_product=$id LIMIT 1";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}
