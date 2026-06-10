<?php

require_once 'config/database.php';

try {

    $database = new Database();
    $connection = $database->connect();

    echo "Database connected successfully.";

} catch (Exception $e) {

    echo $e->getMessage();

}