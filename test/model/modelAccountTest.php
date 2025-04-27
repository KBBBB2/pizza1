<?php
// vendor/bin/phpunit test
// vendor/bin/phpunit test/modelAccountTest.php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../model/Account.php';

class modelAccountTest extends TestCase {
    private $pdoMock;
    private $stmtMock;
    private $account;

    protected function setUp(): void {
        // PDO és PDOStatement mockok létrehozása
        $this->pdoMock  = $this->createMock(\PDO::class);
        $this->stmtMock = $this->createMock(\PDOStatement::class);

        // Account példány reflektálással, PDO mock injektálása
        $this->account = new Account();
        $ref    = new \ReflectionClass($this->account);
        $prop   = $ref->getProperty('pdo');
        $prop->setAccessible(true);
        $prop->setValue($this->account, $this->pdoMock);
    }

    public function testRegisterSuccess(): void {
        // 1) prepare() stub-ok
        $stmtRole = $this->createMock(\PDOStatement::class);
        $this->pdoMock->method('prepare')
            ->willReturnMap([
                ["CALL registAndCheck(?, ?, ?, ?, ?, ?, @p_result)", $this->stmtMock],
                ["INSERT INTO userroles (id, role) VALUES (?, 'customer')", $stmtRole],
            ]);
    
        // 2) execute() és nextRowset()
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock
            ->method('nextRowset')
            ->willReturn(false);
    
        // 3) query() stub-ok
        $resultStmt = $this->createMock(\PDOStatement::class);
        $resultStmt->method('fetchColumn')->willReturn(1);
    
        $lastIdStmt = $this->createMock(\PDOStatement::class);
        $lastIdStmt->method('fetchColumn')->willReturn(123);
    
        $this->pdoMock
            ->expects($this->exactly(2))
            ->method('query')
            ->willReturnMap([
                ["SELECT @p_result",       $resultStmt],
                ["SELECT LAST_INSERT_ID()", $lastIdStmt],
            ])
            ->willReturnOnConsecutiveCalls($resultStmt, $lastIdStmt);
    
        // 4) tényleges hívás a tesztelendő metódusra
        $result = $this->account->register(
            'user1',
            'password',
            'First',
            'Last',
            'emailtest@example.com',
            '06205559999'
        );
    
        // 5) ellenőrzések
        $this->assertArrayHasKey('success', $result);
    }
    
    public function testRegisterDuplicateUser(): void {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('nextRowset')->willReturn(false);;

        $dupStmt = $this->createMock(\PDOStatement::class);
        $dupStmt->method('fetchColumn')->willReturn(0);
        $this->pdoMock->method('query')
            ->with("SELECT @p_result")
            ->willReturn($dupStmt);

        $result = $this->account->register('user1', 'pass', 'First', 'Last', 'email@example.com', '12345');
        $this->assertEquals(400, $result['status']);
        $this->assertStringContainsString('már foglalt', $result['error']);
    }

    public function testLoginInvalidUser(): void {
        $stmtLogin = $this->createMock(\PDOStatement::class);
        $this->pdoMock->method('prepare')->willReturn($stmtLogin);
        $stmtLogin->method('execute')->willReturn(true);
        $stmtLogin->method('fetch')->willReturn(false);

        $result = $this->account->login('nonexistent', 'pass');
        $this->assertEquals(400, $result['status']);
        $this->assertStringContainsString('Érvénytelen hitelesítési adatok', $result['error']);
    }

    public function testLoginSuccess(): void {
        $hashed   = password_hash('secret', PASSWORD_DEFAULT);
        $userData = ['id' => 10, 'password' => $hashed, 'disabled' => 0, 'locked' => 0];

        $stmtLogin = $this->createMock(\PDOStatement::class);
        $stmtRole  = $this->createMock(\PDOStatement::class);

        $this->pdoMock->method('prepare')
            ->willReturnMap([
                ["CALL getAccountLogin(?)", $stmtLogin],
                ["SELECT role FROM userroles WHERE id = ?", $stmtRole],
            ]);

        $stmtLogin->method('execute')->willReturn(true);
        $stmtLogin->method('fetch')->willReturn($userData);
        $stmtLogin->method('closeCursor');

        $stmtRole->method('execute')->with([10])->willReturn(true);
        $stmtRole->method('fetch')->willReturn(['role' => 'customer']);

        $result = $this->account->login('user1', 'secret');
        $this->assertArrayHasKey('success', $result);
        $this->assertEquals(200, $result['status']);
        $this->assertEquals('customer', $result['user']['role']);
    }

    // TODO: További tesztek: getUserData, updateAccount, updatePassword stb.
}
