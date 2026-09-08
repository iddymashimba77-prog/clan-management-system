<?php

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/language.php';

?>

<!DOCTYPE html>

<html lang="<?= htmlspecialchars($current_language) ?>">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="theme-color"
        content="#0f172a"
    >

    <meta
        name="description"
        content="Clan Management System"
    >

    <title>
        <?= APP_NAME ?>
    </title>


    <!-- =====================================================
         ADMINLTE / BOOTSTRAP
    ====================================================== -->

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>adminlte/plugins/fontawesome-free/css/all.min.css"
    >

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>adminlte/plugins/icheck-bootstrap/icheck-bootstrap.min.css"
    >

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>adminlte/plugins/overlayScrollbars/css/OverlayScrollbars.min.css"
    >

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css"
    >

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css"
    >

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>adminlte/plugins/datatables-buttons/css/buttons.bootstrap4.min.css"
    >

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>adminlte/dist/css/adminlte.min.css"
    >


    <!-- =====================================================
         CLAN MANAGEMENT CUSTOM THEME
    ====================================================== -->

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>assets/css/clan-theme.css"
    >

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>assets/css/responsive.css"
    >

</head>


<body
    class="hold-transition sidebar-mini layout-fixed clan-system"
>


<div class="wrapper">