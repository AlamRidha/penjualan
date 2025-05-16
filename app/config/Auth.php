<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'Database.php';

class Auth
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Login untuk admin
    public function loginAdmin($username, $password)
    {
        $stmt = $this->conn->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();

        // if ($admin && password_verify($password, $admin['password'])) {
        if ($admin && $password) {
            $_SESSION['admin'] = [
                'id_admin' => $admin['id_admin'],
                'username' => $admin['username'],
                'nama_lengkap' => $admin['nama_lengkap']
            ];
            return true;
        }
        return false;
    }

    // Login untuk pelanggan
    public function loginPelanggan($email, $password)
    {
        $stmt = $this->conn->prepare("SELECT * FROM pelanggan WHERE email_pelanggan = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $pelanggan = $result->fetch_assoc();

        if ($pelanggan && password_verify($password, $pelanggan['password_pelanggan'])) {
            $_SESSION['pelanggan'] = [
                'id_pelanggan' => $pelanggan['id_pelanggan'],
                'nama_pelanggan' => $pelanggan['nama_pelanggan'],
                'email_pelanggan' => $pelanggan['email_pelanggan']
            ];
            return true;
        }
        return false;
    }

    // Cek apakah admin sudah login
    public function isAdminLoggedIn()
    {
        return isset($_SESSION['admin']);
    }

    // Cek apakah pelanggan sudah login
    public function isPelangganLoggedIn()
    {
        return isset($_SESSION['pelanggan']);
    }

    // Logout
    public function logout()
    {
        session_unset();
        session_destroy();
    }
}
