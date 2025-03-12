<?php
// controllers/AdminAccountController.php

require_once '../model/adminAccount.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Ha a bejövő tartalom JSON, akkor dekódoljuk
if (isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $rawData = file_get_contents("php://input");
    $jsonData = json_decode($rawData, true);
    if (is_array($jsonData)) {
        $_REQUEST = array_merge($_REQUEST, $jsonData);
    }
}

$action = $_REQUEST['action'] ?? '';

$adminAccount = new AdminAccount();

try {
    if ($action == 'getAccounts') {
        $q = $_REQUEST['q'] ?? '';
        $accounts = $adminAccount->getAccounts($q);
        echo json_encode($accounts);

    } elseif ($action == 'tempBan') {
        $id = intval($_REQUEST['id'] ?? 0);
        $duration = $_REQUEST['duration'] ?? '';
        $banExpiresAt = $adminAccount->tempBan($id, $duration);
        echo json_encode([
            'message'        => 'Felhasználó ideiglenesen letiltva.',
            'ban_expires_at' => $banExpiresAt
        ]);


    } elseif ($action == 'permBan') {
        $id = intval($_REQUEST['id'] ?? 0);
        $adminAccount->permBan($id);
        echo json_encode(['message' => 'Felhasználó véglegesen letiltva.']);
    } else {
        echo json_encode(['error' => 'Érvénytelen művelet.']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
