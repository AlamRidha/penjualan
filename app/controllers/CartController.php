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
    $productId = $_GET['id'] ?? 0;

    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
        echo json_encode(['success' => true, 'message' => 'Produk dihapus dari keranjang']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan di keranjang']);
    }
}

// Update jumlah item
function updateCart($conn)
{
    $productId = $_POST['id_produk'] ?? 0;
    $quantity = $_POST['qty'] ?? 1;

    // Validasi input
    if ($productId <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Input tidak valid']);
        return;
    }

    // Cek stok produk
    $product = getProduct($conn, $productId);
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan']);
        return;
    }

    // Cek stok cukup
    if ($product['stok_produk'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Stok tidak mencukupi']);
        return;
    }

    // Update keranjang
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] = $quantity;
        echo json_encode(['success' => true, 'message' => 'Keranjang diperbarui']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan di keranjang']);
    }
}

// Proses checkout
function checkout($conn)
{
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

    // Mulai transaksi
    $conn->begin_transaction();

    try {
        // 1. Simpan data pembelian
        $stmt = $conn->prepare("INSERT INTO pembelian (
            id_pelanggan, id_ongkir, total_pembelian, alamat_pengiriman, nama_kota, tarif
        ) VALUES (?, ?, ?, ?, ?, ?)");

        // Dapatkan data ongkir
        $ongkir = $conn->query("SELECT nama_kota, tarif FROM ongkir WHERE id_ongkir = " . $_POST['id_ongkir'])->fetch_assoc();

        $pelangganId = $_SESSION['user']['id'] ?? 0;
        $stmt->bind_param(
            "iidssd",
            $pelangganId,
            $_POST['id_ongkir'],
            $_POST['total_pembelian'],
            $_POST['alamat'],
            $ongkir['nama_kota'],
            $ongkir['tarif']
        );
        $stmt->execute();
        $pembelianId = $conn->insert_id;
        $stmt->close();

        // 2. Simpan item pembelian
        $stmt = $conn->prepare("INSERT INTO pembelian_produk (
            id_pembelian, id_produk, jumlah, nama_produk, harga, sub_harga
        ) VALUES (?, ?, ?, ?, ?, ?)");

        foreach ($_SESSION['cart'] as $productId => $quantity) {
            $product = getProduct($conn, $productId);
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
            $stmt->execute();

            // Update stok produk
            $conn->query("UPDATE produk SET stok_produk = stok_produk - $quantity WHERE id_produk = $productId");
        }
        $stmt->close();

        // 3. Simpan bukti pembayaran
        $uploadDir = 'uploads/bukti_pembayaran/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION);
        $filename = 'pembayaran_' . $pembelianId . '_' . time() . '.' . $ext;
        $targetFile = $uploadDir . $filename;

        if (!move_uploaded_file($_FILES['bukti']['tmp_name'], $targetFile)) {
            throw new Exception('Gagal mengupload bukti pembayaran');
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
            $_POST['total_pembelian'],
            $filename
        );
        $stmt->execute();
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
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Gagal memproses pesanan: ' . $e->getMessage()
        ]);
    }
}
