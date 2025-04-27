<?php
// models/Delivery.php
require_once 'Database.php';

class Delivery {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Új szállítási (delivery) rekord létrehozása.
     */
    public function createDelivery($data) {
        $sql = "INSERT INTO delivery (city, address, postal_code, phonenumber, status, deliveryperson_user_id, order_id)
                VALUES (:city, :address, :postal_code, :phonenumber, :status, :deliveryperson_user_id, :order_id)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':city' => $data['city'],
            ':address' => $data['address'],
            ':postal_code' => $data['postal_code'],
            ':phonenumber' => $data['phonenumber'],
            ':status' => $data['status'] ?? 'pending',
            ':deliveryperson_user_id' => $data['deliveryperson_user_id'] ?? null,
            ':order_id' => $data['order_id']
        ]);
    }

    /**
     * Szállítási rekord státuszának frissítése.
     */
    public function updateStatus($deliveryId, $status) {
        $sql = "UPDATE delivery SET status = :status WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':id' => $deliveryId
        ]);
    }
    
    /**
     * Egy adott szállítás lekérdezése azonosító alapján.
     * @param int $deliveryId A lekérdezni kívánt szállítás azonosítója.
     * @return mixed A szállítás adatai asszociatív tömbben, vagy false, ha nem található.
     */
    public function getDeliveryById($deliveryId) {
        $sql = "SELECT * FROM delivery WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $deliveryId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Az összes szállítás lekérdezése.
     * @return array Az összes szállítás adatai.
     */
    public function getAllDeliveries() {
        $sql = "SELECT * FROM delivery";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
