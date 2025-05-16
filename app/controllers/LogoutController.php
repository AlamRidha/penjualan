<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_destroy();
header("Location:" . base_url("index.php?page=login"));
exit;
