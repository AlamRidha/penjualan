<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/Auth.php';
require_once '../config/Database.php';

if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$startDate = $_GET['start'] ?? date('Y-m-01');
$endDate = $_GET['end'] ?? date('Y-m-d');

$conn = (new Database())->getConnection();

$sql = "SELECT 
        o.id_order,
        u.username,
        o.tanggal_order,
        SUM(od.subtotal) AS total_harga
    FROM orders o
    JOIN users u ON o.id_user = u.id_user
    JOIN order_details od ON o.id_order = od.id_order
    WHERE o.status = 'selesai'
      AND DATE(o.tanggal_order) BETWEEN ? AND ?
    GROUP BY o.id_order
    ORDER BY o.tanggal_order DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $row['tanggal_order'] = date('d-m-Y', strtotime($row['tanggal_order']));
    $row['total_harga'] = 'Rp' . number_format($row['total_harga'], 0, ',', '.');
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode(['data' => $data]);
exit;
