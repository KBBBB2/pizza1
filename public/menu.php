<?php
// public/menu.php
// This is the entry point for the API; sets CORS then delegates to controller
require_once __DIR__ . '/../controller/Menu.php';

// CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Preflight handling
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Instantiate controller with real Pizza model
$pizzaModel = new Pizza();
$controller = new MenuController($pizzaModel);
$controller->handleRequest();


