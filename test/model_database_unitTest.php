<?php
// ./vendor/bin/phpunit test/model_database_unitTest.php

require_once 'model/Database.php';

class model_database_unitTest extends PHPUnit\Framework\TestCase {

    public function testSingletonBehavior() {
        // Két hívás ugyanarra a példányra kell, hogy mutasson
        $instance1 = Database::getInstance();
        $instance2 = Database::getInstance();
        $this->assertSame($instance1, $instance2, 'A Database osztálynak singletonként kell működnie.');
    }

    public function testGetConnectionReturnsPDO() {
        // Ellenőrizzük, hogy a getConnection() metódus egy PDO példányt ad vissza
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        $this->assertInstanceOf(PDO::class, $pdo, 'A getConnection() metódusnak PDO példányt kell visszaadnia.');
    }
}
