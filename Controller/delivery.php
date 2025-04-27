<?php
// controller/DeliveryController.php

require_once __DIR__ . '/../model/Delivery.php';

class DeliveryController {
    private $deliveryModel;

    // Konstruktor: ha nem adunk át modellt, akkor létrehozza a Delivery példányt
    public function __construct($deliveryModel = null) {
        $this->deliveryModel = $deliveryModel ?? new Delivery();
    }

    /**
     * Fizetés feldolgozása.
     * POST kérés esetén a JSON adatot fogadja paraméterként (ha nincs, akkor a php://input-ból olvassa).
     */
    public function handlePayment($data = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Csak POST metódus engedélyezett']);
            return;
        }

        if (is_null($data)) {
            $input = file_get_contents("php://input");
            $data = json_decode($input, true);
        }
        if (!$data) {
            echo json_encode(['error' => 'Érvénytelen JSON adat']);
            return;
        }

        // A POST-ban érkező adatokat a JSON-ból olvassuk
        $data = [
            'city'        => $data['city'] ?? '',
            'address'     => $data['address'] ?? '',
            'postal_code' => $data['postal_code'] ?? '',
            'phonenumber' => $data['phonenumber'] ?? '',
            'order_id'    => $data['order_id'] ?? '',
            'status'      => 'pending'
        ];

        if (
            empty($data['city']) ||
            empty($data['address']) ||
            empty($data['postal_code']) ||
            empty($data['phonenumber']) ||
            empty($data['order_id'])
        ) {
            echo json_encode(['error' => 'Hiányzó mezők']);
            return;
        }

        $result = $this->deliveryModel->createDelivery($data);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Rendelés sikeresen felvéve']);
        } else {
            echo json_encode(['error' => 'Hiba történt a rendelés mentése során']);
        }
    }

    /**
     * Szállítási státusz frissítése.
     * POST kérés esetén a JSON adat tartalmazza a 'delivery_id' és 'status' mezőket.
     */
    public function updateStatus($data = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Csak POST metódus engedélyezett']);
            return;
        }

        if (is_null($data)) {
            $input = file_get_contents("php://input");
            $data = json_decode($input, true);
        }
        if (!$data) {
            echo json_encode(['error' => 'Érvénytelen JSON adat']);
            return;
        }
        if (empty($data['delivery_id']) || empty($data['status'])) {
            echo json_encode(['error' => 'Hiányzó mezők']);
            return;
        }
        // Csak meghatározott státuszokat engedélyezünk:
        if (!in_array($data['status'], ['in transit', 'delivered'])) {
            echo json_encode(['error' => 'Érvénytelen státusz']);
            return;
        }

        $result = $this->deliveryModel->updateStatus($data['delivery_id'], $data['status']);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Státusz frissítve']);
        } else {
            echo json_encode(['error' => 'Hiba történt a frissítés során']);
        }
    }

    /**
     * Egy adott szállítás lekérdezése.
     * A GET vagy POST kérésből érkező adatok alapján határozza meg a delivery_id-t.
     */
    public function getDelivery($deliveryId = null) {
        if (is_null($deliveryId)) {
            // Először GET, majd POST
            $deliveryId = $_GET['delivery_id'] ?? null;
            if (!$deliveryId && $_SERVER['REQUEST_METHOD'] === 'POST') {
                $input = file_get_contents("php://input");
                $data = json_decode($input, true);
                $deliveryId = $data['delivery_id'] ?? null;
            }
        }
        if (!$deliveryId) {
            echo json_encode(['error' => 'Hiányzó delivery_id']);
            return;
        }

        $delivery = $this->deliveryModel->getDeliveryById($deliveryId);
        if ($delivery) {
            echo json_encode(['success' => true, 'delivery' => $delivery]);
        } else {
            echo json_encode(['error' => 'Nem található a megadott delivery_id-val rendelkező rekord']);
        }
    }
    
    /**
     * Az összes szállítás lekérdezése.
     */
    public function getAllDeliveries() {
        $deliveries = $this->deliveryModel->getAllDeliveries();
        echo json_encode(['success' => true, 'deliveries' => $deliveries]);
    }
}
