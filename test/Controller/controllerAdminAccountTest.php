<?php
// vendor/bin/phpunit test/controller/controllerAdminAccountTest.php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

// betöltjük az új controller osztályodat
require_once __DIR__ . '/../../model/AdminAccount.php';
require_once __DIR__ . '/../../controller/AdminAccount.php';

// egy egyszerű stub a modell helyére
class AdminAccountStub {
    public array $accountsReturn = [];
    public string $tempBanExpires  = '';
    public ?array $tempBanCalledId  = null;
    public ?int $permBanCalledId = null;

    public function getAccounts(string $q): array 
    {
        return $this->accountsReturn;
    }
    public function tempBan(int $id, string $duration): string
    {
        $this->tempBanCalledId = ['id'=>$id,'duration'=>$duration];
        return $this->tempBanExpires;
    }
    public function permBan(int $id): void
    {
        $this->permBanCalledId = $id;
    }
}

class ControllerAdminAccountTest extends TestCase {
    private AdminAccountStub         $stub;
    private AdminAccountController   $ctrl;

    protected function setUp(): void {
        $this->stub = new AdminAccountStub();
        $this->ctrl = new AdminAccountController($this->stub);
    }

    public function testGetAccounts(): void {
        $this->stub->accountsReturn = [
            ['id'=>1,'username'=>'alice'],
            ['id'=>2,'username'=>'bob']
        ];

        $output = $this->ctrl->processRequest(
            ['action' => 'getAccounts', 'q' => 'bo'],
            'POST'
        );

        $this->assertCount(2, $output);
        $this->assertEquals('bob', $output[1]['username']);
    }

    public function testTempBan(): void {
        $this->stub->tempBanExpires = '2025-05-01 10:00:00';

        $output = $this->ctrl->processRequest(
            ['action' => 'tempBan', 'id' => 5, 'duration' => '24h'],
            'POST'
        );

        $this->assertEquals(
            'Felhasználó ideiglenesen letiltva.',
            $output['message']
        );
        $this->assertEquals(
            '2025-05-01 10:00:00',
            $output['ban_expires_at']
        );

        // Ellenőrizzük, hogy a stub-ba valóban bekerült-e a paraméter
        $this->assertEquals(5,   $this->stub->tempBanCalledId['id']);
        $this->assertEquals('24h',$this->stub->tempBanCalledId['duration']);
    }

    public function testPermBan(): void
    {
        $output = $this->ctrl->processRequest(
            ['action' => 'permBan', 'id' => 42],
            'POST'
        );

        $this->assertEquals(
            'Felhasználó véglegesen letiltva.',
            $output['message']
        );
        $this->assertEquals(42, $this->stub->permBanCalledId);
    }

    public function testInvalidAction(): void
    {
        $output = $this->ctrl->processRequest(
            ['action' => 'nonsense'],
            'POST'
        );

        $this->assertArrayHasKey('error', $output);
        $this->assertEquals('Érvénytelen művelet.', $output['error']);
    }

    public function testOptionsEarlyExit(): void
    {
        $output = $this->ctrl->processRequest([], 'OPTIONS');
        $this->assertSame([], $output);
    }
}
