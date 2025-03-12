<?php
require_once 'Database.php';

class Account {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // Regisztráció
    public function register($username, $password, $firstname, $lastname, $email, $phonenumber) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("CALL registAndCheck(?, ?, ?, ?, ?, ?, @p_result)");
        if ($stmt->execute([$username, $passwordHash, $firstname, $lastname, $email, $phonenumber])) {
            // Ürítjük a többi esetleges eredményhalmazt
            while ($stmt->nextRowset()) { ; }
            $result = $this->pdo->query("SELECT @p_result")->fetchColumn();
            if ($result != 1) {
                return ['error' => "A megadott felhasználónév vagy email már foglalt.", 'status' => 400];
            }
            // Az utolsó beszúrt azonosító lekérése
            $accountId = $this->pdo->query("SELECT LAST_INSERT_ID()")->fetchColumn();
            // Beszúrjuk a userroles táblába (ehhez nincs tárolt eljárás)
            $stmtRole = $this->pdo->prepare("INSERT INTO userroles (id, role) VALUES (?, 'customer')");
            $stmtRole->execute([$accountId]);
            return ['success' => "Sikeres regisztráció."];
        } else {
            return ['error' => "Hiba történt a regisztráció során.", 'status' => 500];
        }
    }

    // Belépés
    public function login($username, $password) {
        $stmt = $this->pdo->prepare("CALL getAccountLogin(?)");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        // Lezárjuk a korábbi eredményhalmazt
        $stmt->closeCursor();

        if (!$user) {
            return ['error' => "Érvénytelen hitelesítési adatok.", 'status' => 400];
        }
        if (isset($user['disabled']) && $user['disabled'] == 1) {
            return ['error' => "A fiók véglegesen le van tiltva.", 'status' => 403];
        }
        if (isset($user['locked']) && $user['locked'] == 1) {
            return ['error' => "A fiók ideiglenesen le van tiltva.", 'status' => 403];
        }
        if (password_verify($password, $user['password'])) {
            // Lekérdezzük a felhasználó szerepét
            $stmtRole = $this->pdo->prepare("SELECT role FROM userroles WHERE id = ?");
            $stmtRole->execute([$user['id']]);
            $roleData = $stmtRole->fetch(PDO::FETCH_ASSOC);
            if ($roleData) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $roleData['role']; // Itt mentjük a szerepet
                echo json_encode([
                    "success" => "Sikeres belépés.",
                    "status" => 200,
                    "user" => array_merge($user, ["role" => $roleData['role']])
                ]);
            } else {
                echo json_encode([
                    "error" => "Felhasználóhoz nincs társítva szerep.",
                    "status" => 500
                ]);
            }
            exit;
        } else {
            return ['error' => "Érvénytelen hitelesítési adatok.", 'status' => 400];
        }
    }

    public function getUserData($userId) {
        // Nincs tárolt eljárás, raw query marad
        $stmt = $this->pdo->prepare("SELECT firstname, lastname, username, email, phonenumber FROM account WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateAccount($userId, $firstname, $lastname, $username, $email, $phonenumber) {
        // Nincs tárolt eljárás, raw query marad
        $stmt = $this->pdo->prepare("UPDATE account SET firstname = ?, lastname = ?, username = ?, email = ?, phonenumber = ? WHERE id = ?");
        if ($stmt->execute([$firstname, $lastname, $username, $email, $phonenumber, $userId])) {
            return ['success' => 'Fiók adatok frissítve.'];
        } else {
            return ['error' => 'Hiba történt a frissítés során.', 'status' => 500];
        }
    }

    public function updatePassword($userId, $currentPassword, $newPassword) {
        // Nincs tárolt eljárás, raw query marad
        $stmt = $this->pdo->prepare("SELECT password FROM account WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return ['error' => 'Hibás jelenlegi jelszó.', 'status' => 400];
        }
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("UPDATE account SET password = ? WHERE id = ?");
        if ($stmt->execute([$newPasswordHash, $userId])) {
            return ['success' => 'Jelszó sikeresen módosítva.'];
        } else {
            return ['error' => 'Hiba történt a jelszó módosítása során.', 'status' => 500];
        }
    }
}
?>
