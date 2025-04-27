<?php
// vendor/bin/phpunit test/modelAdminAccountTest.php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../model/AdminAccount.php';

class modelAdminAccountTest extends TestCase {
    private $pdoMock;
    private $stmtMock;
    private $adminModel;

    protected function setUp(): void {
        // PDO és PDOStatement mock
        $this->pdoMock = $this->createMock(\PDO::class);
        $this->stmtMock = $this->createMock(\PDOStatement::class);

        // Model példány és PDO injektálása reflektálással
        $this->adminModel = new AdminAccount();
        $ref = new \ReflectionClass($this->adminModel);
        $prop = $ref->getProperty('pdo');
        $prop->setAccessible(true);
        $prop->setValue($this->adminModel, $this->pdoMock);
    }

    public function testGetAccountsWithoutQuery(): void {
        $expected = [ ['id' => 1], ['id' => 2] ];
        // CALL getAllAccount()
        $this->pdoMock->method('prepare')
            ->with('CALL getAllAccount()')
            ->willReturn($this->stmtMock);
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetchAll')->with(PDO::FETCH_ASSOC)->willReturn($expected);

        $result = $this->adminModel->getAccounts();
        $this->assertEquals($expected, $result);
    }

    public function testGetAccountsWithQuery(): void {
        $q = 'john';
        $qParam = "%{$q}%";
        $expected = [ ['id' => 3, 'username' => 'john_doe'] ];
        // LIKE search SQL
        $this->pdoMock->method('prepare')
            ->with($this->callback(function($sql) {
                return strpos($sql, 'WHERE firstname LIKE') !== false
                    && substr_count($sql, 'LIKE') === 5;
            }))
            ->willReturn($this->stmtMock);
        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([$qParam, $qParam, $qParam, $qParam, $qParam])
            ->willReturn(true);
        $this->stmtMock->method('fetchAll')->with(PDO::FETCH_ASSOC)->willReturn($expected);

        $result = $this->adminModel->getAccounts($q);
        $this->assertEquals($expected, $result);
    }

    public function testTempBanInvalidIdThrowsException(): void {
        $this->expectException(Exception::class);
        $this->adminModel->tempBan(0, '1h');
    }

    public function testTempBanInvalidDurationThrowsException(): void {
        $this->expectException(Exception::class);
        $this->adminModel->tempBan(5, 'invalid');
    }

        public function testTempBanSuccessReturnsDatetime(): void {
        $id = 7;
        $duration = '1m,2h,1d';

        // Stub-oljuk a UPDATE SQL-t, hogy a stmtMock-t kapjuk
        $sql = "UPDATE account SET locked = 1, ban_expires_at = ? WHERE id = ?";
        $this->pdoMock->method('prepare')
            ->with($sql)
            ->willReturn($this->stmtMock);
        $this->stmtMock->method('execute')
            ->with($this->callback(function($params) use ($id) {
                // A paraméterek 0: datetime string, 1: id
                return count($params) === 2 && $params[1] === $id;
            }))
            ->willReturn(true);

        // Hívjuk meg a metódust
        $start = time();
        $result = $this->adminModel->tempBan($id, $duration);

        // Ellenőrizzük, hogy valid dátum formátumot kaptunk vissza
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $result);
        $this->assertInstanceOf(DateTime::class, $dt);
        $ts = $dt->getTimestamp();
        $expectedSeconds = 1*60 + 2*3600 + 1*86400;
        $this->assertGreaterThanOrEqual($start + $expectedSeconds, $ts);
        $this->assertLessThanOrEqual(time() + $expectedSeconds + 1, $ts);
    }

    public function testPermBanInvalidIdThrowsException(): void {
        $this->expectException(Exception::class);
        $this->adminModel->permBan(0);
    }

    public function testPermBanSuccess(): void {
        $id = 8;
        $this->pdoMock->method('prepare')->with('CALL sp_permBan(?)')->willReturn($this->stmtMock);
        $this->stmtMock->expects($this->once())->method('execute')->with([$id])->willReturn(true);

        $result = $this->adminModel->permBan($id);
        $this->assertTrue($result);
    }

    public function testUnbanExpired(): void {
        $sql = 'UPDATE account SET locked = 0, ban_expires_at = NULL WHERE locked = 1 AND ban_expires_at <= NOW()';
        $this->pdoMock->method('prepare')->with($sql)->willReturn($this->stmtMock);
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('rowCount')->willReturn(3);

        $result = $this->adminModel->unbanExpired();
        $this->assertEquals(3, $result);
    }
}
