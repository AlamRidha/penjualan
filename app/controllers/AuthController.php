<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Koneksi database
    $database = new Database();
    $conn = $database->getConnection();

    // Cek user berdasarkan username
    $query = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();
    $user = $result->fetch_assoc();

    // Validasi user
    if ($user && password_verify($password, $user['password'])) {
        // if ($user && $password) {
        $_SESSION['user'] = [
            'id' => $user['id_user'],
            'username' => $user['username'],
            'role' => $user['Role']
        ];

        // Set status login success
        $_SESSION['login_success'] = true;
        $_SESSION['login_message'] = 'Login berhasil! Selamat datang, ' . $user['username'];

        // if ($user['Role'] === 'admin') {
        //     header("Location: " . base_url('index.php?page=dashboard'));
        // } else {
        //     header("Location: " . base_url('index.php?page=home'));
        // }
        header("Location: " . base_url('index.php?page=login'));
        exit;
    } else {
        $error = "Username atau password salah!";
        header("Location: " . base_url('index.php?page=login&error=' . urlencode($error)));
        exit;
    }
}
