<?php
// controller/AdminPizza.php

class AdminPizza {
    protected $pizzaModel;

    // Konstruktorban injektáljuk a Pizza modellt, így tesztelésnél mockolható
    public function __construct($pizzaModel) {
        $this->pizzaModel = $pizzaModel;
    }

    public function handleRequest() {
        // Ha a kérés Content-Type-ja application/json, akkor olvassuk be a JSON adatokat
        if (isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
            $rawData = file_get_contents("php://input");
            $jsonData = json_decode($rawData, true);
            if (is_array($jsonData)) {
                $_REQUEST = array_merge($_REQUEST, $jsonData);
            }
        }

        $method = $_SERVER['REQUEST_METHOD'];

        try {
            if ($method === 'GET') {
                $pizzas = $this->pizzaModel->getAllPizzas();
                return json_encode(["success" => true, "data" => $pizzas]);
            } elseif ($method === 'POST') {
                // Létrehozás esetén action=create szükséges
                if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'create') {
                    $name = $_REQUEST['name'] ?? null;
                    $crust = $_REQUEST['crust'] ?? null;
                    $cutstyle = $_REQUEST['cutstyle'] ?? null;
                    $pizzasize = $_REQUEST['pizzasize'] ?? null;
                    $ingredient = $_REQUEST['ingredient'] ?? null;
                    $price = $_REQUEST['price'] ?? null;
                    if ($name && $crust && $cutstyle && $pizzasize && $ingredient && $price) {
                        $id = $this->pizzaModel->insertPizza($name, $crust, $cutstyle, $pizzasize, $ingredient, $price);
                        return json_encode(["success" => true, "id" => $id]);
                    } else {
                        return json_encode(["success" => false, "error" => "Missing fields for creation"]);
                    }
                } elseif (isset($_REQUEST['id'])) { // frissítés
                    $id = $_REQUEST['id'];
                    $data = [];
                    foreach (['name', 'crust', 'cutstyle', 'pizzasize', 'ingredient', 'price'] as $field) {
                        if (isset($_REQUEST[$field])) {
                            $data[$field] = $_REQUEST[$field];
                        }
                    }
                    if (empty($data)) {
                        return json_encode(["success" => false, "error" => "No data to update"]);
                    } else {
                        if ($this->pizzaModel->updatePizza($id, $data)) {
                            return json_encode(["success" => true]);
                        } else {
                            return json_encode(["success" => false, "error" => "Update failed"]);
                        }
                    }
                } else {
                    return json_encode(["success" => false, "error" => "No id provided and no create action specified"]);
                }
            } elseif ($method === 'DELETE') {
                $id = $_REQUEST['id'] ?? 0;
                if ($id) {
                    if ($this->pizzaModel->deletePizza($id)) {
                        return json_encode(["success" => true]);
                    } else {
                        return json_encode(["success" => false, "error" => "Deletion failed"]);
                    }
                } else {
                    return json_encode(["success" => false, "error" => "No id provided"]);
                }
            } else {
                return json_encode(["success" => false, "error" => "Unsupported method"]);
            }
        } catch (Exception $e) {
            return json_encode(["success" => false, "error" => $e->getMessage()]);
        }
    }
}
