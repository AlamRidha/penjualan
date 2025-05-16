<?php
// app/views/auth/register.php

// Jika sudah login, alihkan ke dashboard / home
if (isLoggedIn()) {
    header("Location: " . base_url(
        'index.php?page=' . ($_SESSION['user']['role'] === 'admin' ? 'dashboard' : 'home')
    ));
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar • Toko Serba 35</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icon -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-light">

    <div class="container d-flex align-items-center justify-content-center vh-100">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow border-0 rounded-4">
                <div class="card-header text-center bg-primary text-white rounded-top-4">
                    <h3 class="fw-bold mb-0">Toko Serba 35</h3>
                    <small>Form Pendaftaran Akun</small>
                </div>

                <div class="card-body p-4">

                    <!-- Alert error (jika ada) -->
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger text-center py-2">
                            <?= htmlspecialchars($_GET['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form id="formRegister"
                        method="POST"
                        action="<?= base_url('index.php?page=proses_register'); ?>">

                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="bi bi-person-fill me-1"></i>Username
                            </label>
                            <input type="text"
                                class="form-control"
                                id="username"
                                name="username"
                                placeholder="Masukkan username"
                                minlength="4"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-key-fill me-1"></i>Password
                            </label>
                            <input type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                placeholder="Min. 6 karakter"
                                minlength="6"
                                required>
                        </div>

                        <div class="mb-4">
                            <label for="confirm" class="form-label">
                                <i class="bi bi-shield-lock-fill me-1"></i>Konfirmasi Password
                            </label>
                            <input type="password"
                                class="form-control"
                                id="confirm"
                                placeholder="Ulangi password"
                                required>
                        </div>

                        <button class="btn btn-primary w-100" type="submit">
                            <i class="bi bi-person-plus-fill me-1"></i>Daftar
                        </button>
                    </form>
                </div>

                <div class="card-footer text-center bg-white rounded-bottom-4">
                    <small>
                        Sudah punya akun?
                        <a href="<?= base_url('index.php?page=login'); ?>">Login di sini</a>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script>
        /* Validasi konfirmasi password sebelum submit */
        document.getElementById('formRegister').addEventListener('submit', function(e) {
            const pass = document.getElementById('password').value;
            const conf = document.getElementById('confirm').value;

            if (pass !== conf) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Password tidak cocok',
                    text: 'Pastikan kolom Password & Konfirmasi sama.',
                    confirmButtonColor: '#3085d6'
                });
            }
        });
    </script>

    <?php
    /* Flash‑alert jika baru berhasil register (set di controller) */
    if (isset($_SESSION['register_success'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Registrasi Berhasil!',
                text: 'Registrasi Akun Berhasil, Silahkan Login.',
                // text: '<?= addslashes($_SESSION['register_success']); ?>',
                timer: 1800,
                showConfirmButton: false
            });
        </script>
    <?php unset($_SESSION['register_success']);
    endif; ?>

</body>

</html>