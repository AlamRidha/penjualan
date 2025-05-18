<?php

require_once base_path('app/config/database.php');
require_once base_path('app/controllers/CartController.php');

// Inisialisasi database
$db = new Database();
$conn = $db->getConnection();

// Pastikan session sudah started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Dapatkan data cart langsung dari session dan database
$cart = getCart($conn);


$content_view = base_path('app/views/pelanggan/Cart/index.php');
include base_path('app/views/pelanggan/home_layout.php');

// Di cart.php sebelum memuat view
echo '<pre>';
print_r($cart);
echo '</pre>';
