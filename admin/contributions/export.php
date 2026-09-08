<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->query("
    SELECT

        c.contribution_id,
        c.title,
        c.description,
        c.target_amount,
        c.current_amount,
        c.progress,
        c.total_paid_members,
        c.total_pending_members,
        c.minimum_amount,
        c.allow_partial_payment,
        c.start_date,
        c.end_date,
        c.status,
        c.created_at,

        u.member_code AS created_by_code,

        CONCAT(
            u.first_name,
            ' ',
            COALESCE(u.middle_name, ''),
            ' ',
            u.last_name
        ) AS created_by_name

    FROM contributions c

    LEFT JOIN users u
        ON u.user_id = c.created_by_user_id

    ORDER BY c.contribution_id DESC
");

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filename =
    'contributions_' .
    date('Y-m-d_H-i-s') .
    '.csv';

header('Content-Type: text/csv; charset=utf-8');
header(
    'Content-Disposition: attachment; filename="' .
    $filename .
    '"'
);

$output = fopen('php://output', 'w');

fputcsv($output, [
    'Contribution ID',
    'Title',
    'Description',
    'Target Amount',
    'Current Amount',
    'Progress %',
    'Paid Members',
    'Pending Members',
    'Minimum Amount',
    'Partial Payment',
    'Start Date',
    'End Date',
    'Status',
    'Created By',
    'Created By Code',
    'Created At'
]);

foreach ($rows as $row) {

    fputcsv($output, [

        $row['contribution_id'],

        $row['title'],

        $row['description'],

        $row['target_amount'],

        $row['current_amount'],

        $row['progress'],

        $row['total_paid_members'],

        $row['total_pending_members'],

        $row['minimum_amount'],

        $row['allow_partial_payment']
            ? 'Yes'
            : 'No',

        $row['start_date'],

        $row['end_date'],

        ucfirst($row['status']),

        $row['created_by_name'],

        $row['created_by_code'],

        $row['created_at']
    ]);
}

fclose($output);

exit;