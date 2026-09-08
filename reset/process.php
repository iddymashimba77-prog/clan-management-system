<?php
declare(strict_types=1);

session_start();

/*
|--------------------------------------------------------------------------
| RESET PROCESS
|--------------------------------------------------------------------------
|
| PURPOSE:
|   Reset one existing Clan installation back to a clean state.
|
| IMPORTANT:
|   This is NOT a new installation.
|
|   SYSTEM DATA:
|       roles
|       permissions
|       role_permissions
|       relationship_types
|
|   CLAN DATA:
|       members
|       contributions
|       wallets
|       messages
|       events
|       documents
|       etc.
|
|   Installation / activation binding is deliberately NOT deleted.
|
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| ERROR HANDLER
|--------------------------------------------------------------------------
*/

function resetError(string $message): never
{
    $_SESSION['reset_error'] = $message;

    header('Location: index.php');

    exit;
}


/*
|--------------------------------------------------------------------------
| SUCCESS REDIRECT
|--------------------------------------------------------------------------
*/

function resetSuccess(
    string $username,
    int $administratorId
): never {

    $_SESSION['reset_success'] = true;

    $_SESSION['reset_username'] = $username;

    $_SESSION['reset_admin_id'] = $administratorId;

    header('Location: index.php');

    exit;
}


/*
|--------------------------------------------------------------------------
| ONLY POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: index.php');

    exit;
}


/*
|--------------------------------------------------------------------------
| INPUT
|--------------------------------------------------------------------------
*/

$username = trim(
    (string)($_POST['admin_username'] ?? '')
);

$password =
    (string)($_POST['admin_password'] ?? '');

$confirmation =
    trim(
        (string)($_POST['confirmation'] ?? '')
    );


/*
|--------------------------------------------------------------------------
| VALIDATION
|--------------------------------------------------------------------------
*/

if ($username === '') {

    resetError(
        'Administrator username is required.'
    );
}


if ($password === '') {

    resetError(
        'Administrator password is required.'
    );
}


if ($confirmation !== 'RESET CLAN') {

    resetError(
        'Invalid confirmation text. Please type RESET CLAN exactly.'
    );
}


/*
|--------------------------------------------------------------------------
| LOAD DATABASE
|--------------------------------------------------------------------------
*/

try {

    require_once dirname(__DIR__) .
        '/config/database.php';

} catch (Throwable $e) {

    resetError(
        'Unable to load database configuration: ' .
        $e->getMessage()
    );
}


try {

    $database = new Database();

    $pdo = $database->connect();

} catch (Throwable $e) {

    resetError(
        'Unable to connect to the database: ' .
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
        'roles'
    ];

    foreach ($requiredTables as $table) {

        $stmt = $pdo->prepare(
            "SHOW TABLES LIKE ?"
        );

        $stmt->execute([
            $table
        ]);

        if (!$stmt->fetchColumn()) {

            throw new RuntimeException(
                "Required table '{$table}' was not found."
            );
        }
    }

} catch (Throwable $e) {

    resetError(
        'Database structure verification failed: ' .
        $e->getMessage()
    );
}


/*
|--------------------------------------------------------------------------
| VERIFY ADMINISTRATOR
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        SELECT
            u.user_id,
            u.username,
            u.password_hash,
            u.role_id,
            u.account_status
        FROM users u
        WHERE u.username = ?
        LIMIT 1
    ");

    $stmt->execute([
        $username
    ]);

    $admin = $stmt->fetch(
        PDO::FETCH_ASSOC
    );


    if (!$admin) {

        resetError(
            'Administrator account was not found.'
        );
    }


    /*
     * Administrator role:
     *
     * Chairperson = 1
     * Secretary   = 2
     * Treasurer   = 3
     * Member      = 4
     * Administrator = 5
     */

    if ((int)$admin['role_id'] !== 5) {

        resetError(
            'Only the Administrator can perform a clan reset.'
        );
    }


    if (
        isset($admin['account_status']) &&
        $admin['account_status'] !== 'active'
    ) {

        resetError(
            'The Administrator account is not active.'
        );
    }


    if (
        !password_verify(
            $password,
            (string)$admin['password_hash']
        )
    ) {

        resetError(
            'Incorrect Administrator password.'
        );
    }


} catch (Throwable $e) {

    resetError(
        'Administrator authentication failed: ' .
        $e->getMessage()
    );
}


/*
|--------------------------------------------------------------------------
| VERIFY INSTALLATION STATE
|--------------------------------------------------------------------------
|
| We deliberately do NOT modify activation/binding data here.
|
| If config/installation.php exists, we load it only if possible.
|
*/

$installationConfig =
    dirname(__DIR__) .
    '/config/installation.php';


if (is_file($installationConfig)) {

    try {

        require_once $installationConfig;

    } catch (Throwable $e) {

        resetError(
            'Installation configuration could not be loaded: ' .
            $e->getMessage()
        );
    }
}


/*
|--------------------------------------------------------------------------
| GET EXISTING TABLES
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->query(
        "SHOW TABLES"
    );

    $existingTables =
        $stmt->fetchAll(
            PDO::FETCH_COLUMN
        );

} catch (Throwable $e) {

    resetError(
        'Unable to read database tables: ' .
        $e->getMessage()
    );
}


/*
|--------------------------------------------------------------------------
| SYSTEM TABLES
|--------------------------------------------------------------------------
|
| NEVER DELETE THESE.
|
*/

$systemTables = [

    'roles',

    'permissions',

    'role_permissions',

    'relationship_types',

];


/*
|--------------------------------------------------------------------------
| CLAN TABLES
|--------------------------------------------------------------------------
|
| These contain clan/member/application data.
|
| IMPORTANT:
|   activation/install/binding tables are NOT included.
|
*/

$tablesToClear = [

    'activity_logs',

    'clan_settings',

    'contribution_method_links',

    'contribution_payment_methods',

    'contribution_payments',

    'contribution_types',

    'contributions',

    'event_contributions',

    'events',

    'expenses',

    'families',

    'family_children',

    'family_documents',

    'family_message_deletions',

    'family_message_reads',

    'family_messages',

    'family_relationships',

    'family_wallet_balances',

    'family_wallet_members',

    'family_wallet_payments',

    'family_wallets',

    'gallery',

    'generations',

    'income',

    'leadership_terms',

    'marriages',

    'media_gallery',

    'member_generations',

    'message_deletions',

    'messages',

    'notifications',

    'payment_methods',

    'user_online_status',

    'user_roles',

    'users',

    'wallet_monthly_dues',

];


/*
|--------------------------------------------------------------------------
| SAFETY CHECK
|--------------------------------------------------------------------------
|
| Never allow system tables to enter the deletion list.
|
*/

$tablesToClear = array_values(
    array_diff(
        $tablesToClear,
        $systemTables
    )
);


/*
|--------------------------------------------------------------------------
| REMOVE TABLES THAT DO NOT EXIST
|--------------------------------------------------------------------------
*/

$tablesToClear = array_values(
    array_filter(
        $tablesToClear,
        static function (string $table) use ($existingTables): bool {

            return in_array(
                $table,
                $existingTables,
                true
            );
        }
    )
);


/*
|--------------------------------------------------------------------------
| HELPER:
| GET TABLE COLUMNS
|--------------------------------------------------------------------------
*/

function getTableColumns(
    PDO $pdo,
    string $table
): array {

    $safeTable =
        str_replace(
            '`',
            '``',
            $table
        );

    $stmt =
        $pdo->query(
            "DESCRIBE `{$safeTable}`"
        );

    $columns = [];

    foreach (
        $stmt->fetchAll(PDO::FETCH_ASSOC)
        as $column
    ) {

        $columns[] =
            $column['Field'];
    }

    return $columns;
}


/*
|--------------------------------------------------------------------------
| HELPER:
| FIND SAFE ENUM VALUE
|--------------------------------------------------------------------------
|
| Used for fields such as:
|
| gender
| family_member_type
|
| This prevents:
|
|   Column cannot be NULL
|
| or:
|
|   Data truncated for column ...
|
|--------------------------------------------------------------------------
*/

function getEnumValues(
    PDO $pdo,
    string $table,
    string $column
): array {

    $safeTable =
        str_replace(
            '`',
            '``',
            $table
        );

    $stmt =
        $pdo->query(
            "SHOW COLUMNS FROM `{$safeTable}` LIKE " .
            $pdo->quote($column)
        );

    $definition =
        $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$definition) {

        return [];
    }


    $type =
        (string)$definition['Type'];


    if (
        stripos($type, 'enum(') !== 0
    ) {

        return [];
    }


    $values = [];

    preg_match_all(
        "/'((?:[^'\\\\]|\\\\.)*)'/",
        $type,
        $matches
    );


    if (!empty($matches[1])) {

        foreach ($matches[1] as $value) {

            $values[] =
                stripslashes($value);
        }
    }


    return $values;
}


/*
|--------------------------------------------------------------------------
| HELPER:
| FIND VALID VALUE FOR REQUIRED COLUMN
|--------------------------------------------------------------------------
*/

function getSafeColumnValue(
    PDO $pdo,
    string $table,
    string $column,
    ?string $preferred = null
) {

    /*
     * First check whether column exists.
     */

    $columns =
        getTableColumns(
            $pdo,
            $table
        );


    if (
        !in_array(
            $column,
            $columns,
            true
        )
    ) {

        return null;
    }


    /*
     * Check column definition.
     */

    $safeTable =
        str_replace(
            '`',
            '``',
            $table
        );


    $stmt =
        $pdo->query(
            "SHOW COLUMNS FROM `{$safeTable}` LIKE " .
            $pdo->quote($column)
        );


    $definition =
        $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$definition) {

        return null;
    }


    $type =
        strtolower(
            (string)$definition['Type']
        );


    /*
     * ENUM
     */

    if (
        str_starts_with(
            $type,
            'enum('
        )
    ) {

        $enumValues =
            getEnumValues(
                $pdo,
                $table,
                $column
            );


        if (
            $preferred !== null &&
            in_array(
                $preferred,
                $enumValues,
                true
            )
        ) {

            return $preferred;
        }


        /*
         * Try common Administrator values.
         */

        $preferredValues = [

            'Administrator',
            'Admin',
            'Member',
            'Other',
            'Male',
            'Female',

        ];


        foreach (
            $preferredValues
            as $value
        ) {

            if (
                in_array(
                    $value,
                    $enumValues,
                    true
                )
            ) {

                return $value;
            }
        }


        /*
         * Last safe option:
         * first available ENUM value.
         */

        return $enumValues[0] ?? null;
    }


    /*
     * BOOLEAN / TINYINT
     */

    if (
        str_contains($type, 'tinyint')
    ) {

        return 0;
    }


    /*
     * DATE
     */

    if (
        $type === 'date'
    ) {

        return date('Y-m-d');
    }


    /*
     * DATETIME / TIMESTAMP
     */

    if (
        str_contains($type, 'datetime') ||
        str_contains($type, 'timestamp')
    ) {

        return date('Y-m-d H:i:s');
    }


    /*
     * STRING
     */

    if (
        str_contains($type, 'varchar') ||
        str_contains($type, 'text')
    ) {

        return $preferred ?? '';
    }


    return $preferred;
}


/*
|--------------------------------------------------------------------------
| BEGIN RESET
|--------------------------------------------------------------------------
*/

$foreignKeysDisabled = false;

$administratorId = 0;


/*
|--------------------------------------------------------------------------
| RESET DATABASE
|--------------------------------------------------------------------------
*/

try {

    /*
     * Disable foreign key checking.
     *
     * This is necessary because the clan tables have
     * relationships between one another.
     */

    $pdo->exec(
        "SET FOREIGN_KEY_CHECKS = 0"
    );

    $foreignKeysDisabled = true;


    /*
     |--------------------------------------------------------------------------
     | CLEAR CLAN DATA
     |--------------------------------------------------------------------------
     */

    foreach (
        $tablesToClear
        as $table
    ) {

        /*
         * Table names are hard-coded above.
         */

        $safeTable =
            str_replace(
                '`',
                '``',
                $table
            );


        /*
         * DELETE instead of TRUNCATE.
         *
         * DELETE avoids TRUNCATE's implicit transaction
         * behaviour and gives us a cleaner reset flow.
         */

        $pdo->exec(
            "DELETE FROM `{$safeTable}`"
        );
    }


    /*
     |--------------------------------------------------------------------------
     | RESET AUTO INCREMENT
     |--------------------------------------------------------------------------
     |
     | This makes the next administrator/member user_id start
     | from 1 where possible.
     |
     */

    foreach (
        $tablesToClear
        as $table
    ) {

        $safeTable =
            str_replace(
                '`',
                '``',
                $table
            );


        try {

            $pdo->exec(
                "ALTER TABLE `{$safeTable}` AUTO_INCREMENT = 1"
            );

        } catch (Throwable $ignored) {

            /*
             * Some tables may not have an AUTO_INCREMENT
             * column. Ignore those.
             */
        }
    }


    /*
     |--------------------------------------------------------------------------
     | CREATE ADMINISTRATOR
     |--------------------------------------------------------------------------
     */

    $userColumns =
        getTableColumns(
            $pdo,
            'users'
        );


    /*
     * Generate the first member code.
     */

    $memberCode =
        'CLAN-0000001';


    /*
     * Administrator details.
     */

    $firstName =
        'System';

    $lastName =
        'Administrator';


    $adminEmail =
        'admin@localhost';


    /*
     * Hash the supplied Administrator password.
     */

    $passwordHash =
        password_hash(
            $password,
            PASSWORD_DEFAULT
        );


    if ($passwordHash === false) {

        throw new RuntimeException(
            'Unable to create Administrator password hash.'
        );
    }


    /*
     |--------------------------------------------------------------------------
     | BUILD USER DATA
     |--------------------------------------------------------------------------
     */

    $data = [];


    /*
     * Required/basic fields.
     */

    $basicData = [

        'member_code' =>
            $memberCode,

        'first_name' =>
            $firstName,

        'last_name' =>
            $lastName,

        'username' =>
            $username,

        'email' =>
            $adminEmail,

        'password_hash' =>
            $passwordHash,

        'role_id' =>
            5,

        'account_status' =>
            'active',

        'is_deceased' =>
            0,

    ];


    foreach (
        $basicData
        as $column => $value
    ) {

        if (
            in_array(
                $column,
                $userColumns,
                true
            )
        ) {

            $data[$column] =
                $value;
        }
    }


    /*
     |--------------------------------------------------------------------------
     | GENDER
     |--------------------------------------------------------------------------
     |
     | We do NOT insert NULL blindly.
     |
     */

    if (
        in_array(
            'gender',
            $userColumns,
            true
        )
    ) {

        $gender =
            getSafeColumnValue(
                $pdo,
                'users',
                'gender',
                'Male'
            );


        if ($gender === null) {

            throw new RuntimeException(
                'Unable to determine a valid value for users.gender.'
            );
        }


        $data['gender'] =
            $gender;
    }


    /*
     |--------------------------------------------------------------------------
     | MARITAL STATUS
     |--------------------------------------------------------------------------
     */

    if (
        in_array(
            'marital_status',
            $userColumns,
            true
        )
    ) {

        $maritalStatus =
            getSafeColumnValue(
                $pdo,
                'users',
                'marital_status',
                'Single'
            );


        if ($maritalStatus !== null) {

            $data['marital_status'] =
                $maritalStatus;
        }
    }


    /*
     |--------------------------------------------------------------------------
     | FAMILY MEMBER TYPE
     |--------------------------------------------------------------------------
     */

    if (
        in_array(
            'family_member_type',
            $userColumns,
            true
        )
    ) {

        $familyMemberType =
            getSafeColumnValue(
                $pdo,
                'users',
                'family_member_type',
                'Administrator'
            );


        if ($familyMemberType === null) {

            throw new RuntimeException(
                'Unable to determine a valid value for users.family_member_type.'
            );
        }


        $data['family_member_type'] =
            $familyMemberType;
    }


    /*
     |--------------------------------------------------------------------------
     | OTHER OPTIONAL USER FIELDS
     |--------------------------------------------------------------------------
     */

    $optionalDefaults = [

        'middle_name' =>
            '',

        'phone' =>
            '',

        'profile_picture' =>
            null,

        'birth_place' =>
            '',

        'current_residence' =>
            '',

    ];


    foreach (
        $optionalDefaults
        as $column => $value
    ) {

        if (
            in_array(
                $column,
                $userColumns,
                true
            )
        ) {

            /*
             * Only insert nullable fields with NULL.
             */

            if ($value === null) {

                /*
                 * Check whether the column permits NULL.
                 */

                $safeUsers =
                    str_replace(
                        '`',
                        '``',
                        'users'
                    );


                $stmt =
                    $pdo->query(
                        "SHOW COLUMNS FROM `{$safeUsers}` LIKE " .
                        $pdo->quote($column)
                    );


                $definition =
                    $stmt->fetch(PDO::FETCH_ASSOC);


                if (
                    $definition &&
                    strtoupper(
                        (string)$definition['Null']
                    ) === 'YES'
                ) {

                    $data[$column] =
                        null;
                }

            } else {

                $data[$column] =
                    $value;
            }
        }
    }


    /*
     |--------------------------------------------------------------------------
     | INSERT ADMINISTRATOR
     |--------------------------------------------------------------------------
     */

    if (empty($data['role_id'])) {

        throw new RuntimeException(
            'users.role_id is required but was not found.'
        );
    }


    $fields =
        array_keys($data);


    $placeholders =
        array_fill(
            0,
            count($fields),
            '?'
        );


    $safeFields = [];


    foreach (
        $fields
        as $field
    ) {

        $safeFields[] =
            '`' .
            str_replace(
                '`',
                '``',
                $field
            ) .
            '`';
    }


    $insertSql = "
        INSERT INTO users
        (" .
        implode(
            ',',
            $safeFields
        ) .
        ")
        VALUES
        (" .
        implode(
            ',',
            $placeholders
        ) .
        ")
    ";


    $stmt =
        $pdo->prepare(
            $insertSql
        );


    $stmt->execute(
        array_values($data)
    );


    $administratorId =
        (int)$pdo->lastInsertId();


    if ($administratorId <= 0) {

        throw new RuntimeException(
            'Administrator was not created.'
        );
    }


    /*
     |--------------------------------------------------------------------------
     | USER ROLES
     |--------------------------------------------------------------------------
     */

    if (
        in_array(
            'user_roles',
            $existingTables,
            true
        )
    ) {

        $roleColumns =
            getTableColumns(
                $pdo,
                'user_roles'
            );


        if (
            in_array(
                'user_id',
                $roleColumns,
                true
            ) &&
            in_array(
                'role_id',
                $roleColumns,
                true
            )
        ) {

            $roleData = [

                'user_id' =>
                    $administratorId,

                'role_id' =>
                    5,

            ];


            /*
             * assigned_by_user_id may be nullable.
             * For the first Administrator there is no
             * previous Administrator.
             */

            if (
                in_array(
                    'assigned_by_user_id',
                    $roleColumns,
                    true
                )
            ) {

                $roleData[
                    'assigned_by_user_id'
                ] = null;
            }


            $roleFields =
                array_keys(
                    $roleData
                );


            $rolePlaceholders =
                array_fill(
                    0,
                    count($roleFields),
                    '?'
                );


            $safeRoleFields = [];


            foreach (
                $roleFields
                as $field
            ) {

                $safeRoleFields[] =
                    '`' .
                    str_replace(
                        '`',
                        '``',
                        $field
                    ) .
                    '`';
            }


            $roleSql = "
                INSERT INTO user_roles
                (" .
                implode(
                    ',',
                    $safeRoleFields
                ) .
                ")
                VALUES
                (" .
                implode(
                    ',',
                    $rolePlaceholders
                ) .
                ")
            ";


            $stmt =
                $pdo->prepare(
                    $roleSql
                );


            $stmt->execute(
                array_values(
                    $roleData
                )
            );
        }
    }


    /*
     |--------------------------------------------------------------------------
     | RESTORE FOREIGN KEYS
     |--------------------------------------------------------------------------
     */

    if ($foreignKeysDisabled) {

        $pdo->exec(
            "SET FOREIGN_KEY_CHECKS = 1"
        );

        $foreignKeysDisabled = false;
    }


    /*
     |--------------------------------------------------------------------------
     | FINAL VERIFICATION
     |--------------------------------------------------------------------------
     */

    $stmt =
        $pdo->prepare("
            SELECT
                u.user_id,
                u.username,
                u.role_id
            FROM users u
            WHERE u.user_id = ?
            LIMIT 1
        ");


    $stmt->execute([
        $administratorId
    ]);


    $createdAdmin =
        $stmt->fetch(
            PDO::FETCH_ASSOC
        );


    if (!$createdAdmin) {

        throw new RuntimeException(
            'Administrator verification failed after reset.'
        );
    }


    if (
        (int)$createdAdmin['role_id'] !== 5
    ) {

        throw new RuntimeException(
            'Administrator role verification failed after reset.'
        );
    }


    /*
     |--------------------------------------------------------------------------
     | RESET SESSION
     |--------------------------------------------------------------------------
     */

    $_SESSION = [];


    /*
     * Keep installation information out of the reset
     * process. The installation remains bound.
     */

    session_regenerate_id(true);


    /*
     |--------------------------------------------------------------------------
     | SUCCESS
     |--------------------------------------------------------------------------
     */

    resetSuccess(
        $username,
        $administratorId
    );


} catch (Throwable $e) {


    /*
     |--------------------------------------------------------------------------
     | RESTORE FOREIGN KEYS
     |--------------------------------------------------------------------------
     */

    if ($foreignKeysDisabled) {

        try {

            $pdo->exec(
                "SET FOREIGN_KEY_CHECKS = 1"
            );

        } catch (Throwable $ignored) {
        }
    }


    /*
     |--------------------------------------------------------------------------
     | RESET ERROR
     |--------------------------------------------------------------------------
     */

    resetError(
        'Reset failed: ' .
        $e->getMessage()
    );
}