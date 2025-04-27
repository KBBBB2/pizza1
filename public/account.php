<?php

// CORS és alap header beállítások
$origin = $_SERVER['HTTP_ORIGIN'] ?? 'http://localhost';
header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

// OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Max-Age: 86400");
    exit(0);
}

// Autoload, model, controller
require_once __DIR__ . '/../vendor/autoload.php';  // ha van composer
require_once __DIR__ . '/../model/Account.php';
require_once __DIR__ . '/../controller/Account.php';

// Példányosítás és futtatás
$accountModel = new Account();
$controller   = new AccountController($accountModel);
$controller->processRequest();
