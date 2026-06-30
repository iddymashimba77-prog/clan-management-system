<?php

function getUserById(PDO $pdo, ?int $userId): ?array
{
    if (empty($userId)) {
        return null;
    }

    $stmt = $pdo->prepare("
        SELECT *
        FROM users
        WHERE user_id = :id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $userId
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ?: null;
}

function getFullName(array $user): string
{
    return trim(
        ($user['first_name'] ?? '') . ' ' .
        ($user['middle_name'] ?? '') . ' ' .
        ($user['last_name'] ?? '')
    );
}