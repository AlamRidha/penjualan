<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$aksi = $_GET['aksi'] ?? '';

switch ($aksi) {
    case 'laporan_pembelian':
        laporanPembelian($conn);
        break;
    case 'cetak_laporan':
        cetakLaporan($conn);
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

function cetakLaporan($conn)
{
    $start_date = $_GET['start_date'] ?? '';
    $end_date = $_GET['end_date'] ?? '';

    // Query yang sama dengan laporanPembelian tapi untuk output cetak
    $query = "SELECT p.id_pembelian, p.tanggal_pembelian, 
             pl.nama_pelanggan, pl.telephone_pelanggan as no_hp,
             p.total_pembelian, p.nama_kota, p.tarif, p.resi_pengiriman,
             GROUP_CONCAT(pp.nama_produk SEPARATOR ', ') as produk
             FROM pembelian p
             JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
             JOIN pembelian_produk pp ON p.id_pembelian = pp.id_pembelian
             WHERE p.status_pembelian = 'selesai'";

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

    // Mulai output HTML untuk cetak
    header("Content-Type: text/html");
?>
    <!DOCTYPE html>
    <html>

    <head>
        <title>Laporan Pembelian Selesai</title>
        <style>
            body {
                font-family: Arial, sans-serif;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th,
            td {
                border: 1px solid #ddd;
                padding: 8px;
            }

            th {
                background-color: #f2f2f2;
                text-align: left;
            }

            .text-right {
                text-align: right;
            }

            .text-center {
                text-align: center;
            }

            .font-bold {
                font-weight: bold;
            }
        </style>
    </head>

    <body>
        <h2 class="text-center">Laporan Pembelian Selesai</h2>
        <?php if (!empty($start_date) && !empty($end_date)): ?>
            <p class="text-center">
                Periode: <?= date('d/m/Y', strtotime($start_date)) ?> - <?= date('d/m/Y', strtotime($end_date)) ?>
            </p>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>ID Pembelian</th>
                    <th>Tanggal</th>
                    <th>Pelanggan</th>
                    <th>No HP</th>
                    <th>Produk</th>
                    <th>Kota Tujuan</th>
                    <th>Ongkir</th>
                    <th>Total</th>
                    <th>Resi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                $total_keuntungan = 0;
                while ($row = $result->fetch_assoc()):
                    $total_keuntungan += $row['total_pembelian'];
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $row['id_pembelian'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($row['tanggal_pembelian'])) ?></td>
                        <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                        <td><?= htmlspecialchars($row['no_hp']) ?></td>
                        <td><?= htmlspecialchars($row['produk']) ?></td>
                        <td><?= htmlspecialchars($row['nama_kota']) ?></td>
                        <td class="text-right">Rp<?= number_format($row['tarif'], 0, ',', '.') ?></td>
                        <td class="text-right">Rp<?= number_format($row['total_pembelian'], 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars($row['resi_pengiriman'] ?? '-') ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr class="font-bold">
                    <td colspan="7">Total Keuntungan</td>
                    <td colspan="3" class="text-right">
                        Rp<?= number_format($total_keuntungan, 0, ',', '.') ?>
                    </td>
                </tr>
            </tfoot>
        </table>

        <script>
            window.onload = function() {
                window.print();
            }
        </script>
    </body>

    </html>
<?php
    exit;
}
