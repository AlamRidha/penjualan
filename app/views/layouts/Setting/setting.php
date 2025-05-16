<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Tambahkan ini untuk akses fungsi base_path()
require_once __DIR__ . "../../../../helpers/functions.php";

header('Content-Type: application/json');

require_once base_path('app/config/Auth.php');
require_once base_path('app/controllers/SettingController.php');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = [
        'username' => $_POST['username'] ?? '',
        'password' => $_POST['password'] ?? ''
    ];

    $result = updateUserSettings($input);
    echo json_encode($result);
    exit;
}

echo json_encode(['success' => false, 'error' => 'invalid_request']);
