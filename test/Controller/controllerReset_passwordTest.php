<?php
// vendor/bin/phpunit test/controller/controllerReset_passwordTest.php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

// 1) include-oljuk a controller osztályt
require_once __DIR__ . '/../../controller/reset_password.php';

class controllerReset_passwordTest extends TestCase
{
    public function testValidateToken_Success(): void
    {
        $secret = 'valami_egyedi_titkos_kulcs';
        // 2) Generáljunk érvényes tokent teszthez:
        $payloadArray = [
            'user_id' => 42,
            'expires' => date('Y-m-d H:i:s', time() + 3600),
        ];
        $payload    = base64_encode(json_encode($payloadArray));
        $signature  = hash_hmac('sha256', $payload, $secret);
        $token      = "{$payload}.{$signature}";

        $controller = new ControllerResetPassword($this->createMock(PDO::class));
        $tokenData  = $controller->validateToken($token, $secret);

        $this->assertIsArray($tokenData);
        $this->assertEquals(42, $tokenData['user_id']);
    }

    public function testValidateToken_Failure_BadSignature(): void
    {
        $controller = new ControllerResetPassword($this->createMock(PDO::class));
        $result = $controller->validateToken('foo.bar', 'valami_egyedi_titkos_kulcs');
        $this->assertFalse($result);
    }

    public function testResetPassword(): void
    {
        // Mockoljunk egy PDO objektumot és a statement-et
        $pdoMock  = $this->createMock(PDO::class);
        $stmtMock = $this->createMock(PDOStatement::class);

        $pdoMock->method('prepare')->willReturn($stmtMock);
        $stmtMock->method('execute')->willReturn(true);

        $controller = new ControllerResetPassword($pdoMock);
        $message    = $controller->resetPassword(123, 'newpassword123');

        $this->assertSame('A jelszó sikeresen frissítve.', $message);
    }
}
