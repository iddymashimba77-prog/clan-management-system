<?php

/**
 * Remove unnecessary spaces from input
 */
function sanitizeInput(string $data): string
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect user to another page
 */
function redirect(string $url): void
{
    header("Location: $url");
    exit;
}

/**
 * Format date
 */
function formatDate(?string $date): string
{
    if (empty($date)) {
        return '-';
    }

    return date('d M Y', strtotime($date));
}

/**
 * Escape output safely
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generate Member Code
 * Example:
 * CLAN-0000001
 */
function generateMemberCode(int $number): string
{
    return 'CLAN-' . str_pad($number, 7, '0', STR_PAD_LEFT);
}