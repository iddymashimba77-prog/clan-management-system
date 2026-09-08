<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

$pdo = $database->connect();

$report = $_GET['report'] ?? '';

$filename = 'clan_report_' . $report . '_' . date('Y-m-d') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header(
    'Content-Disposition: attachment; filename="' .
    $filename .
    '"'
);

$output = fopen('php://output', 'w');

switch ($report) {

    case 'members':

        fputcsv($output, [
            'Member Code',
            'First Name',
            'Middle Name',
            'Last Name',
            'Gender',
            'Date of Birth',
            'Marital Status',
            'Account Status',
            'Deceased'
        ]);

        $rows = $pdo->query("
            SELECT
                member_code,
                first_name,
                middle_name,
                last_name,
                gender,
                date_of_birth,
                marital_status,
                account_status,
                is_deceased
            FROM users
            WHERE is_deleted = 0
            ORDER BY first_name,last_name
        ");

        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }

        break;


    case 'contributions':

        fputcsv($output, [
            'Contribution',
            'Target Amount',
            'Approved Amount',
            'Status'
        ]);

        $rows = $pdo->query("
            SELECT
                c.title,
                c.target_amount,
                COALESCE(SUM(
                    CASE
                        WHEN cp.payment_status = 'approved'
                        THEN cp.amount
                        ELSE 0
                    END
                ),0) AS approved_amount,
                c.status
            FROM contributions c
            LEFT JOIN contribution_payments cp
                ON cp.contribution_id = c.contribution_id
            GROUP BY
                c.contribution_id
            ORDER BY c.created_at DESC
        ");

        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }

        break;


    case 'wallets':

        fputcsv($output, [
            'Wallet',
            'Purpose',
            'Target',
            'Monthly Amount',
            'Current Balance',
            'Status'
        ]);

        $rows = $pdo->query("
            SELECT
                wallet_name,
                purpose,
                target_amount,
                monthly_amount,
                current_balance,
                status
            FROM family_wallets
            ORDER BY created_at DESC
        ");

        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }

        break;


    case 'events':

        fputcsv($output, [
            'Title',
            'Date',
            'Start Time',
            'End Time',
            'Location',
            'Status'
        ]);

        $rows = $pdo->query("
            SELECT
                title,
                event_date,
                start_time,
                end_time,
                location,
                event_status
            FROM events
            ORDER BY event_date DESC
        ");

        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }

        break;


    case 'family_tree':

        fputcsv($output, [
            'Member Code',
            'First Name',
            'Middle Name',
            'Last Name',
            'Father ID',
            'Mother ID',
            'Spouse ID'
        ]);

        $rows = $pdo->query("
            SELECT
                member_code,
                first_name,
                middle_name,
                last_name,
                father_user_id,
                mother_user_id,
                spouse_user_id
            FROM users
            WHERE is_deleted = 0
            ORDER BY first_name,last_name
        ");

        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }

        break;


    case 'finance':

        fputcsv($output, [
            'Source',
            'Approved Amount'
        ]);

        fputcsv($output, [
            'Contributions',
            $pdo->query("
                SELECT COALESCE(SUM(amount),0)
                FROM contribution_payments
                WHERE payment_status='approved'
            ")->fetchColumn()
        ]);

        fputcsv($output, [
            'Wallets',
            $pdo->query("
                SELECT COALESCE(SUM(amount),0)
                FROM family_wallet_payments
                WHERE payment_status='approved'
            ")->fetchColumn()
        ]);

        break;


    case 'activity_logs':

        fputcsv($output, [
            'User ID',
            'Action',
            'Table',
            'Record ID',
            'Description',
            'IP',
            'Date'
        ]);

        $rows = $pdo->query("
            SELECT
                user_id,
                action,
                table_name,
                record_id,
                description,
                ip_address,
                created_at
            FROM activity_logs
            ORDER BY created_at DESC
        ");

        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }

        break;


    default:

        fputcsv($output, [
            'Invalid report type'
        ]);

        break;
}

fclose($output);
exit;