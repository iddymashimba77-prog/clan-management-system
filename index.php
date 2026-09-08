<?php
require_once 'config/clan_db.php';

// Check if current domain has a mapped database
$domainDatabase = getCurrentDomainDatabase();

if ($domainDatabase !== null && $domainDatabase !== '') {
    // If domain has a mapped database, redirect to home.php which will use that database
    header("Location: home.php");
    exit;
}

// Otherwise, redirect to home.php which will use default database
header("Location: home.php");
exit;