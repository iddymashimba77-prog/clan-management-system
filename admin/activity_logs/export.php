<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

$database = new Database();
$pdo = $database->connect();

/*
|--------------------------------------------------------------------------
| SEARCH / FILTERS
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');
$action = trim($_GET['action'] ?? '');
$table  = trim($_GET['table_name'] ?? '');

$where = [];
$params = [];

if ($search !== '') {

    $where[] = "
        (
            al.action LIKE :search
            OR al.table_name LIKE :search
            OR al.description LIKE :search
            OR al.ip_address LIKE :search
            OR u.first_name LIKE :search
            OR u.middle_name LIKE :search
            OR u.last_name LIKE :search
            OR u.member_code LIKE :search
        )
    ";

    $params[':search'] = '%' . $search . '%';
}

if ($action !== '') {

    $where[] = "al.action = :action";

    $params[':action'] = $action;
}

if ($table !== '') {

    $where[] = "al.table_name = :table_name";

    $params[':table_name'] = $table;
}

$whereSQL = '';

if (!empty($where)) {

    $whereSQL =
        'WHERE ' .
        implode(' AND ', $where);
}


/*
|--------------------------------------------------------------------------
| GET LOGS
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT

        al.log_id,

        al.action,

        al.table_name,

        al.record_id,

        al.description,

        al.ip_address,

        al.created_at,

        u.member_code,

        u.first_name,

        u.middle_name,

        u.last_name

    FROM activity_logs al

    LEFT JOIN users u
        ON u.user_id = al.user_id

    $whereSQL

    ORDER BY
        al.created_at DESC,
        al.log_id DESC
";

$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| CSV FILE
|--------------------------------------------------------------------------
*/

$filename =
    'activity_logs_' .
    date('Y-m-d_H-i-s') .
    '.csv';

header(
    'Content-Type: text/csv; charset=utf-8'
);

header(
    'Content-Disposition: attachment; filename="' .
    $filename .
    '"'
);

header('Pragma: no-cache');
header('Expires: 0');


$output = fopen('php://output', 'w');


/*
|--------------------------------------------------------------------------
| UTF-8 BOM
|--------------------------------------------------------------------------
*/

fprintf(
    $output,
    chr(0xEF) .
    chr(0xBB) .
    chr(0xBF)
);


/*
|--------------------------------------------------------------------------
| CSV HEADER
|--------------------------------------------------------------------------
*/

fputcsv($output, [

    'Log ID',
    'Member Code',
    'User Name',
    'Action',
    'Table',
    'Record ID',
    'Description',
    'IP Address',
    'Date & Time'

]);


/*
|--------------------------------------------------------------------------
| CSV DATA
|--------------------------------------------------------------------------
*/

foreach ($logs as $log) {

    $name = trim(

        ($log['first_name'] ?? '') . ' ' .
        ($log['middle_name'] ?? '') . ' ' .
        ($log['last_name'] ?? '')

    );

    if ($name === '') {
        $name = 'System';
    }


    fputcsv($output, [

        $log['log_id'],

        $log['member_code'] ?? '',

        $name,

        $log['action'],

        $log['table_name'] ?? '',

        $log['record_id'] ?? '',

        $log['description'] ?? '',

        $log['ip_address'] ?? '',

        $log['created_at']

    ]);
}


fclose($output);

exit;