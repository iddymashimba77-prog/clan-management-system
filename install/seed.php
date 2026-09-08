<?php
declare(strict_types=1);


/*
|--------------------------------------------------------------------------
| SYSTEM DATA SEED
|--------------------------------------------------------------------------
|
| This file MUST NOT create:
| - clan members
| - families
| - marriages
| - contributions
| - payments
| - wallets
| - income
| - expenses
|
| It only prepares system-level records.
|--------------------------------------------------------------------------
*/


function seedSystemData(PDO $pdo): void
{
    /*
    |--------------------------------------------------------------------------
    | ROLES
    |--------------------------------------------------------------------------
    */

    $roles = [

        1 => [
            'Chairperson',
            'Overall clan leadership and approval responsibilities.'
        ],

        2 => [
            'Secretary',
            'Clan administration and contribution processing.'
        ],

        3 => [
            'Treasurer',
            'Financial management and financial approval responsibilities.'
        ],

        4 => [
            'Member',
            'Standard clan member access.'
        ],

        5 => [
            'Administrator',
            'System administrator responsible for managing the clan system.'
        ]

    ];


    $roleStmt = $pdo->prepare("
        INSERT INTO roles
        (
            role_id,
            role_name,
            description,
            status
        )
        VALUES
        (?, ?, ?, 'active')
        ON DUPLICATE KEY UPDATE
            role_name = VALUES(role_name),
            description = VALUES(description),
            status = 'active'
    ");


    foreach ($roles as $roleId => $role) {

        $roleStmt->execute([
            $roleId,
            $role[0],
            $role[1]
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | BASIC SYSTEM PERMISSIONS
    |--------------------------------------------------------------------------
    |
    | Only create them if they do not already exist.
    |--------------------------------------------------------------------------
    */

    $permissionColumnsStmt = $pdo->query("
        DESCRIBE permissions
    ");

    $permissionColumns = [];

    foreach ($permissionColumnsStmt->fetchAll() as $column) {
        $permissionColumns[] = $column['Field'];
    }


    if (
        in_array('permission_name', $permissionColumns, true) &&
        in_array('permission_key', $permissionColumns, true)
    ) {

        $permissions = [

            [
                'Dashboard',
                'dashboard.view',
                'View dashboard',
                'Dashboard'
            ],

            [
                'Users',
                'users.view',
                'View users',
                'Users'
            ],

            [
                'Users',
                'users.create',
                'Create users',
                'Users'
            ],

            [
                'Users',
                'users.edit',
                'Edit users',
                'Users'
            ],

            [
                'Users',
                'users.delete',
                'Delete users',
                'Users'
            ],

            [
                'Roles',
                'roles.view',
                'View roles',
                'Roles'
            ],

            [
                'Roles',
                'roles.manage',
                'Manage roles and permissions',
                'Roles'
            ],

            [
                'Finance',
                'finance.view',
                'View financial information',
                'Finance'
            ],

            [
                'Transactions',
                'transactions.view',
                'View transactions',
                'Transactions'
            ],

            [
                'Contributions',
                'contributions.view',
                'View contributions',
                'Contributions'
            ],

            [
                'Contributions',
                'contributions.manage',
                'Manage contributions',
                'Contributions'

            ],

            [
                'Wallets',
                'wallets.view',
                'View family wallets',
                'Wallets'
            ],

            [
                'Wallets',
                'wallets.manage',
                'Manage family wallets',
                'Wallets'
            ],

            [
                'Reports',
                'reports.view',
                'View reports',
                'Reports'
            ],

            [
                'System',
                'system.manage',
                'Manage system settings',
                'System'
            ]

        ];


        /*
        |--------------------------------------------------------------------------
        | INSERT PERMISSIONS
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            INSERT INTO permissions
            (
                permission_name,
                permission_key,
                description,
                module,
                status
            )
            VALUES
            (?, ?, ?, ?, 'active')
            ON DUPLICATE KEY UPDATE
                permission_name = VALUES(permission_name),
                description = VALUES(description),
                module = VALUES(module),
                status = 'active'
        ");


        foreach ($permissions as $permission) {

            $stmt->execute([
                $permission[0],
                $permission[1],
                $permission[2],
                $permission[3]
            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | ADMINISTRATOR PERMISSIONS
    |--------------------------------------------------------------------------
    |
    | Administrator gets all active permissions.
    |--------------------------------------------------------------------------
    */

    $permissionTableExists =
        $pdo->query("
            SHOW TABLES LIKE 'permissions'
        ")->fetchColumn();


    $rolePermissionTableExists =
        $pdo->query("
            SHOW TABLES LIKE 'role_permissions'
        ")->fetchColumn();


    if (
        $permissionTableExists &&
        $rolePermissionTableExists
    ) {

        $permissions = $pdo->query("
            SELECT permission_id
            FROM permissions
            WHERE status = 'active'
        ")->fetchAll(PDO::FETCH_COLUMN);


        $stmt = $pdo->prepare("
            INSERT IGNORE INTO role_permissions
            (
                role_id,
                permission_id
            )
            VALUES
            (5, ?)
        ");


        foreach ($permissions as $permissionId) {

            $stmt->execute([
                (int)$permissionId
            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | IMPORTANT
    |--------------------------------------------------------------------------
    |
    | Do NOT seed:
    |
    | users
    | families
    | family_members
    | marriages
    | contributions
    | contribution_payments
    | family_wallets
    | family_wallet_members
    | family_wallet_payments
    | income
    | expenses
    | events
    |
    | These belong to the individual clan.
    |--------------------------------------------------------------------------
    */
}