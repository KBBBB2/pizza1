<?php
// CORS
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
header('Content-Type: application/json; charset=UTF-8');

// Autoload / include-ok
require_once('../model/database.php');
require_once('../controller/reset_password.php');

// Secret kulcs, amivel a tokent aláírod
$secret = 'valami_egyedi_titkos_kulcs';

// PDO példány
$db = Database::getInstance()->getConnection();
$ctrl = new ControllerResetPassword($db);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Token-ellenőrzés
    $token = $_GET['token'] ?? '';
    $data = $ctrl->validateToken($token, $secret);
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Érvénytelen vagy lejárt token.']);
        exit;
    }
    // Sikeres validálás: visszaadhatod a lejárati időt vagy felhasználóadatot
    echo json_encode(['success' => true, 'userId' => $data['userId']]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Bemeneti JSON beolvasása
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    if (!$json || !isset($json['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Hiányzó adat a kérésben.']);
        exit;
    }
    // Token paraméter továbbra is GET-ben
    $token = $_GET['token'] ?? '';
    $data = $ctrl->validateToken($token, $secret);
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Érvénytelen vagy lejárt token.']);
        exit;
    }
    // Jelszó reset
    try {
        $msg = $ctrl->resetPassword($data['userId'], $json['password']);
        echo json_encode(['success' => $msg]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Nem sikerült frissíteni a jelszót.']);
    }
    exit;
}

// Ha ide jut valami furcsa kérelmezés...
http_response_code(405);
echo json_encode(['error' => 'Nem támogatott kérés.']);
