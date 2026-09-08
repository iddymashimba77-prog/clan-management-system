<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| BIRTHDAY NOTIFICATIONS
|--------------------------------------------------------------------------
|
| Generates a "Birthday" notification for every active clan member when
| another member has a birthday today, so the whole community can see and
| celebrate it in the notifications list (with a cake icon/type label).
|
| The generator is idempotent: it only inserts a notification once per
| recipient per birthday-person per calendar day.
|--------------------------------------------------------------------------
*/

if (!function_exists('generateBirthdayNotifications')) {

    /**
     * Create Birthday notifications for today's members.
     *
     * @param PDO   $pdo     Tenant database connection.
     * @param int   $clanId  The authenticated clan id.
     * @return int  Number of newly created notifications.
     */
    function generateBirthdayNotifications(PDO $pdo, int $clanId): int
    {
        $today = date('Y-m-d');

        // Members who celebrate a birthday today
        $stmt = $pdo->prepare("
            SELECT
                u.user_id,
                TRIM(CONCAT(
                    COALESCE(u.first_name, ''),
                    ' ',
                    COALESCE(u.middle_name, ''),
                    ' ',
                    COALESCE(u.last_name, '')
                )) AS full_name,
                u.first_name
            FROM users u
            WHERE u.clan_id = ?
              AND u.is_deleted = 0
              AND u.account_status = 'active'
              AND u.is_deceased = 0
              AND u.date_of_birth IS NOT NULL
              AND MONTH(u.date_of_birth) = MONTH(?)
              AND DAY(u.date_of_birth) = DAY(?)
        ");

        $stmt->execute([$clanId, $today, $today]);

        $birthdayMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($birthdayMembers)) {
            return 0;
        }

        // Every active member should see the birthday notification
        $recipients = $pdo->prepare("
            SELECT user_id
            FROM users
            WHERE clan_id = ?
              AND is_deleted = 0
              AND account_status = 'active'
              AND is_deceased = 0
        ");

        $recipients->execute([$clanId]);

        $recipientIds = $recipients->fetchAll(PDO::FETCH_COLUMN);

        if (empty($recipientIds)) {
            return 0;
        }

        // Idempotency guard: insert only if this recipient does not yet
        // have THIS birthday-person's notification. Using
        // INSERT ... SELECT ... WHERE NOT EXISTS makes check+insert a
        // single atomic statement, so concurrent badge polls (every 5s,
        // possibly from several open tabs) can never create duplicates.
        $insert = $pdo->prepare("
            INSERT INTO notifications
                (clan_id, user_id, title, notification_type, message, is_read)
            SELECT ?, ?, ?, 'Birthday', ?, 0
            FROM DUAL
            WHERE NOT EXISTS (
                SELECT 1
                FROM notifications
                WHERE clan_id = ?
                  AND user_id = ?
                  AND notification_type = 'Birthday'
                  AND title = ?
            )
        ");

        $created = 0;

        foreach ($birthdayMembers as $member) {

            $fullName = $member['full_name'] ?: $member['first_name'];

            $title = 'Birthday: ' . $fullName . ' / ' . $today;

            $message = sprintf(
                'Happy Birthday %s! Tunakutakia siku njema ya kuzaliwa, kila heri na baraka. %s',
                $fullName,
                $today
            );

            foreach ($recipientIds as $recipientId) {

                $insert->execute([
                    $clanId,
                    (int)$recipientId,
                    $title,
                    $message,
                    $clanId,
                    (int)$recipientId,
                    $title,
                ]);

                $created += $insert->rowCount();
            }
        }

        return $created;
    }
}
