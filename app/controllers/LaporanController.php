<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$aksi = $_GET['aksi'] ?? '';

switch ($aksi) {
    case 'laporan_pembelian':
        laporanPembelian($conn);
        break;
    default:
        echo json_encode(['error' => 'Aksi tidak dikenal']);
        break;
}

function laporanPembelian($conn)
{
    header('Content-Type: application/json');

    $start_date = $_GET['start_date'] ?? '';
    $end_date = $_GET['end_date'] ?? '';

    try {
        // Query untuk laporan pembelian selesai
        $query = "SELECT p.id_pembelian, p.tanggal_pembelian, 
                 pl.nama_pelanggan, pl.telephone_pelanggan as no_hp,
                 p.total_pembelian, p.nama_kota, p.tarif, p.resi_pengiriman,
                 GROUP_CONCAT(pp.nama_produk SEPARATOR ', ') as produk
                 FROM pembelian p
                 JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
                 JOIN pembelian_produk pp ON p.id_pembelian = pp.id_pembelian
                 WHERE p.status_pembelian = 'selesai'";

        // Filter tanggal
        if (!empty($start_date) && !empty($end_date)) {
            $query .= " AND DATE(p.tanggal_pembelian) BETWEEN ? AND ?";
        }

        $query .= " GROUP BY p.id_pembelian ORDER BY p.tanggal_pembelian DESC";

        $stmt = $conn->prepare($query);

        if (!empty($start_date) && !empty($end_date)) {
            $stmt->bind_param("ss", $start_date, $end_date);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        $total_keuntungan = 0;

        while ($row = $result->fetch_assoc()) {
            $row['tanggal'] = date('d/m/Y H:i', strtotime($row['tanggal_pembelian']));
            $row['total'] = 'Rp' . number_format($row['total_pembelian'], 0, ',', '.');
            $row['ongkir'] = 'Rp' . number_format($row['tarif'], 0, ',', '.');

            $data[] = $row;
            $total_keuntungan += $row['total_pembelian'];
        }

        echo json_encode([
            'success' => true,
            'data' => $data,
            'total_keuntungan' => 'Rp' . number_format($total_keuntungan, 0, ',', '.'),
            'start_date' => $start_date,
            'end_date' => $end_date
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}
