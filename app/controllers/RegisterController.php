<?php

if (session_status() === PHP_SESSION_NONE) session_start();
require_once base_path('app/controllers/UserController.php');
require_once base_path('app/helpers/functions.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $result = registerUser($username, $password, 'customer');

    if ($result['success']) {
        $_SESSION['register_success'] = true;
        header('Location: ' . base_url('index.php?page=register'));
    } else {
        header('Location: ' . base_url(
            'index.php?page=register&error=' . urlencode($result['error'])
        ));
    }
    exit;
}
