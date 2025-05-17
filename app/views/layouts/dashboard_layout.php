<?php
require_once __DIR__ . '/../../helpers/functions.php';


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

    .sidebar {
        width: 250px;
        height: 100vh;
        background-color: #343a40;
        position: fixed;
        top: 0;
        left: 0;
        padding-top: 60px;
        transform: translateX(0);
        transition: transform 0.3s ease;
        z-index: 1020;
    }

    .sidebar.collapsed {
        transform: translateX(-100%);
    }

    .sidebar a {
        color: #fff;
        padding: 15px;
        display: block;
        text-decoration: none;
    }

    .sidebar a:hover {
        background-color: #495057;
    }

    .content {
        margin-left: 250px;
        padding: 20px;
        margin-top: 60px;
        transition: margin-left 0.3s ease;
    }

    .content.expanded {
        margin-left: 0;
    }

    .navbar-custom {
        z-index: 1031;
    }

    @media (max-width: 768px) {
        .content {
            margin-left: 0;
        }
    }
</style>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top navbar-custom">
    <div class="container-fluid">
        <button class="btn btn-dark me-2" id="toggleSidebar">
            ☰
        </button>
        <a class="navbar-brand" href="<?= base_url('index.php?page=dashboard'); ?>">Dashboard</a>
        <div class="d-flex ms-auto">
            <a href="<?= base_url('index.php?page=logout'); ?>" class="btn btn-outline-light me-2">Logout</a>
        </div>
    </div>
</nav>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <a href="<?= base_url('index.php?page=admin/dashboard'); ?>">🏠 Dashboard</a>
    <a href="<?= base_url('index.php?page=admin/data_produk'); ?>">📁 Data Produk</a>
    <a href="<?= base_url('index.php?page=admin/data_pelanggan'); ?>">🧑 Data Pelanggan</a>
    <a href="<?= base_url('index.php?page=admin/data_ongkir'); ?>">💰 Tarif Kota</a>
    <a href="<?= base_url('index.php?page=admin/data_pembelian'); ?>">💳 Pembelian</a>
    <a href="<?= base_url('index.php?page=logout'); ?>">🚪 Logout</a>
</div>

<!-- Content -->
<div class="content" id="mainContent">
    <?php
    if (file_exists($content_view)) {
        include $content_view;
    } else {
        echo "<p>Halaman tidak ditemukan: <code>$content_view</code></p>";
    }
    ?>
</div>

<script>
    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('mainContent');

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        content.classList.toggle('expanded');
    });
</script>





<?php include base_path('app/views/layouts/footer.php') ?>