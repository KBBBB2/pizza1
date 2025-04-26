<?php
// vendor/bin/phpunit test/controller/controllerAdminCouponTest.php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class controllerAdminCouponTest extends TestCase
{
    private array $originalRequest;
    private array $originalServer;
    private int $obLevel;

    protected function setUp(): void
    {
        // Mentjük az aktuális output buffer szintet
        $this->obLevel = ob_get_level();
        // Nyissuk meg a saját output bufferünket
        ob_start();

        require_once __DIR__ . '/../../controller/adminCoupon.php';

        $this->originalRequest = $_REQUEST;
        $this->originalServer  = $_SERVER;

        $_REQUEST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['CONTENT_TYPE']   = 'application/json';
    }

    protected function tearDown(): void
    {
        // Állítsuk vissza a $_REQUEST és $_SERVER eredeti értékeit
        $_REQUEST = $this->originalRequest;
        $_SERVER  = $this->originalServer;

        // Csak azokat a buffereket zárjuk le, amelyek a setUp-ben nyíltak meg
        while (ob_get_level() > ($this->obLevel + 1)) {
            ob_end_clean();
        }
        // Olvassuk ki (és zárjuk le) azt a buffert, amit setUp-ben nyitottunk
        $output = ob_get_clean();

        // Ha váratlan kimenet érkezett, azt kiírjuk (de ez elsősorban hibakereséshez jó)
        if (!empty($output)) {
            echo "\n==== UNEXPECTED OUTPUT DURING TEST ====\n";
            echo $output . "\n";
        }

        $coupons = handleRequest('GET', ['action' => 'read', 'q' => 'TEST_']);
        if ($coupons['status'] === 200 && is_array($coupons['data'])) {
            foreach ($coupons['data'] as $coupon) {
                if (strpos($coupon['name'], 'TEST') === 0) {
                    handleRequest('POST', ['action' => 'delete', 'id' => $coupon['id']]);
                }
            }
        }
    }

    public function testReadCoupons() {
        // Create first
        handleRequest('POST', [
            'action' => 'create',
            'name' => 'TEST Kupon',
            'description' => 'Leírás',
            'code' => 'TESZT123',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'expiration_date' => '2030-01-01',
            'is_active' => 1
        ]);
    
        $result = handleRequest('GET', [
            'action' => 'read',
            'q' => 'Teszt'
        ]);
    
        $this->assertEquals(200, $result['status']);
        $this->assertStringContainsString('TEST Kupon', json_encode($result['data']));
    }
    
    public function testCreateCoupon(): void
    {
        $result = handleRequest('POST', [
            'action'           => 'create',
            'name'             => 'TEST új Kupon',
            'description'      => 'Leírás',
            'code'             => 'NEW123',
            'discount_type'    => 'percent',
            'discount_value'   => 15,
            'expiration_date'  => '2030-12-31',
            'is_active'        => 1,
        ]);

        $this->assertEquals(200, $result['status']);
        $this->assertArrayHasKey('id', $result['data']);
        $this->assertIsNumeric($result['data']['id']);
        $this->assertGreaterThan(0, $result['data']['id']);
    }

    public function testUpdateCoupon(): void
    {
        $create = handleRequest('POST', [
            'action' => 'create',
            'name' => 'TEST Frissítendő kupon',
            'description' => '',
            'code' => 'UPD_TEST',
            'discount_type' => 'fixed',
            'discount_value' => 25,
            'expiration_date' => '2030-01-01',
            'is_active' => 1,
        ]);

        $id = $create['data']['id'] ?? 0;

        $update = handleRequest('POST', [
            'action' => 'update',
            'id' => $id,
            'name' => 'TEST frissített kupon',
            'description' => 'Leírás frissítve',
            'code' => 'UPD456',
            'discount_type' => 'percent',
            'discount_value' => 50,
            'expiration_date' => '2031-01-01',
            'is_active' => 1,
        ]);

        $this->assertTrue($update['data']['success'] ?? false);
    }

    public function testDeleteCoupon(): void
    {
        $create = handleRequest('POST', [
            'action' => 'create',
            'name' => 'TEST törlendő kupon',
            'description' => '',
            'code' => 'DEL_TEST',
            'discount_type' => 'fixed',
            'discount_value' => 25,
            'expiration_date' => '2030-01-01',
            'is_active' => 1,
        ]);

        $id = $create['data']['id'] ?? 0;

        $delete = handleRequest('POST', [
            'action' => 'delete',
            'id' => $id,
        ]);

        $this->assertTrue($delete['data']['success'] ?? false);
    }

    public function testInvalidAction(): void
    {
        $result = handleRequest('GET', ['action' => 'nonexistent']);
        $this->assertEquals("Invalid action", $result['data']['error'] ?? '');
    }
}
