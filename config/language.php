<?php

if (!isset($_SESSION)) {

    session_start();

}

if (!isset($_SESSION['language'])) {

    $_SESSION['language'] = 'sw';

}

$lang = require __DIR__ . '/../languages/' . $_SESSION['language'] . '.php';

function lang($key)
{
    global $lang;

    return $lang[$key] ?? $key;
}