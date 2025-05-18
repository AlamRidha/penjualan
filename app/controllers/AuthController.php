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
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($this->auth->loginAdmin($username, $password)) {
            header("Location: " . base_url('index.php?page=admin/dashboard'));
            exit;
        } else {
            $this->redirectWithError('../admin/login.php', 'Username atau Password admin salah!');
        }
    }

    // Login pelanggan
    public function loginPelanggan()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        if ($this->auth->loginPelanggan($email, $password)) {
            header("Location:" . base_url('index.php?page=pelanggan/dashboard'));
            exit;
        } else {
            $this->redirectWithError('../pelanggan/login.php', 'Email atau Password pelanggan salah!');
        }
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
}
