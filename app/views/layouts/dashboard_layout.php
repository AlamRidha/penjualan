<?php
require_once __DIR__ . '/../../helpers/functions.php';

if (!isLoggedIn()) {
    header("Location: " . base_url('index.php?page=login'));
    exit;
}

include base_path('app/views/layouts/header.php');
?>

<style>
    :root {
        --sidebar-width: 250px;
        --sidebar-bg: #4f46e5;
        /* Ungu yang lebih cerah */
        --sidebar-active-bg: #4338ca;
        --sidebar-hover-bg: #6366f1;
        --navbar-height: 70px;
        /* Sedikit lebih tinggi */
        --primary-color: #4f46e5;
        --secondary-color: #10b981;
        --accent-color: #f59e0b;
        --danger-color: #ef4444;
        --transition-speed: 0.3s;
        --content-bg: #f9fafb;
        /* Background lebih soft */
    }

    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        background-color: var(--content-bg);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Sidebar dengan gradient */
    .sidebar {
        width: var(--sidebar-width);
        height: 100vh;
        background: linear-gradient(135deg, var(--sidebar-bg) 0%, #7c3aed 100%);
        position: fixed;
        top: 0;
        left: 0;
        padding-top: var(--navbar-height);
        transform: translateX(0);
        transition: transform var(--transition-speed) ease;
        z-index: 1020;
        box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
    }

    .sidebar.collapsed {
        transform: translateX(-100%);
    }

    .sidebar-menu {
        padding: 15px 0;
        margin: 0;
        list-style: none;
    }

    .sidebar-link {
        color: rgba(255, 255, 255, 0.9);
        padding: 14px 25px;
        display: flex;
        align-items: center;
        text-decoration: none;
        transition: all 0.2s ease;
        border-left: 4px solid transparent;
        margin: 5px 10px;
        border-radius: 8px;
    }

    .sidebar-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: #fff;
        transform: translateX(5px);
    }

    .sidebar-link.active {
        background-color: rgba(255, 255, 255, 0.15);
        border-left: 4px solid #fff;
        font-weight: 500;
    }

    .sidebar-icon {
        margin-right: 12px;
        font-size: 1.1rem;
        width: 24px;
        text-align: center;
    }

    /* Content area */
    .content {
        margin-left: var(--sidebar-width);
        padding: 25px;
        margin-top: var(--navbar-height);
        transition: margin-left var(--transition-speed) ease;
        flex: 1;
    }

    .content.expanded {
        margin-left: 0;
    }

    /* Navbar dengan efek glassmorphism */
    .navbar-custom {
        height: var(--navbar-height);
        background: rgba(255, 255, 255, 0.9) !important;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        z-index: 1031;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .navbar-brand {
        font-weight: 700;
        color: var(--sidebar-bg) !important;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
    }

    .navbar-brand i {
        color: var(--accent-color);
        margin-right: 10px;
    }

    .toggle-btn {
        border: none;
        background: none;
        font-size: 1.4rem;
        color: var(--sidebar-bg);
        transition: all 0.2s ease;
    }

    .toggle-btn:hover {
        color: var(--sidebar-active-bg);
        transform: scale(1.1);
    }

    /* User info di navbar */
    .user-info {
        display: flex;
        align-items: center;
        background: rgba(79, 70, 229, 0.1);
        padding: 5px 12px;
        border-radius: 20px;
        margin-right: 15px;
        color: var(--sidebar-bg);
        font-weight: 500;
    }

    .user-info i {
        margin-right: 8px;
        color: var(--accent-color);
    }

    /* Tombol logout */
    .btn-logout {
        background-color: var(--danger-color);
        color: white;
        border-radius: 20px;
        padding: 8px 16px;
        font-weight: 500;
        transition: all 0.2s ease;
        border: none;
    }

    .btn-logout:hover {
        background-color: #dc2626;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
    }

    /* Card styling */
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
        margin-bottom: 25px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background-color: #fff;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        font-weight: 600;
        padding: 15px 20px;
        color: var(--sidebar-bg);
    }

    /* Gaya untuk tombol logout SweetAlert */
    .btn-logout-swal {
        background-color: var(--danger-color);
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 500;
        cursor: pointer;
        margin-right: 10px;
    }

    .btn-logout-swal:hover {
        background-color: #dc2626;
    }

    /* Gaya untuk tombol batal SweetAlert */
    .btn-cancel-swal {
        background-color: #e5e7eb;
        /* Tailwind gray-200 */
        color: #374151;
        /* Tailwind gray-700 */
        border: none;
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 500;
        cursor: pointer;
    }

    .btn-cancel-swal:hover {
        background-color: #d1d5db;
        /* Tailwind gray-300 */
    }


    /* Responsive design */
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.collapsed-mobile {
            transform: translateX(0);
        }

        .content {
            margin-left: 0;
            padding: 15px;
        }

        .navbar-brand {
            font-size: 1.1rem;
        }
    }

    /* Animasi untuk konten */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .content>* {
        animation: fadeIn 0.5s ease forwards;
    }
</style>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light fixed-top navbar-custom">
    <div class="container-fluid">
        <button class="toggle-btn me-3" id="toggleSidebar">
            <i class="fas fa-bars"></i>
        </button>
        <a class="navbar-brand" href="<?= base_url('index.php?page=dashboard'); ?>">
            <i class="fas fa-store-alt"></i>TokoKu
        </a>
        <div class="d-flex ms-auto align-items-center">
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <?= $_SESSION['username'] ?? 'Admin' ?>
            </div>
            <button class="btn-logout" onclick="confirmAdminLogout()">
                <i class="fas fa-sign-out-alt me-1"></i>Logout
            </button>
        </div>
    </div>
</nav>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <ul class="sidebar-menu">
        <li>
            <a href="<?= base_url('index.php?page=admin/dashboard'); ?>" class="sidebar-link <?= (strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false) ? 'active' : '' ?>">
                <span class="sidebar-icon"><i class="fas fa-tachometer-alt"></i></span>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="<?= base_url('index.php?page=admin/data_produk'); ?>" class="sidebar-link <?= (strpos($_SERVER['REQUEST_URI'], 'data_produk') !== false) ? 'active' : '' ?>">
                <span class="sidebar-icon"><i class="fas fa-boxes"></i></span>
                <span>Data Produk</span>
            </a>
        </li>
        <li>
            <a href="<?= base_url('index.php?page=admin/data_pelanggan'); ?>" class="sidebar-link <?= (strpos($_SERVER['REQUEST_URI'], 'data_pelanggan') !== false) ? 'active' : '' ?>">
                <span class="sidebar-icon"><i class="fas fa-users"></i></span>
                <span>Data Pelanggan</span>
            </a>
        </li>
        <li>
            <a href="<?= base_url('index.php?page=admin/data_ongkir'); ?>" class="sidebar-link <?= (strpos($_SERVER['REQUEST_URI'], 'data_ongkir') !== false) ? 'active' : '' ?>">
                <span class="sidebar-icon"><i class="fas fa-truck-moving"></i></span>
                <span>Tarif Pengiriman</span>
            </a>
        </li>
        <li>
            <a href="<?= base_url('index.php?page=admin/data_pembelian'); ?>" class="sidebar-link <?= (strpos($_SERVER['REQUEST_URI'], 'data_pembelian') !== false) ? 'active' : '' ?>">
                <span class="sidebar-icon"><i class="fas fa-shopping-basket"></i></span>
                <span>Pembelian</span>
            </a>
        </li>
        <li>
            <a href="<?= base_url('index.php?page=admin/data_penjualan'); ?>" class="sidebar-link <?= (strpos($_SERVER['REQUEST_URI'], 'data_penjualan') !== false) ? 'active' : '' ?>">
                <span class="sidebar-icon"><i class="fas fa-cash-register"></i></span>
                <span>Penjualan</span>
            </a>
        </li>
    </ul>
</div>

<!-- Content -->
<div class="content" id="mainContent">
    <?php
    if (file_exists($content_view)) {
        include $content_view;
    } else {
        echo '<div class="card">
                <div class="card-header">Error</div>
                <div class="card-body">
                    <div class="alert alert-danger">Halaman tidak ditemukan: <code>' . $content_view . '</code></div>
                </div>
              </div>';
    }
    ?>
</div>

<script>
    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('mainContent');

    // Check screen size and toggle sidebar accordingly
    function handleSidebar() {
        if (window.innerWidth <= 768) {
            sidebar.classList.add('collapsed');
        } else {
            sidebar.classList.remove('collapsed');
        }
    }

    // Initial check
    handleSidebar();

    // Add event listener for window resize
    window.addEventListener('resize', handleSidebar);

    toggleBtn.addEventListener('click', () => {
        if (window.innerWidth <= 768) {
            sidebar.classList.toggle('collapsed-mobile');
        } else {
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('expanded');
        }
    });

    function confirmAdminLogout() {
        Swal.fire({
            title: "Logout?",
            text: "Anda akan keluar dari sistem",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-sign-out-alt"></i> Ya, Logout',
            cancelButtonText: '<i class="fas fa-times"></i> Batal',
            backdrop: 'rgba(0,0,0,0.4)',
            customClass: {
                confirmButton: 'btn-logout-swal',
                cancelButton: 'btn-cancel-swal'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('index.php?page=logout', {
                        method: 'POST'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Sampai Jumpa!',
                                text: data.message,
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false,
                                willClose: () => {
                                    window.location.href = data.redirect;
                                }
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'Gagal melakukan logout', 'error');
                    });
            }
        });
    }

    // Tambahkan animasi saat elemen muncul
    document.addEventListener('DOMContentLoaded', function() {
        const elements = document.querySelectorAll('.card, .table-responsive, .alert');
        elements.forEach((el, index) => {
            el.style.opacity = '0';
            el.style.animation = `fadeIn 0.5s ease forwards ${index * 0.1}s`;
        });
    });
</script>

<?php include base_path('app/views/layouts/footer.php') ?>