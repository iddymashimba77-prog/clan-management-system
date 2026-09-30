<?php

if (!function_exists('envValue')) {
    function envValue(string $key, string $default = ''): string
    {
        $v = getenv($key);
        if ($v === false || $v === '') {
            $v = $_ENV[$key] ?? $_SERVER[$key] ?? '';
        }
        return ($v !== '' && $v !== null) ? (string) $v : $default;
    }
}
