<?php
require_once __DIR__ . '/../config/Auth.php';

class AuthController
{
    private $auth;

    public function __construct()
    {
        $this->auth = new Auth();
    }

    // Login admin
    public function loginAdmin()
    {
        header('Content-Type: application/json');
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($this->auth->loginAdmin($username, $password)) {
            echo json_encode([
                'success' => true,
                'message' => 'Selamat datang, ' . $_SESSION['admin']['username'] . ' 👋',
                'redirect' => base_url('index.php?page=admin/dashboard')
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Username atau Password Admin Salah!',
            ]);
        }

        exit;
    }

    // Login pelanggan
    public function loginPelanggan()
    {
        header('Content-Type: application/json');

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($this->auth->loginPelanggan($email, $password)) {
            echo json_encode([
                'success' => true,
                'message' => 'Login pelanggan berhasil!',
                'redirect' => base_url('index.php?page=pelanggan/dashboard')
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Email atau Password pelanggan salah!'
            ]);
        }
        exit;
    }

    // Logout (digunakan untuk admin maupun pelanggan)
    public function logoutAdmin()
    {
        $this->auth->logout();
        header("Location:" . base_url('index.php?page=login_admin'));
        exit;
    }

    // Fungsi bantu redirect dengan pesan error
    private function redirectWithError($location, $message)
    {
        session_start();
        $_SESSION['error'] = $message;
        header("Location: $location");
        exit;
    }

    public function registerPelanggan()
    {
        header('Content-Type: application/json');

        try {
            $nama = $_POST['nama'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $telepon = $_POST['telepon'] ?? '';
            $alamat = $_POST['alamat'] ?? '';

            // Validasi input
            if (empty($nama) || empty($email) || empty($password) || empty($alamat)) {
                throw new Exception('Semua field wajib harus diisi!');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Format email tidak valid!');
            }

            if (strlen($password) < 6) {
                throw new Exception('Password minimal 6 karakter!');
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $conn = (new Database())->getConnection();

            // Cek jika email sudah ada
            $stmt = $conn->prepare("SELECT * FROM pelanggan WHERE email_pelanggan = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                throw new Exception('Email sudah digunakan!');
            }

            // Insert pelanggan baru
            $stmt = $conn->prepare("INSERT INTO pelanggan (nama_pelanggan, email_pelanggan, password_pelanggan, telephone_pelanggan, alamat_pelanggan) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nama, $email, $hashedPassword, $telepon, $alamat);

            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Registrasi berhasil! Silakan login.',
                    'redirect' => base_url('index.php?page=login_pelanggan')
                ]);
            } else {
                throw new Exception('Gagal registrasi, coba lagi.');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    public function logoutPelanggan()
    {
        $this->auth->logout();
        header("Location:" . base_url('index.php?page=login_pelanggan'));
        exit;
    }


    public function logoutWithConfirmation($type)
    {
        header('Content-Type: application/json');
        $this->auth->logout();

        echo json_encode([
            'success' => true,
            'message' => 'Logout berhasil, Anda Akan Diarahkan Ke Halaman Login',
            'redirect' => ($type === 'admin') ? base_url('index.php?page=login_admin') : base_url('index.php?page=login_pelanggan')
        ]);

        exit;
    }
}
