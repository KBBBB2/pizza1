<?php
// models/AdminAccount.php

require_once 'Database.php';

class AdminAccount {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    // Az összes account lekérdezése: tárolt eljárás (getAllAccount)
    public function getAccounts($q = '') {
        if (!empty($q)) {
            // Készítsd elő a keresési feltételt úgy, hogy mindenhol LIKE operátort használj
            $qParam = "%" . $q . "%";
            $stmt = $this->pdo->prepare(
                "SELECT id, firstname, lastname, username, email, phonenumber, created, locked, disabled, ban_expires_at
                 FROM account
                 WHERE firstname LIKE ? OR lastname LIKE ? OR username LIKE ? OR email LIKE ? OR phonenumber LIKE ?"
            );
            $stmt->execute([$qParam, $qParam, $qParam, $qParam, $qParam]);
        } else {
            // Ha nincs keresési feltétel, akkor a tárolt eljárást hívjuk meg
            $stmt = $this->pdo->prepare("CALL getAllAccount()");
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    // Ideiglenes tiltás: tárolt eljárás (sp_tempBan)
    // models/AdminAccount.php
// models/AdminAccount.php

public function tempBan($id, $duration) {
    if ($id <= 0) {
        throw new Exception('Hibás felhasználó ID.');
    }
    
    // Bontsd fel a duration stringet vessző mentén
    $parts = explode(',', $duration);
    $totalSeconds = 0;
    
    foreach ($parts as $part) {
        $part = trim($part);
        // Regex segítségével olvassuk ki a számot és az egységet (perc, óra, nap)
        if (preg_match('/^(\d+)\s*(m|h|d)$/i', $part, $matches)) {
            $num = intval($matches[1]);
            $unit = strtolower($matches[2]);
            
            switch ($unit) {
                case 'm':
                    $totalSeconds += $num * 60;
                    break;
                case 'h':
                    $totalSeconds += $num * 3600;
                    break;
                case 'd':
                    $totalSeconds += $num * 86400;
                    break;
            }
        } else {
            throw new Exception('Érvénytelen időtartam formátum: ' . $part);
        }
    }
    
    // Számoljuk ki a lejárati időt a jelenlegi időhöz képest
    $banExpiresAt = date('Y-m-d H:i:s', time() + $totalSeconds);
    
    // Frissítjük az account rekordot: locked = 1 és a lejárati idő beállítása
    $stmt = $this->pdo->prepare("UPDATE account SET locked = 1, ban_expires_at = ? WHERE id = ?");
    $stmt->execute([$banExpiresAt, $id]);
    
    return $banExpiresAt;
}



    
    // Végleges tiltás: tárolt eljárás (sp_permBan)
    public function permBan($id) {
        if ($id <= 0) {
            throw new Exception('Hibás felhasználó ID.');
        }
        $stmt = $this->pdo->prepare("CALL sp_permBan(?)");
        $stmt->execute([$id]);
        return true;
    }
    
    public function unbanExpired() {
        // Nincs tárolt eljárás, raw query marad
        $stmt = $this->pdo->prepare("UPDATE account SET locked = 0, ban_expires_at = NULL WHERE locked = 1 AND ban_expires_at <= NOW()");
        $stmt->execute();
        return $stmt->rowCount();
    }
}
?>
