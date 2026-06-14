<?php

require_once __DIR__ . '/session.php';

/*Check if user is logged in*/
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/*Redirect user to login page if not logged in*/
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header("Location: /clan_management_system/login.php");
        exit;
    }
}

/*Check if logged in user has required role*/
function requireRole(int $roleId): void
{
    requireLogin();

    if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != $roleId) {
        header("Location: /clan_management_system/login.php");
        exit;
    }
}

/*Get logged in user id*/
function getUserId(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

/*Get logged in user's role*/
function getRoleId(): ?int
{
    return $_SESSION['role_id'] ?? null;
}

