<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();


$aksi = $_GET['aksi'] ?? '';

switch ($aksi) {
    case 'tambah':
        tambahPelanggan($conn);
        break;
    case 'edit':
        editPelanggan($conn);
        break;
    case 'hapus':
        hapusPelanggan($conn);
        break;
    case 'list':
        listPelanggan($conn);
        break;
    case 'get':
        getPelangganById($conn);
        break;
    case 'detail':
        header('Content-Type: application/json');
        $id = $_GET['id'] ?? 0;
        echo json_encode(detailPelanggan($conn, $id));
        break;
    default:
        echo json_encode(['error' => 'Aksi tidak dikenal']);
        break;
}


function validateInput($data)
{
    return htmlspecialchars(trim($data));
}


function tambahPelanggan($conn)
{
    header('Content-Type: application/json');

    $nama = validateInput($_POST['nama'] ?? '');
    $email = validateInput($_POST['email'] ?? '');
    $password = password_hash($_POST['password'] ?? '', PASSWORD_BCRYPT);
    $no_hp = validateInput($_POST['no_hp'] ?? '');
    $alamat = validateInput($_POST['alamat'] ?? '');

    try {
        $query = "INSERT INTO pelanggan (nama_pelanggan, email_pelanggan, password_pelanggan, telephone_pelanggan, alamat_pelanggan) 
                 VALUES (?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssss", $nama, $email, $password, $no_hp, $alamat);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Pelanggan berhasil ditambahkan']);
        } else {
            throw new Exception('Gagal menambahkan pelanggan');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

function editPelanggan($conn)
{
    header('Content-Type: application/json');

    // Validasi input
    $id = $_POST['id_pelanggan'] ?? 0;
    $nama = validateInput($_POST['nama'] ?? '');
    $email = validateInput($_POST['email'] ?? '');
    $no_hp = validateInput($_POST['no_hp'] ?? '');
    $alamat = validateInput($_POST['alamat'] ?? '');
    $newPassword = $_POST['password'] ?? '';

    try {
        // Jika password tidak kosong, update password
        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE pelanggan SET 
                                   nama_pelanggan=?, 
                                   email_pelanggan=?, 
                                   telephone_pelanggan=?, 
                                   alamat_pelanggan=?, 
                                   password_pelanggan=? 
                                   WHERE id_pelanggan=?");
            $stmt->bind_param("sssssi", $nama, $email, $no_hp, $alamat, $hashedPassword, $id);
        } else {
            // Jika password kosong, tidak update password
            $stmt = $conn->prepare("UPDATE pelanggan SET 
                                   nama_pelanggan=?, 
                                   email_pelanggan=?, 
                                   telephone_pelanggan=?, 
                                   alamat_pelanggan=? 
                                   WHERE id_pelanggan=?");
            $stmt->bind_param("ssssi", $nama, $email, $no_hp, $alamat, $id);
        }

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Pelanggan berhasil diupdate'
            ]);
        } else {
            throw new Exception('Gagal menjalankan query');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal mengupdate pelanggan: ' . $e->getMessage()
        ]);
    }
    exit;
}

function hapusPelanggan($conn)
{
    header('Content-Type: application/json');

    $id = $_POST['id'] ?? 0;

    try {
        $stmt = $conn->prepare("DELETE FROM pelanggan WHERE id_pelanggan = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Pelanggan berhasil dihapus']);
        } else {
            throw new Exception('Gagal menghapus pelanggan');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

function listPelanggan($conn)
{
    header('Content-Type: application/json');

    $columns = ['id_pelanggan', 'nama_pelanggan', 'email_pelanggan', 'telephone_pelanggan', 'alamat_pelanggan'];
    $limit = isset($_GET['length']) ? intval($_GET['length']) : 10;
    $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
    $orderIndex = $_GET['order'][0]['column'] ?? 0;
    $orderDir = $_GET['order'][0]['dir'] ?? 'asc';
    $search = $_GET['search']['value'] ?? '';

    $orderColumn = $columns[$orderIndex] ?? $columns[0];
    $totalQuery = $conn->query("SELECT COUNT(*) as total FROM pelanggan");
    $totalData = $totalQuery ? $totalQuery->fetch_assoc()['total'] : 0;

    if (!empty($search)) {
        $search = $conn->real_escape_string($search);
        $query = "SELECT * FROM pelanggan 
                 WHERE nama_pelanggan LIKE '%$search%' 
                 OR email_pelanggan LIKE '%$search%' 
                 OR telephone_pelanggan LIKE '%$search%' 
                 OR alamat_pelanggan LIKE '%$search%' 
                 ORDER BY $orderColumn $orderDir 
                 LIMIT $start, $limit";

        $filteredQuery = $conn->query("SELECT COUNT(*) as total FROM pelanggan 
                                      WHERE nama_pelanggan LIKE '%$search%' 
                                      OR email_pelanggan LIKE '%$search%' 
                                      OR telephone_pelanggan LIKE '%$search%' 
                                      OR alamat_pelanggan LIKE '%$search%'");
        $totalFiltered = $filteredQuery ? $filteredQuery->fetch_assoc()['total'] : 0;
    } else {
        $query = "SELECT * FROM pelanggan ORDER BY $orderColumn $orderDir LIMIT $start, $limit";
        $totalFiltered = $totalData;
    }

    $result = $conn->query($query);
    $data = [];
    $no = $start + 1;

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'no' => $no++,
            'nama' => htmlspecialchars($row['nama_pelanggan']),
            'email' => htmlspecialchars($row['email_pelanggan']),
            'no_hp' => htmlspecialchars($row['telephone_pelanggan']),
            'alamat' => htmlspecialchars($row['alamat_pelanggan']),
            'aksi' => "<button class='btn btn-sm btn-primary btn-edit' data-id='{$row['id_pelanggan']}'>Edit</button>
                      <button class='btn btn-sm btn-danger btn-hapus' data-id='{$row['id_pelanggan']}'>Hapus</button>"
        ];
    }

    echo json_encode([
        "draw" => intval($_GET['draw'] ?? 0),
        "recordsTotal" => intval($totalData),
        "recordsFiltered" => intval($totalFiltered),
        "data" => $data
    ]);
}



function getPelangganById($conn)
{
    header('Content-Type: application/json');

    $id = $_GET['id'] ?? 0;
    $stmt = $conn->prepare("SELECT id_pelanggan, nama_pelanggan as nama, email_pelanggan as email, 
                           telephone_pelanggan as no_hp, alamat_pelanggan as alamat 
                           FROM pelanggan WHERE id_pelanggan = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    echo json_encode($result->fetch_assoc());
    exit;
}

function detailPelanggan($conn, $id)
{
    $stmt = $conn->prepare("SELECT id_pelanggan, nama_pelanggan as nama, email_pelanggan as email, 
                           telephone_pelanggan as no_hp, alamat_pelanggan as alamat 
                           FROM pelanggan WHERE id_pelanggan = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
