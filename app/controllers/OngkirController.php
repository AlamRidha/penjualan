<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$aksi = $_GET['aksi'] ?? '';

switch ($aksi) {
    case 'tambah':
        tambahOngkir($conn);
        break;
    case 'edit':
        editOngkir($conn);
        break;
    case 'hapus':
        hapusOngkir($conn);
        break;
    case 'list':
        listOngkir($conn);
        break;
    case 'get':
        getOngkirById($conn);
        break;
    default:
        echo json_encode(['error' => 'Aksi tidak dikenal']);
        break;
}

function validateInput($data)
{
    return htmlspecialchars(trim($data));
}

function tambahOngkir($conn)
{
    header('Content-Type: application/json');

    $nama_kota = validateInput($_POST['nama_kota'] ?? '');
    $tarif = validateInput($_POST['tarif'] ?? 0);

    try {
        $query = "INSERT INTO ongkir (nama_kota, tarif) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sd", $nama_kota, $tarif);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Data ongkir berhasil ditambahkan'
            ]);
        } else {
            throw new Exception('Gagal menambahkan data ongkir');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

function editOngkir($conn)
{
    header('Content-Type: application/json');

    $id = $_POST['id_ongkir'] ?? 0;
    $nama_kota = validateInput($_POST['nama_kota'] ?? '');
    $tarif = validateInput($_POST['tarif'] ?? 0);

    try {
        $query = "UPDATE ongkir SET nama_kota = ?, tarif = ? WHERE id_ongkir = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sdi", $nama_kota, $tarif, $id);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Data ongkir berhasil diperbarui'
            ]);
        } else {
            throw new Exception('Gagal memperbarui data ongkir');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

function hapusOngkir($conn)
{
    header('Content-Type: application/json');

    $id = $_POST['id'] ?? 0;

    try {
        $stmt = $conn->prepare("DELETE FROM ongkir WHERE id_ongkir = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Data ongkir berhasil dihapus'
            ]);
        } else {
            throw new Exception('Gagal menghapus data ongkir');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

function listOngkir($conn)
{
    header('Content-Type: application/json');

    $columns = ['id_ongkir', 'nama_kota', 'tarif'];
    $limit = isset($_GET['length']) ? intval($_GET['length']) : 10;
    $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
    $orderIndex = $_GET['order'][0]['column'] ?? 0;
    $orderDir = $_GET['order'][0]['dir'] ?? 'asc';
    $search = $_GET['search']['value'] ?? '';

    $orderColumn = $columns[$orderIndex] ?? $columns[0];
    $totalQuery = $conn->query("SELECT COUNT(*) as total FROM ongkir");
    $totalData = $totalQuery ? $totalQuery->fetch_assoc()['total'] : 0;

    if (!empty($search)) {
        $search = $conn->real_escape_string($search);
        $query = "SELECT * FROM ongkir 
                 WHERE nama_kota LIKE '%$search%' 
                 OR tarif LIKE '%$search%'
                 ORDER BY $orderColumn $orderDir 
                 LIMIT $start, $limit";

        $filteredQuery = $conn->query("SELECT COUNT(*) as total FROM ongkir 
                                      WHERE nama_kota LIKE '%$search%' 
                                      OR tarif LIKE '%$search%'");
        $totalFiltered = $filteredQuery ? $filteredQuery->fetch_assoc()['total'] : 0;
    } else {
        $query = "SELECT * FROM ongkir ORDER BY $orderColumn $orderDir LIMIT $start, $limit";
        $totalFiltered = $totalData;
    }

    $result = $conn->query($query);
    $data = [];
    $no = $start + 1;

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'no' => $no++,
            'id_ongkir' => $row['id_ongkir'],
            'nama_kota' => htmlspecialchars($row['nama_kota']),
            'tarif' => 'Rp' . number_format($row['tarif'], 0, ',', '.'),
            'aksi' => "<button class='btn btn-sm btn-primary btn-edit' data-id='{$row['id_ongkir']}'>Edit</button>
                      <button class='btn btn-sm btn-danger btn-hapus' data-id='{$row['id_ongkir']}'>Hapus</button>"
        ];
    }

    echo json_encode([
        "draw" => intval($_GET['draw'] ?? 0),
        "recordsTotal" => intval($totalData),
        "recordsFiltered" => intval($totalFiltered),
        "data" => $data
    ]);
}

function getOngkirById($conn)
{
    header('Content-Type: application/json');

    $id = $_GET['id'] ?? 0;
    $stmt = $conn->prepare("SELECT * FROM ongkir WHERE id_ongkir = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data) {
        $data['tarif'] = number_format($data['tarif'], 2, '.', '');
    }

    echo json_encode($data);
    exit;
}
