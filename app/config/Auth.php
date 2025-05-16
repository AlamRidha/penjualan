<?php
function isLoggedIn()
{
    return isset($_SESSION['user']);
}

function isAdmin()
{
    return isLoggedIn() && $_SESSION['user']['role'] === 'admin';
}

function isCustomer()
{
    return isLoggedIn() && $_SESSION['user']['role'] === 'customer';
}
