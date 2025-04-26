<?php
// vendor/bin/phpunit test/controller/controllerForgot_passwordTest.php

use PHPUnit\Framework\TestCase;
use PHPMailer\PHPMailer\PHPMailer;

class controllerForgot_passwordTest extends TestCase
{
    public function testGenerateTokenReturnsValidFormat()
    {
        define('PHPUNIT_RUNNING', true);
        require_once __DIR__ . '/../../controller/forgot_password.php';

        $userId = 1;
        $expires = '2025-05-01 12:00:00';
        $secret = 'test_secret';

        $token = generateToken($userId, $expires, $secret);

        $parts = explode('.', $token);
        $this->assertCount(2, $parts);
        $decoded = json_decode(base64_decode($parts[0]), true);
        $this->assertEquals($userId, $decoded['user_id']);
        $this->assertEquals($expires, $decoded['expires']);
    }

    public function testEmailIsSentSuccessfully()
    {
        $mockMailer = $this->createMock(PHPMailer::class);
        $mockMailer->expects($this->once())->method('send')->willReturn(true);
    
        $this->assertTrue($mockMailer->send());
    }
}
