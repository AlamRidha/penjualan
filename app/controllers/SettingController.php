<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once base_path('app/config/Database.php');
require_once base_path('app/config/Auth.php');

$db = new Database();
$conn = $db->getConnection();

function updateUserSettings($data)
{
    global $conn;

    if (!isLoggedIn()) {
        return ['success' => false, 'error' => 'unauthorized'];
    }

    $user_id = $_SESSION['user']['id_user'];
    $username = mysqli_real_escape_string($conn, $data['username']);

    // Validasi kosong
    if (empty($username)) {
        return ['success' => false, 'error' => 'empty_username'];
    }

    // Cek username unik
    $check_query = "SELECT id_user FROM users WHERE username = '$username' AND id_user != $user_id";
    $check_result = mysqli_query($conn, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        return ['success' => false, 'error' => 'username_taken'];
    }

    $query = "UPDATE users SET username = '$username'";
    if (!empty($data['password'])) {
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        $query .= ", password = '$password'";
    }
    $query .= " WHERE id_user = $user_id";

    if (mysqli_query($conn, $query)) {
        $_SESSION['user']['username'] = $username;
        return ['success' => true];
    }

    return ['success' => false, 'error' => 'update_failed'];
}
