<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

$pdo = $database->connect();

$stmt = $pdo->query("
    SELECT
        u.user_id,
        u.member_code,
        u.first_name,
        u.middle_name,
        u.last_name,
        u.gender,
        u.date_of_birth,
        u.marital_status,
        u.family_member_type,
        u.is_deceased,

        CONCAT(
            father.first_name, ' ',
            COALESCE(father.middle_name, ''), ' ',
            father.last_name
        ) AS father_name,

        CONCAT(
            mother.first_name, ' ',
            COALESCE(mother.middle_name, ''), ' ',
            mother.last_name
        ) AS mother_name,

        CONCAT(
            spouse.first_name, ' ',
            COALESCE(spouse.middle_name, ''), ' ',
            spouse.last_name
        ) AS spouse_name

    FROM users u

    LEFT JOIN users father
        ON father.user_id = u.father_user_id

    LEFT JOIN users mother
        ON mother.user_id = u.mother_user_id

    LEFT JOIN users spouse
        ON spouse.user_id = u.spouse_user_id

    WHERE u.is_deleted = 0

    ORDER BY
        u.last_name ASC,
        u.first_name ASC
");

$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filename = 'family_tree_export_' . date('Y-m-d_H-i-s') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header(
    'Content-Disposition: attachment; filename="' .
    $filename .
    '"'
);

$output = fopen('php://output', 'w');

fputcsv($output, [
    'Member Code',
    'First Name',
    'Middle Name',
    'Last Name',
    'Gender',
    'Date of Birth',
    'Marital Status',
    'Member Type',
    'Deceased',
    'Father',
    'Mother',
    'Spouse'
]);

foreach ($members as $member) {

    fputcsv($output, [
        $member['member_code'],
        $member['first_name'],
        $member['middle_name'],
        $member['last_name'],
        $member['gender'],
        $member['date_of_birth'],
        $member['marital_status'],
        $member['family_member_type'],
        !empty($member['is_deceased']) ? 'Yes' : 'No',
        $member['father_name'],
        $member['mother_name'],
        $member['spouse_name']
    ]);
}

fclose($output);
exit;