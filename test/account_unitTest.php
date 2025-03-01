<?php

require_once 'model/Account.php';
require_once 'model/Database.php';

class Account_unitTest extends PHPUnit\Framework\TestCase {
    /**
     * A tesztelt Account példány, amelybe egy mockolt PDO példányt injektálunk.
     * @var Account
     */
    private $account;

    /**
     * A mockolt PDO példány.
     * @var PDO
     */
    private $pdo;

    protected function setUp(): void {
        // Létrehozzuk a PDO mockot
        $this->pdo = $this->createMock(PDO::class);

        // A Database singleton-t módosítjuk, hogy a mockolt PDO-t adja vissza.
        $databaseInstance = Database::getInstance();
        $reflection = new ReflectionClass($databaseInstance);
        $property = $reflection->getProperty('pdo');
        $property->setAccessible(true);
        $property->setValue($databaseInstance, $this->pdo);

        // Most az Account konstruktor a Database singletont fogja használni,
        // ami már a mockolt PDO-t adja vissza.
        $this->account = new Account();
    }

    public function testRegisterSuccess() {
        // Előkészítjük a mockolt PDOStatement-et a tárolt eljárás híváshoz.
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
                 ->method('execute')
                 ->with($this->callback(function($params) {
                     return is_array($params) && count($params) === 6;
                 }))
                 ->willReturn(true);
        $stmtMock->expects($this->any())
                 ->method('nextRowset')
                 ->willReturn(false);
    
        $stmtRoleMock = $this->createMock(PDOStatement::class);
        $stmtRoleMock->expects($this->once())
                     ->method('execute')
                     ->with($this->callback(function($params) {
                         return is_array($params) && count($params) === 1;
                     }))
                     ->willReturn(true);
    
        // PDO::prepare konfigurálása returnValueMap-pal
        $this->pdo->method('prepare')
          ->willReturnMap([
              ["CALL registAndCheck(?, ?, ?, ?, ?, ?, @p_result)", [], $stmtMock],
              ["INSERT INTO userroles (id, role) VALUES (?, 'customer')", [], $stmtRoleMock],
          ]);

        $this->pdo->method('query')
                ->willReturnMap([
                    ["SELECT @p_result", [], $stmtResult],
                    ["SELECT LAST_INSERT_ID()", [], $stmtResult],
                ]);

    
        $result = $this->account->register('testuser', 'testpassword', 'Test', 'User', 'test@example.com', '123456789');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }
    
    public function testLoginSuccess() {
        $stmtMock = $this->createMock(PDOStatement::class);
        $username = 'testlogin';
        $password = 'testpassword';
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
        $userData = [
            'id'       => 1,
            'username' => $username,
            'password' => $passwordHash,
            'disabled' => 0,
            'locked'   => 0,
        ];
    
        $stmtMock->expects($this->once())
                 ->method('execute')
                 ->with($this->equalTo([$username]))
                 ->willReturn(true);
        $stmtMock->expects($this->once())
                 ->method('fetch')
                 ->with(PDO::FETCH_ASSOC)
                 ->willReturn($userData);
        $stmtMock->expects($this->once())
                 ->method('closeCursor');
    
        $stmtRoleMock = $this->createMock(PDOStatement::class);
        $roleData = ['role' => 'customer'];
        $stmtRoleMock->expects($this->once())
                     ->method('execute')
                     ->with($this->equalTo([$userData['id']]))
                     ->willReturn(true);
        $stmtRoleMock->expects($this->once())
                     ->method('fetch')
                     ->with(PDO::FETCH_ASSOC)
                     ->willReturn($roleData);
    
        // Konfiguráljuk a prepare hívásokat returnValueMap-pal
        $this->pdo->method('prepare')
          ->willReturnMap([
              ["CALL getAccountLogin(?)", [], $stmtMock],
              ["SELECT role FROM userroles WHERE id = ?", [], $stmtRoleMock],
          ]);

    
        $result = $this->account->login($username, $password);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }
}    