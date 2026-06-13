<?php

session_start();

if (isset($_GET['lang'])) {

    $allowed = ['sw', 'en'];

    if (in_array($_GET['lang'], $allowed)) {

        $_SESSION['language'] = $_GET['lang'];

    }

}

header("Location: " . $_SERVER['HTTP_REFERER']);

exit;