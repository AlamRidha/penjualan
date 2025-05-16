<?php
session_start();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add') {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $harga = (int) $_POST['harga'];
    $qty = (int) $_POST['qty'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Cek apakah produk sudah ada di cart
    if (isset($_SESSION['cart'][$id])) {
        // If the old structure exists, migrate it
        if (isset($_SESSION['cart'][$id]['qty'])) {
            $_SESSION['cart'][$id]['jumlah'] = $_SESSION['cart'][$id]['qty'];
            unset($_SESSION['cart'][$id]['qty']);
        }
        if (isset($_SESSION['cart'][$id]['nama'])) {
            $_SESSION['cart'][$id]['nama_produk'] = $_SESSION['cart'][$id]['nama'];
            unset($_SESSION['cart'][$id]['nama']);
        }
        // Now increase the quantity
        $_SESSION['cart'][$id]['jumlah'] += $qty;
    } else {
        // Add new item with consistent structure
        $_SESSION['cart'][$id] = [
            'nama_produk' => $nama,
            'harga' => $harga,
            'jumlah' => $qty
        ];
    }

    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false]);
exit;
