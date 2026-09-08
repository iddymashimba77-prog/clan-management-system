<?php

declare(strict_types=1);

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/tenant.php';
require_once __DIR__ . '/config/permissions.php';
require_once __DIR__ . '/config/clan_db.php';


/*
|--------------------------------------------------------------------------
| REQUEST METHOD
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: ' . BASE_URL . 'login.php');

    exit;
}


/*
|--------------------------------------------------------------------------
| CSRF PROTECTION
|--------------------------------------------------------------------------
*/

if (!verify_csrf()) {

    $_SESSION['error'] =
        'Invalid security token. Please try again.';

    header('Location: ' . BASE_URL . 'login.php');

    exit;
}


/*
|--------------------------------------------------------------------------
| LOGIN INPUT
|--------------------------------------------------------------------------
*/

$login =
    trim((string) ($_POST['login'] ?? ''));

$password =
    (string) ($_POST['password'] ?? '');


if (
    $login === '' ||
    $password === ''
) {

    $_SESSION['error'] =
        'Please enter your login details and password.';

    header('Location: ' . BASE_URL . 'login.php');

    exit;
}


/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
|
| Database-per-clan: determine which database holds this user's account.
| The legacy shared DB is tried first, then every registered clan database,
| so each client logs into their own dedicated database.
|--------------------------------------------------------------------------
*/

try {

    $user = null;
    $pdo  = null;
    $resolvedDatabaseName = null;

    foreach (getTenantDatabaseCandidates() as $candidateDb) {

        $candidate = new Database($candidateDb);

        $candidatePdo = $candidate->connect();

        /*
         * Find user.
         *
         * Supported login identifiers:
         * Member Code
         * Username
         * Email
         * Phone
         */

        $stmt = $candidatePdo->prepare("
            SELECT
                u.*,
                r.role_name
            FROM users u

            LEFT JOIN roles r
                ON r.role_id = u.role_id

            WHERE
                (
                    u.member_code = ?
                    OR u.username = ?
                    OR u.email = ?
                    OR u.phone = ?
                )

                AND u.is_deleted = 0

            LIMIT 1
        ");

        $stmt->execute([
            $login,
            $login,
            $login,
            $login
        ]);

        $candidateUser =
            $stmt->fetch(PDO::FETCH_ASSOC);

        /*
         * Verify the password before accepting this database.
         */

        if (
            $candidateUser &&
            !empty($candidateUser['password_hash']) &&
            password_verify($password, $candidateUser['password_hash'])
        ) {

            $user = $candidateUser;
            $pdo  = $candidatePdo;
            $resolvedDatabaseName = $candidateDb;
            break;
        }
    }


    /*
     |--------------------------------------------------------------------------
     | USER NOT FOUND
     |--------------------------------------------------------------------------
     */

    if (!$user) {

        $_SESSION['error'] =
            'Invalid login credentials.';

        header('Location: ' . BASE_URL . 'login.php');

        exit;
    }


    /*
     |--------------------------------------------------------------------------
     | ACCOUNT STATUS
     |--------------------------------------------------------------------------
     */

    if (
        ($user['account_status'] ?? '') !== 'active'
    ) {

        $_SESSION['error'] =
            'Your account is inactive. Please contact the administrator.';

        header('Location: ' . BASE_URL . 'login.php');

        exit;
    }


    /*
     |--------------------------------------------------------------------------
     | REHASH PASSWORD IF NEEDED
     |--------------------------------------------------------------------------
     */

    if (
        password_needs_rehash(
            $user['password_hash'],
            PASSWORD_DEFAULT
        )
    ) {

        $newHash =
            password_hash(
                $password,
                PASSWORD_DEFAULT
            );

        $stmt =
            $pdo->prepare("
                UPDATE users
                SET password_hash = ?
                WHERE user_id = ?
            ");

        $stmt->execute([
            $newHash,
            $user['user_id']
        ]);
    }


    /*
     |--------------------------------------------------------------------------
     | SECURE SESSION
     |--------------------------------------------------------------------------
     */

    session_regenerate_id(true);


    /*
     |--------------------------------------------------------------------------
     | SESSION DATA
     |--------------------------------------------------------------------------
     */

    $_SESSION['user_id'] =
        (int) $user['user_id'];

    $_SESSION['clan_id'] =
        (int) ($user['clan_id'] ?? 1);

    if ($resolvedDatabaseName !== null && $resolvedDatabaseName !== '') {
        $_SESSION['database_name'] = $resolvedDatabaseName;
    } else {
        // If no specific database was resolved, try to get it from current domain
        $domainDatabase = getCurrentDomainDatabase();
        if ($domainDatabase !== null && $domainDatabase !== '') {
            $_SESSION['database_name'] = $domainDatabase;
        }
    }

    $_SESSION['username'] =
        $user['username'];

    $_SESSION['member_code'] =
        $user['member_code'];

    $_SESSION['profile_picture'] =
        ($user['profile_picture_self'] ?? null)
        ?: ($user['profile_picture'] ?? null);

    $_SESSION['role_id'] =
        (int) $user['role_id'];

    $_SESSION['role_name'] =
        $user['role_name'] ?? 'Member';

    $_SESSION['full_name'] =
        trim(
            ($user['first_name'] ?? '') . ' ' .
            ($user['middle_name'] ?? '') . ' ' .
            ($user['last_name'] ?? '')
        );

    $_SESSION['last_activity'] =
        time();


    /*
     |--------------------------------------------------------------------------
     | LOAD RBAC ROLES
     |--------------------------------------------------------------------------
     */

    $_SESSION['roles'] =
        getUserRoles(
            (int) $user['user_id']
        );


    /*
     |--------------------------------------------------------------------------
     | LOGIN ACTIVITY LOG
     |--------------------------------------------------------------------------
     */

    try {

        $stmt = $pdo->prepare("
            INSERT INTO activity_logs
            (
                user_id,
                action,
                table_name,
                record_id,
                description,
                ip_address
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            )
        ");

        $stmt->execute([

            (int) $user['user_id'],

            'LOGIN',

            'users',

            (int) $user['user_id'],

            'User logged into the system successfully.',

            $_SERVER['REMOTE_ADDR'] ?? null

        ]);

    } catch (Throwable $e) {

        /*
         * Do not prevent login if logging fails.
         */

    }


    /*
     |--------------------------------------------------------------------------
     | REDIRECT
     |--------------------------------------------------------------------------
     */

    redirectDashboard();


} catch (Throwable $e) {

    error_log(
        'Login error: ' . $e->getMessage()
    );

    $_SESSION['error'] =
        'Unable to process login at this time. Please try again.';

    header(
        'Location: ' . BASE_URL . 'login.php'
    );

    exit;
}
