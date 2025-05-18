<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$aksi = $_GET['aksi'] ?? '';

switch ($aksi) {
    case 'tambah':
        tambahProduk($conn);
        break;
    case 'edit':
        editProduk($conn);
        break;
    case 'hapus':
        hapusProduk($conn);
        break;
    case 'list':
        listProduk($conn);
        break;
    case 'get':
        getProdukById($conn);
        break;
    case 'detail':
        header('Content-Type: application/json');
        $id = $_GET['id'] ?? 0;
        echo json_encode(detailProduk($conn, $id));
        break;
    default:
        echo json_encode(['error' => 'Aksi tidak dikenal']);
        break;
}


function validateInput($data)
{
    return htmlspecialchars(trim($data));
}

function validateUploadedFile($file)
{
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];

    $maxSize = 10 * 1024 * 1024;

    if (!in_array($file['type'], $allowedTypes)) {
        return 'Tipe file tidak valid. Hanya JPG, PNG, JPEG, WEBP.';
    }

    if ($file['size'] > $maxSize) {
        return 'Ukuran file terlalu besar. Maksimum 2MB.';
    }

    return null; // Tidak ada error
}

// --- Fungsi-fungsi ---

function tambahProduk($conn)
{
    $nama = validateInput($_POST['nama_produk'] ?? '');
    $harga = intval($_POST['harga_produk'] ?? 0);
    $stok =  intval($_POST['stok_produk'] ?? 0);
    $deskripsi = validateInput($_POST['deskripsi_produk'] ?? '');
    $foto = null;

    if (empty($nama) || $harga <= 0 || $stok < 0 || empty($deskripsi)) {
        echo json_encode(['status' => 'error', 'message' => 'Form tidak lengkap atau tidak valid']);
        exit;
    }

    if (isset($_FILES['foto_produk']) && $_FILES['foto_produk']['error'] === 0) {

        $error = validateUploadedFile($_FILES['foto_produk']);
        if ($error) {
            echo json_encode(['status' => 'error', 'message' => $error]);
            exit;
        }


        $targetDir = '../../uploads/produk/';
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = time() . '_' . basename($_FILES['foto_produk']['name']);
        $filePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['foto_produk']['tmp_name'], $filePath)) {
            $foto = 'uploads/produk/' . $fileName;
        }
    }

    $stmt = $conn->prepare("INSERT INTO produk (nama_produk, harga_produk, stok_produk, deskripsi_produk, foto_produk) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("siiss", $nama, $harga, $stok, $deskripsi, $foto);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["status" => "success"]);
    exit;
}

function editProduk($conn)
{
    $id = intval($_POST['id_produk'] ?? 0);
    $nama = validateInput($_POST['nama_produk'] ?? '');
    $harga = intval($_POST['harga_produk'] ?? 0);
    $stok = intval($_POST['stok_produk'] ?? 0);
    $deskripsi = validateInput($_POST['deskripsi_produk'] ?? '');
    $fotoBaru = null;

    if ($id <= 0 || empty($nama) || $harga <= 0 || $stok < 0 || empty($deskripsi)) {
        echo json_encode(['status' => 'error', 'message' => 'Form tidak lengkap atau tidak valid']);
        exit;
    }


    $query = $conn->prepare("SELECT foto_produk FROM produk WHERE id_produk = ?");
    $query->bind_param("i", $id);
    $query->execute();
    $query->store_result();

    if ($query->num_rows > 0) {
        $query->bind_result($fotoLama);
        $query->fetch();
        $fotoBaru = $fotoLama;
    }
    $query->close();

    if (isset($_FILES['foto_produk']) && $_FILES['foto_produk']['error'] === 0) {

        $error = validateUploadedFile($_FILES['foto_produk']);
        if ($error) {
            echo json_encode(['status' => 'error', 'message' => $error]);
            exit;
        }


        if ($fotoBaru && file_exists('../../' . $fotoBaru)) {
            unlink('../../' . $fotoBaru);
        }

        $targetDir = '../../uploads/produk/';
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = time() . '_' . basename($_FILES['foto_produk']['name']);
        $filePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['foto_produk']['tmp_name'], $filePath)) {
            $fotoBaru = 'uploads/produk/' . $fileName;
        }
    }

    $stmt = $conn->prepare("UPDATE produk SET nama_produk=?, harga_produk=?, stok_produk=?, deskripsi_produk=?, foto_produk=? WHERE id_produk=?");
    $stmt->bind_param("siissi", $nama, $harga, $stok, $deskripsi, $fotoBaru, $id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["status" => "success"]);
    exit;
}

function hapusProduk($conn)
{
    $id = $_GET['id'];
    $foto = null;

    $query = $conn->prepare("SELECT foto_produk FROM produk WHERE id_produk = ?");
    $query->bind_param("i", $id);
    $query->execute();
    $query->store_result();

    if ($query->num_rows > 0) {
        $query->bind_result($foto);
        $query->fetch();
    }
    $query->close();

    if ($foto && file_exists('../../' . $foto)) {
        unlink('../../' . $foto);
    }

    $stmt = $conn->prepare("DELETE FROM produk WHERE id_produk = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Produk berhasil dihapus.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus produk.']);
    }

    $stmt->close();
    $conn->close();
}

function listProduk($conn)
{
    header('Content-Type: application/json');

    // Mode raw untuk tampilan grid
    // Mode raw untuk tampilan grid
    if (isset($_GET['mode']) && $_GET['mode'] === 'raw') {
        $query = "SELECT * FROM produk";
        $whereClauses = [];

        // Filter pencarian
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $conn->real_escape_string($_GET['search']);
            $whereClauses[] = "(nama_produk LIKE '%$search%' OR deskripsi_produk LIKE '%$search%')";
        }

        // Filter harga
        if (isset($_GET['price_filter']) && !empty($_GET['price_filter'])) {
            $priceFilter = $conn->real_escape_string($_GET['price_filter']);

            if ($priceFilter === '0-50000') {
                $whereClauses[] = "harga_produk BETWEEN 0 AND 50000";
            } elseif ($priceFilter === '50000-100000') {
                $whereClauses[] = "harga_produk BETWEEN 50000 AND 100000";
            } elseif ($priceFilter === '100000-200000') {
                $whereClauses[] = "harga_produk BETWEEN 100000 AND 200000";
            } elseif ($priceFilter === '200000-') {
                $whereClauses[] = "harga_produk > 200000";
            }
        }

        // Gabungkan where clauses jika ada
        if (!empty($whereClauses)) {
            $query .= " WHERE " . implode(" AND ", $whereClauses);
        }

        // Tambahkan pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $itemsPerPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 8;
        $offset = ($page - 1) * $itemsPerPage;
        $query .= " LIMIT $offset, $itemsPerPage";

        $result = $conn->query($query);
        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'id_produk' => $row['id_produk'],
                'nama_produk' => htmlspecialchars($row['nama_produk']),
                'deskripsi_produk' => htmlspecialchars($row['deskripsi_produk']),
                'harga_produk' => "Rp" . number_format($row['harga_produk'], 0, ',', '.'),
                'stok_produk' => $row['stok_produk'],
                'foto_produk' => $row['foto_produk'] ?: null,
                'deskripsi_singkat' => mb_substr($row['deskripsi_produk'], 0, 60) . '...'
            ];
        }

        // Hitung total produk untuk pagination (dengan filter yang sama)
        $totalQuery = "SELECT COUNT(*) as total FROM produk";
        if (!empty($whereClauses)) {
            $totalQuery .= " WHERE " . implode(" AND ", $whereClauses);
        }
        $totalResult = $conn->query($totalQuery);
        $total = $totalResult->fetch_assoc()['total'];

        echo json_encode([
            'success' => true,
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $itemsPerPage,
            'total_pages' => ceil($total / $itemsPerPage)
        ]);
        exit;
    }

    // Datatable

    $columns = ['id_produk', 'nama_produk', 'deskripsi_produk', 'harga_produk', 'stok_produk'];
    $limit = isset($_GET['length']) ? intval($_GET['length']) : 10;
    $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
    $orderIndex = $_GET['order'][0]['column'] ?? 0;
    $orderDir = $_GET['order'][0]['dir'] ?? 'asc';
    $search = $_GET['search']['value'] ?? '';

    $order = $columns[$orderIndex];
    $totalQuery = $conn->query("SELECT COUNT(*) as total FROM produk");
    $totalData = $totalQuery ? $totalQuery->fetch_assoc()['total'] : 0;

    if (!empty($search)) {
        $query = "SELECT * FROM produk WHERE nama_produk LIKE '%$search%' OR deskripsi_produk LIKE '%$search%' ORDER BY $order $orderDir LIMIT $start, $limit";
        $filteredQuery = $conn->query("SELECT COUNT(*) as total FROM produk WHERE nama_produk LIKE '%$search%' OR deskripsi_produk LIKE '%$search%'");
        $totalFiltered = $filteredQuery ? $filteredQuery->fetch_assoc()['total'] : 0;
    } else {
        $query = "SELECT * FROM produk ORDER BY $order $orderDir LIMIT $start, $limit";
        $totalFiltered = $totalData;
    }

    $result = $conn->query($query);
    $data = [];
    $no = $start + 1;

    while ($row = $result->fetch_assoc()) {
        $nestedData = [];
        $nestedData['no'] = $no++;
        $nestedData['foto_produk'] = $row['foto_produk']
            ? "<img src='{$row['foto_produk']}' alt='Foto' style='width:60px;'>"
            : "<span class='text-muted'>Tidak ada</span>";
        $nestedData['nama_produk'] = htmlspecialchars($row['nama_produk']);
        $nestedData['deskripsi_produk'] = htmlspecialchars($row['deskripsi_produk']);
        $nestedData['harga_produk'] = "Rp" . number_format($row['harga_produk'], 0, ',', '.');
        $nestedData['stok_produk'] = $row['stok_produk'];
        $nestedData['aksi'] = "
     <button class='btn btn-sm btn-warning btn-edit' data-id='{$row['id_produk']}'>Edit</button>
     <button class='btn btn-sm btn-danger btn-hapus' data-id='{$row['id_produk']}'>Hapus</button>";
        $data[] = $nestedData;
    }

    echo json_encode([
        "draw" => intval($_GET['draw'] ?? 0),
        "recordsTotal" => intval($totalData),
        "recordsFiltered" => intval($totalFiltered),
        "data" => $data
    ]);
}

function getProdukById($conn)
{
    $id = $_GET['id'] ?? 0;
    $stmt = $conn->prepare("SELECT * FROM produk WHERE id_produk = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($produk = $result->fetch_assoc()) {
        echo json_encode($produk);
    } else {
        echo json_encode(['error' => 'Produk tidak ditemukan']);
    }

    $stmt->close();
    exit;
}


function detailProduk($conn, $id)
{
    $stmt = $conn->prepare("SELECT * FROM produk WHERE id_produk = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $produk = $result->fetch_assoc();
    $stmt->close();
    return $produk ?: ['error' => 'Produk tidak ditemukan'];
}
