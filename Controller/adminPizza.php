<?php
// controllers/AdminMenuController.php

require_once '../model/Pizza.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if (isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $rawData = file_get_contents("php://input");
    $jsonData = json_decode($rawData, true);
    if (is_array($jsonData)) {
        $_REQUEST = array_merge($_REQUEST, $jsonData);
    }
}

$method = $_SERVER['REQUEST_METHOD'];
$pizzaModel = new Pizza();

try {
    if ($method === 'GET') {
        $pizzas = $pizzaModel->getAllPizzas();
        echo json_encode(["success" => true, "data" => $pizzas]);
    } elseif ($method === 'POST') {
        // Létrehozás esetén action=create kell legyen
        if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'create') {
            $name = $_REQUEST['name'] ?? null;
            $crust = $_REQUEST['crust'] ?? null;
            $cutstyle = $_REQUEST['cutstyle'] ?? null;
            $pizzasize = $_REQUEST['pizzasize'] ?? null;
            $ingredient = $_REQUEST['ingredient'] ?? null;
            $price = $_REQUEST['price'] ?? null;
            if ($name && $crust && $cutstyle && $pizzasize && $ingredient && $price) {
                $id = $pizzaModel->insertPizza($name, $crust, $cutstyle, $pizzasize, $ingredient, $price);
                echo json_encode(["success" => true, "id" => $id]);
            } else {
                echo json_encode(["success" => false, "error" => "Missing fields for creation"]);
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
                echo json_encode(["success" => false, "error" => "No data to update"]);
            } else {
                if ($pizzaModel->updatePizza($id, $data)) {
                    echo json_encode(["success" => true]);
                } else {
                    echo json_encode(["success" => false, "error" => "Update failed"]);
                }
            }
        } else {
            echo json_encode(["success" => false, "error" => "No id provided and no create action specified"]);
        }
    } elseif ($method === 'DELETE') {
        $id = $_REQUEST['id'] ?? 0;
        if ($id) {
            if ($pizzaModel->deletePizza($id)) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "error" => "Deletion failed"]);
            }
        } else {
            echo json_encode(["success" => false, "error" => "No id provided"]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Unsupported method"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
