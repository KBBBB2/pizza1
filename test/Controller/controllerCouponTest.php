<?php
// vendor/bin/phpunit test/controller/controllerCouponTest.php

use PHPUnit\Framework\TestCase;

// Biztosítsd, hogy az include útvonalak megfeleljenek a projekt struktúrájának!
require_once __DIR__ . '/../../controller/Coupon.php';
require_once __DIR__ . '/../../model/coupon.php';

class controllerCouponTest extends TestCase
{
    protected function setUp(): void {
        // Globális változók alaphelyzetbe állítása
        $_REQUEST = [];
        $_SERVER = [];
    }

    public function testNoCouponCodeProvided() {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        // Nincs 'code' beállítva
        $_REQUEST = [];

        // Mock létrehozása
        $couponModel = $this->createMock(Coupon::class);
        // A modellben a getCouponByCode metódus nem kerül meghívásra

        $controller = new CouponController($couponModel);
        $response = $controller->handleRequest();
        $result = json_decode($response, true);

        $this->assertArrayHasKey('error', $result);
        $this->assertEquals("Nincs kupon kód megadva.", $result['error']);
    }

    public function testValidCouponCode() {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_REQUEST = ['code' => 'DISCOUNT10'];

        // Mock létrehozása, és elvárjuk, hogy a getCouponByCode metódus meghívódjon a 'DISCOUNT10' kóddal
        $couponModel = $this->createMock(Coupon::class);
        $couponModel->expects($this->once())
            ->method('getCouponByCode')
            ->with('DISCOUNT10')
            ->willReturn(['code' => 'DISCOUNT10', 'discount' => '10%']);

        $controller = new CouponController($couponModel);
        $response = $controller->handleRequest();
        $result = json_decode($response, true);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('coupon', $result);
        $this->assertEquals('DISCOUNT10', $result['coupon']['code']);
    }

    public function testInvalidCouponCode() {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_REQUEST = ['code' => 'INVALIDCODE'];

        // Mock létrehozása, ahol a getCouponByCode metódus hamis értékkel tér vissza
        $couponModel = $this->createMock(Coupon::class);
        $couponModel->expects($this->once())
            ->method('getCouponByCode')
            ->with('INVALIDCODE')
            ->willReturn(false);

        $controller = new CouponController($couponModel);
        $response = $controller->handleRequest();
        $result = json_decode($response, true);

        $this->assertArrayHasKey('error', $result);
        $this->assertEquals("Érvénytelen vagy lejárt kupon kód.", $result['error']);
    }
}
