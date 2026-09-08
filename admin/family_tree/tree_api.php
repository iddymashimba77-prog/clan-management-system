<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';
require_once '../../config/language.php';
require_once 'family_builder.php';

requireLogin();

try {

    $database = new Database();
    $pdo = $database->connect();

    $builder = new FamilyBuilder($pdo);

    echo json_encode([
        "success" => true,
        "nodes"   => $builder->build()
    ]);
    
    echo json_encode([
        "success" => true,
        "tree" => $tree
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage(),
        "file" => basename($e->getFile()),
        "line" => $e->getLine()
    ], JSON_UNESCAPED_UNICODE);

}