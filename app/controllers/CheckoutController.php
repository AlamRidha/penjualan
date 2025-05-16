<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'app/config/Database2/Database.php';
require_once 'app/helpers/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metode = $_POST['metode'];
    $cart = $_SESSION['cart'] ?? [];
    $id_user = $_SESSION['user']['id'] ?? null;

    if (!$id_user || empty($cart)) {
        header('Location: ' . base_url('index.php?page=cart&error=invalid'));
        exit;
    }

    // Upload bukti transfer
    $uploadDir = 'uploads/bukti/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . '_' . $_FILES['bukti']['name'];
    $fileTmp = $_FILES['bukti']['tmp_name'];
    move_uploaded_file($fileTmp, $uploadDir . $fileName);

    $pdo = Database::getConnection();

    try {
        $pdo->beginTransaction();

        // 1. Insert ke orders
        $stmt = $pdo->prepare("INSERT INTO orders (id_user, tanggal_order) VALUES (?, NOW())");
        $stmt->execute([$id_user]);
        $id_order = $pdo->lastInsertId();

        // 2. Insert ke order_details + update stok & status produk
        $stmtDetail = $pdo->prepare("INSERT INTO order_details (id_order, id_product, jumlah, subtotal) VALUES (?, ?, ?, ?)");
        $stmtUpdateStock = $pdo->prepare("UPDATE products SET stok = stok - ? WHERE id_product = ?");
        // $stmtUpdateStock = $pdo->prepare("UPDATE products SET stok = stok - ?, status = 'pending' WHERE id_product = ?");

        foreach ($cart as $id_produk => $item) {
            $jumlah = $item['jumlah'];
            $subtotal = $item['harga'] * $jumlah;

            $stmtDetail->execute([$id_order, $id_produk, $jumlah, $subtotal]);
            $stmtUpdateStock->execute([$jumlah, $id_produk]);
        }

        // 3. Insert ke payments
        $stmtPayment = $pdo->prepare("INSERT INTO payments (id_order, metode, bukti_transfer, tanggal_bayar) VALUES (?, ?, ?, NOW())");
        $stmtPayment->execute([$id_order, $metode, $fileName]);

        $pdo->commit();

        // 4. Kosongkan keranjang
        unset($_SESSION['cart']);

        // header('Location: ' . base_url('index.php?page=checkout_success&id=' . $id_order));
        header('Location: ' . base_url('index.php?page=cart&success=1'));
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die('Checkout gagal: ' . $e->getMessage());
    }
}
