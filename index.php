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


    case 'login_pelanggan':
        include base_path('app/views/pelanggan/login.php');
        break;

    case 'login_pelanggan_process':
        (new AuthController())->loginPelanggan($_POST['email'], $_POST['password']);
        break;

    case 'register_pelanggan':
        include base_path('app/views/pelanggan/register.php');
        break;

    case 'register_pelanggan_process':
        (new AuthController())->registerPelanggan($_POST);
        break;

    case 'logout':
        (new AuthController())->logoutAdmin();
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
