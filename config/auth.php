<?php

declare(strict_types=1);

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/permissions.php';
require_once __DIR__ . '/tenant.php';


/*
|--------------------------------------------------------------------------
| REQUIRE LOGIN
|--------------------------------------------------------------------------
*/

function requireLogin(): void
{
    if (empty($_SESSION['user_id'])) {

        header(
            'Location: ' .
            BASE_URL .
            'login.php'
        );

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| REQUIRE ACTIVE ACCOUNT
|--------------------------------------------------------------------------
*/

function requireActiveAccount(): void
{
    requireLogin();

    if (
        empty($_SESSION['user_id']) ||
        empty($_SESSION['role_id'])
    ) {

        $_SESSION = [];

        header(
            'Location: ' .
            BASE_URL .
            'login.php'
        );

        exit;
    }

    requireTenantContext();
}


/*
|--------------------------------------------------------------------------
| REQUIRE ADMINISTRATOR
|--------------------------------------------------------------------------
*/

function requireAdministrator(): void
{
    requireActiveAccount();

    if (!isAdministrator()) {

        http_response_code(403);

        exit('Access Denied');
    }
}


/*
|--------------------------------------------------------------------------
| REQUIRE CHAIRPERSON
|--------------------------------------------------------------------------
*/

function requireChairperson(): void
{
    requireActiveAccount();

    if (!isChairperson()) {

        http_response_code(403);

        exit('Access Denied');
    }
}


/*
|--------------------------------------------------------------------------
| REQUIRE SECRETARY
|--------------------------------------------------------------------------
*/

function requireSecretary(): void
{
    requireActiveAccount();

    if (!isSecretary()) {

        http_response_code(403);

        exit('Access Denied');
    }
}


/*
|--------------------------------------------------------------------------
| REQUIRE TREASURER
|--------------------------------------------------------------------------
*/

function requireTreasurer(): void
{
    requireActiveAccount();

    if (!isTreasurer()) {

        http_response_code(403);

        exit('Access Denied');
    }
}


/*
|--------------------------------------------------------------------------
| REQUIRE MEMBER
|--------------------------------------------------------------------------
|
| Any authenticated clan member can access this.
|--------------------------------------------------------------------------
*/

function requireMember(): void
{
    requireActiveAccount();

    if (
        !hasAnyRole([
            'Member',
            'Chairperson',
            'Secretary',
            'Treasurer',
            'Administrator'
        ])
    ) {

        http_response_code(403);

        exit('Access Denied');
    }

    requireActiveClan();
}


/*
|--------------------------------------------------------------------------
| REQUIRE ANY CLAN ROLE
|--------------------------------------------------------------------------
*/

function requireClanUser(): void
{
    requireActiveAccount();

    if (
        !hasAnyRole([
            'Member',
            'Chairperson',
            'Secretary',
            'Treasurer',
            'Administrator'
        ])
    ) {

        http_response_code(403);

        exit('Access Denied');
    }

    requireActiveClan();
}


/*
|--------------------------------------------------------------------------
| REQUIRE ROLE BY NAME
|--------------------------------------------------------------------------
*/

function requireRoleAccess(string $roleName): void
{
    requireActiveAccount();

    requireRole($roleName);
}


/*
|--------------------------------------------------------------------------
| REQUIRE ANY SPECIFIED ROLE
|--------------------------------------------------------------------------
*/

function requireAnyRoleAccess(array $roles): void
{
    requireActiveAccount();

    requireAnyRole($roles);
}


/*
|--------------------------------------------------------------------------
| REDIRECT IF ALREADY LOGGED IN
|--------------------------------------------------------------------------
*/

function redirectIfAuthenticated(): void
{
    if (!empty($_SESSION['user_id'])) {

        redirectDashboard();
    }
}
