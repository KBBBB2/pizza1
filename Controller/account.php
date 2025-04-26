<?php
// controller/AccountController.php

require_once __DIR__ . '/../model/Account.php';
require_once __DIR__ . '/../vendor/autoload.php';  // ha ide is szeretnéd tenni

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AccountController {
    private Account $account;
    private string  $jwt_secret;
    private int     $jwt_expiration;

    public function __construct(
        Account $account = null,
        string  $jwt_secret = 'EZEAZ_A_TE_TITKOS_KULCSOD',
        int     $jwt_expiration = 3600
    ) {
        $this->account        = $account ?? new Account();
        $this->jwt_secret     = $jwt_secret;
        $this->jwt_expiration = $jwt_expiration;
    }

    // ELSŐSORBAN: ezekkel tesztelhető az IO
    protected function getRawInput(): string {
        return file_get_contents('php://input');
    }

    protected function sendStatus(int $code): void {
        http_response_code($code);
    }

    protected function sendJson(array $data): void {
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    // A bejárat
    public function processRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendStatus(405);
            $this->sendJson(['error'=>'Csak POST.', 'status'=>405]);
            return;
        }

        $input = json_decode($this->getRawInput(), true);
        if (!is_array($input)) {
            $this->sendStatus(400);
            $this->sendJson(['error'=>'Érvénytelen JSON.', 'status'=>400]);
            return;
        }
        
        if (empty($input['action'])) {
            $this->sendStatus(400);
            $this->sendJson(['error'=>'Hiányzik az action.', 'status'=>400]);
            return;
        }

        switch ($input['action'] ?? '') {
            case 'register':
                $this->handleRegister($input);
                break;

            case 'login':
                $this->handleLogin($input);
                break;

            case 'getUserData':
            case 'update':
                $this->handleAuthActions($input['action'], $input);
                break;

            default:
                $this->sendStatus(400);
                $this->sendJson(['error'=>'Ismeretlen action.', 'status'=>400]);
        }
    }

    private function handleRegister(array $input): void {
        if (empty($input['username']) || empty($input['password'])) {
            $this->sendStatus(400);
            $this->sendJson(['error'=>'Felhasználónév és jelszó kötelező.', 'status'=>400]);
            return;
        }
        $res = $this->account->register(
            $input['username'],
            $input['password'],
            $input['firstname']  ?? '',
            $input['lastname']   ?? '',
            $input['email']      ?? '',
            $input['phonenumber']?? ''
        );
        if (isset($res['success'])) {
            $this->sendJson(['success'=>$res['success']]);
        } else {
            $this->sendStatus($res['status']);
            $this->sendJson(['error'=>$res['error'],'status'=>$res['status']]);
        }
    }

    private function handleLogin(array $input): void {
        if (empty($input['username']) || empty($input['password'])) {
            $this->sendStatus(400);
            $this->sendJson(['error'=>'Felhasználónév és jelszó kötelező.', 'status'=>400]);
            return;
        }
        $res = $this->account->login(trim($input['username']), $input['password']);
        if (isset($res['success'])) {
            $iat = time();
            $exp = $iat + $this->jwt_expiration;
            $payload = [
                'iat'  => $iat,
                'exp'  => $exp,
                'user' => [
                    'id'   => $res['user']['id'],
                    'role' => $res['user']['role']
                ]
            ];
            $token = JWT::encode($payload, $this->jwt_secret, 'HS256');
            $this->sendJson([
                'success'=>$res['success'],
                'token'  =>$token,
                'user'   =>$res['user'],
                'status' =>$res['status']
            ]);
        } else {
            $this->sendStatus($res['status']);
            $this->sendJson(['error'=>$res['error'],'status'=>$res['status']]);
        }
    }

    private function handleAuthActions(string $action, array $input): void {
        // Token kicsipegetése az Authorization headertől
        $headers = getallheaders();
        $auth    = $headers['Authorization'] ?? '';
        $jwt     = str_starts_with($auth, 'Bearer ')
                 ? substr($auth, 7)
                 : $auth;

        try {
            $decoded = JWT::decode($jwt, new Key($this->jwt_secret, 'HS256'));
            $userId  = $decoded->user->id;
        } catch (\Exception $e) {
            $this->sendStatus(401);
            $this->sendJson(['error'=>'Érvénytelen token.','status'=>401]);
            return;
        }

        if ($action === 'getUserData') {
            $userData = $this->account->getUserData($userId);
            $this->sendJson(['user'=>$userData,'status'=>200]);
        } else {
            // updateAccount/updatePassword ág
            $res = $this->account->updateAccount(
                $userId,
                $input['firstname']   ?? '',
                $input['lastname']    ?? '',
                $input['username']    ?? '',
                $input['email']       ?? '',
                $input['phonenumber'] ?? ''
            );
            if (isset($res['error'])) {
                $this->sendStatus($res['status']);
                $this->sendJson($res);
                return;
            }
            if (!empty($input['current-password']) && !empty($input['new-password'])) {
                $pass = $this->account->updatePassword(
                    $userId,
                    $input['current-password'],
                    $input['new-password']
                );
                if (isset($pass['error'])) {
                    $this->sendStatus($pass['status']);
                    $this->sendJson($pass);
                    return;
                }
                $res['success'] .= ' ' . $pass['success'];
            }
            $this->sendJson($res);
        }
    }
}
