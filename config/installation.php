<?php

declare(strict_types=1);


/*
|--------------------------------------------------------------------------
| Installation Configuration
|--------------------------------------------------------------------------
*/


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| Installation ID
|--------------------------------------------------------------------------
|
| Every production installation gets its own unique identifier.
|
*/

function getInstallationId(): string
{
    $file = dirname(__DIR__) . '/storage/installation.id';

    if (is_file($file)) {

        $id = trim(
            (string) file_get_contents($file)
        );

        if ($id !== '') {
            return $id;
        }
    }


    /*
     * Create installation ID if it does not exist.
     */

    $id = hash(
        'sha256',
        bin2hex(random_bytes(32)) .
        '|' .
        microtime(true)
    );


    $directory = dirname($file);

    if (!is_dir($directory)) {

        mkdir(
            $directory,
            0755,
            true
        );
    }


    file_put_contents(
        $file,
        $id,
        LOCK_EX
    );


    return $id;
}


/*
|--------------------------------------------------------------------------
| Current Domain
|--------------------------------------------------------------------------
*/

function getCurrentDomain(): string
{
    $host = $_SERVER['HTTP_HOST'] ?? '';

    $host = strtolower(
        trim($host)
    );

    /*
     * Remove port.
     */

    $host = preg_replace(
        '/:\d+$/',
        '',
        $host
    );

    return $host ?: 'localhost';
}


/*
|--------------------------------------------------------------------------
| Installation Binding
|--------------------------------------------------------------------------
*/

function getInstallationBinding(): string
{
    return hash(
        'sha256',
        getInstallationId() .
        '|' .
        getCurrentDomain()
    );
}


/*
|--------------------------------------------------------------------------
| Check Installation
|--------------------------------------------------------------------------
*/

function installationExists(PDO $pdo): bool
{
    try {

        $stmt = $pdo->query("
            SELECT activation_id
            FROM installation_activations
            LIMIT 1
        ");

        return (bool) $stmt->fetch();

    } catch (Throwable $e) {

        return false;
    }
}


/*
|--------------------------------------------------------------------------
| Check Installation Lock
|--------------------------------------------------------------------------
*/

function isInstallationLocked(PDO $pdo): bool
{
    try {

        $stmt = $pdo->query("
            SELECT status
            FROM installation_activations
            LIMIT 1
        ");

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        return in_array(
            $row['status'],
            [
                'used',
                'locked'
            ],
            true
        );

    } catch (Throwable $e) {

        return false;
    }
}


/*
|--------------------------------------------------------------------------
| Get Installation Information
|--------------------------------------------------------------------------
*/

function getInstallationInformation(PDO $pdo): ?array
{
    try {

        $stmt = $pdo->query("
            SELECT
                ia.*,
                u.username,
                u.email,
                u.first_name,
                u.last_name
            FROM installation_activations ia
            INNER JOIN users u
                ON u.user_id = ia.admin_user_id
            LIMIT 1
        ");

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;

    } catch (Throwable $e) {

        return null;
    }
}