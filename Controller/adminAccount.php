<?php
require_once __DIR__ . '/../model/AdminAccount.php';

class AdminAccountController {
    private $model;

    public function __construct($model = null) {
        $this->model = $model ?? new AdminAccount();
    }

    public function processRequest(array $input, string $method): array {
        // Ha OPTIONS, jelezd vissza, hogy kiléphetsz
        if ($method === 'OPTIONS') {
            return []; // vagy ['status'=>204]
        }

        $action = $input['action'] ?? '';

        switch ($action) {
            case 'getAccounts':
                $q = $input['q'] ?? '';
                return $this->model->getAccounts($q);

            case 'tempBan':
                $id       = intval($input['id'] ?? 0);
                $duration = $input['duration'] ?? '';
                $expires  = $this->model->tempBan($id, $duration);
                return [
                    'message'        => 'Felhasználó ideiglenesen letiltva.',
                    'ban_expires_at' => $expires
                ];

            case 'permBan':
                $id = intval($input['id'] ?? 0);
                $this->model->permBan($id);
                return ['message'=>'Felhasználó véglegesen letiltva.'];

            default:
                return ['error'=>'Érvénytelen művelet.'];
        }
    }
}
