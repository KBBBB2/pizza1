<?php
// vendor/bin/phpunit test/controller/controllerAdminPizzaTest.php

use PHPUnit\Framework\TestCase;

// Győződj meg róla, hogy a következő include útvonalak megfelelnek a projekt struktúrájának!
require_once __DIR__ . '/../../controller/AdminPizza.php';
require_once __DIR__ . '/../../model/Pizza.php';

class controllerAdminPizzaTest extends TestCase
{
    protected function setUp(): void {
        // A globális változók alaphelyzetbe állítása
        $_REQUEST = [];
        $_SERVER = [];
    }

    public function testGetAllPizzas() {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        // Pizza modell mock létrehozása
        $pizzaModel = $this->createMock(Pizza::class);
        $pizzaModel->expects($this->once())
            ->method('getAllPizzas')
            ->willReturn([['id' => 1, 'name' => 'Margherita']]);

        $controller = new AdminPizza($pizzaModel);
        $response = $controller->handleRequest();
        $result = json_decode($response, true);

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['data']);
        $this->assertEquals('Margherita', $result['data'][0]['name']);
    }

    public function testCreatePizzaSuccess() {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_REQUEST = [
            'action'    => 'create',
            'name'      => 'Pepperoni',
            'crust'     => 'Thin',
            'cutstyle'  => 'Square',
            'pizzasize' => 'Large',
            'ingredient'=> 'Pepperoni',
            'price'     => '10.99'
        ];

        $pizzaModel = $this->createMock(Pizza::class);
        $pizzaModel->expects($this->once())
            ->method('insertPizza')
            ->with('Pepperoni', 'Thin', 'Square', 'Large', 'Pepperoni', '10.99')
            ->willReturn(42);

        $controller = new AdminPizza($pizzaModel);
        $response = $controller->handleRequest();
        $result = json_decode($response, true);

        $this->assertTrue($result['success']);
        $this->assertEquals(42, $result['id']);
    }

    public function testCreatePizzaMissingFields() {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_REQUEST = [
            'action' => 'create',
            'name'   => 'Pepperoni'
            // Hiányoznak a többi kötelező mező
        ];

        $pizzaModel = $this->createMock(Pizza::class);
        $pizzaModel->expects($this->never())
            ->method('insertPizza');

        $controller = new AdminPizza($pizzaModel);
        $response = $controller->handleRequest();
        $result = json_decode($response, true);

        $this->assertFalse($result['success']);
        $this->assertEquals("Missing fields for creation", $result['error']);
    }

    public function testUpdatePizzaSuccess() {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_REQUEST = [
            'id'   => 5,
            'name' => 'Veggie'
        ];

        $pizzaModel = $this->createMock(Pizza::class);
        $pizzaModel->expects($this->once())
            ->method('updatePizza')
            ->with(5, ['name' => 'Veggie'])
            ->willReturn(true);

        $controller = new AdminPizza($pizzaModel);
        $response = $controller->handleRequest();
        $result = json_decode($response, true);
        $this->assertTrue($result['success']);
    }

    public function testUpdatePizzaNoData() {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_REQUEST = [
            'id' => 5
            // Nem adunk át semmilyen frissítendő adatot
        ];

        $pizzaModel = $this->createMock(Pizza::class);
        $pizzaModel->expects($this->never())
            ->method('updatePizza');

        $controller = new AdminPizza($pizzaModel);
        $response = $controller->handleRequest();
        $result = json_decode($response, true);
        $this->assertFalse($result['success']);
        $this->assertEquals("No data to update", $result['error']);
    }

    public function testDeletePizzaSuccess() {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_REQUEST = [
            'id' => 3
        ];

        $pizzaModel = $this->createMock(Pizza::class);
        $pizzaModel->expects($this->once())
            ->method('deletePizza')
            ->with(3)
            ->willReturn(true);

        $controller = new AdminPizza($pizzaModel);
        $response = $controller->handleRequest();
        $result = json_decode($response, true);
        $this->assertTrue($result['success']);
    }

    public function testDeletePizzaNoId() {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_REQUEST = [];

        $pizzaModel = $this->createMock(Pizza::class);
        $pizzaModel->expects($this->never())
            ->method('deletePizza');

        $controller = new AdminPizza($pizzaModel);
        $response = $controller->handleRequest();
        $result = json_decode($response, true);
        $this->assertFalse($result['success']);
        $this->assertEquals("No id provided", $result['error']);
    }
}
