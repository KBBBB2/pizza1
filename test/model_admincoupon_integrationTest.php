<?php
//  ./vendor/bin/phpunit test/model_admincoupon_integrationTest.php

require_once 'model/Database.php';
require_once 'model/adminCoupon.php';

class model_admincoupon_integrationTest extends PHPUnit\Framework\TestCase {
    private static $couponModel;
    private static $pdo;
    private $testCouponId;

    public static function setUpBeforeClass(): void {
        self::$couponModel = new Coupon();
        self::$pdo = Database::getInstance()->getConnection();
    }

    // Minden teszt előtt létrehozunk egy teszt kupon rekordot
    protected function setUp(): void {
        $data = [
            'name'             => 'Teszt Kupon',
            'description'      => 'Ez egy teszt kupon.',
            'code'             => 'TEST123',
            'discount_type'    => 'TypeTest',
            'discount_value'   => 1000,
            'expiration_date'  => date('Y-m-d H:i:s', strtotime('+1 day')),
            'is_active'        => 1
        ];

        $sql = "INSERT INTO coupon (name, description, code, discount_type, discount_value, expiration_date, is_active) 
                VALUES (:name, :description, :code, :discount_type, :discount_value, :expiration_date, :is_active)";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([
            ':name'             => $data['name'],
            ':description'      => $data['description'],
            ':code'             => $data['code'],
            ':discount_type'    => $data['discount_type'],
            ':discount_value'   => $data['discount_value'],
            ':expiration_date'  => $data['expiration_date'],
            ':is_active'        => $data['is_active']
        ]);
        $this->testCouponId = self::$pdo->lastInsertId();
    }

    // Minden teszt után töröljük a beszúrt teszt rekordot
    protected function tearDown(): void {
        $stmt = self::$pdo->prepare("DELETE FROM coupon WHERE id = :id");
        $stmt->execute([':id' => $this->testCouponId]);
    }

    // readCoupons() tesztelése keresési feltétel nélkül
    public function testReadCouponsWithoutQuery() {
        $coupons = self::$couponModel->readCoupons();
        $this->assertIsArray($coupons, "A readCoupons() metódusnak tömböt kell visszaadnia.");
        $found = false;
        foreach ($coupons as $coupon) {
            if ($coupon['id'] == $this->testCouponId) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, "A teszt kupon nem található a readCoupons() eredményében.");
    }

    // readCoupons() tesztelése keresési feltétellel
    public function testReadCouponsWithQuery() {
        $coupons = self::$couponModel->readCoupons('Teszt');
        $this->assertIsArray($coupons, "A readCoupons() metódusnak tömböt kell visszaadnia keresési feltétellel is.");
        $found = false;
        foreach ($coupons as $coupon) {
            if ($coupon['id'] == $this->testCouponId) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, "A teszt kupon nem található a readCoupons() eredményében a keresési feltétellel.");
    }

    // createCoupon() tesztelése: új kupon létrehozása
    public function testCreateCoupon() {
        $data = [
            'name'             => 'Új Teszt Kupon',
            'description'      => 'Új teszt kupon leírása.',
            'code'             => 'NEWTEST123',
            'discount_type'    => 'Akció',
            'discount_value'   => 2500,
            'expiration_date'  => date('Y-m-d H:i:s', strtotime('+2 days')),
            'is_active'        => 1
        ];
        $newCouponId = self::$couponModel->createCoupon($data);
        $this->assertNotFalse($newCouponId, "A createCoupon() metódusnak nem szabad false értékkel visszatérnie.");

        // Ellenőrizzük, hogy az új rekord megtalálható az adatbázisban
        $stmt = self::$pdo->prepare("SELECT * FROM coupon WHERE id = :id");
        $stmt->execute([':id' => $newCouponId]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEmpty($coupon, "Az újonnan létrehozott kupon rekord nem található az adatbázisban.");
        $this->assertEquals($data['name'], $coupon['name'], "A kupon neve nem egyezik.");

        // Cleanup: töröljük az újonnan létrehozott kupon rekordot
        $stmt = self::$pdo->prepare("DELETE FROM coupon WHERE id = :id");
        $stmt->execute([':id' => $newCouponId]);
    }

    // updateCoupon() tesztelése: létező kupon módosítása
    public function testUpdateCoupon() {
        $updatedData = [
            'id'               => $this->testCouponId,
            'name'             => 'Frissített Teszt Kupon',
            'description'      => 'Frissített leírás.',
            'code'             => 'UPDATED123',
            'discount_type'    => 'Disc',
            'discount_value'   => 1500,
            'expiration_date'  => date('Y-m-d H:i:s', strtotime('+3 days')),
            'is_active'        => 0
        ];
        $result = self::$couponModel->updateCoupon($updatedData);
        $this->assertTrue($result, "Az updateCoupon() metódusnak true értékkel kell visszatérnie.");

        // Ellenőrizzük, hogy a rekord módosult-e
        $stmt = self::$pdo->prepare("SELECT * FROM coupon WHERE id = :id");
        $stmt->execute([':id' => $this->testCouponId]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals($updatedData['name'], $coupon['name'], "A kupon neve nem frissült megfelelően.");
        $this->assertEquals($updatedData['is_active'], $coupon['is_active'], "A kupon aktív státusza nem frissült megfelelően.");
    }

    // deleteCoupon() tesztelése: kupon törlése
    public function testDeleteCoupon() {
        // Először hozzunk létre egy új teszt kupon rekordot
        $data = [
            'name'             => 'Törlendő Teszt Kupon',
            'description'      => 'Törlendő kupon leírása.',
            'code'             => 'DELTEST123',
            'discount_type'    => 'Deleted',
            'discount_value'   => 2000,
            'expiration_date'  => date('Y-m-d H:i:s', strtotime('+1 day')),
            'is_active'        => 1
        ];
        $newCouponId = self::$couponModel->createCoupon($data);
        $this->assertNotFalse($newCouponId, "A createCoupon() metódusnak nem szabad false értékkel visszatérnie.");

        // Töröljük a létrehozott kupon rekordot
        $result = self::$couponModel->deleteCoupon($newCouponId);
        $this->assertTrue($result, "A deleteCoupon() metódusnak true értékkel kell visszatérnie.");

        // Ellenőrizzük, hogy a rekord már nem létezik
        $stmt = self::$pdo->prepare("SELECT * FROM coupon WHERE id = :id");
        $stmt->execute([':id' => $newCouponId]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertFalse($coupon, "A törölt kupon rekordnak nem kellene léteznie.");
    }
}
?>
