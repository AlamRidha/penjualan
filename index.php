<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'app/helpers/functions.php';
require_once 'app/config/Auth.php';
require_once 'app/controllers/AuthController.php';


$page = $_GET['page'] ?? 'home';

switch ($page) {
    // Login/Register Admin & Pelanggan
    case 'login_admin':
        include base_path('app/views/admin/login.php');
        break;

    case 'login_admin_process':
        (new AuthController())->loginAdmin($_POST['username'], $_POST['password']);
        break;

    case 'admin/dashboard':
        include base_path('app/views/admin/dashboard.php');
        break;

    // Data Produk
    case 'admin/data_produk':
        include base_path('app/views/admin/data_produk.php');
        break;

    // Data Pelanggan
    case 'admin/data_pelanggan':
        include base_path('app/views/admin/data_pelanggan.php');
        break;

    // Data Ongkir
    case 'admin/data_ongkir':
        include base_path('app/views/admin/data_ongkir.php');
        break;

    // Data Pembelian
    case 'admin/data_pembelian':
        include base_path('app/views/admin/data_pembelian.php');
        break;

    // Data Laporan
    case 'admin/data_penjualan':
        include base_path('app/views/admin/data_laporan.php');
        break;

    case 'login_pelanggan':
        include base_path('app/views/pelanggan/login.php');
        break;

    case 'login_pelanggan_process':
        (new AuthController())->loginPelanggan($_POST['email'], $_POST['password']);
        break;

    case 'pelanggan/dashboard':
        include base_path('app/views/pelanggan/dashboard.php');
        break;

    // Data Produk
    case 'pelanggan/data_product':
        include base_path('app/views/pelanggan/data_produk.php');
        break;

    // Data Riwayat
    case 'pelanggan/riwayat':
        include base_path('app/views/pelanggan/riwayat.php');
        break;

    // Data Keranjang
    case 'pelanggan/cart':
        // include base_path('app/views/pelanggan/cart.php');
        require_once base_path('app/controllers/CartController.php');
        $db = new Database();
        $conn = $db->getConnection();
        $cart = getCart($conn);
        include base_path('app/views/pelanggan/cart.php');
        break;


    case 'cart_action':
        require_once base_path('app/controllers/CartController.php');
        break;

    case 'register_pelanggan':
        include base_path('app/views/pelanggan/register.php');
        break;

    case 'register_pelanggan_process':
        (new AuthController())->registerPelanggan();
        break;

    case 'pelanggan/logout':
        (new AuthController())->logoutWithConfirmation('pelanggan');
        break;

    case 'logout':
        (new AuthController())->logoutWithConfirmation('admin');
        break;

    // Dashboard default
    case 'dashboard':
        $content_view = base_path('app/views/dashboard/index.php');
        include base_path('app/views/layouts/layout.php');
        break;
    default:
        echo "404 Page Not Found";
        break;
}
