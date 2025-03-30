<?php

class AccountTest extends \PHPUnit\Framework\TestCase {

    private $baseUrl = 'http://localhost/merged/controller/account.php';

    public function testRegister() {
        $username = 'testlogin' . rand(1000, 9999);
        $password = 'testpassword';
        $data = [
            'action'      => 'register',
            'username'    => $username,
            'password'    => $password,
            'firstname'   => 'Test',
            'lastname'    => 'User',
            'email'       => $username .'@example.com',
            'phonenumber' => '123456789'
        ];

        $response = $this->sendPostRequest($data);
        $this->assertIsArray($response, 'A válasznak tömbnek kell lennie.');
        $this->assertArrayHasKey('success', $response, 'A regisztráció sikeres kell legyen.');
    }

    public function testLogin() {
        // Új felhasználó regisztrálása
        $username = 'testlogin' . rand(1000, 9999);
        $password = 'testpassword';
        $dataRegister = [
            'action'      => 'register',
            'username'    => $username,
            'password'    => $password,
            'firstname'   => 'Login',
            'lastname'    => 'Tester',
            'email'       => $username . '@example.com',
            'phonenumber' => '987654321'
        ];
        $registerResponse = $this->sendPostRequest($dataRegister);
        $this->assertIsArray($registerResponse, 'A regisztráció válasza tömb kell legyen.');
        $this->assertArrayHasKey('success', $registerResponse, 'A regisztráció sikeres kell legyen.');

        // Belépés a regisztrált felhasználóval
        $dataLogin = [
            'action'   => 'login',
            'username' => $username,
            'password' => $password
        ];
        $loginResponse = $this->sendPostRequest($dataLogin);
        $this->assertIsArray($loginResponse, 'A belépés válasza tömb kell legyen.');
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
