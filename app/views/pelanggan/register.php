<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Pelanggan | Toko Online</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4cc9f0;
            --dark: #212529;
            --light: #f8f9fa;
            --success: #4bb543;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #36d1dc, #5b86e5);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: 20px;
        }

        .register-card {
            width: 100%;
            max-width: 450px;
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .register-card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            color: white;
            padding: 25px;
            text-align: center;
            border-bottom: none;
        }

        .card-header h2 {
            font-weight: 600;
            margin: 0;
            font-size: 1.8rem;
        }

        .card-body {
            padding: 30px;
            background-color: white;
        }

        .form-label {
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }

        .btn-register {
            color: white;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            width: 100%;
            margin-top: 10px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
        }

        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .required-field::after {
            content: " *";
            color: #dc3545;
        }

        .input-hint {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 5px;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-form {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>

<body>
    <div class="register-card animate-form">
        <div class="card-header">
            <h2><i class="fas fa-user-plus me-2"></i>Daftar Akun Baru</h2>
        </div>
        <div class="card-body">
            <form id="registerForm" action="index.php?page=register_pelanggan_process" method="POST">
                <div class="mb-4">
                    <label for="nama" class="form-label required-field">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" id="nama" required placeholder="Masukkan nama lengkap Anda">
                </div>

                <div class="mb-4">
                    <label for="email" class="form-label required-field">Email</label>
                    <input type="email" name="email" class="form-control" id="email" required placeholder="johndoe@inigmail.com">
                </div>

                <div class="mb-4 password-container">
                    <label for="password" class="form-label required-field">Password</label>
                    <input type="password" name="password" class="form-control" id="password" required minlength="6" placeholder="Buat password Anda">
                    <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                    <div class="input-hint">Minimal 6 karakter</div>
                </div>

                <div class="mb-4">
                    <label for="telepon" class="form-label">Nomor Telepon</label>
                    <input type="number" name="telepon" class="form-control" id="telepon" pattern="[0-9]{10,15}" placeholder="081234567890">
                    <div class="input-hint">Opsional (10-15 digit angka)</div>
                </div>

                <div class="mb-4">
                    <label for="alamat" class="form-label required-field">Alamat</label>
                    <textarea name="alamat" class="form-control" id="alamat" required rows="3" placeholder="Masukkan alamat lengkap"></textarea>
                </div>

                <button type="submit" class="btn btn-register">
                    <i class="fas fa-user-plus me-2"></i> Daftar Sekarang
                </button>

                <div class="login-link">
                    Sudah punya akun? <a href="index.php?page=login_pelanggan">Masuk disini</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle password visibility
            $('#togglePassword').click(function() {
                const passwordInput = $('#password');
                const icon = $('#iconToggle');
                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordInput.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            // Form validation + AJAX submit
            $("#registerForm").validate({
                rules: {
                    nama: "required",
                    email: {
                        required: true,
                        email: true
                    },
                    password: {
                        required: true,
                        minlength: 6
                    },
                    alamat: "required"
                },
                messages: {
                    nama: "Silakan masukkan nama lengkap",
                    email: {
                        required: "Silakan masukkan email",
                        email: "Format email tidak valid"
                    },
                    password: {
                        required: "Silakan masukkan password",
                        minlength: "Password minimal 6 karakter"
                    },
                    alamat: "Silakan masukkan alamat"
                },
                errorElement: "div",
                highlight: function(element) {
                    $(element).addClass("is-invalid").removeClass("is-valid");
                },
                unhighlight: function(element) {
                    $(element).removeClass("is-invalid").addClass("is-valid");
                },
                submitHandler: function(form, event) {
                    event.preventDefault();
                    $('.btn-register').html('<i class="fas fa-spinner fa-spin me-2"></i> Memproses...');
                    $('.btn-register').prop('disabled', true);

                    $.ajax({
                        url: $(form).attr('action'),
                        type: 'POST',
                        data: $(form).serialize(),
                        dataType: 'json',
                        success: function(response) {
                            console.log(response);
                            if (response.success) {
                                Swal.fire({
                                    title: 'Pendaftaran Berhasil!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonColor: '#3085d6',
                                    confirmButtonText: 'Lanjutkan'
                                }).then(() => {
                                    window.location.href = response.redirect;
                                });
                            } else {
                                Swal.fire({
                                    title: 'Pendaftaran Gagal',
                                    text: response.message,
                                    icon: 'error',
                                    confirmButtonColor: '#3085d6',
                                    confirmButtonText: 'Mengerti'
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMsg = 'Terjadi kesalahan saat memproses data';
                            try {
                                const response = JSON.parse(xhr.responseText);
                                errorMsg = response.message || errorMsg;
                            } catch (e) {}

                            Swal.fire({
                                title: 'Error',
                                text: errorMsg,
                                icon: 'error'
                            });
                        },
                        complete: function() {
                            $('.btn-register').html('<i class="fas fa-user-plus me-2"></i> Daftar Sekarang');
                            $('.btn-register').prop('disabled', false);
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>