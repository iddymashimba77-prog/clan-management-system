<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Check Login
|--------------------------------------------------------------------------
*/

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/*
|--------------------------------------------------------------------------
| Require Login
|--------------------------------------------------------------------------
*/

function requireLogin(): void
{
    if (!isLoggedIn()) {

        header("Location: /Clan_Management_System/login.php");

        exit;
    }
}

/*
|--------------------------------------------------------------------------
| Check Role
|--------------------------------------------------------------------------
*/

function hasRole(int $roleId): bool
{
    return isset($_SESSION['role_id']) && $_SESSION['role_id'] == $roleId;
}