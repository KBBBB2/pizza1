<?php
// controllers/DeliveryController.php
require_once '../model/Delivery.php';

class DeliveryController {
    private $deliveryModel;

    public function __construct() {
        $this->deliveryModel = new Delivery();
    }

    /**
     * A fizetés gomb megnyomásakor meghívott metódus.
     * A POST metódusból veszi át az adatokat és beszúrja őket a delivery táblába.
     */
    public function handlePayment() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // JSON adat beolvasása a bemeneti streamből
            $input = file_get_contents("php://input");
            $data = json_decode($input, true);
    
            // Ellenőrizd, hogy sikerült-e a dekódolás
            if (!$data) {
                echo json_encode(['error' => 'Érvénytelen JSON adat']);
                exit;
            }
    
            // A POST-ban érkező adatokat itt már a JSON-ból olvassuk
            $data = [
                'city'        => $data['city'] ?? '',
                'address'     => $data['address'] ?? '',
                'postal_code' => $data['postal_code'] ?? '',
                'phonenumber' => $data['phonenumber'] ?? '',
                'order_id'    => $data['order_id'] ?? '',
                'status'      => 'pending'
            ];
    
            if (empty($data['city']) || empty($data['address']) || empty($data['postal_code']) || empty($data['phonenumber']) || empty($data['order_id'])) {
                echo json_encode(['error' => 'Hiányzó mezők']);
                exit;
            }
    
            $result = $this->deliveryModel->createDelivery($data);
    
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Rendelés sikeresen felvéve']);
            } else {
                echo json_encode(['error' => 'Hiba történt a rendelés mentése során']);
            }
        }
    }
}    


// A kérést kezelő futtatás:
$controller = new DeliveryController();
$controller->handlePayment();
?>
