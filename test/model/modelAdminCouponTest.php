<?php
// vendor/bin/phpunit test/modelAdminCouponTest.php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../model/adminCoupon.php';

class modelAdminCouponTest extends TestCase {
    private $pdoMock;
    private $stmtMock;
    private $couponModel;

    protected function setUp(): void {
        // PDO és PDOStatement mock
        $this->pdoMock = $this->createMock(\PDO::class);
        $this->stmtMock = $this->createMock(\PDOStatement::class);

        // Model példány és PDO injektálása reflektálással
        $this->couponModel = new AdminCoupon();
        $ref = new \ReflectionClass($this->couponModel);
        $prop = $ref->getProperty('pdo');
        $prop->setAccessible(true);
        $prop->setValue($this->couponModel, $this->pdoMock);
    }

    public function testReadCouponsWithoutQuery(): void {
        $expected = [ ['id' => 1], ['id' => 2] ];
        $sql = "SELECT * FROM coupon";
        $this->pdoMock->method('query')->with($sql)->willReturn($this->stmtMock);
        $this->stmtMock->method('fetchAll')->with(PDO::FETCH_ASSOC)->willReturn($expected);

        $result = $this->couponModel->readCoupons();
        $this->assertEquals($expected, $result);
    }

    public function testReadCouponsWithQuery(): void {
        $q = 'sale';
        $qParam = "%{$q}%";
        $expected = [ ['id' => 3, 'code' => 'SALE20'] ];
        $this->pdoMock->method('prepare')
            ->with($this->callback(function($sql) {
                $normalized = preg_replace('/\s+/', ' ', $sql);
                return preg_match(
                    '/SELECT \* FROM coupon WHERE name LIKE \? OR description LIKE \? OR code LIKE \? OR discount_type LIKE \?/',
                    $normalized
                ) > 0;
            }))
            ->willReturn($this->stmtMock);
        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([$qParam, $qParam, $qParam, $qParam])
            ->willReturn(true);
        $this->stmtMock->method('fetchAll')->with(PDO::FETCH_ASSOC)->willReturn($expected);

        $result = $this->couponModel->readCoupons($q);
        $this->assertEquals($expected, $result);
    }

    public function testCreateCouponSuccess(): void {
        $data = [
            'name' => 'Discount',
            'description' => 'Test coupon',
            'code' => 'TEST10',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'expiration_date' => '2025-12-31',
            'is_active' => 1
        ];
        $this->pdoMock->method('prepare')
            ->with($this->callback(function($sql) {
                $normalized = preg_replace('/\s+/', ' ', $sql);
                return preg_match(
                    '/INSERT INTO coupon \(name, description, code, discount_type, discount_value, expiration_date, is_active\) VALUES/',
                    $normalized
                ) > 0;
            }))
            ->willReturn($this->stmtMock);
        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([
                ':name' => 'Discount',
                ':description' => 'Test coupon',
                ':code' => 'TEST10',
                ':discount_type' => 'percent',
                ':discount_value' => 10,
                ':expiration_date' => '2025-12-31',
                ':is_active' => 1
            ])
            ->willReturn(true);
        $this->pdoMock->method('lastInsertId')->willReturn('100');

        $result = $this->couponModel->createCoupon($data);
        $this->assertEquals('100', $result);
    }

    public function testCreateCouponFailure(): void {
        $data = [
            'name' => 'Fail',
            'description' => 'Should fail',
            'code' => 'FAIL',
            'discount_type' => 'fixed',
            'discount_value' => 5,
            'expiration_date' => '2025-01-01',
            'is_active' => 0
        ];
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('execute')->willReturn(false);

        $result = $this->couponModel->createCoupon($data);
        $this->assertFalse($result);
    }

    public function testUpdateCoupon(): void {
        $data = [
            'id' => 5,
            'name' => 'Upd',
            'description' => 'Desc',
            'code' => 'UPD',
            'discount_type' => 'fixed',
            'discount_value' => 20,
            'expiration_date' => '2025-06-30',
            'is_active' => 1
        ];
        $this->pdoMock->method('prepare')
            ->with($this->callback(function($sql) {
                $normalized = preg_replace('/\s+/', ' ', $sql);
                return preg_match(
                    '/UPDATE coupon SET name = :name, description = :description, code = :code, discount_type = :discount_type, discount_value = :discount_value, expiration_date = :expiration_date, is_active = :is_active WHERE id = :id/',
                    $normalized
                ) > 0;
            }))
            ->willReturn($this->stmtMock);
        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([
                ':name' => 'Upd',
                ':description' => 'Desc',
                ':code' => 'UPD',
                ':discount_type' => 'fixed',
                ':discount_value' => 20,
                ':expiration_date' => '2025-06-30',
                ':is_active' => 1,
                ':id' => 5
            ])
            ->willReturn(true);

        $result = $this->couponModel->updateCoupon($data);
        $this->assertTrue($result);
    }

    public function testDeleteCoupon(): void {
        $id = 9;
        $sql = "DELETE FROM coupon WHERE id = :id";
        $this->pdoMock->method('prepare')->with($sql)->willReturn($this->stmtMock);
        $this->stmtMock->expects($this->once())->method('execute')->with([':id' => $id])->willReturn(true);

        $result = $this->couponModel->deleteCoupon($id);
        $this->assertTrue($result);
    }
}
