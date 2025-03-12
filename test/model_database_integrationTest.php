<?php
//  ./vendor/bin/phpunit test/model_database_integrationTest.php

require_once 'model/Database.php';

class model_database_integrationTest extends PHPUnit\Framework\TestCase {

    public function testGetInstanceReturnsValidPDO() {
        $db = Database::getInstance();
        $this->assertInstanceOf(Database::class, $db, 'A getInstance() metódusnak Database példányt kell visszaadnia.');

        $pdo = $db->getConnection();
        $this->assertInstanceOf(PDO::class, $pdo, 'A getConnection() metódusnak PDO példányt kell visszaadnia.');

        // Ellenőrizzük, hogy a hibakezelés attribútum megfelelően van beállítva
        $errMode = $pdo->getAttribute(PDO::ATTR_ERRMODE);
        $this->assertEquals(PDO::ERRMODE_EXCEPTION, $errMode, 'A PDO ERRMODE attribútumnak PDO::ERRMODE_EXCEPTION értéket kell kapnia.');
    }
}
