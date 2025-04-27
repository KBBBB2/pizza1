<?php
// vendor/bin/phpunit test/controller/controllerDeliveryTest.php

use PHPUnit\Framework\TestCase;

// Győződj meg róla, hogy a következő include útvonalak megfelelnek a projekt struktúrájának!
require_once __DIR__ . '/../../controller/Delivery.php';
require_once __DIR__ . '/../../model/Delivery.php';

class controllerDeliveryTest extends TestCase
{
    protected function setUp(): void {
        // Reset global request környezet
        $_REQUEST = [];
        $_SERVER = [];
        $_GET = [];
        $_POST = [];
    }

    public function testHandlePaymentSuccess() {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        // Adatok, amiket a fizetés feldolgozásakor várunk:
        $postData = [
            'city'        => 'Budapest',
            'address'     => 'Fő utca 1',
            'postal_code' => '1011',
            'phonenumber' => '123456789',
            'order_id'    => 'ORD123'
        ];
        
        // Mock the Delivery model
        $deliveryModel = $this->createMock(Delivery::class);
        $deliveryModel->expects($this->once())
            ->method('createDelivery')
            ->with($this->callback(function($data) use ($postData) {
                // Ellenőrizzük, hogy a status "pending"
                return $data['city'] === $postData['city'] &&
                       $data['address'] === $postData['address'] &&
                       $data['postal_code'] === $postData['postal_code'] &&
                       $data['phonenumber'] === $postData['phonenumber'] &&
                       $data['order_id'] === $postData['order_id'] &&
                       $data['status'] === 'pending';
            }))
            ->willReturn(true);
            
        $controller = new DeliveryController($deliveryModel);
        
        ob_start();
        $controller->handlePayment($postData);
        $output = ob_get_clean();
        
        $result = json_decode($output, true);
        $this->assertTrue($result['success']);
        $this->assertEquals('Rendelés sikeresen felvéve', $result['message']);
    }

    public function testHandlePaymentMissingFields() {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        // Hiányos adatok (például nincs megadva az order_id)
        $postData = [
            'city'        => 'Budapest',
            'address'     => 'Fő utca 1',
            'postal_code' => '1011',
            'phonenumber' => '123456789'
            // 'order_id' hiányzik
        ];
        
        $deliveryModel = $this->createMock(Delivery::class);
        // Nem várjuk, hogy a createDelivery metódus meghívásra kerüljön
        $deliveryModel->expects($this->never())
            ->method('createDelivery');
        
        $controller = new DeliveryController($deliveryModel);
        ob_start();
        $controller->handlePayment($postData);
        $output = ob_get_clean();
        
        $result = json_decode($output, true);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Hiányzó mezők', $result['error']);
    }
    
    public function testUpdateStatusSuccess() {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        // Érvényes státusz frissítést szimulálunk
        $postData = [
            'delivery_id' => 'DEL456',
            'status'      => 'in transit'
        ];
        
        $deliveryModel = $this->createMock(Delivery::class);
        $deliveryModel->expects($this->once())
            ->method('updateStatus')
            ->with('DEL456', 'in transit')
            ->willReturn(true);
        
        $controller = new DeliveryController($deliveryModel);
        ob_start();
        $controller->updateStatus($postData);
        $output = ob_get_clean();
        
        $result = json_decode($output, true);
        $this->assertTrue($result['success']);
        $this->assertEquals('Státusz frissítve', $result['message']);
    }
    
    public function testUpdateStatusInvalidStatus() {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        // Hibás státusz érték
        $postData = [
            'delivery_id' => 'DEL456',
            'status'      => 'unknown'
        ];
        
        $deliveryModel = $this->createMock(Delivery::class);
        // A modell metódusát nem hívjuk meg, mert a validáció elkapja a hibát
        $deliveryModel->expects($this->never())
            ->method('updateStatus');
        
        $controller = new DeliveryController($deliveryModel);
        ob_start();
        $controller->updateStatus($postData);
        $output = ob_get_clean();
        
        $result = json_decode($output, true);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Érvénytelen státusz', $result['error']);
    }
    
    public function testGetDeliverySuccess() {
        // Szimuláljuk, hogy GET paraméterben érkezik a delivery_id
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['delivery_id'] = 'DEL789';
        
        $deliveryData = [
            'delivery_id' => 'DEL789',
            'city' => 'Budapest',
            'status' => 'delivered'
        ];
        
        $deliveryModel = $this->createMock(Delivery::class);
        $deliveryModel->expects($this->once())
            ->method('getDeliveryById')
            ->with('DEL789')
            ->willReturn($deliveryData);
            
        $controller = new DeliveryController($deliveryModel);
        ob_start();
        $controller->getDelivery();
        $output = ob_get_clean();
        
        $result = json_decode($output, true);
        $this->assertTrue($result['success']);
        $this->assertEquals('DEL789', $result['delivery']['delivery_id']);
    }
    
    public function testGetDeliveryMissingId() {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        // Nincs delivery_id sem GET sem POST-ban
        
        $deliveryModel = $this->createMock(Delivery::class);
        $deliveryModel->expects($this->never())
            ->method('getDeliveryById');
            
        $controller = new DeliveryController($deliveryModel);
        ob_start();
        $controller->getDelivery();
        $output = ob_get_clean();
        
        $result = json_decode($output, true);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Hiányzó delivery_id', $result['error']);
    }
    
    public function testGetAllDeliveries() {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        $allDeliveries = [
            ['delivery_id' => 'DEL001', 'city' => 'Budapest'],
            ['delivery_id' => 'DEL002', 'city' => 'Debrecen']
        ];
        
        $deliveryModel = $this->createMock(Delivery::class);
        $deliveryModel->expects($this->once())
            ->method('getAllDeliveries')
            ->willReturn($allDeliveries);
        
        $controller = new DeliveryController($deliveryModel);
        ob_start();
        $controller->getAllDeliveries();
        $output = ob_get_clean();
        
        $result = json_decode($output, true);
        $this->assertTrue($result['success']);
        $this->assertCount(2, $result['deliveries']);
    }
}
