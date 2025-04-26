<?php
// vendor/bin/phpunit test/modelPizzaTest.php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../model/Pizza.php';

class modelPizzaTest extends TestCase {
    private $pdoMock;
    private $stmtMock;
    private $pizzaModel;

    protected function setUp(): void {
        // PDO és PDOStatement mock
        $this->pdoMock = $this->createMock(\PDO::class);
        $this->stmtMock = $this->createMock(\PDOStatement::class);

        // Model példány, reflektálással PDO injektálása
        $this->pizzaModel = new Pizza();
        $ref = new \ReflectionClass($this->pizzaModel);
        $prop = $ref->getProperty('pdo');
        $prop->setAccessible(true);
        $prop->setValue($this->pizzaModel, $this->pdoMock);
    }

    public function testGetPizza(): void {
        $expected = ['id' => 1, 'name' => 'Margherita'];
        // prepare + execute + fetch
        $this->pdoMock->method('prepare')->with("SELECT * FROM pizza WHERE id = ?")->willReturn($this->stmtMock);
        $this->stmtMock->method('execute')->with([1])->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn($expected);

        $result = $this->pizzaModel->getPizza(1);
        $this->assertEquals($expected, $result);
    }

    public function testGetAllPizzas(): void {
        $expected = [
            ['id' => 1, 'name' => 'Margherita'],
            ['id' => 2, 'name' => 'Pepperoni'],
        ];
        // query + fetchAll
        $this->pdoMock->method('query')->with("SELECT * FROM pizza")->willReturn($this->stmtMock);
        $this->stmtMock->method('fetchAll')->willReturn($expected);

        $result = $this->pizzaModel->getAllPizzas();
        $this->assertEquals($expected, $result);
    }

    public function testGetIndexedPizzas(): void {
        $expected = [ ['id' => 1, 'index' => 10] ];
        $this->pdoMock->method('prepare')->with("CALL pizza_product()")->willReturn($this->stmtMock);
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetchAll')->willReturn($expected);

        $result = $this->pizzaModel->getIndexedPizzas();
        $this->assertEquals($expected, $result);
    }

    public function testInsertPizzaSuccess(): void {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('execute')->willReturn(true);
        $this->pdoMock->method('lastInsertId')->willReturn('42');

        $result = $this->pizzaModel->insertPizza('Name', 'thin', 'square', 'L', 'tomato', 10.5);
        $this->assertEquals('42', $result);
    }

    public function testInsertPizzaFailure(): void {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('execute')->willReturn(false);

        $result = $this->pizzaModel->insertPizza('Name', 'thin', 'square', 'L', 'tomato', 10.5);
        $this->assertFalse($result);
    }

    public function testUpdatePizza(): void {
        $data = ['name' => 'BBQ', 'price' => 12.0];
        // Expect SQL with named placeholders
        $sqlPattern = '/UPDATE pizza SET name = :name, price = :price WHERE id = :id/';
        $this->pdoMock->method('prepare')->with($this->callback(function($sql) use ($sqlPattern) {
            return preg_match($sqlPattern, $sql) === 1;
        }))->willReturn($this->stmtMock);
        $this->stmtMock->method('execute')->with([':name' => 'BBQ', ':price' => 12.0, ':id' => 5])->willReturn(true);

        $result = $this->pizzaModel->updatePizza(5, $data);
        $this->assertTrue($result);
    }

    public function testDeletePizza(): void {
        $this->pdoMock->method('prepare')->with("DELETE FROM pizza WHERE id = ?")->willReturn($this->stmtMock);
        $this->stmtMock->method('execute')->with([3])->willReturn(true);

        $result = $this->pizzaModel->deletePizza(3);
        $this->assertTrue($result);
    }

    public function testUpdatePizzaImage(): void {
        $this->pdoMock->method('prepare')->with("UPDATE pizza SET image = ? WHERE id = ?")->willReturn($this->stmtMock);
        $this->stmtMock->method('execute')->with(['path/to/img.jpg', 7])->willReturn(true);

        $result = $this->pizzaModel->updatePizzaImage(7, 'path/to/img.jpg');
        $this->assertTrue($result);
    }

    public function testGetFeaturedPizzas(): void {
        $expected = [ ['id' => 1], ['id' => 2] ];
        $this->pdoMock->method('prepare')->with("SELECT * FROM featured_pizzas")->willReturn($this->stmtMock);
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetchAll')->willReturn($expected);

        $result = $this->pizzaModel->getFeaturedPizzas();
        $this->assertEquals($expected, $result);
    }

    public function testUpdateImageExt(): void {
        $this->pdoMock->method('prepare')->with("UPDATE pizza SET image_ext = ? WHERE id = ?")->willReturn($this->stmtMock);
        $this->stmtMock->method('execute')->with(['png', 9])->willReturn(true);

        $result = $this->pizzaModel->updateImageExt(9, 'png');
        $this->assertTrue($result);
    }
}
