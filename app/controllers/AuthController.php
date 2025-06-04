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
        $nama = $_POST['nama'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $conn = (new Database())->getConnection();

        // Cek jika email sudah ada
        $stmt = $conn->prepare("SELECT * FROM pelanggan WHERE email_pelanggan = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $this->redirectWithError('../pelanggan/register.php', 'Email sudah digunakan!');
        } else {
            // Insert pelanggan baru
            $stmt = $conn->prepare("INSERT INTO pelanggan (nama_pelanggan, email_pelanggan, password_pelanggan) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nama, $email, $hashedPassword);
            if ($stmt->execute()) {
                header("Location: ../pelanggan/login.php");
                exit;
            } else {
                $this->redirectWithError('../pelanggan/register.php', 'Gagal registrasi, coba lagi.');
            }
        }
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
