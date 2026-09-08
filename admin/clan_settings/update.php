<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: index.php');
    exit;
}

$database = new Database();
$pdo = $database->connect();

/*
|--------------------------------------------------------------------------
| GET TABLE COLUMNS
|--------------------------------------------------------------------------
*/

$columnsStmt = $pdo->query("SHOW COLUMNS FROM clan_settings");
$columns = $columnsStmt->fetchAll(PDO::FETCH_ASSOC);

$allowed = [];

foreach ($columns as $column) {

    $field = $column['Field'];

    if (
        $field === 'id' ||
        $field === 'setting_id' ||
        $field === 'created_at' ||
        $field === 'updated_at'
    ) {
        continue;
    }

    $allowed[] = $field;
}


/*
|--------------------------------------------------------------------------
| CHECK EXISTING ROW
|--------------------------------------------------------------------------
*/

$count = (int)$pdo->query("
    SELECT COUNT(*)
    FROM clan_settings
")->fetchColumn();


try {

    $pdo->beginTransaction();

    if ($count > 0) {

        $sets = [];
        $params = [];

        foreach ($allowed as $field) {

            if (!array_key_exists($field, $_POST)) {
                continue;
            }

            $sets[] = "`$field` = ?";
            $params[] = $_POST[$field];
        }

        if (!empty($sets)) {

            $sql = "
                UPDATE clan_settings
                SET " . implode(', ', $sets);

            /*
            | Update only first row
            */
            $sql .= " LIMIT 1";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }

    } else {

        $fields = [];
        $placeholders = [];
        $params = [];

        foreach ($allowed as $field) {

            if (!array_key_exists($field, $_POST)) {
                continue;
            }

            $fields[] = "`$field`";
            $placeholders[] = "?";
            $params[] = $_POST[$field];
        }

        if (!empty($fields)) {

            $sql = "
                INSERT INTO clan_settings
                (" . implode(',', $fields) . ")
                VALUES
                (" . implode(',', $placeholders) . ")
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG
    |--------------------------------------------------------------------------
    */

    $log = $pdo->prepare("
        INSERT INTO activity_logs
        (
            user_id,
            action,
            table_name,
            description,
            ip_address
        )
        VALUES (?, ?, ?, ?, ?)
    ");

    $log->execute([
        $_SESSION['user_id'] ?? null,
        'UPDATE_CLAN_SETTINGS',
        'clan_settings',
        'Administrator updated clan settings.',
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);


    $pdo->commit();

    header('Location: index.php?success=updated');
    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die(
        "Unable to update clan settings: " .
        htmlspecialchars($e->getMessage())
    );
}