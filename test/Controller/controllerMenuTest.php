<?php
// vendor/bin/phpunit test/controller/controllerMenuTest.php

declare(strict_types=1);

// Betöltjük a tesztelendő controllert és a modellt
require_once __DIR__ . '/../../controller/menu.php';
require_once __DIR__ . '/../../model/Pizza.php';

use PHPUnit\Framework\TestCase;

class controllerMenuTest extends TestCase
{
    private $mockModel;
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        // Pizza modell mockolása és controller példányosítása
        $this->mockModel = $this->createMock(Pizza::class);
        $this->controller = new MenuController($this->mockModel);
    }

    protected function tearDown(): void
    {
        // Globális változók resetelése
        $_SERVER = $_REQUEST = $_POST = $_FILES = [];
        parent::tearDown();
    }

    public function testIndexReturnsJsonWithData(): void
    {
        $sample = [['id' => 1, 'name' => 'Margherita']];
        $this->mockModel->method('getIndexedPizzas')->willReturn($sample);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_HOST']      = 'test.local';

        ob_start();
        $this->controller->handleRequest();
        $rawOutput = ob_get_contents();
        ob_end_clean();

        $output = json_decode($rawOutput, true);
        $this->assertTrue($output['success']);
        $this->assertEquals(1, $output['data'][0]['id']);
        $this->assertStringContainsString('http://test.local', $output['data'][0]['image']);
    }

    public function testUnsupportedMethodReturns405(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PATCH';

        ob_start();
        $this->controller->handleRequest();
        ob_end_clean();

        $this->assertEquals(405, http_response_code());
    }

    public function testStoreFailsWhenNoImageUploaded(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        // nincs $_FILES tartalom

        $this->mockModel->expects($this->never())->method('insertPizza');

        ob_start();
        $this->controller->handleRequest();
        $rawOutput = ob_get_contents();
        ob_end_clean();

        $output = json_decode($rawOutput, true);
        $this->assertFalse($output['success']);
        $this->assertEquals('No image uploaded', $output['error']);
        $this->assertEquals(400, http_response_code());
    }

    public function testDestroyFailsWhenNoIdProvided(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        // $_REQUEST['id'] nincs beállítva

        $this->mockModel->expects($this->never())->method('deletePizza');

        ob_start();
        $this->controller->handleRequest();
        $rawOutput = ob_get_contents();
        ob_end_clean();

        $output = json_decode($rawOutput, true);
        $this->assertFalse($output['success']);
        $this->assertEquals('No id provided', $output['error']);
        $this->assertEquals(400, http_response_code());
    }

    public function testDestroyFailsWhenModelDeletionFails(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_REQUEST['id'] = 42;

        $this->mockModel->method('deletePizza')->with(42)->willReturn(false);

        ob_start();
        $this->controller->handleRequest();
        $rawOutput = ob_get_contents();
        ob_end_clean();

        $output = json_decode($rawOutput, true);
        $this->assertFalse($output['success']);
        $this->assertEquals('Deletion failed', $output['error']);
        $this->assertEquals(500, http_response_code());
    }

    public function testDestroySucceedsWhenModelDeletionSucceeds(): void
    {
        $id = 7;
        $testDir = __DIR__ . "/../../../assets/images/{$id}";
        @mkdir($testDir, 0777, true);
        file_put_contents("{$testDir}/pizza_{$id}.jpg", 'test');

        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_REQUEST['id'] = $id;
        $this->mockModel->method('deletePizza')->with($id)->willReturn(true);

        ob_start();
        $this->controller->handleRequest();
        $rawOutput = ob_get_contents();
        ob_end_clean();

        $output = json_decode($rawOutput, true);
        $this->assertTrue($output['success']);
        $this->assertEquals('Pizza and images deleted', $output['message']);
        $this->assertEquals(200, http_response_code());
    }
}
