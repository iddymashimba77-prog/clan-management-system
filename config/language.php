<?php

/**
 * Clan Management System
 * Language Manager
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Available Languages
|--------------------------------------------------------------------------
*/

$available_languages = [
    'en',
    'sw'
];

/*
|--------------------------------------------------------------------------
| Default Language
|--------------------------------------------------------------------------
*/

$default_language = 'en';

/*
|--------------------------------------------------------------------------
| Select Language
|--------------------------------------------------------------------------
*/

if (isset($_GET['lang'])) {

    $requested_language = strtolower(
        trim($_GET['lang'])
    );

    if (in_array($requested_language, $available_languages, true)) {

        $_SESSION['language'] = $requested_language;

    }

}

/*
|--------------------------------------------------------------------------
| Current Language
|--------------------------------------------------------------------------
*/

$current_language =
    $_SESSION['language']
    ?? $default_language;

/*
|--------------------------------------------------------------------------
| Load Language File
|--------------------------------------------------------------------------
*/

$language_file =
    __DIR__
    . '/../languages/'
    . $current_language
    . '.php';

if (file_exists($language_file)) {

    $translations = require $language_file;

} else {

    $translations = require __DIR__ . '/../languages/en.php';

}

/*
|--------------------------------------------------------------------------
| Translation Function
|--------------------------------------------------------------------------
*/

function __($key, $default = null)
{
    global $translations;

    if (isset($translations[$key])) {
        return $translations[$key];
    }

    return $default ?? $key;
}