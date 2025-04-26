<?php
// models/coupon.php

require_once 'Database.php';

class Coupon {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    /**
     * Lekérdezi a kupon adatait a megadott kód alapján. Csak az aktív és lejárat után még használható kuponok kerülnek vissza.
     *
     * @param string $code A keresett kupon kódja.
     * @return array|false A kupon neve, kódja és értéke vagy false, ha nem található.
     */
    public function getCouponByCode($code) {
        $sql = "SELECT name, code, discount_value 
                FROM coupon 
                WHERE code = :code 
                  AND is_active = 1 
                  AND (expiration_date IS NULL OR expiration_date >= CURDATE())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':code' => $code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
