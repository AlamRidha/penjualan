<?php
require_once base_path('app/config/Auth.php');

if (!isLoggedIn()) {
    header("Location: " . base_url('index.php?page=login_pelanggan'));
    exit;
}

include base_path('app/views/layouts/header.php');
?>

<style>
    /* Customer-specific styles */
    .customer-navbar {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .customer-nav-link {
        text-decoration: none;
        color: rgba(255, 255, 255, 0.85) !important;
        font-weight: 500;
        padding: 0.5rem 1rem !important;
        margin: 0 0.25rem;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
    }

    .customer-nav-link:hover,
    .customer-nav-link.active {
        color: white !important;
        background-color: rgba(255, 255, 255, 0.15);
    }

    .customer-nav-link.active {
        font-weight: 600;
    }

    .customer-cart-btn {
        background-color: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.3);
        margin-right: 1rem;
        color: white !important;
    }

    .customer-cart-btn:hover {
        background-color: rgba(255, 255, 255, 0.2);
    }

    .customer-brand-logo {
        transition: transform 0.3s ease;
    }

    .customer-brand-logo:hover {
        transform: scale(1.05);
    }

    @media (max-width: 768px) {
        .customer-nav-link {
            margin: 0.25rem 0;
        }

        .customer-cart-btn {
            margin: 0.5rem 0;
            width: 100%;
            text-align: left;
            padding-left: 1rem;
        }
    }
</style>

<!-- Customer Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top customer-navbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?= base_url('index.php?page=home'); ?>">
            <img src="<?= base_url('assets/img/grocery.gif') ?>" alt="TokoKu" class="customer-brand-logo rounded me-2" width="40" height="40">
            <span class="fw-bold">TokoKu</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#customerNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="customerNavbar">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item me-2">
                    <a href="index.php?page=pelanggan/cart" class="btn customer-cart-btn position-relative">
                        <i class="fas fa-shopping-cart me-2"></i>Keranjang
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cart-count">
                            <?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="customer-nav-link <?= ($_GET['page'] ?? '') === 'home' ? 'active' : '' ?>" href="<?= base_url('index.php?page=pelanggan/dashboard'); ?>">
                        <i class="fas fa-home me-1"></i> Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="customer-nav-link <?= ($_GET['page'] ?? '') === 'data_product_c' ? 'active' : '' ?>" href="<?= base_url('index.php?page=pelanggan/data_product'); ?>">
                        <i class="fas fa-box-open me-1"></i> Produk
                    </a>
                </li>
                <li class="nav-item">
                    <a class="customer-nav-link <?= ($_GET['page'] ?? '') === 'riwayat' ? 'active' : '' ?>" href="<?= base_url('index.php?page=pelanggan/riwayat'); ?>">
                        <i class="fas fa-history me-1"></i> Riwayat
                    </a>
                </li>
                <li class="nav-item">
                    <button class="btn btn-danger" onclick="confirmCustomerLogout()">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Content -->
<main class="container my-4 flex-fill">
    <div class="content-container">
        <?php
        if (file_exists($content_view)) {
            include $content_view;
        } else {
            echo "<div class='alert alert-danger'>Halaman tidak ditemukan: <code>$content_view</code></div>";
        }
        ?>
    </div>
</main>

<script>
    function confirmCustomerLogout() {
        Swal.fire({
            title: "Logout?",
            text: "Apakah Anda yakin ingin keluar?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, logout',
            cancelButtonText: 'Batal',
            backdrop: 'rgba(0,0,0,0.4)'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Keluar...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('index.php?page=pelanggan/logout', {
                        method: 'POST'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Berhasil logout!',
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
                        console.error('Logout error:', error);
                        Swal.fire('Error', 'Gagal logout', 'error');
                    });
            }
        });
    }

    function updateCartCount(count) {
        document.getElementById('cart-count').textContent = count;
    }
</script>

<?php include base_path('app/views/layouts/footer.php'); ?>