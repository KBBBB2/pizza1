<?php

class ControllerResetPassword
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function validateToken($token, $secret)
    {
        list($payload, $sig) = explode('.', $token);
        if (!hash_equals(hash_hmac('sha256', $payload, $secret), $sig)) return false;
        $data = json_decode(base64_decode($payload), true);
        if (!$data || time() > strtotime($data['expires'])) return false;
    
        // alias létrehozása, ha a generateToken még mindig 'user_id'-t használ:
        if (isset($data['user_id'])) {
            $data['userId'] = $data['user_id'];
        }
    
        return $data;
    }
    

    public function resetPassword($userId, $password)
    {
        // Jelszó frissítése a megfelelő adatbázisban
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("UPDATE account SET password=? WHERE id=?");
        $stmt->execute([$hash, $userId]);

        return 'A jelszó sikeresen frissítve.';
    }
}
