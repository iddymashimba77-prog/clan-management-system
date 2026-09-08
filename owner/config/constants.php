<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| OWNER APPLICATION CONSTANTS
|--------------------------------------------------------------------------
*/

define(
    'OWNER_APP_NAME',
    'Clan Management System - Owner Panel'
);

/*
|--------------------------------------------------------------------------
| OWNER BASE URL
|--------------------------------------------------------------------------
|
| Local development:
| /Clan_Management_System/owner
|
*/

define(
    'OWNER_BASE_URL',
    '/Clan_Management_System/owner'
);

/*
|--------------------------------------------------------------------------
| CLIENT APPLICATION PATH
|--------------------------------------------------------------------------
|
| Jina la folda/path kwenye server ambapo client (tenant) app ipo.
| Inatumiwa kujenga full access URL kwa kila clan (\""http://...\"").
|
*/

define(
    'CLIENT_APP_PATH',
    '/Clan_Management_System'
);

/*
|--------------------------------------------------------------------------
| CLIENT APPLICATION BASE URL
|--------------------------------------------------------------------------
|
| Hii haitumiki ku-connect kwenye database ya client.
| Ni kwa kutengeneza activation URL/reference tu.
|
*/

define(
    'CLIENT_APP_NAME',
    'Clan Management System'
);

/*
|--------------------------------------------------------------------------
| DEFAULT ACTIVATION VALIDITY
|--------------------------------------------------------------------------
*/

define(
    'ACTIVATION_VALIDITY_HOURS',
    24
);

/*
|--------------------------------------------------------------------------
| OWNER ACTIVATION API
|--------------------------------------------------------------------------
*/

if (!defined('OWNER_ACTIVATION_API')) {

    /*
    |--------------------------------------------------------------------------
    | DYNAMIC ACTIVATION API URL
    |--------------------------------------------------------------------------
    |
    | In local development the app may be reached via "localhost",
    | "127.0.0.1" or a LAN IP. A hard-coded "http://localhost/..." URL
    | fails to connect ("Cannot connect to API") whenever the browser or
    | server is addressed through a different host. Deriving the API URL
    | from the current request guarantees the client can always reach it.
    |
    */

    $scheme =
        (
            !empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off'
        )
            ? 'https'
            : 'http';

    $host =
        (string)($_SERVER['HTTP_HOST'] ?? '');

    if ($host === '') {

        $host =
            'localhost';
    }

    define(
        'OWNER_ACTIVATION_API',
        $scheme . '://' . $host . CLIENT_APP_PATH . '/owner/api/activation.php'
    );
}