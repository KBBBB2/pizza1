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
     * @param array $data A szállításhoz szükséges adatok: city, address, postal_code, phonenumber, order_id
     * @return bool Sikeres beszúrás esetén true, ellenkező esetben false.
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
            // Új rendelés esetén default állapot lehet 'pending'
            ':status' => $data['status'] ?? 'pending',
            // Ha nincs kijelölt futár, lehet null
            ':deliveryperson_user_id' => $data['deliveryperson_user_id'] ?? null,
            ':order_id' => $data['order_id']
        ]);
    }

    public function updateStatus($deliveryId, $status) {
        $sql = "UPDATE delivery SET status = :status WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':id' => $deliveryId
        ]);
    }

}
?>
