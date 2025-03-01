<?php
//  ./vendor/bin/phpunit test/model_adminaccount_integrationTest.php

require_once 'model/Database.php';
require_once 'model/AdminAccount.php';

class model_adminaccount_integrationTest extends PHPUnit\Framework\TestCase {
    private static $adminAccount;
    private static $pdo;
    private $testAccountId;

    // Osztály szintű beállítás: példányosítjuk az AdminAccount-ot és lekérjük a PDO kapcsolatot
    public static function setUpBeforeClass(): void {
        self::$adminAccount = new AdminAccount();
        self::$pdo = Database::getInstance()->getConnection();
    }

    // Minden teszt előtt beszúrunk egy teszt rekordot az account táblába
    protected function setUp(): void {
        $stmt = self::$pdo->prepare("
            INSERT INTO account 
            (firstname, lastname, username, email, phonenumber, created, locked, disabled, ban_expires_at)
            VALUES (?, ?, ?, ?, ?, NOW(), 0, 0, NULL)
        ");
        $stmt->execute(['Test', 'User', 'testuser', 'testuser@example.com', '123456789']);
        $this->testAccountId = self::$pdo->lastInsertId();
    }

    // Minden teszt után töröljük a beszúrt teszt rekordot
    protected function tearDown(): void {
        $stmt = self::$pdo->prepare("DELETE FROM account WHERE id = ?");
        $stmt->execute([$this->testAccountId]);
    }

    // getAccounts() metódus tesztelése: üres keresési feltétellel a tárolt eljárás hívása
    public function testGetAccountsWithoutQuery() {
        $accounts = self::$adminAccount->getAccounts();
        $this->assertIsArray($accounts, "A getAccounts() metódusnak tömböt kell visszaadnia.");
        $found = false;
        foreach ($accounts as $account) {
            if ($account['id'] == $this->testAccountId) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, "A teszt account nem található a getAccounts() eredményében.");
    }

    // getAccounts() metódus tesztelése keresési feltétellel
    public function testGetAccountsWithQuery() {
        $accounts = self::$adminAccount->getAccounts('Test');
        $this->assertIsArray($accounts, "A getAccounts() metódusnak tömböt kell visszaadnia keresési feltétellel is.");
        $found = false;
        foreach ($accounts as $account) {
            if ($account['id'] == $this->testAccountId) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, "A teszt account nem található a getAccounts() eredményében a keresési feltétellel.");
    }

    // tempBan() metódus tesztelése: ideiglenes tiltás beállítása
    public function testTempBan() {
        // Például "1m, 1h" összesen 3660 másodpercet jelent
        $duration = "1m, 1h";
        $banExpiresAt = self::$adminAccount->tempBan($this->testAccountId, $duration);
        $this->assertNotEmpty($banExpiresAt, "A tempBan() metódusnak vissza kell adnia a tiltás lejárati dátumát.");

        // Ellenőrizzük, hogy a rekord frissült-e
        $stmt = self::$pdo->prepare("SELECT locked, ban_expires_at FROM account WHERE id = ?");
        $stmt->execute([$this->testAccountId]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(1, $account['locked'], "A rekordnak locked = 1 kell, hogy legyen a tempBan() után.");
        $this->assertEquals($banExpiresAt, $account['ban_expires_at'], "A ban_expires_at mező nem egyezik a várt értékkel.");
    }

    // permBan() metódus tesztelése: végleges tiltás alkalmazása
    public function testPermBan() {
        // Először biztosítjuk, hogy a teszt account nincs tiltva
        $stmt = self::$pdo->prepare("UPDATE account SET disabled = 0, ban_expires_at = NULL WHERE id = ?");
        $stmt->execute([$this->testAccountId]);

        $result = self::$adminAccount->permBan($this->testAccountId);
        $this->assertTrue($result, "A permBan() metódusnak true értékkel kell visszatérnie.");

        // Ellenőrizzük, hogy a rekordban locked = 1 szerepel-e
        $stmt = self::$pdo->prepare("SELECT disabled, ban_expires_at FROM account WHERE id = ?");
        $stmt->execute([$this->testAccountId]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(1, $account['disabled'], "A rekordnak disabled = 1 kell, hogy legyen a permBan() után.");
    }

    // unbanExpired() metódus tesztelése: lejárt tilalmak feloldása
    public function testUnbanExpired() {
        // Állítsuk a teszt account-ban a tiltás lejáratát egy múltbeli időpontra (például 1 órával ezelőtt)
        $pastDate = date('Y-m-d H:i:s', time() - 3600);
        $stmt = self::$pdo->prepare("UPDATE account SET locked = 1, ban_expires_at = ? WHERE id = ?");
        $stmt->execute([$pastDate, $this->testAccountId]);

        // Hívjuk meg az unbanExpired() metódust
        $rowCount = self::$adminAccount->unbanExpired();
        $this->assertGreaterThanOrEqual(1, $rowCount, "Az unbanExpired() metódusnak legalább egy rekordot fel kell oldania.");

        // Ellenőrizzük, hogy a rekord feloldódott-e
        $stmt = self::$pdo->prepare("SELECT locked, ban_expires_at FROM account WHERE id = ?");
        $stmt->execute([$this->testAccountId]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(0, $account['locked'], "A rekordnak unlocked (locked = 0) állapotúnak kell lennie az unbanExpired() után.");
        $this->assertNull($account['ban_expires_at'], "A ban_expires_at mezőnek NULL értéket kell tartalmaznia az unbanExpired() után.");
    }
}
?>
