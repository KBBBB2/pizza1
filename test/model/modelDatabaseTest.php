<?php
// vendor/bin/phpunit test/modelDatabaseTest.php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../model/Database.php';

class modelDatabaseTest extends TestCase {
    protected function setUp(): void {
        // Reset singleton instance via reflection
        $ref = new \ReflectionClass(\Database::class);
        $prop = $ref->getProperty('instance');
        $prop->setAccessible(true);
        $prop->setValue(null, null);
    }

    public function testGetInstanceReturnsSameObject(): void {
        $db1 = Database::getInstance();
        $db2 = Database::getInstance();
        $this->assertSame($db1, $db2, 'Database::getInstance() should return the same instance');
    }

    public function testGetConnectionReturnsPdo(): void {
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        $this->assertInstanceOf(PDO::class, $pdo, 'getConnection() should return a PDO instance');
        // Check error mode is exception
        $this->assertEquals(
            PDO::ERRMODE_EXCEPTION,
            $pdo->getAttribute(PDO::ATTR_ERRMODE),
            'PDO should be configured to throw exceptions on error'
        );
    }

}