<?php

// Betöltjük a szükséges osztályokat
require_once 'model/Database.php';
require_once 'model/Pizza.php';

class model_pizza_integrationTest extends PHPUnit\Framework\TestCase {

    protected static $pizza;

    // Az osztály szintű beállítások futnak a tesztek előtt
    public static function setUpBeforeClass(): void {
        self::$pizza = new Pizza();
    }

    // Beszúrás, lekérdezés és törlés tesztelése
    public function testInsertAndGetPizza() {
        // Új pizza adatai
        $name = 'Teszt Pizza';
        $crust = 'Vékony';
        $cutstyle = 'Háromszög';
        $pizzasize = 'Közepes';
        $ingredient = 'Paradicsom, sajt, bazsalikom';
        $price = 1000;

        // Pizza beszúrása
        $newId = self::$pizza->insertPizza($name, $crust, $cutstyle, $pizzasize, $ingredient, $price);
        $this->assertNotFalse($newId, "Pizza beszúrás sikertelen.");

        // Beszúrt pizza lekérdezése
        $pizzaData = self::$pizza->getPizza($newId);
        $this->assertNotEmpty($pizzaData, "A lekérdezett pizza üres.");
        $this->assertEquals($name, $pizzaData['name'], "A pizza neve nem egyezik a beszúrt adattal.");

        // Cleanup: beszúrt pizza törlése
        $deleteResult = self::$pizza->deletePizza($newId);
        $this->assertTrue($deleteResult, "Pizza törlése sikertelen.");
    }

    // Pizza frissítésének tesztelése
    public function testUpdatePizza() {
        // Beszúrunk egy pizzát, amit frissítünk
        $name = 'Frissítendő Pizza';
        $crust = 'Vékony';
        $cutstyle = 'Négyzet';
        $pizzasize = 'Nagy';
        $ingredient = 'Sajt, sonka, gomba';
        $price = 1500;

        $newId = self::$pizza->insertPizza($name, $crust, $cutstyle, $pizzasize, $ingredient, $price);
        $this->assertNotFalse($newId, "Pizza beszúrás sikertelen.");

        // Frissítjük a pizza adatait
        $updateData = ['name' => 'Frissített Pizza', 'price' => 2300];
        $updateResult = self::$pizza->updatePizza($newId, $updateData);
        $this->assertTrue($updateResult, "Pizza frissítése sikertelen.");

        // Lekérdezzük a frissített pizzát
        $updatedPizza = self::$pizza->getPizza($newId);
        $this->assertEquals('Frissített Pizza', $updatedPizza['name'], "A pizza neve nem frissült megfelelően.");
        $this->assertEquals(2300, $updatedPizza['price'], "A pizza ára nem frissült megfelelően.");

        // Cleanup: töröljük a beszúrt pizzát
        $deleteResult = self::$pizza->deletePizza($newId);
        $this->assertTrue($deleteResult, "Pizza törlése sikertelen.");
    }
}
?>
