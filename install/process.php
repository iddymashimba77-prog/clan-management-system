<?php


declare(strict_types=1);

session_start();

require_once dirname(__DIR__) . '/config/installation.php';
require_once dirname(__DIR__) . '/config/activation.php';

/*
|--------------------------------------------------------------------------
| INSTALLATION LOCK
|--------------------------------------------------------------------------
*/

$storageDir = dirname(__DIR__) . '/storage';
$lockFile   = $storageDir . '/installed.lock';

if (is_file($lockFile)) {
    http_response_code(403);
    exit('Installation has already been completed.');
}


/*
|--------------------------------------------------------------------------
| FAILURE HANDLER
|--------------------------------------------------------------------------
*/

function failInstallation(string $message): never
{
    $_SESSION['install_error'] = $message;

    header('Location: index.php');

    exit;
}


/*
|--------------------------------------------------------------------------
| INPUT
|--------------------------------------------------------------------------
*/

$dbHost = trim($_POST['db_host'] ?? 'localhost');
$dbName = trim($_POST['db_name'] ?? '');
$dbUser = trim($_POST['db_user'] ?? '');
$dbPass = $_POST['db_password'] ?? '';

$firstName = ucwords(strtolower(trim($_POST['first_name'] ?? '')));
$lastName  = ucwords(strtolower(trim($_POST['last_name'] ?? '')));
$username  = trim($_POST['username'] ?? '');
$email     = trim($_POST['email'] ?? '');

$password = $_POST['password'] ?? '';
$passwordConfirmation = $_POST['password_confirmation'] ?? '';

$confirm = $_POST['confirm'] ?? '';


/*
|--------------------------------------------------------------------------
| VALIDATION
|--------------------------------------------------------------------------
*/

if ($dbHost === '') {
    failInstallation('Database host is required.');
}

if ($dbName === '') {
    failInstallation('Database name is required.');
}

if ($dbUser === '') {
    failInstallation('Database username is required.');
}

if ($firstName === '' || $lastName === '') {
    failInstallation('Administrator first name and last name are required.');
}

if ($username === '') {
    failInstallation('Administrator username is required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    failInstallation('Please enter a valid administrator email.');
}

if (strlen($password) < 8) {
    failInstallation(
        'Administrator password must contain at least 8 characters.'
    );
}

if ($password !== $passwordConfirmation) {
    failInstallation('Passwords do not match.');
}

if ($confirm !== '1') {
    failInstallation(
        'You must confirm the production installation.'
    );
}


/*
|--------------------------------------------------------------------------
| SQL FILE
|--------------------------------------------------------------------------
*/

$sqlFile = dirname(__DIR__) .
    '/database/clan_management_system.sql';

if (!is_file($sqlFile)) {

    failInstallation(
        'SQL database file was not found.'
    );
}


/*
|--------------------------------------------------------------------------
| CONNECT TO MYSQL SERVER
|--------------------------------------------------------------------------
*/

try {

    $pdoServer = new PDO(
        "mysql:host={$dbHost};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

} catch (PDOException $e) {

    failInstallation(
        'Unable to connect to MySQL: ' .
        $e->getMessage()
    );
}


/*
|--------------------------------------------------------------------------
| CREATE DATABASE
|--------------------------------------------------------------------------
*/

try {

    $safeDbName = str_replace('`', '``', $dbName);

    $pdoServer->exec(
        "CREATE DATABASE IF NOT EXISTS `{$safeDbName}`
         CHARACTER SET utf8mb4
         COLLATE utf8mb4_unicode_ci"
    );

} catch (PDOException $e) {

    failInstallation(
        'Unable to create database: ' .
        $e->getMessage()
    );
}


/*
|--------------------------------------------------------------------------
| CONNECT TO DATABASE
|--------------------------------------------------------------------------
*/

try {

    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

} catch (PDOException $e) {

    failInstallation(
        'Unable to connect to database: ' .
        $e->getMessage()
    );
}


/*
|--------------------------------------------------------------------------
| IMPORT SQL DUMP
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| IMPORT DATABASE STRUCTURE
|--------------------------------------------------------------------------
*/

try {

    $sql = file_get_contents($sqlFile);

    if ($sql === false) {

        throw new RuntimeException(
            'Unable to read SQL file.'
        );
    }

    $sql = trim($sql);

    if ($sql === '') {

        throw new RuntimeException(
            'The SQL file is empty.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | IMPORTANT
    |--------------------------------------------------------------------------
    |
    | Do NOT remove /*! ...  MySQL executable comments.
    |
    | mysqldump uses them for commands such as:
    |
    | SET FOREIGN_KEY_CHECKS=0;
    | SET FOREIGN_KEY_CHECKS=1;
    |
    | These are required when importing tables that have
    | foreign-key relationships.
    |
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | Disable foreign-key checking
    |--------------------------------------------------------------------------
    */

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");


    /*
    |--------------------------------------------------------------------------
    | Import SQL dump
    |--------------------------------------------------------------------------
    |
    | PDO::exec() supports multiple SQL statements when used
    | with the MySQL/MariaDB driver.
    |
    */

    $pdo->exec($sql);

    /*
|--------------------------------------------------------------------------
| INSTALL ACTIVATION TABLE
|--------------------------------------------------------------------------
*/

$activationSqlFile =
dirname(__DIR__) .
'/database/activation.sql';


if (!is_file($activationSqlFile)) {

throw new RuntimeException(
    'Activation SQL file was not found.'
);
}


$activationSql =
file_get_contents(
    $activationSqlFile
);


if (
$activationSql === false ||
trim($activationSql) === ''
) {

throw new RuntimeException(
    'Activation SQL file is empty.'
);
}


$pdo->exec(
$activationSql
);


    /*
    |--------------------------------------------------------------------------
    | Re-enable foreign-key checking
    |--------------------------------------------------------------------------
    */

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");


} catch (Throwable $e) {

    /*
    |--------------------------------------------------------------------------
    | Safety: always attempt to restore FK checks
    |--------------------------------------------------------------------------
    */

    try {

        if (isset($pdo)) {

            $pdo->exec(
                "SET FOREIGN_KEY_CHECKS = 1"
            );
        }

    } catch (Throwable $ignored) {
        // Ignore cleanup failure.
    }


    failInstallation(
        'Database structure installation failed: ' .
        $e->getMessage()
    );
}


/*
|--------------------------------------------------------------------------
| SYSTEM DATA
|--------------------------------------------------------------------------
*/

try {

    require_once __DIR__ . '/seed.php';

    seedSystemData($pdo);

} catch (Throwable $e) {

    failInstallation(
        'System data installation failed: ' .
        $e->getMessage()
    );
}


/*
|--------------------------------------------------------------------------
| VERIFY REQUIRED TABLES
|--------------------------------------------------------------------------
*/

try {

    $requiredTables = [
        'users',
        'roles',
        'permissions',
        'role_permissions',
        'user_roles'
    ];

    foreach ($requiredTables as $table) {

        $stmt = $pdo->prepare(
            "SHOW TABLES LIKE ?"
        );

        $stmt->execute([$table]);

        if (!$stmt->fetchColumn()) {

            throw new RuntimeException(
                "Required table '{$table}' was not found."
            );
        }
    }

} catch (Throwable $e) {

    failInstallation(
        'Database verification failed: ' .
        $e->getMessage()
    );
}


/*
|--------------------------------------------------------------------------
| CREATE FIRST ADMINISTRATOR
|--------------------------------------------------------------------------
*/

try {

    $pdo->beginTransaction();


    /*
     * Administrator role must be ID 5.
     */

    $stmt = $pdo->prepare("
        SELECT role_id
        FROM roles
        WHERE role_id = 5
        LIMIT 1
    ");

    $stmt->execute();

    if (!$stmt->fetch()) {

        throw new RuntimeException(
            'Administrator role ID 5 was not found.'
        );
    }


    /*
     * Check username.
     */

    $stmt = $pdo->prepare("
        SELECT user_id
        FROM users
        WHERE username = ?
        LIMIT 1
    ");

    $stmt->execute([$username]);

    if ($stmt->fetch()) {

        throw new RuntimeException(
            'Administrator username already exists.'
        );
    }


    /*
     * Check email.
     */

    $stmt = $pdo->prepare("
        SELECT user_id
        FROM users
        WHERE email = ?
        LIMIT 1
    ");

    $stmt->execute([$email]);

    if ($stmt->fetch()) {

        throw new RuntimeException(
            'Administrator email already exists.'
        );
    }


    /*
     * Password hash.
     */

    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );


    /*
     * Member code.
     */

    $stmt = $pdo->query("
        SELECT COALESCE(MAX(user_id), 0) AS max_id
        FROM users
    ");

    $row = $stmt->fetch();

    $nextId = ((int)$row['max_id']) + 1;

    $memberCode =
        'CLAN-' .
        str_pad(
            (string)$nextId,
            7,
            '0',
            STR_PAD_LEFT
        );


    /*
     * Detect actual users columns.
     */

    $columnsStmt = $pdo->query("
        DESCRIBE users
    ");

    $columns = [];

    foreach ($columnsStmt->fetchAll() as $column) {
        $columns[] = $column['Field'];
    }


    /*
     * IMPORTANT:
     *
     * gender is NOT NULL in your schema.
     *
     * We therefore provide a valid value.
     */
    $data = [

        'member_code' => $memberCode,

        'first_name' => $firstName,

        'last_name' => $lastName,

        'username' => $username,

        'email' => $email,

        'password_hash' => $passwordHash,

        'gender' => 'Male',

        'marital_status' => 'Single',

        'role_id' => 5,

        'is_deceased' => 0,

        'account_status' => 'active'

    ];


    /*
     * Only insert columns that actually exist.
     */

    $insertData = [];

    foreach ($data as $column => $value) {

        if (in_array($column, $columns, true)) {
            $insertData[$column] = $value;
        }
    }


    /*
     * Required fields.
     */

    foreach (
        [
            'first_name',
            'last_name',
            'username',
            'email',
            'password_hash',
            'gender',
            'role_id'
        ] as $requiredColumn
    ) {

        if (
            !array_key_exists(
                $requiredColumn,
                $insertData
            )
        ) {

            throw new RuntimeException(
                "Required users column '{$requiredColumn}' was not found."
            );
        }
    }


    $columnNames = array_keys($insertData);

    $quotedColumns = array_map(
        static fn(string $column): string =>
            '`' . str_replace('`', '``', $column) . '`',
        $columnNames
    );

    $placeholders = array_fill(
        0,
        count($columnNames),
        '?'
    );


    $insertSql = "
        INSERT INTO users
        (" . implode(',', $quotedColumns) . ")
        VALUES
        (" . implode(',', $placeholders) . ")
    ";


    $stmt = $pdo->prepare($insertSql);

    $stmt->execute(
        array_values($insertData)
    );

   

    $administratorUserId =
        (int)$pdo->lastInsertId();

        /*
|--------------------------------------------------------------------------
| CREATE INSTALLATION ACTIVATION
|--------------------------------------------------------------------------
*/

try {

    /*
     * Generate one-time activation token
     * for this production installation.
     */

    $activation =
        generateActivationToken(
            $pdo,
            $administratorUserId
        );


    $_SESSION['activation_token'] =
        $activation['token'];

    $_SESSION['activation_url'] =
        buildActivationUrl(
            $activation['token']
        );

    $_SESSION['activation_expires_at'] =
        $activation['expires_at'];


} catch (Throwable $e) {

    throw new RuntimeException(
        'Installation activation could not be created: ' .
        $e->getMessage()
    );
}

    /*
     * user_roles
     */

    $roleColumnsStmt = $pdo->query("
        DESCRIBE user_roles
    ");

    $roleColumns = [];

    foreach ($roleColumnsStmt->fetchAll() as $column) {
        $roleColumns[] = $column['Field'];
    }


    if (
        in_array('user_id', $roleColumns, true) &&
        in_array('role_id', $roleColumns, true)
    ) {

        /*
         * Prevent duplicate relationship.
         */
        $stmt = $pdo->prepare("
            SELECT 1
            FROM user_roles
            WHERE user_id = ?
              AND role_id = ?
            LIMIT 1
        ");

        $stmt->execute([
            $administratorUserId,
            5
        ]);


        if (!$stmt->fetch()) {

            $stmt = $pdo->prepare("
                INSERT INTO user_roles
                (user_id, role_id)
                VALUES (?, ?)
            ");

            $stmt->execute([
                $administratorUserId,
                5
            ]);
        }
    }


    /*
     * Commit.
     */

    $pdo->commit();


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    failInstallation(
        'Administrator creation failed: ' .
        $e->getMessage()
    );
}


/*
|--------------------------------------------------------------------------
| CREATE INSTALLATION LOCK
|--------------------------------------------------------------------------
*/

try {

    if (!is_dir($storageDir)) {

        if (!mkdir($storageDir, 0775, true)) {

            throw new RuntimeException(
                'Unable to create storage directory.'
            );
        }
    }


    $lockData = [
        'installed_at' => date('Y-m-d H:i:s'),
        'database' => $dbName,
        'administrator_user_id' => $administratorUserId,
        'administrator_username' => $username,
    ];


    $result = file_put_contents(
        $lockFile,
        json_encode(
            $lockData,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        )
    );


    if ($result === false) {

        throw new RuntimeException(
            'Unable to create installation lock.'
        );
    }


} catch (Throwable $e) {

    failInstallation(
        'Installation completed but system lock could not be created: ' .
        $e->getMessage()
    );
}


/*
|--------------------------------------------------------------------------
| SESSION
|--------------------------------------------------------------------------
*/

$_SESSION['installation_completed'] = true;

$_SESSION['administrator_user_id'] =
    $administratorUserId;

$_SESSION['administrator_username'] =
    $username;

$_SESSION['database_name'] =
    $dbName;


/*
|--------------------------------------------------------------------------
| SUCCESS
|--------------------------------------------------------------------------
*/

header('Location: success.php');

exit;

