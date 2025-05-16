<?php
require_once '../config/Database.php';

$db = new Database();
$conn = $db->getConnection();

$id_order = $_GET['id_order'];
$id_order = mysqli_real_escape_string($conn, $id_order);

$query = "
    SELECT p.nama_produk, od.jumlah, od.subtotal
    FROM order_details od
    JOIN products p ON od.id_product = p.id_product
    WHERE od.id_order = '$id_order'
";

$result = mysqli_query($conn, $query);

$total = 0;

echo "<table class='table table-bordered'>
        <thead >
            <tr>
                <th>Nama Produk</th>
                <th>Jumlah</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>";

while ($row = mysqli_fetch_assoc($result)) {
    $total += $row['subtotal'];
    echo "<tr>
            <td>{$row['nama_produk']}</td>
            <td>{$row['jumlah']}</td>
            <td>Rp " . number_format($row['subtotal'], 0, ',', '.') . "</td>
        </tr>";
}

echo "<tr class='fw-bold'>
        <td colspan='2' class='text-end'>Total Bayar</td>
        <td>Rp " . number_format($total, 0, ',', '.') . "</td>
    </tr>";

echo "</tbody></table>";
