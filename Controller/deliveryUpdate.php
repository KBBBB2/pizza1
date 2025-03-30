<?php
// controllers/DeliveryController.php
require_once '../model/Delivery.php';

class DeliveryController {
    private $deliveryModel;

    public function __construct() {
        $this->deliveryModel = new Delivery();
    }

    /**
     * Frissíti a szállítás státuszát a POST kérés alapján.
     * Elvárja, hogy a JSON tartalmazza a 'delivery_id' és 'status' mezőket.
     */
    public function updateStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = file_get_contents("php://input");
            $data = json_decode($input, true);

            if (!$data) {
                echo json_encode(['error' => 'Érvénytelen JSON adat']);
                exit;
            }

            if (empty($data['delivery_id']) || empty($data['status'])) {
                echo json_encode(['error' => 'Hiányzó mezők']);
                exit;
            }

            // Csak meghatározott státuszokat engedélyezünk:
            if (!in_array($data['status'], ['in transit', 'delivered'])) {
                echo json_encode(['error' => 'Érvénytelen státusz']);
                exit;
            }

            $result = $this->deliveryModel->updateStatus($data['delivery_id'], $data['status']);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Státusz frissítve']);
            } else {
                echo json_encode(['error' => 'Hiba történt a frissítés során']);
            }
        }
    }
}

// Például itt kezeljük a kérés útvonalát:
$controller = new DeliveryController();
$controller->updateStatus();
?>
