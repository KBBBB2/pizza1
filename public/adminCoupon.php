<?php
require_once __DIR__ . '/../Controller/adminCoupon.php';

if (php_sapi_name() !== 'cli') {
    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0);
    }

    if (isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
        $rawData = file_get_contents("php://input");
        $jsonData = json_decode($rawData, true);
        if (is_array($jsonData)) {
            $_REQUEST = array_merge($_REQUEST, $jsonData);
        }
    }

    $result = handleRequest($_SERVER['REQUEST_METHOD'], $_REQUEST);
    http_response_code($result['status']);
    echo json_encode($result['data']);
}
