<?php
// models/Coupon.php

require_once 'Database.php';

class Coupon {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    public function readCoupons($q = '') {
        if (!empty($q)) {
            $qParam = "%" . $q . "%";
            // Szűrés név, description, code vagy discount_type alapján
            $stmt = $this->pdo->prepare("SELECT * FROM coupon WHERE name LIKE ? OR description LIKE ? OR code LIKE ? OR discount_type LIKE ?");
            $stmt->execute([$qParam, $qParam, $qParam, $qParam]);
        } else {
            $stmt = $this->pdo->query("SELECT * FROM coupon");
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    public function createCoupon($data) {
        $sql = "INSERT INTO coupon (name, description, code, discount_type, discount_value, expiration_date, is_active) 
                VALUES (:name, :description, :code, :discount_type, :discount_value, :expiration_date, :is_active)";
        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute([
            ':name'             => $data['name'],
            ':description'      => $data['description'],
            ':code'             => $data['code'],
            ':discount_type'    => $data['discount_type'],
            ':discount_value'   => $data['discount_value'],
            ':expiration_date'  => $data['expiration_date'],
            ':is_active'        => $data['is_active']
        ])) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }
    
    public function updateCoupon($data) {
        $sql = "UPDATE coupon 
                SET name = :name, description = :description, code = :code, discount_type = :discount_type, 
                    discount_value = :discount_value, expiration_date = :expiration_date, is_active = :is_active 
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':name'             => $data['name'],
            ':description'      => $data['description'],
            ':code'             => $data['code'],
            ':discount_type'    => $data['discount_type'],
            ':discount_value'   => $data['discount_value'],
            ':expiration_date'  => $data['expiration_date'],
            ':is_active'        => $data['is_active'],
            ':id'               => $data['id']
        ]);
    }
    
    public function deleteCoupon($id) {
        $stmt = $this->pdo->prepare("DELETE FROM coupon WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
?>
