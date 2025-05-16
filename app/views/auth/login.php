<?php

if (isLoggedIn() && !isset($_SESSION['login_success'])) {
    $role = $_SESSION['user']['role'];
    if ($role === 'admin') {
        header("Location: " . base_url('index.php?page=dashboard'));
    } else {
        header("Location: " . base_url('index.php?page=home'));
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container d-flex align-items-center justify-content-center vh-100">
        <div class="row justify-content-center w-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-lg border-0 rounded-lg">
                    <div class="card-header bg-primary text-white rounded-top py-3">
                        <h3 class="text-center fw-light my-0">Selamat Datang di <br> Toko Serba 35</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger text-center" role="alert">
                                <?= htmlspecialchars($_GET['error']); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?= base_url('index.php?page=proses_login'); ?>">
                            <div class="mb-3">
                                <label for="username" class="form-label"><i class="bi bi-person-fill me-2"></i> Username</label>
                                <input type="text" name="username" class="form-control form-control-lg" id="username" placeholder="Masukkan username Anda" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label"><i class="bi bi-key-fill me-2"></i> Password</label>
                                <input type="password" name="password" class="form-control form-control-lg" id="password" placeholder="Masukkan password Anda" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary btn-lg" type="submit" name="login"><i class="bi bi-box-arrow-in-right me-2"></i> Login</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center py-3">
                        <div class="small"><a href="<?= base_url('index.php?page=register'); ?>" style="text-decoration: none">Belum punya akun? Daftar di sini</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <?php if (isset($_SESSION['login_success']) && $_SESSION['login_success']): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Login Berhasil!',
                text: '<?= $_SESSION['login_message'] ?>',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = "<?= base_url('index.php?page=' . ($_SESSION['user']['role'] === 'admin' ? 'dashboard' : 'home')) ?>";
            });
        </script>
        <?php
        // Hapus session flash
        unset($_SESSION['login_success']);
        unset($_SESSION['login_message']);
        ?>
    <?php endif; ?>


</body>

</html>