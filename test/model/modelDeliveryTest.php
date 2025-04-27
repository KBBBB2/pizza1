<?php
// vendor/bin/phpunit test/modelDeliveryTest.php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../model/Delivery.php';

class modelDeliveryTest extends TestCase {
    private $pdoMock;
    private $stmtMock;
    private $deliveryModel;

    protected function setUp(): void {
        // PDO és PDOStatement mock
        $this->pdoMock = $this->createMock(\PDO::class);
        $this->stmtMock = $this->createMock(\PDOStatement::class);

        // Model példány és PDO injektálása reflektálással
        $this->deliveryModel = new Delivery();
        $ref = new \ReflectionClass($this->deliveryModel);
        $prop = $ref->getProperty('pdo');
        $prop->setAccessible(true);
        $prop->setValue($this->deliveryModel, $this->pdoMock);
    }

    public function testCreateDeliveryWithDefaults(): void {
        $data = [
            'city' => 'Budapest',
            'address' => 'Fő utca 1',
            'postal_code' => '1000',
            'phonenumber' => '0612345678',
            'order_id' => 55
        ];
        // Ellenőrizzük csak az SQL struktúrát, a pontos whitespace-et nem
        $this->pdoMock->method('prepare')
            ->with($this->callback(function($sql) {
                return (bool) preg_match(
                    '/INSERT INTO delivery \(city, address, postal_code, phonenumber, status, deliveryperson_user_id, order_id\)\s+VALUES/',
                    $sql
                );
            }))
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([
                ':city' => 'Budapest',
                ':address' => 'Fő utca 1',
                ':postal_code' => '1000',
                ':phonenumber' => '0612345678',
                ':status' => 'pending',
                ':deliveryperson_user_id' => null,
                ':order_id' => 55
            ])
            ->willReturn(true);

        $result = $this->deliveryModel->createDelivery($data);
        $this->assertTrue($result);
    }

    public function testCreateDeliveryWithCustomStatusAndPerson(): void {
        $data = [
            'city' => 'Szeged',
            'address' => 'Tisza Lajos krt. 10',
            'postal_code' => '6720',
            'phonenumber' => '0620456789',
            'status' => 'delivered',
            'deliveryperson_user_id' => 3,
            'order_id' => 77
        ];
        $this->pdoMock->method('prepare')
            ->with($this->callback(function($sql) {
                return (bool) preg_match(
                    '/INSERT INTO delivery \(city, address, postal_code, phonenumber, status, deliveryperson_user_id, order_id\)\s+VALUES/',
                    $sql
                );
            }))
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([
                ':city' => 'Szeged',
                ':address' => 'Tisza Lajos krt. 10',
                ':postal_code' => '6720',
                ':phonenumber' => '0620456789',
                ':status' => 'delivered',
                ':deliveryperson_user_id' => 3,
                ':order_id' => 77
            ])
            ->willReturn(true);

        $result = $this->deliveryModel->createDelivery($data);
        $this->assertTrue($result);
    }

    public function testUpdateStatus(): void {
        $deliveryId = 20;
        $status = 'shipped';
        $sql = "UPDATE delivery SET status = :status WHERE id = :id";
        $this->pdoMock->method('prepare')->with($sql)->willReturn($this->stmtMock);
        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([':status' => $status, ':id' => $deliveryId])
            ->willReturn(true);

        $result = $this->deliveryModel->updateStatus($deliveryId, $status);
        $this->assertTrue($result);
    }

    public function testGetDeliveryById(): void {
        $deliveryId = 42;
        $expected = ['id' => 42, 'city' => 'Debrecen'];
        $sql = "SELECT * FROM delivery WHERE id = :id";
        $this->pdoMock->method('prepare')->with($sql)->willReturn($this->stmtMock);
        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([':id' => $deliveryId])
            ->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn($expected);

        $result = $this->deliveryModel->getDeliveryById($deliveryId);
        $this->assertEquals($expected, $result);
    }

    public function testGetAllDeliveries(): void {
        $expected = [ ['id' => 1], ['id' => 2] ];
        $sql = "SELECT * FROM delivery";
        $this->pdoMock->method('query')->with($sql)->willReturn($this->stmtMock);
        $this->stmtMock->method('fetchAll')->willReturn($expected);

        $result = $this->deliveryModel->getAllDeliveries();
        $this->assertEquals($expected, $result);
    }
}
