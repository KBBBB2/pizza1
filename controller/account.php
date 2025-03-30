<?php
// controllers/AccountController.php

require_once '../model/Account.php';

class AccountController {
    private $account;

    public function __construct() {
        $this->account = new Account();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function processRequest() {
        header("Content-Type: application/json; charset=UTF-8");

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                "error" => "Csak POST metódusú kérések engedélyezettek.",
                "status" => 405
            ]);
            exit;
        }

        $input = json_decode(file_get_contents("php://input"), true);
        if (!$input) {
            http_response_code(400);
            echo json_encode([
                "error" => "Érvénytelen JSON bemenet.",
                "status" => 400
            ]);
            exit;
        }

        if (!isset($input['action'])) {
            http_response_code(400);
            echo json_encode([
                "error" => "Hiányzik az action paraméter.",
                "status" => 400
            ]);
            exit;
        }

        $action = $input['action'];

        if ($action === 'register') {
            if (!isset($input['username']) || !isset($input['password'])) {
                http_response_code(400);
                echo json_encode([
                    "error" => "Felhasználónév és jelszó megadása kötelező.",
                    "status" => 400
                ]);
                exit;
            }
            $username = $input['username'];
            $password = $input['password'];
            $firstname = $input['firstname'];
            $lastname = $input['lastname'];
            $email = $input['email'];
            $phonenumber = $input['phonenumber'];

            $result = $this->account->register($username, $password, $firstname, $lastname, $email, $phonenumber);
            if (isset($result['success'])) {
                echo json_encode(["success" => $result['success']]);
            } else {
                http_response_code($result['status']);
                echo json_encode([
                    "error" => $result['error'],
                    "status" => $result['status']
                ]);
            }
        } else if ($action === 'login') {
            if (!isset($input['username']) || !isset($input['password'])) {
                http_response_code(400);
                echo json_encode([
                    "error" => "Felhasználónév és jelszó megadása kötelező.",
                    "status" => 400
                ]);
                exit;
            }
            $username = trim($input['username']);
            $password = $input['password'];
            $result = $this->account->login($username, $password);
            // Példa a login ág módosítására a Controller-ben
            if (isset($result['success'])) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['user_id'] = $result['user']['id'];
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $result['user']['role'];
                echo json_encode([
                    "success" => $result['success'],
                    "status" => $result['status']
                ]);
            } else {
                http_response_code($result['status']);
                echo json_encode([
                    "error" => $result['error'],
                    "status" => $result['status']
                ]);
            }
        } else if ($action === 'getUserData') {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode([
                    "error" => "Be kell jelentkezni a megtekintéshez.",
                    "status" => 401
                ]);
                exit;
            }
            $userId = $_SESSION['user_id'];
            $userData = $this->account->getUserData($userId);
            echo json_encode(["user" => $userData, "status" => 200]);
        }
        // Adatok frissítése (beleértve a jelszó módosítást)
        else if ($action === 'update') {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(["error" => "Be kell jelentkezni a módosításhoz.", "status" => 401]);
                exit;
            }
            $userId = $_SESSION['user_id'];
            
            // Alap adatok frissítése
            $firstname = $input['firstname'] ?? '';
            $lastname  = $input['lastname'] ?? '';
            $username  = $input['username'] ?? '';
            $email     = $input['email'] ?? '';
            $phonenumber     = $input['phonenumber'] ?? '';

            $result = $this->account->updateAccount($userId, $firstname, $lastname, $username, $email, $phonenumber);
            if (isset($result['error'])) {
                http_response_code($result['status']);
                echo json_encode($result);
                exit;
            }
            
            // Jelszó módosítás, ha a mezők ki vannak töltve
            if (!empty($input['current-password']) && !empty($input['new-password'])) {
                $currentPassword = $input['current-password'];
                $newPassword     = $input['new-password'];
                $passResult = $this->account->updatePassword($userId, $currentPassword, $newPassword);
                if (isset($passResult['error'])) {
                    http_response_code($passResult['status']);
                    echo json_encode($passResult);
                    exit;
                }
                // Ha a jelszó módosítása is sikeres, összekombinálhatod az üzenetet:
                $result['success'] .= " " . $passResult['success'];
            }
            echo json_encode($result);
        } else {
            http_response_code(400);
            echo json_encode([
                "error" => "Érvénytelen action paraméter.",
                "status" => 400
            ]);
        }
    }
}

$controller = new AccountController();
$controller->processRequest();
?>
