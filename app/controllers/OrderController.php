<?php
require_once base_path('app/config/Database.php');

$db = new Database();
$conn = $db->getConnection();

// Ambil semua order, bisa ditambahkan keyword kalau mau cari by status atau username
function getAllOrders($keyword = null)
{
    global $conn;
    $query = "SELECT o.*, u.username 
              FROM orders o 
              JOIN users u ON o.id_user = u.id_user";

    if ($keyword) {
        $escaped = mysqli_real_escape_string($conn, $keyword);
        $query .= " WHERE u.username LIKE '%$escaped%' OR o.status LIKE '%$escaped%'";
    }

    $query .= " ORDER BY o.id_order DESC";

    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

// Tambah order baru
function createOrder($id_user)
{
    global $conn;
    $tanggal_order = date('Y-m-d');
    $status = 'pending';

    $query = "INSERT INTO orders (id_user, tanggal_order, status) 
              VALUES ('$id_user', '$tanggal_order', '$status')";

    return mysqli_query($conn, $query);
}

// Update status order
function updateOrderStatus($id_order, $status)
{
    global $conn;
    $id_order = mysqli_real_escape_string($conn, $id_order);
    $status = mysqli_real_escape_string($conn, $status);

    $query = "UPDATE orders SET status = '$status' WHERE id_order = '$id_order'";
    return mysqli_query($conn, $query);
}

// Ambil detail order by ID
function getOrderById($id_order)
{
    global $conn;
    $id_order = mysqli_real_escape_string($conn, $id_order);

    // $query = "SELECT o.*, u.username 
    //           FROM orders o 
    //           JOIN users u ON o.id_user = u.id_user
    //           WHERE o.id_order = '$id_order' LIMIT 1";

    $query = "SELECT  o.*, 
                    u.username,
                    IFNULL(pay.bukti_transfer,'')   AS bukti_transfer,
                    IFNULL(pay.metode,'')           AS metode_bayar,
                    pay.tanggal_bayar
            FROM orders o
            JOIN users u          ON u.id_user  = o.id_user
            LEFT JOIN payments pay ON pay.id_order = o.id_order
            WHERE o.id_order = '$id_order'
            LIMIT 1";

    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

// Hapus order (opsional, jika dibutuhkan)
function deleteOrder($id_order)
{
    global $conn;
    $id_order = mysqli_real_escape_string($conn, $id_order);

    $query = "DELETE FROM orders WHERE id_order = '$id_order'";
    return mysqli_query($conn, $query);
}

function getOrderDetails($id_order)
{
    global $conn;
    $id_order = mysqli_real_escape_string($conn, $id_order);

    $query = "SELECT
        od.id_detail,
        od.id_order,
        od.id_product,
        od.jumlah,
        od.subtotal,
        p.nama_produk,
        p.harga
    FROM order_details od
    JOIN products p ON od.id_product = p.id_product
    WHERE od.id_order = '$id_order'";


    $result = mysqli_query($conn, $query);
    $details = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $details[] = $row;
    }
    return $details;
}


// function getOrderDetails($id_order)
// {
// global $conn;
// $id_order = mysqli_real_escape_string($conn, $id_order);

// $query = "SELECT od.*, p.nama_product, p.harga
// FROM order_details od
// JOIN products p ON od.id_product = p.id_product
// WHERE od.id_order = '$id_order'";

// $result = mysqli_query($conn, $query);
// $details = [];
// while ($row = mysqli_fetch_assoc($result)) {
// $details[] = $row;
// }
// return $details;
// }