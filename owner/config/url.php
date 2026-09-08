<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| TENANT ACCESS URL HELPER
|--------------------------------------------------------------------------
|
| Hujenga full access URL (e.g. http://localhost/Clan_Management_System/)
| kwa kila clan kulingana na domain_name iliyohifadhiwa kwenye
| owner_installations / owner_activations.
|
| domain_name inaweza kuwa:
|   - host tu        : "localhost"            -> http://localhost/Clan_Management_System/
|   - host + port    : "clan1.site.com:8080"  -> http://clan1.site.com:8080/Clan_Management_System/
|   - full URL       : "https://clan1.site.com/x" -> hutumika kama ilivyo
|
| Hii inaruhusu baadaye, mtu akipata domain halisi, kuweka domain hiyo
| kwenye domain_name na mfumo ukajenga link sahihi.
|--------------------------------------------------------------------------
*/

function clanAccessUrl(?string $domain): string
{
    $appPath = defined('CLIENT_APP_PATH')
        ? CLIENT_APP_PATH
        : '/Clan_Management_System';

    // Ensure a leading slash and no trailing path confusion.
    $appPath = '/' . ltrim($appPath, '/');

    $raw = trim((string)$domain);

    if ($raw === '' || $raw === '-' || strtolower($raw) === 'null') {
        $raw = 'localhost';
    }

    // If it is already a full URL (with scheme), sanitize and use verbatim.
    if (preg_match('#^(https?)://#i', $raw, $m)) {

        $scheme = strtolower($m[1]);

        $rest = preg_replace('#^https?://#i', '', $raw);

        $host = parse_url('http://' . $rest, PHP_URL_HOST);
        $port = parse_url('http://' . $rest, PHP_URL_PORT);

        if ($host === null || $host === false || $host === '') {
            $host = $rest;
        }

        $hostPort = $host . ($port ? ':' . $port : '');

        return $scheme . '://' . $hostPort . $appPath . '/';
    }

    // Host-only (with optional port).
    $host = $raw;

    if (preg_match('#^[0-9.:\[\]a-zA-Z-]+$#', $host) === 0) {

        // Sanitize any path that may have slipped in.
        $host = preg_replace('#/.*$#', '', $host);
    }

    return 'http://' . $host . $appPath . '/';
}
