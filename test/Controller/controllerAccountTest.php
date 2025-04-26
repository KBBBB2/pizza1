<?php
// vendor/bin/phpunit test/controller/controllerAccountTest.php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

// Betöltjük a globális osztályokat:
require_once __DIR__ . '/../../controller/account.php';
require_once __DIR__ . '/../../model/Account.php';

class ControllerAccountTest extends TestCase {
    private $controller;
    private $accountMock;

    protected function setUp(): void {
        // Mock-oljuk a globális Account modellt:
        $this->accountMock = $this->createMock(\Account::class);

        // Ez a névtelen alosztály override-olja az IO metódusokat
        $this->controller = new class($this->accountMock) extends \AccountController {
            public string $lastJson = '';
            public int    $lastStatus = 200;
            public string $rawInput = '';

            // override-oljuk a bemenetet
            protected function getRawInput(): string {
                return $this->rawInput;
            }
            // nem igazán küldünk header()-t
            protected function sendStatus(int $code): void {
                $this->lastStatus = $code;
            }
            protected function sendJson(array $data): void {
                $this->lastJson = json_encode($data);
            }
        };
    }

    public function testInvalidMethod(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->controller->processRequest();

        $this->assertEquals(405, $this->controller->lastStatus);
        $this->assertStringContainsString('Csak POST', $this->controller->lastJson);
    }

    public function testInvalidJson(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->controller->rawInput = 'not a json';
        $this->controller->processRequest();

        $data = json_decode($this->controller->lastJson, true);
        $this->assertEquals('Érvénytelen JSON.', $data['error']);        
    }

    public function testMissingAction(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->controller->rawInput = json_encode([]);
        $this->controller->processRequest();

        $data = json_decode($this->controller->lastJson, true);
        $this->assertEquals('Hiányzik az action.', $data['error']);
    }

    public function testRegisterSuccess(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->controller->rawInput = json_encode([
            'action'   => 'register',
            'username' => 'u',
            'password' => 'p'
        ]);

        // Ha a modell register()-re ezt adja vissza, a controllernek success
        $this->accountMock
             ->method('register')
             ->with('u','p','','','','')
             ->willReturn(['success'=>'Regisztráció sikerült']);

        $this->controller->processRequest();

        $data = json_decode($this->controller->lastJson, true);
        $this->assertEquals('Regisztráció sikerült', $data['success']);
    }

    public function testLoginFailure(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->controller->rawInput = json_encode([
            'action'   => 'login',
            'username' => 'u',
            'password' => 'p'
        ]);

        // Modell hibát ad vissza
        $this->accountMock
             ->method('login')
             ->with('u','p')
             ->willReturn(['error'=>'hibás adat','status'=>401]);

        $this->controller->processRequest();

        $data = json_decode($this->controller->lastJson, true);
        $this->assertEquals('hibás adat', $data['error']);
    }

    public function testLoginSuccessGeneratesToken(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->controller->rawInput = json_encode([
            'action'   => 'login',
            'username' => 'u',
            'password' => 'p'
        ]);

        $user = ['id'=>1,'username'=>'u','role'=>'admin'];
        $this->accountMock
             ->method('login')
             ->with('u','p')
             ->willReturn(['success'=>'OK','status'=>200,'user'=>$user]);

        $this->controller->processRequest();

        $this->assertEquals(200, $this->controller->lastStatus);
        $json = json_decode($this->controller->lastJson, true);
        $this->assertArrayHasKey('token', $json);
        $this->assertEquals('OK', $json['success']);
        $this->assertEquals($user, $json['user']);
    }
}
