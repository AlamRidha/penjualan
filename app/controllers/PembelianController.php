<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$aksi = $_GET['aksi'] ?? '';

switch ($aksi) {
    case 'tambah':
        tambahPembelian($conn);
        break;
    case 'edit':
        editPembelian($conn);
        break;
    case 'hapus':
        hapusPembelian($conn);
        break;
    case 'list':
        listPembelian($conn);
        break;
    case 'detail':
        getDetailPembelian($conn);
        break;
    case 'update_status':
        updateStatusPembelian($conn);
        break;
    default:
        echo json_encode(['error' => 'Aksi tidak dikenal']);
        break;
}

function validateInput($data)
{
    return htmlspecialchars(trim($data));
}

function tambahPembelian($conn)
{
    header('Content-Type: application/json');

    $id_pelanggan = $_POST['id_pelanggan'] ?? 0;
    $id_ongkir = $_POST['id_ongkir'] ?? 0;
    $total_pembelian = $_POST['total_pembelian'] ?? 0;
    $alamat_pengiriman = validateInput($_POST['alamat_pengiriman'] ?? '');

    try {
        // Dapatkan data ongkir untuk disimpan
        $stmt_ongkir = $conn->prepare("SELECT nama_kota, tarif FROM ongkir WHERE id_ongkir = ?");
        $stmt_ongkir->bind_param("i", $id_ongkir);
        $stmt_ongkir->execute();
        $ongkir = $stmt_ongkir->get_result()->fetch_assoc();

        if (!$ongkir) {
            throw new Exception('Data ongkir tidak ditemukan');
        }

        $query = "INSERT INTO pembelian (
            id_pelanggan, 
            id_ongkir, 
            total_pembelian, 
            nama_kota, 
            tarif, 
            alamat_pengiriman
        ) VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($query);
        $stmt->bind_param(
            "iiddss",
            $id_pelanggan,
            $id_ongkir,
            $total_pembelian,
            $ongkir['nama_kota'],
            $ongkir['tarif'],
            $alamat_pengiriman
        );

        if ($stmt->execute()) {
            $id_pembelian = $conn->insert_id;

            echo json_encode([
                'success' => true,
                'message' => 'Pembelian berhasil dibuat',
                'id_pembelian' => $id_pembelian
            ]);
        } else {
            throw new Exception('Gagal membuat pembelian');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

function editPembelian($conn)
{
    header('Content-Type: application/json');

    $id_pembelian = $_POST['id_pembelian'] ?? 0;
    $id_ongkir = $_POST['id_ongkir'] ?? 0;
    $alamat_pengiriman = validateInput($_POST['alamat_pengiriman'] ?? '');

    try {
        // Jika ongkir diubah, dapatkan data ongkir baru
        if ($id_ongkir) {
            $stmt_ongkir = $conn->prepare("SELECT nama_kota, tarif FROM ongkir WHERE id_ongkir = ?");
            $stmt_ongkir->bind_param("i", $id_ongkir);
            $stmt_ongkir->execute();
            $ongkir = $stmt_ongkir->get_result()->fetch_assoc();

            if (!$ongkir) {
                throw new Exception('Data ongkir tidak ditemukan');
            }

            $query = "UPDATE pembelian SET 
                id_ongkir = ?,
                nama_kota = ?,
                tarif = ?,
                alamat_pengiriman = ?
                WHERE id_pembelian = ?";

            $stmt = $conn->prepare($query);
            $stmt->bind_param(
                "isdss",
                $id_ongkir,
                $ongkir['nama_kota'],
                $ongkir['tarif'],
                $alamat_pengiriman,
                $id_pembelian
            );
        } else {
            $query = "UPDATE pembelian SET 
                alamat_pengiriman = ?
                WHERE id_pembelian = ?";

            $stmt = $conn->prepare($query);
            $stmt->bind_param(
                "si",
                $alamat_pengiriman,
                $id_pembelian
            );
        }

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Pembelian berhasil diperbarui'
            ]);
        } else {
            throw new Exception('Gagal memperbarui pembelian');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

function updateStatusPembelian($conn)
{
    header('Content-Type: application/json');

    $id_pembelian = $_POST['id_pembelian'] ?? 0;
    $status = validateInput($_POST['status'] ?? '');
    $resi = validateInput($_POST['resi'] ?? '');

    try {
        $valid_status = ['pending', 'dibayar', 'dikirim', 'selesai', 'batal'];
        if (!in_array($status, $valid_status)) {
            throw new Exception('Status tidak valid');
        }

        $query = "UPDATE pembelian SET 
            status_pembelian = ?,
            resi_pengiriman = ?
            WHERE id_pembelian = ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $status, $resi, $id_pembelian);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Status pembelian berhasil diperbarui'
            ]);
        } else {
            throw new Exception('Gagal memperbarui status pembelian');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

function hapusPembelian($conn)
{
    header('Content-Type: application/json');

    $id = $_POST['id'] ?? 0;

    try {
        $stmt = $conn->prepare("DELETE FROM pembelian WHERE id_pembelian = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Pembelian berhasil dihapus'
            ]);
        } else {
            throw new Exception('Gagal menghapus pembelian');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

function listPembelian($conn)
{
    header('Content-Type: application/json');

    $columns = [
        'p.id_pembelian',
        'pl.nama_pelanggan',
        'p.tanggal_pembelian',
        'p.total_pembelian',
        'p.status_pembelian'
    ];

    $limit = isset($_GET['length']) ? intval($_GET['length']) : 10;
    $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
    $orderIndex = $_GET['order'][0]['column'] ?? 0;
    $orderDir = $_GET['order'][0]['dir'] ?? 'asc';
    $search = $_GET['search']['value'] ?? '';
    $status = $_GET['status'] ?? '';

    $orderColumn = $columns[$orderIndex] ?? $columns[0];

    // Query dasar dengan join ke tabel pelanggan
    $baseQuery = "SELECT p.*, pl.nama_pelanggan 
                 FROM pembelian p
                 JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan";

    // Filter berdasarkan status jika ada
    $whereClause = "";
    if (!empty($status) && $status != 'all') {
        $whereClause = " WHERE p.status_pembelian = '" . $conn->real_escape_string($status) . "'";
    }

    // Hitung total data
    $totalQuery = $conn->query("SELECT COUNT(*) as total FROM pembelian");
    $totalData = $totalQuery ? $totalQuery->fetch_assoc()['total'] : 0;

    // Query dengan pencarian
    if (!empty($search)) {
        $search = $conn->real_escape_string($search);
        $searchClause = " WHERE (pl.nama_pelanggan LIKE '%$search%' 
                          OR p.id_pembelian LIKE '%$search%'
                          OR p.status_pembelian LIKE '%$search%')";

        if (!empty($whereClause)) {
            $searchClause = str_replace("WHERE", "AND", $searchClause);
            $whereClause .= $searchClause;
        } else {
            $whereClause = $searchClause;
        }

        $filteredQuery = $conn->query("SELECT COUNT(*) as total 
                                      FROM pembelian p
                                      JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
                                      $whereClause");
        $totalFiltered = $filteredQuery ? $filteredQuery->fetch_assoc()['total'] : 0;
    } else {
        $totalFiltered = $totalData;
    }

    // Query akhir dengan pengurutan dan pagination
    $query = "$baseQuery $whereClause ORDER BY $orderColumn $orderDir LIMIT $start, $limit";
    $result = $conn->query($query);
    $data = [];
    $no = $start + 1;

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'no' => $no++,
            'id_pembelian' => $row['id_pembelian'],
            'nama_pelanggan' => htmlspecialchars($row['nama_pelanggan']),
            'tanggal' => date('d/m/Y H:i', strtotime($row['tanggal_pembelian'])),
            'total' => 'Rp' . number_format($row['total_pembelian'], 0, ',', '.'),
            'status' => getStatusBadge($row['status_pembelian']),
            'aksi' => "<button class='btn btn-sm btn-info btn-detail' data-id='{$row['id_pembelian']}'>Detail</button>
                      <button class='btn btn-sm btn-primary btn-edit-status' data-id='{$row['id_pembelian']}'>Ubah Status</button>
                      <button class='btn btn-sm btn-danger btn-hapus' data-id='{$row['id_pembelian']}'>Hapus</button>"
        ];
    }

    echo json_encode([
        "draw" => intval($_GET['draw'] ?? 0),
        "recordsTotal" => intval($totalData),
        "recordsFiltered" => intval($totalFiltered),
        "data" => $data
    ]);
}

function getDetailPembelian($conn)
{
    header('Content-Type: application/json');

    $id = $_GET['id'] ?? 0;

    try {
        // Query untuk data pembelian
        $stmt = $conn->prepare("SELECT p.*, pl.nama_pelanggan, pl.email_pelanggan as email, 
                               pl.telephone_pelanggan as no_hp
                               FROM pembelian p
                               JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
                               WHERE p.id_pembelian = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $pembelian = $stmt->get_result()->fetch_assoc();

        if (!$pembelian) {
            throw new Exception('Data pembelian tidak ditemukan');
        }

        // Ambil detail produk yang dibeli
        $produk = getDetailProdukPembelian($conn, $id);

        // Format data
        $pembelian['tanggal_pembelian'] = date('d/m/Y H:i', strtotime($pembelian['tanggal_pembelian']));
        $pembelian['total_pembelian'] = number_format($pembelian['total_pembelian'], 0, ',', '.');
        $pembelian['tarif'] = number_format($pembelian['tarif'], 0, ',', '.');

        echo json_encode([
            'success' => true,
            'data' => $pembelian,
            'produk' => $produk
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

function getStatusBadge($status)
{
    $badges = [
        'pending' => '<span class="badge bg-warning">Pending</span>',
        'dibayar' => '<span class="badge bg-info">Dibayar</span>',
        'dikirim' => '<span class="badge bg-primary">Dikirim</span>',
        'selesai' => '<span class="badge bg-success">Selesai</span>',
        'batal' => '<span class="badge bg-danger">Batal</span>'
    ];

    return $badges[$status] ?? $status;
}


function getDetailProdukPembelian($conn, $id_pembelian)
{
    $query = "SELECT pp.*, p.foto_produk, p.deskripsi_produk
              FROM pembelian_produk pp
              JOIN produk p ON pp.id_produk = p.id_produk
              WHERE pp.id_pembelian = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_pembelian);
    $stmt->execute();

    $result = $stmt->get_result();
    $produk = [];

    while ($row = $result->fetch_assoc()) {
        $row['harga_formatted'] = 'Rp' . number_format($row['harga'], 0, ',', '.');
        $row['sub_harga_formatted'] = 'Rp' . number_format($row['sub_harga'], 0, ',', '.');
        $produk[] = $row;
    }

    return $produk;
}
