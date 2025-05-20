<?php

$db = new Database();
$conn = $db->getConnection();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_GET['aksi'])) {
    // Handle request
    $action = $_GET['aksi'];

    switch ($action) {
        case 'tambah':
            addToCart($conn);
            break;
        case 'hapus':
            removeFromCart();
            break;
        case 'update':
            updateCart($conn);
            break;
        case 'checkout':
            checkout($conn);
            break;
        case 'get':
            header('Content-Type: application/json');
            echo json_encode(getCart($conn));
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
            break;
    }
}

function getCart($conn)
{
    $cartItems = [];
    $total = 0;

    foreach ($_SESSION['cart'] as $productId => $quantity) {
        $product = getProduct($conn, $productId);
        if ($product) {
            $subtotal = $product['harga_produk'] * $quantity;
            $cartItems[] = [
                'id' => $product['id_produk'],
                'nama' => $product['nama_produk'],
                'harga' => $product['harga_produk'],
                'foto' => $product['foto_produk'],
                'quantity' => $quantity,
                'subtotal' => $subtotal,
                'stok' => $product['stok_produk']
            ];
            $total += $subtotal;
        }
    }

    return [
        'items' => $cartItems,
        'total' => $total,
        'count' => count($_SESSION['cart'])
    ];
}


// Helper function untuk mendapatkan produk
function getProduct($conn, $id)
{
    $stmt = $conn->prepare("SELECT * FROM produk WHERE id_produk = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return null;
    }

    $product = $result->fetch_assoc();
    $stmt->close();

    return $product;
}

// Tambah item ke keranjang
function addToCart($conn)
{
    $productId = $_POST['id_produk'] ?? 0;
    $quantity = $_POST['qty'] ?? 1;

    // Validasi input
    if ($productId <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Input tidak valid']);
        return;
    }

    // Mulai transaksi untuk menghindari race condition
    $conn->begin_transaction();

    try {
        // Dapatkan produk dengan lock untuk menghindari race condition
        $stmt = $conn->prepare("SELECT * FROM produk WHERE id_produk = ? FOR UPDATE");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$product) {
            throw new Exception('Produk tidak ditemukan');
        }

        // Hitung total quantity yang sudah ada di keranjang
        $existingQuantity = $_SESSION['cart'][$productId] ?? 0;
        $totalRequested = $existingQuantity + $quantity;

        if ($product['stok_produk'] < $totalRequested) {
            throw new Exception('Stok tidak mencukupi');
        }

        // Update keranjang
        $_SESSION['cart'][$productId] = $totalRequested;

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan ke keranjang',
            'cart_count' => count($_SESSION['cart'])
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// Hapus item dari keranjang
function removeFromCart()
{
    header('Content-Type: application/json');

    try {
        if (empty($_GET['id'])) {
            throw new Exception('ID Produk tidak valid');
        }

        $productId = (int)$_GET['id'];

        if (!isset($_SESSION['cart'][$productId])) {
            throw new Exception('Produk tidak ada di keranjang');
        }

        unset($_SESSION['cart'][$productId]);

        echo json_encode([
            'success' => true,
            'message' => 'Produk dihapus dari keranjang',
            'count' => array_sum($_SESSION['cart'] ?? [])
        ]);

        exit; // Pastikan tidak ada output lain
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

// Update jumlah item
function updateCart($conn)
{
    // Set header JSON di awal
    header('Content-Type: application/json');

    try {
        $productId = $_POST['id_produk'] ?? 0;
        $quantity = $_POST['qty'] ?? 1;

        // Validasi input
        if ($productId <= 0 || $quantity <= 0) {
            throw new Exception('Input tidak valid');
        }

        // Mulai transaksi untuk konsistensi data
        $conn->begin_transaction();

        // Dapatkan produk dengan lock untuk menghindari race condition
        $stmt = $conn->prepare("SELECT * FROM produk WHERE id_produk = ? FOR UPDATE");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$product) {
            throw new Exception('Produk tidak ditemukan');
        }

        // Validasi stok
        if ($product['stok_produk'] < $quantity) {
            throw new Exception('Stok tidak mencukupi');
        }

        // Update keranjang
        if (!isset($_SESSION['cart'][$productId])) {
            throw new Exception('Produk tidak ada di keranjang');
        }

        $_SESSION['cart'][$productId] = $quantity;
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Keranjang diperbarui',
            'cart' => getCart($conn) // Kirim data keranjang terbaru
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

    exit; // Pastikan tidak ada output lain
}


// Proses checkout
function checkout($conn)
{
    // Pastikan semua output selalu dalam format JSON
    try {
        // Validasi data
        $required = ['nama', 'telepon', 'alamat', 'id_ongkir', 'bank'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
                return;
            }
        }

        // Validasi file upload
        if (!isset($_FILES['bukti']) || $_FILES['bukti']['error'] != UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Bukti pembayaran harus diupload']);
            return;
        }

        // Validasi total pembayaran
        if (empty($_POST['total_pembelian'])) {
            echo json_encode(['success' => false, 'message' => 'Total pembelian tidak valid']);
            return;
        }

        // Debug: log what we're receiving
        error_log("Checkout data: " . json_encode($_POST));
        error_log("File data: " . json_encode($_FILES));

        // Validasi folder upload
        $uploadDir = __DIR__ . '/../../uploads/bukti/';
        // atau
        // $uploadDir = './uploads/bukti/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
            throw new Exception('Tidak dapat membuat direktori upload');
        }

        if (!is_writable($uploadDir)) {
            throw new Exception('Direktori upload tidak dapat ditulis');
        }

        // Mulai transaksi
        $conn->begin_transaction();

        // 1. Simpan data pembelian
        $stmt = $conn->prepare("INSERT INTO pembelian (
            id_pelanggan, id_ongkir, total_pembelian, alamat_pengiriman, nama_kota, tarif
        ) VALUES (?, ?, ?, ?, ?, ?)");

        // Dapatkan data ongkir
        $ongkirQuery = $conn->prepare("SELECT nama_kota, tarif FROM ongkir WHERE id_ongkir = ?");
        $ongkirQuery->bind_param("i", $_POST['id_ongkir']);
        $ongkirQuery->execute();
        $ongkirResult = $ongkirQuery->get_result();

        if ($ongkirResult->num_rows === 0) {
            throw new Exception('Data ongkir tidak ditemukan');
        }

        $ongkir = $ongkirResult->fetch_assoc();
        $ongkirQuery->close();

        $pelangganId = $_SESSION['pelanggan']['id_pelanggan'] ?? 0;
        $totalPembelian = (float)$_POST['total_pembelian'];

        $stmt->bind_param(
            "iidssd",
            $pelangganId,
            $_POST['id_ongkir'],
            $totalPembelian,
            $_POST['alamat'],
            $ongkir['nama_kota'],
            $ongkir['tarif']
        );

        if (!$stmt->execute()) {
            throw new Exception('Gagal menyimpan data pembelian: ' . $stmt->error);
        }

        $pembelianId = $conn->insert_id;
        $stmt->close();

        // 2. Simpan item pembelian
        $stmt = $conn->prepare("INSERT INTO pembelian_produk (
            id_pembelian, id_produk, jumlah, nama_produk, harga, sub_harga
        ) VALUES (?, ?, ?, ?, ?, ?)");

        foreach ($_SESSION['cart'] as $productId => $quantity) {
            $product = getProduct($conn, $productId);
            if (!$product) {
                throw new Exception('Produk id ' . $productId . ' tidak ditemukan');
            }

            $subtotal = $product['harga_produk'] * $quantity;

            $stmt->bind_param(
                "iiisdd",
                $pembelianId,
                $productId,
                $quantity,
                $product['nama_produk'],
                $product['harga_produk'],
                $subtotal
            );

            if (!$stmt->execute()) {
                throw new Exception('Gagal menyimpan item pembelian: ' . $stmt->error);
            }

            // Update stok produk
            $updateStok = $conn->prepare("UPDATE produk SET stok_produk = stok_produk - ? WHERE id_produk = ?");
            $updateStok->bind_param("ii", $quantity, $productId);

            if (!$updateStok->execute()) {
                throw new Exception('Gagal memperbarui stok produk: ' . $updateStok->error);
            }

            $updateStok->close();
        }
        $stmt->close();

        // 3. Simpan bukti pembayaran
        // Validasi ekstensi file
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $fileExtension = strtolower(pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedExtensions)) {
            echo json_encode([
                'success' => false,
                'message' => 'Format file tidak didukung. Gunakan JPG, PNG, atau PDF'
            ]);
            return;
        }

        $filename = 'bukti_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExtension;
        $targetPath = $uploadDir . $filename;

        if (!move_uploaded_file($_FILES['bukti']['tmp_name'], $targetPath)) {
            throw new Exception('Gagal menyimpan bukti pembayaran. Error code: ' . $_FILES['bukti']['error']);
        }

        // 4. Simpan data pembayaran
        $stmt = $conn->prepare("INSERT INTO pembayaran (
            id_pembelian, nama, bank, jumlah, bukti_pembayaran
        ) VALUES (?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "issds",
            $pembelianId,
            $_POST['nama'],
            $_POST['bank'],
            $totalPembelian,
            $filename
        );

        if (!$stmt->execute()) {
            throw new Exception('Gagal menyimpan data pembayaran: ' . $stmt->error);
        }

        $stmt->close();

        // 5. Kosongkan keranjang
        $_SESSION['cart'] = [];

        // Commit transaksi
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Pesanan berhasil dibuat',
            'id_pembelian' => $pembelianId
        ]);
    } catch (Exception $e) {
        // Rollback transaksi jika ada kesalahan
        $conn->rollback();

        // hapus file jika sudah diupload
        if (isset($targetPath) && file_exists($targetPath)) {
            unlink($targetPath);
        }

        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}
