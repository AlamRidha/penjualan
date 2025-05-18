<?php
require_once base_path('app/config/Auth.php');

if (!isLoggedIn()) {
    header("Location: " . base_url('index.php?page=login'));
    exit;
}

include base_path('app/views/layouts/header.php');
?>

<style>
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
</style>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url('index.php?page=home'); ?>">
            <img src="<?= base_url('assets/img/grocery.gif') ?>" alt="imageicon" class="rounded me-2" width="40" height="40"> Toko Penjualan Barang
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <a href="index.php?page=pelanggan/cart" class="btn btn-outline-light position-relative">
                    🛒 Keranjang
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cart-count">
                        <?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>
                    </span>
                </a>
                <li class="nav-item">
                    <a class="nav-link <?= ($_GET['page'] ?? '') === 'home' ? 'active' : '' ?>" href="<?= base_url('index.php?page=pelanggan/dashboard'); ?>">Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($_GET['page'] ?? '') === 'data_product_c' ? 'active' : '' ?>" href="<?= base_url('index.php?page=pelanggan/data_product'); ?>">Produk</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('index.php?page=logout'); ?>">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Content -->
<main class="container my-4 ">
    <?php
    if (file_exists($content_view)) {
        include $content_view;
    } else {
        echo "<p>Halaman tidak ditemukan: <code>$content_view</code></p>";
    }
    ?>
</main>


<?php include base_path('app/views/layouts/footer.php'); ?>