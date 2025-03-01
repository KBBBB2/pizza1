<?php
//  ./vendor/bin/phpunit test/model_account_integrationTest.php

require_once 'model/Database.php';

class model_account_integrationTest extends PHPUnit\Framework\TestCase {

    private $baseUrl = 'http://localhost/merged/controller/account.php';
    private $testUsername;
    private $testPassword = 'testpassword';
    private $pdo;

    protected function setUp(): void {
        $this->pdo = Database::getInstance()->getConnection();
        $this->testUsername = 'testlogin' . rand(1000, 9999);
        $data = [
            'action'      => 'register',
            'username'    => $this->testUsername,
            'password'    => $this->testPassword,
            'firstname'   => 'Test',
            'lastname'    => 'User',
            'email'       => $this->testUsername . '@example.com',
            'phonenumber' => '123456789'
        ];

        $response = $this->sendPostRequest($data);
        $this->assertIsArray($response, 'A regisztráció válasznak tömbnek kell lennie.');
        $this->assertArrayHasKey('success', $response, 'A regisztráció sikeres kell legyen.');
    }


    protected function tearDown(): void {
        // Közvetlen SQL művelettel töröljük a tesztfelhasználót az adatbázisból
        $stmt = $this->pdo->prepare("DELETE FROM account WHERE username = :username");
        $stmt->execute([':username' => $this->testUsername]);
    }

    public function testRegister() {
        // Új regisztráció
        $username = 'testlogin' . rand(1000, 9999);
        $password = 'testpassword';
        $data = [
            'action'      => 'register',
            'username'    => $username,
            'password'    => $password,
            'firstname'   => 'Test',
            'lastname'    => 'User',
            'email'       => $username . '@example.com',
            'phonenumber' => '123456789'
        ];

        $response = $this->sendPostRequest($data);
        $this->assertIsArray($response, 'A válasznak tömbnek kell lennie.');
        $this->assertArrayHasKey('success', $response, 'A regisztráció sikeres kell legyen.');

        // Töröljük az itt létrehozott felhasználót az adatbázisból közvetlenül
        $stmt = $this->pdo->prepare("DELETE FROM account WHERE username = :username");
        $stmt->execute([':username' => $username]);
    }

    public function testLogin() {
        // A setUp-ban regisztrált tesztfelhasználó adataival próbálunk bejelentkezni
        $dataLogin = [
            'action'   => 'login',
            'username' => $this->testUsername,
            'password' => $this->testPassword
        ];
        $loginResponse = $this->sendPostRequest($dataLogin);
        $this->assertIsArray($loginResponse, 'A belépés válasza tömbnek kell lennie.');
        $this->assertArrayHasKey('success', $loginResponse, 'A belépés sikeres kell legyen.');
    }

    /**
     * Segédfüggvény, amely cURL segítségével küld POST kérést JSON adatokkal.
     */
    private function sendPostRequest($data) {
        $ch = curl_init($this->baseUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $jsonData = json_encode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ]);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            $this->fail("cURL hiba: " . $error_msg);
        }
        curl_close($ch);

        $decoded = json_decode($result, true);
        if ($decoded === null) {
            $this->fail("Érvénytelen JSON válasz: " . $result);
        }
        return $decoded;
    }
}
