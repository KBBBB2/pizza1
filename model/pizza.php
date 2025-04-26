<?php
// models/Pizza.php

require_once 'Database.php';

class Pizza {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // Egy adott pizza lekérdezése
    public function getPizza($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM pizza WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Az összes pizza lekérdezése
    public function getAllPizzas() {
        // Itt nincs külön tárolt eljárás, raw query marad
        $stmt = $this->pdo->query("SELECT * FROM pizza");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Indexed pizzák: tárolt eljárás (pizza_product)
    public function getIndexedPizzas() {
        $stmt = $this->pdo->prepare("CALL pizza_product()");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Új pizza beszúrása – visszaadja az új rekord ID-t
    public function insertPizza($name, $crust, $cutstyle, $pizzasize, $ingredient, $price) {
        $stmt = $this->pdo->prepare("
            INSERT INTO pizza (name, crust, cutstyle, pizzasize, ingredient, price) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        if ($stmt->execute([$name, $crust, $cutstyle, $pizzasize, $ingredient, $price])) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    // Pizza adatainak frissítése
    public function updatePizza($id, $data) {
        $fields = [];
        $params = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        $params[':id'] = $id;
        $sql = "UPDATE pizza SET " . implode(", ", $fields) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    // Pizza törlése
    public function deletePizza($id) {
        $stmt = $this->pdo->prepare("DELETE FROM pizza WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Pizza kép elérési útvonalának frissítése
    public function updatePizzaImage($id, $imagePath) {
        $stmt = $this->pdo->prepare("UPDATE pizza SET image = ? WHERE id = ?");
        return $stmt->execute([$imagePath, $id]);
    }
    
    // Featured pizzák lekérdezése – itt raw query (nincs tárolt eljárás megadva erre)
    public function getFeaturedPizzas() {
        $query = "SELECT * FROM featured_pizzas";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateImageExt(int $id, string $ext): bool {
        $sql = "UPDATE pizza SET image_ext = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$ext, $id]);
    }
    
}
?>
