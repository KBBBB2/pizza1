<?php
// CORS és alap header beállítások
$origin = $_SERVER['HTTP_ORIGIN'] ?? 'http://localhost';
header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

// OPTIONS preflight kezelése
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Max-Age: 86400");
    exit(0);
}

// Autoload, model és controller betöltése
require_once __DIR__ . '/../vendor/autoload.php'; // Ha használod a Composer autoloadot
require_once __DIR__ . '/../model/AdminAccount.php';
require_once __DIR__ . '/../controller/AdminAccount.php';

// JSON input dekódolása (ha van)
$inputData = json_decode(file_get_contents('php://input'), true);

// Ha a JSON dekódolása nem sikerült, próbáljuk meg az $_REQUEST-et
if (!is_array($inputData)) {
    $inputData = $_REQUEST;
}

// Modell és controller példányosítása
$adminAccountModel = new AdminAccount();
$controller = new AdminAccountController($adminAccountModel);

// A request paraméterek olvasása
// Ha GET vagy POST metódust használsz, érdemes lehet a bemeneti adatokat megfelelően kezelni.
// Ebben a példában a $_REQUEST tömböt használjuk, de igény szerint ezt finomíthatod.
$response = $controller->processRequest($inputData, $_SERVER['REQUEST_METHOD']);

// Válasz JSON formátumban történő visszaküldése
echo json_encode($response);
